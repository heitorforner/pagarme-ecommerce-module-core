<?php

namespace Mundipagg\Core\Test\Recurrence\Services;

use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Recurrence\Factories\ChargeFactory;
use Mundipagg\Core\Recurrence\Repositories\ChargeRepository;
use Mundipagg\Core\Recurrence\Services\InvoiceService;
use PHPUnit\Framework\TestCase;
use Mundipagg\Core\Test\Mock\Concrete\PlatformCoreSetup;

class Invoice extends TestCase
{
    /**
     * @var InvoiceService
     */
    protected $service;

    public function setUp()
    {
        $this->service = \Mockery::mock(InvoiceService::class)->makePartial();

        $logMock = \Mockery::mock(LogService::class);
        $logMock->shouldReceive('info')->andReturn(true);
        $this->service->shouldReceive('getLogService')->andReturn($logMock);

        PlatformCoreSetup::bootstrap();

        $this->insertCharge();
        $this->insertCanceledCharge();

        parent::setUp();
    }

    public function testCancelShouldNotReturnAnError()
    {
        $apiMock = \Mockery::mock(APIService::class);

        $apiMock->shouldReceive('cancelInvoice')->andReturnTrue();

        $this->service->shouldReceive('getApiService')->andReturn($apiMock);

        $return = $this->service->cancel('in_1234567890123456');

        $expected = [
            "message" => 'Invoice canceled successfully',
            "code" => 200
        ];

        $this->assertEquals($return, $expected);
    }

    public function testCancelShouldReturnAnErrorMessage()
    {
        $this->expectException(\Exception::class);
        $this->service->cancel('in_1234567890123458');//Not found invoice id
    }

    public function testAlreadyCanceledInvoiceSholudReturnAMessage()
    {
        $apiMock = \Mockery::mock(APIService::class);

        $apiMock->shouldReceive('cancelInvoice')->andReturnTrue();

        $this->service->shouldReceive('getApiService')->andReturn($apiMock);
        $return = $this->service->cancel('in_1234567890123457');//Invoice already canceled

        $expected = [
            "message" => 'Invoice already canceled',
            "code" => 200
        ];

        $this->assertEquals($return, $expected);
    }

    private function insertCharge()
    {
        $charge = [
            "id" => null,
            "mundipagg_id" => "ch_1234567890123456",
            "subscription_id" => "sub_1234567890123456",
            "invoice_id" => "in_1234567890123456",
            "code" => "123",
            "amount" => 500,
            "paid_amount" => 0,
            "canceled_amount" => 0,
            "refunded_amount" => 0,
            "status" => 'pending',
            "payment_method" => "credit_card",
            "cycle_start" => "2020-01-01",
            "cycle_end" => "2020-02-01",
        ];

        $this->saveCharge($charge);
    }

    private function insertCanceledCharge()
    {
        $charge = [
            "id" => null,
            "mundipagg_id" => "ch_1234567890123457",
            "subscription_id" => "sub_1234567890123457",
            "invoice_id" => "in_1234567890123457",
            "code" => "123",
            "amount" => 500,
            "paid_amount" => 0,
            "canceled_amount" => 0,
            "refunded_amount" => 0,
            "status" => 'canceled',
            "payment_method" => "credit_card",
            "cycle_start" => "2020-01-01",
            "cycle_end" => "2020-02-01",
        ];

        $this->saveCharge($charge);
    }

    private function saveCharge($charge)
    {
        $chargeFactory = new ChargeFactory();
        $charge = $chargeFactory->createFromDbData($charge);

        $repo = new ChargeRepository();
        $repo->save($charge);
    }
}