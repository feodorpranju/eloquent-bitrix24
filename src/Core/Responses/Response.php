<?php

namespace Pranju\Bitrix24\Core\Responses;

use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\Response as ResponseInterface;
use Pranju\Bitrix24\Contracts\Responses\ResponseTime as ResponseTimeInterface;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Arr;

class Response implements ResponseInterface
{
    protected ResponseTimeInterface $time;

    protected mixed $result = null;
    protected array $responseArray;

    /**
     * @param HttpResponse|array $response
     * @param Command|null $command
     */
    public function __construct(protected HttpResponse|array $response, protected ?Command $command = null)
    {
//        throw_if($this->failed(), $this->toException());
    }

    /**
     * Gets response as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->responseArray ??= $this->httpResponse()?->json() ?? $this->response;
    }

    /**
     * @inheritDoc
     */
    public function command(): ?Command
    {
        return $this->command;
    }

    /**
     * @inheritDoc
     */
    public function httpResponse(): ?HttpResponse
    {
        if ($this->response instanceof HttpResponse) {
            return $this->response;
        }

        return null;
    }

    /**
     * @param string|null $key
     * @param mixed|null $default
     * @inheritDoc
     */
    public function result(?string $key = null, mixed $default = null): mixed
    {
        $this->result
            ??= $this->httpResponse()?->json()['result']
            ?? $this->response['result']
            ?? [];

        if (is_null($key)) {
            return $this->result;
        }

        return Arr::get($this->result, $key, $default);
    }

    /**
     * @inheritDoc
     */
    public function time(): ResponseTimeInterface
    {
        return $this->time ??= new ResponseTime(...$this['time']);
    }

    /**
     * @inheritDoc
     */
    public function successful(): bool
    {
        return $this->httpResponse()?->successful() ?? !empty($this->result());
    }

    /**
     * @inheritDoc
     */
    public function failed(): bool
    {
        return !$this->successful();
    }

    /**
     * @inheritDoc
     */
    public function toException(): ?ResponseException
    {
        return new ResponseException($this);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->toArray()[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->toArray()[$offset] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        //TODO throw exception
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void
    {
        //TODO throw exception
    }
}