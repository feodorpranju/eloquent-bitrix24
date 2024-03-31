<?php

namespace Pranju\Bitrix24\Contracts\Repositories;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;

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

    /**
     * Gets selected items
     *
     * @return array[]
     */
    public function getSelectedItems(Response $response): array;

    /**
     * Retrieves list of column aliases to select all columns
     *
     * @return array
     */
    public function getAllColumnsSelect(): array;
}