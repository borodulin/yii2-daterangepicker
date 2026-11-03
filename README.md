# Date Range Picker widget for Yii2 framework

[![Latest Version](https://img.shields.io/packagist/v/conquer/daterangepicker.svg)](https://packagist.org/packages/conquer/daterangepicker)
[![Total Downloads](https://img.shields.io/packagist/dt/conquer/daterangepicker.svg)](https://packagist.org/packages/conquer/daterangepicker)
[![License](https://img.shields.io/packagist/l/conquer/daterangepicker.svg)](LICENSE)

## Description

A JavaScript widget for choosing date ranges. 
Designed to work with the Bootstrap CSS framework.
For more information please visit [Date Range Picker](http://www.daterangepicker.com/) 

## Requirements

- PHP 7.4 or 8.x
- Yii 2.0
- [asset-packagist](https://asset-packagist.org) repository configured in your application's `composer.json` (standard for Yii2 app templates)

## Installation

Install the extension via [Composer](https://getcomposer.org):

```bash
composer require conquer/daterangepicker:^1.1
```

## Usage

```php
use conquer\daterangepicker\DaterangepickerWidget;

$form->field($model, 'range')->widget(
    DaterangepickerWidget::className(),
    [
        'language'=>'ru',
    ]
);
```

## License

**conquer/daterangepicker** is released under the MIT License. See the bundled [`LICENSE`](LICENSE) for details.
