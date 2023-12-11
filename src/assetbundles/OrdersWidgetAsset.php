<?php
namespace verbb\snipcart\assetbundles;

use yii\web\JqueryAsset;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Working Concept
 * @package   Snipcart
 * @since     1.0.0
 */
class OrdersWidgetAsset extends \craft\web\AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@verbb/snipcart/assetbundles/dist';
        $this->depends = [
            SnipcartAsset::class,
            ChartAsset::class,
            CpAsset::class,
            JqueryAsset::class
        ];
        $this->js = ['js/OrdersWidget.js'];
        $this->css = [];

        parent::init();
    }
}
