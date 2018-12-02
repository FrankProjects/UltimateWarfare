<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Form\ResetPasswordType;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Service\MailService;
use FrankProjects\UltimateWarfare\Util\TokenGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Throwable;

final class ResetPasswordController extends AbstractController
{
    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * ResetPasswordController constructor
     *
     * @param MailService $mailService
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     */
    public function __construct(
        MailService $mailService,
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository
    ) {
        $this->mailService = $mailService;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function requestPasswordReset(Request $request): Response
    {
        $email = $request->request->get('email');
        if ($email) {
            $user = $this->userRepository->findByEmail($email);

            if ($user) {
                if (!$user->isEnabled()) {
                    $this->addFlash('error', 'Your account is not activated!');
                } elseif ($user->getPasswordRequestedAt()->getTimestamp() + 12 * 60 * 60 < time()) {
                    $generator = new TokenGenerator();
                    $token = $generator->generateToken(40);

                    $user->setPasswordRequestedAt(new \DateTime());
                    $user->setConfirmationToken($token);
                    $this->userRepository->save($user);

                    try {
                        $this->mailService->sendPasswordResetMail($user, $request->getClientIp());
                        $this->addFlash('success', "An e-mail has been sent to {$user->getEmail()} with your recovery instructions... Check your Spam mail if you didn't receive an email");
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

    /**
     * @param Request $request
     * @param string $token
     * @return Response
     */
    public function resetPassword(Request $request, string $token): Response
    {
        $user = $this->userRepository->findByConfirmationToken($token);

        if ($user) {
            $form = $this->createForm(ResetPasswordType::class, $user);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);
                $user->setConfirmationToken(null);
                $this->userRepository->save($user);

                $this->addFlash('success', 'You successfully changed your password!');
                return $this->redirectToRoute('Site/Login');
            }

            return $this->render('site/resetPassword.html.twig', [
                'form' => $form->createView(),
                'token' => $token
            ]);
        }

        $this->addFlash('error', 'Invalid password reset token!');
        return $this->redirectToRoute('Site/Login');
    }
}
