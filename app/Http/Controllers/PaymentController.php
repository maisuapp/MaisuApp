<?php

namespace App\Http\Controllers;

use App\Classes\NotificationClass;
use App\model\CouponDetails;
use App\model\GeneralSettings;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ServiceCategory;
use App\model\TempUserBooking;
use App\model\TransportDriverDetails;
use App\model\TransportRentalRideBooking;
use App\model\TransportRideBook;
use App\model\User;
use App\model\UserPackageBooking;
use App\model\UserProductBooking;
use App\model\UserProductBookingStatus;
use App\model\UserPurchaseCouponDetails;
use App\model\UserWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Luigel\Paymongo\Facades\Paymongo;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PaymentController extends Controller
{
    private $_api_context;

    private $transport_service_id_array;
    private $delivery_service_id_array;
    private $on_demand_service_id_array;
    private $coupon_deals;
    private $rental_service_id_array;

    private $notificationClass;

    public function __construct(NotificationClass $notificationClass)
    {
        $this->transport_service_id_array = [1, 2, 4];
        $this->coupon_deals = [3];
        $this->rental_service_id_array = [31, 32];
        $this->delivery_service_id_array = [5, 6, 7, 8, 9, 10];

        $service_cat = ServiceCategory::query()->whereIN('category_type', [3, 4])->where('status', 1)->get()->pluck('id')->toArray();
        $this->on_demand_service_id_array = $service_cat;

        $this->notificationClass = $notificationClass;
        $get_settings = GeneralSettings::query()->first();
        if ($get_settings == Null && $get_settings->paypal_sandbox == Null && $get_settings->paypal_client_id == Null && $get_settings->paypal_client_secret_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => "Something went to wrong!",
                "message_code" => 9,
            ]);
        }
        $paypal_sandbox = $get_settings->paypal_sandbox . "";

        $this->_api_context = new ApiContext(new OAuthTokenCredential($get_settings->paypal_client_id, $get_settings->paypal_client_secret_key));

        $settings = array(
            'mode' => $paypal_sandbox,
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path() . '/logs/paypal.log',
            'log.LogLevel' => 'ERROR'
        );
        $this->_api_context->setConfig($settings);

    }

    //paypal payment
    //paypal status web hook
    public function getPaymentStatus(Request $request)
    {
        $payment_id = $request->get('paymentId');
        if (empty($request->get('PayerID')) || empty($request->get('token'))) {
            \Session::put('error', 'Payment failed');
            return redirect()->route('addmoney.paywithpaypal');
        }
        $payment = Payment::get($payment_id, $this->_api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($request->get('PayerID'));
        /**Execute the payment **/
        $result = $payment->execute($execution, $this->_api_context);
        if ($result->getState() == 'approved') {

            $get_transactions = $result->getTransactions();

            list($user_id, $service_cat_id, $order_id) = explode(",", $get_transactions[0]->custom);

            if (in_array($service_cat_id, $this->transport_service_id_array)) {
                $ride_details = TransportRideBook::query()->where('id', $order_id)->first();
                if ($ride_details != Null) {
                    $ride_details->payment_status = 1;
                    $ride_details->status = 7;
                    $ride_details->save();

                    if ($ride_details->completed_by == 1) {
                        if ($ride_details->driver_id != Null) {
                            $driver_details = TransportDriverDetails::query()->where('id', $ride_details->driver_id)->first();
                            if ($driver_details != Null) {
                                $update_driver_status = ProviderServices::query()->where('id', $driver_details->provider_service_id)->first();
                                if ($update_driver_status != Null) {
                                    $update_driver_status->current_status = 1;
                                    $update_driver_status->save();
                                }
                            }

                            ProviderUserRunningService::query()->where('service_cat_id', $ride_details->service_cat_id)->where('user_id', $ride_details->user_id)->where('booking_id', $ride_details->id)->delete();
                            //$driver_details = Provider::query()->select('providers.email', 'providers.device_token', 'provider_services.id', 'providers.name', 'providers.login_device')
                            //->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                            //->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                            //->where('transport_driver_details.id', $transport_ride->driver_id)->first();
                            //if ($driver_details != Null) {
                            //$this->notificationClass->driverCompletedNotification($driver_details->device_token, $transport_ride->service_cat_id, $driver_details->id, $driver_details->login_device);
                            //}
                        }
                        $ride_details->status = 9;
                        $ride_details->save();
                    }

                    return redirect()->route('paypal.success');
                } else {
                    return redirect()->route('paypal.success');
                }
            } elseif (in_array($service_cat_id, $this->rental_service_id_array)) {
                $ride_details = TransportRentalRideBooking::query()->where('id', $order_id)->first();
                if ($ride_details != Null) {
                    $ride_details->payment_status = 1;
                    $ride_details->status = 7;
                    $ride_details->save();

                    $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                    return redirect()->route('paypal.success');
                } else {
                    return redirect()->route('paypal.success');
                }
            } elseif (in_array($service_cat_id, $this->delivery_service_id_array)) {
                $order_details = UserProductBooking::query()->where('id', $order_id)->where('user_id', $user_id)->first();
                if ($order_details != Null) {
                    $order_details->payment_status = 1;
                    $order_details->status = 1;
                    $order_details->save();

                    $product_status = new UserProductBookingStatus();
                    $product_status->order_id = $order_details->id;
                    $product_status->status = 1;
                    $product_status->save();

                    $store_provider_details = Provider::select('providers.device_token', 'providers.login_device', 'providers.email', 'providers.language')
                        ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                        ->join('store_details', 'store_details.provider_service_id', '=', 'provider_services.id')
                        ->where('store_details.id', $order_details->store_id)->first();
                    if ($store_provider_details != Null) {
                        $this->notificationClass->storeDeliveryUserNotification($order_details->id, $store_provider_details->device_token, $order_details->status, $store_provider_details->login_device, $store_provider_details->language);
                    }
                    return redirect()->route('paypal.success');
                } else {
                    return redirect()->route('paypal.success');
                }

            } elseif (in_array($service_cat_id, $this->on_demand_service_id_array)) {
                $place_order = UserPackageBooking::where('user_service_package_booking.id', $order_id)->first();
                if ($place_order != Null) {
                    $place_order->payment_status = 1;
                    $place_order->save();

                    return redirect()->route('paypal.success');
                } else {
                    return redirect()->route('paypal.success');
                }
            } elseif (in_array($service_cat_id, $this->coupon_deals)) {
                $purchase_coupon_details = UserPurchaseCouponDetails::query()->where('trans_id', $order_id)->get();

                if (count($purchase_coupon_details) > 0) {
                    foreach ($purchase_coupon_details as $single_coupon) {
                        $coupon_details = CouponDetails::query()->where('id', $single_coupon->coupon_id)->first();
                        if ($coupon_details != NULL) {
                            $user_purchase_coupon = UserPurchaseCouponDetails::query()->where('id', $single_coupon->id)->first();
                            $user_purchase_coupon->payment_status = 1;
                            $user_purchase_coupon->save();
                            $coupon_details->coupon_usage = $coupon_details->coupon_usage + 1;
                            $coupon_details->save();
                        }
                    }
                    return redirect()->route('paypal.success');
                } else {
                    return redirect()->route('paypal.success');
                }
            } elseif ($service_cat_id == 0) {
                $temp_user_payment = TempUserBooking::query()->where('booking_id', $order_id)->first();
                if ($temp_user_payment != Null) {
                    $user_details = User::query()->where('id', $temp_user_payment->user_id)->first();
                    if ($user_details != Null) {
                        try {
                            $get_last_transaction = UserWalletTransaction::where('user_id', $temp_user_payment->user_id)->orderBy('id', 'desc')->first();
                            if ($get_last_transaction != Null) {
                                $last_amount = $get_last_transaction->remaining_balance;
                            } else {
                                $last_amount = 0;
                            }
                        } catch (\Exception $e) {
                            $last_amount = 0;
                        }

                        $add_balance = new UserWalletTransaction();
                        $add_balance->user_id = $temp_user_payment->user_id;
                        $add_balance->transaction_type = 1;
                        $add_balance->amount = $temp_user_payment->amount;
                        $add_balance->subject = "Deposit from Paypal";
                        //$add_balance->subject = "credit by " . $user_details->first_name;
                        $add_balance->remaining_balance = floatval($last_amount + $temp_user_payment->amount);
                        $add_balance->subject_code = 11;
                        $add_balance->order_no = '';
                        $add_balance->save();
                        return redirect()->route('paypal.success');
                    } else {
                        return redirect()->route('paypal.success');
                    }

                } else {
                    return redirect()->route('paypal.success');
                }
            } else {
                return redirect()->route('paypal.success');
            }
        } else {
            try {
                $get_transactions = $result->getTransactions();

                list($user_id, $service_cat_id, $order_id) = explode(",", $get_transactions[0]->custom);

                if (in_array($service_cat_id, $this->delivery_service_id_array)) {
                    $order_details = UserProductBooking::query()->where('id', $order_id)->where('user_id', $user_id)->first();
                    if ($order_details != Null) {
                        $order_details->status = 10;
                        $order_details->save();
                        return redirect()->route('paypal.failed');
                    } else {
                        return redirect()->route('paypal.failed');
                    }

                } elseif (in_array($service_cat_id, $this->coupon_deals)) {
                    $purchase_coupon_details = UserPurchaseCouponDetails::query()->where('trans_id', $order_id)->delete();
                } else {
                    return redirect()->route('paypal.failed');
                }
            } catch (\Exception $e) {
                \Log::info("web hook try catch =>");
                \Log::info($e);
            }
            return redirect()->route('paypal.failed');
        }
    }

    //paypal success
    public function getPaypalSuccess(Request $request)
    {
        return view('success');
    }

    //paypal failed
    public function getPaypalFailed(Request $request)
    {
        return view('failed');
    }
    //end paypal payment

    //Paymongo Payment
    //paymongo success
    public function getPaypmongoSuccess(Request $request)
    {
        return view('success');
        //return "success";
    }

    //paymongo failed
    public function getPaypmongoFailed(Request $request)
    {
        return view('failed');
        //return "failed";
    }

    //paymongo error
    public function getMongoPayError(Request $request)
    {
        return view('error');
        //return "something want to wrong!";
    }

    //paymongo with gcash payment success
    public function getMongoPaySuccess(Request $request, $user_id, $order_id)
    {
        $temp_user_payment = TempUserBooking::query()->where('booking_id', $order_id)->first();
        if ($temp_user_payment != Null) {
            $user_details = User::query()->where('id', $temp_user_payment->user_id)->first();
            if ($user_details != Null) {

                $description = "";
                $st_description = "";
                $service_cat_id = $temp_user_payment->service_cat_id;
                if (in_array($service_cat_id, $this->transport_service_id_array)) {
                    $description = "Transoprt Service Payment";
                    $st_description = "Transoprt Service Payment by PayMongo";
                }
                elseif (in_array($service_cat_id, $this->rental_service_id_array)) {
                    $description = "Transoprt Rental Service Payment";
                    $st_description = "Transoprt Rental Service Payment by PayMongo";
                }
                elseif (in_array($service_cat_id, $this->delivery_service_id_array)) {
                    $description = "Delivery Service Payment";
                    $st_description = "Delivery Service Payment by PayMongo";
                }
                elseif (in_array($service_cat_id, $this->on_demand_service_id_array)) {
                    $description = "On-demand Service Payment";
                    $st_description = "On-demand Service Payment by PayMongo";
                }
                elseif (in_array($service_cat_id, $this->coupon_deals)) {
                    $description = "Coupon Deals Payment";
                    $st_description = "Coupon Deals Payment by PayMongo";
                }
                elseif ($service_cat_id == 0) {
                    $description = "Add Wallet Amount";
                    $st_description = "Add Wallet Amount by PayMongo";
                }

                $gcashSource = Paymongo::source()->find($temp_user_payment->transaction_id);
                $gcashSource_status = $gcashSource->getStatus();
                $source_type = $gcashSource->getSourceType();

                if ($gcashSource_status == "chargeable" && $source_type == "gcash") {
                    $payment = Paymongo::payment()
                        ->create([
                            'amount' => str_replace(",", "", $gcashSource->getAmount()) - 0,
                            'currency' => $gcashSource->getCurrency(),
                            'description' => $description . '',
                            'statement_descriptor' => $st_description . "",
                            'source' => [
                                'id' => $gcashSource->getId(),
                                'type' => $gcashSource->getType()
                            ]
                        ]);
                    if ($payment->getStatus() == "paid") {
//                        $transaction_id = $payment->getId();
//                        try {
//                            $get_last_transaction = UserWalletTransaction::where('user_id', $temp_user_payment->user_id)->orderBy('id', 'desc')->first();
//                            if ($get_last_transaction != Null) {
//                                $last_amount = $get_last_transaction->remaining_balance;
//                            } else {
//                                $last_amount = 0;
//                            }
//                        } catch (\Exception $e) {
//                            $last_amount = 0;
//                        }

//                        $add_balance = new UserWalletTransaction();
//                        $add_balance->user_id = $temp_user_payment->user_id;
//                        $add_balance->transaction_type = 1;
//                        $add_balance->amount = $temp_user_payment->amount;
//                        $add_balance->subject = "credit by " . $user_details->first_name;
//                        $add_balance->remaining_balance = floatval($last_amount + $temp_user_payment->amount);
//                        $add_balance->save();
//                        $temp_user_payment->transaction_id = $payment->getId();
//                        $temp_user_payment->save();

                        if (in_array($service_cat_id, $this->transport_service_id_array)) {
                            $ride_details = TransportRideBook::query()->where('id', $order_id)->first();
                            if ($ride_details != Null) {
                                $ride_details->payment_status = 1;
                                $ride_details->status = 7;
                                $ride_details->save();

                                if ($ride_details->completed_by == 1) {
                                    if ($ride_details->driver_id != Null) {
                                        $driver_details = TransportDriverDetails::query()->where('id', $ride_details->driver_id)->first();
                                        if ($driver_details != Null) {
                                            $update_driver_status = ProviderServices::query()->where('id', $driver_details->provider_service_id)->first();
                                            if ($update_driver_status != Null) {
                                                $update_driver_status->current_status = 1;
                                                $update_driver_status->save();
                                            }
                                        }

                                        ProviderUserRunningService::query()->where('service_cat_id', $ride_details->service_cat_id)->where('user_id', $ride_details->user_id)->where('booking_id', $ride_details->id)->delete();
                                        //$driver_details = Provider::query()->select('providers.email', 'providers.device_token', 'provider_services.id', 'providers.name', 'providers.login_device')
                                        //->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                                        //->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                                        //->where('transport_driver_details.id', $transport_ride->driver_id)->first();
                                        //if ($driver_details != Null) {
                                        //$this->notificationClass->driverCompletedNotification($driver_details->device_token, $transport_ride->service_cat_id, $driver_details->id, $driver_details->login_device);
                                        //}
                                    }
                                    $ride_details->status = 9;
                                    $ride_details->save();
                                }

                                $temp_user_payment->transaction_id = $payment->getId();
                                $temp_user_payment->payment_status = 1;
                                $temp_user_payment->save();

                                \Log::info("payment success =>");

                                return redirect()->route('paypmongo.success');
                            } else {
                                return redirect()->route('paypmongo.success');
                            }
                        }
                        elseif (in_array($service_cat_id, $this->rental_service_id_array)) {

                            $ride_details = TransportRentalRideBooking::query()->where('id', $order_id)->first();
                            if ($ride_details != Null) {
                                $ride_details->payment_status = 1;
                                $ride_details->status = 7;
                                $ride_details->save();

                                $temp_user_payment->transaction_id = $payment->getId();
                                $temp_user_payment->payment_status = 1;
                                $temp_user_payment->save();

                                $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                                \Log::info("payment success =>");
                                return redirect()->route('paypmongo.success');
                            } else {
                                return redirect()->route('paypmongo.success');
                            }
                        }
                        elseif (in_array($service_cat_id, $this->delivery_service_id_array)) {
                            $order_details = UserProductBooking::query()->where('order_no', $order_id)->where('user_id', $user_id)->first();
                            \Log::info("order_details =>");
                            \Log::info($order_details);
                            if ($order_details != Null) {
                                $order_details->payment_status = 1;
                                $order_details->status = 1;
                                $order_details->save();

                                $product_status = new UserProductBookingStatus();
                                $product_status->order_id = $order_details->id;
                                $product_status->status = 1;
                                $product_status->save();

                                $temp_user_payment->transaction_id = $payment->getId();
                                $temp_user_payment->payment_status = 1;
                                $temp_user_payment->save();

                                $store_provider_details = Provider::select('providers.device_token', 'providers.login_device', 'providers.email', 'providers.language')
                                    ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                                    ->join('store_details', 'store_details.provider_service_id', '=', 'provider_services.id')
                                    ->where('store_details.id', $order_details->store_id)->first();
                                if ($store_provider_details != Null) {
                                    $this->notificationClass->storeDeliveryUserNotification($order_details->id, $store_provider_details->device_token, $order_details->status, $store_provider_details->login_device, $store_provider_details->language);
                                }

                                \Log::info("payment success =>");
                                return redirect()->route('paypmongo.success');
                            } else {
                                return redirect()->route('paypmongo.success');
                            }

                        }
                        elseif (in_array($service_cat_id, $this->on_demand_service_id_array)) {
                            $place_order = UserPackageBooking::where('user_service_package_booking.id', $order_id)->first();
                            if ($place_order != Null) {
                                $place_order->payment_status = 1;
                                $place_order->save();

                                $temp_user_payment->transaction_id = $payment->getId();
                                $temp_user_payment->payment_status = 1;
                                $temp_user_payment->save();

                                \Log::info("payment success =>");
                                return redirect()->route('paypmongo.success');
                            } else {
                                return redirect()->route('paypmongo.success');
                            }
                        }
                        elseif (in_array($service_cat_id, $this->coupon_deals)) {
                            $purchase_coupon_details = UserPurchaseCouponDetails::query()->where('trans_id', $order_id)->get();

                            if (count($purchase_coupon_details) > 0) {
                                foreach ($purchase_coupon_details as $single_coupon) {
                                    $coupon_details = CouponDetails::query()->where('id', $single_coupon->coupon_id)->first();
                                    if ($coupon_details != NULL) {
                                        $user_purchase_coupon = UserPurchaseCouponDetails::query()->where('id', $single_coupon->id)->first();
                                        $user_purchase_coupon->payment_status = 1;
                                        $user_purchase_coupon->save();
                                        $coupon_details->coupon_usage = $coupon_details->coupon_usage + 1;
                                        $coupon_details->save();
                                    }
                                }
                                $temp_user_payment->transaction_id = $payment->getId();
                                $temp_user_payment->payment_status = 1;
                                $temp_user_payment->save();
                                return redirect()->route('paypmongo.success');
                            } else {
                                return redirect()->route('paypmongo.success');
                            }
                        } elseif ($service_cat_id == 0) {
                            try {
                                $get_last_transaction = UserWalletTransaction::where('user_id', $temp_user_payment->user_id)->orderBy('id', 'desc')->first();
                                if ($get_last_transaction != Null) {
                                    $last_amount = $get_last_transaction->remaining_balance;
                                } else {
                                    $last_amount = 0;
                                }
                            } catch (\Exception $e) {
                                $last_amount = 0;
                            }

                            $amount = round(($temp_user_payment->amount), 2);
                            $add_balance = new UserWalletTransaction();
                            $add_balance->user_id = $temp_user_payment->user_id;
                            $add_balance->transaction_type = 1;
                            $add_balance->amount = $amount;
                            $add_balance->subject = "Deposit from PayMongo";
                            //$add_balance->subject = "credit by " . $user_details->first_name;
                            $add_balance->remaining_balance = floatval($last_amount + $amount);
                            $add_balance->subject_code = 12;
                            $add_balance->order_no = '';
                            $add_balance->save();
                            $temp_user_payment->transaction_id = $payment->getId();
                            $temp_user_payment->payment_status = 1;
                            $temp_user_payment->save();
                            return redirect()->route('paypmongo.success');
                        }
                        else {
                            $temp_user_payment->transaction_id = $payment->getId();
                            //$temp_user_payment->payment_status = 1;
                            $temp_user_payment->save();
                            return redirect()->route('paypmongo.success');
                        }
                    }
                    else {
                        $temp_user_payment->transaction_id = $payment->getId();
//                        $temp_user_payment->payment_status = 1;
                        $temp_user_payment->save();
                        return redirect()->route('paypmongo.failed');
                    }
                } else {
                    return redirect()->route('mongo_pay.error');
                }
            } else {
                return redirect()->route('mongo_pay.error');
            }
        } else {
            return redirect()->route('mongo_pay.error');
        }

//        dd($user_id);
//
//        $gcashSource = Paymongo::source()->find($order_id);
//        dd($gcashSource);
//        try {
//            $paymentIntent = Paymongo::paymentIntent()->find($user_id);
//            $get_payment_check = $paymentIntent->getData();
//            echo $get_payment_check['status'];
//            if ($get_payment_check['status'] == "succeeded") {
//                return redirect()->route('paypmongo.success');
//            } else {
//                return redirect()->route('paypmongo.failed');
//            }
//
//        } catch (\Exception $e) {
//            return $e->getMessage();
//        }
    }

    //paymongo with gcash payment failed
    public function getMongoPayFailed(Request $request, $user_id, $order_id)
    {
        \Log::info("get payment faild =>");
        $temp_user_payment = TempUserBooking::query()->where('booking_id', $order_id)->first();
        if ($temp_user_payment != Null) {
            $user_details = User::query()->where('id', $temp_user_payment->user_id)->first();
            if ($user_details != Null) {
                //$temp_user_payment->payment_status = 1;
                //$temp_user_payment->save();
                $service_cat_id = $temp_user_payment->service_cat_id;

                if (in_array($service_cat_id, $this->delivery_service_id_array)) {
                    $order_details = UserProductBooking::query()->where('id', $order_id)->where('user_id', $user_id)->first();
                    \Log::info("order_details =>");
                    \Log::info($order_details);
                    if ($order_details != Null) {
                        $order_details->status = 10;
                        $order_details->save();
                        \Log::info("payment faild =>");
                    }
                } elseif (in_array($service_cat_id, $this->coupon_deals)) {
                    $purchase_coupon_details = UserPurchaseCouponDetails::query()->where('trans_id', $order_id)->delete();
                }
                return redirect()->route('paypmongo.failed');
            } else {
                return redirect()->route('paypmongo.failed');
            }
        } else {
            return redirect()->route('paypmongo.failed');
        }
    }

}
