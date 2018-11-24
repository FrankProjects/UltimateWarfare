<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use Exception;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\MapDesignRepository;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Util\TokenGenerator;
use RuntimeException;
use Swift_Mailer;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Psr\Log\LoggerInterface;
use Twig_Environment;

final class RegisterActionService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MapDesignRepository
     */
    private $mapDesignRepository;

    /**
     * @var Twig_Environment
     */
    private $twig;

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
     * RegisterActionService constructor
     *
     * @param LoggerInterface $logger
     * @param MapDesignRepository $mapDesignRepository
     * @param Twig_Environment $twig
     * @param Swift_Mailer $mailer
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     */
    public function __construct(
        LoggerInterface $logger,
        MapDesignRepository $mapDesignRepository,
        Twig_Environment $twig,
        Swift_Mailer $mailer,
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository
    ) {
        $this->logger = $logger;
        $this->mapDesignRepository = $mapDesignRepository;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $token
     */
    public function activateUser(string $token): void
    {
        $user = $this->userRepository->findByConfirmationToken($token);

        if (!$user) {
            throw new RunTimeException("User with this token does not exist");
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $this->userRepository->save($user);
    }

    /**
     * XXX TODO: Add captcha
     * XXX TODO: username, email already in use
     *
     * @param User $user
     */
    public function register(User $user): void
    {
        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);

        // Get default MapDesign
        // XXX TODO: make setting?
        $mapDesign = $this->mapDesignRepository->find(3);

        try {
            $generator = new TokenGenerator();
            $token = $generator->generateToken(40);
        } catch (\Exception $exception) {
            throw new RunTimeException('TokenGenerator failed!');
        }

        $user->setMapDesign($mapDesign);
        $user->setSignup(new \DateTime());
        $user->setConfirmationToken($token);

        if ($this->userRepository->findByEmail($user->getEmail()) !== null) {
            throw new RunTimeException('User with this email already exist!');
        }

        if ($this->userRepository->findByUsername($user->getUsername()) !== null) {
            throw new RunTimeException('User with this username already exist!');
        }

        $this->userRepository->save($user);

        $this->sendRegistrationMail($user);
    }

    /**
     * @param User $user
     */
    private function sendRegistrationMail(User $user): void
    {
        try {
            $message = (new \Swift_Message('Welcome to Ultimate-Warfare'))
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

            $messages = $this->mailer->send($message);
            if ($messages == 0) {
                $this->logger->error("Send a registration email to {$user->getEmail()} failed");
                throw new RunTimeException('Sending the registration email failed!');
            }

            $this->logger->info("Send a registration email to {$user->getEmail()}");
        } catch (Exception $exception) {
            $this->logger->error("Send a registration email to {$user->getEmail()} failed");
            throw new RunTimeException('Sending the registration email failed!');
        }
    }
}
