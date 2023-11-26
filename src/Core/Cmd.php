<?php


namespace Feodorpranju\Eloquent\Bitrix24\Core;


use Feodorpranju\Eloquent\Bitrix24\Contracts\Client;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Command;
use Feodorpranju\Eloquent\Bitrix24\Traits\GetsDefaultClient;
use Feodorpranju\Eloquent\Bitrix24\Traits\HasStaticMake;
use Illuminate\Support\Facades\DB;

/**
 * Class Cmd
 * @package Feodorpranju\Eloquent\Bitrix24\Core
 *
 * @method static static make(string $action, array $data = [], ?Client $client = null)
 */
class Cmd implements Command
{
    use HasStaticMake, GetsDefaultClient;

    /**
     * @inheritDoc
     */
    public function call(): array
    {
        //TODO throw on empty client
        return $this->client->call($this->action, $this->data);
    }

    public function __construct(protected string $action, protected array $data = [], protected ?Client $client = null)
    {
        $this->client ??= $this->getDefaultClient();
    }

    /**
     * @inheritDoc
     */
    public function getAction(): string
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
    public function setAction(string $action): void
    {
        $this->action = $action;
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

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->action.(empty($this->data) ? "" : "?".http_build_query($this->data));
    }
}