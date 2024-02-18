<?php


namespace Feodorpranju\Eloquent\Bitrix24\Core;


use Feodorpranju\Eloquent\Bitrix24\Contracts\Client;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Command;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\BatchResponse;
use Feodorpranju\Eloquent\Bitrix24\Traits\GetsDefaultClient;
use Feodorpranju\Eloquent\Bitrix24\Traits\HasStaticMake;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Feodorpranju\Eloquent\Bitrix24\Contracts\Batch as BatchInterface;

/**
 * Class Batch
 * @package Feodorpranju\Eloquent\Bitrix24\Core
 *
 * @method static static make(Command[]|Collection $commands = [], ?Client $client = null, bool $halt = true)
 */
class Batch extends Collection implements BatchInterface
{
    use HasStaticMake, GetsDefaultClient;

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
        return $this->client->call($this->getAction(), $this->getData(), $this);
    }

    /**
     * @inheritDoc
     */
    public function getAction(): string
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
    public function setAction(string $action): void
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
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->getAction().(empty($this->getData()) ? "" : "?".http_build_query($this->getData()));
    }

    public function push(...$values): static
    {
        return parent::push(...array_filter(
            $values,
            fn($value) => $this->isCommand($value)
        ));
    }

    public function put($key, $value): static
    {
        if (!$this->isCommand($value)) {
            return $this;
        }

        return parent::put($key, $value);
    }

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
}