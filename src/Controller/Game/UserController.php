<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\UnbanRequestRepository;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Service\Action\UserActionService;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends BaseGameController
{
    private UserRepository $userRepository;
    private UnbanRequestRepository $unbanRequestRepository;
    private UserActionService $userActionService;

    public function __construct(
        UserRepository $userRepository,
        UnbanRequestRepository $unbanRequestRepository,
        UserActionService $userActionService
    ) {
        $this->userRepository = $userRepository;
        $this->unbanRequestRepository = $unbanRequestRepository;
        $this->userActionService = $userActionService;
    }

    public function deleteAccount(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        Security $security
    ): Response {
        $user = $this->getGameUser();
        $error = null;

        if ($request->isMethod(Request::METHOD_POST)) {
            $error = $this->getDeleteAccountError($request, $passwordHasher, $user);

            if ($error === null) {
                try {
                    $this->userActionService->deleteUser($user);
                } catch (RuntimeException $e) {
                    $error = $e->getMessage();
                }
            }

            if ($error === null) {
                $security->logout(false);
                $this->addFlash('success', 'Your account has been deleted.');
                return $this->redirectToRoute('Site/Homepage');
            }
        }

        // An error status rolls back the request transaction (PlayerRequestSubscriber)
        return $this->render(
            'game/deleteAccount.html.twig',
            [
                'user' => $user,
                'error' => $error,
            ],
            new Response(null, $error === null ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    private function getDeleteAccountError(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        User $user
    ): ?string {
        if (!$this->isCsrfTokenValid('delete_account', (string) $request->request->get('_token'))) {
            return 'Invalid CSRF token, please try again.';
        }

        if (!$passwordHasher->isPasswordValid($user, (string) $request->request->get('password'))) {
            return 'Wrong password!';
        }

        return null;
    }

    public function banned(Request $request): Response
    {
        $user = $this->getGameUser(allowBanned: true);
        if (!$user->isBanned()) {
            $this->addFlash('error', 'You are not banned!');
            return $this->redirectToRoute('Game/WorldMap');
        }

        $unbanRequest = $this->unbanRequestRepository->findByUser($user);

        if ($unbanRequest === null) {
            $unbanRequest = new UnbanRequest();
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            $unbanReason = trim((string) $request->request->get('post'));

            $unbanRequest->setPost($unbanReason);
            $unbanRequest->setUser($user);
            $this->unbanRequestRepository->save($unbanRequest);

            $this->addFlash('success', 'We have received your request, we will try to read your request ASAP...');
        }

        return $this->render(
            'game/banned.html.twig',
            [
                'user' => $user,
                'unbanRequest' => $unbanRequest
            ]
        );
    }

    public function profileApi(): JsonResponse
    {
        $user = $this->getGameUser();
        $player = $this->getPlayer();
        $isFederationGeneral = $player->getFederation() !== null
            && $player->getFederationHierarchy() === Player::FEDERATION_HIERARCHY_GENERAL;

        return new JsonResponse([
            'success' => true,
            'data' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'signedUpAt' => $user->getSignedUpAt()->format('Y-m-d H:i:s'),
                'accountType' => $this->getAccountType(),
                'banned' => $user->isBanned(),
                'canSurrender' => $player->canSurrender(),
                'isFederationGeneral' => $isFederationGeneral,
            ]
        ]);
    }

    public function surrenderApi(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        PlayerRepository $playerRepository
    ): JsonResponse {
        $player = $this->getPlayer();
        $user = $this->getGameUser();

        try {
            /** @var array{password?: string} $data */
            $data = json_decode($request->getContent(), true);
            $password = $data['password'] ?? '';

            if ($password === '') {
                return new JsonResponse(['success' => false, 'message' => 'Password is required.']);
            }

            if (!$passwordHasher->isPasswordValid($user, $password)) {
                return new JsonResponse(['success' => false, 'message' => 'Wrong password!']);
            }

            if (!$player->canSurrender()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'You cannot surrender for the first 48 hours!',
                ]);
            }

            if (
                $player->getFederation() !== null
                && $player->getFederationHierarchy() === Player::FEDERATION_HIERARCHY_GENERAL
            ) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'You cannot surrender while you are a Federation General. '
                        . 'Please hand over or disband your Federation first.',
                ]);
            }

            $playerRepository->remove($player);

            return new JsonResponse([
                'success' => true,
                'message' => 'You have surrendered your empire...',
                'redirect' => '/game/world/select',
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => 'An error occurred.']);
        }
    }

    public function changePasswordApi(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $user = $this->getGameUser();

        try {
            /** @var array{oldPassword?: string, newPassword?: string, newPasswordRepeat?: string} $data */
            $data = json_decode($request->getContent(), true);
            $oldPassword = $data['oldPassword'] ?? '';
            $newPassword = $data['newPassword'] ?? '';
            $newPasswordRepeat = $data['newPasswordRepeat'] ?? '';

            if ($oldPassword === '' || $newPassword === '') {
                return new JsonResponse(['success' => false, 'message' => 'All fields are required.']);
            }

            if (strlen($newPassword) < 8) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'New password must be at least 8 characters.',
                ]);
            }

            if ($newPassword !== $newPasswordRepeat) {
                return new JsonResponse(['success' => false, 'message' => 'New passwords do not match.']);
            }

            if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                return new JsonResponse(['success' => false, 'message' => 'Old password is invalid.']);
            }

            $newEncodedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($newEncodedPassword);
            $this->userRepository->save($user);

            return new JsonResponse(['success' => true, 'message' => 'Password changed successfully!']);
        } catch (\Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => 'An error occurred.']);
        }
    }

    private function getAccountType(): string
    {
        $user = $this->getGameUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_PLAYER', $roles, true)) {
            return 'Player';
        }

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return 'Admin';
        }

        return 'Guest';
    }
}
