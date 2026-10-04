<?php

namespace App\Http\Controllers\User;

use App\model\CouponCodeDetail;
use App\model\CouponDetails;
use App\model\GeneralSettings;
use App\model\TempUserBooking;
use App\model\UserCouponCart;
use App\model\UserPurchaseCouponDetails;
use App\model\UserWalletTransaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    //
    public function getPaymentProcessing(Request $request){

            $payment_status = $request->get('status');
            $payment_tx_ref = $request->get('tx_ref');
            $payment_transaction_id = $request->get('transaction_id');

            $get_temp_booking_data = TempUserBooking::query()->where('booking_id',$payment_tx_ref)->first();

            if($get_temp_booking_data != Null){

                $service_cat_id = $get_temp_booking_data->service_cat_id;

                $user_id = $get_temp_booking_data->user_id;

                    //code for coupon service payment check
                    if($service_cat_id == 1 ){
                        //ride service

                    }else if($service_cat_id == 2 ){
                        //delivery service
                    }else if($service_cat_id == 3 || $service_cat_id == 4 ){
                        //other service
                    }else if($service_cat_id == 5  ){
                        // courier service
                    }else if($service_cat_id == 6 ){
                         if($payment_status == "successful"){
                              //Check in server of flutter wave for confirm transaction
                              $payment_status_check = $this->payementStatusCheck($payment_transaction_id);
                              /*\Log::info("paymenysstatus");
                              \Log::info($payment_status_check);*/
//                              dd($payment_status_check);

                              if(isset($payment_status_check['status']) &&  $payment_status_check['status'] == "success")
                              {
                                  $get_temp_booking_data->transaction_id = $payment_transaction_id;
                                  $get_temp_booking_data->payment_status = 1;
                                  $get_temp_booking_data->save();

                                  $get_user_coupon_cart_data =  UserCouponCart::query()->where('user_id',$user_id)->get();
                                  foreach ($get_user_coupon_cart_data as $singel_coupon_cart_data) {
                                      $coupon_id = $singel_coupon_cart_data->coupon_id;
                                      $coupon_qty = $singel_coupon_cart_data->coupon_qty;
                                      $coupon_details = CouponDetails::query()->where('id', $coupon_id)->first();
                                      $coupon_validity = ($coupon_details->coupon_validity > 0) ? $coupon_details->coupon_validity : 1;
                                      if ($coupon_details != Null) {
                                          //                                    if ($coupon_details->coupon_limit > $coupon_details->coupon_usage) {
                                          $purchse_coupon_code_qty = $coupon_qty;

                                          //code for update couden code table assign user to code
                                          for ($i = 1; $i <= $purchse_coupon_code_qty; $i++) {
                                              $get_coupon_code = CouponCodeDetail::query()->where('coupon_id', $coupon_details->id)
                                                  ->where('user_id', '=', 0)->first();
                                              if($get_coupon_code == NUll){
                                                  //in case if coupon code is completed or manual changed by admin in database
                                                  $get_coupon_code = new CouponDetails();
                                                  $get_coupon_code->user_id = $coupon_details->id;

                                                  $str_result = '123456789009876543212';
                                                  $coupon_name =  str_replace(' ', '', $coupon_details->coupon_name);;
                                                  $random_srt= substr(str_shuffle($str_result),
                                                      0, 4);
                                                  $coupon_code = strtoupper(substr($coupon_name,0,4).$random_srt);

                                                  $get_coupon_code->coupon_code = $coupon_code;
                                              }

                                              $get_coupon_code->user_id = $user_id;
                                              $get_coupon_code->code_active_date = date('Y-m-d H:i:s');
                                              $get_coupon_code->code_expire_date = date('Y-m-d H:i:s', strtotime("+" . $coupon_validity . " day"));
                                              $get_coupon_code->save();
                                          }
                                          //update table entry to remain and available on purchse coun code entry
                                          $user_purchase_coupon = new UserPurchaseCouponDetails();
                                          $user_purchase_coupon->user_id = $user_id;
                                          $user_purchase_coupon->trans_id = $payment_tx_ref;
                                          $user_purchase_coupon->coupon_id = $coupon_details->id;
                                          $user_purchase_coupon->coupon_name = $coupon_details->coupon_name;
                                          $user_purchase_coupon->coupon_brand_name = $coupon_details->coupon_brand_name;
                                          $user_purchase_coupon->coupon_amount = $coupon_details->coupon_amount;
                                          //                                            $user_purchase_coupon->coupon_purchase_qtys = $purchse_coupon_code_qty;
                                          //                                        $user_purchase_coupon->coupon_code = $coupon_details->coupon_code;
                                          $user_purchase_coupon->coupon_code = "";
                                          $user_purchase_coupon->payment_status = 1;
                                          $user_purchase_coupon->purchase_date = date('Y-m-d H:i:s');
                                          $user_purchase_coupon->expiry_date = date('Y-m-d H:i:s', strtotime("+" . $coupon_validity . " day"));
                                          $user_purchase_coupon->status = 1;
                                          $user_purchase_coupon->save();
                                          //                                      $coupon_details->coupon_usage = $coupon_details->coupon_usage + 1;
                                          $coupon_details->available_coupon = $coupon_details->available_coupon - $purchse_coupon_code_qty;
                                          $coupon_details->purchased_coupon = $coupon_details->purchased_coupon + $purchse_coupon_code_qty;
                                          $coupon_details->save();
                                          //                                    }
                                      }
                                      UserCouponCart::query()->where('user_id',$user_id)->where('coupon_id',$coupon_id)->delete();
                                  }
                                  return redirect()->route('get:coupon-list')->with('success','coupon successfully purchased');
                              }else{
                                  return redirect()->route('get:coupon-list')->with('error','Payment Failed ! Please try again..');
                              }
                         }else{
                             return redirect()->route('get:coupon-list')->with('error','Payment Failed ! Please try again..');
                         }

                    }else{

                    }


            }else{
                return redirect()->route('get:homepage')->with('error','Something went wrong.please try again later.');
            }
    }

    public function payementStatusCheck($payment_transaction_id = 0){

        if($payment_transaction_id != "")
        {
            $general_settings =GeneralSettings::query()->first();
            $flutter_sec_key = isset($general_settings->flutterwave_secret_key)?$general_settings->flutterwave_secret_key:"";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/".$payment_transaction_id."/verify",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",

                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer ".$flutter_sec_key
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $return_data = json_decode($response,true);

            return $return_data;
        }else{
                return false;
        }

    }
}
