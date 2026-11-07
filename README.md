# Date Range Picker widget for Yii2 framework

[![Latest Version](https://img.shields.io/packagist/v/conquer/daterangepicker.svg)](https://packagist.org/packages/conquer/daterangepicker)
[![Total Downloads](https://img.shields.io/packagist/dt/conquer/daterangepicker.svg)](https://packagist.org/packages/conquer/daterangepicker)
[![CI](https://github.com/borodulin/yii2-daterangepicker/actions/workflows/ci.yml/badge.svg)](https://github.com/borodulin/yii2-daterangepicker/actions/workflows/ci.yml)
[![License](https://img.shields.io/packagist/l/conquer/daterangepicker.svg)](LICENSE)

## Description

An input for choosing date ranges with [Date Range Picker](https://www.daterangepicker.com/) 3.1
and [Moment.js](https://momentjs.com/). The picker has its own styles and does not need Bootstrap.

| Version | Date Range Picker | Moment.js | PHP |
|---|---|---|---|
| 2.x | 3.1 | 2.9 or later | 7.4, 8.x |
| [1.x](https://github.com/borodulin/yii2-daterangepicker/tree/1.x) | 1.3 | 2.9 only | 7.4, 8.x |

## Requirements

- PHP 7.4 or 8.x
- Yii 2.0.14 or later
- [asset-packagist](https://asset-packagist.org) repository configured in your application's `composer.json` (standard for Yii2 app templates)

## Installation

```bash
composer require conquer/daterangepicker:^2.0
```

## Usage

```php
use conquer\daterangepicker\DaterangepickerWidget;

<?= $form->field($model, 'range')->widget(DaterangepickerWidget::class, [
    'pluginOptions' => ['maxDate' => 'js:moment()'],
]) ?>

<?= DaterangepickerWidget::widget(['name' => 'range', 'language' => 'ru']) ?>
```

The value of the input is `start - end` in the date format of the Moment.js locale (`L`):
`01.11.2026 - 05.11.2026` for `ru`, `11/01/2026 - 11/05/2026` for `en`. The picker reads the value
and writes the selected range in this format; set `pluginOptions['locale']['format']` and `['separator']`
to use others, or `convertValue` to parse a value of another format. An empty input stays empty
until a range is applied, so the widget can be used in filters.

| Property | Default | Description |
|---|---|---|
| `language` | `Yii::$app->language` | Moment.js locale of the page and language of the labels; `false` keeps the locale of `conquer\momentjs\MomentjsAsset` |
| `pluginOptions` | `[]` | [Options](https://www.daterangepicker.com/#options) of the picker |
| `ranges` | today, yesterday, last 7 and 30 days, this and last month | `label => "js:[start, end]"`, `false` for none |
| `startDate`, `endDate` | last 30 days | Range of an empty input |
| `applyLabel`, `cancelLabel`, `customRangeLabel` | translated | Labels |
| `onSelect` | `null` | JavaScript callback `function (start, end, label) {}`; `{id}` is replaced with the input id |
| `convertValue` | `null` | `function ($value) { return [$start, $end]; }` |
| `inputType` | `'text'` | Type of the input |

Names of days and months and the first day of week come from the Moment.js locale.

Strings with the `js:` prefix are JavaScript expressions in the widget configuration (`pluginOptions`,
`ranges`, `startDate`, `endDate`): never put user data there. Dates taken from the input value are always
encoded as plain strings. The picker inserts labels and range names as HTML.

### Translations

The labels are translated in the category `conquer/daterangepicker`. Russian is included; to add a language
or change the texts, configure the category in your application:

```php
'components' => [
    'i18n' => [
        'translations' => [
            'conquer/daterangepicker' => [
                'class' => yii\i18n\PhpMessageSource::class,
                'basePath' => '@app/messages',
                'fileMap' => ['conquer/daterangepicker' => 'daterangepicker.php'],
            ],
        ],
    ],
],
```

## Upgrading from 1.x

- Date Range Picker 3.1 instead of 1.3: see its [options](https://www.daterangepicker.com/#options).
  `format` is `locale.format`; the picker has no "from" and "to" inputs, so `fromLabel` and `toLabel` are removed.
  Moment.js is no longer limited to 2.9.
- The picker uses `daterangepicker.css` and no longer needs Bootstrap.
- An empty input stays empty until a range is applied (Date Range Picker 3 fills it on creation).
- The labels are translated, the default apply label is "Apply" instead of "Submit".
- `run()` returns the input instead of echoing it.

## License

**conquer/daterangepicker** is released under the MIT License. See the bundled [`LICENSE`](LICENSE) for details.
Date Range Picker is released under the MIT License.
