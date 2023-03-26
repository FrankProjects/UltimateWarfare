<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Entity\Contact;
use FrankProjects\UltimateWarfare\Form\ContactType;
use FrankProjects\UltimateWarfare\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContactController extends AbstractController
{
    private ContactRepository $contactRepository;

    public function __construct(
        ContactRepository $contactRepository
    ) {
        $this->contactRepository = $contactRepository;
    }

    public function contact(Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->contactRepository->save($contact);
            $this->addFlash('success', 'Thank you for contacting us!');
        }

        return $this->render(
            'site/contact.html.twig',
            [
                'form' => $form->createView(),
                'gg_recaptcha_site_key' => $this->getParameter('app.gg_recaptcha_site_key')
            ]
        );
    }
}
