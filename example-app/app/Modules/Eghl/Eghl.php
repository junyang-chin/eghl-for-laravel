<?php

namespace App\Modules\Eghl;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class Eghl
{
    private $password;
    private $serviceId;
    private $merchantName;
    private $serviceUrl;
    private $merchantReturlUrl;
    private $merchantApprovalUrl;
    private $merchantCallbackUrl;
    private $merchantUnApprovalUrl;
    private $currencyCode;
    private $transactionType;
    private $paymentMethod;
    private $pageTimeout;

    public function __construct()
    {
        $this->password = config('services.eghl.password');
        $this->serviceId = config('services.eghl.service_id');
        $this->merchantName = config('services.eghl.merchant_name');
        $this->serviceUrl = config('services.eghl.service_url');
        $this->merchantReturlUrl = config('services.eghl.merchant_return_url');
        $this->merchantApprovalUrl = config('services.eghl.merchant_approval_url');
        $this->merchantUnApprovalUrl = config('services.eghl.merchant_unapproval_url');
        $this->merchantCallbackUrl = config('services.eghl.merchant_callback_url');
        $this->currencyCode = config('services.eghl.currency_code');
        $this->pageTimeout = config('services.eghl.page_timeout');
        $this->transactionType = config('services.eghl.transaction_type');
        $this->paymentMethod = config('services.eghl.payment_method');
        $this->pageTimeout = config('services.eghl.page_timeout');
    }

    /**
     * Get the hashing value
     *
     * @param array]
     *
     * @return string
     */
    public function generateHash(array $value)
    {
        $this->paymentRequestValidation($value);

        // refer eGHL-API-VER2.9q.pdf 2.13.1.1 Payment Request Hashing
        $param = $this->password . $this->serviceId . $value['PaymentID'] . $this->merchantReturlUrl . $this->merchantCallbackUrl . $value['Amount'] . $this->currencyCode . $this->pageTimeout;

        return hash('sha256', $param);
    }

    public function generateHttpQuery($data)
    {
        // refer eGHL-API-VER2.9q.pdf 2.1 Payment Request (Merchant System → Payment Gateway)
        $queryParam = [
            'TransactionType' => $this->transactionType,
            'PymtMethod' => $this->paymentMethod,
            'ServiceID' => $this->serviceId,
            'PaymentID' =>  $data['PaymentID'],
            'OrderNumber' => $data['OrderNumber'],
            'PaymentDesc' => $data['PaymentDesc'],
            'MerchantName' => $this->merchantName,
            'MerchantReturnURL' => $this->merchantReturlUrl,
            'Amount' => $data["Amount"],
            'CurrencyCode' => $this->currencyCode,
            'HashValue' => $this->generateHash($data),
            'CustName' => $data['CustName'],
            'CustEmail' => $data['CustEmail'],
            'CustPhone' => $data['CustPhone'],
            'MerchantCallbackURL' => $this->merchantCallbackUrl,
            'LanguageCode' => 'en',
            'PageTimeout' => $this->pageTimeout,
        ];

        $httpQuery = http_build_query($queryParam);

        return $httpQuery;
    }

    /**
     * Process eghl payment for payment request
     *
     *
     * @return string url payment page
     */
    public function processPaymentRequest($data)
    {
        $url = $this->serviceUrl . $this->generateHttpQuery($data);

        return $url;
    }

    /**
     *
     * Validate payment response
     *
     *
     * @return boolean
     */
    public function validatePaymentResponse($data)
    {
        // refer eGHL-API-VER2.9q.pdf 2.13.1.2 Payment/Query/Reversal/Capture/Refund Response Hashing
        $string = $this->password . $data["TxnID"] . $this->serviceId . $data["PaymentID"] . $data["TxnStatus"] . $data["Amount"] . $data["CurrencyCode"] . $data["AuthCode"] . $data['OrderNumber'] . $data['Param6'] . $data['Param7'];

        $hash = (hash('sha256', $string));

        if ($hash === $data["HashValue2"]) {
            return true;
        }

        return false;
    }

    /**
     * Validate array value that require to process payment
     *
     * @param array []
     *
     * @return \ErrorException
     */
    private function paymentRequestValidation($value)
    {
        $validator = Validator::make(
            $value,
            [
                'PaymentID' => 'required|max:20', // no duplicate payment id
                'OrderNumber' => 'required|max:20', // Non unique , can be same under payment id
                'PaymentDesc' => 'required|max:100',
                'Amount' => 'required|numeric|gt:0',
                'CustIP' => 'sometimes|max:20',
                'CustName' => 'required|max:50',
                'CustEmail' => 'required|max:60',
                'CustPhone' => 'required|max:25',
                'B4TaxAmt' => 'sometimes|numeric|gt:0',
                'TaxAmt' => 'sometimes|numeric|gt:0',
                'MerchantName' => 'sometimes|max:25',
                'CustMAC' => 'sometimes|max:50',
                'LanguageCode' => 'sometimes|max:2',
                'PageTimeout' => 'sometimes|max:4',
            ],
            [
                "PaymentID.required" => "Payment ID Required!",
            ]
        );

        if ($validator->fails())
            throw new \ErrorException($validator->errors()->first());
    }
}
