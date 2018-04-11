<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Form\ResetPasswordType;
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
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function requestPasswordReset(Request $request, Swift_Mailer $mailer, LoggerInterface $logger): Response
    {
        $email = $request->request->get('email');
        if ($email) {
            $em = $this->getDoctrine()->getManager();

            /** @var User $user */
            $user = $em->getRepository('Game:User')
                ->findOneBy(['email' => $email]);

            if ($user) {
                if ($user->getPasswordRequestedAt()->getTimestamp() + 12 * 60 * 60 < time()) {
                    $generator = new TokenGenerator();
                    $token = $generator->generateToken(40);

                    $user->setPasswordRequestedAt(new \DateTime());
                    $user->setConfirmationToken($token);

                    $em->persist($user);
                    $em->flush();

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

                    $messages = $mailer->send($message);
                    if ($messages == 0) {
                        $logger->error("Send a password reset email to {$user->getEmail()} failed");
                        $this->addFlash('error', 'An error occurred while sending a password recover email. Send an email to admin@ultimate-warfare.com with your email and username.');
                    } else {
                        $logger->info("Send a password reset email to {$user->getEmail()}");
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
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('Game:User')
            ->findOneBy(['confirmationToken' => $token]);

        if ($user) {
            $form = $this->createForm(ResetPasswordType::class, $user);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);
                $user->setConfirmationToken(null);
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'You succesfully changed your password!');
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
