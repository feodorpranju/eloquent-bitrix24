<?php

namespace Factories;

use Pranju\Bitrix24\Bitrix24Exception;
use Pranju\Bitrix24\Contracts\Repositories\HasDynamicId;
use Pranju\Bitrix24\Core\Client;
use Pranju\Bitrix24\Factories\RepositoryFactory;
use Pranju\Bitrix24\Repositories\Crm\CompanyRepository;
use Pranju\Bitrix24\Repositories\Crm\ContactRepository;
use Pranju\Bitrix24\Repositories\Crm\DealRepository;
use Pranju\Bitrix24\Repositories\Crm\LeadRepository;
use Pranju\Bitrix24\Tests\TestCase;

class RepositoryFactoryTest extends TestCase
{
    /**
     * @param string $table
     * @param string $class
     * @param int|null $dynamicId
     * @dataProvider repositoryDataProvider
     * @throws Bitrix24Exception
     */
    public function testFactory(string $table, string $class, ?int $dynamicId = null): void
    {
        $repository = (new RepositoryFactory(Client::make('')))->make($table);

        $this->assertInstanceOf($class, $repository, 'Get repository for table');

        if ($repository instanceof HasDynamicId) {
            $this->assertEquals($dynamicId, $repository->getDynamicId());
        }
    }

    /**
     * @return array
     */
    public static function repositoryDataProvider(): array
    {
        return [
            'crm_lead' => ['crm_lead', LeadRepository::class, 1],
            'crm_deal' => ['crm_deal', DealRepository::class, 2],
            'crm_contact' => ['crm_contact', ContactRepository::class, 3],
            'crm_company' => ['crm_company', CompanyRepository::class, 4],
        ];
    }
}