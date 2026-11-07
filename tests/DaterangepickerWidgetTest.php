<?php

namespace conquer\daterangepicker\tests;

use conquer\daterangepicker\DaterangepickerWidget;
use conquer\momentjs\MomentjsAsset;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\DynamicModel;
use yii\base\Widget;
use yii\helpers\FileHelper;
use yii\web\Application;
use yii\web\View;

class DaterangepickerWidgetTest extends TestCase
{
    private $assetPath;

    protected function setUp(): void
    {
        $this->assetPath = sys_get_temp_dir() . '/daterangepicker-test-' . uniqid();
        mkdir($this->assetPath);
        new Application([
            'id' => 'test',
            'basePath' => __DIR__,
            'language' => 'ru-RU',
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'aliases' => ['@bower' => '@vendor/bower-asset', '@npm' => '@vendor/npm-asset'],
            'components' => [
                'request' => ['cookieValidationKey' => 'test', 'scriptFile' => __DIR__ . '/index.php', 'scriptUrl' => '/index.php'],
                'assetManager' => ['basePath' => $this->assetPath, 'baseUrl' => '/assets'],
                'view' => ['class' => View::class],
            ],
        ]);
        Widget::$counter = 0;
        MomentjsAsset::$language = null;
    }

    protected function tearDown(): void
    {
        FileHelper::removeDirectory($this->assetPath);
        MomentjsAsset::$language = null;
        Yii::$app = null;
    }

    /**
     * @return array [widget html, page html]
     */
    private function render(array $config)
    {
        $widget = DaterangepickerWidget::widget($config);
        return [$widget, Yii::$app->view->renderFile(__DIR__ . '/views/page.php')];
    }

    private function script($html)
    {
        $this->assertSame(1, preg_match('#jQuery\(document\.getElementById\((.+?)\)\)\.daterangepicker\((.*)\);\n#', $html, $m), $html);
        return $m;
    }

    public function testInput()
    {
        list($widget, $html) = $this->render(['name' => 'range', 'value' => '01.11.2026 - 05.11.2026']);
        $this->assertSame('<input type="text" id="w0" name="range" value="01.11.2026 - 05.11.2026">', $widget);
        $m = $this->script($html);
        $this->assertSame('"w0"', $m[1]);
        // the picker parses and writes the value in the format of the locale
        $this->assertStringContainsString('"format":moment.localeData().longDateFormat(\'L\')', $m[2]);
        $this->assertStringContainsString('"startDate":"01.11.2026","endDate":"05.11.2026"', $m[2]);
        $this->assertStringContainsString('"firstDay":moment.localeData().firstDayOfWeek()', $m[2]);
        // no callback overwriting the value in another format
        $this->assertStringEndsWith('}', $m[2]);
        $this->assertMatchesRegularExpression('#/locale/ru\.js"#', $html);
        $this->assertStringContainsString('moment.locale("ru");', $html);
    }

    public function testModel()
    {
        $model = new DynamicModel(['range' => '11/01/2026 - 11/05/2026']);
        list($widget, $html) = $this->render(['model' => $model, 'attribute' => 'range', 'language' => false, 'pluginOptions' => ['format' => 'MM/DD/YYYY']]);
        $this->assertSame('<input type="text" id="dynamicmodel-range" name="DynamicModel[range]" value="11/01/2026 - 11/05/2026">', $widget);
        $m = $this->script($html);
        $this->assertStringStartsWith('{"format":"MM/DD/YYYY","ranges":', $m[2]);
        $this->assertStringContainsString('"startDate":"11\\/01\\/2026","endDate":"11\\/05\\/2026"', $m[2]);
    }

    public function testEmptyValueUsesDefaultDates()
    {
        list(, $html) = $this->render(['name' => 'range']);
        $m = $this->script($html);
        $this->assertStringContainsString('"startDate":moment().subtract(29, \'days\'),"endDate":moment()', $m[2]);
    }

    public function testValueIsNotJavaScript()
    {
        list($widget, $html) = $this->render(['name' => 'range', 'value' => 'js:alert(1) - </script><script>alert(2)']);
        $this->assertStringContainsString('value="js:alert(1) - &lt;/script&gt;&lt;script&gt;alert(2)"', $widget);
        $m = $this->script($html);
        $u = '\\' . 'u';
        $this->assertStringContainsString('"startDate":"js:alert(1)","endDate":"' . $u . '003C\/script' . $u . '003E' . $u . '003Cscript' . $u . '003Ealert(2)"', $m[2]);
        $this->assertStringNotContainsString('<script>alert(2)', $html);
    }

    public function testIdIsEscaped()
    {
        list($widget, $html) = $this->render([
            'name' => 'range',
            'options' => ['id' => "a').length;alert(1);jQuery('#a"],
            'onSelect' => "function () { console.log('{id}'); }",
        ]);
        $this->assertStringContainsString('id="a&#039;).length;alert(1);jQuery(&#039;#a"', $widget);
        $u = '\\' . 'u';
        $id = 'a' . $u . '0027).length;alert(1);jQuery(' . $u . '0027#a';
        $this->assertStringContainsString('jQuery(document.getElementById("' . $id . '")).daterangepicker({', $html);
        $this->assertStringContainsString("},function () { console.log('$id'); });", $html);
        $this->assertStringNotContainsString("jQuery('#", $html);
    }

    public function testConvertValueAndRanges()
    {
        list(, $html) = $this->render([
            'name' => 'range',
            'value' => '2026-11-01/2026-11-05',
            'convertValue' => function ($value) {
                return explode('/', $value);
            },
            'ranges' => ['Week' => "js:[moment().subtract(6, 'days'), moment()]"],
        ]);
        $m = $this->script($html);
        $this->assertStringContainsString('"ranges":{"Week":[moment().subtract(6, \'days\'), moment()]}', $m[2]);
        $this->assertStringContainsString('"startDate":"2026-11-01","endDate":"2026-11-05"', $m[2]);
    }
}
