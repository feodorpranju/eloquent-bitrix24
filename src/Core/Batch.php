<?php


namespace Pranju\Bitrix24\Core;


use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Client;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\BatchResponse;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Responses\UnlimitedBatchResponse;
use Pranju\Bitrix24\Traits\Dumps;
use Pranju\Bitrix24\Traits\GetsDefaultClient;
use Pranju\Bitrix24\Traits\HasStaticMake;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Pranju\Bitrix24\Contracts\Batch as BatchInterface;

/**
 * Class Batch
 * @package Pranju\Bitrix24\Core
 *
 * @method static static make(Command[]|Collection $commands = [], ?Client $client = null, bool $halt = true)
 */
class Batch extends Collection implements BatchInterface
{
    use HasStaticMake, GetsDefaultClient, ConvertsCmd, Dumps;

    /** @const Limit of commands in one batch*/
    public const BATCH_CMD_LIMIT = 50;

    /**
     * Batch constructor.
     *
     * @param Command[]|Collection $items
     * @param Client|null $client
     * @param bool $halt
     */
    public function __construct($items = [], protected ?Client $client = null, protected bool $halt = true)
    {
        parent::__construct($items);

        $this->client ??= $this->getDefaultClient();
    }

    /**
     * @inheritDoc
     */
    public function call(): BatchResponse
    {
        //TODO throw on empty client

        $count = $this->count();

        $response = $this->client->call($this->getMethod(), $this->getData(1), $this);

        if ($count <= static::BATCH_CMD_LIMIT) {
            return $response;
        }

        $pages = ceil($count / self::BATCH_CMD_LIMIT);
        $responses = $response->responses();

        /**
         * If we have more commands than can be executed in a batch
         * we interpolate results ourselves and executes next batches
         */
        for ($i = 2; $i <= $pages; $i++) {

            $data = $this->getData($i);

            $key = array_key_first($data['cmd']);
            try {
                $data['cmd'][$key] = $this->interpolateCommand($responses, $data['cmd'][$key]);
            } catch (Bitrix24Exception $e) {}

            $response = $this->client->call($this->getMethod(), $data, $this);

            $responses = array_merge($responses, $response->responses());
        }

        return new UnlimitedBatchResponse($responses);
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return 'batch';
    }

    /**
     * @param null|int $page Chunk number due to limit
     * @inheritDoc
     * @see Batch::BATCH_CMD_LIMIT
     */
    #[ArrayShape(["halt" => "int", "cmd" => "array"])]
    public function getData(?int $page = null): array
    {
        return [
            "halt" => (int)$this->halt,
            "cmd" => $this->commands($page),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getHalt(): bool
    {
        return $this->halt;
    }

    /**
     * @inheritDoc
     */
    public function getClient(): Client
    {
        //TODO throw on null
        return $this->client;
    }

    /**
     * @inheritDoc
     */
    public function setMethod(string $method): void
    {

    }

    /**
     * @inheritDoc
     */
    public function setData(array $data): void
    {
        if (isset($data['halt'])) {
            $this->setHalt((bool)$data['halt']);
        }

        if (isset($data['cmd']) && is_array($data['cmd'])) {
            $this->items = [];
            $this->push(...array_map(
                fn($command) => is_string($command)
                    ? $this->stringToCommand($command)
                    : (
                        $this->isCommand($command)
                            ? $command
                            : null
                    ),
                $data['cmd']
            ));
        }
    }

    /**
     * @param bool $halt
     */
    public function setHalt(bool $halt): void
    {
        $this->halt = $halt;
    }

    /**
     * @inheritDoc
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * Gets commands array as strings
     *
     * @param int|null $page Chunk number due to limit. Null to retrieve all
     * @return array
     * @see Batch::BATCH_CMD_LIMIT
     */
    protected function commands(?int $page = null): array
    {
        return $this
            ->clear()
            ->when(
                !is_null($page),
                fn(Batch $batch) => $batch->forPage($page, static::BATCH_CMD_LIMIT)
            )
            ->map(fn(Command $cmd) => $cmd->toString())
            ->all();
    }

    /**
     * Removes all not command items
     *
     * @return static
     */
    protected function clear(): static
    {
        return $this
            ->filter(fn($value) => $this->isCommand($value));
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->clear()->count();
    }

    /**
     * @inheritDoc
     */
    public function push(...$values): static
    {
        return parent::push(...array_filter(
            $values,
            fn($value) => $this->isCommand($value)
        ));
    }

    /**
     * @inheritDoc
     */
    public function put($key, $value): static
    {
        if (!$this->isCommand($value)) {
            return $this;
        }

        return parent::put($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($key, $value): void
    {
        if (!$this->isCommand($value)) {
            return;
        }

        parent::offsetSet($key, $value);
    }

    /**
     * Checks if value is valid
     *
     * @param mixed $value
     * @return bool
     */
    #[Pure]
    protected function isCommand(mixed $value): bool
    {
        return $value instanceof Command;
    }

    /**
     * Converts command string to command
     *
     * @param string $command
     * @return Command
     */
    protected function stringToCommand(string $command): Command
    {
        $parts = explode('?', $command);
        parse_str($parts[1] ?? '', $data);

        return Cmd::make($parts[0], $data, $this->getClient());
    }

    /**
     * Interpolates response into command
     *
     * @param Response[] $responses
     * @param string $command
     * @return string
     * @throws Bitrix24Exception
     */
    private function interpolateCommand(array $responses, string $command): string
    {
        $items = preg_match_all('/\$result(\[[a-zA-Z0-9_]*\])*/', $command, $data);

        if (!$items) {
            return $command;
        }

        foreach ($data[0] as $search) {
            $command = $this->interpolateResult($responses, $search, $command);
        }

        return $command;
    }

    /**
     * Interpolates result value
     *
     * @param Response[] $responses
     * @param string $search
     * @param string $command
     * @return string
     * @throws Bitrix24Exception
     */
    private function interpolateResult(array $responses, string $search, string $command): string
    {
        return Str::replace(
            $search,
            $this->getInterpolationValue($responses, $search),
            $command
        );
    }

    /**
     * Retrieves interpolation value for search
     *
     * @param Response[] $responses
     * @param string $search
     * @return string
     * @throws Bitrix24Exception
     */
    private function getInterpolationValue(array $responses, string $search): string
    {
        preg_match_all('/\[([a-zA-Z0-9_]*)/', $search, $data);

        $key = array_shift($data[1]);

        if (!$key || !isset($responses[$key])) {
            throw new Bitrix24Exception("Undefined response '$key' on batch interpolation");
        }

        $value = Arr::get($responses[$key], join('.', $data[1]));

        return is_array($value)
            ? http_build_query($value)
            : $value;
    }
}