<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Repositories;

use Feodorpranju\Eloquent\Bitrix24\Contracts\Command;

interface CanGetItem extends Repository
{
    /**
     * Gets item by id
     *
     * @param string|int $id Item's ID
     * @return array
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
}