<?php


namespace Pranju\Bitrix24\Contracts;


use Illuminate\Support\Enumerable;
use Pranju\Bitrix24\Contracts\Responses\BatchResponse;

interface Batch extends Command, Enumerable
{
    /**
     * Batch constructor
     *
     * @param array|mixed $items
     * @param Client|null $client
     * @param bool $halt
     */
    public function __construct($items = [], ?Client $client = null, bool $halt = true);

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