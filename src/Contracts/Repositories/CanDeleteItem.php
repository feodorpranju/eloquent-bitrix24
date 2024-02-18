<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Repositories;

use Feodorpranju\Eloquent\Bitrix24\Contracts\Command;

interface CanDeleteItem extends Repository
{
    /**
     * Delete item
     *
     * @param int|string $id Item's ID
     * @return bool
     */
    public function delete(int|string $id): bool;

    /**
     * Makes delete item command
     *
     * @param int|string $id Item's ID
     * @return Command
     * @see CanDeleteItem::delete()
     */
    public function makeDeleteCommand(int|string $id): Command;
}