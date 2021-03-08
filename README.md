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

```php
use ItalyStrap\Config\Config;

$config = new Config( $configObjOrArrayOptional, $configDefaultOptional );

$value = $config->get( 'key', $optionalDefaultValue ); 

```

## Advanced Usage

You can see more advanced example in the tests folder.

## Contributing

All feedback / bug reports / pull requests are welcome.

## License

Copyright (c) 2019 Enea Overclokk, ItalyStrap

This code is licensed under the [MIT](LICENSE).

## Credits

Ideas from:
 - [Tonya Mork](https://github.com/wpfulcrum/config)
 - [Alain Schlesser](https://github.com/brightnucleus/config)
