<?php


namespace Pranju\Bitrix24\Contracts;


use Illuminate\Support\Enumerable;
use Pranju\Bitrix24\Contracts\Responses\BatchResponse;

interface Batch extends Command, Enumerable
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