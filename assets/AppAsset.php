<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css', // Keep site.css for potential base styles
        // 'css/site-custom.css', // Remove as it was likely BS5 specific
    ];
    public $js = [
        'js/main.js', // Added main.js
    ];
    public $depends = [
        'yii\web\YiiAsset', // Provides jQuery
        // 'yii\bootstrap5\BootstrapAsset', // Removed to use SB Admin 2's Bootstrap 4
    ];
}
