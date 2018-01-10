![Globalpay](docs/images/github_banner.png "Globalpay")

[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Stable Version](https://img.shields.io/packagist/v/w-vision/globalpay.svg?style=flat-square)](https://packagist.org/packages/w-vision/globalpay)

Globalpay Plugin for Pimcore – enables access to various gateways

## Getting Started

Install with composer

```
composer require w-vision/Globalpay 1.0.0
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

### Configuring Custom Routes
```
 Name: donate_pay
 Pattern: /(\w+)\/payment\/(.*)\/(\w+)/
 Reverse: /%lang/payment/%gateway/%act
 Module: Globalpay
 Controller: payment
 Action: %act
 Variables: lang,gateway,act
 ```
 
 

