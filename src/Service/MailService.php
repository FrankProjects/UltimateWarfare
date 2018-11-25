<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use Exception;
use FrankProjects\UltimateWarfare\Entity\User;
use RuntimeException;
use Swift_Mailer;
use Psr\Log\LoggerInterface;
use Swift_Message;
use Twig_Environment;

final class MailService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * MailService constructor
     *
     * @param LoggerInterface $logger
     * @param Twig_Environment $twig
     * @param Swift_Mailer $mailer
     */
    public function __construct(
        LoggerInterface $logger,
        Twig_Environment $twig,
        Swift_Mailer $mailer
    ) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    /**
     * @param User $user
     */
    public function sendRegistrationMail(User $user): void
    {
        try {
            $message = (new Swift_Message('Welcome to Ultimate-Warfare'))
                ->setFrom('no-reply@ultimate-warfare.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->twig->render(
                        'email/register.html.twig',
                        [
                            'username' => $user->getUsername(),
                            'token' => $user->getConfirmationToken()
                        ]),
                    'text/html'
                )
                ->addPart(
                    $this->twig->render(
                        'email/register.txt.twig',
                        [
                            'username' => $user->getUsername(),
                            'token' => $user->getConfirmationToken()
                        ]
                    ),
                    'text/plain'
                );

            $this->sendMail($message, $user->getEmail(), 'registration');
        } catch (Exception $exception) {
            $this->logger->error("Send a registration email to {$user->getEmail()} failed");
            throw new RunTimeException('Sending the registration email failed!');
        }
    }

    /**
     * @param User $user
     * @param string $ipAddress
     */
    public function sendPasswordResetMail(User $user, string $ipAddress): void
    {
        try {
            $message = (new Swift_Message('Username & Password request'))
                ->setFrom('no-reply@ultimate-warfare.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->twig->render(
                        'email/passwordReset.html.twig',
                        [
                            'username' => $user->getUsername(),
                            'token' => $user->getConfirmationToken(),
                            'ipAddress' => $ipAddress
                        ]
                    ),
                    'text/html'
                );

            $this->sendMail($message, $user->getEmail(), 'password reset');
        } catch (Exception $exception) {
            $this->logger->error("Send a password reset email to {$user->getEmail()} failed");
            throw new RunTimeException('Sending the password reset email failed!');
        }
    }

    /**
     * @param Swift_Message $message
     * @param string $email
     * @param string $type
     */
    private function sendMail(Swift_Message $message, string $email, string $type): void
    {
        $messages = $this->mailer->send($message);
        if ($messages == 0) {
            $logMessage = "Send a {$type} email to {$email} failed";
            $this->logger->error($logMessage);
            throw new RunTimeException($logMessage);
        }

        $this->logger->info("Send a {$type} email to {$email}");
    }
}
