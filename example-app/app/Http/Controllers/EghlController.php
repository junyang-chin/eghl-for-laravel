<?php

namespace App\Http\Controllers;

use App\Facades\Eghl;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EghlController extends Controller
{
    //
    public function generatePaymentUrl()
    {
        // Your logic here. Modify the $data array according to your needs

        $data = [
            'PaymentID' =>  'payment_003',
            'OrderNumber' => 'order_0001',
            'PaymentDesc' => 'lorem ipsum',
            'Amount' => number_format(69.99, 2),
            'CustName' => 'John Doe',
            'CustEmail' => 'john_doe@example.com',
            'CustPhone' => '010-12345678',
        ];

        $paymentUrl = Eghl::processPaymentRequest($data);

        return redirect()->away($paymentUrl);
        // or
        // return response()->json(['payment_url' => $paymentUrl], 200);
    }

    /**
     * 
     * @param Request $data 
     * @return Response|ResponseFactory|void 
     */
    public function callback(Request $data)
    {
        $validated = Eghl::validatePaymentResponse($data);

        if ($validated) {
            // Your logic here

            return response('OK', 200);
        }

        // do nothing if fail
    }


    public function redirect(Request $data)
    {
        $validated = Eghl::validatePaymentResponse($data);

        if ($validated) {
            // Your logic here

            /**
             * Refer eGHL-API-Ver 2.9q.pdf section 5.2 Payment/Capture Transaction Status 
             * Success = 0
             * Fail = 1
             * Pending = 2
             */
            if ($data['TxnStatus'] == 0) {
                $url = 'https://www.example.com/success';
            } else {
                $url = 'https://www.example.com/fail';
            }
        } else {

            // if validation fail
            $url = 'https://www.example.com/eghl-validate-fail';
        }

        return redirect()->away($url);
    }
}
