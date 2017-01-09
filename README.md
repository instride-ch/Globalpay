# Globalpay
================

[![Latest Stable Version](https://poser.pugx.org/coreshop/omnipay/v/stable)](https://packagist.org/packages/coreshop/omnipay)
[![Total Downloads](https://poser.pugx.org/coreshop/omnipay/downloads)](https://packagist.org/packages/coreshop/omnipay)
[![License](https://poser.pugx.org/coreshop/omnipay/license)](https://packagist.org/packages/coreshop/omnipay)

Globalpay Plugin for Pimcore – enables access to various gateways

## Getting Started

Install with composer

```
composer require w-vision/Globalpay 1.0
```

### Description
Globalpay depends on the great Omnipay PHP Library [https://github.com/thephpleague/omnipay](https://github.com/thephpleague/omnipay). Globalpay supports following Gateways

 - Postfinance

### How to trigger a payment:

```php
$this->_helper->globalpay('Postfinance', 'donate_pay', 100, 'CHF', [
    'controller' => 'default',
    'action' => 'donate-success',
    'module' => 'default',
    'params' => [
        'TEST' => 1
    ]
], [
    'controller' => 'default',
    'action' => 'donate-cancel',
    'module' => 'default',
    'params' => [
        'TEST' => 2
    ]
], [
    'controller' => 'default',
    'action' => 'donate-error',
    'module' => 'default',
    'params' => [
        'TEST' => 3
    ]
]);
```