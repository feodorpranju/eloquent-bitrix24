<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Responses;

use Feodorpranju\Eloquent\Bitrix24\Contracts\Command;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Exceptions\ResponseException;
use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use \Illuminate\Http\Client\Response as HttpResponse;

/**
 * @mixin Response
 */
interface Response extends ArrayAccess, Arrayable
{
    public function __construct(HttpResponse|array $response, ?Command $command);

    /**
     * Gets called command
     *
     * @return Command|null
     */
    public function command(): ?Command;

    /**
     * Gets http response
     *
     * @return HttpResponse|null
     */
    public function httpResponse(): ?HttpResponse;

    /**
     * Gets request result or specific key of result
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return array
     */
    public function result(?string $key = null, mixed $default = null): array;

    /**
     * Gets request time info
     *
     * @return ResponseTime
     */
    public function time(): ResponseTime;

    /**
     * Determine if the request was successful.
     *
     * @return bool
     */
    public function successful(): bool;

    /**
     * Determine if the response indicates a client or server error occurred.
     *
     * @return bool
     */
    public function failed(): bool;

    /**
     * Create an exception if a server or client error occurred.
     *
     * @return ResponseException|null
     */
    public function toException(): ?ResponseException;
}