<?php

namespace Feodorpranju\Eloquent\Bitrix24\Contracts\Responses;

use Carbon\Carbon;

interface ResponseTime
{
    /**
     * @param float $start Start time in seconds (UNIX)
     * @param float $finish Finish time in seconds (UNIX)
     * @param string $date_start Start date string (e. g. ISO 8601)
     * @param string $date_finish Finish date string (e. g. ISO 8601)
     * @param float|null $duration Executing duration in seconds
     * @param float|null $processing Processing duration in seconds
     * @param int|null $operating_reset_at Time operating was reset at
     * @param float|null $operating Operating time
     */
    public function __construct(
        float $start,
        float $finish,
        string $date_start,
        string $date_finish,
        ?float $duration = null,
        ?float $processing = null,
        ?int $operating_reset_at = null,
        ?float $operating = null,
        //TODO add request send time and get answer time to log it in future
    );

    /**
     * Get operation duration on remote server
     *
     * @return float
     */
    public function duration(): float;

    /**
     * Get operation processing time
     *
     * @return float|null
     */
    public function processing(): ?float;

    /**
     * Get operation remote start time in seconds (UNIX)
     *
     * @return float
     */
    public function satrtTime(): float;

    /**
     * Get operation remote finish time in seconds (UNIX)
     *
     * @return float
     */
    public function finishTime(): float;

    /**
     * Get operation remote start DatetTime
     *
     * @return Carbon
     */
    public function satrtDate(): Carbon;

    /**
     * Get operation remote finish DatetTime
     *
     * @return Carbon
     */
    public function finishDate(): Carbon;
}