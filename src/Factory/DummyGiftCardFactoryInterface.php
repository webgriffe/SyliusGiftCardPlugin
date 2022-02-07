<?php

declare(strict_types=1);

namespace Setono\SyliusGiftCardPlugin\Factory;

use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface DummyGiftCardFactoryInterface extends FactoryInterface
{
    public function createNew(): GiftCardInterface;
}
