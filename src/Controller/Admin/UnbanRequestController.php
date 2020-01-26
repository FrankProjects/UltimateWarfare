<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Repository\UnbanRequestRepository;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class UnbanRequestController extends AbstractController
{
    private UnbanRequestRepository $unbanRequestRepository;
    private UserRepository $userRepository;

    public function __construct(
        UnbanRequestRepository $unbanRequestRepository,
        UserRepository $userRepository
    ) {
        $this->unbanRequestRepository = $unbanRequestRepository;
        $this->userRepository = $userRepository;
    }

    public function list(): Response
    {
        return $this->render('admin/unbanRequest/list.html.twig', [
            'unbanRequests' => $this->unbanRequestRepository->findAll()
        ]);
    }

    public function remove(int $unbanRequestId): RedirectResponse
    {
        $unbanRequest = $this->unbanRequestRepository->find($unbanRequestId);
        if ($unbanRequest === null) {
            $this->addFlash('error', 'UnbanRequest does not exist');
            return $this->redirectToRoute('Admin/UnbanRequest/List', [], 302);
        }

        $this->unbanRequestRepository->remove($unbanRequest);
        $this->addFlash('success', 'UnbanRequest removed!');
        return $this->redirectToRoute('Admin/UnbanRequest/List', [], 302);
    }

    public function unban(int $unbanRequestId): RedirectResponse
    {
        $unbanRequest = $this->unbanRequestRepository->find($unbanRequestId);
        if ($unbanRequest === null) {
            $this->addFlash('error', 'UnbanRequest does not exist');
            return $this->redirectToRoute('Admin/UnbanRequest/List', [], 302);
        }

        $user = $unbanRequest->getUser();
        $user->setActive(true);

        $this->userRepository->save($user);
        $this->unbanRequestRepository->remove($unbanRequest);
        $this->addFlash('success', 'User unbanned!!');
        return $this->redirectToRoute('Admin/UnbanRequest/List', [], 302);
    }
}
