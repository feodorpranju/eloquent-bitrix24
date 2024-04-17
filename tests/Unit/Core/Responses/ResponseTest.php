<?php

namespace Core\Responses;

use Pranju\Bitrix24\Core\Responses\Response;
use Pranju\Bitrix24\Core\Responses\ResponseTime;
use Pranju\Bitrix24\Tests\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @return void
     */
    public function testArrayAccessible(): void
    {
        $responseArray = [
            'result' => [true],
            'time' => [
                'duration' => 0.123
            ]
        ];

        $response = new Response($responseArray);

        $this->assertSame($responseArray['result'], $response['result']);
        $this->assertSame($responseArray['time'], $response['time']);
        $this->assertSame($responseArray, $response->toArray());
    }

    /**
     * @return void
     */
    public function testTime(): void
    {
        $responseArray = [
            'result' => [true],
            'time' => [
                "start" => 1708292539.6627,
                "finish" => 1708292539.7105,
                "duration" => 0.047799110412598,
                "processing" => 5.2928924560547E-5,
                "date_start" => "2024-02-19T00:42:19+03:00",
                "date_finish" => "2024-02-19T00:42:19+03:00",
                "operating_reset_at" => 1708293139,
                "operating" => 0,
            ]
        ];

        $response = new Response($responseArray);

        $this->assertInstanceOf(ResponseTime::class, $response->time());
    }
}