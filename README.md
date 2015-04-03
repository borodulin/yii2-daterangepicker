Date Range Picker widget for Yii2 framework
=================

## Description

A JavaScript widget for choosing date ranges. 
Designed to work with the Bootstrap CSS framework.
For more information please visit [Date Range Picker](http://www.daterangepicker.com/) 

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/). 

To install, either run

```
$ php composer.phar require conquer/codemirror "*"
```
or add

```
"conquer/codemirror": "*"
```

to the ```require``` section of your `composer.json` file.

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

**conquer/daterangepicker** is released under the MIT License. See the bundled `LICENSE.md` for details.