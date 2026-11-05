<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use DateTime;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Form\ResetPasswordType;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Service\MailService;
use FrankProjects\UltimateWarfare\Util\TokenGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Throwable;

final class ResetPasswordController extends AbstractController
{
    /** A reset link is valid this long; a new one can only be requested after the previous one expired */
    private const int PASSWORD_RESET_TOKEN_LIFETIME_SECONDS = 60 * 60;

    private MailService $mailService;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;

    public function __construct(
        MailService $mailService,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ) {
        $this->mailService = $mailService;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
    }

    public function requestPasswordReset(Request $request): Response
    {
        $email = (string) $request->request->get('email');
        if ($email !== '') {
            $user = $this->userRepository->findByEmail($email);

            if ($user !== null) {
                if (!$user->isEmailVerified()) {
                    $this->addFlash('error', 'Your email address is not verified!');
                } elseif (!$this->isPasswordResetTokenActive($user)) {
                    $generator = new TokenGenerator();
                    $token = $generator->generateToken(40);

                    $user->setPasswordResetRequestedAt(new DateTime());
                    $user->setPasswordResetToken($token);
                    $this->userRepository->save($user);

                    try {
                        $this->mailService->sendPasswordResetMail($user, (string) $request->getClientIp());
                        $this->addFlash(
                            'success',
                            "An e-mail has been sent to {$user->getEmail()} with your recovery instructions..."
                            . " Check your Spam mail if you didn't receive an email"
                        );
                    } catch (Throwable $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                } else {
                    $this->addFlash('error', 'Already has active reset token, please check your email!');
                }
            } else {
                $this->addFlash('error', 'Unknown email address');
            }
        }

        return $this->render('site/requestPasswordReset.html.twig');
    }

    public function resetPassword(Request $request, string $token): Response
    {
        $user = $this->userRepository->findByPasswordResetToken($token);

        if ($user !== null && $this->isPasswordResetTokenActive($user)) {
            $form = $this->createForm(ResetPasswordType::class);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var string $plainPassword */
                $plainPassword = $form->get('plainPassword')->getData();
                $password = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($password);
                $user->setPasswordResetToken(null);
                $this->userRepository->save($user);

                $this->addFlash('success', 'You successfully changed your password!');
                return $this->redirectToRoute('Site/Login');
            }

            return $this->render(
                'site/resetPassword.html.twig',
                [
                    'form' => $form->createView(),
                    'token' => $token
                ]
            );
        }

        $this->addFlash('error', 'Invalid or expired password reset link!');
        return $this->redirectToRoute('Site/Login');
    }

    private function isPasswordResetTokenActive(User $user): bool
    {
        $requestedAt = $user->getPasswordResetRequestedAt();

        return $user->getPasswordResetToken() !== null
            && $requestedAt !== null
            && $requestedAt->getTimestamp() + self::PASSWORD_RESET_TOKEN_LIFETIME_SECONDS >= time();
    }
}
