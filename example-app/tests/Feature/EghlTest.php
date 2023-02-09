<?php

namespace App\Modules\Eghl;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Eghl
{
    private $password;
    private $serviceId;
    private $merchantName;
    private $serviceUrl;
    private $merchantReturlUrl;
    private $merchantCallbackUrl;
    private $currencyCode;
    private $transactionType;
    private $paymentMethod;
    private $languageCode;
    private $pageTimeout;
    private $transactionTypeEnum = ['SALE', 'AUTH', 'CAPTURE', 'QUERY', 'RSALE', 'REFUND', 'SETTLE'];
    private $paymentMethodEnum = ['CC', 'MO', 'DD', 'WA', 'OTC', 'ANY'];
    private $languageCodeEnum = ['EN', 'MS', 'TH', 'ZH'];
    private $currencyCodeEnum = ['MYR', 'SGD', 'THB', 'CNY', 'PHP'];

    public function __construct()
    {
        $this->serviceUrl = config('services.eghl.service_url');
        $this->password = config('services.eghl.password');
        $this->transactionType = config('services.eghl.transaction_type');
        $this->paymentMethod = config('services.eghl.payment_method');
        $this->serviceId = config('services.eghl.service_id');
        $this->merchantReturlUrl = config('services.eghl.merchant_return_url');
        $this->currencyCode = config('services.eghl.currency_code');
        $this->merchantName = config('services.eghl.merchant_name');
        $this->merchantCallbackUrl = config('services.eghl.merchant_callback_url');
        $this->languageCode = config('services.eghl.language_code');
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
            'CustName' => $data['CustName'],
            'CustEmail' => $data['CustEmail'],
            'CustPhone' => $data['CustPhone'],
            'MerchantCallbackURL' => $this->merchantCallbackUrl,
            'LanguageCode' => $this->languageCode,
            'PageTimeout' => $this->pageTimeout,
        ];

        $validated = $this->paymentRequestValidation($queryParam);

        $validated['HashValue'] = $this->generateHash($validated);

        $httpQuery = http_build_query($validated);

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
     * @param mixed $value 
     * @return array 
     */
    private function paymentRequestValidation($value)
    {
        $validator = Validator::make(
            $value,
            [
                'TransactionType' => [
                    'required',
                    Rule::in($this->transactionTypeEnum),
                ],
                'PymtMethod' => [
                    'required',
                    Rule::in($this->paymentMethodEnum),
                ],
                'ServiceID' => 'required|max:20',
                'PaymentID' => 'required|max:20', // no duplicate payment id
                'OrderNumber' => 'required|max:20', // Non unique , can be same under payment id
                'PaymentDesc' => 'required|max:100',
                'MerchantReturnURL' => 'required',
                'Amount' => 'required|numeric|gt:0',
                'CurrencyCode' => [
                    'required',
                    Rule::in($this->currencyCodeEnum),
                ],
                'CustName' => 'required|max:50',
                'CustEmail' => 'required|max:60',
                'CustPhone' => 'required|max:25',
                'B4TaxAmt' => 'sometimes|numeric|gt:0',
                'TaxAmt' => 'sometimes|numeric|gt:0',
                'MerchantName' => 'sometimes|max:25',
                'MerchantCallbackURL' => 'sometimes',
                'LanguageCode' => [
                    'sometimes',
                    Rule::in($this->languageCodeEnum),
                ],
                'PageTimeout' => 'sometimes|numeric|max:900',
            ]
        );

        if ($validator->fails()) {
            throw new \ErrorException($validator->errors());
        }

        return $validator->validated();
    }
}
