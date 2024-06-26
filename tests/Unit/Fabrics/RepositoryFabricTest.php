<?php

namespace Fabrics;

use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Repositories\HasDynamicId;
use Pranju\Bitrix24\Core\Client;
use Pranju\Bitrix24\Fabrics\RepositoryFabric;
use Pranju\Bitrix24\Repositories\Crm\CompanyRepository;
use Pranju\Bitrix24\Repositories\Crm\ContactRepository;
use Pranju\Bitrix24\Repositories\Crm\DealRepository;
use Pranju\Bitrix24\Repositories\Crm\ItemRepository;
use Pranju\Bitrix24\Repositories\Crm\LeadRepository;
use Pranju\Bitrix24\Repositories\Department\DepartmentRepository;
use Pranju\Bitrix24\Repositories\User\UserRepository;
use Pranju\Bitrix24\Tests\TestCase;

class RepositoryFabricTest extends TestCase
{
    /**
     * @param string $table
     * @param string $class
     * @param int|null $dynamicId
     * @dataProvider getMakeDataProvider
     * @throws Bitrix24Exception
     */
    public function testMake(string $table, string $class, ?int $dynamicId = null): void
    {
        $repository = (new RepositoryFabric(Client::make('')))->make($table);

        $this->assertInstanceOf($class, $repository, 'Get repository for table');

        if ($repository instanceof HasDynamicId) {
            $this->assertEquals($dynamicId, $repository->getDynamicId());
        }
    }

    /**
     * @return void
     */
    public function testMakeExceptions(): void
    {
        $this->assertThrows(
            fn() => $this->getFactory()->make('test_table'),
            Bitrix24Exception::class,
            "Undefined repository 'Pranju\Bitrix24\Repositories\Test\TableRepository' for 'test_table' table"
        );
    }

    /**
     * @return void
     * @throws Bitrix24Exception
     */
    public function testMakeCached(): void
    {
        $factory = $this->getFactory();
        $first = $factory->make('crm_lead');
        $second = $factory->make('crm_lead');
        $third = $this->getFactory()->make('crm_lead');

        $this->assertSame($first, $second);
        $this->assertNotSame($first, $third);
    }

    /**
     * @return array
     */
    public static function getMakeDataProvider(): array
    {
        return [
            'crm_lead' => ['crm_lead', LeadRepository::class, 1],
            'crm_deal' => ['crm_deal', DealRepository::class, 2],
            'crm_contact' => ['crm_contact', ContactRepository::class, 3],
            'crm_company' => ['crm_company', CompanyRepository::class, 4],
            'invoice' => ['crm_item_31', ItemRepository::class, 31],
            'user' => ['user', UserRepository::class],
            'department' => ['department', DepartmentRepository::class],
        ];
    }

    public function getFactory(): RepositoryFabric
    {
        return new RepositoryFabric(Client::make(''));
    }
}