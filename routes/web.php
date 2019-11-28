<?php

use App\PagSeguro\PagSeguro;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/checkout/1');
});

Route::get('/checkout/success', function () {
    return 'Pagamento efetuado com sucesso!';
});

Route::get('/checkout/{id}', function ($id) {
    $data = [];
    $data['email'] = 'andersonsrsilva@gmail.com';
    $data['token'] = '93D854144E9E49F198C857080B118DA3';

    $response = (new PagSeguro)->request(PagSeguro::SESSION_SANDBOX, $data);

    $session = new \SimpleXMLElement($response->getContents());
    $session = $session->id;

    $amount = number_format(5.00, 2, '.', '');

    return view('store.checkout', compact('id', 'session', 'amount'));
});

Route::post('/checkout/{id}', function ($id) {
    $data = request()->all();
    unset($data['_token']);

    $data['email'] = 'andersonsrsilva@gmail.com';
    $data['token'] = '93D854144E9E49F198C857080B118DA3';
    $data['paymentMode'] = 'default';
    $data['paymentMethod'] = 'creditCard';
    $data['receiverEmail'] = 'andersonsrsilva@gmail.com';
    $data['currency'] = 'BRL';

    $data['senderAreaCode'] = substr($data['senderPhone'], 0, 2);
    $data['senderPhone'] = substr($data['senderPhone'], 2, strlen($data['senderPhone']));

    $data['creditCardHolderAreaCode'] = substr($data['creditCardHolderPhone'], 0, 2);
    $data['creditCardHolderPhone'] = substr($data['creditCardHolderPhone'], 2, strlen($data['creditCardHolderPhone']));

    //SE FOR CORREIO PEGAR DO FORM
    $data['shippingAddressCity'] = 'A'; 
    $data['shippingAddressState'] = 'GO';
    $data['shippingAddressDistrict'] = 'A';
    $data['shippingAddressPostalCode'] = '00000000';
    $data['shippingAddressNumber'] = 'A';
    $data['shippingAddressStreet'] = 'A';
    $data['shippingAddressComplement'] = 'A';
    $data['shippingAddressCountry'] = 'BR';

    //PREENCHIMENTO AUTO
    $data['billingAddressCity'] = 'A'; 
    $data['billingAddressState'] = 'GO';
    $data['billingAddressDistrict'] = 'A';
    $data['billingAddressPostalCode'] = '00000000';
    $data['billingAddressNumber'] = 'A';
    $data['billingAddressStreet'] = 'A';
    $data['billingAddressComplement'] = 'A';
    $data['billingAddressCountry'] = 'BR';

    $data['installmentValue'] = number_format($data['installmentValue'], 2, '.', '');
    $data['shippingAddressCountry'] = 'BR';
    $data['billingAddressCountry'] = 'BR';

    try {
        $response = (new PagSeguro)->request(PagSeguro::CHECKOUT, $data);
    } catch (\Exception $e) {
        dd($e->getMessage());
    }

    return $data;
    //return ['status'=>'success'];
});
