<?php


namespace Feodorpranju\Eloquent\Bitrix24\Contracts;


use Feodorpranju\Eloquent\Bitrix24\Contracts\Repositories\Repository;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\BatchResponse;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\ListResponse;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\Response;
use Feodorpranju\Eloquent\Bitrix24\Core\Authorization\Webhook;

interface Client
{
    /**
     * Client constructor
     *
     * @param string|Token $token if string, makes webhook token instance
     * @see Webhook
     */
    public function __construct(string|Token $token);

    /**
     * Calls comamnd. Returns batch response if batch called.
     *
     * @param string $method Method to be called (e. g. crm.lead.get)
     * @param array $data Method data
     * @param Command|null $command Used to fill up result. Autogenerated if null
     * @return Response|ListResponse|BatchResponse
     */
    public function call(string $method, array $data, ?Command $command = null): Response|ListResponse|BatchResponse;

    /**
     * Gets authorization token
     *
     * @return Token
     */
    public function getToken(): Token;

    /**
     * Gets repository for given collection
     *
     * @param string $collection
     * @return Repository
     */
    public function getRepository(string $collection): Repository;

    /**
     * Creates Command instance
     *
     * @param string $method
     * @param array $data
     * @return Command
     */
    public function cmd(string $method, array $data): Command;

    /**
     * Crates batch command instance
     *
     * @param Command[] $commands
     * @param bool $halt Determines if execution should be halted on first error
     * @return Batch
     */
    public function batch(array $commands, bool $halt = true): Batch;
}