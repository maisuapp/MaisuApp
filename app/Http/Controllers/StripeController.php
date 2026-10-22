<?php

namespace App\Http\Controllers;

use App\Classes\NotificationClass;
use App\model\CouponCodeDetail;
use App\model\CouponDetails;
use App\model\Provider;
use App\model\ServiceCategory;
use App\model\TempUserBooking;
use App\model\User;
use App\model\UserPackageBooking;
use App\model\UserPurchaseCouponDetails;
use App\model\UserWalletTransaction;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeController extends Controller
{
    private $coupon_deals = [3];
    private $on_demand_service_id_array;
    private $settings;

    public function __construct() {
        $service_cat = ServiceCategory::query()->whereIN('category_type', [3, 4])->where('status', 1)->get()->pluck('id')->toArray();
        $this->on_demand_service_id_array = $service_cat;
        $this->settings = request()->get("general_settings");
    }

    private function getStripeApiKey()
    {
        $settings = request()->get("general_settings");
        $key = null;
        if ($settings) {
            $key = ($settings->is_stripe_sandbox == 1) ? $settings->stripe_key : $settings->stripe_sandbox_key;
        }
        if (empty($key)) {
            $key = config('services.stripe.secret') ?: env('STRIPE_SECRET');
        }
        return $key;
    }

    public function paymentStripe($amount,$currency,$name,$order_no)
    {
        $key = $this->getStripeApiKey();
        Stripe::setApiKey($key);

        if($currency == "JPY"){
            $amount = round($amount);
            $amount =$amount/100;
        }

        try {
            $checkout_session = Session::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            "name" => $name
                        ],
                        'unit_amount'=>$amount * 100
                    ],
                    'quantity' => 1,
                ]],
                'client_reference_id'=>$order_no,
                'mode' => 'payment',
                'success_url' => url('/stripe-success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => route('stripe.fail',['id'=>$order_no]),
            ]);
            $response = [
                "redirect_url"=> $checkout_session->url,
            ];
            return json_encode($response);
        }catch (\Exception $e){
            return response()->json([
                'status' => 0,
                'message'=> $e->getMessage(),
                "message_code" => 9,
            ]);
        }
    }

    public function stripeSuccess(Request $request)
    {
        $key = $this->getStripeApiKey();
        Stripe::setApiKey($key);
        $sessionId = $request->get('session_id');

        $session = Session::retrieve($sessionId);
        if (!$session) {
            return response()->json([
                'status' => 0,
                'message' => __('user_messages.353'),
                'message_code' => 353,
            ]);
        }

        if($session->payment_status == "paid" && $session->status == "complete"){
            $trans_id = $session->client_reference_id;
            $temp = TempUserBooking::query()->where('transaction_id',$trans_id)->first();
            $service_cat_id = $temp->service_cat_id;
            $payment_type = 2;

            if (in_array($service_cat_id, $this->on_demand_service_id_array)) {
                $order_details = UserPackageBooking::query()->where('id', $temp->booking_id)->first();
                $user_details = Provider::query()->whereKey($order_details->provider_id)->first();

                if ($order_details == Null) {
                    return response()->json([
                        "status" => 0,
                        'message' => __('user_messages.59'),
                        "message_code" => 59,
                    ]);
                }
                $order_details->payment_type = $payment_type;
                $order_details->payment_status = 1;
                $order_details->save();
                $temp->payment_status = 1;
                $temp->save();

                $settings = $this->settings;
                if ($settings && isset($settings->auto_settle_payment) && $settings->auto_settle_payment == 1){
                    if ($order_details->provider_pay_settle_status == 0 && $order_details->payment_status == 1) {
                        try {
                            $get_last_transaction = UserWalletTransaction::query()
                                ->where('wallet_provider_type', "=", 3)
                                ->where('user_id', "=", $order_details->provider_id)
                                ->orderBy('id', 'desc')
                                ->first();
                            if ($get_last_transaction != Null) {
                                $last_amount = $get_last_transaction->remaining_balance;
                            } else {
                                $last_amount = 0;
                            }
                        } catch (\Exception $e) {
                            $last_amount = 0;
                        }

                        if($order_details->payment_type == 2){
                            $amount_to_default = round($order_details->provider_amount, 2);
                            $add_balance = new UserWalletTransaction();
                            $add_balance->user_id = $order_details->provider_id;
                            $add_balance->wallet_provider_type = 3;
                            $add_balance->transaction_type = 1;
                            $add_balance->amount = $amount_to_default;
                            $add_balance->order_no = $order_details->order_no;
                            $add_balance->subject = "Paid by Admin For On-demand Service - Order #".$order_details->order_no;
                            $add_balance->subject_code = 17;
                            $add_balance->remaining_balance = floatval($last_amount + $amount_to_default);
                            $add_balance->save();
                        }

                        $order_details->provider_pay_settle_status = 1;
                        $order_details->save();
                    }
                }

                // Sending notification to Provider
                (new NotificationClass())->userProviderPaymentNotification(
                    device_token: $user_details->device_token,service_cat_id: $order_details->service_cat_id,
                    login_device: $user_details->login_device,language: $user_details->language,
                    order_number: $order_details->order_no,order_status: $order_details->status,order_id: $order_details->id
                );

                return redirect()->route('pay.success');
            }
            elseif ($service_cat_id == 0) {
                $order_details = TempUserBooking::query()->where('booking_id', $temp->booking_id)->first();
                if ($order_details == Null){
                    return response()->json([
                        "status" => 0,
                        'message' => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                $user_id = $order_details->user_id - 0;
                $amount = round($order_details->amount, 2);
                if($order_details->user_type == 0){
                    $user_details = User::query()->where('id',"=", $user_id)->first();
                    if ($user_details == Null) {
                        return response()->json([
                            'status' => 0,
                            'message' => __('user_messages.5'),
                            "message_code" => 5,
                        ]);
                    }
                }else{
                    $user_details = Provider::query()->where('id',"=", $user_id)->first();
                    if ($user_details == Null) {
                        return response()->json([
                            'status' => 0,
                            'message' => __('user_messages.5'),
                            "message_code" => 5,
                        ]);
                    }
                }

                try {
                    $get_last_transaction = UserWalletTransaction::query()->where('user_id', $user_details->id)->where('wallet_provider_type',$order_details->user_type)->orderBy('id', 'desc')->first();
                    if ($get_last_transaction != Null) {
                        $last_amount = $get_last_transaction->remaining_balance;
                    } else {
                        $last_amount = 0;
                    }
                } catch (\Exception $e) {
                    $last_amount = 0;
                }

                $add_balance = new UserWalletTransaction();
                $add_balance->user_id = $user_id;
//                $add_balance->service_category_id = 0;
                $add_balance->wallet_provider_type = $order_details->user_type;
                $add_balance->transaction_type = 1;
                $add_balance->amount = $amount;
                $add_balance->subject = "credit by " . $user_details->first_name;
                $add_balance->remaining_balance = floatval($last_amount + $amount);
                $add_balance->save();

                $order_details->payment_status = 1;
                $order_details->save();

                return redirect()->route('pay.success');

            }
        }else{
            return redirect()->route('pay.fail');
        }
    }

    public function stripeFail(Request $request)
    {
        $key = $this->getStripeApiKey();
        Stripe::setApiKey($key);

        if($request->id != ""){
            $temp = TempUserBooking::query()->where('transaction_id',$request->id)->first();
            $service_cat_id = $temp->service_cat_id;
            if (in_array($service_cat_id, $this->coupon_deals)) {
                $purchase_ids = explode(',',$temp->purchase_ids);
                $coupon_ids = explode(',',$temp->coupon_ids);
                $get_coupon_code_ids = explode(',',$temp->get_coupon_code_detail_ids);
                $purchase_coupon_details = UserPurchaseCouponDetails::query()->whereIn('id', $purchase_ids)->get();

                $coupon_details = CouponDetails::query()->where('id', $coupon_ids[0])->first();
//                    $coupon_details->coupon_usage = $coupon_details->coupon_usage - 1;
                $coupon_details->available_coupon = $coupon_details->available_coupon + $temp->coupon_qtys;
                $coupon_details->purchased_coupon = $coupon_details->purchased_coupon - $temp->coupon_qtys;
                $coupon_details->save();

                if (count($get_coupon_code_ids) > 0) {
                    foreach ($get_coupon_code_ids as $get_coupon_code_id) {
                        CouponCodeDetail::query()
                            ->where('id',$get_coupon_code_id)
                            ->update([
                                "user_id" => 0,
                                "code_active_date" => Null,
                                "code_expire_date" => Null
                            ]);
                    }
                }
                UserPurchaseCouponDetails::query()->whereIn('id', $purchase_ids)->delete();
            }
        }
        return redirect()->route('pay.fail');
    }

    public function paySuccess()
    {
        return view('success');
    }

    public function payFail()
    {
        return view('failed');
    }

    /**
     * Retorna o status de integracao com o Stripe para o WebApp
     */
    public function getStripeStatus()
    {
        $key = $this->getStripeApiKey();
        $pubKey = config('services.stripe.key') ?: env('STRIPE_KEY');
        $isLive = strpos($pubKey ?? '', 'pk_live_') === 0;

        return response()->json([
            'status' => 1,
            'configured' => !empty($key) && !empty($pubKey),
            'mode' => $isLive ? 'live' : 'test',
            'public_key' => $pubKey ? substr($pubKey, 0, 12) . '...' . substr($pubKey, -4) : null,
            'has_secret' => !empty($key),
            'supported_currencies' => ['JPY', 'BRL', 'USD', 'EUR'],
            'test_cards' => [
                ['number' => '4242 •••• •••• 4242', 'brand' => 'Visa', 'type' => 'Aprovado Imediato', 'cvc' => '123', 'exp' => '12/28'],
                ['number' => '4000 •••• •••• 0027', 'brand' => 'Visa', 'type' => 'Autenticação 3D Secure', 'cvc' => '123', 'exp' => '12/28'],
                ['number' => '4000 •••• •••• 0002', 'brand' => 'Visa', 'type' => 'Recusado (Fundos Insuficientes)', 'cvc' => '123', 'exp' => '12/28']
            ]
        ]);
    }

    /**
     * Endpoint para criar sessao de checkout de teste pelo WebApp
     */
    public function createTestSession(Request $request)
    {
        $amount = $request->input('amount', 5000);
        $currency = strtoupper($request->input('currency', 'JPY'));
        $name = $request->input('service_name', 'Serviço MaisuApp');
        $orderNo = 'TEST-' . time();

        $key = $this->getStripeApiKey();

        if (empty($key) || strpos($key, 'mk_') === 0) {
            // Modo simulacao assistida caso chave real nao esteja configurada
            return response()->json([
                'status' => 1,
                'mode' => 'sandbox_simulation',
                'order_no' => $orderNo,
                'amount' => $amount,
                'currency' => $currency,
                'service_name' => $name,
                'simulated_checkout_url' => url('/stripe-success?session_id=cs_test_mock_' . uniqid()),
                'message' => 'Sessão simulada com sucesso (Stripe Test Sandbox)'
            ]);
        }

        Stripe::setApiKey($key);

        try {
            $unitAmount = ($currency === 'JPY') ? intval($amount) : intval($amount * 100);

            $checkout_session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => $name,
                            'description' => 'Contratação via MaisuApp Web'
                        ],
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => 1,
                ]],
                'client_reference_id' => $orderNo,
                'mode' => 'payment',
                'success_url' => url('/pay-success?session_id={CHECKOUT_SESSION_ID}&order_no=' . $orderNo),
                'cancel_url' => url('/pay-fail?order_no=' . $orderNo),
            ]);

            return response()->json([
                'status' => 1,
                'redirect_url' => $checkout_session->url,
                'session_id' => $checkout_session->id,
                'order_no' => $orderNo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
