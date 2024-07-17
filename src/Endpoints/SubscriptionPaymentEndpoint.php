<?php

declare(strict_types=1);

namespace Mollie\Api\Endpoints;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\PaymentCollection;

class SubscriptionPaymentEndpoint extends EndpointCollection
{
    protected string $resourcePath = "customers_subscriptions_payments";

    protected $customerId = null;

    protected $subscriptionId = null;

    protected function getResourceCollectionClass(): string
    {
        return PaymentCollection::class;
    }

    public static function getResourceClass(): string
    {
        return Payment::class;
    }

    /**
     * Retrieves a paginated collection of Subscription Payments from Mollie.
     *
     * @param string $customerId
     * @param string $subscriptionId
     * @param string|null $from The first payment ID you want to include in your list.
     * @param int|null $limit The maximum amount of results you want to retrieve per page.
     * @param array $parameters
     *
     * @return PaymentCollection
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function pageForIds(
        string $customerId,
        string $subscriptionId,
        ?string $from = null,
        ?int $limit = null,
        array $parameters = []
    ) {
        $this->customerId = $customerId;
        $this->subscriptionId = $subscriptionId;

        return $this->fetchCollection($from, $limit, $parameters);
    }

    public function getResourcePath(): string
    {
        if (is_null($this->customerId)) {
            throw new ApiException('No customerId provided.');
        }

        if (is_null($this->subscriptionId)) {
            throw new ApiException('No subscriptionId provided.');
        }

        return "customers/{$this->customerId}/subscriptions/{$this->subscriptionId}/payments";
    }
}
