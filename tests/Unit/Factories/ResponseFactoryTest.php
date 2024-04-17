<?php

namespace Factories;

use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;
use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Contracts\Responses\BatchResponse;
use Pranju\Bitrix24\Contracts\Responses\ListResponse;
use Pranju\Bitrix24\Contracts\Responses\Response;
use Pranju\Bitrix24\Core\Batch;
use Pranju\Bitrix24\Core\Client;
use Pranju\Bitrix24\Core\Cmd;
use Pranju\Bitrix24\Factories\ResponseFactory;
use Pranju\Bitrix24\Tests\TestCase;
use \Illuminate\Http\Client\Response as HttpResponse;

class ResponseFactoryTest extends TestCase
{
    /**
     * @param Command $command
     * @param string $class
     * @return void
     * @dataProvider responseDataProvider
     */
    public function testFactory(Command $command, string $class, ?HttpResponse $response = null): void
    {
        $this->assertInstanceOf(
            $class,
            ResponseFactory::make(
                $response ?? $this->mock(HttpResponse::class, function (MockInterface $mock) {
                    $mock->shouldReceive('json')->andReturn(false);
                }),
                $command
            ),
        );
    }

    /**
     * @return array
     */
    public static function responseDataProvider(): array
    {
        $client = Client::make('');

        return [
            'basic' => [Cmd::make('crm.lead.get', [], $client), Response::class],
            'list' => [Cmd::make('crm.lead.list', [], $client), ListResponse::class],
            'list_by_total' => [
                Cmd::make('user.get', [], $client),
                ListResponse::class,
                Mockery::mock(HttpResponse::class, function (MockInterface $mock) {
                    $mock->shouldReceive('json')->withArgs(['total', false])->andReturn(45);
                }),
            ],
            'batch_basic' => [Cmd::make('batch', [], $client), BatchResponse::class],
            'batch_command' => [Batch::make([], $client), BatchResponse::class],
        ];
    }
}