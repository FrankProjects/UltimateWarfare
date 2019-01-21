<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserController constructor
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * @param int $userId
     * @return RedirectResponse
     */
    public function ban(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if (!$user->getActive()) {
            $this->addFlash('error', 'User is already banned');
        } else {
            $user->setActive(false);
            $this->userRepository->save($user);
            $this->addFlash('success', 'User banned!');
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId], 302);
    }

    /**
     * @param int $userId
     * @return RedirectResponse
     */
    public function unban(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if ($user->getActive()) {
            $this->addFlash('error', 'User is not banned');
        } else {
            $user->setActive(true);
            $this->userRepository->save($user);
            $this->addFlash('success', 'User unbanned!');
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId], 302);
    }

    /**
     * @param int $userId
     * @return RedirectResponse
     */
    public function enable(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if ($user->isEnabled()) {
            $this->addFlash('error', 'User is already enabled');
        } else {
            $user->setEnabled(true);
            $user->setConfirmationToken(null);
            $this->userRepository->save($user);
            $this->addFlash('success', 'User enabled!');
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId], 302);
    }

    /**
     * @param int $userId
     * @return RedirectResponse
     */
    public function forumBan(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if ($user->getForumBan()) {
            $this->addFlash('error', 'User is already forum banned');
        } else {
            $user->setForumBan(true);
            $this->userRepository->save($user);
            $this->addFlash('success', 'User forum banned!');
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId], 302);
    }

    /**
     * @param int $userId
     * @return RedirectResponse
     */
    public function forumUnban(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if (!$user->getForumBan()) {
            $this->addFlash('error', 'User is not forum banned');
        } else {
            $user->setForumBan(false);
            $this->userRepository->save($user);
            $this->addFlash('success', 'User forum unbanned!');
        }

        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId], 302);
    }

    /**
     * @return Response
     */
    public function list(): Response
    {
        return $this->render('admin/user/list.html.twig', [
            'users' => $this->userRepository->findAll()
        ]);
    }

    /**
     * @param int $userId
     * @return RedirectResponse
     */
    public function makeAdmin(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if ($user->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'User already has ROLE_ADMIN');
            return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId], 302);
        }

        $roles = $user->getRoles();
        $roles[] = 'ROLE_ADMIN';
        $user->setRoles($roles);

        $this->userRepository->save($user);
        $this->addFlash('success', 'Add ROLE_ADMIN to user!');
        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId], 302);
    }

    /**
     * @param int $userId
     * @return RedirectResponse
     */
    public function removeAdmin(int $userId): RedirectResponse
    {
        $user = $this->getUserObject($userId);
        if (!$user->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'User has no ROLE_ADMIN');
            return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId], 302);
        }

        $roles = $user->getRoles();
        foreach ($roles as $key => $role) {
            if ($role === 'ROLE_ADMIN') {
                unset($roles[$key]);
            }
        }

        $user->setRoles($roles);
        $this->userRepository->save($user);
        $this->addFlash('success', 'Removed ROLE_ADMIN from user!');
        return $this->redirectToRoute('Admin/User/Read', ['userId' => $userId], 302);
    }

    /**
     * @param int $userId
     * @return Response
     */
    public function read(int $userId): Response
    {
        return $this->render('admin/user/read.html.twig', [
            'user' => $this->userRepository->find($userId)
        ]);
    }

    /**
     * @param int $userId
     * @return User
     */
    private function getUserObject(int $userId): User
    {
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new \RuntimeException('User does not exist');
        }

        return $user;
    }
}
