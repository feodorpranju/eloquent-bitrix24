<?php

namespace Pranju\Bitrix24\Repositories\Traits;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Responses\BatchResponse;
use Pranju\Bitrix24\Helpers\ListCommandsGenerator;

trait SelectsItems
{


    /**
     * @inheritDoc
     */
    public function select(?array $filter = null, ?array $select = null, ?array $order = null, int $offset = -1, ?int $limit = null): array
    {
        return $this->getSelectedItems(
            $this->makeSelectCommand($filter, $select, $order, $offset, $limit)->call()
        );
    }

    /**
     * @inheritDoc
     */
    public function makeSelectCommand(?array $filter = null, ?array $select = null, ?array $order = null, int $offset = -1, ?int $limit = null): Command
    {
        $count = $this->count($filter);

        return (new ListCommandsGenerator())->generateBatch(
            $this->cmd(
                'list',
                [
                    'filter' => $filter,
                    'order' => $order,
                    'select' => $select,
                    'start' => $offset,
                ]
            ),
            min($count, $limit ?: $count),
        );
    }

    /**
     * @inheritDoc
     */
    public function getSelectedItems(Response $response): array
    {
        if ($response instanceof BatchResponse) {
            return collect($response->responses())
                ->map(fn(Response $response) => $response->result())
                ->flatten(1)
                ->all();
        }

        return (array)$response->result();
    }

    /**
     * @inheritDoc
     */
    public function count(array $filter): int
    {
        return $this->cmd(
            'list',
            [
                'filter' => $filter,
                'select' => [$this->getPrimaryKey()]
            ]
        )->call()?->pagination()->total() ?? 0;
    }
}