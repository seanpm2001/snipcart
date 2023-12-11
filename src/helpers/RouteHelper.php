<?php
namespace verbb\snipcart\helpers;

class RouteHelper
{
    /**
     * Returns an array of control panel routes.
     *
     * @return array
     */
    public static function getCpRoutes(): array
    {
        return [
            'snipcart' => 'snipcart/overview/index',
            'snipcart/orders' => 'snipcart/orders/index',
            'snipcart/order/<orderId>' => 'snipcart/orders/order-detail',
            'snipcart/customers' => 'snipcart/customers/index',
            'snipcart/customer/<customerId>' => 'snipcart/customers/customer-detail',
            'snipcart/discounts' => 'snipcart/discounts/index',
            'snipcart/discounts/new' => 'snipcart/discounts/new',
            'snipcart/discount/<discountId>' => 'snipcart/discounts/discount-detail',
            'snipcart/abandoned' => 'snipcart/carts/index',
            'snipcart/abandoned/<cartId>' => 'snipcart/carts/detail',
            'snipcart/subscriptions' => 'snipcart/subscriptions/index',
            'snipcart/subscription/<subscriptionId>' => 'snipcart/subscriptions/detail',
        ];
    }
}
