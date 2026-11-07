<?php
/**
 * @link https://github.com/borodulin/yii2-daterangepicker
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-daterangepicker/blob/master/LICENSE
 */

namespace conquer\daterangepicker;

use conquer\helpers\Json;
use conquer\momentjs\MomentjsAsset;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json as DataJson;
use yii\i18n\PhpMessageSource;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

/**
 * Date range input
 *
 * ```php
 * <?= $form->field($model, 'range')->widget(DaterangepickerWidget::class, [
 *     'pluginOptions' => ['maxDate' => 'js:moment()'],
 * ]) ?>
 * ```
 *
 * The value of the input is `start - end` in the date format of the Moment.js locale (`L`),
 * e.g. `01.11.2026 - 05.11.2026` for `ru`.
 *
 * @link https://www.daterangepicker.com/
 * @author Andrey Borodulin
 */
class DaterangepickerWidget extends InputWidget
{
    /**
     * Translation category of the labels
     */
    const I18N_CATEGORY = 'conquer/daterangepicker';

    /**
     * Moment.js locale of the page, e.g. `ru`. null uses `Yii::$app->language`,
     * false keeps the locale of conquer\momentjs\MomentjsAsset.
     * The labels are translated into this language.
     * @link https://momentjs.com/docs/#/i18n/
     * @var string|false|null
     */
    public $language;

    /**
     * Options of the picker. Strings with the "js:" prefix are JavaScript expressions: never put user data here.
     * @link https://www.daterangepicker.com/#options
     * @var array
     */
    public $pluginOptions = [];

    /**
     * @see Html::input()
     * @var string
     */
    public $inputType = 'text';

    /**
     * Labels; null to translate the default ones. The picker inserts them as HTML.
     * @var string|null
     */
    public $applyLabel;
    public $cancelLabel;
    public $customRangeLabel;

    /**
     * Predefined ranges: label => "js:[start, end]". null for the default ranges (today, yesterday,
     * last 7 and 30 days, this and last month), false for none. Labels are inserted as HTML.
     * @var array|false|null
     */
    public $ranges;

    /**
     * Range used when the input is empty. Strings with the "js:" prefix are JavaScript expressions.
     * Dates taken from the input value are always encoded as plain strings.
     * @var string|JsExpression
     */
    public $startDate = "js:moment().subtract(29, 'days')";
    public $endDate = 'js:moment()';

    /**
     * JavaScript callback called when a range is selected: `function (start, end, label) {...}`.
     * `{id}` is replaced with the id of the input escaped for a JavaScript string.
     * The picker writes the selected range to the input itself.
     * @var string|null
     */
    public $onSelect;

    /**
     * Converts the input value into the initial range: `function ($value) { return [$start, $end]; }`.
     * The dates are strings in the format of the picker. By default the value is split by the separator.
     * @var callable|null
     */
    public $convertValue;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->language === null) {
            $this->language = Yii::$app->language;
        }
        static::registerTranslations();
    }

    /**
     * Registers the translations of the labels, unless the application configures the category
     */
    public static function registerTranslations()
    {
        $i18n = Yii::$app->getI18n();
        if (!isset($i18n->translations[static::I18N_CATEGORY]) && !isset($i18n->translations['conquer/*'])) {
            $i18n->translations[static::I18N_CATEGORY] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => __DIR__ . '/messages',
                'fileMap' => [static::I18N_CATEGORY => 'daterangepicker.php'],
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $view = $this->getView();
        DaterangepickerAsset::register($view);
        if ($this->language) {
            MomentjsAsset::$language = $this->language;
        }

        if ($this->hasModel()) {
            $input = Html::activeInput($this->inputType, $this->model, $this->attribute, $this->options);
            $value = Html::getAttributeValue($this->model, $this->attribute);
        } else {
            $input = Html::input($this->inputType, $this->name, $this->value, $this->options);
            $value = $this->value;
        }
        $value = is_scalar($value) ? (string)$value : '';

        $pluginOptions = ArrayHelper::merge(['locale' => $this->localeSettings()], (array)$this->pluginOptions);
        if (!isset($pluginOptions['ranges']) && $this->ranges !== false) {
            $pluginOptions['ranges'] = is_array($this->ranges) ? $this->ranges : $this->defaultRanges();
        }

        $dates = [];
        if ($value !== '') {
            if (is_callable($this->convertValue)) {
                $dates = array_values((array)call_user_func($this->convertValue, $value));
            } else {
                $separator = isset($pluginOptions['locale']['separator']) ? (string)$pluginOptions['locale']['separator'] : ' - ';
                $dates = $separator === '' ? [$value] : explode($separator, $value, 2);
            }
            if (!empty($pluginOptions['singleDatePicker']) || !isset($dates[1])) {
                $dates[1] = $dates[0];
            }
        }
        // the value comes from the user: never let it be interpreted as a "js:" expression
        foreach (['startDate', 'endDate'] as $i => $option) {
            if (isset($dates[$i]) && $dates[$i] !== '') {
                $pluginOptions[$option] = new JsExpression(DataJson::htmlEncode((string)$dates[$i]));
            } elseif (!isset($pluginOptions[$option])) {
                $pluginOptions[$option] = $this->$option;
            }
        }

        // the picker writes the range to the input when it is created: keep an empty input empty until a range is applied
        $fillOnApply = !isset($pluginOptions['autoUpdateInput']) && $value === '';
        if ($fillOnApply) {
            $pluginOptions['autoUpdateInput'] = false;
        }

        $id = DataJson::htmlEncode((string)$this->options['id']);
        $js = 'jQuery(document.getElementById(' . $id . ')).daterangepicker(' . Json::encode($pluginOptions);
        if (!empty($this->onSelect)) {
            $js .= ',' . strtr($this->onSelect, ['{id}' => substr($id, 1, -1)]);
        }
        $js .= ')';
        if ($fillOnApply) {
            $js .= ".on('apply.daterangepicker', function (e, picker) {"
                . ' if (!picker.autoUpdateInput) { picker.autoUpdateInput = true; picker.updateElement(); } })';
        }
        $view->registerJs($js . ';');

        return $input;
    }

    /**
     * @return array the default ranges
     */
    public function defaultRanges()
    {
        return [
            $this->t('Today') => "js:[moment().startOf('day'), moment()]",
            $this->t('Yesterday') => "js:[moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')]",
            $this->t('Last 7 Days') => "js:[moment().subtract(6, 'days'), moment()]",
            $this->t('Last 30 Days') => "js:[moment().subtract(29, 'days'), moment()]",
            $this->t('This Month') => "js:[moment().startOf('month'), moment()]",
            $this->t('Last Month') => "js:[moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]",
        ];
    }

    /**
     * The format, the names of days and months and the first day of week are taken by the picker
     * from the Moment.js locale.
     * @return array the `locale` option
     */
    public function localeSettings()
    {
        return [
            'applyLabel' => $this->applyLabel !== null ? $this->applyLabel : $this->t('Apply'),
            'cancelLabel' => $this->cancelLabel !== null ? $this->cancelLabel : $this->t('Cancel'),
            'customRangeLabel' => $this->customRangeLabel !== null ? $this->customRangeLabel : $this->t('Custom Range'),
        ];
    }

    /**
     * @param string $message
     * @return string the message translated into the language of the widget
     */
    protected function t($message)
    {
        return Yii::t(static::I18N_CATEGORY, $message, [], $this->language ? (string)$this->language : Yii::$app->language);
    }
}
