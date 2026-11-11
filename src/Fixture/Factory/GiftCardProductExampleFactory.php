<?php

declare(strict_types=1);

namespace Setono\SyliusGiftCardPlugin\Fixture\Factory;

use Setono\SyliusGiftCardPlugin\Model\ProductInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ProductExampleFactory;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

class GiftCardProductExampleFactory extends ProductExampleFactory
{
    public function create(array $options = []): ProductInterface
    {
        $resolvedOptions = $this->optionsResolver->resolve($options);

        $product = parent::create($options);
        Assert::isInstanceOf($product, ProductInterface::class);

        $product->setGiftCard(true);
        $product->setGiftCardAmountConfigurable($resolvedOptions['amount_configurable']);

        /** @var ProductVariantInterface $variant */
        foreach ($product->getVariants() as $variant) {
            $price = $this->getVariantPrice($variant, $resolvedOptions);
            foreach ($variant->getChannelPricings() as $channelPricing) {
                $channelPricing->setPrice($price);
            }
        }

        return $product;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('shipping_required', false)

            ->setDefault('amount_configurable', false)
            ->setAllowedTypes('amount_configurable', 'bool')

            ->setDefault('amount', null)
            ->setAllowedTypes('amount', ['null', 'float', 'int'])
            ->setNormalizer('amount', static fn (Options $options, float|int|null $amount): ?int => null === $amount ? null : self::toMinorUnits($amount))

            ->setDefault('amounts', [])
            ->setAllowedTypes('amounts', 'array')
            ->setNormalizer('amounts', static fn (Options $options, array $amounts): array => array_map(self::toMinorUnits(...), $amounts))
        ;
    }

    private function getVariantPrice(ProductVariantInterface $variant, array $options): int
    {
        $optionValue = $variant->getOptionValues()->first();
        if (false === $optionValue) {
            Assert::integer($options['amount'], sprintf('The gift card product "%s" has no options, so it needs the "amount" option', (string) $variant->getProduct()?->getCode()));

            return $options['amount'];
        }

        $optionValueCode = (string) $optionValue->getCode();
        Assert::keyExists($options['amounts'], $optionValueCode, sprintf('The "amounts" option has no price for the option value "%s"', $optionValueCode));

        return $options['amounts'][$optionValueCode];
    }

    private static function toMinorUnits(float|int $amount): int
    {
        return (int) round($amount * 100);
    }
}
