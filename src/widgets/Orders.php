<?php
/**
 * Snipcart plugin for Craft CMS 3.x
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2018 Working Concept Inc.
 */

namespace workingconcept\snipcart\widgets;

use workingconcept\snipcart\assetbundles\OrdersWidgetAsset;
use workingconcept\snipcart\Snipcart;
use Craft;
use craft\base\Widget;

/**
 * Orders Widget
 */
class Orders extends Widget
{

    // Properties
    // =========================================================================

    /**
     * @var string Type of order data to be displayed.
     */
    public $chartType;

    /**
     * @var string Range of time for which data should be summarized.
     */
    public $chartRange;


    // Static Methods
    // =========================================================================

    /**
     * Disallow multiple widget instances.
     *
     * @return bool
     */
    protected static function allowMultipleInstances(): bool
    {
        return false;
    }


    // Public Methods
    // =========================================================================

    /**
     * Returns the translated widget display name.
     *
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('snipcart', 'Snipcart Orders');
    }

    /**
     * Returns the widget's icon path.
     *
     * @return string
     */
    public static function iconPath(): string
    {
        return Craft::getAlias('@workingconcept/snipcart/assetbundles/dist/img/orders-icon.svg');
    }

    /**
     * Sets the maximum column span to 1.
     *
     * @return int
     */
    public static function maxColspan(): int
    {
        return 3;
    }

    /**
     * Returns the translated widget title.
     *
     * @return string
     */
    public function getTitle(): string {
        return Craft::t('snipcart', 'Snipcart Orders');
    }

   /**
    * @inheritdoc
    */
   public function rules(): array
   {
       $rules = parent::rules();

       $rules[] = [['chartType', 'chartRange'], 'required'];
       $rules[] = [['chartType', 'chartRange'], 'string'];
       $rules[] = [['chartType'], 'default', 'value' => 'itemsSold'];
       $rules[] = [['chartRange'], 'default', 'value' => 'weekly'];

       $rules[] = [['chartType'], 'in', 'range' => array_keys($this->getChartTypeOptions())];
       $rules[] = [['chartRange'], 'in', 'range' => array_keys($this->getChartRangePeriodOptions())];

       return $rules;
   }

    /**
     * Returns the widget body HTML.
     *
     * @return false|string
     * @throws \RuntimeException
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getBodyHtml()
    {
        Craft::$app->getView()->registerAssetBundle(OrdersWidgetAsset::class);

        return Craft::$app->getView()->renderTemplate(
            'snipcart/widgets/orders/orders',
            [
                'widget' => $this,
                'settings' => Snipcart::$plugin->getSettings()
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('snipcart/widgets/orders/settings',
            [
                'widget' => $this
            ]
        );
    }

    /**
     * Get a key-value array representing options for the type of data to be charted.
     *
     * @return array
     */
    public function getChartTypeOptions(): array
    {
        return [
            'itemsSold'      => 'Items Sold',
            'totalSales'     => 'Total Sales',
            'numberOfOrders' => 'Number of Orders',
        ];
    }

    /**
     * Get a key-value array representing options for the chart's time range.
     *
     * @return array
     */
    public function getChartRangeOptions(): array
    {
        return [
            'weekly' => 'Weekly',
        ];
    }

}
