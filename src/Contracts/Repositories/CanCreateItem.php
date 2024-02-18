<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Repositories;

interface CanCreateItem extends Repository
{
    /**
     * Create item
     *
     * @param array $fields Item's fields
     * @param array|null $options Create options e.g. REGISTER_SONET_EVENT
     * @return int|string|array
     * @see https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_add.php
     */
    public function create(array $fields, ?array $options = null): int|string|array;

    /**
     * Makes create item command
     *
     * @param array $fields Item's fields
     * @param array|null $options Create options e.g. REGISTER_SONET_EVENT
     * @return Command
     * @see CanCreateItem::create()
     */
    public function makeCreateCommand(array $fields, ?array $options = null): Command;
}