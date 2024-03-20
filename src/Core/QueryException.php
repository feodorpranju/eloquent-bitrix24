<?php

namespace Pranju\Bitrix24\Core;

use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Command;
use Throwable;

class QueryException extends Bitrix24Exception
{
    /**
     * Create a new query exception instance.
     *
     * @param string $connectionName
     * @param Command $command
     * @param Throwable $previous
     * @return void
     */
    public function __construct(protected string $connectionName, protected Command $command, Throwable $previous)
    {
        parent::__construct('', 0, $previous);

        $this->code = $previous->getCode();
        $this->message = $this->formatMessage($connectionName, $command, $previous);
    }

    /**
     * Format the SQL error message.
     *
     * @param string $connectionName
     * @param Command $command
     * @param Throwable $previous
     * @return string
     */
    protected function formatMessage(string $connectionName, Command $command, Throwable $previous): string
    {
        return $previous->getMessage().' (Connection: '.$connectionName.', Bitrix24: '.$command->jsonSerialize().')';
    }

    /**
     * Get the connection name for the query.
     *
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    /**
     * Get the command for the query.
     *
     * @return Command
     */
    public function getCommand(): Command
    {
        return $this->command;
    }
}