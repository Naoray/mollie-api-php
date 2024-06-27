<?php

namespace Mollie\Api\Endpoints;

use Mollie\Api\Contracts\SingleResourceEndpointContract;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\BaseResource;
use Mollie\Api\Resources\ResourceFactory;
use RuntimeException;

abstract class RestEndpoint extends BaseEndpoint
{
    /**
     * Resource id prefix.
     * Used to validate resource id's.
     *
     * @var string
     */
    protected static string $resourceIdPrefix;

    /**
     * @param array $body
     * @param array $filters
     * @return BaseResource
     * @throws ApiException
     */
    protected function createResource(array $body, array $filters): BaseResource
    {
        $result = $this->client->performHttpCall(
            self::REST_CREATE,
            $this->getResourcePath() . $this->buildQueryString($filters),
            $this->parseRequestBody($body)
        );

        return ResourceFactory::createFromApiResult($result->decode(), $this->getResourceObject());
    }

    /**
     * Sends a PATCH request to a single Mollie API object.
     *
     * @param string $id
     * @param array $body
     *
     * @return null|BaseResource
     * @throws ApiException
     */
    protected function updateResource(string $id, array $body = []): ?BaseResource
    {
        $id = urlencode($id);

        $response = $this->client->performHttpCall(
            self::REST_UPDATE,
            $this->getPathToSingleResource($id),
            $this->parseRequestBody($body)
        );

        if ($response->isEmpty()) {
            return null;
        }

        return ResourceFactory::createFromApiResult($response->decode(), $this->getResourceObject());
    }

    /**
     * Retrieves a single object from the REST API.
     *
     * @param string $id Id of the object to retrieve.
     * @param array $filters
     * @return BaseResource
     * @throws ApiException
     */
    protected function readResource(string $id, array $filters): BaseResource
    {
        if (!$this instanceof SingleResourceEndpointContract && empty($id)) {
            throw new ApiException("Invalid resource id.");
        }

        $id = urlencode($id);
        $response = $this->client->performHttpCall(
            self::REST_READ,
            $this->getPathToSingleResource($id) . $this->buildQueryString($filters)
        );

        return ResourceFactory::createFromApiResult($response->decode(), $this->getResourceObject());
    }

    /**
     * Sends a DELETE request to a single Mollie API object.
     *
     * @param string $id
     * @param array $body
     *
     * @return null|BaseResource
     * @throws ApiException
     */
    protected function deleteResource(string $id, array $body = []): ?BaseResource
    {
        if (empty($id)) {
            throw new ApiException("Invalid resource id.");
        }

        $id = urlencode($id);
        $response = $this->client->performHttpCall(
            self::REST_DELETE,
            $this->getPathToSingleResource($id),
            $this->parseRequestBody($body)
        );

        if ($response->isEmpty()) {
            return null;
        }

        return ResourceFactory::createFromApiResult($response->decode(), $this->getResourceObject());
    }

    protected function guardAgainstInvalidId(string $id): void
    {
        if (empty(static::$resourceIdPrefix)) {
            throw new RuntimeException("Resource ID prefix is not set.");
        }

        if (empty($id) || strpos($id, static::$resourceIdPrefix) !== 0) {
            $resourceType = $this->getResourceType();

            throw new ApiException("Invalid {$resourceType} ID: '{$id}'. A resource ID should start with '" . static::$resourceIdPrefix . "'.");
        }
    }

    public function getResourceType(): string
    {
        $resourceClass = $this->getResourceObject()::class;
        $classBasename = basename(str_replace("\\", "/", $resourceClass));

        return strtolower($classBasename);
    }

    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return BaseResource
     */
    abstract protected function getResourceObject(): BaseResource;
}
