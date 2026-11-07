# Date Range Picker widget for Yii2 framework

[![Latest Version](https://img.shields.io/packagist/v/conquer/daterangepicker.svg)](https://packagist.org/packages/conquer/daterangepicker)
[![Total Downloads](https://img.shields.io/packagist/dt/conquer/daterangepicker.svg)](https://packagist.org/packages/conquer/daterangepicker)
[![CI](https://github.com/borodulin/yii2-daterangepicker/actions/workflows/ci.yml/badge.svg?branch=1.x)](https://github.com/borodulin/yii2-daterangepicker/actions/workflows/ci.yml)
[![License](https://img.shields.io/packagist/l/conquer/daterangepicker.svg)](LICENSE)

## Description

A JavaScript widget for choosing date ranges.
Designed to work with the Bootstrap CSS framework: include Bootstrap CSS in your layout
(the widget no longer depends on `yiisoft/yii2-bootstrap`).
For more information please visit [Date Range Picker](http://www.daterangepicker.com/) 

## Requirements

- PHP 7.4 or 8.x
- Yii 2.0
- [asset-packagist](https://asset-packagist.org) repository configured in your application's `composer.json` (standard for Yii2 app templates)

## Installation

Install the extension via [Composer](https://getcomposer.org):

```bash
composer require conquer/daterangepicker:^1.2
```

## Usage

```php
use conquer\daterangepicker\DaterangepickerWidget;

$form->field($model, 'range')->widget(
    DaterangepickerWidget::class,
    [
        'language'=>'ru',
    ]
);
```

This is the 1.x branch (Date Range Picker 1.3, Moment.js 2.9). Version 2 uses Date Range Picker 3.1
and any Moment.js 2.x: see the master branch.

The input value (`start - end`) is used as the initial range; use `convertValue` to parse other formats.
Dates are parsed and written in the date format of the Moment.js locale (`L`: `DD.MM.YYYY` for `ru`,
`MM/DD/YYYY` for `en`); set `pluginOptions['format']` to use another one.
Dates taken from the value are always encoded as plain strings. Strings with the `js:` prefix are JavaScript
expressions only in the widget configuration (`pluginOptions`, `ranges`, `startDate`, `endDate`):
never put user data there.

## License

**conquer/daterangepicker** is released under the MIT License. See the bundled [`LICENSE`](LICENSE) for details.
