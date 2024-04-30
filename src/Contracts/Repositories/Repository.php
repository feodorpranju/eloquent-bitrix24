<?php

namespace Pranju\Bitrix24\Contracts\Repositories;

use Pranju\Bitrix24\Contracts\Client;
use Pranju\Bitrix24\Contracts\Command;

interface Repository
{
    public function __construct(Client $client, string $table);

    /**
     * Retrieves repository's client
     *
     * @return Client
     */
    public function getClient(): Client;

    /**
     * Retrieves table's primary key
     *
     * @return string
     */
    public function getPrimaryKey(): string;

    /**
     * Returns item name e.g. crm.lead
     *
     * @return mixed
     */
    public function getItemName(): string;

    /**
     * Creates command instance for action
     *
     * @param string $action Item's action part e.g. "get" in "crm.lead.get".
     * Appended after item name in command as method
     * @param array $data Command payload
     * @return Command
     * @see Repository::getItemName()
     */
    public function cmd(string $action, array $data = []): Command;
}