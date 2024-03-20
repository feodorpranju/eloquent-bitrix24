<?php

namespace Pranju\Bitrix24\Repositories;

use Pranju\Bitrix24\Contracts\Client;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\Repository;
use Pranju\Bitrix24\Repositories\Crm\AbstractCrmRepository;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class AbstractRepository implements Repository
{
    /**
     * Item name
     *
     * @var string
     * @see AbstractCrmRepository::getItemName()
     */
    protected string $name;

    public function __construct(protected Client $client)
    {

    }

    /**
     * Returns item name e.g. crm.lead
     *
     * @return mixed
     */
    protected function getItemName(): string
    {
        return $this->name ??= Str::dot(
            Str::after(static::class, __NAMESPACE__."\\")
        );
    }

    /**
     * Creates command instance for action
     *
     * @param string $action Item's action part e.g. "get" in "crm.lead.get".
     * Appended after item name in command as method
     * @param array $data
     * @return Command
     * @see AbstractRepository::getItemName()
     */
    protected function cmd(string $action, array $data = []): Command
    {
        return $this->getClient()->cmd(
            $this->getItemName().'.'.$action,
            $data
        );
    }

    /**
     * @inheritDoc
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}