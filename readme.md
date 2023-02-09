# eGHL for Laravel

[eGHL](https://www.ghl.com/e-commerce) is a payment service provider that helps you collect online payments from your customers.

**eGhL for Laravel** is an encapsulated module that simplifies your Laravel app interaction with the eGHL's API.

## Payment Flow and Code Walkthrough

![flowchart](/assets/flowchart.png "flowchart")

You can find in the `example-app` the full example of collecting payment using the Eghl service module including tests.

The core implementations of the this module could be found in `app/Modules/Eghl/Eghl.php` class.

> For eGHL API documentation, please refer to [eGHL Docs](https://drive.google.com/file/d/16cdwph5fhVtXfo1uPYhe3knYSQ6MiC0T/view?usp=sharing).

1. The process begins when the client browser makes a payment request to the backend e.g. amount
   e.g. `GET api/payment`

2. At the backend, we generate the full url with our **payment request parameters** using `processPaymentRequest` method. The url is then returned in the response / redirected.

   > In section 2.1 Payment Request (Merchant System -> Payment Gateway), there are 49 available parameters. The required parameters are marked by a 'Y' in the 'Req?' column.<br><br>The `generateHttpQuery` method contains all of the basic parameters. You may modify it to suit your needs.

   > We do not collect user's payment infomration such as credit card number. Collection and storage of credit card infomration involves strict compliance to [Payment Card Industry Data Security Standard(PCIDSS)](https://www.pcidssguide.com/pci-requirements-for-storing-credit-card-information/) and any applicable local legislation.<br><br>Hence, it is recommended that we let established entities like eGHL / Stripe to handle these information

3. (Scenario 1: Client cancels) The client clicks the cancel button in eGHL's site. The client browser makes a request to **MerchantReturnURL** provided in the **payment request parameters** previously. e.g. `GET api/eghl/redirect`

4. (Scenario 2a: Client completes payment) The client completed the payment with eGHL. The client browser makes a request to **MerchantReturnURL** provided in the **payment request parameters** previously. e.g. `POST api/eghl/redirect`

5. (Scenario 2b: **MerchantCallbackURL** is provided in the payment request paramters) eGHL server makes another separate request to **MerchantCallbackURL**. e.g. `POST api/eghl/callback`

   The is essentially a webhook which does not require a response. The advantage of using this webhook is when the client has poor internet connection / client closes browser halfway. eGHL server could still send the completed transaction data to the backend.

   > Since, the client browser and eGHL server are both making request to the backend. Make sure you do not process your order twice!

6. The three requests above contain a request body which are explained in the eGHL documentation.

   > In section 2.2 Payment Response (Payment Gateway -> Merchant System), there are 30 available parameters. The request body are marked by a 'Y' in the 'Req?' column.

   Before you process the body data, you should validate its authenticity using the `validatePaymentResponse` method. If the validation fails, this means the transaction was tampered / do not belong to you. therefore, should be dealt with appropriately.

   > eGHL recommends validating `HashValue2`. Refer to section 2.13.1.2 for the validation example.

   For client browser requests, you can then return with a view or redirect to a frontend url depending on the payment status.

   > Refer to section 5.2 Payment/Capture Transaction Status

## Prerequisites

1. You are a registered merchant with eGHL.
2. You have your eGHL password and Service ID.
3. You have the eGHL Service URL for staging and production.

## Quickstart

1. Copy `app/Services/Eghl/Eghl.php` into your Laravel app. You can place the file anywhere as you see fit.

2. Setup your `config/service.php` file. Add the following key values to the array

```php
[
   'eghl' => [
        "service_url" => env("EGHL_SERVICE_URL"),
        "password" => env("EGHL_PASSWORD"),
        "transaction_type" => env("EGHL_TRANSACTION_TYPE", 'SALE'),
        "payment_method" => env("EGHL_PAYMENT_METHOD", 'ANY'),
        "service_id" => env("EGHL_SERVICE_ID"),
        "merchant_return_url" => env("EGHL_MERCHANT_RETURN_URL"),
        "currency_code" => env("EGHL_CURRENCY_CODE", 'MYR'),
        "merchant_name" => env("EGHL_MERCHANT_NAME", 'FooBar'),
        "merchant_callback_url" => env("EGHL_MERCHANT_CALLBACK_URL"),
        'language_code' => env('EGHL_LANGUAGE_CODE', 'EN'),
        "page_timeout" => env("EGHL_PAGE_TIMEOUT", 600),
    ],
];
```

3. Setup your `.env` as according to `.env.example`. Or add any other variables you like according to `config/services.php` you configured above.

```
# eghl
EGHL_SERVICE_URL=https://www.example.com
EGHL_PASSWORD=foobar
EGHL_TRANSACTION_TYPE=SALE
EGHL_PAYMENT_METHOD=ANY
EGHL_SERVICE_ID=foobar1234
EGHL_MERCHANT_RETURN_URL=https://www.example.com
EGHL_CURRENCY_CODE=MYR
EGHL_MERCHANT_NAME=FooBar
EGHL_MERCHANT_CALLBACK_URL=https://www.example.com
EGHL_LANGUAGE_CODE=https://www.example.com
EGHL_PAGE_TIMEOUT=600
```

4. Essentially there are only two methods in the Eghl class that you should use i.e.
   `processPaymentRequest(array $data) : string` and `validatePaymentResponse(array $eghlResponse) : bool`

5. To generate a payment request url:

```php
use App\Modules\Eghl;
...
$data = [
            'PaymentID' =>  'payment_003',
            'OrderNumber' => 'order_0001',
            'PaymentDesc' => 'lorem ipsum',
            'Amount' => number_format(69.99, 2),
            'CustName' => 'John Doe',
            'CustEmail' => 'john_doe@example.com',
            'CustPhone' => '010-12345678',
        ];

$url = (new Eghl())->processPaymentRequest($data);

```

6. To validate eGHL payment request response

```php
use App\Modules\Eghl\Eghl;
...
$validated = (new Eghl())->validatePaymentResponse($eghlResponse);

if($validated){
   // your code here
}else{
   // your code here
}
```

7. (Optional) In the `example-app` we provided a [Facade](https://laravel.com/docs/8.x/facades#main-content) to encapsulate the module's implementations. Hence you can invoke the Facade using static methods like this.

```php
use App\Facades\Eghl\Eghl;
...
$url = Eghl::processPaymentRequest($data);

$validated = Eghl::validatePaymentResponse($eghlResponse);
```

## Acknowledgement

This is a modification of [eghl-laravel](https://github.com/killallskywalker/eghl-laravel) by [killallskywalker](https://github.com/killallskywalker)

## License

[MIT](https://github.com/junyang-chin/eghl-for-laravel/blob/main/LICENSE.md)
