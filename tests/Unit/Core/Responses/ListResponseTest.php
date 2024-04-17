<?php

namespace Core\Responses;

use Pranju\Bitrix24\Contracts\Responses\ResponsePagination;
use Pranju\Bitrix24\Core\Responses\ListResponse;

class ListResponseTest extends \Pranju\Bitrix24\Tests\TestCase
{
    public function testPagination(): void
    {
        $responseArray = [
            'result' => [true],
            'next' => 250,
            'total' => 273,
        ];

        $response = new ListResponse($responseArray);

        $this->assertInstanceOf(ResponsePagination::class, $response->pagination());
    }
}