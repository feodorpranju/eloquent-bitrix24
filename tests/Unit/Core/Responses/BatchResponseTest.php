<?php

namespace Core\Responses;

use Pranju\Bitrix24\Core\Responses\BatchResponse;
use \Generator;

class BatchResponseTest extends \Pranju\Bitrix24\Tests\TestCase
{
    /**
     * @param array $response
     * @param array $classes
     * @return void
     * @dataProvider ResponseClassesDataProvider
     */
    public function testResponseClasses(array $result, array $classes): void
    {



    }

    public static function ResponseClassesDataProvider(): Generator
    {
//        yield 'one_response' => [[], []];
    }

    public function testResponseContents(): void
    {

    }

    public function testResponseTime(): void
    {

    }

    public function testResponsePagination(): void
    {

    }
}