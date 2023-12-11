<?php
namespace verbb\snipcart\variables;

use verbb\snipcart\Snipcart;
use verbb\snipcart\fields\ProductDetails;
use verbb\snipcart\helpers\FieldHelper;
use verbb\snipcart\helpers\FormatHelper;
use verbb\snipcart\models\snipcart\Customer;
use verbb\snipcart\models\snipcart\Order;
use verbb\snipcart\models\snipcart\Subscription;

use Craft;
use craft\base\Element;
use craft\helpers\App;
use craft\helpers\Template as TemplateHelper;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;

use yii\base\Exception;
use yii\base\InvalidConfigException;

use DateTime;

class SnipcartVariable
{
    // Public Methods
    // =========================================================================

    public function publicApiKey(): string
    {
        return Snipcart::$plugin->getSettings()->getPublicKey();
    }

    public function defaultCurrency(): string
    {
        return Snipcart::$plugin->getSettings()->getDefaultCurrency();
    }

    public function defaultCurrencySymbol(): string
    {
        return Snipcart::$plugin->getSettings()->getDefaultCurrencySymbol();
    }

    public function formatCurrency(mixed $value, string $currencyType = null): string
    {
        return FormatHelper::formatCurrency($value, $currencyType);
    }

    public function tinyDateInterval(DateTime $dateTime): string
    {
        return FormatHelper::tinyDateInterval($dateTime);
    }

    public function getCustomer(string $customerId): ?Customer
    {
        return Snipcart::$plugin->getCustomers()->getCustomer($customerId);
    }

    public function getOrder(string $orderId): ?Order
    {
        return Snipcart::$plugin->getOrders()->getOrder($orderId);
    }

    public function getSubscription(string $subscriptionId): ?Subscription
    {
        return Snipcart::$plugin->getSubscriptions()->getSubscription($subscriptionId);
    }

    public function getProductInfo(Element $element): ?ProductDetails
    {
        return FieldHelper::getProductInfo($element);
    }

    public function cartLink(string $text = null, bool $showCount = true): Markup
    {
        return $this->renderTemplate('snipcart/front-end/cart-link', [
            'text' => $text,
            'showCount' => $showCount,
        ]);
    }

    public function cartSnippet(bool $includejQuery = true, string $onload = '', bool $includeStyles = true): Markup
    {
        $settings = Snipcart::$plugin->getSettings();

        return $this->renderTemplate('snipcart/front-end/cart-js', [
            'settings' => $settings,
            'includejQuery' => $includejQuery,
            'includeStyles' => $includeStyles,
            'publicApiKey' => $settings->getPublicKey(),
            'onload' => $onload,
        ]);
    }

    private function renderTemplate(string $template, array $data = []): Markup
    {
        $view = Craft::$app->getView();
        $templateMode = $view->getTemplateMode();

        // use CP mode
        $view->setTemplateMode($view::TEMPLATE_MODE_CP);

        // render the thing
        $html = $view->renderTemplate($template, $data);

        // put it back how we found it
        $view->setTemplateMode($templateMode);

        return TemplateHelper::raw($html);
    }
}
