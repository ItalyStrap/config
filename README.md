# ItalyStrap Config API

[![Build status](https://github.com/ItalyStrap/config/actions/workflows/test.yml/badge.svg)](https://github.com/ItalyStrap/config/actions/workflows/test.yml?query=workflow%3Atest)
[![Latest Stable Version](https://img.shields.io/packagist/v/italystrap/config.svg)](https://packagist.org/packages/italystrap/config)
[![Total Downloads](https://img.shields.io/packagist/dt/italystrap/config.svg)](https://packagist.org/packages/italystrap/config)
[![Latest Unstable Version](https://img.shields.io/packagist/vpre/italystrap/config.svg)](https://packagist.org/packages/italystrap/config)
[![License](https://img.shields.io/packagist/l/italystrap/config.svg)](https://packagist.org/packages/italystrap/config)
![PHP from Packagist](https://img.shields.io/packagist/php-v/italystrap/config)

ItalyStrap Config Module - a simple and useful configuration package the OOP way

## Table Of Contents

* [Installation](#installation)
* [Basic Usage](#basic-usage)
* [Advanced Usage](#advanced-usage)
* [Contributing](#contributing)
* [License](#license)

## Installation

The best way to use this package is through Composer:

```CMD
composer require italystrap/config
```

## Basic Usage

This package is meant to be used in your application to manage configuration settings.
Values can be accessed using dot notation or array notation or a single key, and you can also set, update, delete, and check if a key exists.

Let's see a simple example:

```php
use ItalyStrap\Config\Config;

$config = new Config([
    'key' => 'value',
    'key2' => 'value2',
    'key3' => [
        'key4' => 'value4',
    ],
]);

$value = $config->get('key');

// Output: 'value'

// You can access using dot notation
$value = $config->get('key3.key4');

// Output: 'value4'

$value = $config->get('key3.key5', 'mixed default value');

// Output: 'mixed default value'

// You can also access using array notation
$value = $config->get(['key3', 'key4']);

// Output: 'value4'

// It is possible to set a value
$isSet = $config->set('key5', 'new value');

// Output: true

$value = $config->get('key5');

// Output: 'new value'

// You can also set a value using array notation or dot notation
$isSet = $config->set(['key6', 'key7'], 'new value');
// or
$isSet = $config->set('key6.key7', 'new value');

// Output: true

$value = $config->get('key6.key7');
// or
$value = $config->get(['key6', 'key7']);

// Output: 'new value'

// You can delete a value using dot notation or array notation or a single key

$isDeleted = $config->delete('key6.key7');
// or
$isDeleted = $config->delete(['key6', 'key7']);
// or
$isDeleted = $config->delete('key6');

// Output: true

// You can check if a key exists using dot notation or array notation or a single key

$exists = $config->has('key6.key7');
// or
$exists = $config->has(['key6', 'key7']);
// or
$exists = $config->has('key6');

// Output: true if exists, false otherwise

// You can update a value using dot notation or array notation or a single key
// It works in the same way as the set method

$isUpdated = $config->update('key', 'updated value');
// or
$isUpdated = $config->update(['key6', 'key7'], 'updated value');
// or
$isUpdated = $config->update('key6.key7', 'updated value');

// Output: true

// This package also has multiple methods to work with arrays like PSR-6

$config->getMultiple(['key', 'key2', 'key3.key4'], 'default');
$config->setMultiple(['key', 'key2', 'key3.key4'], 'value');
$config->deleteMultiple(['key', 'key2', 'key3.key4']);

// You can merge iterables after the Config object is created

$config->merge([
    'key' => 'value',
    'key2' => 'value2',
    'key3' => [
        'key4' => 'value4',
    ],
]);

// You can also get all the configuration settings as an array

$all = $config->toArray();

// Output: ['key' => 'value', 'key2' => 'value2', 'key3' => ['key4' => 'value4']]

// You can use the object and pass it to a \json_encode

$json = \json_encode($config);

// Output: '{"key":"value","key2":"value2","key3":{"key4":"value4"}}'

```

## Advanced Usage

You can see more advanced example in the tests' folder.

## Deprecation

List of all deprecated method that will  be removed in the next major release.

* `Config::push()` => `Config::set()`
* `Config::add()` => `Config::set()`
* `Config::remove()` => `Config::delete()`
* `Config::all()` => `Config::toArray()`
* `ConfigThemeMods::class`

## Contributing

All feedback / bug reports / pull requests are welcome.

## License

Copyright (c) 2019 Enea Overclokk, ItalyStrap

This code is licensed under the [MIT](LICENSE).

## Credits

Ideas for the Config::class from:
 - [Tonya Mork](https://github.com/wpfulcrum/config)
 - [Alain Schlesser](https://github.com/brightnucleus/config)

For the Notation Array Search:
 - https://github.com/balambasik/input/blob/master/src/Input.php

For some ideas:
 - [ChatGPT](https://chat.openai.com)
