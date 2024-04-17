<?php


namespace Pranju\Bitrix24\Tests\Unit\Core;


use Pranju\Bitrix24\Contracts\Repositories\HasDynamicId;
use Pranju\Bitrix24\Core\Client;
use Pranju\Bitrix24\Repositories\Crm\CompanyRepository;
use Pranju\Bitrix24\Repositories\Crm\ContactRepository;
use Pranju\Bitrix24\Repositories\Crm\DealRepository;
use Pranju\Bitrix24\Repositories\Crm\LeadRepository;
use Pranju\Bitrix24\Tests\TestCase;

/**
 * Class CoreTest
 * @package Pranju\Bitrix24\Tests\Core
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
}