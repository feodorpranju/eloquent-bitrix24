<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\HasDynamicId;
use Pranju\Bitrix24\Enums\Crm\EntityTypeIdEnum;

class StatusRepository extends AbstractCrmRepository
{
    /**
     * @inheritDoc
     */
    public function getAllColumnsSelect(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function makeSelectCommand(?array $filter = null, ?array $select = null, ?array $order = null, int $offset = -1, ?int $limit = null): Command
    {
        return $this->cmd(
            'list',
            [
                'filter' => $filter,
                'order' => $order,
            ]
        );
    }
}