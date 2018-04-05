<?php

namespace FrankProjects\UltimateWarfare\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name',
                'translation_domain' => 'contact'
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.email',
                'translation_domain' => 'contact'
            ])
            ->add('message', TextareaType::class, [
                'label' => 'label.message',
                'translation_domain' => 'contact'
            ]);
    }
}
