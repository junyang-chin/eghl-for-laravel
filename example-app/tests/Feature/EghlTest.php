<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;



class EghlTest extends TestCase
{
    protected function fakeEghlResponse()
    {
        $payload =  [
            "IssuingBank" => "eGHL Test",
            "OrderNumber" => "NEX0009012010325810P",
            "PaymentID" => "NEX0009012010325810P",
            "PymtMethod" => "CC",
            "TransactionType" => "SALE",
            "TxnID" => "AAXNEX0009012010325810P",
            "TxnMessage" => "Transaction Successful",
            "TxnStatus" => 0,
            "Amount" => "100.00",
            "CurrencyCode" => "MYR",
            "AuthCode" => "bla",
            "Param6" => 'ahah',
            "Param7" => 'foobar',
        ];

        $hashValue = env('EGHL_PASSWORD') . $payload["TxnID"] . env('EGHL_SERVICE_ID') . $payload["PaymentID"] . $payload["TxnStatus"] . $payload["Amount"] . $payload["CurrencyCode"] . $payload["AuthCode"];
        $hash = (hash('sha256', $hashValue));

        $hashValue2 = env('EGHL_PASSWORD') . $payload["TxnID"] . env('EGHL_SERVICE_ID') . $payload["PaymentID"] . $payload["TxnStatus"] . $payload["Amount"] . $payload["CurrencyCode"] . $payload["AuthCode"] . $payload['OrderNumber'] . $payload['Param6'] . $payload['Param7'];
        $hash2 = (hash('sha256', $hashValue2));

        $payload["HashValue"] = $hash;
        $payload["HashValue2"] = $hash2;

        return $payload;
    }

    public function test_can_make_payment_request()
    {
        $response = $this->getJson('api/payment');
        $response->assertStatus(302);
    }

    public function test_eghl_server_can_callback()
    {
        $payload = $this->fakeEghlResponse();

        $response = $this->postJson('api/eghl/callback', $payload);
        $response->assertStatus(200);
    }

    public function test_client_browser_can_redirect_to_status_success_page()
    {
        $payload = $this->fakeEghlResponse();

        $response = $this->post('api/eghl/redirect', $payload);
        $response->assertStatus(302)->assertRedirect('https://www.example.com/success');
    }

    public function test_client_browser_can_redirect_to_status__eghl_validate_fail_page()
    {
        $payload = $this->fakeEghlResponse();
        $payload['HashValue'] = 'failhaha';
        $payload['HashValue2'] = 'failhaha';

        $response = $this->post('api/eghl/redirect', $payload);
        $response->assertStatus(302)->assertRedirect('https://www.example.com/eghl-validate-fail');
    }
}
