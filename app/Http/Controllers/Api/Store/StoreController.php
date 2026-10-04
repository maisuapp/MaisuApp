<?php

namespace App\Http\Controllers\Api\Store;

use App\Classes\NotificationClass;
use App\Classes\StoreClassApi;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\ShopperProviderDetails;
use App\model\ShopperRating;
use App\model\StoreDetails;
use App\model\StoreProductDetails;
use App\model\StoreTimings;
use App\model\TransportDriverDetails;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserProductBooking;
use App\model\UserProductBookingOptions;
use App\model\UserProductBookingQuantity;
use App\model\UserProductBookingSize;
use App\model\UserProductBookingStatus;
use App\model\UserProductBookingToppings;
use App\model\WorldCurrency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{

    //json response status [
    //    0 => false,
    //    1 => true,
    //    2 => registration pending,
    //    3 => app user blocked,
    //    4 => app user access token not match,
    //    5 => app user not found,
    //    6 => user service not register,
    //    7 => user service requested,
    //    8 => user service rejected,
    //  ]

    //ApiLogDetail logger type => 0:user,1:store,2:driver,3:provider
    private $storeClassApi;
    private $notificationClass;

    public function __construct(StoreClassApi $storeClassApi, NotificationClass $notificationClass)
    {
        $this->storeClassApi = $storeClassApi;
        $this->notificationClass = $notificationClass;
    }

    public function postStoreServiceDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }

        $store_details = StoreDetails::select('store_details.name as store_name', 'store_details.image as store_banner', 'provider_services.current_status', 'service_category.name as service_category_name', 'service_category.icon_name as service_icon')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('store_details.provider_service_id', $request->get('store_service_id'))->first();
        if ($store_details != Null) {
            return response()->json([
                "status" => 1,
                "message" => "success!",
                "message_code" => 1,
                "store_name" => $store_details->store_name,
                "store_banner" => $store_details->store_banner != Null ? url('/assets/images/store-images/' . $store_details->store_banner) : '',
                "store_current_status" => $store_details->current_status,
                "service_category_name" => $store_details->service_category_name,
                "service_category_icon" => $store_details->service_icon != Null ? url('/assets/images/service-category/' . $store_details->service_icon) : '',
            ]);
        } else {
            return response()->json([
                "status" => 0,
                "message" => "store details not found!",
                "message_code" => 71,
            ]);
        }
    }

    public function postStoreCheckServiceStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $provider_details = Provider::query()->where('id', $request->get('store_id'))->first();
        if ($provider_details != Null) {
            if ($provider_details->status == 2) {
                return response()->json([
                    "status" => 3,
//                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message" => __('store_messages.3'),
                    "message_code" => 3,
                ]);
            }
            if ($provider_details->access_token != $request->get('access_token')) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('store_messages.4'),
                    "message_code" => 4,
                ]);
            }
            return $this->storeClassApi->storeLoginRegisterResponse($provider_details);
        } else {
            return response()->json([
                "status" => 5,
//                "message" => "store provider not found!",
                "message" => __('store_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function postStoreDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }

        $store_details = StoreDetails::query()->where('provider_service_id', $request->get('store_service_id'))->first();
        if ($store_details != Null) {
            $store_timing = StoreTimings::query()->select('day', 'store_open_time', 'store_close_time')->where('store_id', $store_details->id)->get();
            if ($store_timing == Null) {
                $store_timing = [];
            }
            $store_currency = WorldCurrency::query()->where('symbol', $store_check->currency)->first();
            if ($store_currency == Null) {
                $store_currency = WorldCurrency::query()->where('default_currency', 1)->first();
            }
            $currency = $store_currency->ratio;
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('store_messages.1'),
                "message_code" => 1,
                "store_name" => trim(ucwords(strtolower($store_details->name))),
                "store_banner" => $store_details->image != Null ? url('/assets/images/store-images/' . $store_details->store_banner) : '',
                "offer_min_bill_amount" => round($store_details->offer_min_amount * $currency, 2),
                "offer_discount" => $store_details->offer_type != Null ? ($store_details->offer_type == 1 ? round($store_details->offer_amount * $currency, 2) : $store_details->offer_amount) : 0,
                "offer_discount_type" => $store_details->offer_type != Null ? $store_details->offer_type : 0,
                "store_address" => $store_details->address,
                "store_address_lat" => $store_details->address_lat,
                "store_address_long" => $store_details->address_long,
                "eta_delivery_time" => $store_details->eta_delivery_time != Null ? $store_details->eta_delivery_time : '',
                "store_packaging_charges" => round($store_details->packaging_charges * $currency, 2),
                "taken_away_type"=>$store_details->taken_away_type-0,
                "store_rating" => $store_details->rating,
                "store_description" => $store_details->description,
                "store_timings" => $store_timing
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "store details not found!",
                "message" => __('store_messages.161'),
                "message_code" => 9,
            ]);
        }
    }

    public function postStoreGetAndUpdateOffer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer",
            "is_update" => "required|in:0,1",
            "offer_min_bill_amount" => "nullable|numeric",
            "offer_discount" => "nullable|numeric",
            "offer_discount_type" => "nullable|numeric|in:0,1,2",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }
        $store_currency = WorldCurrency::query()->where('symbol', $store_check->currency)->first();
        if ($store_currency == Null) {
            $store_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $store_currency->ratio;
        $store_details = StoreDetails::query()->where('provider_service_id', $request->get('store_service_id'))->first();
        if ($store_details != Null) {
            if ($request->get('is_update') != 0) {
                $validator = Validator::make($request->all(), [
                        "offer_min_bill_amount" => "nullable|numeric",
                        "offer_discount" => "nullable|numeric",
                        "offer_discount_type" => "nullable|numeric|in:0,1,2",
                    ]
                );
                if ($validator->fails()) {
                    return response()->json([
                        "status" => 0,
                        "message" => $validator->errors()->first(),
                        "message_code" => 9,
                    ]);
                }
                if ($request->get('offer_min_bill_amount') == 0 || $request->get('offer_min_bill_amount') == Null) {
                    $store_details->offer_min_amount = 0.00;
                } else {
                    $store_details->offer_min_amount = round($request->get('offer_min_bill_amount') / $currency, 2);
                }
                if ($request->get('offer_discount') == 0 || $request->get('offer_discount') == Null || $request->get('offer_discount') == 0) {
                    $store_details->offer_amount = 0.00;
                    $store_details->offer_type = 0;
                } else {
                    if ($request->get('offer_discount_type') == 0 || $request->get('offer_discount_type') == Null) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "The offer discount type field is required.",
                            "message" => __('store_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    if ($request->get('offer_discount_type') == 1) {
                        $store_details->offer_amount = round($request->get('offer_discount') / $currency, 2);
                    } else {
                        $store_details->offer_amount = $request->get('offer_discount');
                    }
                    $store_details->offer_type = $request->get('offer_discount_type');
                }
                $store_details->save();
            }
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('store_messages.1'),
                "message_code" => 1,
                "offer_min_bill_amount" => round($store_details->offer_min_amount * $currency, 2) - 0,
                "offer_discount" => round($store_details->offer_amount * $currency, 2) - 0,
                "offer_discount_type" => $store_details->offer_type - 0
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "store details not found!",
                "message" => __('store_messages.161'),
                "message_code" => 71,
            ]);
        }
    }

    //store timing update pending
    public function postStoreGetAndUpdateSettings(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =1, $request->get('store_id'), "postStoreGetAndUpdateSettings", $request->all());
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer",
            "is_update" => "required|in:0,1"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }
        $store_currency = WorldCurrency::query()->where('symbol', $store_check->currency)->first();
        if ($store_currency == Null) {
            $store_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $store_currency->ratio;
        $store_lang = isset($store_check->language)?$store_check->language:"";
        if($store_lang !="en" && $store_lang != "" && $store_lang != "Null" ){
            $store_lang = $store_lang."_";
        }else{
            $store_lang ="";
        }

        $provider_details = Provider::query()->where('id', $request->get('store_id'))->first();
        if ($provider_details != Null) {
            $store_details = StoreDetails::query()->where('provider_service_id', $request->get('store_service_id'))->first();
            if ($store_details != Null) {
                $days = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
                if ($request->get('is_update') != 0) {
                    $validator = Validator::make($request->all(), [
                        "eta_delivery_time" => "nullable|numeric",
                        "service_radius" => "required|numeric",
                        "order_min_amount" => "nullable|numeric",
                        "packaging_charges" => "nullable|numeric",
                        "store_timing" => "required",
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            "status" => 0,
                            "message" => $validator->errors()->first(),
                            "message_code" => 9,
                        ]);
                    }
                    $store_timings = json_decode($request->get('store_timing'), true);
                    if (count($store_timings) < 1) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "store timing not set!",
                            "message" => __('store_messages.68'),
                            "message_code" => 9,
                        ]);
                    }

                    $provider_details->service_radius = $request->get('service_radius');
                    $provider_details->save();
                    if ($request->get('eta_delivery_time') != Null) {
                        $store_details->eta_delivery_time = $request->get('eta_delivery_time');
                    } else {
                        $store_details->eta_delivery_time = Null;
                    }
                    if ($request->get('order_min_amount') == 0 || $request->get('order_min_amount') == Null) {
                        $store_details->order_min_amount = 0;
                    } else {
                        $store_details->order_min_amount = round($request->get('order_min_amount') / $currency, 2);
                    }
                    if ($request->get('packaging_charges') == 0 || $request->get('packaging_charges') == Null) {
                        $store_details->packaging_charges = 0;
                    } else {
                        $store_details->packaging_charges = round($request->get('packaging_charges') / $currency, 2);
                    }
                    $store_details->save();
                    foreach ($store_timings as $store_timing) {
                        if (in_array($store_timing['day'], $days)) {
                            //check_duplicate_value
                            if ($store_timing['isCheck'] == "true" && !in_array($store_timing['open_time'], ["00:00 AM", "00:00 am"]) && !in_array($store_timing['close_time'], ["00:00 PM", "00:00 pm"])) {
                                if ($store_timing['open_time'] != "00:00 AM") {
                                    $set_store_timing = StoreTimings::query()->where('store_id', $store_details->id)->where('day', $store_timing['day'])->first();
                                    if ($set_store_timing == Null) {
                                        $set_store_timing = new StoreTimings();
                                        $set_store_timing->store_id = $store_details->id;
                                        $set_store_timing->day = strtoupper(trim($store_timing['day']));
                                    }
                                    $set_store_timing->store_open_time = Date('H:i:s', strtotime($store_timing['open_time']));
                                    $set_store_timing->store_close_time = Date('H:i:s', strtotime($store_timing['close_time']));
                                    $set_store_timing->save();
                                }
                            } else {
//                                StoreTimings::query()->where('store_id', $store_details->id)->where('day', $store_timing['day'])->delete();
                                $set_store_timing = StoreTimings::query()->where('store_id', $store_details->id)->where('day', $store_timing['day'])->first();
                                if ($set_store_timing == Null) {
                                    $set_store_timing = new StoreTimings();
                                    $set_store_timing->store_id = $store_details->id;
                                    $set_store_timing->day = strtoupper(trim($store_timing['day']));
                                }
                                $set_store_timing->store_open_time = "00:00:00";
                                $set_store_timing->store_close_time = "00:00:00";
                                $set_store_timing->save();
                            }
                        }
                    }
                }
                $get_store_timings = StoreTimings::query()->where('store_id', $store_details->id)->get();
                $store_time_list = [];
                if (count($get_store_timings) == 0) {
                    for ($i = 0; $i < 7; $i++) {
                        $store_time_list[] = [
                            "day" => $days[$i],
                            "display_day" => (config('global.lang_constant.'.$days[$i].'.'.$store_lang.'value')!="")?config('global.lang_constant.'.$days[$i].'.'.$store_lang.'value'):$days[$i],
                            "isCheck" => false,
                            "open_time" => '00:05 AM',
                            "close_time" => '11:55 PM'
                        ];
                    }
                }
                else {
                    $get_store_day = $get_store_timings->pluck('day')->toArray();
                    $get_store_open_time = $get_store_timings->pluck('store_open_time')->toArray();
                    $get_store_close_time = $get_store_timings->pluck('store_close_time')->toArray();
                    if (in_array("all", $get_store_day)) {
                        for ($i = 0; $i < 7; $i++) {
                            $store_time_list[] = [
                                "day" => $days[$i],
                                "display_day" => (config('global.lang_constant.'.$days[$i].'.'.$store_lang.'value')!="")?config('global.lang_constant.'.$days[$i].'.'.$store_lang.'value'):$days[$i],
                                "isCheck" => true,
                                "open_time" => Date('h:i A', strtotime($get_store_open_time[0])),
                                "close_time" => Date('h:i A', strtotime($get_store_close_time[0]))
                            ];
                        }
                    } else {
                        for ($i = 0; $i < 7; $i++) {
                            if (in_array($days[$i], $get_store_day)) {
                                $key_days = array_search($days[$i], $get_store_day);
                                if($get_store_open_time[$key_days] == "00:00:00" && $get_store_close_time[$key_days] == "00:00:00") {
                                    $store_time_list[] = [
                                        "day" => $days[$i],
                                        "display_day" => (config('global.lang_constant.'.$days[$i].'.'.$store_lang.'value')!="")?config('global.lang_constant.'.$days[$i].'.'.$store_lang.'value'):$days[$i],
                                        "isCheck" => false,
                                        "open_time" => '00:05 AM',
                                        "close_time" => '11:55 PM'
                                    ];
                                }
                                else {
                                    $store_time_list[] = [
                                        "day" => $days[$i],
                                        "display_day" => (config('global.lang_constant.'.$days[$i].'.'.$store_lang.'value')!="")?config('global.lang_constant.'.$days[$i].'.'.$store_lang.'value'):$days[$i],
                                        "isCheck" => true,
                                        "open_time" => Date('h:i A', strtotime($get_store_open_time[$key_days])),
                                        "close_time" => Date('h:i A', strtotime($get_store_close_time[$key_days]))
                                    ];
                                }

                            } else {
                                $store_time_list[] = [
                                    "day" => $days[$i],
                                    "display_day" => (config('global.lang_constant.'.$days[$i].'.'.$store_lang.'value')!="")?config('global.lang_constant.'.$days[$i].'.'.$store_lang.'value'):$days[$i],
                                    "isCheck" => false,
                                    "open_time" => '00:05 AM',
                                    "close_time" => '11:55 PM'
                                ];
                            }
                        }
                    }
                }
                return response()->json([
                    "status" => 1,
//                    "message" => "success!",
                    "message" => __('store_messages.1'),
                    "message_code" => 1,
                    "eta_delivery_time" => $store_details->eta_delivery_time != Null ? $store_details->eta_delivery_time : '',
                    "order_min_amount" => round($store_details->order_min_amount * $currency, 2),
                    "packaging_charges" => round($store_details->packaging_charges * $currency, 2),
                    "service_radius" => $provider_details->service_radius,
                    "store_timings" => $store_time_list
                ]);
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
                "status" => 5,
//                "message" => "store details not found!",
                "message" => __('store_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function postStoreProductList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }

        $store_details = StoreDetails::query()->where('provider_service_id', $request->get('store_service_id'))->first();
        if ($store_details != Null) {
            $get_products = StoreProductDetails::query()->select('store_product_details.id as product_id',
                'store_product_details.name as product_name', 'store_product_details.status as product_status',
                'store_product_category.id as category_id', 'store_product_category.name as category_name')
                ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                ->where('store_product_details.store_id', $store_details->id)
                ->orderBy('store_product_category.name')
                ->get();
            $product_list = [];
            if ($get_products != Null) {
                $store_product_categories = array_unique(Arr::pluck($get_products, 'category_name', 'category_id'));
                $get_products = $get_products->groupBy('category_id');
                foreach ($get_products as $key => $get_product) {
                    $products = [];
                    foreach ($get_product as $product) {
                        $products[] = [
                            'product_id' => $product->product_id,
                            'product_name' => $product->product_name,
                            'product_status' => $product->product_status
                        ];
                    }
                    $product_list[] = [
                        'category_id' => $key,
                        'category_name' => $store_product_categories[$key],
                        'products' => $products
                    ];
                }
            }
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('store_messages.1'),
                "message_code" => 1,
                "product_list" => $product_list
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "store details not found!",
                "message" => __('store_messages.161'),
                "message_code" => 71,
            ]);
        }
    }

    public function postStoreUpdateProductStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer",
            "product_id" => "required|integer",
            "product_status" => "required|integer|in:0,1"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }

        $product = StoreProductDetails::query()->where('id', $request->get('product_id'))->first();
        if ($product != Null) {
            $product->status = $request->get('product_status');
            $product->save();
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('store_messages.1'),
                "message_code" => 1,
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "product not found!",
                "message" => __('store_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function postStoreUpdateCurrentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer",
            "update_status" => "required|integer|in:0,1",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }

        $store_service = ProviderServices::query()->where('id', $request->get('store_service_id'))->first();
        if ($store_service != Null) {
            $store_service->current_status = $request->get('update_status');
            $store_service->save();
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('store_messages.1'),
                "message_code" => 1,
                "store_current_status" => $store_service->current_status - 0
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "store service not found!",
                "message" => __('store_messages.6'),
                "message_code" => 6,
            ]);
        }
    }

    public function postStoreOrderDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer",
            "order_id" => "required|integer",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }
        $store_currency = WorldCurrency::query()->where('symbol', $store_check->currency)->first();
        if ($store_currency == Null) {
            $store_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $store_currency->ratio;

        $order_details = UserProductBooking::query()->select('landmark', 'flat_no', 'shopper_rating_status', 'order_shopper_status', 'shopper_id', 'shopper_type', 'id', 'user_id', 'order_no', 'delivery_address', 'delivery_people_id', 'created_at as order_date_time', 'order_date_time as schedule_order_date_time', 'order_type', 'status', 'order_delivery_status', 'total_pay', 'payment_type', 'delivery_people_name', 'total_item_cost', 'discount_cost', 'tax_cost', 'delivery_cost', 'delivery_admin_commission', 'packaging_cost', 'cancel_by', 'cancel_reason', 'prescription', 'promo_code', 'additional_request', 'taken_away_type','store_pay_settle_status')->where('id', $request->get('order_id'))->first();
        if ($order_details != Null) {
            $customer = User::query()->where('id', $order_details->user_id)->first();
            try {
                $delivery_person_details = TransportDriverDetails::query()->select('providers.country_code', 'providers.contact_number', 'providers.avatar',
                    'transport_driver_details.rating','transport_driver_details.driver_service', 'transport_driver_details.delivery_boy',
                    'transport_driver_details.courier_boy', 'transport_driver_details.rental_service','provider_services.service_cat_id',
                    'providers.language','providers.device_token','providers.id as driver_id')
                    ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                    ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                    ->where('transport_driver_details.id', $order_details->delivery_people_id)
                    ->first();

                if ($delivery_person_details != Null) {
                    if ($delivery_person_details->avatar != Null) {
                        if (filter_var($delivery_person_details->avatar, FILTER_VALIDATE_URL) == true) {
                            $delivery_person_image = $delivery_person_details->avatar;
                        } else {
                            $delivery_person_image = url('/assets/images/profile-images/provider/' . $delivery_person_details->avatar);
                        }
                    } else {
                        $delivery_person_image = "";
                    }
                    $delivery_person_contact_no = $delivery_person_details->country_code.$delivery_person_details->contact_number;
                    $delivery_person_rating = $delivery_person_details->rating;

                    $driver_service = $delivery_person_details->driver_service;
                    $delivery_boy = $delivery_person_details->delivery_boy;
                    $courier_boy = $delivery_person_details->courier_boy;
                    //$service_lang_converted = $this->notificationClass->serviceLangConvert();
                    $lang = $delivery_person_details->language;
                    if($lang !="en" && $lang != "" && $lang != "Null" ){
                        $lang = $lang."_";
                    }else{
                        $lang ="";
                    }
                    $ride_service =  (config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') !="")?config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') ." ":config('global.lang_constant.RIDE_SERVICE.value')." ";
                    $courier_delivery = (config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.COURIER_DELIVERY.value')." " ;
                    $store_delivery = (config('global.lang_constant.STORE_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.STORE_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.STORE_DELIVERY.value')." " ;
                    $rental_service = (config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')!="")?config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')." ":config('global.lang_constant.RENTAL_SERVICE.value')." " ;
                    if ($delivery_person_details->service_cat_id == 1) {
                        $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ? ", ".$store_delivery : ''), ', ');
                    } elseif ($delivery_person_details->service_cat_id == 2) {
                        $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ?  ", ".$store_delivery : '') . ($delivery_person_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');
                    } elseif ($delivery_person_details->service_cat_id == 4) {
                        $service = $courier_delivery;
                    } else {
                        $service = '';
                    }
                    $delivery_person_device_token = $delivery_person_details->device_token;
                    $driver_id = $delivery_person_details->driver_id;
                } else {
                    $delivery_person_image = "";
                    $delivery_person_contact_no = "";
                    $delivery_person_rating = 0;
                    $service = '';
                    $delivery_person_device_token = '';
                    $driver_id = 0;
                }
            } catch (\Exception $e) {
                $delivery_person_image = "";
                $delivery_person_contact_no = "";
                $delivery_person_rating = 0;
                $service = '';
                $delivery_person_device_token = '';
                $driver_id = 0;
            }

            if ($customer != Null && $customer->avatar != Null ) {
                if (filter_var($customer->avatar, FILTER_VALIDATE_URL) == true) {
                    $customer_image = $customer->avatar;
                } else {
                    $customer_image = url('/assets/images/profile-images/customer/' . $customer->avatar);
                }
            } else {
                $customer_image = "";
            }
            $order_items = UserProductBookingQuantity::query()->select('id','product_id', 'product_name','food_type as food_type',
                'num_of_items as num_of_items',
                DB::raw('ROUND(price_for_one,2) As price_for_one')
            )->where('order_id', $order_details->id)->get();
            if ($order_items->isEmpty()) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('store_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $order_product_list = [];
            foreach ($order_items as $product) {
                $size = UserProductBookingSize::query()->where('order_id', $order_details->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                $options = UserProductBookingOptions::query()->where('order_id', $order_details->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                $toppings = UserProductBookingToppings::query()->where('order_id', $order_details->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();

                $add_ons = array_merge($size, $options, $toppings);
                $add_ons = implode(', ', $add_ons);
                $order_product_list[] = [
                    "product_id" => $product->product_id,
                    "product_name" => $product->product_name,
                    "food_type" => $product->food_type,
                    "quantity" => $product->num_of_items,
                    "product_price" => round((($product->price_for_one) * $currency), 2),
                    "price_for_one" => round((($product->price_for_one * $product->num_of_items) * $currency), 2),
                    //"amount" => round(($product->product_price_for_one) * ($product->num_of_items), 2),
                    "add_ons" => $add_ons
                ];
            }
            $promocode_details = UsedPromocodeDetails::query()->where('id', $order_details->promo_code)->first();
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('store_messages.1'),
                "message_code" => 1,
                "order_id" => $order_details->id,
                "order_no" => $order_details->order_no,
                'additional_remark' => "" . $order_details->additional_request,
                "order_cancel_by" => ($order_details->cancel_by != Null) ? $order_details->cancel_by : "",
                "order_cancel_reason" => ($order_details->cancel_reason != Null) ? $order_details->cancel_reason : "",
                "customer_id" => $customer != Null ? $customer->id : 0,
                "customer_fcm_token" => $customer != Null ? $customer->device_token : '',
                "customer_name" => $customer != Null ? $customer->first_name : '',
                "customer_contact" => $customer != Null ? $customer->country_code.$customer->contact_number : '',
                "customer_image" => $customer_image,
                'prescription' => $order_details->prescription != Null ? url('/assets/images/prescription-images/' . $order_details->prescription) : "",
                "delivery_address" => $order_details->delivery_address,
                "flat_no" => $order_details->flat_no . "",
                "landmark" => $order_details->landmark . "",
                "order_type" => $order_details->order_type,
                "schedule_order_date_time" => $order_details->schedule_order_date_time != Null ? $order_details->schedule_order_date_time : '',
                "schedule_order_date" => $order_details->schedule_order_date_time != Null ? $this->notificationClass->dateLangConvert(Date('D d M, Y', strtotime($order_details->schedule_order_date_time)),$store_check->language) : '',
                "schedule_order_time" => $order_details->schedule_order_date_time != Null ? Date('h:i A', strtotime($order_details->schedule_order_date_time)) : '',
                "order_date_time" => $order_details->order_date_time != Null ? date('Y-m-d H:i:s',strtotime($order_details->order_date_time)) : '',
                "order_date" => $order_details->order_date_time != Null ? $this->notificationClass->dateLangConvert(Date('D d M, Y', strtotime($order_details->order_date_time)),$store_check->language) : '',
                "order_time" => $order_details->order_date_time != Null ? Date('h:i A', strtotime($order_details->order_date_time)) : '',
                "user_taken_type" => $order_details->taken_away_type - 0,
                //"driver_assign_type" => ($order_details->taken_away_type == 1 && $order_details->delivery_people_id == Null) ? 1 : 2,
                "order_status" => $order_details->status,
                "order_delivery_status" => $order_details->order_delivery_status,
                "order_shopper_status" => $order_details->order_shopper_status - 0,
                "shopper_rating_status" => $order_details->shopper_rating_status - 0,
                "order_item_total" => round($order_details->total_item_cost * $currency, 2),
                "order_discount_cost" => round($order_details->discount_cost * $currency, 2),
                "order_delivery_cost" => round(($order_details->delivery_cost) * $currency, 2),
                "order_packaging_cost" => round($order_details->packaging_cost * $currency, 2),
                "order_tax" => round($order_details->tax_cost * $currency, 2),
                "order_total_pay" => round($order_details->total_pay * $currency, 2),
                "promocode_discount" => $promocode_details != Null ? round($promocode_details->discount_amount * $currency, 2) : 0,
                "promocode_name" => $promocode_details != Null ? $promocode_details->promocode_name : '',
                "payment_type" => $order_details->payment_type,
                "delivery_person_name" => $order_details->delivery_people_name != Null ? $order_details->delivery_people_name : '',
                "delivery_person_image" => $delivery_person_image,
                "delivery_person_contact_no" => $delivery_person_contact_no,
                "delivery_person_rating" => $delivery_person_rating,
                "driver_selected_service_string" => $service,
                "driver_fcm_token" => $delivery_person_device_token,
                "driver_id" => $driver_id,
                "store_pay_settle_status"=>$order_details->store_pay_settle_status,
                "item_list" => $order_product_list
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "order details not found!",
                "message" => __('store_messages.59'),
                "message_code" => 59,
            ]);
        }
    }

    public function postStoreOrderHistory(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =1, $request->get('store_id'), "StoreAppPostStoreOrderHistory", $request->all());

        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer",
            "filter_type" => "nullable|in:0,1,2,3,4,5",
            "timezone" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }

        $store_currency = WorldCurrency::query()->where('symbol', $store_check->currency)->first();
        if ($store_currency == Null) {
            $store_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $store_currency->ratio;

        date_default_timezone_set($request->get('timezone'));

        $store_details = StoreDetails::query()->where('provider_service_id', $request->get('store_service_id'))->first();
        if ($store_details != Null) {
            $filter_type = $request->get('filter_type');
            $date = date('Y-m-d');
            if ($filter_type == 1) {
                //today
                $start_date = $date . " 00:00:01";
                $end_date = $date . " 23:59:59";
            } elseif ($filter_type == 2) {
                //last 7 day
                $start_date = date('Y-m-d', strtotime('-7 days', strtotime($date)));
                // $start_date = date('Y-m-d', strtotime($date . ' - 7 days'));
                $end_date = $date;
                $start_date = $start_date . " 00:00:01";
                $end_date = $end_date . " 23:59:59";
            } elseif ($filter_type == 4) {
                //this year
                $start_date = date("Y-01-01", strtotime($date));
                $end_date = date("Y-m-d", strtotime($date));
                $start_date = $start_date . " 00:00:01";
                $end_date = $end_date . " 23:59:59";
            } elseif ($filter_type == 5) {
                $start_date = date('Y-m-d', strtotime('+1 days', strtotime($date)));
                $start_date = $start_date . " 00:00:01";
                //not used
                $get_end_date = date('Y-m-d', strtotime('+365 days', strtotime($date)));
                $end_date = $get_end_date . " 23:59:59";
            } else {
                //last 30 day
                $start_date = date('Y-m-d', strtotime('-30 days', strtotime($date)));
                $end_date = $date;
                $start_date = $start_date . " 00:00:01";
                $end_date = $end_date . " 23:59:59";
                //$current_month = date('m');
                //$current_year = date('Y');
                //$last_month = $current_month - 1;
                //$start_date = date("Y-m-d", strtotime($current_year . '-' . $last_month . '01'));
                //$end_date = date("Y-m-d", strtotime($start_date));
            }
            $per_page = 10;
            if ($request->get('per_page') != Null) {
                $per_page = $request->get('per_page');
            }

            if ($filter_type == 1 || $filter_type == 2 || $filter_type == 3 || $filter_type == 4 || $filter_type == 5) {
                $order_history = UserProductBooking::query()->select('id as order_id',
                    'order_no', 'user_name as customer_name', 'created_at as order_date_time','order_date_time as schedule_order_date_time',
                    //'store_amount as total_amount',
                    DB::raw('ROUND(store_amount * ' . $currency . ',2) As total_amount'),
                    'status as order_status','store_pay_settle_status', 'order_product_list')->where('store_id', $store_details->id)->orderBy('id', 'desc')
                    ->where('status', "!=", 11)
                    ->where('order_date_time', '>=', $start_date);
                if($filter_type != 5) {
                    $order_history = $order_history->where('order_date_time', '<=', $end_date);
                }
            } else {
                $order_history = UserProductBooking::query()->select('id as order_id',
                    'order_no', 'user_name as customer_name', 'created_at as order_date_time','order_date_time as schedule_order_date_time',
                    //'store_amount as total_amount',
                    DB::raw('ROUND(store_amount * ' . $currency . ',2) As total_amount'),
                    'status as order_status','store_pay_settle_status', 'order_product_list')->where('store_id', $store_details->id)->orderBy('id', 'desc')
                    ->where('status', "!=", 11);
            }

            $order_history_get = $order_history->get()->toArray();

            $order_history_pagination = $order_history->paginate($per_page);
            $current_page = $order_history_pagination->currentPage();
            $last_page = $order_history_pagination->lastPage();
            $total = $order_history_pagination->total();

            $complete_orders = count(array_filter($order_history_get, function ($var) {
                    return ($var['order_status'] == 9);
                })
            );
            $cancelled_orders = count(array_filter($order_history_get, function ($var) {
                    return ($var['order_status'] == 3 || $var['order_status'] == 4 || $var['order_status'] == 10);
                })
            );
            $complete_orders_count = array_filter($order_history_get, function ($var) {
                return ($var['order_status'] == 9);
            });
            //$total_revenues = round(array_sum(array_column($complete_orders_count, 'total_amount')), 2);
            $total_store_earning = array_filter($order_history_get, function ($var) {
                return ($var['order_status'] == 9 && $var['store_pay_settle_status'] == 0);
            });
            $total_revenues = round(array_sum(array_column($total_store_earning, 'total_amount')), 2);

            $pending_orders = count($order_history_get) - ($complete_orders + $cancelled_orders);

            $history_data = [];
            foreach ($order_history_pagination as $history){
                $history_data[] = [
                    'order_id' => $history->order_id,
                    'order_no' => $history->order_no,
                    'customer_name' => $history->customer_name,
                    'order_date_time' => date('Y-m-d H:i:s'.strtotime($history->order_date_time)),
                    'schedule_order_date_time' => $history->schedule_order_date_time,
                    'total_amount' => $history->total_amount,
                    'order_status' => $history->order_status,
                    'store_pay_settle_status' => $history->store_pay_settle_status,
                    'order_product_list' => $history->order_product_list,
                ];
            }
            return response()->json([
                "status" => 1,
//                "message" => "success",
                "message" => __('store_messages.1'),
                "message_code" => 1,
                'current_page' => $current_page - 0,
                'last_page' => $last_page - 0,
                'total' => $total - 0,
                "pending_payments" => round($total_revenues * $currency, 2),
                "pending_order" => $pending_orders,
                "completed_order" => $complete_orders,
                "canceled order" => $cancelled_orders,
                "order_history" => $history_data
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "store details not found!",
                "message" => __('store_messages.161'),
                "message_code" => 71,
            ]);
        }
    }

    public function postStoreHome(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer",
            "app_version" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }

        $store_check = Provider::query()->where('id', '=', $request->get("store_id"))->first();
        if ($store_check == Null){
            return response()->json([
                "status" => 5,
//                "message" => 'Store not found!',
                "message" => __('store_messages.5'),
                "message_code" => 5,
            ]);
        }
        $store_check->app_version = $request->get("app_version");
        $store_check->save();

        $store_currency = WorldCurrency::query()->where('symbol', $store_check->currency)->first();
        if ($store_currency == Null) {
            $store_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $store_currency->ratio;
        $store_details = StoreDetails::query()->where('provider_service_id', $request->get('store_service_id'))->first();
        if ($store_details != Null) {
            return $this->storeClassApi->getOrderDispatcher($store_details->id,$currency,$store_check->language);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "store details not found!",
                "message" => __('store_messages.161'),
                "message_code" => 71,
            ]);
        }
    }

    public function postStoreUpdateOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
            "store_service_id" => "required|integer",
            "order_id" => "required|integer",
            "update_status" => "required|integer|in:2,3,5,8,9",
            "shopper_type" => "nullable|in:0,1"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }

        $store_currency = WorldCurrency::query()->where('symbol', $store_check->currency)->first();
        if ($store_currency == Null) {
            $store_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $store_currency->ratio;
        $order = UserProductBooking::query()->where('id', $request->get('order_id'))->first();
        if ($order != Null) {
            if ($order->status == 4) {
                return response()->json([
                    "status" => 0,
//                    "message" => "order cancelled by user!",
                    "message" => __('store_messages.36'),
                    "message_code" => 75,
                    "order_status" => $order->status - 0,
                ]);
            }
            if ($order->status == 9) {
                return response()->json([
                    "status" => 0,
//                    "message" => "order completed!",
                    "message" => __('store_messages.56'),
                    "message_code" => 151,
                    "order_status" => $order->status - 0,
                ]);
            }
            if ($order->status == 3) {
                return response()->json([
                    "status" => 0,
//                    "message" => "order rejected by store!",
                    "message" => __('store_messages.52'),
                    "message_code" => 52,
                ]);
            }
            if ($order->status == 10) {
                return response()->json([
                    "status" => 0,
//                    "message" => "order is failed!",
                    "message" => __('store_messages.163'),
                    "message_code" => 9,
                ]);
            }
            if ($request->get('update_status') == 2) {
                if ($order->status == 1) {
                    $order->status = $request->get('update_status');
                    $order->save();
                    //$this->notificationClass->driverRequestNotification($order->service_cat_id, $order->id);
                    return $this->storeClassApi->addOrderStatus($order->id, $request->get('update_status'), $currency);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('store_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            }
            elseif ($request->get('update_status') == 3) {
                $validator = Validator::make($request->all(), [
                    "rejected_reason" => "required"
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        "status" => 0,
                        "message" => $validator->errors()->first(),
                        "message_code" => 9,
                    ]);
                }
                if ($order->status < 5) {
                    $order_status = $order->status;
                    $order->otp = null;
                    $order->status = $request->get('update_status');
                    $order->cancel_by = "store";
                    $order->cancel_reason = $request->get('rejected_reason');
                    $order->save();

                    if ($order_status < 5){
                        if ($order->promo_code > 0){
                            $used_promocode_details = UsedPromocodeDetails::query()->where('id', $order->promo_code)->first();
                            if ($used_promocode_details != Null) {
                                $get_promocode = PromocodeDetails::query()->where('id', $used_promocode_details->promocode_id)->first();
                                if ($get_promocode != Null) {
                                    $count_promocode = $get_promocode->total_usage - 1;
                                    $get_promocode->total_usage = ($count_promocode > 0)? $count_promocode : 0;
                                    $get_promocode->save();
                                }
                                $used_promocode_details->status = 2;
                                $used_promocode_details->save();
                            }
                        }
                    }

                    if ($order->payment_type == 2 || $order->payment_type == 3) {
                        //$service_settings = ServiceSettings::where('service_cat_id', $order->service_cat_id)->first();
                        //if ($service_settings != Null) {
                        //$cancel_charge = $service_settings->cancel_charge != Null ? $service_settings->cancel_charge : 0;
                        //$count_cancel_charge = round((($order->total_item_cost * $cancel_charge) / 100), 2);
                        //$charges = round($order->total_pay - $count_cancel_charge, 2);
                        $order->cancel_charge = 0;
                        $order->refund_amount = $order->total_pay;
                        $order->save();
                        //}
                    }

                    $general_settings = request()->get("general_settings");
                    if ($general_settings != Null){
                        if ($general_settings->send_mail == 1) {
                            $service_category = ServiceCategory::query()->where('id', $order->service_cat_id)->first();
                            if ($service_category != Null) {
                                $store_details = StoreDetails::query()->where('id', $order->store_id)->first();
                                if ($store_details != Null) {
                                    $user_details = User::query()->where('id', $order->user_id)->first();
                                    if ($user_details != Null) {
                                        $service_name = ucwords(strtolower($service_category->name));
                                        $store_name = $store_details->name;
                                        $cancel_by = ($store_name != "") ? $store_name : "";
                                        $user_name = ($order->user_name != "") ? $order->user_name : "";
                                        try {
                                            $mail_type = "order_reject/cancel_-_delivery";
                                            $to_mail = $user_details->email;
                                            $subject = "Your " . $general_settings->mail_site_name . " " . $service_name . " delivery order cancelled by " . $store_name;
                                            $disp_data = array("##user_name##" => $user_name, "##service_name##" => $service_name, "##cancel_by##" => $cancel_by);
                                            $mail_return_data = $this->notificationClass->sendMail($subject, $to_mail, $mail_type, $disp_data);
                                        } catch (\Exception $e) {}
                                    }
                                }
                            }
                        }
                    }
                    return $this->storeClassApi->addOrderStatus($order->id, $request->get('update_status'), $currency);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('store_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            }
            elseif ($request->get('update_status') == 5) {
                if ($order->status < 8) {
                    if ($order->order_type == 1 || $order->order_date_time != Null) {
                        $pickup_date_time = strtotime(date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($order->order_date_time))));
                        $current_date_time = strtotime(date('Y-m-d H:i:s'));
                        if ($current_date_time >= $pickup_date_time) {
                        } else {
                            return response()->json([
                                "status" => 0,
//                                "message" => "You can start the service before 60 minutes of requested time.",
                                "message" =>  __('store_messages.118'),
                                "message_code" => 118,
                            ]);
                        }
                    }

                    if ($order->taken_away_type == 2) {
                        $user = User::query()->where('id', $order->user_id)->first();
                        if ($user == Null) {
                            return response()->json([
                                "status" => 0,
//                                "message" => "something went to wrong!",
                                "message" => __('store_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
                        //$this->notificationClass->userStoreDeliveryNotification($order->id, $user->device_token, $request->get('update_status'), $user->login_device);
                    }
                    $order->status = $request->get('update_status');
                    $order->save();

                    if ($order->taken_away_type != 2) {
                        $this->notificationClass->driverRequestNotification($order->service_cat_id, $order->id);
                    }


                    return $this->storeClassApi->addOrderStatus($order->id, $request->get('update_status'), $currency);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "order is processing!",
                        "message" => __('store_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            }
            elseif ($request->get('update_status') >= 5 && $request->get('update_status') <= 8) {
                if ($order->status < 8) {
                    if ($order->taken_away_type == 2) {
                        $user = User::query()->where('id', $order->user_id)->first();
                        if ($user == Null) {
                            return response()->json([
                                "status" => 0,
//                                "message" => "something went to wrong!",
                                "message" => __('store_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
                        $order->status = $request->get('update_status');
                        $order->save();
                        $this->notificationClass->userStoreDeliveryNotification($order->id, $user->device_token, $request->get('update_status'), $user->login_device, $user->language);
                    } else {
                        if ($order->delivery_people_id != Null) {
//                        $order->status = $request->get('update_status');
//                        $order->save();
//                        return $this->storeClassApi->addOrderStatus($order->id, $request->get('update_status'), $currency);
                            if ($order->delivery_people_id != Null) {
                                $order->status = $request->get('update_status');
                                $order->save();

                                return $this->storeClassApi->addOrderStatus($order->id, $request->get('update_status'), $currency);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "You can dispatch the order after delivery person accept the order2.",
                                    "message" => __('store_messages.119'),
                                    "message_code" => 119,
                                ]);
                            }
                        } else {
                            return response()->json([
                                "status" => 0,
                                "message" => __('store_messages.119'),
                                "message_code" => 119,
                            ]);
                        }
                    }
                    return $this->storeClassApi->addOrderStatus($order->id, $request->get('update_status'), $currency);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" =>  __('store_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            }
            elseif ($request->get('update_status') == 9 && $order->taken_away_type == 2) {
                if ($order->status >= 5 && $order->status <= 8) {
//                        if ($request->get('update_status') == 8 && $order->order_delivery_status == 0) {
//                            return response()->json([
//                                "status" => 0,
//                                "message" => "The order is still not assigned to any delivery person, First, assign the order to a delivery person!",
//                                "message_code" => 84,
//                            ]);
//                        }
                    if ($order->taken_away_type == 2) {
                        $user = User::query()->where('id', $order->user_id)->first();
                        if ($user == Null) {
                            return response()->json([
                                "status" => 0,
//                                "message" => "something went to wrong!",
                                "message" =>  __('store_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
                        $this->notificationClass->userStoreDeliveryNotification($order->id, $user->device_token, $request->get('update_status'), $user->login_device, $user->language);
                    }
                    $order->status = $request->get('update_status');
                    $order->save();

                    return $this->storeClassApi->addOrderStatus($order->id, $request->get('update_status'), $currency);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" =>  __('store_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            }
            else {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" =>  __('store_messages.9'),
                    "message_code" => 9,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "order not found!",
                "message" =>  __('store_messages.59'),
                "message_code" => 59,
            ]);
        }
    }

    public function postStoreShopperOrderRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "store_id" => "required|integer",
                "access_token" => "required|numeric",
                "store_service_id" => "required|integer",
                "order_id" => "required|integer",
                "rating" => "required",
                "comment" => "nullable",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }

        $store_currency = WorldCurrency::query()->where('symbol', $store_check->currency)->first();
        if ($store_currency == Null) {
            $store_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $store_currency->ratio;

        //$order_details = UserProductBooking::query()->select('shopper_rating_status', 'order_shopper_status', 'shopper_id', 'shopper_type', 'id', 'user_id', 'order_no', 'delivery_address', 'delivery_people_id', 'created_at as order_date_time', 'order_date_time as schedule_order_date_time', 'order_type', 'status', 'order_delivery_status', 'total_pay', 'payment_type', 'delivery_people_name', 'total_item_cost', 'discount_cost', 'tax_cost', 'delivery_cost', 'packaging_cost', 'cancel_by', 'cancel_reason', 'prescription', 'promo_code')->where('id', $request->get('order_id'))->first();
        $order_details = UserProductBooking::where('id', $request->get('order_id'))->first();
        if ($order_details != Null) {
            $order_details->shopper_rating_status = 1;
            $order_details->save();

            $check_duplicate = ShopperRating::where('store_id', $order_details->store_id)->where('booking_id', $order_details->id)->first();
            if ($check_duplicate == Null) {
                $store_rating = new ShopperRating();
                $store_rating->store_id = $order_details->store_id;
                $store_rating->shopper_id = $order_details->shopper_id;
                $store_rating->booking_id = $order_details->id;
                $store_rating->rating = round($request->get('rating'), 1);
                if ($request->get('comment') != Null) {
                    $store_rating->comment = $request->get('comment');
                }
                $store_rating->status = 1;
                $store_rating->save();
                $store_details = ShopperProviderDetails::where('id', $order_details->shopper_id)->first();
                if ($store_details != Null) {
                    $ratings = ShopperRating::query()
//                            ->select(DB::raw('avg(rating) as ratings'))
                        ->groupBy('shopper_id')
                        ->where('shopper_id', $order_details->shopper_id)
                        ->avg('rating');
                    $store_details->rating = round($ratings, 1);
                    $store_details->save();
                }
            }
            return response()->json([
                'status' => 1,
//                'message' => "success!",
                'message' =>   __('store_messages.1'),
                'message_code' => 1,
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "order details not found!",
                "message" =>   __('store_messages.59'),
                "message_code" => 59,
            ]);
        }
    }

}
