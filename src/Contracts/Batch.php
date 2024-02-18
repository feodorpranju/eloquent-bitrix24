<?php


namespace Feodorpranju\Eloquent\Bitrix24\Contracts;


use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\BatchResponse;

interface Batch extends Command
{
    /**
     * Calls action on client
     *
     * @return BatchResponse
     */
    public function call(): BatchResponse;

    /**
     * Returns halt value
     *
     * @return bool
     */
    public function getHalt(): bool;

    /**
     * Sets halt param
     *
     * @param bool $halt Determines if execution should be halted on first error
     */
    public function setHalt(bool $halt): void;
}