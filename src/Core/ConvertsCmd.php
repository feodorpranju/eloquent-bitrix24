<?php

namespace Pranju\Bitrix24\Core;

use JetBrains\PhpStorm\ArrayShape;

trait ConvertsCmd
{
    /**
     * Converts command to string
     */
    public function toString(): string
    {
        return $this->getMethod().(empty($this->getData()) ? "" : "?".urldecode(http_build_query($this->getData())));
    }

    /**
     * @see ConvertsCmd::toString()
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Gets command params as array
     *
     * @return array
     */
    #[ArrayShape([
        'host' => 'string',
        'method' => 'string',
        'data' => 'array',
    ])]
    public function toArray(): array
    {
        return [
            'connection' => $this->getClient()->getConnectionName(),
            'host' => $this->getClient()->getToken()->getHost(),
            'method' => $this->getMethod(),
            'data' => $this->getData(),
        ];
    }

    /**
     * Returns command params as json
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Returns command params as json serializable data type
     *
     * @inheritDoc
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}