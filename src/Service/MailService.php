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
        $emailVerificationToken = $user->getEmailVerificationToken();
        if ($emailVerificationToken === null) {
            throw new RuntimeException("Invalid email verification token");
        }

        $mailParameters = [
            'username' => $user->getUsername(),
            'token' => $emailVerificationToken
        ];

        $message = (new Email())
            ->subject('Welcome to Ultimate-Warfare')
            ->from('no-reply@ultimate-warfare.com')
            ->to($user->getEmail())
            ->html($this->generateMailBody('email/register.html.twig', $mailParameters))
            ->text($this->generateMailBody('email/register.txt.twig', $mailParameters));

        $this->sendMail($message, $user, 'registration');
    }

    public function sendPasswordResetMail(User $user, string $ipAddress): void
    {
        $passwordResetToken = $user->getPasswordResetToken();
        if ($passwordResetToken === null) {
            throw new RuntimeException("Invalid password reset token");
        }

        $mailParameters = [
            'username' => $user->getUsername(),
            'token' => $passwordResetToken,
            'ipAddress' => $ipAddress
        ];

        $message = (new Email())
            ->subject('Username & Password request')
            ->from('no-reply@ultimate-warfare.com')
            ->to($user->getEmail())
            ->html($this->generateMailBody('email/passwordReset.html.twig', $mailParameters));

        $this->sendMail($message, $user, 'password reset');
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

    /**
     * Logs the user id, never the email address: logs must not contain personal data, so they need no retention.
     */
    private function sendMail(Email $message, User $user, string $type): void
    {
        try {
            $this->mailer->send($message);
            $this->logger->info("Sent a {$type} email to user {$user->getId()}");
        } catch (TransportExceptionInterface $e) {
            // Not the exception message: SMTP errors can contain the recipient address
            $this->logger->error("Sending a {$type} email to user {$user->getId()} failed (" . $e::class . ')');
            throw new RuntimeException("Sending the {$type} email failed, please try again later.");
        }
    }
}
