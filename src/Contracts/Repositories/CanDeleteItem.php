<?php

namespace Pranju\Bitrix24\Contracts\Repositories;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;

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

    /**
     * Checks if deletion was successful
     *
     * @param Response $response
     * @return bool
     */
    public function deletedSuccessfully(Response $response): bool;
}