# php-rollbar-extensions

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.txt)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This package provides extra strategies to be used with the Rollbar SDK for PHP. The base Rollbar SDK comes with some default strategies for truncation, but they don't always do everything that is needed. The configuration allows for specifying custom strategies for transforming, truncating, etc, and this package aims to provide some of these custom strategies.

## Install

Via Composer

``` bash
$ composer require shiftonelabs/php-rollbar-extensions
```

## Configuration

Rollbar configuration is dependent on the framework/application being used.

Rollbar documentation for installing rollbar for PHP: https://docs.rollbar.com/docs/php

Rollbar configuration options: https://docs.rollbar.com/docs/php-configuration-reference

## Usage

This package provides a set of classes that can be provided to the Rollbar configuration.
For the most part, usage of this queue driver is the same as the built in queue drivers. There are, however, a few extra things to consider when working with Amazon's SQS FIFO queues.

### Custom Data Method

> Allows creating dynamic custom data on runtime during error reporting. The callable taking two parameters `$toLog` (the context of what's being logged) and `$context` (additional context data provided in the config). You provide `$context` by adding `custom_data_method_context` key to the `$extra` or `$context` parameters `Rollbar::log` or `RollbarLogger::log`.
>
> -- <cite>[Rollbar config reference](https://docs.rollbar.com/docs/php-configuration-reference)</cite>

To specify a custom data method to use, set the `custom_data_method` key of the Rollbar config to a new instance of an invocable Custom Data Method class. These classes should implement the `\ShiftOneLabs\PhpRollbarExtensions\CustomDataInterface` interface.

**No custom data methods available yet.**

### Custom Truncation

> A fully qualified name of your custom truncation strategy class. It has to inherit from `\Rollbar\Truncation\AbstractStrategy`. This custom strategy will be applied before the built-in strategies.
>
> -- <cite>[Rollbar config reference](https://docs.rollbar.com/docs/php-configuration-reference)</cite>

To specify a custom truncation to use, set the `custom_truncation` key of the Rollbar config to the fully qualified name of the class to use.

**No custom truncation methods available yet.**

### Transformers

From Rollbar:

> The class to be used to transform the payload before it gets prepared for sending to Rollbar API. It has to implement `\Rollbar\TransformerInterface`.
>
> -- <cite>[Rollbar config reference](https://docs.rollbar.com/docs/php-configuration-reference)</cite>

To specify a transformer to use, set the `transformer` key of the Rollbar config to the fully qualified name of the class to use.

Available transformers:

| Transformer | Description |
| --- | --- |
| `AddExceptionPropertiesTransformer` | Adds all properties of the exception to the Rollbar log. |

#### AddExceptionPropertiesTransformer

When an exception is logged to Rollbar, only the basic exception information is sent to Rollbar (exception name, message, code, line number, file, previous exception, and stack trace). However, if you have created a custom exception with additional properties, the values of those properties are not sent to Rollbar by default. Sometimes this information is very valuable in attempting to debug the issue.

The `AddExceptionPropertiesTransformer` transformer will add all data stored on the exception to the "custom" element of the Rollbar payload, under a new "properties" key. This includes all defined public, protected, and private properties, as well as any properties dynamically assigned at runtime. This transformer also traverses the exception chain, so all the properties for the set "previous" exceptions will be reported, as well. This custom data will be displayed in Rollbar for each occurrence.

Example configuration: `'transformer' => \ShiftOneLabs\PhpRollbarExtensions\Transformers\AddExceptionPropertiesTransformer::class`

## Contributing

Contributions are welcome. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email patrick@shiftonelabs.com instead of using the issue tracker.

## Credits

- [Patrick Carlo-Hickman][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/shiftonelabs/php-rollbar-extensions.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/shiftonelabs/php-rollbar-extensions/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/shiftonelabs/php-rollbar-extensions.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/shiftonelabs/php-rollbar-extensions.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/shiftonelabs/php-rollbar-extensions.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/shiftonelabs/php-rollbar-extensions
[link-travis]: https://travis-ci.org/shiftonelabs/php-rollbar-extensions
[link-scrutinizer]: https://scrutinizer-ci.com/g/shiftonelabs/php-rollbar-extensions/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/shiftonelabs/php-rollbar-extensions
[link-downloads]: https://packagist.org/packages/shiftonelabs/php-rollbar-extensions
[link-author]: https://github.com/patrickcarlohickman
[link-contributors]: ../../contributors
