<?php


namespace Feodorpranju\Eloquent\Bitrix24\Tests\Unit\Core;


use Feodorpranju\Eloquent\Bitrix24\Core\Client;
use Feodorpranju\Eloquent\Bitrix24\Scopes\Crm\Company;
use Feodorpranju\Eloquent\Bitrix24\Scopes\Crm\Contact;
use Feodorpranju\Eloquent\Bitrix24\Scopes\Crm\Deal;
use Feodorpranju\Eloquent\Bitrix24\Scopes\Crm\Item;
use Feodorpranju\Eloquent\Bitrix24\Scopes\Crm\Lead;
use Feodorpranju\Eloquent\Bitrix24\Tests\TestCase;
use Illuminate\Support\Facades\DB;

/**
 * Class CoreTest
 * @package Feodorpranju\Eloquent\Bitrix24\Tests\Core
 *
 * @tag core
 * @author Fiodor Pranju
 */
class ClientTest extends TestCase
{
    /**
     * @return array[]
     */
    public static function callDataProvider(): array
    {
        return [
            'test' => ['test', []]
        ];
    }

    /**
     * @param string $collection
     * @param string $class
     * @param int|null $dynamicId
     * @dataProvider getScopeDataProvider
     */
    public function testGetScope(string $collection, string $class, ?int $dynamicId = null): void
    {
        $scope = DB::connection('bitrix24')->getClient()->getScope($collection);

        $this->assertInstanceOf($class, $scope, 'Get scope for collection');
        $this->assertEquals($collection, $scope->getCollection(), 'Scope contains correct collection name');

        if ($class === Item::class) {
            $this->assertEquals($dynamicId, $scope->getDynamicId());
        }
    }

    /**
     * @return array
     */
    public static function getScopeDataProvider(): array
    {
        return [
            'crm.lead' => ['crm.lead', Lead::class, 1],
            'crm.deal' => ['crm.deal', Deal::class, 2],
            'crm.contact' => ['crm.contact', Contact::class, 3],
            'crm.company' => ['crm.company', Company::class, 4],
            'crm.item.31' => ['crm.company', Company::class, 31],
        ];
    }
}