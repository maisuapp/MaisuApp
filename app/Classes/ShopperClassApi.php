<?php


namespace App\Classes;


use App\model\Provider;
use App\model\ProviderServices;
use App\model\ServiceCategory;
use App\model\ShopperProviderDetails;
use App\model\ShopperProviderDocuments;
use App\model\ShopperRequiredDocuments;
use App\model\StoreDetails;
use App\model\UserProductBooking;
use Illuminate\Support\Facades\DB;

class ShopperClassApi
{
    private $store_service_id_array;
    private $transport_service_id_array;
    private $transport_other_service_id_array;
    private $adminClass;

    public function __construct(AdminClass $adminClass)
    {
        $this->adminClass = $adminClass;
        $transport_service = [1, 2, 4];
        $this->store_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->transport_service_id_array = $transport_service;
        $service_cat = ServiceCategory::query()->whereIN('category_type', [3, 4])->where('status', 1)->get()->pluck('id')->toArray();
        $this->transport_other_service_id_array = array_merge($transport_service, $service_cat);
    }

    //register login response
    public function shopperLoginRegisterResponse($provider_details)
    {
        if ($provider_details == Null) {
            return response()->json([
                'status' => 0,
                'message' => 'something went to wrong!',
                "message_code" => 9
            ]);
        }
        if ($provider_details['status'] == 0 || $provider_details['status'] == 1) {

            $get_provider_service = ProviderServices::where('provider_id', $provider_details['id'])
                ->whereIn('service_cat_id', $this->store_service_id_array)
                ->where('status', 1)
                ->first();
            if ($get_provider_service != Null) {
                $provider_current_status = $get_provider_service->current_status;

                $store_details = StoreDetails::where('provider_service_id', $get_provider_service->id)->first();
                if ($store_details != Null) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'You Are Not Register As Shopper Service Provider!',
                        "message_code" => 129
                    ]);
                }
                $required_documents = ShopperRequiredDocuments::where('status', 1)->get();
                if (!$required_documents->isEmpty()) {
                    foreach ($required_documents as $document) {
                        $get_provider_document = ShopperProviderDocuments::where('provider_id', $provider_details['id'])->where('req_document_id', $document->id)->first();
                        if ($get_provider_document != Null) {
                            if ($get_provider_document->status == 1) {
                                $provider_current_status = 1;
                            } else {
                                $provider_current_status = 0;
                            }
                        } else {
                            $provider_current_status = 0;
                        }
                    }
                }
            } else {
                $check_provider_service_for_transport = ProviderServices::where('provider_id', $provider_details['id'])
                    ->whereIn('service_cat_id', $this->transport_other_service_id_array)
                    ->where('status', 1)
                    ->first();
                if ($check_provider_service_for_transport != Null) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'You Are Not Register As Shopper Service Provider!',
                        "message_code" => 129
                    ]);
                }
                $provider_current_status = 0;
            }

            $extra_provider_details = ShopperProviderDetails::where('provider_id', $provider_details['id'])->first();
            return response()->json([
                'status' => 1,
                'message' => 'success!',
                "message_code" => 1,
                'shopper_id' => $provider_details['id'],
                'shopper_verified' => $provider_details['verified_at'] != Null ? 1 : 0,
                'shopper_current_status' => $provider_current_status,
                'shopper_name' => $provider_details['name'],
                'access_token' => $provider_details['access_token'],
                'email' => $provider_details['email'] != Null ? $provider_details['email'] : '',
                'login_type' => $provider_details['login_type'],
                'service_radius' => $provider_details['service_radius'] != Null ? $provider_details['service_radius'] - 0 : 0,
                'profile_image' => $provider_details['avatar'] != Null ? url('/assets/images/profile-images/shopper/' . $provider_details['avatar']) : '',
                'contact_number' => $provider_details['contact_number'] != Null ? $provider_details['contact_number'] : "",
                'address' => $extra_provider_details != Null ? $extra_provider_details->address : "",
                'landmark' => $extra_provider_details != Null ? $extra_provider_details->landmark != Null ? $extra_provider_details->landmark : "" : "",
                'lat_long' => $extra_provider_details != Null && $extra_provider_details != Null ? $extra_provider_details->lat . ',' . $extra_provider_details->long : "",
                'gender' => $provider_details['gender'] != Null ?( ($provider_details['gender'] == 1 ? $provider_details['gender'] - 0 : ($provider_details['gender'] == 2 ? $provider_details['gender'] - 0 : 0))) : 0,
                'select_country_code' => $provider_details['country_code'] != Null ? $provider_details['country_code'] : "",
                'select_currency' => $provider_details['currency'] != Null ? $provider_details['currency'] : "",
                'select_language' => $provider_details['language'] != Null ? $provider_details['language'] : "",
            ]);
        } elseif ($provider_details['status'] == 2) {
            return response()->json([
                'status' => 3,
                'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                "message_code" => 3
            ]);
        } elseif ($provider_details['status'] == 3) {
            $check_provider_service_for_transport = ProviderServices::where('provider_id', $provider_details['id'])
                ->whereIn('service_cat_id', $this->transport_other_service_id_array)
//                ->orWhereIn('service_cat_id', $this->delivery_service_id_array)
                ->where('status', 1)
                ->first();
            if ($check_provider_service_for_transport != Null) {
                return response()->json([
                    'status' => 0,
                    'message' => 'You Are Not Register As Shopper Service Provider!',
                    "message_code" => 129
                ]);
            }
            return response()->json([
                'status' => 1,
                'message' => 'Your Are Not Register Any Service!',
                "message_code" => 1,
                'shopper_id' => $provider_details['id'],
                'shopper_verified' => $provider_details['verified_at'] != Null ? 1 : 0,
                'shopper_current_status' => 0,
                'shopper_name' => $provider_details['name'],
                'access_token' => $provider_details['access_token'],
                'email' => $provider_details['email'] != Null ? $provider_details['email'] : '',
                'login_type' => $provider_details['login_type'],
                'service_radius' => $provider_details['service_radius'] != Null ? $provider_details['service_radius'] - 0 : 0,
                'profile_image' => $provider_details['avatar'] != Null ? url('/assets/images/profile-images/shopper/' . $provider_details['avatar']) : '',
                'contact_number' => $provider_details['contact_number'] != Null ? $provider_details['contact_number'] : "",
                'address' => "",
                'landmark' => "",
                'lat_long' => "",
                'gender' => $provider_details['gender'] != Null ? (($provider_details['gender'] == 1 ? $provider_details['gender'] - 0 :( $provider_details['gender'] == 2 ? $provider_details['gender'] - 0 : 0))) : 0,
                'select_country_code' => $provider_details['country_code'] != Null ? $provider_details['country_code'] : "",
                'select_currency' => $provider_details['currency'] != Null ? $provider_details['currency'] : "",
                'select_language' => $provider_details['language'] != Null ? $provider_details['language'] : "",
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'something went to wrong!',
            ]);
        }
    }

    public function shopperRegisterAllow($shopper_id, $access_token)
    {
        $provider_details = Provider::where('id', $shopper_id)->first();
        if ($provider_details != Null) {
            if ($provider_details->status == 2) {
                return response()->json([
                    "status" => 3,
                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message_code" => 3
                ]);
            }
            if ($provider_details->access_token != $access_token) {
                return response()->json([
                    'status' => 4,
                    'message' => "Access Token Not Match!",
                    "message_code" => 4
                ]);
            }
            if ($provider_details->verified_at == Null) {
                return response()->json([
                    'status' => 2,
                    'message' => "shopper Not Verified!",
                    "message_code" => 2,
                ]);
            }
            return $provider_details;
        } else {
            return response()->json([
                "status" => 5,
                "message" => 'shopper not found!',
                "message_code" => 5
            ]);
        }
    }

    public function shopperServiceList($provider_id)
    {

        $provider_lang = Provider::query()->select('language')->where('id', $provider_id)->first();
//        return $provider_lang->language;
        if ($provider_lang != null) {

            $lang_prefix = $this->adminClass->get_langugae_fields($provider_lang->language);

        } else {
            $lang_prefix = "";
        }
        $get_provider_service_list = ProviderServices::select('provider_services.id as shopper_service_id', 'service_category.' . $lang_prefix . 'name as shopper_service_name', 'provider_services.status as shopper_service_status', 'provider_services.current_status as shopper_service_current_status')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('provider_services.provider_id', $provider_id)
            ->whereIN('provider_services.service_cat_id', $this->store_service_id_array)
            ->get();

        return response()->json([
            "status" => 1,
            "message" => "success!",
            "message_code" => 1,
            "shopper_service_list" => $get_provider_service_list
        ]);
    }

    public function getOrderDispatcher($provider_id, $currency, $provider_status)
    {
        $date = new \DateTime();
        $date->modify('-24 hours');
        $date = $date->format('Y-m-d h:i:s');
        $provider_current_status = 0;
        $provider_services_list = '';
        $provider_details = Provider::query()->where('id', $provider_id)->first();
        if ($provider_details == Null) {
            return response()->json([
                "status" => 0,
                "message" => "provider details not found",
                "message_code" => 130,
            ]);
        }
        $required_documents = ShopperRequiredDocuments::where('status', 1)->get();
        if (!$required_documents->isEmpty()) {
            foreach ($required_documents as $document) {
                $get_provider_document = ShopperProviderDocuments::where('provider_id', $provider_id)->where('req_document_id', $document->id)->first();
                if ($get_provider_document != Null) {
                    if ($get_provider_document->status == 1) {
                    } else {
                        $provider_status = 0;
                    }
                } else {
                    $provider_status = 0;
                }
            }
        }
        $lang_prefix = $this->adminClass->get_langugae_fields($provider_details->language);
        $provider_services = ProviderServices::query()->select('provider_services.id',
            'provider_services.service_cat_id', 'service_category.' . $lang_prefix . 'name as service_cat_name', 'provider_services.status', 'provider_services.current_status')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('provider_services.provider_id', $provider_id)
//            ->whereIN('provider_services.service_cat_id', $this->on_demand_service_id_array)
            ->whereIN('service_category.category_type', [2])
            ->get();
        if ($provider_services != Null) {
            $provider_services_list = $provider_services->pluck('service_cat_name')->toArray();
            $provider_services_list = implode(', ', $provider_services_list);
//            $get_status = $provider_services->pluck('status')->toArray();
            $get_current_status = $provider_services->pluck('current_status')->toArray();
//            if (in_array(1, $get_status)) {
            if (in_array(1, $get_current_status)) {
                $provider_current_status = 1;
            }
//            }
        }

        if ($provider_details->avatar != Null) {
            if (filter_var($provider_details->avatar, FILTER_VALIDATE_URL) == true) {
                $provider_profile_image = $provider_details->avatar;
            } else {
                $provider_profile_image = url('/assets/images/profile-images/shopper/' . $provider_details->avatar);
            }
        } else {
            $provider_profile_image = "";
        }
        $pending_order_list = [];
        $accepted_order_list = [];
        $processing_order_list = [];
        $completed_order_list = [];
        if ($provider_services != Null) {
            $store_url = url('/assets/images/store-images');
            $service_category = $provider_services->pluck('service_cat_id')->toArray();
            $get_orders = UserProductBooking::query()->select(
                'user_store_product_booking.id as order_id',
                'user_store_product_booking.user_name as customer_name',
                'user_store_product_booking.payment_type as order_payment_type',
                'user_store_product_booking.payment_status as order_payment_status',
//                'users.gender as customer_gender',
                'user_store_product_booking.order_no',
                'service_category.' . $lang_prefix . 'name as service_cat_name',
                'user_store_product_booking.order_product_list',
                'user_store_product_booking.order_type',
                'user_store_product_booking.order_date_time as service_date_time',
                DB::raw("(CASE WHEN user_store_product_booking.order_date_time!='' THEN user_store_product_booking.order_date_time ELSE '' END) as schedule_order_date_time"),
                DB::raw("(CASE WHEN user_store_product_booking.order_date_time!='' THEN DATE_FORMAT(order_date_time, '%a %d %b, %Y') ELSE '' END) as schedule_order_date"),
                DB::raw("(CASE WHEN user_store_product_booking.order_date_time!='' THEN DATE_FORMAT(order_date_time, '%h:%i %p') ELSE '' END) as schedule_order_time"),

                DB::raw('ROUND(user_store_product_booking.shopper_earring * ' . $currency . ',2) As total_pay'),
//                'user_store_product_booking.total_pay',
                'user_store_product_booking.lat_long',
                'user_store_product_booking.status as order_status',
                'user_store_product_booking.order_shopper_status',
                'store_details.id as store_id',
                'store_details.name as store_name',
                'providers.contact_number as store_contact_no',
                DB::raw("(concat(store_details.address_lat,',',store_details.address_long)) as store_lat_long"),
                DB::raw("(CASE WHEN store_details.image!='' THEN concat('$store_url','/',store_details.image) ELSE '' END) as store_image")
            )
                ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
                ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
                ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('user_store_product_booking.shopper_id', $provider_id)
                ->whereIn('user_store_product_booking.service_cat_id', $service_category)
                ->orderBy('user_store_product_booking.created_at', 'desc')
//                ->where('created_at', '>', $date)
                ->get()->toArray();
            if (count($get_orders) > 0) {
                $pending_orders = array_filter($get_orders, function ($var) {
                    return ($var['order_shopper_status'] == 1);
                });
                $accepted_orders = array_filter($get_orders, function ($var) {
                    return ($var['order_shopper_status'] == 2 || $var['order_shopper_status'] == 3);
                });
                $processing_orders = array_filter($get_orders, function ($var) {
                    return ($var['order_shopper_status'] == 4 || $var['order_shopper_status'] == 5 || $var['order_shopper_status'] == 6);
                });
                $completed_orders = array_filter($get_orders, function ($var) {
                    return ($var['order_shopper_status'] == 7);
                });
                foreach ($pending_orders as $order) {
                    $pending_order_list[] = $order;
                }
                foreach ($accepted_orders as $order) {
                    $accepted_order_list[] = $order;
                }
                foreach ($processing_orders as $order) {
                    $processing_order_list[] = $order;
                }
                foreach ($completed_orders as $order) {
                    $completed_order_list[] = $order;
                }
            }
        }
        return response()->json([
            "status" => 1,
            "message" => "success",
            "message_code" => 1,
            "shopper_name" => $provider_details != Null ? $provider_details->name : '',
            "shopper_status" => $provider_status,
            "shopper_profile_image" => $provider_profile_image,
            "shopper_service_radius" => $provider_details != Null ? ($provider_details->service_radius != Null ? $provider_details->service_radius . " km" : '') : '',
            "shopper_services_list" => $provider_services_list,
            "current_status" => $provider_current_status,
            "pending_orders" => $pending_order_list,
            "accepted_orders" => $accepted_order_list,
            "processing_order" => $processing_order_list,
            "completed_order" => $completed_order_list,
        ]);
    }

    public function providerDocumentList($provider_id)
    {
        $provider_lang = Provider::query()->select('language')->where('id', $provider_id)->first();
//        return $provider_lang->language;
        if ($provider_lang != null) {

            $lang_prefix = $this->adminClass->get_langugae_fields($provider_lang->language);
        } else {
            $lang_prefix = "";
        }
        $document_list = [];
        $required_documents = ShopperRequiredDocuments::where('status', 1)->get();
        if (!$required_documents->isEmpty()) {
            $provider_document = [];
            foreach ($required_documents as $document) {
                $get_provider_document = ShopperProviderDocuments::where('provider_id', $provider_id)->where('req_document_id', $document->id)->first();
                $document_list[] = [
                    "document_id" => $document->id,
                    "document_name" => $document->name,
                    "document_file" => $get_provider_document != Null ? url('/assets/images/provider-documents/' . $get_provider_document->document_file) : '',
                    "document_status" => $get_provider_document != Null ? $get_provider_document->status : 3,
                ];
            }
        }
        return response()->json([
            "status" => 1,
            "message" => "success",
            "message_code" => 1,
            "required_document_list" => $document_list
        ]);
    }
}
