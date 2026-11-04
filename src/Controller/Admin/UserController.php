<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Service\Action\UserActionService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class UserController extends AbstractController
{
    private const int USERS_PER_PAGE = 50;

    private UserRepository $userRepository;
    private UserActionService $userActionService;

    public function __construct(
        UserRepository $userRepository,
        UserActionService $userActionService
    ) {
        $this->userRepository = $userRepository;
        $this->userActionService = $userActionService;
    }

    public function ban(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if ($user->isBanned()) {
            $this->addFlash('error', 'User is already banned');
        } else {
            $user->setBanned(true);
            $this->userRepository->save($user);
            $this->addFlash('success', 'User banned!');
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId]);
    }

    public function unban(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if (!$user->isBanned()) {
            $this->addFlash('error', 'User is not banned');
        } else {
            $user->setBanned(false);
            $this->userRepository->save($user);
            $this->addFlash('success', 'User unbanned!');
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId]);
    }

    public function verifyEmail(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if ($user->isEmailVerified()) {
            $this->addFlash('error', 'User email address is already verified');
        } else {
            $user->setEmailVerified(true);
            $user->setEmailVerificationToken(null);
            $this->userRepository->save($user);
            $this->addFlash('success', 'User email address marked as verified!');
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId]);
    }

    public function forumBan(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if ($user->isForumBanned()) {
            $this->addFlash('error', 'User is already forum banned');
        } else {
            $user->setForumBanned(true);
            $this->userRepository->save($user);
            $this->addFlash('success', 'User forum banned!');
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId]);
    }

    public function forumUnban(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if (!$user->isForumBanned()) {
            $this->addFlash('error', 'User is not forum banned');
        } else {
            $user->setForumBanned(false);
            $this->userRepository->save($user);
            $this->addFlash('success', 'User forum unbanned!');
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId]);
    }

    public function list(Request $request): Response
    {
        $filter = $request->attributes->getString('filter');
        $sort = $request->query->getString('sort') === 'lastSeen' ? 'lastSeen' : 'signup';
        $direction = $request->query->getString('direction') === 'asc' ? 'asc' : 'desc';

        $totalUsers = $this->userRepository->countForAdminList($filter);
        $totalPages = max(1, (int) ceil($totalUsers / self::USERS_PER_PAGE));
        $page = min(max(1, $request->query->getInt('page', 1)), $totalPages);

        $users = $this->userRepository->findForAdminList(
            $filter,
            $sort,
            $direction,
            self::USERS_PER_PAGE,
            ($page - 1) * self::USERS_PER_PAGE
        );

        return $this->render(
            'admin/user/list.html.twig',
            [
                'users' => $users,
                'filter' => $filter,
                'sort' => $sort,
                'direction' => $direction,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'totalUsers' => $totalUsers,
                ],
            ]
        );
    }

    public function makeAdmin(int $userId): RedirectResponse
    {
        try {
            $user = $this->getUserObject($userId);
            $this->userActionService->addRoleToUser($user, User::ROLE_ADMIN);
            $this->addFlash('success', 'Add admin role to user!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId]);
    }

    public function removeAdmin(int $userId): RedirectResponse
    {
        try {
            $user = $this->getUserObject($userId);
            $this->userActionService->removeRoleFromUser($user, User::ROLE_ADMIN);
            $this->addFlash('success', 'Removed admin role from user!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId]);
    }

    public function read(int $userId): Response
    {
        $user = $this->getUserObject($userId);

        $sameIpUsers = [];
        if ($user->getLastSeenIp() !== null) {
            $sameIpUsers = array_filter(
                $this->userRepository->findByLastSeenIp($user->getLastSeenIp()),
                static fn (User $other): bool => $other->getId() !== $user->getId()
            );
        }

        return $this->render(
            'admin/user/read.html.twig',
            [
                'user' => $user,
                'sameIpUsers' => $sameIpUsers,
            ]
        );
    }

    private function getUserObject(int $userId): User
    {
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new RuntimeException('User does not exist');
        }

        return $user;
    }
}
