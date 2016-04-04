<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace mickey\commentator;

use Yii;
use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CommentatorAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'site.css',
    ];
    public $js = [

    ];
    public $depends = [
        'yii\jui\JuiAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',

    ];
//    public $jsOptions = array(
//        'position' => \yii\web\View::POS_HEAD
//    );

    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets/css';
        parent::init();
    }
}
