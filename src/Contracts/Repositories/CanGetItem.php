<?php

namespace Pranju\Bitrix24\Contracts\Repositories;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;

interface CanGetItem extends Repository
{
    /**
     * Gets item by id
     *
     * @param string|int $id Item's ID
     * @return array|null
     */
    public function get(string|int $id): ?array;

    /**
     * Makes get command
     *
     * @param string|int $id Item's ID
     * @return Command
     * @see CanGetItem::find()
     */
    public function makeGetCommand(string|int $id): Command;

    /**
     * Gets item attributes from response
     *
     * @param Response $response
     * @return array
     */
    public function getReceivedItemAttributes(Response $response): array;
}