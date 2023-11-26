<?php


namespace Feodorpranju\Eloquent\Bitrix24\Contracts;


interface Command
{
    /**
     * Calls action on client
     *
     * @return array
     */
    public function call(): array;

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