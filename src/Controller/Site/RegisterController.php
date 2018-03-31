<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Form\RegistrationType;
use FrankProjects\UltimateWarfare\Entity\User;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, Swift_Mailer $mailer, LoggerInterface $logger): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();

            // Get default MapDesign
            // XXX TODO: make setting?
            $mapDesign = $em->getRepository('Game:MapDesign')
                ->find(3);

            $user->setMapDesign($mapDesign);
            $user->setSignup(new \DateTime());

            $em->persist($user);
            $em->flush();

            $message = (new \Swift_Message('Welcome to Ultimate-Warfare'))
                ->setFrom('send@example.com')
                ->setTo('recipient@example.com')
                ->setBody(
                    $this->renderView(
                        'email/registration.html.twig',
                        array('username' => $user->getUsername())
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        'email/registration.txt.twig',
                        array('username' => $user->getUsername())
                    ),
                    'text/plain'
                )
            ;

            $messages = $mailer->send($message);
            if ($messages == 0) {
                $logger->error("Send a registration email to {$user->getEmail()} failed");
            } else {
                $logger->info("Send a registration email to {$user->getEmail()}");
            }
            // XXX TODO: Send mail
            /**
            //Init mail settings
            $mail_settings['title'] = "Welcome to Ultimate-Warfare";
            $mail_settings['from_header'] = "From: Ultimate-Warfare <no-reply@ultimate-warfare.com>";
            $mail_settings['from_address'] = "no-reply@ultimate-warfare.com";

            //Init error/success messages
            $mail_settings['error'] = "* You succesfully registered, but an error occurred while sending an activation email!<br />Send an email to admin@ultimate-warfare.com with your email and username.";
            $mail_settings['success'] = "You have been succesfully registered.<br /> An e-mail has been sent to ".filter_html($mail_vars['email'])." with your activation code... <br />Activate your account within 48 hours!<br /><br /><a class=\"B\" href=\"login.php\" title=\"Ultimate Warfare - Login\">Click here to Login.</a>";

             */

            $this->addFlash('success', "You successfully reqistered an account! An e-mail has been sent to {$user->getEmail()} with your activation code... <br />Activate your account within 48 hours!");
        }

        return $this->render('site/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
