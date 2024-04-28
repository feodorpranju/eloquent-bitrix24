<?php

namespace Pranju\Bitrix24\Repositories\Traits;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response;

trait DeletesItem
{
    /**
     * @inheritDoc
     */
    public function delete(int|string $id): bool
    {
        return $this->deletedSuccessfully(
            $this->makeDeleteCommand($id)->call()
        );
    }

    /**
     * @inheritDoc
     */
    public function makeDeleteCommand(int|string $id): Command
    {
        return $this->cmd('delete', ['id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function deletedSuccessfully(Response $response): bool
    {
        return (bool)$response->result();
    }
}