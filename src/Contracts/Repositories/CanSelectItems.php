<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Repositories;

use Feodorpranju\Eloquent\Bitrix24\Contracts\Command;

interface CanSelectItems extends Repository
{
    /**
     * Selects items by filter
     *
     * @param array|null $filter Filter
     * @param array|null $select Fields to be selected
     * @param array|null $order Order by [field => ASC|DESC]
     * @param int $offset Offset of selected items. Must be multiple of 50 or equal -1
     * @param int|null $limit Limit of items to be selected. Must be multiple of 50
     * @return array
     * @see https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_list.php
     */
    public function select(
        ?array $filter = null,
        ?array $select = null,
        ?array $order = null,
        int $offset = -1,
        ?int $limit = null,
    ): array;

    /**
     * Makes select command
     *
     * @param array|null $filter Filter
     * @param array|null $select Fields to be selected
     * @param array|null $order Order by [field => ASC|DESC]
     * @param int $offset Offset of selected items. Must be multiple of 50 or equal -1
     * @param int|null $limit Limit of items to be selected. Must be multiple of 50
     * @return Command
     * @see CanSelectItems::select()
     */
    public function makeSelectCommand(
        ?array $filter = null,
        ?array $select = null,
        ?array $order = null,
        int $offset = -1,
        ?int $limit = null,
    ): Command;
}