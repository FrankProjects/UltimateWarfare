<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form;

use FrankProjects\UltimateWarfare\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/** @extends AbstractType<null> */
class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'label.name',
                    'constraints' => [
                        new Length(min: 1)
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'label' => 'label.email',
                    'constraints' => [
                        new NotBlank(),
                        new Email()
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'subject',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'label.subject',
                    'constraints' => [
                        new Length(min: 5)
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'message',
                TextareaType::class,
                [
                    'required' => true,
                    'label' => 'label.message',
                    'constraints' => [
                        new Length(min: 10)
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'captcha',
                ReCaptchaType::class,
                [
                    'mapped' => false,
                    'type' => 'checkbox' // (invisible, checkbox)
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'label.send'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Contact::class,
                'translation_domain' => 'contact'
            ]
        );
    }
}
