<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Forum;

use FrankProjects\UltimateWarfare\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
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
                'data_class' => Category::class,
            )
        );
    }
}
