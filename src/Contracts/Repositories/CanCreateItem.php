<?php

namespace Pranju\Bitrix24\Contracts\Repositories;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;

interface CanCreateItem extends Repository
{
    /**
     * Create item
     *
     * @param array $attributes Item's fields
     * @param array|null $options Create options e.g. REGISTER_SONET_EVENT
     * @return int|string
     * @see https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_add.php
     */
    public function create(array $attributes, ?array $options = null): int|string;

    /**
     * Makes create item command
     *
     * @param array $attributes Item's fields
     * @param array|null $options Create options e.g. REGISTER_SONET_EVENT
     * @return Command
     * @see CanCreateItem::create()
     */
    public function makeCreateCommand(array $attributes, ?array $options = null): Command;

    /**
     * Gets ID for created item from response
     *
     * @param Response $response
     * @return int|string
     */
    public function getCreatedItemId(Response $response): int|string;
}