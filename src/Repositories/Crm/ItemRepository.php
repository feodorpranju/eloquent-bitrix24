<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Illuminate\Support\Str;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\HasDynamicId;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Responses\BatchResponse;

class ItemRepository extends AbstractCrmRepository implements HasDynamicId
{
    /**
     * CRM entity type id
     *
     * @var int $dynamicId
     */
    protected int $dynamicId;

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
        return ["*", "uf_*"];
    }

    /**
     * @inheritDoc
     */
    public function cmd(string $action, array $data = []): Command
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

    /**
     * @inheritDoc
     */
    public function getSelectedItems(Response $response): array
    {
        if ($response instanceof BatchResponse) {
            return parent::getSelectedItems($response);
        }

        return (array)$response->result('items', []);
    }
}