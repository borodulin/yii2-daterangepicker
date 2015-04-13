<?php
/**
 * @link https://github.com/borodulin/yii2-daterangepicker
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-daterangepicker/blob/master/LICENSE
 */

namespace conquer\daterangepicker;

/**
 * @author Andrey Borodulin
 */
class DaterangepickerAsset extends \yii\web\AssetBundle
{
	// The files are not web directory accessible, therefore we need
	// to specify the sourcePath property. Notice the @bower alias used.
	public $sourcePath = '@bower/bootstrap-daterangepicker';
	public $css = [
			'daterangepicker-bs3.css',
	];
	
	public $js = [
			'daterangepicker.js',
	];

	public $depends = [
		'yii\bootstrap\BootstrapPluginAsset',
		'conquer\momentjs\MomentjsAsset'
	];
}