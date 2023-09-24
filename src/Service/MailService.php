<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use Exception;
use FrankProjects\UltimateWarfare\Entity\User;
use RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class MailService
{
    private LoggerInterface $logger;
    private Environment $twig;
    private MailerInterface $mailer;

    public function __construct(
        LoggerInterface $logger,
        Environment $twig,
        MailerInterface $mailer
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

        $message = (new Email())
            ->subject('Welcome to Ultimate-Warfare')
            ->from('no-reply@ultimate-warfare.com')
            ->to($user->getEmail())
            ->html($this->generateMailBody('email/register.html.twig', $mailParameters))
            ->text($this->generateMailBody('email/register.txt.twig', $mailParameters));

        $this->sendMail($message, $user->getEmail(), 'registration');
    }

    public function sendPasswordResetMail(User $user, string $ipAddress): void
    {
        $mailParameters = [
            'username' => $user->getUsername(),
            'token' => $user->getConfirmationToken(),
            'ipAddress' => $ipAddress
        ];

        $message = (new Email())
            ->subject('Username & Password request')
            ->from('no-reply@ultimate-warfare.com')
            ->to($user->getEmail())
            ->html($this->generateMailBody('email/passwordReset.html.twig', $mailParameters));

        $this->sendMail($message, $user->getEmail(), 'password reset');
    }

    /**
     * @param array<string, string> $parameters
     */
    private function generateMailBody(string $templateName, array $parameters): string
    {
        try {
            return $this->twig->render($templateName, $parameters);
        } catch (Exception $exception) {
            throw new RuntimeException('Rendering mail failed!');
        }
    }

    private function sendMail(Email $message, string $email, string $type): void
    {
        try {
            $this->mailer->send($message);
            $this->logger->info("Send a {$type} email to {$email}");
        } catch (TransportExceptionInterface $e) {
            $logMessage = "Send a {$type} email to {$email} failed";
            $this->logger->error($logMessage);
            throw new RuntimeException($logMessage);
        }
    }
}
