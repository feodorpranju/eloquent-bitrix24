<?php


namespace Pranju\Bitrix24\Core;


use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Client;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\BatchResponse;
use Pranju\Bitrix24\Contracts\Responses\Response;
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
    use HasStaticMake, GetsDefaultClient, ConvertsCmd;

    public const BATCH_CMD_LIMIT = 50;
    public const LIST_ITEMS_LIMIT = 50;

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
        return $this->client->call($this->getMethod(), $this->getData(), $this);
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return 'batch';
    }

    /**
     * @inheritDoc
     */
    #[ArrayShape(["halt" => "int", "cmd" => "array"])]
    public function getData(): array
    {
        return [
            "halt" => (int)$this->halt,
            "cmd" => $this->commands()
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
     * @return array
     */
    protected function commands(): array
    {
        return $this
            ->filter(fn($value) => $this->isCommand($value))
            ->map(fn(Command $cmd) => (string)$cmd)
            ->slice(0, static::BATCH_CMD_LIMIT)
            ->toArray();
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
     * Removes all commands
     *
     * @return $this
     */
    public function clear(): static
    {
        $this->items = [];
        return $this;
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