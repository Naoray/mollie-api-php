<?php

namespace Mollie\Api\Resources;

use Mollie\Api\MollieApiClient;

abstract class BaseCollection extends \ArrayObject
{
    protected MollieApiClient $client;

    /**
     * @var \stdClass|null
     */
    public ?\stdClass $_links = null;

    /**
     * @param MollieApiClient $client
     * @param array|object $items
     * @param \stdClass|null $_links
     */
    public function __construct(MollieApiClient $client, $items = [], ?\stdClass $_links = null)
    {
        parent::__construct($items);

        $this->_links = $_links;
        $this->client = $client;
    }

    public function contains(callable $callback): bool
    {
        foreach ($this as $item) {
            if ($callback($item)) {
                return true;
            }
        }

        return false;
    }

    public function filter(callable $callback): static
    {
        $filteredItems = array_filter($this->getArrayCopy(), $callback);

        /** @phpstan-ignore-next-line */
        return new static($this->client, $filteredItems,  $this->_links);
    }


    abstract public static function getCollectionResourceName(): ?string;
}
