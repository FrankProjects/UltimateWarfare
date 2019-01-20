<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class ContactController extends AbstractController
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * ContactController constructor
     *
     * @param ContactRepository $contactRepository
     */
    public function __construct(
        ContactRepository $contactRepository
    ) {
        $this->contactRepository = $contactRepository;
    }

    /**
     * @return Response
     */
    public function list(): Response
    {
        return $this->render('admin/contact/list.html.twig', [
            'contacts' => $this->contactRepository->findAll()
        ]);
    }

    /**
     * @param int $contactId
     * @return Response
     */
    public function read(int $contactId): Response
    {
        return $this->render('admin/contact/read.html.twig', [
            'contact' => $this->contactRepository->find($contactId)
        ]);
    }

    /**
     * @param int $contactId
     * @return RedirectResponse
     */
    public function remove(int $contactId): RedirectResponse
    {
        $contact = $this->contactRepository->find($contactId);
        if ($contact === null) {
            $this->addFlash('error', 'Contact does not exist');
            return $this->redirectToRoute('Admin/Contact/List', [], 302);
        }

        $this->contactRepository->remove($contact);
        $this->addFlash('success', 'Contact removed!');
        return $this->redirectToRoute('Admin/Contact/List', [], 302);
    }
}
