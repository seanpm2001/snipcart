<?php
/**
 * Snipcart plugin for Craft CMS 3.x
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2018 Working Concept Inc.
 */

namespace fostercommerce\snipcart\events;

use fostercommerce\snipcart\models\snipcart\Subscription;
use yii\base\Event;

/**
 * Subscription event class.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2018 Working Concept Inc.
 */
class SubscriptionEvent extends Event
{
    /**
     * @var Subscription
     */
    public $subscription;

}
