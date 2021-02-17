<?php

declare(strict_types=1);

namespace Setono\SyliusGiftCardPlugin\Grid\FieldTypes;

use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;

final class StringFieldType implements FieldTypeInterface
{
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function render(Field $field, $data, array $options)
    {
        try {
            $value = $this->propertyAccessor->getValue($data, $field->getPath());
        } catch (UnexpectedTypeException $e) {
            return '';
        }

        return htmlspecialchars((string) $value);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
