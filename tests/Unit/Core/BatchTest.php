<?php


namespace Pranju\Bitrix24\Tests\Unit\Core;


use Pranju\Bitrix24\Contracts\Command;
use Pranju\Bitrix24\Core\Batch;
use Pranju\Bitrix24\Core\Client;
use Pranju\Bitrix24\Core\Cmd;
use Pranju\Bitrix24\Core\Responses\BatchResponse;
use Pranju\Bitrix24\Tests\TestCase;
use Mockery\MockInterface;

/**
 * Class BatchTest
 * @package Pranju\Bitrix24\Tests\Unit\Core
 * @tag core
 * @author Fiodor Pranju
 */
class BatchTest extends TestCase
{
    protected static string $action = 'test';
    protected static array $data = ['test' => true];

    /**
     * @param array $commands
     * @dataProvider commandsDataProvider
     */
    public function testMake(array $commands): void
    {
        $batch = Batch::make($commands);

        $count = collect($commands)->filter(
            fn($cmd) => is_object($cmd) && $cmd instanceof Command
        )->count();
        $count = $count > 50 ? 50 : $count;

        $this->assertCount($count, $batch->getData()['cmd'], 'Command count in batch');
    }

    public function testCall(): void
    {
        $response = ['result' => []];
        $halt = true;

        $cmd = Cmd::make('test', ['test' => 'test']);
        $batch = Batch::make([
            $cmd,
            'q1' => $cmd,
        ]);

        $client = $this->mock(Client::class, function (MockInterface $mock) use ($response, $halt, $batch) {
            $mock->shouldReceive('call')->withArgs(['batch', [
                'halt' => (int)$halt,
                'cmd' => [
                    'test?'.http_build_query(['test' => 'test']),
                    'q1' => 'test?'.http_build_query(['test' => 'test'])
                ]
            ], $batch])->andReturn(new BatchResponse($response));
        });

        $batch->setClient($client);

        $this->assertEquals($response, $batch->call()->toArray(), 'Call client method through batch');
    }

    public function testSetClient()
    {
        $client = $this->mock(Client::class, function (MockInterface $mock) {});

        $cmd = Batch::make();
        $cmd->setClient($client);

        $this->assertEquals($client, $cmd->getClient(), 'Set client & get client');
    }

    /**
     * @param array $commands
     * @dataProvider commandsDataProvider
     */
    public function testSetDataCount(array $commands): void
    {
        $batch = Batch::make();

        $batch->setData(['cmd' => $commands]);

        $count = collect($commands)->filter(
            fn($cmd) => (is_object($cmd) && $cmd instanceof Command) || is_string($cmd)
        )->count();
        $count = $count > 50 ? 50 : $count;

        $this->assertCount($count, $batch->getData()['cmd'], 'Command count in batch');
    }

    /**
     * @param string $action
     * @param array $data
     * @param bool|null $halt
     * @dataProvider setDataDataProvider
     */
    public function testSetData(string $action, array $data, ?bool $halt = null)
    {
        $command = Cmd::make($action, $data);
        $commandString = (string)$command;
        $batch = Batch::make();

        $batch->setData(['cmd' => [$commandString]]);

        parse_str(http_build_query($data), $decodedData);

        $this->assertEquals($decodedData, $batch->first()->getData(), 'data in first command set in setData as string');
        $this->assertEquals($action, $batch->first()->getAction(), 'action in first command set in setData as string');
        $this->assertEquals($commandString, $batch->getData()['cmd'][0], 'command string in first command set in setData as string');

        $batch = Batch::make();
        $batch->setData(['cmd' => [$command]]);

        $this->assertSame($data, $batch->first()->getData(), 'data in first command set in setData as object');
        $this->assertEquals($action, $batch->first()->getAction(), 'action in first command set in setData as object');
        $this->assertEquals($commandString, $batch->getData()['cmd'][0], 'command string in first command set in setData as object');

        if (!is_null($halt)) {
            $batch = Batch::make();
            $batch->setData(['halt' => $halt]);

            $this->assertEquals($halt, $batch->getHalt(), 'Check halt set in setData');
        }
    }

    public static function setDataDataProvider(): array
    {
        return [
            'empty_action_and_data' => ['', []],
            'empty_data' => [static::$action, []],
            'empty_action' => ['', static::$data],
            'filled_data' => [
                'test.test',
                [
                    'id' => 1,
                    'order' => ['ID' => 'ASC'],
                    'select' => ['ID', 'UF_*', 'UTM_*', 'PHONE'],
                    'filter' => [
                        'T' => 1,
                        '>T' => '2023-01-01 10:00',
                        '<T' => 45.4,
                        '>=T' => -123,
                        '<=T' => -123.2,
                        'T2' => [1, 3, 6],
                        '=T' => null,
                        '!T' => '',
                        '!=T' => true
                    ]
                ],
            ],
            'halt_true' => ['', [], true],
            'halt_false' => ['', [], false],
        ];
    }

    public static function commandsDataProvider(): array
    {
        $client = Client::make('');
        $cmd = Cmd::make(static::$action, static::$data, $client);

        return [
            'no_cmd' => [[]],
            'one_cmd' => [[$cmd]],
            'two_cmd' => [array_fill(0, 2, $cmd)],
            '50_cmd' => [array_fill(0, 50, $cmd)],
            '51_cmd' => [[array_fill(0, 51, $cmd)]],
            'one_mixed_cmd' => [[1, $cmd, null, '', 1.2, false, []]],
            'two_mixed_cmd' => [[1, $cmd, null, '', 1.2, $cmd, false, []]],
            '50_mixed_cmd' => [array_merge(
                [1, $cmd, null, '', 1.2, $cmd, false, []],
                array_fill(0, 48, $cmd)
            )],
            '51_mixed_cmd' => [array_merge(
                [1, $cmd, null, '', 1.2, $cmd, false, []],
                array_fill(0, 49, $cmd)
            )],
        ];
    }
}