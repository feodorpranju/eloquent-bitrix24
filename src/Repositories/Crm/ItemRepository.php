<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Illuminate\Support\Str;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\HasDynamicId;

class ItemRepository extends AbstractCrmRepository implements HasDynamicId
{
    /**
     * CRM entity type id
     *
     * @var int $dynamicId
     */
    protected $dynamicId;

    /**
     * @inheritDoc
     */
    public function getPrimaryKey(): string
    {
        return 'id';
    }

    /**
     * @inheritDoc
     */
    public function getAllColumnsSelect(): array
    {
        return ["id", "uf_*"];
    }

    protected function cmd(string $action, array $data = []): Command
    {
        return parent::cmd($action, array_replace($data, ['entityTypeId' => $this->getDynamicId()]));
    }

    /**
     * @inheritDoc
     */
    public function getDynamicId(): int
    {
        return $this->dynamicId ??= (int)Str::afterLast($this->table, '_');
    }
}