<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Repositories;

use Feodorpranju\Eloquent\Bitrix24\Contracts\Command;

interface CanUpdateItem extends Repository
{
    /**
     * Updates item
     *
     * @param string|int $id Item's ID
     * @param array $fields Item's fields to be updated
     * @param array|null $options Create options e.g. REGISTER_SONET_EVENT
     * @return int|string|array
     * @see https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_update.php
     */
    public function update(string|int $id, array $fields, ?array $options = null): int|string|array;

    /**
     * Makes item update command
     *
     * @param string|int $id Item's ID
     * @param array $fields Item's fields to be updated
     * @param array|null $options Create options e.g. REGISTER_SONET_EVENT
     * @return Command
     * @see CanUpdateItem::update()
     */
    public function makeUpdateCommand(): Command;
}