<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 22-02-2019
 * Time: 01:36 PM
 */

namespace App\Classes;

use App\model\AppVersionSetting;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ServiceCategory;
use App\model\StoreDetails;
use App\model\UserProductBooking;
use App\model\UserProductBookingStatus;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;

class StoreClassApi
{
    //json response status [
    //    0 => false,
    //    1 => true,
    //    2 => registration pending,
    //    3 => app user blocked,
    //    4 => app user access token not match,
    //    5 => app user not found,
    //    6 => user service pending,
    //    7 => user service requested,
    //    8 => user service rejected,
    //  ]

    private $store_service_id_array;
    private $food_delivery;
    private $grocery_delivery;
    private $liquor_delivery;
    private $flower_delivery;
    private $water_delivery;
    private $medicine_delivery;
    private $notificationClass;
    private $adminClass;

    public function __construct(NotificationClass $notificationClass,AdminClass $adminClass)
    {
        $this->adminClass = $adminClass;
        $this->store_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->food_delivery = 5;
        $this->grocery_delivery = 6;
        $this->liquor_delivery = 7;
        $this->flower_delivery = 8;
        $this->water_delivery = 9;
        $this->medicine_delivery = 10;
        $this->notificationClass = $notificationClass;
    }

    //register login response
    public function storeLoginRegisterResponse($provider_details,$request_type=0,$request="")
    {
        $provider_details = Provider::query()->where('id', $provider_details['id'])->first();
        if ($provider_details == Null) {
            return response()->json([
                'status' => 5,
//                'message' => 'Store Provider Not Found!',
                'message' => __('store_messages.5'),
                "message_code" => 5,
            ]);
        }
        if (in_array($provider_details['gender'], [1, 2])) {
            $gender = $provider_details['gender'];
        } else {
            $gender = 0;
        }
        if ($provider_details['avatar'] != Null) {
            if (filter_var($provider_details['avatar'], FILTER_VALIDATE_URL) == true) {
                $provider_profile_image = $provider_details['avatar'];
            } else {
                $provider_profile_image = url('/assets/images/profile-images/provider/' . $provider_details['avatar']);
            }
        } else {
            $provider_profile_image = '';
        }
        if ($provider_details['status'] == 0 || $provider_details['status'] == 1) {
            $get_store_service = ProviderServices::query()->where('provider_id', $provider_details['id'])
                ->whereIn('service_cat_id', $this->store_service_id_array)->first();
                //->orWhere('service_cat_id', $this->food_delivery)->orWhere('service_cat_id', $this->grocery_delivery)->orWhere('service_cat_id', $this->liquor_delivery)->orWhere('service_cat_id', $this->flower_delivery)->orWhere('service_cat_id', $this->water_delivery)->orWhere('service_cat_id', $this->medicine_delivery)->first();
            if ($get_store_service != Null) {
                if ($request_type == 1){
                    if ($request != Null) {
                        $provider_details->last_active = date('Y-m-d H:i:s');
                        $provider_details->country_code = $request['select_country_code'];
                        $provider_details->currency = $request['select_currency'];
                        $provider_details->language = $request['select_language'];
                        $provider_details->device_token = $request['device_token'];
                        $provider_details->login_device = $request['login_device'];
                        $provider_details->save();
                    }
                    $provider_details->generateAccessToken($provider_details->id);
                }

                $lang_prefix = $this->adminClass->get_langugae_fields($provider_details->language);

                $select_service_category = ServiceCategory::query()->select('service_category.'.$lang_prefix.'name as name')->where('id', $get_store_service->service_cat_id)->first();
                $store_service_id = $get_store_service->id;
                $store_register_status = $get_store_service->status;
                $store_current_status = $get_store_service->current_status;
                //$store_rejected_reason = $get_store_service->rejected_reason;
                if ($get_store_service->status == 0) {
//                    $store_service_message = "Sorry, You don't have to permission to access the app features because your store account has not been approved by the admin.";
                    $store_service_message = __('store_messages.158');
                } elseif ($get_store_service->status == 2) {
//                    $store_service_message = "Your account approval request has been rejected";
                    $store_service_message = __('store_messages.159');
                } elseif ($get_store_service->status == 3) {
                    $store_service_message = $get_store_service->rejected_reason;
                } else {
                    $store_service_message = '';
                }
            } else {
                return response()->json([
                    'status' => 0,
//                    'message' => 'You Are Not Register As Store!',
                    'message' => __('store_messages.94'),
                    "message_code" => 94,
                ]);
            }
            $store_details = StoreDetails::query()->where('provider_service_id', $get_store_service->id)->first();
            if ($store_details == Null) {
                return response()->json([
                    'status' => 6,
//                    'message' => 'store not found!',
                    'message' => __('store_messages.6'),
                    "message_code" => 6,
                ]);
            }

            return response()->json([
                'status' => 1,
//                'message' => 'success!',
                'message' => __('store_messages.1'),
                "message_code" => 1,
                'store_id' => $provider_details['id'],
                'store_verified' => $provider_details['verified_at'] != Null ? 1 : 0,
                'store_service_id' => $store_service_id,
                'service_status' => $store_register_status,
                'service_message' => $store_service_message,
                'service_category_name' => $select_service_category != Null ? $select_service_category->name : '',
                'service_current_status' => $store_current_status,
                'store_name' => $store_details['name']."",
                'store_provider_name' => $provider_details['first_name']."",
                //'store_last_name' => $provider_details['last_name'],
                'provider_profile_image' => $provider_profile_image,
                'provider_gender' => in_array($provider_details['gender'], [1, 2]) ? $provider_details['gender'] : 0,
                'access_token' => $provider_details['access_token'],
                'email' => $provider_details['email'] != Null ? $provider_details['email'] : '',
                'login_type' => $provider_details['login_type'],
                'store_banner' => $store_details['image'] != Null ? url('/assets/images/store-images/' . $store_details['image']) : '',
                'contact_number' => $provider_details['contact_number'] != Null ? $provider_details['contact_number'] : "",
                'select_country_code' => $provider_details['country_code'] != Null ? $provider_details['country_code'] : "",
                'select_currency' => $provider_details['currency'] != Null ? $provider_details['currency'] : "",
                'select_language' => $provider_details['language'] != Null ? $provider_details['language'] : "",
                'server_time_zone' => config('app.timezone'),
            ]);
        } elseif ($provider_details['status'] == 2) {
            return response()->json([
                'status' => 3,
//                'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                'message' => __('store_messages.3'),
                "message_code" => 3,
            ]);
        } elseif ($provider_details['status'] == 3) {
            $message = "<div style='padding: 0px 50px;' xmlns='http://www.w3.org/1999/html'>
                                <pre style='font-family: &quot;Courier New&quot;; font-size: 9pt;'>
                                    Thank you for signing up!
                                <br />
                                Please complete your registration by going to <a href='". route("get:store-admin:login")."'>". route("get:store-admin:login")."</a>
                                <br />
                                Enter Service Type, Store Details, Upload Required Documents and add your Product Lists.  Upon completion of entries, your application will be reviewed for approval.
                                <br />
                                Any questions, email us at  <a href='mailto:sales@whitelabelfox.com'>sales@whitelabelfox.com</a>
                                </span>
                                </pre>
                            </div>";

            if ($request_type == 1){
                if ($request != Null) {
                    $provider_details->last_active = date('Y-m-d H:i:s');
                    $provider_details->country_code = $request['select_country_code'];
                    $provider_details->currency = $request['select_currency'];
                    $provider_details->language = $request['select_language'];
                    $provider_details->device_token = $request['device_token'];
                    $provider_details->login_device = $request['login_device'];
                    $provider_details->save();
                }
                $provider_details->generateAccessToken($provider_details->id);
            }

            return response()->json([
                'status' => 1,
//                'message' => 'Your Service Register Request is Pending!',
                'message' => __('store_messages.1'),
                "message_code" => 1,
                'store_id' => $provider_details['id'],
                'store_verified' => $provider_details['verified_at'] != Null ? 1 : 0,
                'store_service_id' => 0,
                'service_status' => 4,
                //'service_message' => 'Your account not sent for approval request unless you add the store details from store web panel. Please add your all store details and sent your account request to approval.',
                'service_message' => $message,
                'service_category_name' => '',
                'service_current_status' => 0,
                'store_name' => '',
                'store_provider_name' => $provider_details['first_name']."",
                //'store_last_name' => $provider_details['last_name'],
                'provider_profile_image' => $provider_profile_image,
                'provider_gender' => in_array($provider_details['gender'], [1, 2]) ? $provider_details['gender'] : 0,
                'access_token' => $provider_details['access_token'],
                'email' => $provider_details['email'] != Null ? $provider_details['email'] : '',
                'login_type' => $provider_details['login_type'],
                'store_banner' => '',
                'contact_number' => $provider_details['contact_number'] != Null ? $provider_details['contact_number'] : "",
                'select_country_code' => $provider_details['country_code'] != Null ? $provider_details['country_code'] : "",
                'select_currency' => $provider_details['currency'] != Null ? $provider_details['currency'] : "",
                'select_language' => $provider_details['language'] != Null ? $provider_details['language'] : "",
                'server_time_zone' => config('app.timezone'),
            ]);
        } else {
            return response()->json([
                'status' => 0,
//                'message' => 'something went to wrong!',
                'message' => __('store_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    //check status of store service register
    public function checkStoreRegisterAllow($store_id, $access_token, $store_service_id)
    {
        $store_details = Provider::query()->where('id', $store_id)->first();
        if ($store_details != Null) {
            if ($store_details->status == 2) {
                return response()->json([
                    "status" => 3,
//                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message" => __('store_messages.3'),
                    "message_code" => 3
                ]);
            }
            if ($store_details->access_token != $access_token) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('store_messages.4'),
                    "message_code" => 4
                ]);
            }
            if ($store_details->verified_at == Null) {
                return response()->json([
                    'status' => 2,
//                    'message' => "Store Not Verified!",
                    'message' => __('store_messages.2'),
                    "message_code" => 2,
                ]);
            }
            $store_service = ProviderServices::query()->where('id', $store_service_id)->where('provider_id', $store_details->id)->first();
            if ($store_service != Null) {
                if ($store_service->status == 2) {
                    return response()->json([
                        "status" => 3,
//                        "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                        "message" => __('store_messages.3'),
                        "message_code" => 3,
                    ]);
                }
                if ($store_service->status == 0) {
                    return response()->json([
                        "status" => 7,
//                        "message" => 'Your service request is pending!',
                        "message" => __('store_messages.7'),
                        "message_code" => 7,
                    ]);
                }
                if ($store_service->status == 3) {
                    return response()->json([
                        "status" => 8,
//                        "message" => 'Your service request rejected by admin!',
                        "message" => __('store_messages.8'),
                        "message_code" => 8,
                    ]);
                }
                return $store_details;
            } else {
                return response()->json([
                    "status" => 6,
//                    "message" => 'store service not found!',
                    "message" => __('store_messages.6'),
                    "message_code" => 6,
                ]);
            }
        } else {
            return response()->json([
                "status" => 5,
//                "message" => 'store not found!',
                "message" => __('store_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function addOrderStatus($order_id, $update_status, $currency)
    {
        $order_status = UserProductBookingStatus::query()->where('order_id', $order_id)->where('status', $update_status)->first();
        if ($order_status == Null) {
            $order_status = new UserProductBookingStatus();
            $order_status->order_id = $order_id;
            $order_status->status = $update_status;
            $order_status->save();
            if ($update_status == 2 || $update_status == 3) {
                $order = UserProductBooking::query()->where('id', $order_id)->first();
                if ($order == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('store_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                $user = User::query()->where('id', $order->user_id)->first();
                if ($order == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('store_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                $this->notificationClass->userStoreDeliveryNotification($order_id,$user->device_token,$update_status,$user->login_device,$user->language);
//                return response()->json([
//                    "status" => 1,
//                    "message" => "success!"
//                ]);
            }
//            return response()->json([
//                "status" => 1,
//                "message" => "success!"
//            ]);
        }
//        else {
//            return response()->json([
//                "status" => 1,
//                "message" => "success!"
//            ]);
//        }
        $order_details = UserProductBooking::query()->where('id', $order_id)->first();
        if ($order_details != Null) {
            $store_details = StoreDetails::query()->where('id', $order_details->store_id)->first();
            if ($store_details != Null) {
//                        $this->notificationClass->userStoreDeliveryNotification($order_id, $user->device_token, $update_status);
//                        return response()->json([
//                            "status" => 1,
//                            "message" => "success!"
//                        ]);
                return $this->getOrderDispatcher($store_details->id, $currency);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "store details not found!",
                    "message" => __('store_messages.161'),
                    "message_code" => 71,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "order details not found!",
                "message" => __('store_messages.59'),
                "message_code" => 59,
            ]);
        }
    }

    public function getOrderDispatcher($store_id,$currency,$lang="en")
    {
        $store_details = StoreDetails::query()
            ->select("store_details.id","providers.login_device")
            ->join("provider_services","provider_services.id","=","store_details.provider_service_id")
            ->join("providers","providers.id","=","provider_services.provider_id")
            ->where('store_details.id', '=', $store_id)
            ->first();
        if ($store_details == Null){
            return response()->json([
                "status" => 0,
//                "message" => "store details not found!",
                "message" => __('store_messages.161'),
                "message_code" => 71,
            ]);
        }

        $date = new \DateTime();
        $date->modify('-24 hours');
        $date = $date->format('Y-m-d h:i:s');
        $get_orders_qry = UserProductBooking::query()->select('id as order_id', 'user_id as customer_id',
            'order_no', 'user_name as customer_name', 'delivery_address', 'taken_away_type as user_taken_type',
            //DB::raw("(CASE WHEN user_store_product_booking.taken_away_type !=2 THEN CASE WHEN user_store_product_booking.delivery_people_id !='' THEN 2 ELSE 1 END ELSE 2 END) as driver_assign_type"),
            DB::raw('ROUND(total_pay * ' . $currency . ',2) As total_amount'),
            //'total_pay as total_amount',
            'order_type',
            DB::raw("(CASE WHEN user_store_product_booking.order_date_time IS NOT NULL THEN user_store_product_booking.order_date_time ELSE '' END) as schedule_order_date_time"),
            DB::raw("(CASE WHEN user_store_product_booking.order_date_time IS NOT NULL THEN DATE_FORMAT(order_date_time, '%a %d %b, %Y') ELSE '' END) as schedule_order_date"),
            DB::raw("(CASE WHEN user_store_product_booking.order_date_time IS NOT NULL THEN DATE_FORMAT(order_date_time, '%h:%i %p') ELSE '' END) as schedule_order_time"),
            'status as order_status', 'order_product_list', 'payment_type')
            ->where('store_id', $store_id)
            ->where('status', '!=', 11)
            ->orderBy('created_at', 'asc')
            //->where('created_at', '>', $date)
            ->get()->toArray();

        $get_orders = [];
        foreach ($get_orders_qry as $key=> $singel) {
            $get_orders[$key]['order_id'] = $singel['order_id'];
            $get_orders[$key]['order_no'] = $singel['order_no'];
            $get_orders[$key]['customer_name'] = $singel['customer_name'];
            $get_orders[$key]['delivery_address'] = $singel['delivery_address'];
            $get_orders[$key]['total_amount'] = $singel['total_amount'];
            $get_orders[$key]['order_status'] = $singel['order_status'];
            //$get_orders[$key]['booking_type'] = $singel['booking_type'];
            $get_orders[$key]['order_type'] = $singel['order_type'];
            $get_orders[$key]['order_product_list'] = $singel['order_product_list'];
            $get_orders[$key]['schedule_order_date_time'] = $singel['schedule_order_date_time'];
            $get_orders[$key]['schedule_order_date'] = $this->notificationClass->dateLangConvert($singel['schedule_order_date'],$lang);
            $get_orders[$key]['schedule_order_time'] = $singel['schedule_order_time'];
            $get_orders[$key]['payment_type'] = $singel['payment_type'];
            $get_orders[$key]['user_taken_type'] = $singel['user_taken_type'];
        }

        $pending_orders = array_filter($get_orders, function ($var) {
            return ($var['order_status'] == 1);
        });
        $accepted_orders = array_filter($get_orders, function ($var) {
            return ($var['order_status'] == 2);
        });
        $processing_orders = array_filter($get_orders, function ($var) {
            return ($var['order_status'] == 5 || $var['order_status'] == 6 || $var['order_status'] == 7);
        });
        $dispatched_orders = array_filter($get_orders, function ($var) {
            return ($var['order_status'] == 8);
        });
        $pending_order_list = [];
        $accepted_order_list = [];
        $processing_order_list = [];
        $dispatched_order_list = [];
        foreach ($pending_orders as $order) {
            $pending_order_list[] = $order;
        }
        foreach ($accepted_orders as $order) {
            $accepted_order_list[] = $order;
        }
        foreach ($processing_orders as $order) {
            $processing_order_list[] = $order;
        }
        foreach ($dispatched_orders as $order) {
            $dispatched_order_list[] = $order;
        }

        return response()->json([
            "status" => 1,
//            "message" => "success",
            "message" => __('store_messages.1'),
            "message_code" => 1,
            "pending_orders" => $pending_order_list,
            "accepted_orders" => $accepted_order_list,
            "processing_order" => $processing_order_list,
            "dispatched_order" => $dispatched_order_list,
        ]);
    }

}
