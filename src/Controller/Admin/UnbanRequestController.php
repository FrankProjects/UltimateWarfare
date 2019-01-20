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
    /**
     * @var UnbanRequestRepository
     */
    private $unbanRequestRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UnbanRequestController constructor
     *
     * @param UnbanRequestRepository $unbanRequestRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        UnbanRequestRepository $unbanRequestRepository,
        UserRepository $userRepository
    ) {
        $this->unbanRequestRepository = $unbanRequestRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @return Response
     */
    public function list(): Response
    {
        return $this->render('admin/unbanRequest/list.html.twig', [
            'unbanRequests' => $this->unbanRequestRepository->findAll()
        ]);
    }

    /**
     * @param int $unbanRequestId
     * @return RedirectResponse
     */
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

    /**
     * @param int $unbanRequestId
     * @return RedirectResponse
     */
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
