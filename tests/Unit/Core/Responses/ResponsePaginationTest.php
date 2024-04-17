<?php

namespace Core\Responses;

use Pranju\Bitrix24\Core\Responses\ResponsePagination;

class ResponsePaginationTest extends \Pranju\Bitrix24\Tests\TestCase
{
    public function testGetMethods(): void
    {
        $pagination = new ResponsePagination(250, 50);

        $this->assertSame(250, $pagination->next(), 'next');
        $this->assertSame(50, $pagination->total(), 'total');
    }
}