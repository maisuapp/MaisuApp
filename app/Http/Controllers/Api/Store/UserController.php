<?php

namespace App\Http\Controllers\api\Store;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Classes\UserClassApi;
use App\model\GeneralSettings;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\RestrictedArea;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\ServiceSliderBanner;
use App\model\StoreDetails;
use App\model\StoreProductCategory;
use App\model\StoreProductDetails;
use App\model\StoreProductOptions;
use App\model\StoreProductSize;
use App\model\StoreProductToppings;
use App\model\StoreRatings;
use App\model\StoreTimings;
use App\model\SurchargeTimings;
use App\model\TempUserBooking;
use App\model\TransportDriverDetails;
use App\model\TransportRatings;
use App\model\TransportRideDriverToken;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserAddress;
use App\model\UserCardDetails;
use App\model\UserFavouriteStore;
use App\model\UserProductBooking;
use App\model\UserProductBookingOptions;
use App\model\UserProductBookingQuantity;
use App\model\UserProductBookingSize;
use App\model\UserProductBookingStatus;
use App\model\UserProductBookingToppings;
use App\model\UserReferHistory;
use App\model\UserWalletTransaction;
use App\model\WorldCurrency;
use Braintree_Configuration;
use Braintree_Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Luigel\Paymongo\Facades\Paymongo;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class UserController extends Controller
{
//        json response status [
//            0 => false,
//            1 => true,
//            2 => registration pending,
//            3 => app user blocked,
//            4 => app user access token not match,
//            5 => app user not found
//          ]

    //ApiLogDetail logger type => 0:user,1:store,2:driver,3:provider
    private $userClassapi;
    private $notificationClass;
    private $medicineDelivery;
    private $adminClassApi;
    private $fav_store_display_limit = 10;
    private $user_type = 0;

    public function __construct( AdminClass $adminClassApi , UserClassApi $userClassapi, NotificationClass $notificationClass)
    {
        $this->userClassapi = $userClassapi;
        $this->adminClassApi = $adminClassApi;
        $this->notificationClass = $notificationClass;
        $this->medicineDelivery = 10;
    }

    public function postStoreList(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postStoreList", $request->all());

        $validator = Validator::make($request->all(), [
            "user_id" => "nullable|numeric",
            "access_token" => "nullable|numeric",
            "lat" => "required",
            "long" => "required",
            "service_category_id" => "required|numeric",
            //"filter_type" => "required|in:all,filter",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_id = $request->get("user_id");
        $access_token = $request->get("access_token");
        $address_lat = $request->get("lat");
        $address_long = $request->get("long");
        $service_category_id = $request->get("service_category_id");
        $filter_key = $request->get("filter_key");

        if ($user_id != Null && $access_token != Null){
            $user_details = $this->userClassapi->checkUserAllow($user_id, $access_token);
            if ($decoded['status'] = json_decode($user_details) == false) {
                return $user_details;
            }
            $language = $user_details->language;
            $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        } else {
            $user_currency = Null;
            $language = "en";
        }
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = (isset($user_currency->ratio) ? $user_currency->ratio : 1);
        $currency_symbol = (isset($user_currency->symbol) ? $user_currency->symbol : "$");

        if ($address_lat == Null || $address_long == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "User address not found!",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }

        //open_now, offer, order_delivery, order_takeaway, cost_high_to_low, cost_low_to_high, near_by, delivery_time, rating,1, 2, 4, 5, 7
        if ($filter_key != Null){
            //single (radio type) filter
            $is_single_filter = 0;
            $cost_high_to_low = 0;
            $cost_low_to_high = 0;
            $near_by = 0;
            $delivery_time = 0;
            $rating = 0;
            //multi (check type) filter
            $is_multi_filter = 0;
            $open_now = 0;
            $offer = 0;
            $order_takeaway = 0;
            $is_product_filter = 0;

            $selected_filter_key = explode(',', $filter_key);
            $product_cat_id_for_filter = [];

            foreach ($selected_filter_key as $filter_key_return) {
                if (is_numeric($filter_key_return)) {
                    $product_cat_id_for_filter[] = $filter_key_return - 0;
                    $is_multi_filter = 1;
                    $is_product_filter = 1;
                } else {
                    if ($filter_key_return == "cost_high_to_low") {
                        $cost_high_to_low = 1;
                        $is_single_filter = 1;
                    } elseif ($filter_key_return == "cost_low_to_high") {
                        $cost_low_to_high = 1;
                        $is_single_filter = 1;
                    } elseif ($filter_key_return == "near_by") {
                        $near_by = 1;
                        $is_single_filter = 1;
                    } elseif ($filter_key_return == "delivery_time") {
                        $delivery_time = 1;
                        $is_single_filter = 1;
                    } elseif ($filter_key_return == "rating") {
                        $rating = 1;
                        $is_single_filter = 1;
                    }
                    if ($filter_key_return == "open_now"){
                        $open_now = 1;
                        $is_multi_filter = 1;
                    }
                    if ($filter_key_return == "offer"){
                        $offer = 1;
                        $is_multi_filter = 1;
                    }
                    if ($filter_key_return == "order_takeaway"){
                        $order_takeaway = 1;
                        $is_multi_filter = 1;
                    }
                }
            }
        }
        $time = Date('H:i:s');
        $get_store_list = StoreDetails::query()->select('store_details.id',
            'store_details.name', 'store_details.image',
            'store_details.offer_min_amount', 'store_details.offer_amount',
            'store_details.offer_type', 'store_details.order_min_amount',
            'store_details.rating', 'store_details.eta_delivery_time',
            'provider_services.current_status'
            , 'providers.service_radius',
            'store_timings.store_open_time',
            'store_timings.store_close_time',
            'store_timings.day'
            ,
            DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
            +COS( ' . $address_lat . ' * pi()/180)
            * COS(store_details.address_lat * pi()/180)
            * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
        )
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->leftJoin('store_timings', 'store_timings.store_id', '=', 'store_details.id')
            ->where('store_timings.day', substr(date('l'), 0, 3))
            ->where('provider_services.status', 1)
            ->where('provider_services.service_cat_id', $service_category_id);
        if ($filter_key != Null) {
            if ($is_multi_filter == 1) {
                if ($order_takeaway == 1) {
                    $get_store_list->where('store_details.taken_away_type', 2);
                }
                if ($offer == 1) {
                    $get_store_list->where('store_details.offer_amount', '>', 0);
                    $get_store_list->where('store_details.offer_min_amount', '>', 0);
                }
                if ($open_now == 1) {
                    $get_store_list->where('provider_services.current_status', 1)
                        ->where('store_timings.store_open_time', "<=", $time)
                        ->where('store_timings.store_close_time', ">=", $time);
                }
                if ($is_product_filter == 1){
                    $get_store_list->join("store_product_details", "store_product_details.store_id", "=", "store_details.id")
                        ->where(function ($query) use ($product_cat_id_for_filter) {
                            foreach ($product_cat_id_for_filter as $key => $product_cat_id) {
                                $query->orWhere('store_product_details.product_cate_id', '=', $product_cat_id)->where('store_product_details.status', 1);
                            }
                        })
                        ->groupBy("store_product_details.store_id");
                }
            }
            if ($is_single_filter == 1) {
                if ($cost_high_to_low == 1) {
                    $get_store_list->orderBy('store_details.order_min_amount', 'desc');
                } elseif ($cost_low_to_high == 1) {
                    $get_store_list->orderBy('store_details.order_min_amount', 'asc');
                } elseif ($near_by == 1) {
                    $get_store_list->orderByRaw("distance");
                } elseif ($delivery_time == 1) {
                    $get_store_list->orderBy('store_details.eta_delivery_time', 'asc');
                } elseif ($rating == 1) {
                    $get_store_list->orderBy('store_details.rating', 'desc');
                }
            }
        }
        $this->userClassapi->checkDayShowRecord($get_store_list,0);
        $get_store_list = $get_store_list->get();
        //->havingRaw('providers.service_radius > distance')
        $store_list = [];
        $count_store = 0;
        if (!$get_store_list->isEmpty()) {
            if ($language !="en" && $language != "" && $language != "Null" ) {
                $language = $language."_";
            } else {
                $language ="";
            }
            $res_store_list = $this->userClassapi->storeList($get_store_list, $language, $currency, $currency_symbol);
            $getData = $res_store_list->getData();
            $count_store = $getData->count_store;
            $store_list = $getData->store_list;
        }

        //code for get service wise slide data
        $store_banner_path = url('/assets/images/service-slider-banner/');
        $store_details = ServiceSliderBanner::query()->select('service_slider_banner.id','service_slider_banner.store_id','service_slider_banner.service_cat_id',
            'store_details.name as store_name','store_details.rating as average_ratings','store_details.eta_delivery_time',
            'store_details.offer_min_amount', 'store_details.offer_amount',
            'store_details.offer_type', 'store_details.order_min_amount',
            DB::raw("(CASE WHEN service_slider_banner.banner_image != '' THEN (concat('$store_banner_path','/',service_slider_banner.banner_image)) ELSE '' END) as store_image"),
            DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                +COS( ' . $address_lat . ' * pi()/180)
                * COS(store_details.address_lat * pi()/180)
                * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance'),'providers.service_radius')
            ->join('store_details','store_details.id','=','service_slider_banner.store_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('service_slider_banner.status',1)
            ->where('providers.status',1)
            ->where('provider_services.status',1)
            ->where('service_slider_banner.service_cat_id','=',$service_category_id);
        $this->userClassapi->checkDayShowRecord($store_details, 0);
            //->havingRaw('providers.service_radius > distance')
        $store_details = $store_details->orderByRaw("distance DESC")
            ->get();
        $store_slider_data = [];
        foreach ($store_details as $keys => $store_detail) {
            $store_slider_data[$keys] = [
                "id" => $store_detail->id,
                "store_id" => $store_detail->store_id,
                "service_category_id" => $store_detail->service_cat_id,
                "store_name" => $store_detail->store_name,
                "banner_image" => ($store_detail->store_image != "") ? $store_detail->store_image : "",
            ];
        }

        return response()->json([
            'status' => 1,
//            'message' => 'Success!',
            'message' => __('user_messages.1'),
            "message_code" => 1,
            'no_of_store' => $count_store,
            'store_list' => $store_list,
            'service_slider_data' => $store_slider_data
        ]);
        //return $this->userClassapi->filterStoreList($lat_long, $filter_type, $filter_key, $service_category_id, $currency, $currency_symbol, $language);
    }

    public function postSearchStore(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postSearchStoreProduct", $request->all());

        $validator = Validator::make($request->all(), [
            "user_id" => "nullable|numeric",
            "access_token" => "nullable|numeric",
            "service_category_id" => "required|numeric",
            "search" => "required_without:category_id",
            "lat" => "required",
            "long" => "required",
            "category_id" => "required_without:search",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_id = $request->get("user_id");
        $access_token = $request->get("access_token");
        $address_lat = $request->get("lat");
        $address_long = $request->get("long");
        $service_category_id = $request->get("service_category_id");
        $search = $request->get("search");
        $product_category_id = $request->get("category_id");

        if ($user_id != Null && $access_token != Null) {
            $user_details = $this->userClassapi->checkUserAllow($user_id, $access_token);
            if ($decoded['status'] = json_decode($user_details) == false) {
                return $user_details;
            }
            $language = $user_details->language;
            $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        } else {
            $user_currency = Null;
            $language = "en";
        }
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = (isset($user_currency->ratio) ? $user_currency->ratio : 1);
        $currency_symbol = (isset($user_currency->symbol) ? $user_currency->symbol : "$");

        if ($address_lat == Null || $address_long == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "User address not found!",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }

        $get_store_list = StoreDetails::query()->select('store_details.id',
            'store_details.name', 'store_details.image',
            'store_details.offer_min_amount', 'store_details.offer_amount',
            'store_details.offer_type', 'store_details.order_min_amount',
            'store_details.rating', 'store_details.eta_delivery_time',
            'provider_services.current_status'
            , 'providers.service_radius',
            'store_timings.store_open_time',
            'store_timings.store_close_time',
            'store_timings.day'
            , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
            +COS( ' . $address_lat . ' * pi()/180)
            * COS(store_details.address_lat * pi()/180)
            * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
        )
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->leftJoin('store_timings', 'store_timings.store_id', '=', 'store_details.id')
            ->where('store_timings.day', substr(date('l'), 0, 3))
            ->where('provider_services.status', 1)
            ->where('provider_services.service_cat_id', $service_category_id);
        if ($product_category_id != Null) {
            $get_store_list->join('store_product_details', 'store_product_details.store_id', '=', 'store_details.id')
                ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                ->where('store_product_details.status', 1)
                ->where('store_product_category.status', 1)
                ->where('store_product_category.id', $product_category_id)
                ->groupBy('store_product_details.store_id');
        } else {
            $get_store_list->where('store_details.name', 'LIKE', "%{$search}%");
        }
        $this->userClassapi->checkDayShowRecord($get_store_list, 0);
        $get_store_list =
//            $get_store_list->havingRaw('providers.service_radius > distance')
            $get_store_list->orderByRaw("distance")
            ->get();
        $store_list = [];
        $count_store = 0;
        if (!$get_store_list->isEmpty()) {
            if ($language !="en" && $language != "" && $language != "Null" ) {
                $language = $language."_";
            } else {
                $language ="";
            }
            $res_store_list = $this->userClassapi->storeList($get_store_list, $language, $currency, $currency_symbol);
            $getData = $res_store_list->getData();
            $count_store = $getData->count_store;
            $store_list = $getData->store_list;
        }

        return response()->json([
            'status' => 1,
//            'message' => 'Success!',
            'message' => __('user_messages.1'),
            "message_code" => 1,
            'no_of_store' => $count_store,
            'store_list' => $store_list,
        ]);
    }

    public function postSearchAllProduct(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postSearchStoreProduct", $request->all());

        $validator = Validator::make($request->all(), [
            "user_id" => "nullable|numeric",
            "access_token" => "nullable|numeric",
            "service_category_id" => "required|numeric",
            "search" => "required",
            "lat" => "required",
            "long" => "required",
            "page" => "nullable|numeric",
            "per_page" => "nullable|numeric",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_id = $request->get("user_id");
        $access_token = $request->get("access_token");
        $address_lat = $request->get("lat");
        $address_long = $request->get("long");
        $service_category_id = $request->get("service_category_id");
        $search = $request->get("search");
        $per_page = 10;
        if ($request->get('per_page') != Null) {
            $per_page = $request->get('per_page');
        }

        if ($user_id != Null && $access_token != Null) {
            $user_details = $this->userClassapi->checkUserAllow($user_id, $access_token);
            if ($decoded['status'] = json_decode($user_details) == false) {
                return $user_details;
            }
            $language = $user_details->language;
            $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        } else {
            $user_currency = Null;
            $language = "en";
        }
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = (isset($user_currency->ratio) ? $user_currency->ratio : 1);
        $currency_symbol = (isset($user_currency->symbol) ? $user_currency->symbol : "$");

        if ($address_lat == Null || $address_long == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "User address not found!",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        $product_url = url('/assets/images/store-images/product-images/');
        $get_products_list = StoreProductDetails::query()->select(
            'store_product_details.id as product_id',
            'store_product_details.name as product_name',
            DB::raw("(CASE WHEN store_product_details.image != '' THEN (concat('$product_url','/',store_product_details.image)) ELSE '' END) as product_image"),
            'store_product_details.food_type',
            'store_product_details.amount',
            'store_product_details.discount_amount',
            'store_product_details.discount_type',
            'store_details.name as store_name',
            'store_details.id as store_id',
            'providers.service_radius',
            DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
            +COS( ' . $address_lat . ' * pi()/180)
            * COS(store_details.address_lat * pi()/180)
            * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
        )
            ->join('store_product_category', 'store_product_category.id', 'store_product_details.product_cate_id')
            ->join('store_details','store_details.id','=', 'store_product_details.store_id')
            ->join('provider_services','provider_services.id','=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('provider_services.status', 1)
            ->where('store_product_details.status', 1)
            ->where('store_product_category.status', 1)
            ->where('provider_services.service_cat_id', $service_category_id);
        $this->userClassapi->checkDayShowRecord($get_products_list,0);
            //->havingRaw('providers.service_radius > distance')
        $get_products_list = $get_products_list->where('store_product_details.name', 'LIKE', "%{$search}%")
            //->orderBy('store_product_category.id')
            ->paginate($per_page);
        $current_page = $get_products_list->currentPage();
        $last_page = $get_products_list->lastPage();
        $total = $get_products_list->total();
        $product_data = [];
        foreach ($get_products_list as $key => $product) {
            $product_size = StoreProductSize::query()->select('id', 'name', 'amount')
                ->where('product_id', $product->product_id)
                ->orderBy('amount', 'asc')->first();
            if ($product_size != Null){
                $product_amount = $product_size->amount;
            } else {
                $product_amount = $product->amount;
            }
            if ($product->discount_type == 1) {
                $price_discount = round((($product_amount) - ($product->discount_amount)), 2);
            } elseif ($product->discount_type == 2) {
                $price_discount = round((($product_amount) - (($product_amount) * ($product->discount_amount)) / (100)), 2);
            } else {
                $price_discount = 0;
            }
            if ($price_discount != 0) {
                $offer_calculation = round(($product_amount - $price_discount), 2);
                $product_price = $price_discount;
                $price_discount = $product_amount;
            } else {
                $product_price = $product_amount;
                $offer_calculation = 0;
            }

            $product_data[] = [
                "product_id" => $product->product_id,
                "product_image" => $product->product_image.'',
                "product_name" => $product->product_name,
                "food_type" => $product->food_type,
                "product_amount" => round($product_price * $currency, 2),
                "discount_amount" => round($price_discount * $currency, 2),
                "offer_discount" => round($offer_calculation * $currency, 2),
                "store_id" => $product->store_id ? $product->store_id : '',
                "store_name" => $product->store_name ? $product->store_name : '',
            ];
        }
        return response()->json([
            'status' => 1,
//            'message' => 'Success!',
            'message' => __('user_messages.1'),
            "message_code" => 1,
            'current_page' => $current_page - 0,
            'last_page' => $last_page - 0,
            'total' => $total - 0,
            'product_list' => $product_data
        ]);
    }

    public function postStoreProductCategoryList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric",
            "store_id" => "nullable|numeric",
            "category_id" => "nullable|numeric",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $service_category_id = $request->get("service_category_id");
        $category_id = $request->get("category_id");
        $store_id = $request->get("store_id");

        $is_sub_cat_flow = 0;
        $service_category = ServiceCategory::query()->where("id", "=", $service_category_id)->first();
        if ($service_category != Null){
            $is_sub_cat_flow = $service_category->is_sub_cat_flow-0;
        }
        if ($is_sub_cat_flow == 1){
            $validator = Validator::make($request->all(), [
                "category_id" => "required|numeric",
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
        }

        $category_image_path = url('/assets/images/store-images/category-images/');
        $product_category = StoreProductCategory::query()->select('store_product_category.id as product_category_id', 'store_product_category.name as product_category_name'
            ,
        DB::raw("(CASE WHEN store_product_category.category_icon != '' THEN (concat('$category_image_path','/',store_product_category.category_icon)) ELSE '' END) as category_icon")
        )
        ->join('store_product_details','store_product_details.product_cate_id','=','store_product_category.id')
        ->where('store_product_category.service_cat_id', $service_category_id);
        if ($is_sub_cat_flow == 1){
            $product_category->where('store_product_category.parent_id', '=', $category_id)
                ->where('store_product_details.store_id','=',$store_id);
        }
        $product_category = $product_category->where('store_product_category.parent_id','!=', 0)

            ->where('store_product_category.status', '=',1)
            ->where('store_product_details.status','=',1)
            ->groupBy('store_product_category.id')
            ->get();
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            "message" => __('user_messages.1'),
            "message_code" => 1,
            "product_category_list" => $product_category
        ]);
    }

    public function postStoreDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "store_id" => "required|numeric",
            "lat" => "required|numeric",
            "long" => "required|numeric",
            "filter_type" => "nullable|in:0,1,2",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_id = $request->get('user_id');
        $user_details = $this->userClassapi->checkUserAllow($user_id, $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;
        $currency_symbol = $user_currency->symbol;
        $language = $user_details->language;
        $user_lat = $request->get('lat');
        $user_long = $request->get('long');
        $filter_type = $request->get("filter_type");
        return $this->userClassapi->findStoreData($request->get('store_id'), $currency, $currency_symbol,$language, $filter_type,$user_lat,$user_long,$user_id);
    }

    public function postStoreProductList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "store_id" => "required|numeric",
            "category_id" => "required|numeric",
            "page" => "nullable|numeric",
            "per_page" => "nullable|numeric",
            "search" => "nullable",
            "filter_type" => "required|in:0,1,2",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;
        $language = $user_details->language;
        $category_id = $request->get('category_id');
        $store_details = StoreDetails::query()->select('store_details.id',
            'store_details.name',
            'store_details.image',
            'store_details.description',
            'store_details.eta_delivery_time',
            'store_details.order_min_amount',
            'store_details.offer_min_amount',
            'store_details.offer_amount',
            'store_details.offer_type',
            'store_details.address',
            'store_details.address_lat',
            'store_details.address_long',
            'store_details.packaging_charges',
            'store_details.rating as average_rating',
            'provider_services.current_status',
            'provider_services.service_cat_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->where('store_details.id', $request->get('store_id'))->where('provider_services.status', 1)->first();
        if ($store_details != Null) {
            $per_page = 10;
            if ($request->get('per_page') != Null) {
                $per_page = $request->get('per_page');
            }
            $search = $request->get('search');
            $filter_type = $request->get('filter_type');
            $get_products_list = StoreProductDetails::query()->select('store_product_details.id',
                'store_product_details.image',
                'store_product_details.name',
                'store_product_details.food_type',
                'store_product_details.amount',
                'store_product_details.discount_amount',
                'store_product_details.discount_type',
                'store_product_details.description',
                'store_product_category.name as category_name',
                'store_product_category.id as category_id')
                ->join('store_product_category', 'store_product_category.id', 'store_product_details.product_cate_id')
                ->where('store_product_details.store_id', $store_details->id)
                ->where('store_product_details.status', 1)
                ->where('store_product_category.status', 1);
            if ($category_id > 0) {
                if ($search != Null) {
                    $get_products_list->where('store_product_details.name', 'LIKE', "%{$search}%");
                }
                $get_products_list = $get_products_list->where('store_product_details.product_cate_id', $category_id);

            } else {
                if ($search != Null) {
                    $get_products_list->where('store_product_details.name', 'LIKE', "%{$search}%");
                } else {
                    $get_products_list = $get_products_list->where('store_product_details.is_recommended', '=', 1);
                }
            }
            //1=veg,2=non-veg, 0=not required
            if (in_array($filter_type, [1, 2])) {
                $get_products_list->where('store_product_details.food_type', $filter_type);
            }
            $get_products = $get_products_list->orderBy('store_product_category.id')
                ->paginate($per_page);

            $current_page = $get_products->currentPage();
            $last_page = $get_products->lastPage();
            $total = $get_products->total();
            //$items = $get_products->items();
            //->get();
            $product_data = [];
            foreach ($get_products as $key => $product) {
                $product_size_arr = [];
                if ($product->discount_type == 1) {
                    $price_discount = round((($product->amount) - ($product->discount_amount)), 2);
                } elseif ($product->discount_type == 2) {
                    $price_discount = round((($product->amount) - (($product->amount) * ($product->discount_amount)) / (100)), 2);
                } else {
                    $price_discount = 0;
                }
                if ($price_discount != 0) {
                    $offer_calculation = round(($product->amount - $price_discount), 2);
//                        $offer_calculation = $offer_calculation - 0;
                    $product_price = $price_discount;
                    $price_discount = $product->amount;
                } else {
                    $product_price = $product->amount;
                    $offer_calculation = 0;
                }

                $product_customize_arr = [];
                $product_size = StoreProductSize::query()->select('id', 'name', 'amount')
                    ->where('product_id', $product->id)->orderBy('amount', 'asc')->get();
                foreach ($product_size as $size_key => $size) {
                    if ($size_key == 0) {
                        $product_amount = $size->amount;
                    }
                    if ($product->discount_type == 1) {
                        $discount_price = round((($size->amount) - ($product->discount_amount)), 2);
                    } elseif ($product->discount_type == 2) {
                        $discount_price = round((($size->amount) - (($size->amount) * ($product->discount_amount)) / (100)), 2);
                    } else {
                        $discount_price = 0;
                    }
                    if ($discount_price != 0) {
                        $amount_price = $discount_price;
                        $discount_price = $size->amount;
                    } else {
                        $amount_price = $size->amount;
                    }
                    $product_size_arr[] = [
                        "id" => $size->id,
                        "name" => $size->name,
                        "amount" => $amount_price,
                        "discount_price" => $discount_price,
                        "category_name" => "Quantity",
                        "selection_type" => 1,
                    ];
                }

                if ($product_size_arr != Null) {
                    $product_customize_arr[] = [
                        "category_name" => "Quantity",
                        "selection_type" => 1,
                        "customize_type" => 1,
                        "options" => $product_size_arr
                    ];
                }
                $product_options = StoreProductOptions::query()->select('store_product_options.id',
                    'store_product_options.name',
                    'store_product_options.amount',
                    'store_product_customize_category.name as category_name',
                    'store_product_customize_category.selection_type')
                    ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_options.category_id')
                    ->where('store_product_options.product_id', $product->id)->get();
                $product_options_category = $product_options->pluck('selection_type', 'category_name')->toArray();
                $product_options = $product_options->groupBy('category_name')->toArray();
                foreach ($product_options as $option_key => $option) {
                    $product_customize_arr[] = [
                        'category_name' => $option_key,
                        'selection_type' => $product_options_category[$option_key],
                        "customize_type" => 2,
                        'options' => $option
                    ];
                }
                $product_toppings = StoreProductToppings::query()->select('store_product_toppings.id',
                    'store_product_toppings.name',
                    'store_product_toppings.amount',
                    'store_product_customize_category.name as category_name',
                    'store_product_customize_category.selection_type')
                    ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')
                    ->where('store_product_toppings.product_id', $product->id)->get();

                $product_toppings_category = $product_toppings->pluck('selection_type', 'category_name')->toArray();
                $product_toppings = $product_toppings->groupBy('category_name')->toArray();

                foreach ($product_toppings as $topping_key => $topping) {
                    $product_customize_arr[] = [
                        'category_name' => $topping_key,
                        'selection_type' => $product_toppings_category[$topping_key],
                        "customize_type" => 3,
                        'options' => $topping
                    ];
                }

                $product_data[] = [
                    "product_id" => $product->id,
                    "product_image" => $product->image != Null ? url('/assets/images/store-images/product-images/' . $product->image) : '',
                    "product_name" => $product->name,
                    "food_type" => $product->food_type,
                    "product_amount" => round($product_price * $currency, 2),
                    "discount_amount" => round($price_discount * $currency, 2),
                    "offer_discount" => round($offer_calculation * $currency, 2),
                    "description" => $product->description ? $product->description : '',
                    "customize_list" => $product_customize_arr
                ];
            }
            return response()->json([
                'status' => 1,
//                'message' => 'Success!',
                'message' => __('user_messages.1'),
                "message_code" => 1,
                'store_id' => $store_details->id - 0,
                'category_id' => $category_id - 0,
                'current_page' => $current_page - 0,
                'last_page' => $last_page - 0,
                'total' => $total - 0,
                'product_list' => $product_data
            ]);
        } else {
            return response()->json([
                'status' => 0,
//                'message' => 'Store Not Found!',
                'message' => __('user_messages.71'),
                "message_code" => 71,
            ]);
        }
    }

    //new place order for topping
    public function postStorePlaceOrder(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postStorePlaceOrder", $request->all());

        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric",
            "store_id" => "required|numeric",
            "address" => "required|numeric",
            "payment_type" => "required|in:1,2,3",
//            "promocode_id" => "required|numeric",
            "product_list" => "required",
            //"product_id_list" => "required",
            //"product_quantity_list" => "required",
            "additional_info" => "nullable",
            "user_taken_type" => "nullable|in:1,2",
            "tip" => "required|numeric",
            "promo_code" => "nullable",
            "reorder_id" => "nullable",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $settings = request()->get("general_settings");
        if ($settings == Null) {
            return response()->json([
                'status' => 0,
//                'message' => "Something went to wrong!",
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        //comment code for payment gateway
        if ($request->get('payment_type') == 2) {
            //1=paymongo, 0=paypal
            /*$validator = Validator::make($request->all(), [
                "payment_method_type" => "nullable|in:1,0",
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            if ($request->get('payment_method_type') != 1) {
                $get_usd_currency = WorldCurrency::query()->where('symbol', "$")->first();
                if ($get_usd_currency == Null) {
                    return response()->json([
                        'status' => 0,
//                        'message' => "Something went to wrong!",
                        'message' => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                if ($settings->paypal_sandbox == Null && $settings->paypal_client_id == Null && $settings->paypal_client_secret_key == Null) {
                    return response()->json([
                        'status' => 0,
                        'message' => "Something went to wrong!",
                        "message_code" => 9,
                    ]);
                }
            }*/
            $validator = Validator::make($request->all(), [
                "card_id" => "required|numeric",
            ]);
            if ($validator->fails()) {
                return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
                ]);
            }
            $card_details = UserCardDetails::query()->where('id', $request->get('card_id'))->first();
            if ($card_details == Null) {
                return response()->json([
                "status" => 0,
//                "message" => "card details not found",
                "message" => __('user_messages.120'),
                "message_code" => 120,
                ]);
            }
        }
        if ($request->get('service_category_id') == $this->medicineDelivery) {
            $validator = Validator::make($request->all(), [
                "prescription" => "nullable|file",
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
        }
        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $tip = $request->get('tip');
        $reorder_id = ($request->get('reorder_id') > 0)?$request->get('reorder_id'):0;
        $store_details = StoreDetails::query()->where('id', $request->get('store_id'))->first();
        if ($store_details != Null) {
            $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
            if ($user_currency == Null) {
                $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
            }
            $currency = $user_currency != Null ? $user_currency->ratio : 1;
            $currency_code = $user_currency != Null ? $user_currency->currency_code : '';
            $currency_symbol = $user_currency != Null ? $user_currency->symbol : '';

            $service_status = ProviderServices::query()->where('id', $store_details->provider_service_id)
                ->where('service_cat_id', '=', $request->get('service_category_id'))
                ->where('status', '=',1)
                ->where('current_status','=',1)->first();
            if ($service_status == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "store currently not available!a",
                    "message" => __('user_messages.69'),
                    "message_code" => 69,
                ]);
            }


            //check store open close

            $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                ->where('store_id', $store_details->id)
                ->where('day', 'all')
                //->orwhere('day', substr(date('l'), 0, 3))
                ->first();
            if ($store_time == Null) {
                if ($request->get('schedule_date_time') != Null) {
                    $timestamp = strtotime($request->get('schedule_date_time'));
                    $order_day = date('D', $timestamp);
                }else{
                    $order_day = date('D');
                }
                $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                    ->where('store_id', $store_details->id)
                    //->where('day', 'all')
                    ->where('day', $order_day)
                    ->first();

            }
            $open_time = "";
            $time = Date('H:i:s');
            //store open status find (not for demo)
            if ($store_time != Null) {

                if (($time >= $store_time->store_open_time && $time <= $store_time->store_close_time && $service_status->current_status == 0) || ( $store_time->store_open_time == "00:00:00" && $store_time->store_close_time == "00:00:00") ) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "store currently not available!",
                        "message" => __('user_messages.69'),
                        "message_code" => 69,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "store currently not available!",
                    "message" => __('user_messages.69'),
                    "message_code" => 69,
                ]);
            }

            /*$product_id_list = array_map('trim', explode(",", $request->get('product_id_list')));
            $product_quantity_list = array_map('trim', explode(",", $request->get('product_quantity_list')));
            $order_product_list = [];
            if (count($product_id_list) == count($product_quantity_list)) {*/

            $place_order = new UserProductBooking();
            $place_order->user_id = $request->get('user_id');
            $place_order->reorder_id = ($reorder_id > 0)?$reorder_id:0;
            $place_order->store_id = $store_details->id;
            $get_category = ServiceCategory::query()->where('id', $request->get('service_category_id'))->first();
            if ($get_category == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }

            //address json decode
            $delivery_cost = 0;
            if ($request->get('user_taken_type') != 2) {

                /*$address = json_decode($request->get('address'), true);
                if ((!array_key_exists('address', $address)) || (!array_key_exists('lat', $address)) || (!array_key_exists('long', $address)) || (!array_key_exists('flat_no', $address)) || (!array_key_exists('landmark', $address))) {
                    return response()->json([
                        "status" => 0,
                        "message" => "something went to wrong!",
                        "message_code" => 9,
                    ]);
                }
                if ($address['address'] == Null || $address['lat'] == Null || $address['long'] == Null) {
                    // if ($address['address'] == Null || $address['lat'] == Null || $address['long'] == Null || $address['flat_no'] == Null || $address['landmark'] == Null) {
                    return response()->json([
                        "status" => 0,
                        "message" => "something went to wrong!",
                        "message_code" => 9,
                    ]);
                }
                $address_lat = trim($address['lat']);
                $address_long = trim($address['long']);*/

                $address = UserAddress::query()->where('id','=', $request->get('address'))
                    ->where('user_id','=', $user_details->id)
                    ->where('status','=', 1)
                    ->first();
                if ($address == Null) {
                    return response()->json([
                        "status" => 0,
//                            "message" => "something went to wrong!",
                        "message" => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                $address_lat_long =  explode(",",$address->lat_long);
                $address_lat = isset($address_lat_long[0])?$address_lat_long[0]:0;
                $address_long = isset($address_lat_long[1])?$address_lat_long[1]:0;


                $area_details = RestrictedArea::query()->where('status',1)->get();
                $latitudes = $longitudes = '';
                foreach ($area_details as $area){
                    $latitudes = $latitudes . $area->latitude . ',';
                    $longitudes = $longitudes . $area->longitude . ',';
                }
                $restricted_lat = explode(',',substr($latitudes, 0, -1));
                $restricted_long = explode(',',substr($longitudes, 0, -1));
                $points_polygon = count($restricted_lat);
                if($this->adminClassApi->is_in_restricted_area($points_polygon,$restricted_lat,$restricted_long,$address_lat,$address_long)) {
                    return response()->json([
                        "status" => 0,
//                            "message" => "Your delivery address exist in restricted area, please try with other location!",
                        "message" => __('user_messages.195'),
                        "message_code" => 195,
                    ]);
                }

                $user_address = urlencode($address_lat.','.$address_long);
                $origin = urlencode($store_details->address_lat . "," . $store_details->address_long);
                $get_coordinates = $this->userClassapi->get_coordinates($origin, $user_address);
                $coordinates_check = json_decode($get_coordinates, TRUE);
                if ($coordinates_check == 0) {
                    return response()->json([
                        "status" => 0,
//                            "message" => "something went to wrong!",
                        "message" => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                $total_distance = 0;
                if (count($coordinates_check['routes']) > 0) {
                    $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);
                    $total_distance = ($total_distance * 0.001);
                }
                if ($total_distance > 0 ) {
                    $check_near_delivery_address = Provider::query()->where("id","=",$service_status->provider_id)->where('providers.service_radius', '<',$total_distance)->first();
                    if ($check_near_delivery_address != Null) {
                        return response()->json([
                            "status" => 0,
//                                "message" => "Your delivery address out of store delivery range! Please change your delivery address.",
                            "message" => __('user_messages.115'),
                            "message_code" => 115,
                        ]);
                    }
                }

                //$check_near_delivery_address = StoreDetails::query()->select('store_details.id', 'providers.service_radius'
                //    , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                //+COS( ' . $address_lat . ' * pi()/180)
                //* COS(store_details.address_lat * pi()/180)
                //* POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
                //)
                //    ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                //    ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                //    ->where('provider_services.service_cat_id', $request->get('service_category_id'))
                //    ->where('store_details.id', $store_details->id)
                //    ->havingRaw('providers.service_radius > distance')
                //    ->first();
                //if ($check_near_delivery_address == Null) {
                //    return response()->json([
                //        "status" => 0,
                //        "message" => "Your delivery address out of store delivery range! Please change your delivery address.",
                //        "message_code" => 115,
                //    ]);
                //}
                //$total_distance = $check_near_delivery_address->distance;

                $place_order->delivery_address = $address->address;
                $place_order->lat_long = $address->lat_long;
                $place_order->flat_no = ($address->flat_no != Null) ? $address->flat_no : "";
                $place_order->landmark = ($address->landmark != Null) ? $address->landmark : "";

                $delivery_cost = $this->userClassapi->checkDeliveryCharge($total_distance,$request->get('service_category_id'));
            }

            $service_settings = ServiceSettings::query()->where('service_cat_id', $request->get('service_category_id'))->first();
            if ($service_settings != Null) {
                //$delivery_charge = $service_settings->delivery_charge != Null ? $service_settings->delivery_charge : 0;

                $admin_commission = $service_settings->admin_commission;
                $tax_cost = $service_settings->tax != 0 ? $service_settings->tax : 0;
            } else {
                $admin_commission = 0;
                //$delivery_charge = 0;
                $tax_cost = 0;
            }
            $delivery_charge = $delivery_cost != Null ? $delivery_cost : 0;
            $get_surcharge_cost = 0;
            $get_surcharge_price = 0;
            $get_surcharge_start_time = "";
            $get_surcharge_end_time = "";
            if ($store_details->taken_away_type == 2) {
                if ($request->get('user_taken_type') == 2) {
                    $delivery_charge = 0;
                }else{
                    if ($service_settings->surcharge_timings_status == 1){
                        if ($request->get('order_pickup_time') != Null) {
                            $order_date_time = date('Y-m-d H:i:s', strtotime($request->get('order_pickup_time')));
                        } else {
                            $order_date_time = date('Y-m-d H:i:s');
                        }

                        $get_surcharge_time = SurchargeTimings::query()->select('surcharge_timings.day', 'surcharge_timings.start_time', 'surcharge_timings.end_time', 'surcharge_timings.price')
                            ->where('service_cat_id', '=', $get_category->id)
                            ->where('day','=','all')
                            ->first();
                        if ($get_surcharge_time == Null) {
                            $get_surcharge_time = SurchargeTimings::query()->select('surcharge_timings.day', 'surcharge_timings.start_time', 'surcharge_timings.end_time', 'surcharge_timings.price')
                                ->where('service_cat_id', '=', $get_category->id)
                                ->where('day', '=', substr(date('l',strtotime($order_date_time)), 0, 3))
                                ->first();
                        }

                        $surchargetime = date('H:i:s', strtotime($order_date_time));
                        if ($get_surcharge_time != Null) {
                            if ($surchargetime >= $get_surcharge_time->start_time && $surchargetime <= $get_surcharge_time->end_time) {
                                $get_surcharge_price = $get_surcharge_time->price;
                                $get_surcharge_start_time = $get_surcharge_time->start_time;
                                $get_surcharge_end_time = $get_surcharge_time->end_time;
                            }
                        }
                        if ($delivery_cost > 0 && $get_surcharge_price > 0){
                            $delivery_cost = ($delivery_cost * $get_surcharge_price);
                            $get_surcharge_cost = ($delivery_cost - $delivery_charge);
                        }
                    }
                }
            }

            $place_order->service_cat_id = $get_category->id;
            $place_order->delivery_cost = $delivery_cost;

            $place_order->delivery_charge = $delivery_charge;
            $place_order->surcharge_price = $get_surcharge_price;
            $place_order->surcharge_cost = $get_surcharge_cost;
            $place_order->surcharge_start_time = $get_surcharge_start_time;
            $place_order->surcharge_end_time = $get_surcharge_end_time;

            $place_order->packaging_cost = $store_details->packaging_charges;
            $place_order->status = 1;
            $place_order->store_name = $store_details->name;
            $place_order->user_name = $user_details['first_name'] ;

            $place_order->payment_type = $request->get('payment_type');
            //$place_order->payment_status = 0;
            if ($request->get('schedule_date_time') != Null) {
                $place_order->order_type = 1;
                $place_order->order_date_time = date('Y-m-d H:i:s', strtotime($request->get('schedule_date_time')));
            } else {
                $place_order->order_type = 0;
                $place_order->order_date_time = date('Y-m-d H:i:s');
            }
            $place_order->taken_away_type = ($request->get('user_taken_type') != Null) ? $request->get('user_taken_type') : 1;
            if ($place_order->taken_away_type == 1) {
                $place_order->otp = $place_order->generateOtp(4);
            }
            $place_order->additional_request = ($request->get('additional_info') != "") ? $request->get('additional_info') : "";
            $place_order->driver_algorithm = ($settings->driver_algorithm != Null)? $settings->driver_algorithm : 0;

            $product_list = json_decode($request->get('product_list'), true);
            $product_price = [];
            $order_product_list = [];
            $order_product_list_string = [];
            $discount_cost = 0;
            $total_price = 0;

            $in_active_product = "";
            foreach ($product_list as $single_product) {
                $get_product1 = StoreProductDetails::query()->where('id', $single_product['product_id'])->where('status', 1)->first();
                if ($get_product1 == Null) {
                    $in_active_product .= $single_product['product_id'] . ",";
                }
            }
            foreach ($product_list as $product) {
                if ($product['product_id'] != Null && $product['product_quantity'] != Null) {
                    $get_product = StoreProductDetails::query()->where('id', $product['product_id'])->where('status', 1)->first();
                    if ($get_product == Null) {
                        return response()->json([
                            "status" => 0,
//                                "message" => "no more available product, added to cart",
                            "message" => __('user_messages.157'),
                            "message_code" => 157,
                            "in_active_product_ids" => rtrim($in_active_product, ","),
                        ]);
                    }

                    $product_details = StoreProductDetails::query()->where('id', $product['product_id'])->where('status', 1)->first();
                    if ($product_details != Null) {
                        if ($product['product_quantity'] != Null) {
                            $quantity = 0;
                            foreach ($product['product_quantity'] as $product_quantity) {
                                $product_category = StoreProductCategory::query()->where('id', $product_details->product_cate_id)->first();
                                $size = $product_quantity['size'];
                                $size_data = [];
                                if ($size != 0) {
                                    $size_details = StoreProductSize::query()->where('id', $size)->first();
                                    if ($size_details != Null) {
                                        $price = $size_details->amount;
                                        $size_data = [
                                            "id" => $size_details->id,
                                            "name" => $size_details->name,
                                            "amount" => $size_details->amount,
                                        ];
                                    } else {
                                        $size_get = StoreProductSize::query()->where('product_id', $product_details->id)->get();
                                        if (count($size_get) > 2) {
                                            return response()->json([
                                                "status" => 0,
//                                                    "message" => "your not selected valid size",
                                                "message" => __('user_messages.9'),
                                                "message_code" => 9,
                                            ]);
                                        } elseif (count($size_get) == 1) {
                                            $price = $size_get[0]->amount;
                                            $size_data = [
                                                "id" => $size_get[0]->id,
                                                "name" => $size_get[0]->name,
                                                "amount" => $size_get[0]->amount
                                            ];
                                        } else {
                                            $price = $product_details->amount;
                                        }
                                    }
                                } else {
                                    $size_get = StoreProductSize::query()->where('product_id', $product_details->id)->get();
                                    if (count($size_get) > 2) {
                                        return response()->json([
                                            "status" => 0,
//                                                "message" => "your not selected valid size",
                                            "message" => __('user_messages.9'),
                                            "message_code" => 9,
                                        ]);
                                    } elseif (count($size_get) == 1) {
                                        $price = $size_get[0]->amount;
                                        $size_data = [
                                            "id" => $size_get[0]->id,
                                            "name" => $size_get[0]->name,
                                            "amount" => $size_get[0]->amount
                                        ];
                                    } else {
                                        $price = $product_details->amount;
                                    }
                                }

                                if ($product_details->discount_amount != Null && $product_details->discount_type != Null) {
                                    if ($product_details->discount_type == 1) {
                                        $price = $price - $product_details->discount_amount;
                                        $discount_amount = $product_details->discount_amount;
                                    } elseif ($product_details->discount_type == 2) {
                                        $discount_amount = round((($price * $product_details->discount_amount) / 100), 2);
                                        $price = $price - $discount_amount;
                                    } else {
                                        $discount_amount = 0;
                                    }
                                } else {
                                    $discount_amount = 0;
                                }

                                $options = $product_quantity['options'];
                                $options_data = Null;
                                $options_data = [];
                                if ($options != Null) {
                                    foreach ($options as $option) {
                                        $options_details = StoreProductOptions::query()->select('store_product_options.id',
                                            'store_product_options.name',
                                            'store_product_options.amount',
                                            'store_product_customize_category.name as category_name')
                                            ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_options.category_id')
                                            ->where('store_product_options.id', $option)
                                            ->where('store_product_options.product_id', $product_details->id)->first();
                                        if ($options_details != Null) {
                                            $price = $price + $options_details->amount;
                                            $options_data[] = [
                                                'id' => $options_details->id,
                                                'name' => $options_details->name,
                                                'amount' => $options_details->amount,
                                                'category' => $options_details->category_name
                                            ];
                                        }
                                    }
                                }
                                $toppings = $product_quantity['toppings'];
                                $toppings_data = Null;
                                $toppings_data = [];
                                if ($toppings != Null) {
                                    foreach ($toppings as $topping) {
                                        $toppings_details = StoreProductToppings::query()->select('store_product_toppings.id',
                                            'store_product_toppings.name',
                                            'store_product_toppings.amount',
                                            'store_product_customize_category.name as category_name')
                                            ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')
                                            ->where('store_product_toppings.id', $topping)
                                            ->where('store_product_toppings.product_id', $product_details->id)->first();
                                        if ($toppings_details != Null) {
                                            $price = $price + $toppings_details->amount;
                                            $toppings_data[] = [
                                                'id' => $toppings_details->id,
                                                'name' => $toppings_details->name,
                                                'amount' => $toppings_details->amount,
                                                'category' => $toppings_details->category_name
                                            ];
                                        }
                                    }
                                }
                                $quantity = $quantity + $product_quantity['quantity'];
                                $price_for_one = $price;
                                $price = round($price * $product_quantity['quantity'], 2);
                                $total_price = round($total_price + $price, 2);
                                $product_price[] = round($price, 2);

                                $order_product_list [] = [
                                    'product_id' => $product_details->id,
                                    'product_name' => $product_details->name,
                                    'product_quantity' => $product_quantity['quantity'] - 0,
                                    'product_category' => $product_category != Null ? $product_category->name : Null,
                                    'product_amount' => $price_for_one,
                                    'product_discount' => $discount_amount,
                                    'food_type' => $product_details->food_type,
                                    'size' => $size_data,
                                    'options' => $options_data,
                                    'toppings' => $toppings_data
                                ];
                            }
                            array_push($order_product_list_string, $product_details->name . ' x ' . $quantity);
                        }
                    }
                }
            }

            $discount_cost = 0;
            if ($store_details->offer_min_amount != 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
                if ($total_price >= $store_details->offer_min_amount) {
                    if ($store_details->offer_type == 1) {
                        $discount_cost = $total_price - (($total_price) - ($store_details->offer_amount));
                    } else {
                        $discount_cost = ((($total_price) * ($store_details->offer_amount)) / 100);
                    }
                }
            }
            if ($store_details->offer_min_amount == 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
                if ($store_details->offer_type == 1) {
                    $discount_cost = $total_price - (($total_price) - ($store_details->offer_amount));
                } else {
                    $discount_cost = ((($total_price) * ($store_details->offer_amount)) / 100);
                }
            }
            if ($store_details->order_min_amount > $total_price) {
                return response()->json([
                    "status" => 0,
//                        "message" => "Order min amount" . $currency_symbol . " " . round(($store_details->order_min_amount * $currency), 2),

                    "message" =>  __('user_messages.127', ['currencySymbol' => $currency_symbol, 'amount' => round(($store_details->order_min_amount * $currency), 2)]),
                    "message_code" => 127,
                    "order_min_amount" => round(($store_details->order_min_amount * $currency), 2) - 0
                ]);
            }

            if ($request->file('prescription')) {
                $file = $request->file('prescription');
//                    $destinationPath = public_path('/assets/images/prescription-images/');
                /*$img = Image::make($file->getRealPath());
                $img->orientate();
                $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
                $img->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->save($destinationPath . $file_new);*/
                $file_new = random_int(1, 99) . date('siHYdm') . random_int(1, 99) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path() . '/assets/images/prescription-images/', $file_new);
                $place_order->prescription = $file_new;
            }
            $place_order->save();
            $place_order->generateBookingNo();

            //$extra_charges = $place_order->delivery_cost + $place_order->packaging_cost;
            $delivery_cost = $place_order->delivery_cost;
            $packaging_cost = $place_order->packaging_cost;
            $promo_code_discount = 0;
            $get_promo_code = $request->get('promo_code');
            if ($get_promo_code != 0) {
                if (!is_numeric($get_promo_code)) {
                    $get_promocode = PromocodeDetails::query()->where('promo_code', $get_promo_code)->where('service_cat_id', $request->get('service_category_id'))->where('status','1')->first();
                    $get_promo_code_id = ($get_promocode != Null) ? $get_promocode->id : 0;
                }
                //$discount_on_amount = (($total_price - $discount_cost) + $place_order->packaging_cost);

                if($get_promo_code_id > 0)
                {
                    $discount_on_amount = $total_price;
//                        $promo_code = $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $discount_on_amount, $user_details->id, $request->get('service_category_id'));
                    list($promo_code_status,$promo_message_code,$promo_code_message,$min_order_amount,$promo_code,$promo_code_name) = $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $discount_on_amount, $user_details->id, $request->get('service_category_id'));
                    /*$promo_code_status = $promo_code_status;
                    $promo_message_code = $promo_message_code;
                    $promo_code_message = $promo_code_message;
                    $min_order_amount = $min_order_amount;*/

                    if ($promo_code_status == 1) {
                        $get_prmocode = PromocodeDetails::query()->where('id', $get_promo_code_id)->where('status', 1)->first();
                        if ($get_prmocode != Null) {
                            $promo_code_discount = $promo_code;

                            $add_promocode_details = new UsedPromocodeDetails();
                            $add_promocode_details->service_cat_id = $request->get('service_category_id');
                            $add_promocode_details->user_id = $request->get('user_id');
                            $add_promocode_details->promocode_id = $get_promo_code_id;
                            $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                            $add_promocode_details->discount_amount = $promo_code;
//                                $add_promocode_details->status = 1;
                            $add_promocode_details->save();

                            $get_prmocode->total_usage = $get_prmocode->total_usage + 1;
                            $get_prmocode->save();

                            $place_order->promo_code = $add_promocode_details->id;
                            $place_order->save();
                        }
                    }
                }

            }
            $admin_delivery_commission = ($settings != Null) ? $settings->delivery_commission : 0;

            $place_order->totalPay($total_price, $discount_cost, $promo_code_discount, $tax_cost, $delivery_cost, $packaging_cost, $admin_commission, $admin_delivery_commission,$tip);

            //if ($place_order->payment_type != 2) {
            $product_status = new UserProductBookingStatus();
            $product_status->order_id = $place_order->id;
            $product_status->status = 1;
            $product_status->save();
            //}

            $place_order->order_product_list = implode(", ", $order_product_list_string);
            $place_order->save();

            if ($user_details->pending_refer_discount > 0) {
                $user = User::query()->where('id', $user_details->id)->first();
                if ($user != Null) {
                    $user_refer_history = UserReferHistory::query()->where('user_id', $user_details->id)->where('user_status', 0)->first();
                    $total_price = $place_order->total_pay;
                    if ($user_refer_history != Null) {
                        if ($user_refer_history->user_discount_type == 1) {
                            $refer_discount_price = round($total_price - $user_refer_history->user_discount, 2);
                        } else {
                            $refer_discount_price = round((($total_price * $user_refer_history->user_discount) / 100), 2);
                            $refer_discount_price = round($total_price - $refer_discount_price, 2);
                        }
                        if ($refer_discount_price < 0) {
                            $refer_discount_price = 0;
                        }
                        $place_order->total_pay = $refer_discount_price;
                        $place_order->refer_discount = round($user_refer_history->user_discount, 2);
                        $place_order->save();
                        $user_refer_history->user_status = 1;
                        $user_refer_history->save();
                        $user->pending_refer_discount = $user->pending_refer_discount - 1;
                        $user->save();
                    } else {
                        $user_refer_history = UserReferHistory::query()->where('refer_id', $user_details->id)->where('refer_status', 0)->first();
                        if ($user_refer_history != Null) {
                            if ($user_refer_history->refer_discount_type == 1) {
                                $refer_discount_price = round($total_price - $user_refer_history->refer_discount, 2);
                            } else {
                                $refer_discount_price = round((($total_price * $user_refer_history->refer_discount) / 100), 2);
                                $refer_discount_price = round($total_price - $refer_discount_price, 2);
                            }
                            if ($refer_discount_price < 0) {
                                $refer_discount_price = 0;
                            }
                            $place_order->total_pay = $refer_discount_price;
                            $place_order->refer_discount = round($user_refer_history->user_discount, 2);
                            $place_order->save();
                            $user_refer_history->refer_status = 1;
                            $user_refer_history->save();
                            $user->pending_refer_discount = $user->pending_refer_discount - 1;
                            $user->save();
                        }
                    }
                }
            }

            if ($order_product_list != Null) {
                foreach ($order_product_list as $product) {
                    $add_product = new UserProductBookingQuantity();
                    $add_product->order_id = $place_order->id;
                    $add_product->product_id = $product['product_id'];
                    $add_product->product_name = $product['product_name'];
                    $add_product->product_cat_name = $product['product_category'];
                    $add_product->num_of_items = $product['product_quantity'];
                    $add_product->price_for_one = $product['product_amount'];
                    $add_product->food_type = $product['food_type'];
                    $add_product->save();
                    if ($product['size'] != Null) {
                        $product_size = new UserProductBookingSize();
                        $product_size->order_id = $place_order->id;
                        $product_size->product_id = $add_product->id;
                        $product_size->size_id = $product['size']['id'];
                        $product_size->name = $product['size']['name'];
                        $product_size->amount = $product['size']['amount'];
                        $product_size->save();
                    }
                    if ($product['options'] != Null) {
                        foreach ($product['options'] as $options) {
                            $product_option = new UserProductBookingOptions();
                            $product_option->order_id = $place_order->id;
                            $product_option->product_id = $add_product->id;
                            $product_option->options_id = $options['id'];
                            $product_option->name = $options['name'];
                            $product_option->category = $options['category'];
                            $product_option->amount = $options['amount'];
                            $product_option->save();
                        }
                    }
                    if ($product['toppings'] != Null) {
                        foreach ($product['toppings'] as $toppings) {
                            $product_topping = new UserProductBookingToppings();
                            $product_topping->order_id = $place_order->id;
                            $product_topping->product_id = $add_product->id;
                            $product_topping->toppings_id = $toppings['id'];
                            $product_topping->name = $toppings['name'];
                            $product_topping->category = $toppings['category'];
                            $product_topping->amount = $toppings['amount'];
                            $product_topping->save();
                        }
                    }
                }
            }

            if ($place_order->payment_type == 3) {
                $wallet_balance = UserWalletTransaction::query()->where('user_id', $request->get('user_id'))->orderBy('id', 'desc')->first();
                if ($wallet_balance != Null) {
                    $balance = $wallet_balance->remaining_balance;
                } else {
                    $balance = 0;
                }
                if ($place_order->total_pay > $balance) {
                    $place_order->status = 10;
                    $place_order->save();
                    return response()->json([
                        "status" => 0,
//                            "message" => "You can't pay order amount through Wallet because your wallet balance is insufficient.",
                        "message" => __('user_messages.110'),
                        "message_code" => 110
                    ]);
                }
                $add_balance = new UserWalletTransaction();
                $add_balance->user_id = $request->get('user_id');
                $add_balance->wallet_provider_type = $this->user_type;
                $add_balance->transaction_type = 2;
                $add_balance->amount = $place_order->total_pay;
                //$add_balance->subject = "Paid To Store - Order #" . $place_order->order_no;
                $add_balance->subject = "Paid to " . $store_details->name . " - " . $get_category->name . " Booking # " . $place_order->order_no;
                $add_balance->subject_code = 3;
                $add_balance->order_no = $place_order->order_no;
                $add_balance->remaining_balance = round($balance - $place_order->total_pay, 2);
                $add_balance->save();
                $place_order->payment_type = $request->get('payment_type');
                $place_order->payment_status = 1;
                $place_order->save();
            }

            $success_url = "";
            $failed_url = "";
            $redirect_url = "";
            if ($place_order->payment_type == 2) {
                $place_order->payment_status = 1;
                $place_order->save();
//                    if ($request->get('payment_method_type') == 1) {
//
//                        $amount_to_php = round($place_order->total_pay, 2);
//                        if ($amount_to_php < 100 || $amount_to_php > 99999999) {
//                            $place_order->status = 10;
//                            $place_order->save();
//                            return response()->json([
//                                "status" => 0,
//                                "message" => "With paymongo min amount must be ₱100 and max amount must be ₱99999999",
//                                "message_code" => 158,
//                            ]);
//                        }
//
//                        $success_url = \URL::route('paypmongo.success');
//                        $failed_url = \URL::route('paypmongo.failed');
//                        $error_url = \URL::route('paypmongo.failed');
//                        //$error_url = \URL::route('mongo_pay.error');
//
//                        try {
//                            $user_id = $request->get('user_id');
//                            $service_category_id = $get_category->id;
//                            $order_id = $place_order->order_no;
//
//
//                            $mongo_success_url = \URL::route('mongo_pay.success', ["id" => $user_id, "order_id" => $order_id]);
//                            $mongo_failed_url = \URL::route('mongo_pay.failed', ["id" => $user_id, "order_id" => $order_id]);
//
//                            $gcashSource = Paymongo::source()->create([
//                                'type' => 'gcash',
//                                'amount' => $amount_to_php,
//                                'currency' => 'PHP',
//                                'user_id' => $user_id,
//                                'redirect' => [
//                                    'success' => $mongo_success_url,
//                                    'failed' => $mongo_failed_url
//                                ]
//
//                            ]);
//
//                            $get_gcashSource_data = $gcashSource->getData();
//                            $mongo_success_url = $get_gcashSource_data['redirect']['checkout_url'];
//                            $gcashSource_status = $get_gcashSource_data['status'];
//                            $source_type = $get_gcashSource_data['source_type'];
//                            $gcashSource_id = $get_gcashSource_data['id'];
//                            if ($mongo_success_url != "" && $gcashSource_status == "pending" && $source_type == "gcash") {
//
//                                $place_order->status = 11;
//                                $place_order->transaction_id = $gcashSource_id;
//                                $place_order->save();
//
//                                $tempUserBooking = New TempUserBooking();
//                                $tempUserBooking->user_id = $user_id;
//                                $tempUserBooking->service_cat_id = $service_category_id;
//                                $tempUserBooking->booking_id = $order_id;
//                                $tempUserBooking->transaction_id = $gcashSource_id;
//                                $tempUserBooking->payment_method_type = 1;
//                                $tempUserBooking->coupon_ids = 0;
//                                $tempUserBooking->amount = round($amount_to_php, 2);
//                                $tempUserBooking->payment_status = 0;
//                                $tempUserBooking->save();
//
//                                $redirect_url = $mongo_success_url;
//
////                                return response()->json([
////                                    "status" => 1,
////                                    "message" => "success!",
////                                    "message_code" => 1,
////                                    "redirect_url" => $mongo_success_url,
////                                    "success_url" => $success_url,
////                                    "failed_url" => $failed_url,
////                                    "error_url" => $error_url,
////                                ]);
//
//                            } else {
//                                $place_order->status = 10;
//                                $place_order->save();
//                                return response()->json([
//                                    "status" => 0,
//                                    "message" => "Payment transaction failed",
//                                    "message_code" => 108,
//                                ]);
//                            }
//
//
//                        } catch (\Exception $e) {
//                            $place_order->status = 10;
//                            $place_order->save();
//                            return response()->json([
//                                "status" => 0,
//                                "message" => $e->getMessage(),
//                                "message_code" => 108,
//                            ]);
//                        }
//
//                    }
//                    else {
//                        $amount_to_usd = round($place_order->total_pay * $get_usd_currency->ratio, 2);
//
//                        try {
//                            $user_id = $request->get('user_id');
//                            $service_category_id = $get_category->id;
//                            $order_id = $place_order->id;
//                            $paypal_sandbox = $settings->paypal_sandbox . "";
//                            $paypal_client_id = $settings->paypal_client_id . "";
//                            $paypal_client_secret_key = $settings->paypal_client_secret_key . "";
//
//                            $api_context = new ApiContext(new OAuthTokenCredential($paypal_client_id, $paypal_client_secret_key));
//
//                            $settings = array(
//                                'mode' => $paypal_sandbox,
//                                'http.ConnectionTimeOut' => 30,
//                                'log.LogEnabled' => true,
//                                'log.FileName' => storage_path() . '/logs/paypal.log',
//                                'log.LogLevel' => 'ERROR'
//                            );
//                            $api_context->setConfig($settings);
//
//
//                            $payer = new Payer();
//                            $payer->setPaymentMethod('paypal');
//
//                            $item_1 = new Item();
//                            $item_1->setName('Item 1')
//                                ->setCurrency('USD')
//                                ->setQuantity(1)
//                                ->setPrice($amount_to_usd);
//
//                            $item_list = new ItemList();
//                            $item_list->setItems(array($item_1));
//
//                            $amount = new Amount();
//                            $amount->setCurrency('USD')
//                                ->setTotal($amount_to_usd);
//
//                            $transaction = new Transaction();
//                            $transaction->setAmount($amount)
//                                ->setItemList($item_list)
//                                ->setCustom($user_id . ',' . $service_category_id . ',' . $order_id)
//                                ->setDescription('Your transaction description');
//
//                            $redirect_urls = new RedirectUrls();
//                            $redirect_urls->setReturnUrl(\URL::route('payment.status'))/** Specify return URL **/
//                            ->setCancelUrl(\URL::route('payment.status'));
//
//                            $payment = new Payment();
//                            $payment->setIntent('Sale')
//                                ->setPayer($payer)
//                                ->setRedirectUrls($redirect_urls)
//                                ->setTransactions(array($transaction));
//
//                            try {
//                                $payment->create($api_context);
//                            } catch (\PayPal\Exception\PPConnectionException $ex) {
//                                if (\Config::get('app.debug')) {
//                                    $place_order->status = 10;
//                                    $place_order->save();
//                                    return response()->json([
//                                        "status" => 0,
//                                        "message" => "Connection timeout",
//                                        "message_code" => 9,
//                                    ]);
//                                } else {
//                                    $place_order->status = 10;
//                                    $place_order->save();
//                                    return response()->json([
//                                        "status" => 0,
//                                        "message" => "Some error occur, sorry for inconvenient",
//                                        "message_code" => 9,
//                                    ]);
//                                }
//                            }
//                            foreach ($payment->getLinks() as $link) {
//                                if ($link->getRel() == 'approval_url') {
//                                    $redirect_url = $link->getHref();
//                                    break;
//                                }
//                            }
//                            \Session::put('paypal_payment_id', $payment->getId());
//                            if (isset($redirect_url)) {
//                                $place_order->status = 11;
//                                $place_order->transaction_id = $payment->getId();
//                                $place_order->save();
//                            } else {
//                                $place_order->status = 10;
//                                $place_order->save();
//                                return response()->json([
//                                    "status" => 0,
//                                    "message" => "Payment transaction failed",
//                                    "message_code" => 108,
//                                ]);
//                            }
//                        } catch (\Exception $e) {
//                            $place_order->status = 10;
//                            $place_order->save();
//                            return response()->json([
//                                "status" => 0,
//                                "message" => $e->getMessage(),
//                                "message_code" => 108,
//                            ]);
//                        }
//                        $success_url = \URL::route('paypal.success');
//                        $failed_url = \URL::route('paypal.failed');
//                    }
            }

           // if ($place_order->payment_type != 2) {
            $store_provider_details = Provider::query()->select('providers.device_token', 'providers.login_device', 'providers.email', 'providers.language')
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->join('store_details', 'store_details.provider_service_id', '=', 'provider_services.id')
                ->where('store_details.id', $place_order->store_id)->first();

            if ($store_provider_details != Null) {
                $this->notificationClass->storeDeliveryUserNotification($place_order->id, $store_provider_details->device_token, $place_order->status, $store_provider_details->login_device, $store_provider_details->language);
            }
            //}

            if ($settings->send_mail == 1) {
                //user mail
                $email = $user_details['email'];
                $service_name = ucwords(strtolower($get_category->name));
                $store_name = ucwords($store_details->name);
                $user_name = ($place_order->user_name != "") ? $place_order->user_name : "";
                try {
                    $mail_type = "new_order_place-delivery";
                    $to_mail = $email;
                    $subject = "Your " . $settings->mail_site_name . " " . $service_name . " order from " . $store_name;
                    $disp_data = array("##user_name##" => $user_name, "##store_name##" => $store_name);
                    $mail_return_data = $this->notificationClass->sendMail($subject, $to_mail, $mail_type, $disp_data);
                } catch (\Exception $e) {}
                if ($store_provider_details != Null){
                    $html_product_detail = "";
                    $product_details = UserProductBookingQuantity::query()->select('product_name', 'product_cat_name', 'num_of_items', 'price_for_one')->where('order_id', $place_order->id)->get()->groupBy('product_cat_name');
                    foreach ($product_details as $get_single_product) {
                        foreach ($get_single_product as $single_product) {
                            $html_product_detail .= '<tr>
                                    <td><p class="lh top-desc" style="margin-bottom: 3px;">' . $single_product->product_name . '</p></td>
                                    <td><p class="lh top-desc" style="margin-bottom: 3px;">' . $currency_symbol . $single_product->price_for_one . '</p></td>
                                </tr>';
                        }
                    }

                    // store mail
                    try {
                        $product_details = $html_product_detail;
                        $mail_type = "store_new_order";
                        $to_mail = $store_provider_details->email;
                        $total = $place_order->total_pay;
                        $subject = $store_name . " you have a new order request.";
                        $disp_data = array("##store_name##" => $store_name, "##user_name##" => $user_name, "##product_details##" => $product_details, "##total##" => $total, "##currency_symbol##" => $currency_symbol);
                        $mail_return_data = $this->notificationClass->sendMail($subject, $to_mail, $mail_type, $disp_data);
                    } catch (\Exception $e) {}

                    //admin mail
                    try {
                        if ($settings->send_receive_email != Null) {
                            $product_details = $html_product_detail;
                            $mail_type = "admin_new_order_–_delivery";
                            $to_mail = $settings->send_receive_email;
                            $total = $place_order->total_pay;
                            $subject = $store_name . " Store get a new order request.";
                            $disp_data = array("##store_name##" => $store_name, "##user_name##" => $user_name, "##product_details##" => $product_details, "##total##" => $total, "##currency_symbol##" => $currency_symbol);
                            $mail_return_data = $this->notificationClass->sendMail($subject, $to_mail, $mail_type, $disp_data);
                        }
                    } catch (\Exception $e) {}
                }
            }

            return response()->json([
                "status" => 1,
                //"message" => "success!",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "order_id" => $place_order->id,
                "order_no" => $place_order->order_no,
                "redirect_url" => $redirect_url,
                "success_url" => $success_url,
                "failed_url" => $failed_url,
                'unavailable_products' => !empty($in_active_product) ? $in_active_product : ''
            ]);
        } else {
            return response()->json([
                "status" => 0,
                //"message" => "store not found!",
                "message" => __('user_messages.71'),
                "message_code" => 71,
            ]);
        }
    }

    public function postStoreTrackOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "order_id" => "required|numeric"
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $order = UserProductBooking::query()->select('id', 'store_id', 'delivery_people_id', 'order_no', 'service_cat_id', 'total_item_cost', 'payment_type',
            'created_at as order_time', 'total_pay', 'store_name', 'delivery_address', 'lat_long', 'delivery_people_name','otp',
            'status', 'cancel_by', 'cancel_reason', 'taken_away_type','order_date_time')->where('id', $request->get('order_id'))->first();
        if ($order != Null) {
            $service_settings = ServiceSettings::query()->where('service_cat_id', $order->service_cat_id)->first();
            if ($service_settings == Null) {
                return response()->json([
                    'status' => 0,
//                    'message' => "Something went to wrong!",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $cancel_charge = 0;
            //if ($order->taken_away_type != 2){
            if ($order->payment_type == 2 || $order->payment_type == 3) {
                $cancel_charge = round((($order->total_item_cost * $service_settings->cancel_charge) / 100), 2);
            }
            //}

            $total_items = UserProductBookingQuantity::query()->where('order_id', $order->id)->count();
            $store_details = StoreDetails::query()->where('id', $order->store_id)->first();
            $get_status = UserProductBookingStatus::query()->select('status as order_status', 'created_at as update_status_time')->where('order_id', $order->id)->get();
            if ($get_status->isEmpty()) {
                return response()->json([
                    'status' => 0,
//                    'message' => "something went to wrong!",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $order_status = [];
            foreach ($get_status as $status) {
                if ($status["order_status"] == 1) {
                    $order_status[] = [
                        'order_status' => $status["order_status"],
                        'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                        'update_status_date_time' => $status["update_status_time"],
                    ];
                }
                if ($status["order_status"] == 2) {
                    $order_status[] = [
                        'order_status' => $status["order_status"],
                        'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                        'update_status_date_time' => $status["update_status_time"],
                    ];
                }
                if ($status["order_status"] == 3) {
                    $order_status[] = [
                        'order_status' => $status["order_status"],
                        'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                        'update_status_date_time' => $status["update_status_time"],
                    ];
                }
                if ($status["order_status"] == 4) {
                    $order_status[] = [
                        'order_status' => $status["order_status"],
                        'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                        'update_status_date_time' => $status["update_status_time"],
                    ];
                }

                if ($order->taken_away_type == 2) {
                    if ($status["order_status"] == 5) {
                        $order_status[] = [
                            'order_status' => $status["order_status"],
                            'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                            'update_status_date_time' => $status["update_status_time"],
                        ];
                    }
                } else {
                    if ($status["order_status"] == 6) {
                        $order_status[] = [
                            'order_status' => $status["order_status"],
                            'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                            'update_status_date_time' => $status["update_status_time"],
                        ];
                    }
                }
                if ($status["order_status"] == 8) {
                    $order_status[] = [
                        'order_status' => $status["order_status"],
                        'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                        'update_status_date_time' => $status["update_status_time"],
                    ];
                }
                if ($status["order_status"] == 9) {
                    $order_status[] = [
                        'order_status' => $status["order_status"],
                        'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                        'update_status_date_time' => $status["update_status_time"],
                    ];
                }
//                if ($status["order_status"] == 9) {
//                    $order_status[$status["order_status"]] = [
//                        'order_status' => $status["order_status"],
//                        'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
//                    ];
//                }
//                if ($status["order_status"] == 1 || $status["order_status"] == 2 || $status["order_status"] == 3 || $status["order_status"] == 4 || $status["order_status"] == 5 || $status["order_status"] == 6 || $status["order_status"] == 7 || $status["order_status"] == 9) {
//                    $order_status[] = [
//                        'order_status' => $status["order_status"],
//                        'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
//                    ];
//                }
            }
//            return $order_status;
//            usort($order_status, function ($a, $b) {
//                if ($a['order_status'] == $b['order_status']) return 0;
//                return $a['order_status'] < $b['order_status'] ? -1 : 1;
//            });
            $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
            if ($user_currency == Null) {
                $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
            }
            $currency = $user_currency->ratio;

            $store_image = url('/assets/images/profile-images/provider/');
            $store_banner = url('/assets/images/store-images/');
            $find_store = StoreDetails::query()->select('providers.id',
                DB::raw("(CASE WHEN store_details.image != '' THEN (CASE WHEN CHAR_LENGTH(store_details.image) >= 25 THEN store_details.image ELSE concat('$store_banner','/',store_details.image) END) ELSE '' END) as store_banner"),
                'store_details.rating as store_ratting',
                DB::raw("(CASE WHEN providers.avatar != '' THEN (CASE WHEN CHAR_LENGTH(providers.avatar) >= 25 THEN providers.avatar ELSE concat('$store_image','/',providers.avatar) END) ELSE '' END) as store_image"),
                'providers.contact_number as store_contact_number',
                'providers.country_code as store_country_code'
            )
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('store_details.id', $order->store_id)->first();

            $order_cancel_reject_string = config('rejectcancelstring.'.$user_details->language);

            $order_cancel_reject_title = '';
            if(isset($order_cancel_reject_string[$order->status])){
                if($order->cancel_by != Null){
                    $order_cancel_by_string = config('rejectcancelstring.'.$user_details->language);
                    $cancel_by = $order_cancel_by_string[5];
                    if($order->cancel_by == 'user'){
                        $cancel_by = $order_cancel_by_string[5];
                    }
                    elseif ($order->cancel_by == 'admin') {
                        $cancel_by = $order_cancel_by_string[7];
                    }
                    else {
                        $cancel_by = $order_cancel_by_string[6];
                    }
                }
                $order_cancel_reject_title = $order->status == 3 ? $order_cancel_reject_string[$order->status] : ($order->status == 4 ? $order_cancel_reject_string[$order->status] . ucwords(strtolower(trim($cancel_by))) : '');
            }

            try {
                $delivery_person_details = TransportDriverDetails::query()->select('providers.contact_number', 'providers.avatar',
                    'transport_driver_details.rating','transport_driver_details.driver_service', 'transport_driver_details.delivery_boy',
                    'transport_driver_details.courier_boy', 'transport_driver_details.rental_service','provider_services.service_cat_id',
                    'providers.language','providers.device_token','providers.id as driver_id',DB::raw("CONCAT(providers.first_name,' ') as driver_name"),'transport_driver_details.rating')
                    ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                    ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                    ->where('transport_driver_details.id', $order->delivery_people_id)
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
                    $delivery_person_contact_no = $delivery_person_details->contact_number;
                    $delivery_person_rating = $delivery_person_details->rating;
                    $delivery_person_name = $delivery_person_details->driver_name;

                    $driver_service = $delivery_person_details->driver_service;
                    $delivery_boy = $delivery_person_details->delivery_boy;
                    $courier_boy = $delivery_person_details->courier_boy;
//                    $service_lang_converted = $this->notificationClass->serviceLangConvert();
                    $lang = $delivery_person_details->language;

                    if($lang !="en" && $lang != "" && $lang != "Null" ){
                        $lang = $lang."_";
                    }else{
                        $lang ="";
                    }
                    $ride_service =  (config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') !="")?config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') ." ":config('global.lang_constant.RIDE_SERVICE.value')." ";
                    $courier_delivery = (config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.COURIER_DELIVERY.value')." " ;
                    $store_delivery = (config('global.lang_constant.STORE_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.STORE_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.STORE_DELIVERY.value')." " ;
                    $rental_service = (config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')!="")?config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')." ":config('global.lang_constant.RENTAL_SERVICE.value');

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
                    $service = '';
                    $delivery_person_device_token = '';
                    $driver_id = 0;
                    $delivery_person_rating = 0;
                    $delivery_person_name = '';

                }
            } catch (\Exception $e) {
                $delivery_person_image = "";
                $delivery_person_contact_no = "";
                $service = '';
                $delivery_person_device_token = '';
                $driver_id = 0;
                $delivery_person_rating = 0;
                $delivery_person_name = '';
            }
            return response()->json([
                'status' => 1,
//                'message' => "success!",
                'message' => __('user_messages.1'),
                "message_code" => 1,
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'otp' => $order->otp.'',
                'cancel_charge' => round($cancel_charge * $currency, 2),
                'cancel_by' => $order->cancel_by != Null ? $order->cancel_by : '',
                'cancel_reason' => $order->cancel_reason != Null ? $order->cancel_reason : '',
                'order_time' => $this->notificationClass->dateLangConvert(Date('D d M, h:iA', strtotime($order->order_time)),$user_details->language),
                'total_items' => $total_items,
                'total_pay' => round($order->total_pay * $currency, 2),
                'store_id' => $find_store != Null ? $find_store->id : 0,
                //
                'store_image' => $find_store->store_image != Null ? $find_store->store_image : '',
                'store_banner' => $find_store->store_banner != Null ? $find_store->store_banner : '',
                'store_ratting' => $find_store->store_ratting != Null ? $find_store->store_ratting : 0,
                'store_contact_number' => $find_store->store_contact_number != Null ? $find_store->store_country_code.$find_store->store_contact_number : 0,
                //
                'store_name' => $order->store_name != Null ? $order->store_name : '',
                'pickup_address' => $store_details != Null ? $store_details->address : '',
                'pickup_lat_long' => $store_details != Null ? $store_details->address_lat . ',' . $store_details->address_long : '0,0',
                'destination_address' => $order->delivery_address,
                'destination_lat_long' => $order->lat_long,
                'service_date_time' => $order->order_time,
                'schedule_order_date_time' => $order->order_date_time,
                'delivery_people_name' => $order->delivery_people_name != Null ? $order->delivery_people_name : '',
                //
                'service_category_name' => $order->service_category_name != Null ? $order->service_category_name : '',
                'driver_contact_number' => $delivery_person_contact_no != Null ? $delivery_person_contact_no : '',
                'driver_selected_service_string' => $service != Null ? $service : '',
                'driver_id' => $driver_id != Null ? $driver_id : 0,
                'driver_fcm_token' => $delivery_person_device_token != Null ? $delivery_person_device_token : '',
                'driver_image' => $delivery_person_image != Null ? $delivery_person_image : '',
                'driver_rating' => $delivery_person_rating != Null ? $delivery_person_rating : 0,
                //
                'order_current_status' => $order->status,
                'user_taken_type' => $order->taken_away_type - 0,
                'order_cancel_reject_title' => $order_cancel_reject_title,
                'order_status_list' => $order_status
            ]);
        } else {
            return response()->json([
                'status' => 0,
//                'message' => "order not found!",
                'message' => __('user_messages.59'),
                "message_code" => 59,
            ]);
        }
    }

    public function postStoreOrderDetails(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "order_id" => "required|numeric"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;

        return $this->userClassapi->findStoreOrderDetails($request->get('order_id'), $currency);
    }

    public function postStoreOrderHistory(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postStoreOrderHistory", $request->all());

        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "filter_type" => "nullable|in:0,1,2,3,4,5",
            "timezone" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }

        $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;

        date_default_timezone_set($request->get('timezone'));

        if ($user_details->language != "en") {
            $lang_prefix = $user_details->language."_";
        }  else {
            $lang_prefix = "";
        }

        $filter_type = $request->get('filter_type');
        $date = date('Y-m-d');
        if ($filter_type == 1) {
            //today
            $start_date = $date . " 00:00:01";
            $end_date = $date . " 23:59:59";
        } elseif ($filter_type == 2) {
            //last 7 day
            $start_date = date('Y-m-d', strtotime('-7 days', strtotime($date)));
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
        }

        $per_page = 10;
        if ($request->get('per_page') != Null) {
            $per_page = $request->get('per_page');
        }

       /* $start = new \DateTime($start_date, new \DateTimeZone(config('app.timezone')));
        $start->setTimezone(new \DateTimeZone($request->get('timezone')));

        $end = new \DateTime($end_date, new \DateTimeZone(config('app.timezone')));
        $end->setTimezone(new \DateTimeZone($request->get('timezone')));*/


        if ($filter_type == 1 || $filter_type == 2 || $filter_type == 3 || $filter_type == 4 || $filter_type == 5) {
            $orders = UserProductBooking::query()->select('service_category.id as category_id',
                'service_category.' . $lang_prefix . 'name as category_name',
                'service_category.icon_name as category_icon',
                'user_store_product_booking.created_at as order_date_time',
                'user_store_product_booking.id',
                'user_store_product_booking.order_no',
                'user_store_product_booking.store_name',
                'user_store_product_booking.delivery_address',
                'user_store_product_booking.total_pay',
                'user_store_product_booking.reorder_type',
                'user_store_product_booking.taken_away_type',
                DB::raw("DATE_FORMAT(user_store_product_booking.created_at, '%Y-%m-%d %H:%i:%s') as service_date_time"),
                DB::raw("DATE_FORMAT(user_store_product_booking.order_date_time, '%Y-%m-%d %H:%i:%s') as schedule_order_date_time"),
                'user_store_product_booking.status')
                ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
                ->where('user_store_product_booking.user_id', $user_details->id)
                ->where('user_store_product_booking.status', '!=', 11)
                ->orderBy('user_store_product_booking.id', 'desc')
                ->where('user_store_product_booking.order_date_time', '>=', $start_date);
            if($filter_type != 5) {
                $orders = $orders->where('user_store_product_booking.order_date_time', '<=', $end_date);
            }
        } else {
            $orders = UserProductBooking::query()->select('service_category.id as category_id',
                'service_category.' . $lang_prefix . 'name as category_name',
                'service_category.icon_name as category_icon',
                'user_store_product_booking.created_at as order_date_time',
                'user_store_product_booking.id',
                'user_store_product_booking.order_no',
                'user_store_product_booking.store_name',
                'user_store_product_booking.delivery_address',
                'user_store_product_booking.total_pay',
                'user_store_product_booking.reorder_type',
                'user_store_product_booking.taken_away_type',
                DB::raw("DATE_FORMAT(user_store_product_booking.created_at, '%Y-%m-%d %H:%i:%s') as service_date_time"),
                DB::raw("DATE_FORMAT(user_store_product_booking.order_date_time, '%Y-%m-%d %H:%i:%s') as schedule_order_date_time"),
                'user_store_product_booking.status')
                ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
                ->where('user_store_product_booking.user_id', $user_details->id)
                ->where('user_store_product_booking.status', '!=', 11)
                ->orderBy('user_store_product_booking.id', 'desc');
        }
        $orders = $orders->paginate($per_page);
        $current_page = $orders->currentPage();
        $last_page = $orders->lastPage();
        $total = $orders->total();
        $order_list = [];
        foreach ($orders as $order) {
            $order_list[] = [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'category_id' => $order->category_id,
                'category_name' => $order->category_name,
                'category_icon' => $order->category_icon != Null ? url('/assets/images/service-category/' . $order->category_icon) : '',
                'store_name' => $order->store_name != Null ? $order->store_name : '',
                'service_date' => $this->notificationClass->dateLangConvert(date('D d M ,Y', strtotime($order->order_date_time)),$user_details->language),
                'service_time' => date('h:i', strtotime($order->order_date_time)),
                'service_date_time' => $order->service_date_time,
                'schedule_order_date_time' => $order->schedule_order_date_time,
                'delivery_address' => $order->delivery_address,
                'total_pay' => round($order->total_pay * $currency, 2),
                'order_status' => $order->status,
                'user_taken_type' => $order->taken_away_type - 0,
                'allow_reorder' => $order->reorder_type ,
            ];
        }
        return response()->json([
            'status' => 1,
//            'message' => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            'current_page' => $current_page - 0,
            'last_page' => $last_page - 0,
            'total' => $total - 0,
            'order_list' => $order_list
        ]);
    }

    public function postStoreOrderCancelled(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "order_id" => "required|numeric",
            "cancel_reason" => "nullable",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }

        $order_details = UserProductBooking::query()->where('id', $request->get('order_id'))->first();
        if ($order_details != Null) {
            if ($order_details->status < 4) {
                if ($order_details->status == 3) {
                    return response()->json([
                        'status' => 0,
                        //'message' => "order rejected by store!",
                        'message' => __('user_messages.52'),
                        'message_code' => 52,
                    ]);
                }

                //if ($order_details->order_type == 1 && $order_details->order_date_time != Null) {
                //    $pickup_date_time = strtotime(date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($order_details->order_date_time))));
                //    $current_date_time = strtotime(date('Y-m-d H:i:s'));
                //    if ($current_date_time >= $pickup_date_time) {
                //        return response()->json([
                //            "status" => 0,
                //            "message" => "You cant cancel the order after 60 minutes of order time.",
                //            "message_code" => 147,
                //        ]);
                //    }
                //}
                $order_status = $order_details->status;
                $order_details->otp = null;
                $order_details->status = 4;
                $order_details->cancel_by = "user";
                if ($request->get('cancel_reason') != Null) {
                    $order_details->cancel_reason = $request->get('cancel_reason');
                }
                $order_details->save();

                if ($order_status < 5){
                    if ($order_details->promo_code > 0){
                        $used_promocode_details = UsedPromocodeDetails::query()->where('id', $order_details->promo_code)->first();
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

                try {
                    TransportRideDriverToken::query()->where('delivery_id', $order_details->id)->where('service_cat_id', $order_details->service_cat_id)->delete();
                }catch (\Exception $e){}

                if ($order_details->order_type != 1) {
                    if ($order_details->payment_type == 2 || $order_details->payment_type == 3) {
                        $service_settings = ServiceSettings::query()->where('service_cat_id', $order_details->service_cat_id)->first();
                        if ($service_settings != Null) {
                            $cancel_charge = $service_settings->cancel_charge != Null ? $service_settings->cancel_charge : 0;
                            $count_cancel_charge = round((($order_details->total_item_cost * $cancel_charge) / 100), 2);
                            $charges = round($order_details->total_pay - $count_cancel_charge, 2);
                            $order_details->cancel_charge = $count_cancel_charge;
                            $order_details->refund_amount = $charges;
                            $order_details->save();
                        }
                    }
                }
                $order_status = UserProductBookingStatus::query()->where('order_id', $request->get('order_id'))->where('status', 4)->first();
                if ($order_status == Null) {
                    $order_status = new UserProductBookingStatus();
                    $order_status->order_id = $order_details->id;
                    $order_status->status = 4;
                    $order_status->save();
                }

                ProviderUserRunningService::query()->where('service_cat_id', $order_details->service_cat_id)->where('user_id', $order_details->user_id)->where('booking_id', $order_details->id)->delete();
                $order = UserProductBooking::query()->select('order_shopper_status', 'shopper_type', 'id', 'service_cat_id', 'shopper_id', 'store_id', 'order_no', 'created_at as order_time', 'total_pay', 'store_name', 'delivery_address', 'lat_long', 'delivery_people_name', 'status', 'cancel_by', 'taken_away_type')
                    ->where('id', $request->get('order_id'))->first();
                if ($order != Null) {
                    $get_status = UserProductBookingStatus::query()->select('status as order_status', 'created_at as update_status_time')->where('order_id', $order->id)->get();
                    if ($get_status->isEmpty()) {
                        return response()->json([
                            'status' => 0,
//                            'message' => "something went to wrong!",
                            'message' => __('user_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    $order_status = [];
                    foreach ($get_status as $status) {
                        if ($status["order_status"] == 1) {
                            $order_status[$status["order_status"]] = [
                                'order_status' => $status["order_status"],
                                'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                            ];
                        }
                        if ($status["order_status"] == 2) {
                            $order_status[$status["order_status"]] = [
                                'order_status' => $status["order_status"],
                                'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                            ];
                        }
                        if ($status["order_status"] == 3 || $status["order_status"] == 4) {
                            $order_status[$status["order_status"]] = [
                                'order_status' => $status["order_status"],
                                'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                            ];
                        }
                        if ($order->taken_away_type == 2) {
                            if ($status["order_status"] == 5) {
                                $order_status[] = [
                                    'order_status' => $status["order_status"],
                                    'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                                ];
                            }
                        } else {
                            if ($status["order_status"] == 6) {
                                $order_status[$status["order_status"]] = [
                                    'order_status' => $status["order_status"],
                                    'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                                ];
                            }
                        }
                        if ($status["order_status"] == 8) {
                            $order_status[$status["order_status"]] = [
                                'order_status' => $status["order_status"],
                                'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                            ];
                        }
                        if ($status["order_status"] == 9) {
                            $order_status[$status["order_status"]] = [
                                'order_status' => $status["order_status"],
                                'update_status_time' => Date('h:i A', strtotime($status["update_status_time"])),
                            ];
                        }
                    }
                    usort($order_status, function ($a, $b) {
                        if ($a['order_status'] == $b['order_status']) return 0;
                        return $a['order_status'] < $b['order_status'] ? -1 : 1;
                    });
                    $store_provider_details = Provider::query()->select('providers.device_token', 'providers.login_device', 'providers.language')
                        ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                        ->join('store_details', 'store_details.provider_service_id', '=', 'provider_services.id')
                        ->where('store_details.id', $order->store_id)->first();
                    if ($store_provider_details != Null) {
                        $this->notificationClass->storeDeliveryUserNotification($order->id, $store_provider_details->device_token, $order_details->status, $store_provider_details->login_device, $store_provider_details->language);
                    }

                    $order_cancel_reject_string = config('rejectcancelstring.'.$user_details->language);

                    $order_cancel_reject_title = '';
                    if(isset($order_cancel_reject_string[$order->status])){
                        if($order->cancel_by != Null){
                            $order_cancel_by_string = config('rejectcancelstring.'.$user_details->language);
                            $cancel_by = $order_cancel_by_string[5];
                            if($order->cancel_by == 'user') {
                                $cancel_by = $order_cancel_by_string[5];
                            } elseif ($order->cancel_by == 'admin') {
                                $cancel_by = $order_cancel_by_string[7];
                            } else{
                                $cancel_by = $order_cancel_by_string[6];
                            }
                        }
                        $order_cancel_reject_title = $order->status == 3 ? $order_cancel_reject_string[$order->status] : ($order->status == 4 ? $order_cancel_reject_string[$order->status] . ucwords(strtolower(trim($cancel_by))) : '');
                    }

                    return response()->json([
                        'status' => 1,
//                        'message' => "success!",
                        'message' => __('user_messages.1'),
                        "message_code" => 1,
                        'store_name' => $order->store_name != Null ? $order->store_name : '',
                        'order_current_status' => $order->status,
                        'order_cancel_reject_title' => $order_cancel_reject_title,
                        'cancel_charge' => $order_details->cancel_charge - 0,
                        'order_status_list' => $order_status
                    ]);
                } else {
                    return response()->json([
                        'status' => 0,
//                        'message' => "order not found!",
                        'message' => __('user_messages.59'),
                        "message_code" => 59,
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 0,
//                    'message' => "customer not allow to cancel this order!",
                    'message' => __('user_messages.70'),
                    "message_code" => 70,
                ]);
            }
        } else {
            return response()->json([
                'status' => 0,
//                'message' => "order details not found!",
                'message' => __('user_messages.59'),
                "message_code" => 59,
            ]);
        }
    }

    public function postStoreOrderRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "order_id" => "required|numeric",
            "driver_rating" => "nullable|numeric|required_with:driver_comment",
            "driver_comment" => "nullable",
            "store_rating" => "nullable|numeric|required_with:store_comment",
            "store_comment" => "nullable"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'message_code' => 9
            ]);
        }

        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }

        $order_details = UserProductBooking::where('id', $request->get('order_id'))->first();
        if ($order_details != Null) {
            $order_details->user_rating_status = 1;
            $order_details->save();

            if (($request->get('driver_rating') != Null)) {
                if ($order_details->delivery_people_id != Null) {
                    $check_duplicate = TransportRatings::where('driver_id', $order_details->delivery_people_id)->where('delivery_id', $order_details->id)->first();
                    if ($check_duplicate == Null) {
                        $driver_rating = new TransportRatings();
                        $driver_rating->driver_id = $order_details->delivery_people_id;
                        $driver_rating->user_id = $user_details->id;
                        $driver_rating->delivery_id = $order_details->id;
                        $driver_rating->rating = round($request->get('driver_rating'), 1);
                        if ($request->get('driver_comment') != Null) {
                            $driver_rating->comment = $request->get('driver_comment');
                        }
                        $driver_rating->status = 1;
                        $driver_rating->save();
                        $driver_details = TransportDriverDetails::where('id', $order_details->delivery_people_id)->first();
                        if ($driver_details != Null) {
                            $ratings = TransportRatings::query()
//                            ->select(DB::raw('avg(rating) as ratings'))
                                ->groupBy('driver_id')
                                ->where('driver_id', $order_details->delivery_people_id)
//                            ->first();
                                ->avg('rating');
                            $driver_details->rating = round($ratings, 1);
                            $driver_details->save();
                        }
                    }
                }
            }
            if (($request->get('store_rating') != Null)) {
                $check_duplicate = StoreRatings::where('store_id', $order_details->store_id)->where('booking_id', $order_details->id)->first();
                if ($check_duplicate == Null) {
                    $store_rating = new StoreRatings();
                    $store_rating->store_id = $order_details->store_id;
                    $store_rating->user_id = $user_details->id;
                    $store_rating->booking_id = $order_details->id;
                    $store_rating->rating = round($request->get('store_rating'), 1);
                    if ($request->get('store_comment') != Null) {
                        $store_rating->comment = $request->get('store_comment');
                    }
                    $store_rating->status = 1;
                    $store_rating->save();
                    $store_details = StoreDetails::where('id', $order_details->store_id)->first();
                    if ($store_details != Null) {
                        $ratings = StoreRatings::query()
//                            ->select(DB::raw('avg(rating) as ratings'))
                            ->groupBy('store_id')
                            ->where('store_id', $order_details->store_id)
                            ->avg('rating');
                        $store_details->rating = round($ratings, 1);
                        $store_details->save();
                    }
                }
            }
            return response()->json([
                'status' => 1,
//                'message' => "success!",
                'message' => __('user_messages.1'),
                'message_code' => 1,
            ]);
        } else {
            return response()->json([
                'status' => 0,
//                'message' => "order details not found!",
                'message' => __('user_messages.59'),
                'message_code' => 59
            ]);
        }
    }

    //code new topping data display
    public function postOrderPreview(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postOrderPreview", $request->all());

        $validator = Validator::make($request->all(), [
            "user_id" => "required|integer",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric",
            "store_id" => "required|numeric",
            "product_list" => "required",
            //"product_id_list" => "required",
            //"product_quantity_list" => "required",
//            "promocode_id" => "required",
            "address" => "required|numeric",
            "user_taken_type" => "nullable|in:1,2",
            "promo_code" => "nullable",
            "tip" => "required",
            "order_pickup_time"=>"nullable",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;
        $store_details = StoreDetails::query()->where('id', $request->get('store_id'))->first();
        $product_price = [];
        $total_price = 0;
        $discount_cost = 0;
        if ($store_details != Null) {

            $service_status = ProviderServices::query()->where('id', $store_details->provider_service_id)
                ->where('service_cat_id', '=', $request->get('service_category_id'))->first();

//            if ($service_status == Null) {
//                return response()->json([
//                    "status" => 0,
////                    "message" => "store currently not available!a",
//                    "message" => __('user_messages.69'),
//                    "message_code" => 69,
//                ]);
//            }

            $product_list = json_decode($request->get('product_list'), true);
            $tip = $request->get('tip');
            $order_product_list = [];
            $item_cost = 0;
            $discount_cost = 0;
            $packaging_cost = $store_details->packaging_charges; // not required
            $promo_discount_cost = 0;

            /*$in_active_product = "";
            foreach ($product_list as $product) {
                $get_product1 = StoreProductDetails::where('id', $product['product_id'])->where('status', 1)->first();
                if ($get_product1 == Null) {
                    $in_active_product .= $product['product_id'] . ",";
                }
            }*/
            $unavailable_products = [];
            foreach ($product_list as $product) {
                /*$get_product = StoreProductDetails::where('id', $product['product_id'])->where('status', 1)->first();
                if ($get_product == Null) {
                    return response()->json([
                        "status" => 0,
                        "message" => "no more available product, added to cart",
                        "message_code" => 157,
                        "in_active_product_ids" => rtrim($in_active_product, ","),
                    ]);
                }*/
                if ($product['product_id'] != Null && $product['product_quantity'] != Null) {
                    $product_details = StoreProductDetails::query()->where('id', $product['product_id'])->where('status', 1)->first();
                    if ($product_details != Null) {
                        if ($product['product_quantity'] != Null) {
                            $quantity_price = 0;
                            $quantity = 0;
                            $order_product_quantity = [];
                            foreach ($product['product_quantity'] as $product_quantity) {
                                $size = $product_quantity['size'];
                                $add_ons = [];
                                if ($size != 0) {
                                    $size_details = StoreProductSize::query()->where('id', $size)->first();
                                    if ($size_details != Null) {
                                        $price = $size_details->amount;
                                        array_push($add_ons, $size_details->name);
                                    } else {
                                        $size_get = StoreProductSize::query()->where('product_id', $product_details->id)->get();
                                        if (count($size_get) > 2) {
                                            return response()->json([
                                                "status" => 0,
//                                                "message" => "your not selected valid size",
                                                "message" => __('user_messages.9'),
                                                "message_code" => 9,
                                            ]);
                                        } elseif (count($size_get) == 1) {
                                            $size_get = StoreProductSize::query()->where('product_id', $product_details->id)->first();
                                            if ($size_get == Null) {
                                                return response()->json([
                                                    "status" => 0,
//                                                    "message" => "your not selected valid size",
                                                    "message" => __('user_messages.9'),
                                                    "message_code" => 9,
                                                ]);
                                            }
                                            $price = $size_get->amount;
                                            array_push($add_ons, $size_details->name);
                                        } else {
                                            $price = $product_details->amount;
                                        }
                                    }
                                } else {
                                    $size_get = StoreProductSize::query()->where('product_id', $product_details->id)->get();
                                    if (count($size_get) > 2) {
                                        return response()->json([
                                            "status" => 0,
//                                            "message" => "your not selected valid size",
                                            "message" => __('user_messages.9'),
                                            "message_code" => 9,
                                        ]);
                                    } elseif (count($size_get) == 1) {
                                        $size_get = StoreProductSize::query()->where('product_id', $product_details->id)->first();
                                        if ($size_get == Null) {
                                            return response()->json([
                                                "status" => 0,
//                                                "message" => "your not selected valid size",
                                                "message" => __('user_messages.9'),
                                                "message_code" => 9,
                                            ]);
                                        }
                                        $price = $size_get->amount;
                                        array_push($add_ons, $size_get->name);
                                    } else {
                                        $price = $product_details->amount;
                                    }
                                }
                                if ($product_details->discount_amount != Null && $product_details->discount_type != Null) {
                                    if ($product_details->discount_type == 1) {
                                        $price = $price - $product_details->discount_amount;
                                        $discount_amount = $product_details->discount_amount;
                                    } elseif ($product_details->discount_type == 2) {
                                        $discount_amount = round((($price * $product_details->discount_amount) / 100), 2);
                                        $price = $price - $discount_amount;
                                    } else {
                                        $discount_amount = 0;
                                    }
                                } else {
                                    $discount_amount = 0;
                                }
                                $options = $product_quantity['options'];
                                if ($options != Null) {
                                    foreach ($options as $option) {
                                        $options_details = StoreProductOptions::query()->where('id', $option)->first();
                                        if ($options_details != Null) {
                                            $price = $price + $options_details->amount;
                                            array_push($add_ons, $options_details->name);
                                        }
                                    }
                                }
                                $toppings = $product_quantity['toppings'];
                                if ($toppings != Null) {
                                    foreach ($toppings as $topping) {
                                        $toppings_details = StoreProductToppings::query()->where('id', $topping)->first();
                                        if ($toppings_details != Null) {
                                            $price = $price + $toppings_details->amount;
                                            array_push($add_ons, $toppings_details->name);
                                        }
                                    }
                                }
                                $price_for_one = $price;
                                $price = round($price * $product_quantity['quantity'], 2);
                                $total_price = round($total_price + $price, 2);
                                $product_price[] = round($price, 2);
                                $quantity_price = $quantity_price + $price;
                                $quantity = $quantity + $product_quantity['quantity'];

                                $order_product_quantity [] = [
                                    'product_quantity' => $product_quantity['quantity'] - 0,
                                    'price_for_one'=>round($price_for_one * $currency, 2),
                                    'product_amount' => round($price * $currency, 2),
                                    'product_discount' => round($discount_amount * $currency, 2),
                                    'product_add_ons' => implode(', ', $add_ons),
                                    'product_size' => $size,
                                    'product_options' => $options,
                                    'product_toppings' => $toppings
                                ];
                            }

                            $order_product_list [] = [
                                'product_id' => $product_details->id,
                                'product_name' => $product_details->name,
                                'product_total_quantity' => $quantity,
                                'product_total_amount' => round($quantity_price * $currency, 2),
                                'product_discount' => 0,// user other services
                                'product_quantity_list' => $order_product_quantity
                            ];

                        }
                    } else{
                        $unavailable_products[] = $product['product_id'];
                    }
                }
            }
            $unavailable_products = implode(',',$unavailable_products);
            if(count($order_product_list) == 0){
                return response()->json([
                    "status" => 0,
//                    "message" => "The added products are not available at the moment!",
                    "message" => __('user_messages.157'),
                    "message_code" => 157,
                    'unavailable_products' => !empty($unavailable_products) ? $unavailable_products : ''
                ]);
            }

            if ($store_details->offer_min_amount != 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
                if ($total_price >= $store_details->offer_min_amount) {
                    if ($store_details->offer_type == 1) {
                        $discount_cost = $total_price - (($total_price) - ($store_details->offer_amount));
                    } else {
                        $discount_cost = ((($total_price) * ($store_details->offer_amount)) / 100);
                    }
                }
            }
            if ($store_details->offer_min_amount == 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
                if ($store_details->offer_type == 1) {
                    $discount_cost = $total_price - (($total_price) - ($store_details->offer_amount));
                } else {
                    $discount_cost = ((($total_price) * ($store_details->offer_amount)) / 100);
                }
            }

            // checking store timing
            $order_day = date('D');
            $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                ->where('store_id', $store_details->id)
                ->where('day', $order_day)
                ->first();
            if ($store_time != Null) {
                $time = Date('H:i:s');
                if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time && $service_status->current_status == 1) {
                    $store_time_status = 1; //"open"
                } else {
                    $store_time_status = 0; //"close"
                }
            } else {
                $store_time_status = 0; //"close"
            }

            $delivery_cost = 0;
            if ($request->get('address') != Null && $request->get('address') > 0 ) {

                /*$address = json_decode($request->get('address'), true);

                if ((!array_key_exists('address', $address)) || (!array_key_exists('lat', $address)) || (!array_key_exists('long', $address)) ) {
                    return response()->json([
                        "status" => 0,
                        "message" => "something went to wrong!",
                        "message_code" => 9,
                    ]);
                }
                if ($address['address'] == Null || $address['lat'] == Null || $address['long'] == Null) {
                    // if ($address['address'] == Null || $address['lat'] == Null || $address['long'] == Null || $address['flat_no'] == Null || $address['landmark'] == Null) {
                    return response()->json([
                        "status" => 0,
                        "message" => "something went to wrong!",
                        "message_code" => 9,
                    ]);
                }
                $address_lat = trim($address['lat']);
                $address_long = trim($address['long']);*/

                /*$address = explode(',',$request->get('address'));
                if (!isset($address[0]) || $address[0] == Null || !isset($address[1]) || $address[1] == Null) {
                    return response()->json([
                        "status" => 0,
                        "message" => "something went to wrong!",
                        "message_code" => 9,
                    ]);
                }
                $address_lat = trim($address[0]);
                $address_long = trim($address[1]);*/
               /* $check_near_delivery_address = StoreDetails::select('store_details.id', 'providers.service_radius'
                    , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                    +COS( ' . $address_lat . ' * pi()/180)
                    * COS(store_details.address_lat * pi()/180)
                    * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
                )
                    ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                    ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                    ->where('provider_services.service_cat_id', $request->get('service_category_id'))
                    ->where('store_details.id', $store_details->id)
                    ->havingRaw('providers.service_radius > distance')
                    ->first();
                if ($check_near_delivery_address == Null) {
                    return response()->json([
                        "status" => 0,
                        "message" => "Your delivery address out of store delivery range! Please change your delivery address.",
                        "message_code" => 115,
                    ]);
                }
                $total_distance = $check_near_delivery_address->distance;

                $delivery_cost = $this->userClassapi->checkDeliveryCharge($total_distance);*/

                $address = UserAddress::query()->where('id','=', $request->get('address'))
                    ->where('user_id','=', $user_details->id)
                    ->where('status','=', 1)
                    ->first();

                if ($address == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
                }

                $address_lat_long =  explode(",",$address->lat_long);
                $address_lat = isset($address_lat_long[0])?$address_lat_long[0]:0;
                $address_long = isset($address_lat_long[1])?$address_lat_long[1]:0;


                $user_address = urlencode($address_lat.','.$address_long);
                $origin = urlencode($store_details->address_lat . "," . $store_details->address_long);
                $get_coordinates = $this->userClassapi->get_coordinates($origin, $user_address);
                $coordinates_check = json_decode($get_coordinates, TRUE);
                if ($coordinates_check['status'] != 'OK') {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Your delivery address out of store delivery range! Please change your delivery address.",
                        "message" => __('user_messages.115'),
                        "message_code" => 115,
                    ]);
                }
                $total_distance = 0;
                if (count($coordinates_check['routes']) > 0) {
                    $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);
                    $total_distance = ($total_distance * 0.001);
                }

                //code for check delivery address of store

                    /*$check_near_delivery_address = StoreDetails::select('store_details.id', 'providers.service_radius'
                    )
                        ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                        ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                        ->where('provider_services.service_cat_id', $request->get('service_category_id'))
                        ->where('store_details.id', $store_details->id)
                        ->first();
                    if ($check_near_delivery_address != Null) {
                        if($total_distance >$check_near_delivery_address->service_radius )
                        {
                            return response()->json([
                                "status" => 0,
                                "message" => "Your delivery address out of store delivery range! Please change your delivery address.",
                                "message_code" => 115,
                            ]);
                        }
                    }*/

                //End code for check delivery address of store

                $delivery_cost = $this->userClassapi->checkDeliveryCharge($total_distance,$request->get('service_category_id'));
            }

            $service_cat_id = $request->get("service_category_id");
            $settings = ServiceSettings::query()->where('service_cat_id',$service_cat_id)->first();
            if ($settings != Null) {
                $tax_cost = $settings->tax != 0 ? $settings->tax : 0;
                //$delivery_cost = $settings->delivery_charge != Null ? $settings->delivery_charge : 0;
                if ($settings->surcharge_timings_status == 1){
                    if ($request->get('order_pickup_time') != Null) {
                        $order_date_time = date('Y-m-d H:i:s', strtotime($request->get('order_pickup_time')));
                    } else {
                        $order_date_time = date('Y-m-d H:i:s');
                    }
                    $get_surcharge_time = SurchargeTimings::query()->select('surcharge_timings.day', 'surcharge_timings.start_time', 'surcharge_timings.end_time', 'surcharge_timings.price')
                        ->where('service_cat_id', '=', $service_cat_id)
                        ->where('day','=','all')
                        ->first();
                    if ($get_surcharge_time == Null) {
                        $get_surcharge_time = SurchargeTimings::query()->select('surcharge_timings.day', 'surcharge_timings.start_time', 'surcharge_timings.end_time', 'surcharge_timings.price')
                            ->where('service_cat_id', '=', $service_cat_id)
                            ->where('day', '=', substr(date('l',strtotime($order_date_time)), 0, 3))
                            ->first();
                    }
                    $get_surcharge_price = 0;
                    $surchargetime = date('H:i:s', strtotime($order_date_time));
                    if ($get_surcharge_time != Null) {
                        if ($surchargetime >= $get_surcharge_time->start_time && $surchargetime <= $get_surcharge_time->end_time) {
                            $get_surcharge_price = $get_surcharge_time->price;
                        }
                    }
                    if ($delivery_cost > 0 && $get_surcharge_price > 0){
                        $delivery_cost = ($delivery_cost * $get_surcharge_price);
                    }
                }

            } else {
                //$delivery_cost = 0;
                $tax_cost = 0;
            }
            $delivery_cost = $delivery_cost != Null ? $delivery_cost : 0;
            if ($store_details->taken_away_type == 2) {
                if ($request->get('user_taken_type') == 2) {
                    $delivery_cost = 0;
                }
            }
            $extra_charges = $delivery_cost + $packaging_cost;
            $promo_discount_cost = 0;
            $total_item_cost = round($total_price, 2);
            $discount_cost = round($discount_cost, 2);
            $promocode_name = '';
            $promo_code_apply = 0;
            $promo_code_status = 0;
            $promo_message_code = 0;
            $promo_code_message = "";
            $min_order_amount = 0;
            $get_promo_code = $request->get('promo_code');
            $get_promo_code_id = 0;

            if ($get_promo_code != "" &&  $get_promo_code!= Null ) {

                $promo_code_apply = 1;
                if (!is_numeric($get_promo_code)) {
                    $get_promocode = PromocodeDetails::query()->where('promo_code', $get_promo_code)->where('service_cat_id', $request->get('service_category_id'))->where('status','1')->first();
                    $get_promo_code_id = ($get_promocode != Null) ? $get_promocode->id : 0;
                }

                if ($get_promo_code_id != 0){
                    $discount_on_amount = $total_price;

//                    return $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $discount_on_amount, $user_details->id, $request->get('service_category_id'));
                    list($promo_code_status,$promo_message_code,$promo_code_message,$min_order_amount,$promo_code_amount,$promo_code_name) = $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $discount_on_amount, $user_details->id, $request->get('service_category_id'));
                    $promo_code_status = $promo_code_status;
                    $promo_message_code = $promo_message_code;
                    $promo_code_message = $promo_code_message;
                    $min_order_amount = $min_order_amount;
                    $promo_discount_cost = $promo_code_amount;
                    $promocode_name = $promo_code_name != Null ? $promo_code_name : "";

                }else{

                    $promo_code_status = 0;
                    $promo_message_code = 232;
                    $promo_code_message = __('user_messages.232');
                    $min_order_amount = 0;
                }
            }

            $tax_cost = round(((((($total_item_cost - $discount_cost) + $extra_charges) - $promo_discount_cost) * $tax_cost) / 100), 2);
            $total_pay = round((((($total_item_cost - $discount_cost) + $extra_charges + $tip) - $promo_discount_cost) + $tax_cost), 2);

            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "store_id" => $store_details->id,
                "store_name" => $store_details->name,
                "store_address" => isset($store_details->address)?$store_details->address:"",
                "required_prescription" => ($request->get('service_category_id') == $this->medicineDelivery) ? 1 : 0,
                "store_rating" => $store_details->rating,
                "store_banner" => $store_details->image != Null ? url('/assets/images/store-images/' . $store_details->image) : '',
                "total_item_cost" => round($total_item_cost * $currency, 2),
                "discount_cost" => round($discount_cost * $currency, 2),
                "apply_promocode_amount" => round((($total_item_cost - $discount_cost) + $packaging_cost) * $currency, 2),
                "packaging_cost" => round($packaging_cost * $currency, 2),
                "delivery_cost" => round($delivery_cost * $currency, 2),
                "tax_cost" => round($tax_cost * $currency, 2),
                "total_pay" => round($total_pay * $currency, 2),
                "order_min_amount" => $store_details->order_min_amount,
                "user_taken_type" => $request->get('user_taken_type') - 0,
                "user_taken_status" => $store_details->taken_away_type - 0,
                'promo_code_apply' => $promo_code_apply,
                'promo_code_status' => $promo_code_status,
                'promo_message_code' => $promo_message_code,
                'promo_code_message' => $promo_code_message,
                'min_order_amount' => $min_order_amount,
                "promocode_discount" => round($promo_discount_cost, 2),
                "promocode_name" => $promocode_name,
                'tip' => ($tip > 0) ? $tip-0 : 0,
                "product_list" => $order_product_list,
                'unavailable_products' => !empty($unavailable_products) ? $unavailable_products : '',
                "store_status" => $store_time_status,
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "store not found!",
                "message" => __('user_messages.71'),
                "message_code" => 71,
//                "message_code" => 9,
            ]);
        }
    }

    //code for display fav store
    public function postAddFavouriteStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric",
            "store_id" => "required|numeric",
            "is_favourite" => "required|in:1,0"
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $favourite_store = UserFavouriteStore::query()->select('id', 'store_id')->where('user_id','=', $user_details->id)
            ->where('service_cat_id', '=',$request->get('service_category_id'))
            ->where('store_id', '=',$request->get('store_id'))
            ->first();


        if ($request->get('is_favourite') != 1) {
            if ($favourite_store != Null) {
                $remove_favourite_store = UserFavouriteStore::query()->where('id','=', $favourite_store->id)->first();
                $remove_favourite_store->delete();
            }
            $message = "Favourite store remvove success!";
            $is_favourite = 0;
        } else {
            if ($favourite_store == Null) {
                $favourite_store = new UserFavouriteStore();
            }
            $favourite_store->user_id = $user_details->id;
            $favourite_store->store_id = $request->get('store_id');
            $favourite_store->service_cat_id = $request->get('service_category_id');
            $favourite_store->status = 1;
            $favourite_store->save();
            $message = "success!";
            $is_favourite = 1;
        }
        return response()->json([
            "status" => 1,
//            "message" => $message,
            "message" => __('user_messages.1'),
            "message_code" => 1,
            "is_favourite" => $is_favourite,
        ]);
    }

    //code for display fav store lists
    public function postFavouriteStoreLists(Request $request){
        $validator = Validator::make($request->all(), [
            "user_id" => "nullable|numeric",
            "access_token" => "nullable|numeric",
            "service_category_id" => "required",
            "view_all" => "required|in:0,1",
            "page" => "nullable|numeric",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_id = $request->get('user_id');
        $access_token = $request->get('access_token');
        $view_all = $request->get('view_all');
        /*$address_lat = $request->get('lat');
        $address_long = $request->get('long');*/
        $service_category_id = $request->get('service_category_id');
        if ($user_id != Null && $access_token != Null) {
            $user_details = $this->userClassapi->checkUserAllow($user_id, $access_token);
            if ($decoded['status'] = json_decode($user_details) == false) {
                return $user_details;
            }
            $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        } else {
            $user_currency = Null;
        }
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;
        $currency_symbol = $user_currency->symbol;

        $store_image_path = url('/assets/images/store-images/');
        $service_category_icon_url = url('/assets/images/service-category/');
        $language = isset($user_details->language) ? $user_details->language : "";
        if($language !="en" && $language != "" && $language != "Null" ){
            $lang_prefix = $language."_";
        }else{
            $lang_prefix ="";
        }
        $service_category_data = ServiceCategory::query()->select($lang_prefix."name as service_category_name",
            DB::raw("(CASE WHEN service_category.icon_name != '' THEN (concat('$service_category_icon_url','/',service_category.icon_name,'?v=0.3')) ELSE '' END) as service_category_icon")
        )->where('id',$service_category_id)->first();
        if ($service_category_data == Null){
            return response()->json([
                "status" => 0,
//                "message" => "service category not found",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }

        $fav_const = config('global.lang_constant.FAVORITE.'.$lang_prefix.'value') ." ";
        $get = config('global.lang_constant.GET.'.$lang_prefix.'value') ." ";
        $off_on_all_orders = " ".config('global.lang_constant.OFF_ON_ALL_ORDERS.'.$lang_prefix.'value')." " ;
        $off_on_orders_above = " ".config('global.lang_constant.OFF_ON_ORDERS_ABOVE.'.$lang_prefix.'value')." " ;
        $per_page_rec =($view_all == 0)?2:(($request->get('page') > 0)?$this->fav_store_display_limit:30);

        $store_details = UserFavouriteStore::query()->select('user_favourite_store.id', 'user_favourite_store.store_id', 'user_favourite_store.service_cat_id',
            'store_details.name as store_name', 'store_details.rating as average_ratings', 'store_details.eta_delivery_time',
            'store_details.offer_min_amount', 'store_details.offer_amount',
            'store_details.offer_type', 'store_details.order_min_amount','provider_services.current_status',
            DB::raw("(CASE WHEN store_details.image != '' THEN (concat('$store_image_path','/',store_details.image)) ELSE '' END) as store_image"),
            'providers.service_radius')
            ->join('store_details', 'store_details.id', '=', 'user_favourite_store.store_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('providers.status', 1)
            ->where('provider_services.status', 1)
            ->where('user_favourite_store.service_cat_id', '=', $service_category_id)
            ->where('user_favourite_store.user_id', '=', $user_id)
            //->havingRaw('providers.service_radius > distance')
            ->orderByRaw("user_favourite_store.id desc");
        $this->userClassapi->checkDayShowRecord($store_details,0);

        $total_fav = $store_details->count();

            //->get();
            //->toArray();
        $store_details=$store_details->paginate($per_page_rec);

        $store_slider_data = [];
        foreach ($store_details as $keys => $store_detail) {
            $product_cat_name = array_unique(StoreProductDetails::query()->select('store_product_category.name')
                ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                ->where('store_product_details.store_id', $store_detail->store_id)->where('store_product_category.status', 1)->where('store_product_details.status', 1)->get()->pluck('name')->toArray());
            $count_product_cat_name = count($product_cat_name);
            if ($count_product_cat_name > 3) {
                for ($n = 3; $n <= $count_product_cat_name; $n++) {
                    unset($product_cat_name[$n]);
                }
            }
            $product_cat_name = implode(',', $product_cat_name);

            // store offer get
            $offer_amount = round($store_detail->offer_amount * $currency, 2);
            $offer_min_amount = round($store_detail->offer_min_amount * $currency, 2);
            if ($store_detail->offer_min_amount != 0 && $store_detail->offer_amount != 0) {
                if ($store_detail->offer_type == 1) {
                    $offer = $get . $currency_symbol . $offer_amount . $off_on_orders_above . $currency_symbol . $offer_min_amount;
                } else {
                    $offer = $get . $offer_amount . "% " . $off_on_orders_above . "" . $currency_symbol . $offer_min_amount;
                }
            } elseif ($store_detail->offer_amount != 0 && $store_detail->offer_min_amount == 0) {
                if ($store_detail->offer_type == 1) {
                    $offer = $get . $currency_symbol . $offer_amount . " " . $off_on_all_orders;
                } else {
                    $offer = $get . $store_detail->offer_amount . "% " . $off_on_all_orders;
                }
            } else {
                $offer = "";
            }
            //check store open close

            $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                ->where('store_id', $store_detail->store_id)
                ->where('day', 'all')
                //->orwhere('day', substr(date('l'), 0, 3))
                ->first();
            if ($store_time == Null) {
                $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                    ->where('store_id', $store_detail->store_id)
                    //->where('day', 'all')
                    ->where('day', substr(date('l'), 0, 3))
                    ->first();
            }
            $open_time = "";
            $time = Date('H:i:s');
            //store open status find (not for demo)
            if ($store_time != Null) {
                if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time && $store_detail->current_status == 1) {
                    $store_time_status = 1; //"open"
                    $open_time = "";
                } else {
                    $store_time_status = 0; //"close"
                    $open_time = date('H:i A', strtotime($store_time->store_open_time));
                }
            } else {
                $store_time_status = 0; //"close"
                //$open_time = date('H:i A', strtotime($store_time->store_open_time));
            }


            $store_slider_data[$keys] = [
                "id" => $store_detail->id,
                "store_id" => $store_detail->store_id,
                "service_category_id" => $store_detail->service_cat_id,
                "store_name" => $store_detail->store_name,
                "store_products" => $product_cat_name,
                "average_ratings" => ($store_detail->average_ratings > 0) ? $store_detail->average_ratings : 0,
                "order_delivery_time" => $store_detail->eta_delivery_time,
                "store_banner" => ($store_detail->store_image != "") ? $store_detail->store_image : "",
                "offer" => $offer,
                "order_min_amount" => round($store_detail->order_min_amount * $currency, 2),
                "store_status" => $store_time_status,
            ];
        }

        $home_page_feature_all_store_lists['status'] = 1;
//        $home_page_feature_all_store_lists['message'] = "success!";
        $home_page_feature_all_store_lists['message'] = __('user_messages.1');
        $home_page_feature_all_store_lists['message_code'] = 1;
        $home_page_feature_all_store_lists['service_category_id'] = $service_category_id;
        $home_page_feature_all_store_lists['service_category_name'] = $service_category_data->service_category_name;
        $home_page_feature_all_store_lists['service_category_icon'] = $service_category_data->service_category_icon;
        $home_page_feature_all_store_lists['display_title_name'] = $fav_const ;
        $home_page_feature_all_store_lists['store_lists'] = $store_slider_data;
        $home_page_feature_all_store_lists['view_all_btn'] = ($total_fav > 2)?1:0;

        $home_page_feature_all_store_lists['total_page'] = ($store_details->total() > 0) ? ceil($store_details->total() / $per_page_rec) : 0;
        $home_page_feature_all_store_lists['per_page'] = ($store_details->perPage() > 0) ? $store_details->perPage() : 0;
        $home_page_feature_all_store_lists['current_page'] = ($store_details->currentPage() > 0) ? $store_details->currentPage() : 0;
        return $home_page_feature_all_store_lists;
    }

    //code for display fav store
    public function postDeliveryReorder(Request $request)
    {

        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postDeliveryReorder", $request->all());

        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "order_id" => "required|numeric",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_id = $request->get('user_id');
        $access_token = $request->get('access_token');
        $order_id = $request->get('order_id');
        $user_details = $this->userClassapi->checkUserAllow($user_id, $access_token);
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;
        $currency_symbol = $user_currency->symbol;
        $language = $user_details->language;
        if($language !="en" && $language != "" && $language != "Null" ){
            $language = $language."_";
        }else{
            $language ="";
        }
        $service_category_icon_url = url('/assets/images/service-category/');
        $get_booking_data= UserProductBooking::query()->where('user_id','=',$user_id)->where('id','=',$order_id)
                            ->where('reorder_type','=',1)
                            ->first();

        if($get_booking_data != Null){
            $store_details = StoreDetails::query()->select('store_details.id',
                'store_details.name',
                'store_details.image',
                'store_details.description',
                'store_details.eta_delivery_time',
                'store_details.order_min_amount',
                'store_details.offer_min_amount',
                'store_details.offer_amount',
                'store_details.offer_type',
                'store_details.taken_away_type',
                'store_details.address',
                'store_details.address_lat',
                'store_details.address_long',
                'store_details.packaging_charges',
                'store_details.rating as average_rating',
                'provider_services.current_status',
                'provider_services.service_cat_id',
                'service_category.'.$language . 'name as service_category_name',
                DB::raw("(CASE WHEN service_category.icon_name != '' THEN (concat('$service_category_icon_url','/',service_category.icon_name,'?v=0.3')) ELSE '' END) as service_category_icon"),
                'service_category.is_sub_cat_flow')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('store_details.id', $get_booking_data->store_id)->where('provider_services.status', 1)->first();

            if($store_details != Null){

                $get_active_products = StoreProductDetails::query()->select(
                    'store_product_category.name as category_name',
                    //'store_product_category.icon as category_icon',
                    'store_product_category.id as category_id')
                    ->join('store_product_category', 'store_product_category.id', 'store_product_details.product_cate_id')
                    ->where('store_product_details.store_id', $get_booking_data->store_id)
                    ->where('store_product_details.status', 1)
                    ->where('store_product_category.status', 1)->get();
                $store_active_product_categories = array_unique(Arr::pluck($get_active_products, 'category_name', 'category_id'));

                //code for check store status
                $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                    ->where('store_id', $store_details->id)
                    ->where('day', 'all')
                    ->first();

                if ($store_time == Null) {
                    $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                        ->where('store_id', $store_details->id)
                        //->where('day', 'all')
                        ->where('day', substr(date('l'), 0, 3))
                        ->first();

                }

                $time = Date('H:i:s');
                //store open status find (not for demo)
                if ($store_time != Null) {
                    if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time && $store_details->current_status == 1) {
                        $store_time_status = 1; //"open"
                    } else {
                        $store_time_status = 0; //"close"
                    }
                } else {
                    $store_time_status = 0; //"close"
                }

                //get offer list
                /*if($language !="en" && $language != "" && $language != "Null" ){
                    $language = $language."_";
                }else{
                    $language ="";
                }*/
                $get = config('global.lang_constant.GET.'.$language.'value') ." ";
                $off_on_all_orders = " ".config('global.lang_constant.OFF_ON_ALL_ORDERS.'.$language.'value')." " ;
                $off_on_orders_above = " ".config('global.lang_constant.OFF_ON_ORDERS_ABOVE.'.$language.'value')." " ;
                $offer_amount = round($store_details->offer_amount * $currency, 2);
                $offer_min_amount = round($store_details->offer_min_amount * $currency, 2);
                if ($store_details->offer_min_amount != 0 && $store_details->offer_amount != 0) {
                    if ($store_details->offer_type == 1) {
                        $offer = $get . $currency_symbol . $offer_amount . $off_on_orders_above . $currency_symbol . $offer_min_amount;
                    } else {
                        $offer = $get . $offer_amount . "% ".$off_on_orders_above . $currency_symbol . $offer_min_amount;
                    }
                } elseif ($store_details->offer_amount != 0 && $store_details->offer_min_amount == 0) {
                    $offer = $get. $store_details->offer_amount . "% ".$off_on_all_orders;

                } else {
                    $offer = "";
                }

                //check store fav or not
                //check store for store is fav or not
                $is_fav_store = 0;

                if($user_id > 0){
                    $check_fav_store = UserFavouriteStore::query()->where('user_id','=',$user_id)
                        ->where('store_id','=',$store_details->id)->first();
                    if($check_fav_store != Null){
                        $is_fav_store = 1;
                    }
                }

                $add_products = UserProductBookingQuantity::query()//Old Product Detail
                ->where('order_id','=',$get_booking_data->id)

                    ->get();
                $order_product_quantity = [];
                foreach ($add_products as $key => $add_product){
                    $size = [];
                    $options = [];
                    $toppings = [];
                    $product_total_amount = 0;
                    $product_details = StoreProductDetails::query()//New Product Detail
                    ->where('id', $add_product->product_id)
                        ->first();
                    $product_total_amount = isset($product_details->amount)?$product_details->amount:0;
                    if ($product_details != null){

                        $product_size = UserProductBookingSize::query()//Old Size Detail
                        ->where('order_id',$get_booking_data->id)
                            ->where('product_id',$add_product->id)
                            ->where('size_id','!=',0)
                            ->first();
                        //                    return $product_size;
                        $product_size_ids = 0;
                        if ($product_size != null){
                            $size_details = StoreProductSize::query()//New Size Detail
                            ->where('id', $product_size->size_id)
                                ->first();
                            if ($size_details != null){
                                $product_size_ids = $size_details->id;
                                $product_total_amount = $size_details->amount;

                                /*$size [] = [
                                    'id' => $size_details->id,
                                    'name' => $size_details->name,
                                    'amount' => $size_details->amount
                                ];*/
                            }
                        }
                        $product_option_ids = "";
                        $product_options = UserProductBookingOptions::query()
                            ->where('order_id',$get_booking_data->id)
                            ->where('product_id',$add_product->id)
                            ->where('options_id','!=',0)
                            ->get();
                        if (!$product_options->isEmpty()) {
                            foreach ($product_options as $key => $product_option){
                                $options_details = StoreProductOptions::query()
                                    ->where('id', $product_option->options_id)
                                    ->first();
                                if ($options_details != null){
                                    $product_option_ids .=$options_details->id.",";
                                    $product_total_amount = $product_total_amount + $options_details->amount;
                                    /*$options [] = [
                                        'id' => $options_details->id,
                                        'category_id' => $options_details->category_id,
                                        'name' => $options_details->name,
                                        'amount' => $options_details->amount
                                    ];*/
                                }
                            }
                        }
                        $product_toppings_ids = "";
                        $product_toppings = UserProductBookingToppings::query()
                            ->where('order_id',$get_booking_data->id)
                            ->where('product_id',$add_product->id)
                            ->where('toppings_id','!=',0)
                            ->get();

                        if (!$product_toppings->isEmpty()) {
                            foreach ($product_toppings as $key => $product_topping){
                                $toppings_details = StoreProductToppings::query()
                                    ->where('id', $product_topping->toppings_id)
                                    ->first();
                                if ($toppings_details != null){
                                    $product_toppings_ids .=$toppings_details->id.",";
                                    $product_total_amount = $product_total_amount + $toppings_details->amount;
                                    /*$toppings [] = [
                                        'id' => $toppings_details->id,
                                        'category_id' => $toppings_details->category_id,
                                        'name' => $toppings_details->name,
                                        'amount' => $toppings_details->amount
                                    ];*/
                                }
                            }
                        }
                        $product_total_amount = $product_total_amount * $add_product->num_of_items;
                        $product = [
                            'product_id' => $product_details->id,
                            'product_name' => $product_details->name,
                            'product_amount' => $product_details->amount,
                            'product_total_amount' => number_format($product_total_amount,2) - 0,
                            'product_food_type' => $product_details->food_type,
                            'product_quantity' => $add_product->num_of_items,
                            'product_size_ids' => $product_size_ids,
                            'product_option_ids' => trim($product_option_ids,","),
                            'product_toppings_ids' => trim($product_toppings_ids,","),
                        ];
                        $order_product_quantity[] = $product;
                        /*$order_product_quantity [] = [
                            'product' => $product,
                            'product_size' => $size,
                            'product_options' => $options,
                            'product_toppings' => $toppings,

                        ];*/
                    }
                }

                return response()->json([
                    "status" => 1,
//                    "message" => "success!",
                    "message" => __('user_messages.1'),
                    "message_code" => 1,
                    "reorder_id" => $order_id - 0,
                    "store_id" => $get_booking_data->store_id,
                    "service_category_id" => $get_booking_data->service_cat_id,
                    "service_category_name" => $store_details->service_category_name,
                    "service_category_icon" => $store_details->service_category_icon,


                    'store_name' => $store_details->name,
                    'store_banner' => $store_details->image != Null ? url('/assets/images/store-images/' . $store_details->image) : '',
                    'store_status' => $store_time_status,

                    'average_rating' => $store_details->average_rating,
                    'product_category' => implode(',', $store_active_product_categories),
                    'description' => $store_details->description != Null ? $store_details->description : '',
                    'delivery_time' => $store_details->eta_delivery_time,
                    'order_min_amount' => round($store_details->order_min_amount * $currency, 2),
                    'offer' => $offer,
                    'offer_min_amount' => round($store_details->offer_min_amount * $currency, 2),
                    'offer_amount' => round($store_details->offer_amount * $currency, 2),
                    'offer_type' => round($store_details->offer_type * $currency, 2),
                    'offer_percentage' => round($store_details->offer_amount * $currency, 2),
                    'is_fav_store' =>$is_fav_store,
                    "taken_away_type"=>$store_details->taken_away_type-0,
                    'address' => $store_details->address,
                    'latitude' => $store_details->address_lat,
                    'longitude' => $store_details->address_long,
                    'packaging_charges' => round($store_details->packaging_charges * $currency, 2),
                    "order_detail" => $order_product_quantity,
                ]);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => 'Store not found!',
                    "message" => __('user_messages.71'),
                    "message_code" => 71,
                ]);
            }

        }else{
            return response()->json([
                'status' => 0,
//                'message' => "order not found!",
                'message' => __('user_messages.59'),
                "message_code" => 59,
            ]);
        }
    }

}
