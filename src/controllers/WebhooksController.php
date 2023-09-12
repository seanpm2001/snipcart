<?php
/**
 * Snipcart plugin for Craft CMS 3.x
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2018 Working Concept Inc.
 */

namespace fostercommerce\snipcart\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;

use fostercommerce\snipcart\services\Webhooks;
use fostercommerce\snipcart\Snipcart;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class WebhooksController extends Controller
{
    /**
     * Snipcart's available webhook events
     */
    public const WEBHOOK_ORDER_COMPLETED = 'order.completed';

    public const WEBHOOK_SHIPPINGRATES_FETCH = 'shippingrates.fetch';

    public const WEBHOOK_ORDER_STATUS_CHANGED = 'order.status.changed';

    public const WEBHOOK_ORDER_PAYMENT_STATUS_CHANGED = 'order.paymentStatus.changed';

    public const WEBHOOK_ORDER_TRACKING_NUMBER_CHANGED = 'order.trackingNumber.changed';

    public const WEBHOOK_SUBSCRIPTION_CREATED = 'subscription.created';

    public const WEBHOOK_SUBSCRIPTION_CANCELLED = 'subscription.cancelled';

    public const WEBHOOK_SUBSCRIPTION_PAUSED = 'subscription.paused';

    public const WEBHOOK_SUBSCRIPTION_RESUMED = 'subscription.resumed';

    public const WEBHOOK_SUBSCRIPTION_INVOICE_CREATED = 'subscription.invoice.created';

    public const WEBHOOK_TAXES_CALCULATE = 'taxes.calculate';

    public const WEBHOOK_CUSTOMER_UPDATED = 'customauth:customer_updated';

    public const WEBHOOK_REFUND_CREATED = 'order.refund.created';

    public const WEBHOOK_NOTIFICATION_CREATED = 'order.notification.created';

    /**
     * What we call to handle each event
     */
    public const WEBHOOK_EVENT_MAP = [
        self::WEBHOOK_ORDER_COMPLETED => 'handleOrderCompleted',
        self::WEBHOOK_SHIPPINGRATES_FETCH => 'handleShippingRatesFetch',
        self::WEBHOOK_ORDER_STATUS_CHANGED => 'handleOrderStatusChange',
        self::WEBHOOK_ORDER_PAYMENT_STATUS_CHANGED => 'handleOrderPaymentStatusChange',
        self::WEBHOOK_ORDER_TRACKING_NUMBER_CHANGED => 'handleOrderTrackingNumberChange',
        self::WEBHOOK_SUBSCRIPTION_CREATED => 'handleSubscriptionCreated',
        self::WEBHOOK_SUBSCRIPTION_CANCELLED => 'handleSubscriptionCancelled',
        self::WEBHOOK_SUBSCRIPTION_PAUSED => 'handleSubscriptionPaused',
        self::WEBHOOK_SUBSCRIPTION_RESUMED => 'handleSubscriptionResumed',
        self::WEBHOOK_SUBSCRIPTION_INVOICE_CREATED => 'handleSubscriptionInvoiceCreated',
        self::WEBHOOK_TAXES_CALCULATE => 'handleTaxesCalculate',
        self::WEBHOOK_CUSTOMER_UPDATED => 'handleCustomerUpdated',
        self::WEBHOOK_REFUND_CREATED => 'handleRefundCreated',
        self::WEBHOOK_NOTIFICATION_CREATED => 'handleNotificationCreated',
    ];

    /**
     * @var bool Disable CSRF for this controller
     */
    public $enableCsrfValidation = false;

    /**
     * @var bool allow all endpoints in this controller to be used publicly
     */
    protected array|int|bool $allowAnonymous = true;

    private static bool $validateWebhook = true;

    public function init(): void
    {
        parent::init();

        /**
         * Return all controller output as JSON.
         */
        \Yii::$app->response->format = Response::FORMAT_JSON;
    }

    /**
     * Validates and responds to the post request.
     *
     * @throws BadRequestHttpException if method isn't post or if something's
     *                                 wrong with the post itself.
     * @throws Exception if the mapped handler method doesn't exist.
     * @todo appropriately handle weird/old timestamps in $postData->createdOn
     */
    public function actionHandle(): Response
    {
        $this->requirePostRequest();
        $requestBody = Craft::$app->getRequest()->getRawBody();
        $payload = Json::decode($requestBody, false);

        if ($reason = $this->hasInvalidRequestData($payload)) {
            return $this->badRequestResponse([
                'reason' => $reason,
            ]);
        }

        Snipcart::$plugin->webhooks->setData($payload);

        return $this->handleWebhookData($payload->eventName);
    }

    /**
     * Sends the webhook’s POST payload to the appropriate handler method.
     *
     * @param string $eventName    Event name from `WEBHOOK_EVENT_MAP`.
     * @throws Exception if the mapped handler method doesn’t exist.
     */
    private function handleWebhookData($eventName): Response
    {
        $methodName = self::WEBHOOK_EVENT_MAP[$eventName];

        if (method_exists(Snipcart::$plugin->webhooks, $methodName)) {
            /**
             * Call the method defined in `WEBHOOK_EVENT_MAP`,
             * with $postContent as its argument.
             *
             * Send whatever it returns as a JSON response.
             */
            return $this->asJson(
                Snipcart::$plugin->webhooks->{$methodName}()
            );
        }

        throw new Exception(sprintf(
            'Invalid Snipcart webhook handler specified for `%s`.',
            $eventName
        ));
    }

    /**
     * Returns a 400 response with an optional JSON error array.
     *
     * @param  array  $errors Array of errors that explain the 400 response
     */
    private function badRequestResponse(array $errors): Response
    {
        $response = Craft::$app->getResponse();

        $response->format = Response::FORMAT_JSON;
        $response->content = json_encode([
            'success' => false,
            'errors' => $errors,
        ], JSON_THROW_ON_ERROR);

        $response->setStatusCode(400, 'Bad Request');

        return $response;
    }

    /**
     * Make sure we don’t have invalid data, or return a reason if we do.
     *
     * @param mixed $payload Decoded post payload to be checked.
     * @throws BadRequestHttpException if there's a problem with the token.
     */
    private function hasInvalidRequestData(mixed $payload): bool|string
    {
        /**
         * Reject requests that can’t be validated.
         */
        if (self::$validateWebhook && ! $this->requestIsValid()) {
            return 'Could not validate webhook request. Are you Snipcart?';
        }

        /**
         * Every Snipcart post should have an eventName, so we’ve got
         * missing data or a bad format.
         */
        if ($payload === null || ! isset($payload->eventName)) {
            return 'NULL request body or missing eventName.';
        }

        /**
         * Every Snipcart post should have a content property.
         */
        if (! isset($payload->content)) {
            return 'Request missing content.';
        }

        /**
         * Every Snipcart post should either be in live or test mode.
         */
        if (! isset($payload->mode)) {
            return 'Request missing mode.';
        }

        /**
         * Every Snipcart post should clarify whether it’s in live or test mode.
         */
        if (! in_array(
            $payload->mode,
            [Webhooks::WEBHOOK_MODE_LIVE, Webhooks::WEBHOOK_MODE_TEST],
            false
        )) {
            return 'Invalid mode.';
        }

        /**
         * We’ve received an invalid `eventName`.
         */
        if (! array_key_exists(
            $payload->eventName,
            self::WEBHOOK_EVENT_MAP
        )) {
            return 'Invalid event.';
        }

        // Looks valid! We do *not* have invalid data.
        return false;
    }

    /**
     * Evaluates the supplied token to be sure the request is genuine.
     *
     * @return boolean
     * @throws BadRequestHttpException if the token is missing or malformed.
     * @throws \Exception if the token is invalid or there’s a problem
     *                    with the API call.
     */
    private function requestIsValid(): bool
    {
        $key = 'x-snipcart-requesttoken';
        $headers = Craft::$app->getRequest()->getHeaders();
        $devMode = Craft::$app->getConfig()->general->devMode;

        if (! $headers->has($key) && $devMode) {
            // don’t validate if we’re in dev mode
            return true;
        }

        if (! $headers->has($key)) {
            throw new BadRequestHttpException(
                'Invalid request: no request token'
            );
        }

        $token = $headers->get($key);

        if (! is_string($token)) {
            throw new BadRequestHttpException(
                'Invalid request: token can only be a string'
            );
        }

        return Snipcart::$plugin->api->tokenIsValid($token);
    }
}
