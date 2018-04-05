<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContactController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function contact(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = new \Swift_Message('Ultimate Warfare contact form');
            $message
                ->setFrom('no-reply@ultimate-warfare.com')
                ->setTo('admin@frankprojects.com')
                ->setBody(
                    $this->renderView(
                        'email/contact.html.twig',
                        [
                            'name' => $form->getData()['name'],
                            'email' => $form->getData()['email'],
                            'message' => $form->getData()['message']
                        ]
                    ),
                    'text/html'
                );

            $this->get('mailer')->send($message);
        }

        return $this->render('site/contact.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
