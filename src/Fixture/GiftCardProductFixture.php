<?php

declare(strict_types=1);

namespace Setono\SyliusGiftCardPlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\ProductFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class GiftCardProductFixture extends ProductFixture
{
    public function getName(): string
    {
        return 'setono_gift_card_product';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        parent::configureResourceNode($resourceNode);

        $resourceNode
            ->children()
                ->booleanNode('amount_configurable')->end()
                ->floatNode('amount')->end()
                ->arrayNode('amounts')->floatPrototype()->end()->end()
        ;
    }
}
