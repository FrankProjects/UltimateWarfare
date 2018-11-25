<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Form\ResetPasswordType;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Util\TokenGenerator;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class ResetPasswordController extends Controller
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

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
     * @param LoggerInterface $logger
     * @param Swift_Mailer $mailer
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     */
    public function __construct(
        LoggerInterface $logger,
        Swift_Mailer $mailer,
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository
    ) {
        $this->logger = $logger;
        $this->mailer = $mailer;
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

                    $message = (new \Swift_Message('Username & Password request'))
                        ->setFrom('no-reply@ultimate-warfare.com')
                        ->setTo($user->getEmail())
                        ->setBody(
                            $this->renderView(
                                'email/passwordReset.html.twig',
                                [
                                    'username' => $user->getUsername(),
                                    'token' => $token,
                                    'ipAddress' => $request->getClientIp()
                                ]
                            ),
                            'text/html'
                        );

                    $messages = $this->mailer->send($message);
                    if ($messages == 0) {
                        $this->logger->error("Send a password reset email to {$user->getEmail()} failed");
                        $this->addFlash('error', 'An error occurred while sending a password recover email. Send an email to admin@ultimate-warfare.com with your email and username.');
                    } else {
                        $this->logger->info("Send a password reset email to {$user->getEmail()}");
                        $this->addFlash('success', "An e-mail has been sent to {$user->getEmail()} with your recovery instructions... Check your Spam mail if you didn't receive an email");
                    }
                } else {
                    $this->addFlash('error', 'Already has active reset token, please check your email!');
                }
            } else {
                $this->addFlash('error', 'Unknown email adress');
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
