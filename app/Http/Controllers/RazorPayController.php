<?php


/**
 * File name: RazorPayController.php
 * Last modified: 2020.06.13 at 12:38:51
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers;

use App\Models\DeliveryAddress;
use App\Models\Payment;
use App\Models\CouponHistory;
use App\Models\User;
use App\Repositories\DeliveryAddressRepository;
use App\Repositories\MarketRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Flash;
use Razorpay\Api\Api;

class RazorPayController extends ParentOrderController
{

    /**
     * @var Api
     */
    private $couponCode;
    private $api;
    private $currency;
    /** @var DeliveryAddressRepository
     *
     */
    private $deliveryAddressRepo;

    public function __init()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
        $this->currency = setting('default_currency_code', 'INR');
        $this->deliveryAddressRepo = new DeliveryAddressRepository(app());
        $this->marketRepository = new MarketRepository(app());
        $this->productRepository = new ProductRepository(app());
    }


    public function index()
    {
        return view('welcome');
    }


    public function checkout(Request $request)
    {
        try{
            $this->couponCode = "";

            $user = $this->userRepository->findByField('api_token', $request->get('api_token'))->first();

            /**
             * Check Coupon Code Used 
             * */
            $coupon_code = $request->get('coupon_code');
            $customer_id = $user->id;
            $history = CouponHistory::where('coupon_code', $coupon_code)
                        ->where('customer_id', $customer_id)
                        ->get();
            
            if (count($history) > 0) {
                $coupon = [];
            } else {
                $coupon = $this->couponRepository->findByField('code', $coupon_code)->first();
                $this->couponCode = $request->get('coupon_code');
            }
            /* ------------------- */


            $deliveryId = $request->get('delivery_address_id');
            $deliveryAddress = $this->deliveryAddressRepo->findWithoutFail($deliveryId);
            if (!empty($user)) {
                $this->order->user = $user;
                $this->order->user_id = $user->id;
                $this->order->delivery_address_id = $deliveryId;
                $this->coupon = $coupon;
                $razorPayCart = $this->getOrderData();

                $razorPayOrder = $this->api->order->create($razorPayCart);
                $fields = $this->getRazorPayFields($razorPayOrder, $user, $deliveryAddress);
                //url-ify the data for the POST
                $fields_string = http_build_query($fields);
					
				/* --------- Market Working Time --------- */

                date_default_timezone_set('Asia/Calcutta');
                $curTime = date('H:i');
                $marketId  = $this->order->user->cart[0]->product->market->id;
                $market = $this->marketRepository->findWithoutFail($marketId);
                $allowFlag = 1;
                if($market["market_open_time"] != '' && $market["market_open_time"] > $curTime) $allowFlag = 0;
                if($market["market_close_time"] != '' && $market["market_close_time"] < $curTime) $allowFlag = 0;
                
                if($allowFlag == 0){
                    $result="";
                    $result .= $allowFlag;
                    $result.=",";
                    $result .= $market['market_open_time'];
                    $result.=",";
                    $result .= $market['market_close_time'];
                    $result.=",";
                    $result .= $curTime;
                    $result.=",";
                    return redirect(url('payments.failed' . json_encode($result)));
                }
				else {
					//open connection
					$ch = curl_init();

					//set the url, number of POST vars, POST data
					curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/checkout/embedded');
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
					$result = curl_exec($ch);
					if($result === true){
						die();
					}
				}
            }else{
                Flash::error("Error processing RazorPay user not found");
                return redirect(url('payments.failed'));
            }
        }catch (\Exception $e){
            Flash::error("Error processing RazorPay payment for your order :" . $e->getMessage());
            return redirect(url('payments.failed'));
        }
    }


    /**
     * @param int $userId
     * @param int $deliveryAddressId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function paySuccess(int $userId, int $deliveryAddressId = 0,string $couponCode = '', Request $request)
    {
        $data = $request->all();
        $description = $this->getPaymentDescription($data);

        $this->order->user_id = $userId;
        $this->order->user = $this->userRepository->findWithoutFail($userId);
        $this->coupon = $this->couponRepository->findByField('code', $couponCode)->first();
        $this->order->delivery_address_id = $deliveryAddressId;

        if ($this->couponcode != "") {
            $couponHistory = new CouponHistory;
            $couponHistory->coupon_code = $this->couponcode;
            $couponHistory->customer_id = auth()->id();
            $couponHistory->save();
        }

        if ($request->hasAny(['razorpay_payment_id','razorpay_signature'])) {

            $this->order->payment = new Payment();
            $this->order->payment->status = trans('lang.order_paid');
            $this->order->payment->method = 'RazorPay';
            $this->order->payment->description = $description;

            $this->createOrder();

            return redirect(url('payments/razorpay'));
        }else{
            Flash::error("Error processing RazorPay payment for your order");
            return redirect(url('payments.failed'));
        }

    }

    /**
     * Set cart data for processing payment on PayPal.
     *
     *
     * @return array
     */
    private function getOrderData()
    {
        $data = [];
        $this->calculateTotal();
        $amountINR = $this->total;
        if ($this->currency !== 'INR') {
            $url = "https://api.exchangeratesapi.io/latest?symbols=$this->currency&base=INR";
            $exchange = json_decode(file_get_contents($url), true);
            $amountINR =  $this->total / $exchange['rates'][$this->currency];
        }
        $order_id = $this->paymentRepository->all()->count() + 1;
        $data['amount'] = (int)($amountINR * 100);
        $data['payment_capture'] = 1;
        $data['currency'] = 'INR';
        $data['receipt'] = $order_id . '_' . date("Y_m_d_h_i_sa");

        return $data;
    }

    /**
     * @param $razorPayOrder
     * @param User $user
     * @param DeliveryAddress $deliveryAddress
     * @return array
     */
    private function getRazorPayFields($razorPayOrder, User $user, DeliveryAddress $deliveryAddress): array
    {
        $market = $this->order->user->cart[0]->product->market;

        $fields = array(
            'key_id' => config('services.razorpay.key', ''),
            'order_id' => $razorPayOrder['id'],
            'name' => $market->name,
            'description' => count($this->order->user->cart) ." items",
            'image' => $this->order->user->cart[0]->product->market->getFirstMedia('image')->getUrl('thumb'),
            'prefill' => [
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->custom_fields['phone']['value'],
            ],
            'callback_url' => url('payments/razorpay/pay-success',['user_id'=>$user->id,'delivery_address_id'=>$deliveryAddress->id]),

        );

        if (isset($this->coupon)){
            $fields['callback_url'] = url('payments/razorpay/pay-success',['user_id'=>$user->id,'delivery_address_id'=>$deliveryAddress->id, 'coupon_code' => $this->coupon->code]);
        }

        if (!empty($deliveryAddress)) {
            $fields ['notes'] = [
                'delivery_address' => $deliveryAddress->address,
            ];
        }

        if ($this->currency !== 'INR') {
            $fields['display_amount'] = $this->total;
            $fields['display_currency'] = $this->currency;
        }
        return $fields;
    }

    /**
     * @param array $data
     * @return string
     */
    private function getPaymentDescription(array $data): string
    {
        $description = "Id: " . $data['razorpay_payment_id'] . "</br>";
        $description .= trans('lang.order').": " . $data['razorpay_order_id'];
        return $description;
    }

}
