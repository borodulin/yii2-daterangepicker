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
        $this->assertSame(1, preg_match('#jQuery\(document\.getElementById\((.+?)\)\)\.daterangepicker\((\{.*?\})(?=\)|,function)#', $html, $m), $html);
        return $m;
    }

    public function testInput()
    {
        list($widget, $html) = $this->render(['name' => 'range', 'value' => '01.11.2026 - 05.11.2026']);
        $this->assertSame('<input type="text" id="w0" name="range" value="01.11.2026 - 05.11.2026">', $widget);
        $m = $this->script($html);
        $this->assertSame('"w0"', $m[1]);
        // the format, days and months come from the Moment.js locale
        $this->assertStringStartsWith('{"locale":{"applyLabel":"Применить","cancelLabel":"Отмена","customRangeLabel":"Другой период"},"ranges":{"Сегодня":[moment().startOf(\'day\'), moment()],"Вчера":', $m[2]);
        $this->assertStringContainsString('"Прошлый месяц":[moment().subtract(1, \'month\')', $m[2]);
        $this->assertStringEndsWith('"startDate":"01.11.2026","endDate":"05.11.2026"}', $m[2]);
        // the value is not empty: the picker updates it
        $this->assertStringNotContainsString('autoUpdateInput', $html);
        $this->assertStringNotContainsString('apply.daterangepicker', $html);
        $this->assertMatchesRegularExpression('#/locale/ru\.js"#', $html);
        $this->assertStringContainsString('moment.locale("ru");', $html);
        preg_match('#href="/assets/(\w+)/daterangepicker\.css"#', $html, $a);
        $this->assertSame(['daterangepicker.css', 'daterangepicker.js'], array_values(array_diff(scandir($this->assetPath . '/' . $a[1]), ['.', '..'])));
    }

    public function testEmptyInputIsFilledOnApply()
    {
        list(, $html) = $this->render(['name' => 'range', 'language' => 'en-US']);
        $m = $this->script($html);
        $this->assertStringStartsWith('{"locale":{"applyLabel":"Apply","cancelLabel":"Cancel","customRangeLabel":"Custom Range"},"ranges":{"Today":', $m[2]);
        $this->assertStringEndsWith('"startDate":moment().subtract(29, \'days\'),"endDate":moment(),"autoUpdateInput":false}', $m[2]);
        $this->assertStringContainsString("}).on('apply.daterangepicker', function (e, picker) {", $html);
        $this->assertStringContainsString('moment.locale("en-US");', $html);
    }

    public function testModel()
    {
        $model = new DynamicModel(['range' => '2026-11-01 ~ 2026-11-05']);
        list($widget, $html) = $this->render([
            'model' => $model,
            'attribute' => 'range',
            'ranges' => false,
            'applyLabel' => 'OK',
            'pluginOptions' => ['locale' => ['format' => 'YYYY-MM-DD', 'separator' => ' ~ ', 'cancelLabel' => 'Clear'], 'autoUpdateInput' => false],
        ]);
        $this->assertSame('<input type="text" id="dynamicmodel-range" name="DynamicModel[range]" value="2026-11-01 ~ 2026-11-05">', $widget);
        $m = $this->script($html);
        $this->assertSame('{"locale":{"applyLabel":"OK","cancelLabel":"Clear","customRangeLabel":"Другой период","format":"YYYY-MM-DD","separator":" ~ "},"autoUpdateInput":false,"startDate":"2026-11-01","endDate":"2026-11-05"}', $m[2]);
        $this->assertStringNotContainsString('apply.daterangepicker', $html);
    }

    public function testSingleDatePicker()
    {
        list(, $html) = $this->render(['name' => 'date', 'value' => '01.11.2026', 'pluginOptions' => ['singleDatePicker' => true]]);
        $this->assertStringContainsString('"startDate":"01.11.2026","endDate":"01.11.2026"}', $this->script($html)[2]);
    }

    public function testLanguageFalseKeepsMomentLocale()
    {
        MomentjsAsset::$language = 'de';
        list(, $html) = $this->render(['name' => 'range', 'language' => false]);
        $this->assertStringContainsString('moment.locale("de");', $html);
        // the labels use the language of the application
        $this->assertStringContainsString('"applyLabel":"Применить"', $html);
    }

    public function testApplicationTranslationsAreKept()
    {
        Yii::$app->i18n->translations['conquer/daterangepicker'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__,
            'forceTranslation' => true,
            'on missingTranslation' => function ($event) {
                $event->translatedMessage = '[' . $event->message . ']';
            },
        ];
        list(, $html) = $this->render(['name' => 'range']);
        $this->assertStringContainsString('"applyLabel":"[Apply]"', $html);
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
            'value' => '01.11.2026 - 05.11.2026',
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
