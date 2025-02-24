# Sylius Gift Card Plugin

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]

Add gift card functionality to your store:

- Buy gift cards
- Use gift cards to purchase products
- See the balance of a gift card by looking up the gift card code

The administrator will have access to a dashboard showing the total outstanding balance of gift cards which
can be used for auditing.

## Screenshots

▶▶ [Skip screenshots and go to installation](#Installation)

![Screenshot showing admin menu and index](docs/images/admin-menu.png)

![Screenshot showing gift card admin create page](docs/images/admin-gift-card-create.png)

## Installation

### Require plugin with composer:

```bash
$ composer require setono/sylius-gift-card-plugin
```

### Import configuration:

```yaml
# config/packages/setono_sylius_gift_card.yaml
imports:
    # ...
    - { resource: "@SetonoSyliusGiftCardPlugin/Resources/config/app/config.yaml" }
```

### (Optional) Import fixtures 

If you wish to have some gift cards to play with in your application during development.

```yaml
# config/packages/setono_sylius_gift_card.yaml
imports:
    # ...
    - { resource: "@SetonoSyliusGiftCardPlugin/Resources/config/app/fixtures.yaml" }
```

### Import routing:
   
```yaml
# config/routes.yaml
setono_sylius_gift_card:
    resource: "@SetonoSyliusGiftCardPlugin/Resources/config/routes.yaml"
```

or if your app doesn't use locales:
   
```yaml
# config/routes.yaml
setono_sylius_gift_card:
    resource: "@SetonoSyliusGiftCardPlugin/Resources/config/routes_no_locale.yaml"
```

### Add plugin class to your `bundles.php`:

Make sure you add it before `SyliusGridBundle`, otherwise you'll get
`You have requested a non-existent parameter "setono_sylius_gift_card.model.gift_card.class".` exception.

```php
<?php
$bundles = [
    // ...
    Setono\SyliusGiftCardPlugin\SetonoSyliusGiftCardPlugin::class => ['all' => true],
    Sylius\Bundle\GridBundle\SyliusGridBundle::class => ['all' => true],
    // ...
];
```

### Copy templates

You will find the templates you need to override in the [test application](https://github.com/Setono/SyliusGiftCardPlugin/tree/master/tests/Application/templates).

### Extend `Product`, `Order`, `OrderRepository`, and `CustomerRepository`

**Extend `Product`**
```php
<?php

# src/Entity/Product/Product.php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusGiftCardPlugin\Model\ProductInterface as SetonoSyliusGiftCardProductInterface;
use Setono\SyliusGiftCardPlugin\Model\ProductTrait as SetonoSyliusGiftCardProductTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct implements SetonoSyliusGiftCardProductInterface
{
    use SetonoSyliusGiftCardProductTrait;
}
```

**Extend `Order`**

```php
<?php

# src/Entity/Order/Order.php

declare(strict_types=1);

namespace App\Entity\Order;

use Setono\SyliusGiftCardPlugin\Model\OrderInterface as SetonoSyliusGiftCardPluginOrderInterface;
use Setono\SyliusGiftCardPlugin\Model\OrderTrait as SetonoSyliusGiftCardPluginOrderTrait;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order")
 */
class Order extends BaseOrder implements SetonoSyliusGiftCardPluginOrderInterface
{
    use SetonoSyliusGiftCardPluginOrderTrait {
        SetonoSyliusGiftCardPluginOrderTrait::__construct as private __giftCardTraitConstruct;
    }
    
    public function __construct()
    {
        $this->__giftCardTraitConstruct();

        parent::__construct();
    }
}
```

**Extend `OrderItemUnit`**

```php
<?php

# src/Entity/Order/OrderItemUnit.php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusGiftCardPlugin\Model\OrderItemUnitInterface as SetonoSyliusGiftCardOrderItemUnitInterface;
use Setono\SyliusGiftCardPlugin\Model\OrderItemUnitTrait as SetonoSyliusGiftCardOrderItemUnitTrait;
use Sylius\Component\Core\Model\OrderItemUnit as BaseOrderItemUnit;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order_item_unit")
 */
class OrderItemUnit extends BaseOrderItemUnit implements SetonoSyliusGiftCardOrderItemUnitInterface
{
    use SetonoSyliusGiftCardOrderItemUnitTrait;
}
```
    
**Extend `OrderRepository`:**

```php
<?php

# src/Doctrine/ORM/OrderRepository.php

declare(strict_types=1);

namespace App\Doctrine\ORM;

use Setono\SyliusGiftCardPlugin\Repository\OrderRepositoryInterface as SetonoSyliusGiftCardPluginOrderRepositoryInterface;
use Setono\SyliusGiftCardPlugin\Doctrine\ORM\OrderRepositoryTrait as SetonoSyliusGiftCardPluginOrderRepositoryTrait;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository as BaseOrderRepository;

class OrderRepository extends BaseOrderRepository implements SetonoSyliusGiftCardPluginOrderRepositoryInterface
{
    use SetonoSyliusGiftCardPluginOrderRepositoryTrait;
}
```

**Extend `CustomerRepository`:**

```php
<?php

# src/Doctrine/ORM/CustomerRepository.php

declare(strict_types=1);

namespace App\Doctrine\ORM;

use Setono\SyliusGiftCardPlugin\Repository\CustomerRepositoryInterface as SetonoSyliusGiftCardPluginCustomerRepositoryInterface;
use Setono\SyliusGiftCardPlugin\Doctrine\ORM\CustomerRepositoryTrait as SetonoSyliusGiftCardPluginCustomerRepositoryTrait;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository as BaseCustomerRepository;

class CustomerRepository extends BaseCustomerRepository implements SetonoSyliusGiftCardPluginCustomerRepositoryInterface
{
    use SetonoSyliusGiftCardPluginCustomerRepositoryTrait;
}
```

- Add configuration:

```yaml
# config/services.yaml
sylius_customer:
    resources:
        customer:
            classes:
                repository: App\Doctrine\ORM\CustomerRepository

sylius_order:
    resources:
        order:
            classes:
                model: App\Entity\Order\Order
                repository: App\Doctrine\ORM\OrderRepository
        order_item_unit:
            classes:
                model: App\Entity\Order\OrderItemUnit
                
sylius_product:
    resources:
        product:
            classes:
                model: App\Entity\Product\Product
```

### Update your database:

```bash
$ bin/console doctrine:migrations:diff
$ bin/console doctrine:migrations:migrate
```

### Install assets:

```bash
$ php bin/console assets:install
```

### Clear cache:

```bash
$ php bin/console cache:clear
```

# Configuration

## Change redirect routes on add/remove gift card to/from order

You can customize where you will be redirected after adding or removing a gift card. To do so, you can simply change the route configuration :

```yaml
setono_sylius_gift_card_shop_remove_gift_card_from_order:
    path: /gift-card/{giftCard}/remove-from-order
    methods: GET
    defaults:
        _controller: setono_sylius_gift_card.controller.action.remove_gift_card_from_order
        redirect:
            route: sylius_shop_cart_summary
            parameters: []
```

The same applies for the `setono_sylius_gift_card_shop_partial_add_gift_card_to_order` route

You can also override or decorate the service `setono_sylius_gift_card.resolver.redirect_url` to define a more custom way of redirecting

# Development

## Testing

```bash
$ composer tests
```

## Playing

To run built-in application showing plugin at work, just run:  

```bash
$ composer try
```

## Contribution

Learn more about our contribution workflow on http://docs.sylius.org/en/latest/contributing/.

Please, run `composer all` to run all checks and tests before making pull request.

[ico-version]: https://img.shields.io/packagist/v/setono/sylius-gift-card-plugin.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-github-actions]: https://github.com/Setono/SyliusGiftCardPlugin/workflows/build/badge.svg

[link-packagist]: https://packagist.org/packages/setono/sylius-gift-card-plugin
[link-github-actions]: https://github.com/Setono/SyliusGiftCardPlugin/actions

# Known issues

## 0.11.x symfony/security package

There is a difference in dependencies requirement between Symfony 4 and 5.
Symfony 4 requires `symfony/security` whereas Symfony 5 requires `symfony/security-core`

So for v0.11.x of this package, the dependency has been moved to dev dependency in order to allow installation. But those
packages are installed anyway in Sylius and the plugin will work the same.
