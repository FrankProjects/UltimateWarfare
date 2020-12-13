<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Forum;

use FrankProjects\UltimateWarfare\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'content',
                TextareaType::class,
                [
                    'attr' => array('cols' => 70, 'rows' => 8),
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    "label" => "label.post",
                    "translation_domain" => "forum"
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => Post::class,
            )
        );
    }
}
