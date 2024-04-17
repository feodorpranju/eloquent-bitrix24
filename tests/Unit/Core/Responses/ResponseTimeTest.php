<?php

namespace Core\Responses;

use Carbon\Carbon;
use Pranju\Bitrix24\Core\Responses\ResponseTime;
use \Pranju\Bitrix24\Contracts\Responses\ResponseTime as ResponseTimeInterface;

class ResponseTimeTest extends \Pranju\Bitrix24\Tests\TestCase
{
    public function testGetFunctions(): void
    {
        $diff = 12;

        $dateFrom = Carbon::make('2024-02-19T00:42:19+03:00');
        $dateTo = $dateFrom->clone()->addSeconds($diff);
        $time = $this->getTimeResponse($dateFrom, $dateTo);

        $this->assertEquals($diff, $time->duration(), 'duration');
        $this->assertEquals($diff, $time->processing(), 'processing');
        $this->assertEquals($dateFrom->unix(), $time->satrtTime(), 'start time');
        $this->assertEquals($dateTo->unix(), $time->finishTime(), 'finish time');
        $this->assertEquals($dateFrom, $time->satrtDate(), 'start date');
        $this->assertEquals($dateTo, $time->finishDate(), 'finish date');
    }

    protected function getTimeResponse(Carbon $dateFrom, Carbon $dateTo): ResponseTimeInterface
    {
        return new ResponseTime(...[
            "start" => $dateFrom->unix(),
            "finish" => $dateTo->unix(),
            "duration" => $dateFrom->diff($dateTo)->s,
            "processing" => $dateFrom->diff($dateTo)->s,
            "date_start" => $dateFrom->format('c'),
            "date_finish" => $dateTo->format('c'),
            "operating_reset_at" => $dateTo->unix(),
            "operating" => $dateFrom->diff($dateTo)->s,
        ]);
    }
}