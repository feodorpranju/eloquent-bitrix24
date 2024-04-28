<?php

namespace Pranju\Bitrix24\Repositories\Traits;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;

trait GetsItem
{
    /**
     * @inheritDoc
     */
    public function get(int|string $id): ?array
    {
        return $this->getReceivedItemAttributes(
            $this->makeGetCommand($id)->call()
        ) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function makeGetCommand(int|string $id): Command
    {
        return $this->cmd('get', ['id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function getReceivedItemAttributes(Response $response): array
    {
        return (array)$response->result();
    }
}