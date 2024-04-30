<?php

namespace Pranju\Bitrix24\Repositories\Crm;

use Illuminate\Support\Arr;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Repositories\CanCreateItem;
use Pranju\Bitrix24\Contracts\Repositories\CanDeleteItem;
use Pranju\Bitrix24\Contracts\Repositories\CanSelectItems;
use Pranju\Bitrix24\Contracts\Repositories\CanUpdateItem;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Batch;
use Pranju\Bitrix24\Core\Responses\BatchResponse;
use Pranju\Bitrix24\Repositories\AbstractRepository;
use Pranju\Bitrix24\Repositories\Traits\CreatesItem;
use Pranju\Bitrix24\Repositories\Traits\DeletesItem;
use Pranju\Bitrix24\Repositories\Traits\SelectsItems;
use Pranju\Bitrix24\Repositories\Traits\UpdatesItem;

abstract class AbstractContactPivotRepository extends AbstractRepository implements CanCreateItem, CanUpdateItem, CanSelectItems, CanDeleteItem
{
    use CreatesItem, UpdatesItem, SelectsItems, DeletesItem;

    /** @var string Parent id field e.g. LEAD_ID or DEAL_ID */
    protected const PARENT_ID_FIELD = '';

    /** @var string Parent id field e.g. CONTACT_ID or COMPANY_ID */
    protected const CHILD_ID_FIELD = '';

    /**
     * @inheritDoc
     */
    public function makeSelectCommand(?array $filter = null, ?array $select = null, ?array $order = null, int $offset = -1, ?int $limit = null): Command
    {
        $ids = (array)($filter['='.static::PARENT_ID_FIELD] ?? $filter[static::PARENT_ID_FIELD] ?? []);

        $batch = Batch::make([], $this->getClient());

        foreach ($ids as $id) {
            $batch->put($id, $this->cmd('items.get', ['id' => $id]));
        }

        return $batch;
    }

    /**
     * @inheritDoc
     */
    public function getSelectedItems(Response $response): array
    {
        if ($response instanceof BatchResponse) {
            return collect($response->responses())
                ->map(
                    fn(Response $response, int $parentId) => array_map(
                        fn (array $attributes) => array_merge(
                            $attributes,
                            [static::PARENT_ID_FIELD => $parentId],
                        ),
                        $this->getSelectedItems($response)
                    )
                )
                ->flatten(1)
                ->all();
        }

        return (array)$response->result();
    }

    /**
     * @inheritDoc
     */
    public function makeUpdateCommand(int|string $id, array $attributes, ?array $options = null): Command
    {
        $parentId = Arr::get($attributes, static::PARENT_ID_FIELD);
        $childId = Arr::get($attributes, static::CHILD_ID_FIELD);

        if (empty($parentId) || empty($childId)) {
            throw new \InvalidArgumentException('Attributes must contain both '.static::PARENT_ID_FIELD.' and '.static::CHILD_ID_FIELD);
        }

        return Batch::make([
            'delete' => $this->makeDeleteCommand("$parentId:$childId"),
            'create' => $this->makeCreateCommand($attributes),
        ], $this->getClient());
    }

    /**
     * @inheritDoc
     */
    public function makeCreateCommand(array $attributes, ?array $options = null): Command
    {
        $parentId = Arr::get($attributes, static::PARENT_ID_FIELD);

        if (empty($parentId)) {
            throw new \InvalidArgumentException('Attributes must contain '.static::PARENT_ID_FIELD);
        }

        return $this->cmd('add', [
            'id' => $parentId,
            'fields' => Arr::except($attributes, [static::PARENT_ID_FIELD]),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function makeDeleteCommand(int|string $id): Command
    {
        [$parentId, $childId] = explode(':', $id);

        return $this->cmd('delete', [
            'id' => $parentId,
            'fields' => [
                static::CHILD_ID_FIELD => $childId
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function count(array $filter): int
    {
        return count($this->select($filter));
    }
}