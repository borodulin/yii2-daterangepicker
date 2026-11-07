<?php
/**
 * @link https://github.com/borodulin/yii2-daterangepicker
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-daterangepicker/blob/master/LICENSE
 */

namespace conquer\daterangepicker;

use yii\web\AssetBundle;

/**
 * Date Range Picker 3
 *
 * The picker has its own styles and does not need Bootstrap.
 *
 * @link https://www.daterangepicker.com/
 * @author Andrey Borodulin
 */
class DaterangepickerAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/bootstrap-daterangepicker';

    /**
     * @inheritdoc
     */
    public $css = ['daterangepicker.css'];

    /**
     * @inheritdoc
     */
    public $js = ['daterangepicker.js'];

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => ['daterangepicker.js', 'daterangepicker.css'],
        'except' => ['/example/', '/website/'],
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
        'conquer\momentjs\MomentjsAsset',
    ];
}
