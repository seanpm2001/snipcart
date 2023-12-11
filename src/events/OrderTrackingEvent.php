<?php
namespace verbb\snipcart\events;

use verbb\snipcart\models\snipcart\Order;
use yii\base\Event;

/**
 * Order tracking event class.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2018 Working Concept Inc.
 */
class OrderTrackingEvent extends Event
{
    /**
     * @var Order
     */
    public $order;

    /**
     * @var string
     */
    public $trackingNumber;

    /**
     * @var string
     */
    public $trackingUrl;

}
