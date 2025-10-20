<?php


declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Game;

use FrankProjects\UltimateWarfare\Entity\GameResource;
use FrankProjects\UltimateWarfare\Entity\MarketItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<null> */
class MarketOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $gameResources = [];
        foreach(GameResource::getTradableGameResources() as $gameResource) {
            $gameResources['label.' . $gameResource] = $gameResource;
        }

        $builder
            ->add('option', ChoiceType::class, [
                'choices'  => [
                    'label.buy' => MarketItem::TYPE_BUY,
                    'label.sell' => MarketItem::TYPE_SELL,
                ],
                'label' => 'label.whatDoYouWantToDo',
            ])
            ->add('resource', ChoiceType::class, [
                'choices' => $gameResources,
                'label' => 'label.resource',
            ])
            ->add(
                'price',
                IntegerType::class,
                [
                    'required' => true,
                    'label' => 'label.price',
                ]
            )
            ->add(
                'amount',
                IntegerType::class,
                [
                    'required' => true,
                    'label' => 'label.amount',
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'label.placeOrder'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'market'
            )
        );
    }
}
