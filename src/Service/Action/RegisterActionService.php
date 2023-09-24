<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use DateTime;
use Exception;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Service\MailService;
use FrankProjects\UltimateWarfare\Util\TokenGenerator;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterActionService
{
    private MailService $mailService;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;

    public function __construct(
        MailService $mailService,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ) {
        $this->mailService = $mailService;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
    }

    public function activateUser(string $token): void
    {
        $user = $this->userRepository->findByConfirmationToken($token);

        if ($user === null) {
            throw new RuntimeException("User with this token does not exist");
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $this->userRepository->save($user);
    }

    /**
     * XXX TODO: Add captcha
     *
     * @param User $user
     * @throws Exception
     */
    public function register(User $user): void
    {
        $password = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
        $user->setPassword($password);

        try {
            $generator = new TokenGenerator();
            $token = $generator->generateToken(40);
        } catch (Exception $exception) {
            throw new RuntimeException('TokenGenerator failed!');
        }

        $user->setSignup(new DateTime());
        $user->setConfirmationToken($token);

        if ($this->userRepository->findByEmail($user->getEmail()) !== null) {
            throw new RuntimeException('User with this email already exist!');
        }

        if ($this->userRepository->findByUsername($user->getUsername()) !== null) {
            throw new RuntimeException('User with this username already exist!');
        }

        $this->userRepository->save($user);
        $this->mailService->sendRegistrationMail($user);
    }
}
