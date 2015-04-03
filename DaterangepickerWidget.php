<?php
/**
 * @link https://github.com/borodulin/yii2-daterangepicker
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-daterangepicker/blob/master/LICENSE
 */
namespace conquer\daterangepicker;

use yii\helpers\Json;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;
use conquer\momentjs\MomentjsAsset;
use yii\i18n\Formatter;

class DaterangepickerWidget extends \yii\widgets\InputWidget
{
	
	/**
	 * Customize the language and localization options for the calendar.
	 * @link http://momentjs.com/docs/#/i18n/
	 * @var string
	 */
	public $language=false;
	
	/**
	 * General Date Range Picker setting
	 * @link http://www.daterangepicker.com/#options
	 * @var array()
	 */
	public $settings;
	
    /**
     * @var array the HTML attributes for the input tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
	public $options = ['class'=>'pull-right', 'style'=>"background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc"];
	
	
	public $applyLabel = 'Submit';
	public $cancelLabel = 'Cancel';
	public $fromLabel = 'From';
	public $toLabel = 'To';
	public $customRangeLabel = 'Custom';
	
	public $ranges = [];
	
	public $startDate = "js:moment().subtract(29, 'days')";
	public $endDate = "js:moment()";
	
	public $init = <<<JS
$('#{id} span').html({start}.format('MMMM D, YYYY') + ' - ' + {end}.format('MMMM D, YYYY'));
JS;
	
	public $onSelect = <<<JS
function(start, end, label) {
	$('#{start}').val(start.format('YYYY-MM-DDTHH:mm:ss'));
	$('#{end}').val(end.format('YYYY-MM-DDTHH:mm:ss'));
	$('#{id} span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
}
JS;
	
	public $template = '<i class="glyphicon glyphicon-calendar fa fa-calendar"></i><span></span><b class="caret"></b>';
	
	/**
	 * @inheritdoc
	 */
	public function run()
	{
		$view = $this->view;
		$this->registerAssets($view);
		if(!empty($this->settings))
			$settings = ArrayHelper::merge($this->localeSettings(), $this->settings);
		
		if(empty($settings['ranges']))
			$settings['ranges']=$this->defaultRanges();
		
		$settings['startDate']=$this->startDate;
		$settings['endDate']=$this->endDate;
		
		$id=$this->options['id'];
		if ($this->hasModel()) {
			$value = Html::getAttributeValue($this->model, $this->attribute);
			$name = Html::getInputName($this->model, $this->attribute);			
		} else {
			$name=$this->name;
			$value=$this->value;
		}
		$formatter=\Yii::$app->formatter;
		$settings['startDate']=isset($value['start'])?$formatter->asDatetime($value['start']):$this->startDate;
		$settings['endDate']=isset($value['end'])?$formatter->asDatetime($value['end']):$this->endDate;

		array_walk_recursive($settings, function(&$item){
			if(is_string($item) && (strpos('js:', $item)==0))
				$item = new JsExpression(substr($item,3));
		});
		if(!empty($this->init))
			$init = strtr($this->init, [
					'{id}'=>$id,
					'{start}'=>$settings['startDate'],
					'{end}'=>$settings['endDate'],
			]);
		else
			$init='';
		$settings=Json::encode($settings);
		
		if(!empty($this->onSelect))
			$settings.=','.strtr($this->onSelect, [
					'{start}'=>"{$id}_start",
					'{end}'=>"{$id}_end",
					'{id}'=>$id,
			]);
			
		$view->registerJs("$init\njQuery('#{$this->options['id']}').daterangepicker($settings);");
		echo Html::beginTag('div', $this->options);
		echo $this->template;
		
		echo Html::hiddenInput($name.'[start]', isset($value['start'])?$value['start']:null, ['id'=>"{$id}_start"]);
		echo Html::hiddenInput($name.'[end]', isset($value['end'])?$value['end']:null, ['id'=>"{$id}_end"]);
		
		echo Html::endTag('div');
	}

	
	public function registerAssets($view)
	{
		DaterangepickerAsset::register($view);
		if($this->language){
			MomentjsAsset::$language=$this->language;
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
			'format' => MomentjsAsset::fromPhpFormat(\Yii::$app->formatter->dateFormat),
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