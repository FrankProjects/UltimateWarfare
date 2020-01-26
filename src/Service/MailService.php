<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use Exception;
use FrankProjects\UltimateWarfare\Entity\User;
use RuntimeException;
use Swift_Mailer;
use Psr\Log\LoggerInterface;
use Swift_Message;
use Twig\Environment;

final class MailService
{
    private LoggerInterface $logger;
    private Environment $twig;
    private Swift_Mailer $mailer;

    public function __construct(
        LoggerInterface $logger,
        Environment $twig,
        Swift_Mailer $mailer
    ) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    public function sendRegistrationMail(User $user): void
    {
        $mailParameters = [
            'username' => $user->getUsername(),
            'token' => $user->getConfirmationToken()
        ];

        $message = (new Swift_Message('Welcome to Ultimate-Warfare'))
            ->setFrom('no-reply@ultimate-warfare.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->generateMailBody('email/register.html.twig', $mailParameters),
                'text/html'
            )
            ->addPart(
                $this->generateMailBody('email/register.txt.twig', $mailParameters),
                'text/plain'
            );

        $this->sendMail($message, $user->getEmail(), 'registration');
    }

    public function sendPasswordResetMail(User $user, string $ipAddress): void
    {
        $mailParameters = [
            'username' => $user->getUsername(),
            'token' => $user->getConfirmationToken(),
            'ipAddress' => $ipAddress
        ];

        $message = (new Swift_Message('Username & Password request'))
            ->setFrom('no-reply@ultimate-warfare.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->generateMailBody('email/passwordReset.html.twig', $mailParameters),
                'text/html'
            );

        $this->sendMail($message, $user->getEmail(), 'password reset');
    }

    private function generateMailBody(string $templateName, array $parameters): string
    {
        try {
            return $this->twig->render($templateName, $parameters);
        } catch (Exception $exception) {
            throw new RunTimeException('Rendering mail failed!');
        }
    }

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
