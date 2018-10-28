<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Form\RegistrationType;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Util\TokenGenerator;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Psr\Log\LoggerInterface;

final class RegisterController extends Controller
{
    /**
     * XXX TODO: Add captcha
     * XXX TODO: username, email already in use
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Swift_Mailer $mailer
     * @param LoggerInterface $logger
     * @return Response
     * @throws \Exception
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, Swift_Mailer $mailer, LoggerInterface $logger): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);


            // Get default MapDesign
            // XXX TODO: make setting?
            $mapDesign = $em->getRepository('Game:MapDesign')
                ->find(3);

            $generator = new TokenGenerator();
            $token = $generator->generateToken(40);

            $user->setMapDesign($mapDesign);
            $user->setSignup(new \DateTime());
            $user->setConfirmationToken($token);

            $em->persist($user);
            $em->flush();

            $message = (new \Swift_Message('Welcome to Ultimate-Warfare'))
                ->setFrom('no-reply@ultimate-warfare.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'email/register.html.twig',
                        [
                            'username' => $user->getUsername(),
                            'token' => $token
                        ]),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        'email/register.txt.twig',
                        [
                            'username' => $user->getUsername(),
                            'token' => $token
                        ]
                    ),
                    'text/plain'
                );

            $messages = $mailer->send($message);
            if ($messages == 0) {
                $logger->error("Send a registration email to {$user->getEmail()} failed");
                $this->addFlash('error', 'You successfully reqistered an account, but an error occurred while sending an activation email!');
            } else {
                $logger->info("Send a registration email to {$user->getEmail()}");
                $this->addFlash('success', "You successfully reqistered an account! An e-mail has been sent to {$user->getEmail()} with your activation code...");
            }
        }

        return $this->render('site/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param string $token
     * @return Response
     */
    public function activateUser(Request $request, string $token): Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('Game:User')
            ->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            throw new NotFoundHttpException("User with token {$token} does not exist");
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'You successfully activated your account!');
        return $this->redirectToRoute('Site/Login');
    }
}
