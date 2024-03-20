<?php


namespace Pranju\Bitrix24\Contracts;


use Pranju\Bitrix24\Contracts\Responses\BatchResponse;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Stringable;

interface Command extends JsonSerializable, Jsonable, Stringable, Arrayable
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
    public function getMethod(): string;

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
     * @param string $method
     * @return mixed
     */
    public function setMethod(string $method): void;

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