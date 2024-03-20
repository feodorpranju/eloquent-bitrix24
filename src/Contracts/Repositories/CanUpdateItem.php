<?php

namespace Pranju\Bitrix24\Contracts\Repositories;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Core\Responses\Response;

interface CanUpdateItem extends Repository
{
    /**
     * Updates item
     *
     * @param string|int $id Item's ID
     * @param array $attributes Item's fields to be updated
     * @param array|null $options Create options e.g. REGISTER_SONET_EVENT
     * @return bool
     * @see https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_update.php
     */
    public function update(string|int $id, array $attributes, ?array $options = null): bool;

    /**
     * Makes item update command
     *
     * @param string|int $id Item's ID
     * @param array $attributes Item's fields to be updated
     * @param array|null $options Create options e.g. REGISTER_SONET_EVENT
     * @return Command
     * @see CanUpdateItem::update()
     */
    public function makeUpdateCommand(string|int $id, array $attributes, ?array $options = null): Command;

    /**
     * Gets item attributes from update response
     *
     * @param Response $response
     * @return bool
     */
    public function updatedSuccessfully(Response $response): bool;
}