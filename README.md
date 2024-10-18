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

## Documentation

* [Overview](docs/01_config.md)
* [Traversing Data](docs/02_traversing-data.md)

## Deprecation

List of all deprecated method that will  be removed in the next major release.

* `Config::push()` => `Config::set()`
* `Config::add()` => `Config::set()`
* `Config::remove()` => `Config::delete()`
* `Config::all()` => `Config::toArray()`
* `Config::toJson()` => (string)\json_encode(new Config(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
* `ConfigThemeMods::class` => No replacement class is provided
* `ConfigThemeModTest::class` => No replacement class is provided
* `Config_Factory::class` => `ConfigFactory::class`
* `Config_Interface::class` => `ConfigInterface::class`
* Move `\JsonSerializable` at the `ConfigInterface::class` level

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

For the `traverse` method:
 - [Nikita Popov](https://github.com/nikic/PHP-Parser)

For some ideas:
 - [ChatGPT](https://chat.openai.com)
