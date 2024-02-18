<?php


namespace Feodorpranju\Eloquent\Bitrix24\Contracts;


use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\BatchResponse;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\Response;

interface Command
{
    /**
     * Calls action on client
     *
     * @return BatchResponse|Response
     */
    public function call(): BatchResponse|Response;

    /**
     * Returns action
     *
     * @return string
     */
    public function getAction(): string;

    /**
     * Returns data
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Returns client
     *
     * @return Client
     */
    public function getClient(): Client;

    /**
     * Sets data
     *
     * @param string $action
     * @return mixed
     */
    public function setAction(string $action): void;

    /**
     * Sets data
     *
     * @param array $data
     */
    public function setData(array $data): void;

    /**
     * Sets client
     *
     * @param Client $client
     */
    public function setClient(Client $client): void;

    /**
     * Returns command as get request
     *
     * @return string
     */
    public function __toString(): string;
}