<?php


namespace Pranju\Bitrix24\Core;


use Pranju\Bitrix24\Contracts\Client;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\BatchResponse;
use Pranju\Bitrix24\Contracts\Responses\ListResponse;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Traits\GetsDefaultClient;
use Pranju\Bitrix24\Traits\HasStaticMake;
use Illuminate\Support\Facades\DB;

/**
 * Class Cmd
 * @package Pranju\Bitrix24\Core
 *
 * @method static static make(string $action, array $data = [], ?Client $client = null)
 */
class Cmd implements Command
{
    use HasStaticMake, GetsDefaultClient, ConvertsCmd;

    /**
     * @inheritDoc
     */
    public function call(): Response|ListResponse|BatchResponse
    {
        //TODO throw on empty client
        return $this->client->call($this->action, $this->data, $this);
    }

    public function __construct(protected string $action, protected array $data = [], protected ?Client $client = null)
    {
        $this->client ??= $this->getDefaultClient();
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->action;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function getClient(): Client
    {
        //TODO throw on empty client
        return $this->client;
    }

    /**
     * @inheritDoc
     */
    public function setMethod(string $method): void
    {
        $this->action = $method;
    }

    /**
     * @inheritDoc
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }
}