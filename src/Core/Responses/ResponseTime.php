<?php

namespace Feodorpranju\Eloquent\Bitrix24\Core\Responses;

use Carbon\Carbon;

class ResponseTime implements \Feodorpranju\Eloquent\Bitrix24\Contracts\Responses\ResponseTime
{
    /**
     * @inheritDoc
     */
    public function __construct(
        protected float $start,
        protected float $finish,
        protected string $date_start,
        protected string $date_finish,
        protected ?float $duration = null,
        protected ?float $processing = null,
        protected ?int $operating_reset_at = null,
        protected ?float $operating = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function duration(): float
    {
        return $this->duration;
    }

    /**
     * @inheritDoc
     */
    public function processing(): ?float
    {
        return $this->processing;
    }

    /**
     * @inheritDoc
     */
    public function satrtTime(): float
    {
        return $this->start;
    }

    /**
     * @inheritDoc
     */
    public function finishTime(): float
    {
        return $this->finish;
    }

    /**
     * @inheritDoc
     */
    public function satrtDate(): Carbon
    {
        return Carbon::make($this->date_start);
    }

    /**
     * @inheritDoc
     */
    public function finishDate(): Carbon
    {
        return Carbon::make($this->date_finish);
    }
}