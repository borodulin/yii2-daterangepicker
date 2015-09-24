<?php
/**
 * @link https://github.com/borodulin/yii2-daterangepicker
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-daterangepicker/blob/master/LICENSE
 */

namespace conquer\daterangepicker;

use yii\helpers\Html;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;
use yii\i18n\Formatter;
use conquer\momentjs\MomentjsAsset;
use conquer\helpers\Json;

/**
 * @author Andrey Borodulin
 */
class DaterangepickerWidget extends \yii\widgets\InputWidget
{
    
    /**
     * Customize the language and localization options for the calendar.
     * @link http://momentjs.com/docs/#/i18n/
     * @var string
     */
    public $language;
    
    /**
     * General Date Range Picker setting
     * @link http://www.daterangepicker.com/#options
     * @var array()
     */
    public $pluginOptions;
    
    /**
     * @see Html::input()
     * @var string
     */
    public $inputType = 'text';
    
    public $applyLabel = 'Submit';
    public $cancelLabel = 'Cancel';
    public $fromLabel = 'From';
    public $toLabel = 'To';
    public $customRangeLabel = 'Custom';
    
    public $ranges;
    
    public $startDate = "js:moment().subtract(29, 'days')";
    public $endDate = "js:moment()";

    
    public $onSelect = <<<JS
function(start, end, label) {
    $('#{id}').val(start.format('L') + ' - ' + end.format('L'));
}
JS;
    /**
     * Returns array of 2 elements. [0] - startDate, [1] - endDate
     * @var callable
     */
    public $convertValue;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (is_null($this->language)) {
            $this->language = \Yii::$app->language;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        $view = $this->view;
        $this->registerAssets($view);
        if ($this->language) {
            $pluginOptions = ArrayHelper::merge($this->localeSettings(), $this->pluginOptions);
        }
        
        if (is_array($this->ranges)) {
            $pluginOptions['ranges'] = $this->ranges;
        }
        
        if (!isset($pluginOptions['ranges']) ) {
            $pluginOptions['ranges'] = $this->defaultRanges();
        }

        
        if ($this->hasModel()) {
            echo Html::activeInput($this->inputType, $this->model, $this->attribute, $this->options);
            $value = Html::getAttributeValue($this->model, $this->attribute);
        } else {
            echo Html::input($this->inputType, $this->model, $this->attribute, $this->options);
            $value = $this->value;
        }
        if ($value) {
            if (is_callable($this->convertValue)) {
                $dates = call_user_func($this->convertValue, $value);
            } else {
                $dates = explode(' - ', $value);
            }
        } 
        $pluginOptions['startDate'] = isset($dates[0]) ? $dates[0] : $this->startDate;
        $pluginOptions['endDate'] = isset($dates[1]) ? $dates[1] : $this->endDate;

        $pluginOptions = Json::encode($pluginOptions);
        
        $id = $this->options['id'];
        if (!empty($this->onSelect)) {
            $pluginOptions .= ','.strtr($this->onSelect, [
                    '{id}' => $id,
            ]);
        }
        $view->registerJs("jQuery('#$id').daterangepicker($pluginOptions);");
    }

    
    public function registerAssets($view)
    {
        DaterangepickerAsset::register($view);
        if ($this->language) {
            MomentjsAsset::$language = $this->language;
        }
    }
    
    public function defaultRanges()
    {
        return [
            'Today'=> "js:[moment().startOf('day'), moment()]",
            'Yesterday'=> "js:[moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')]",
            'Last 7 Days'=> "js:[moment().subtract(6, 'days'), moment()]",
            'Last 30 Days'=> "js:[moment().subtract(29, 'days'), moment()]",
            'This Month'=> "js:[moment().startOf('month'), moment()]",
            'Last Month'=> "js:[moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]",
        ];
    }
    
    public function localeSettings()
    {
        return [
            'locale' => [
                'applyLabel' => $this->applyLabel,
                'cancelLabel' => $this->cancelLabel,
                'fromLabel' => $this->fromLabel,
                'toLabel' => $this->toLabel,
                'customRangeLabel' => $this->customRangeLabel,
                'daysOfWeek' => 'js:moment.weekdaysMin()',
                'monthNames' => 'js:moment.months()',
                'firstDay' => 'js:moment.localeData().firstDayOfWeek()',
            ],
        ];
    }
}