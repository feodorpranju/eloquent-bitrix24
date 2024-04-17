<?php


namespace Pranju\Bitrix24\Tests\Unit\Core;


use Pranju\Bitrix24\Core\Client;
use Pranju\Bitrix24\Core\Cmd;
use Pranju\Bitrix24\Core\Responses\Response;
use Pranju\Bitrix24\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;

/**
 * Class CmdTest
 * @package Pranju\Bitrix24\Tests\Unit\Core
 * @tag core
 * @author Fiodor Pranju
 */
class CmdTest extends TestCase
{
    protected string $action = 'test';
    protected array $data = ['test' => true];

    public function testMake(): void
    {
        $client = DB::connection('bitrix24')->getClient();

        $cmd = Cmd::make($this->action, $this->data);
        $cmd2 = new Cmd($this->action, $this->data);

        $this->assertEquals($cmd2, $cmd, '__construct() vs make()');
        $this->assertEquals($this->action, $cmd->getMethod(), 'Get on make set action');
        $this->assertEquals($this->data, $cmd->getData(), 'Get on make set data');
        $this->assertEquals($client, $cmd->getClient(), 'Get set on make client');
        $this->assertEquals($client, Cmd::make('')->getClient(), 'Get automatic set client');
    }

    public function testCall(): void
    {
        $response = ['result' => []];
        $cmd = Cmd::make('test');

        $client = $this->mock(Client::class, function (MockInterface $mock) use ($response, $cmd) {
            $mock->shouldReceive('call')->withArgs(['test', [], $cmd])->andReturn(new Response($response));
        });

        $cmd->setClient($client);

        $this->assertEquals($response, $cmd->call()->toArray(), 'Call client method through cmd');
    }

    public function testSetAction()
    {
        $cmd = Cmd::make('');
        $cmd->setMethod($this->action);

        $this->assertEquals($this->action, $cmd->getMethod(), 'Set action & get action');
    }

    public function testSetData()
    {
        $cmd = Cmd::make('');
        $cmd->setData($this->data);

        $this->assertEquals($this->data, $cmd->getData(), 'Set data & get data');
    }

    public function testSetClient()
    {
        $client = $this->mock(Client::class, function (MockInterface $mock) {});

        $cmd = Cmd::make('test');
        $cmd->setClient($client);

        $this->assertEquals($client, $cmd->getClient(), 'Set client & get client');
    }

    /**
     * @param string $action
     * @param array $data
     * @param string|null $result
     * @dataProvider toStringDataProvider
     */
    public function testToString(string $action, array $data, ?string $result)
    {
        $cmd = Cmd::make($action, $data);
        $result ??= $action.(empty($data) ? "" : "?".http_build_query($data));

        $this->assertEquals($result, (string)$cmd, 'Get command as string');
    }

    public static function toStringDataProvider(): array
    {
        return [
            'empty_data' => ['test', [], 'test'],
            'filled_simple_array' => ['test', ['test' => 'test'], 'test?test=test'],
        ];
    }
}