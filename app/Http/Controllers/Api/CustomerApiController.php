<?php

namespace App\Http\Controllers\Api;

use App\Classes\AdminClass;
use App\Classes\DriverClassApi;
use App\Classes\NotificationClass;
use App\Classes\OnDemandClassApi;
use App\Classes\StoreClassApi;
use App\Classes\UserClassApi;
use App\model\AppVersionSetting;
use App\model\CouponCodeDetail;
use App\model\CouponDetails;
use App\model\EmailTemplates;
use App\model\GeneralSettings;
use App\model\HomePageBanner;
use App\model\HomepageFeatureStore;
use App\model\HomepageSpotLight;
use App\model\LanguageLists;
use App\model\OtherServiceCategory;
use App\model\OtherServiceProviderDetails;
use App\model\OtherServiceProviderPackages;
use App\model\OtherServiceRatings;
use App\model\PageSettings;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ServiceCategory;
use App\model\StoreDetails;
use App\model\StoreProductDetails;
use App\model\StoreRatings;
use App\model\TempUserBooking;
use App\model\TransportRideBook;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserAddress;
use App\model\UserCardDetails;
use App\model\UserPackageBooking;
use App\model\UserPurchaseCouponDetails;
use App\model\UserRunningRide;
use App\model\UserWalletTransaction;
use App\model\WorldCountry;
use App\model\WorldCurrency;
use Braintree_Configuration;
use Braintree_Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Luigel\Paymongo\Facades\Paymongo;
use Mockery\Exception;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class CustomerApiController extends Controller
{
//        json response status [
//            0 => false,
//            1 => true,
//            2 => registration pending,
//            3 => app user blocked,
//            4 => app user access token not match,
//            5 => app user not found
//          ]

    private $userClassapi;
    private $on_demand_service_id_array;
    private $store_service_id_array = [5, 6, 7, 8, 9, 10];
    private $transport_service_id_array = [1, 2, 4];
    private $courier_service_id_array = [4];
    private $adminClass;
    private $notificationClass;
    private $driverClassApi;
    private $storeClassApi;
    private $onDemandClassApi;
    private $feature_store_display_limit = 15;
    private $spot_display_limit  = 15;
    private $top_rated_display_limit  = 3;
    private $user_type = 0;
    private $store_type = 1;
    private $driver_type = 2;
    private $provider_type = 3;

    private $delivery_category_type = [2];
    private $on_demand_category_type = [3,4];

    public function __construct(OnDemandClassApi $onDemandClassApi,StoreClassApi $storeClassApi,UserClassApi $userClassapi, AdminClass $adminClass, NotificationClass $notificationClass,DriverClassApi $driverClassApi)
    {
        $this->userClassapi = $userClassapi;
        $this->adminClass = $adminClass;
        $this->notificationClass = $notificationClass;
        $this->driverClassApi = $driverClassApi;
        $this->storeClassApi = $storeClassApi;
        $this->onDemandClassApi = $onDemandClassApi;

        //$this->on_demand_service_id_array = [11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30];
        $service_cat = ServiceCategory::query()->whereIN('category_type', [3, 4])->where('status', 1)->get()->pluck('id')->toArray();
        $this->on_demand_service_id_array = $service_cat;
    }

    public function postTestEmailTemplates(Request $request){
        //echo date('Y-m-d H:i:s');exit;
        $email_templates = EmailTemplates::query()->where('id',2)->where('status',1)->first();
        if($email_templates != Null){
            $items = ['%NAME%','%LINK%','%ADMIN%'];
            $replace = ['kkcoder','https://www.google.com','Froyo Tech Inc'];
            $html = str_replace($items,$replace,$email_templates->content);

            echo $html;
        }
    }

    public function postHomepage(Request $request) {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "app_version" => "required",
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
        $user_details = User::query()->where("id","=",$user_details->id)->first();
        if ($user_details == Null){
            return response()->json([
                'status' => 5,
//                'message' => "User not found!",
                'message' => __('user_messages.5'),
                "message_code" => 5,
            ]);
        }
        $user_details->ip_address = $request->header('select-ip-address')!= Null ? $request->header('select-ip-address') : Null;
        $user_details->time_zone = $request->header('select-time-zone')!= Null ? $request->header('select-time-zone') : Null;
        $user_details->app_version = $request->get("app_version");
        $user_details->save();

        $service_category_icon_url = url('/assets/images/service-category/');
        $homepage_slider_url = url('/assets/images/home-banner/');

        //$lang_prefix = $this->adminClass->get_langugae_fields($user_details->language);
        $language = $user_details->language;
        if($language !="en" && $language != "" && $language != "Null" ){
            $lang_prefix = $language."_";
        }else{
            $lang_prefix ="";
        }

//        $services = ServiceCategory::query()->select('service_category.id as service_category_id',
//            'service_category.'.$lang_prefix . 'name as service_category_name',
//            DB::raw("(CASE WHEN service_category.icon_name != '' THEN (concat('$service_category_icon_url','/',service_category.icon_name,'?v=0.3')) ELSE '' END) as service_category_icon"))
//            ->where('service_category.status', 1)->orderBy('service_category.display_order', 'asc')->get();

        $services=OtherServiceCategory::query()->select('service_category.id as service_category_id',
            'service_category.'.$lang_prefix . 'name as service_category_name',
            DB::raw("(CASE WHEN service_category.icon_name != '' THEN (concat('$service_category_icon_url','/',service_category.icon_name,'?v=0.3')) ELSE '' END) as service_category_icon"))
            ->join('service_category','service_category.id','=','other_service_sub_category.service_cat_id')
            ->where('service_category.status', 1)
            ->where('other_service_sub_category.status', 1)
            ->groupBy('other_service_sub_category.service_cat_id')
            ->orderBy('service_category.display_order', 'asc')->get();

//        return $services->count();
//        $homepage_slider = HomePageBanner::query()->select('home_page_banner.service_id as service_category_id',
//            'service_category.'.$lang_prefix . 'name as service_category_name',
//            DB::raw("(CASE WHEN home_page_banner.banner_image != '' THEN (concat('$homepage_slider_url','/',home_page_banner.banner_image)) ELSE '' END) as homepage_slider_image"),
//            DB::raw("(CASE WHEN service_category.icon_name != '' THEN (concat('$service_category_icon_url','/',service_category.icon_name,'?v=0.3')) ELSE '' END) as service_category_icon"))
//            ->leftJoin('service_category','service_category.id','=','home_page_banner.service_id')
//            ->where('home_page_banner.type','=',0)
//            ->where('home_page_banner.status', 1)->get();

//        $provider_image_url =  url('/assets/images/profile-images/provider/');
//        $get_top_rated_providers = OtherServiceProviderDetails::query()->select('other_service_provider_details.provider_id as provider_id',
//            'providers.first_name as provider_name','other_service_provider_details.rating as provider_rating',
//            DB::raw("(CASE WHEN providers.avatar != '' THEN (concat('$provider_image_url','/',providers.avatar)) ELSE '' END) as provoider_image"),
//            'service_category.'.$lang_prefix . 'name as service_category_name',)
//            ->join('provider_services','provider_services.provider_id','other_service_provider_details.provider_id')
//            ->join('providers','providers.id','=','other_service_provider_details.provider_id')
//            ->join('service_category','service_category.id','=','provider_services.service_cat_id');
//        $get_top_rated_providers_total =    $get_top_rated_providers->count();
//        $get_top_rated_providers_list  =    $get_top_rated_providers->limit($this->top_rated_display_limit)->get()->toArray();
//        $top_rated_array = array(
//            'view_all_bnt' => ($get_top_rated_providers_total > $this->top_rated_display_limit)?1:0,
//            'top_rated_provider_lists' => $get_top_rated_providers_list
//        );


        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "total_ongoing_order" => 0,
            "total_unread_message" => 0,
            "services" => $services,
//            "home_slider" => $homepage_slider,
        ]);
    }

    public function postCustomerGetRunningService(Request $request) {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required"
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

        $get_running_service = UserRunningRide::query()->where('user_id', $user_details->id)->whereIN('service_cat_id', $this->transport_service_id_array)->first();
        if ($get_running_service != Null) {
            $ride_details = TransportRideBook::query()->where('id', $get_running_service->booking_id)->first();
            if ($ride_details == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => 'service not running!',
                    'message' => __('user_messages.1'),
                    "message_code" => 1,
                ]);
            }
            if ($ride_details->status == 9) {
                $ride_completed_status = 1;
                $ride_cancelled_status = 0;
            } elseif ($ride_details->status == 4) {
                $ride_completed_status = 0;
                $ride_cancelled_status = 1;
            } else {
                $ride_completed_status = 0;
                $ride_cancelled_status = 0;
            }
            return response()->json([
                "status" => 1,
//                "message" => 'success!',
                'message' => __('user_messages.1'),
                "message_code" => 1,
                "order_id" => $ride_details->id,
                "order_status" => $ride_details->status,
                "way_point_status" => $ride_details->way_point_status,
                "service_cat_id" => $ride_details->service_cat_id,
                "ride_completed_status" => $ride_completed_status,
                "ride_cancelled_status" => $ride_cancelled_status,
            ]);
        }
        return response()->json([
            "status" => 0,
            "message" => 'service not running!',
            "message_code" => 1,
        ]);
    }

    public function postCustomerAddCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "holder_name" => "nullable",
            "card_number" => "required",
            "month" => "required|numeric",
            "year" => "required|numeric",
            "cvv" => "required|numeric",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }

        return $this->userClassapi->addCardManage($this->user_type, $request->get('user_id'), $request->all());
    }

    public function postCustomerRemoveCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "card_id" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_id = $request->get("user_id");
        $card_id = $request->get("card_id");
        $user_check = $this->userClassapi->checkUserAllow($user_id, $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        return $this->userClassapi->deleteCardManage($this->user_type, $user_id, $card_id);
    }

    public function postCustomerCardList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        return $this->userClassapi->manageCardList($this->user_type, $request->get('user_id'));
    }

    public function postCustomerAddWalletBalance(Request $request) {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "amount" => "required|numeric",
            //"card_id" => "required",
            "payment_method_type" => "nullable|in:1,0",
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
        return $this->userClassapi->addWalletBalance($this->user_type, $request->get('user_id'), $user_details->first_name, $user_details->currency,  $request->all());
    }

    public function postCustomerWalletTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $user_language = $user_check->language == null ? 'en' : $user_check->language;
        return $this->userClassapi->getWalletTransactionList($this->user_type, $request->get('user_id'), $user_check->currency, $user_language);
    }

    public function postCustomerGetWalletBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }

        return $this->userClassapi->getWalletBalance($this->user_type, $request->get('user_id'), $user_check->currency);
    }

    public function postCustomerSearchWalletTransferUserList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "search" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        return $this->userClassapi->searchWalletTransferUserList($this->user_type, $request->get('user_id'), $request->get("search"));
    }

    public function postCustomerWalletToWalletTransfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "amount" => "required|numeric",
            "transfer_id" => "required|numeric",
            "wallet_provider_type" => "required|in:".$this->user_type.",".$this->store_type.",".$this->driver_type.",".$this->provider_type,
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        return $this->userClassapi->walletToWalletTransfer($this->user_type, $request->get('user_id'), $user_check->first_name, $user_check->currency, $request->all());
    }

    public function postCustomerAddAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }

        return $this->userClassapi->userAddressManage($request, true, false, false);
    }

    public function postCustomerEditAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }

        return $this->userClassapi->userAddressManage($request, false, true, false);
    }

    public function postCustomerDeleteAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }

        return $this->userClassapi->userAddressManage($request, false, false, true);
    }

    public function postCustomerAddressList(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $addresses = UserAddress::select('id', 'address_type', 'address', 'lat_long', 'flat_no', 'landmark')->where('user_id', $request->get('user_id'))->where('status', 1)->get();
        if (!$addresses->isEmpty()) {
            foreach ($addresses as $address) {
                $lat_long = explode(',', $address->lat_long);
                $lat = $lat_long[0];
                $long = $lat_long[1];
                $address_list[] = [
                    'address_id' => $address->id,
                    'type' => $address->address_type,
                    'address' => $address->address,
                    'lat' => $lat,
                    'long' => $long,
                    'flat_no' => $address->flat_no,
                    'landmark' => $address->landmark
                ];
            }
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                'message' => __('user_messages.1'),
                "message_code" => 1,
                "address_list" => $address_list,
                "address_limit" => 5
            ]);
        } else {
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                'message' => __('user_messages.1'),
                "message_code" => 1,
                "address_list" => [],
                "address_limit" => 5
            ]);
//            return response()->json([
//                "status" => 0,
////                "message" => "address not found!",
//                'message' => __('user_messages.9'),
//                "message_code" => 9,
//            ]);
        }
    }

    public function postStorePromocodeList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|integer",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric"
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

        $current_date = date('Y-m-d H:i:s');
        $service_cat_id = $request->get('service_category_id');
        $promocode_list = PromocodeDetails::query()->where('service_cat_id', $service_cat_id)
            ->where('status', 1)
            ->whereDate('expiry_date_time','>=',$current_date)
            ->get();
        if ($promocode_list == Null) {
            return response()->json([
                "status" => 1,
//                "message" => 'success!',
                'message' => __('user_messages.1'),
                "message_code" => 1,
                "promocode_list" => []
            ]);
        } else {
            $promo_list = [];
            foreach ($promocode_list as $promocode) {
                try {
                    $get_user_used = UsedPromocodeDetails::query()->where('user_id', $user_details->id)->where('promocode_id', $promocode->id)
                        ->whereIn("status",[0,1])
                        ->count();
                } catch (\Exception $e) {
                    $get_user_used = 0;
                }
                if ($promocode->coupon_limit != 0) {
                    if ($promocode->coupon_limit > $promocode->total_usage) {
                        if ($promocode->usage_limit > $get_user_used) {
                            $promo_list[] = [
                                'promocode_id' => $promocode->id,
                                'promocode_name' => $promocode->promo_code,
                                'discount_amount' => $promocode->discount_amount,
                                'discount_type' => $promocode->discount_type,
                                'min_order_amount' => $promocode->min_order_amount != Null ? $promocode->min_order_amount : 0,
                                'max_discount_amount' => $promocode->max_discount_amount != Null ? $promocode->max_discount_amount : 0,
                                'promocode_description' => $promocode->description != Null ? $promocode->description : '',
                            ];
                        }
                    }
                } else {
                    if ($promocode->usage_limit > $get_user_used) {
                        $promo_list[] = [
                            'promocode_id' => $promocode->id,
                            'promocode_name' => $promocode->promo_code,
                            'discount_amount' => $promocode->discount_amount,
                            'discount_type' => $promocode->discount_type,
                            'min_order_amount' => $promocode->min_order_amount != Null ? $promocode->min_order_amount : 0,
                            'promocode_description' => $promocode->description != Null ? $promocode->description : '',
                        ];
                    }
                }
            }
            return response()->json([
                "status" => 1,
//                "message" => 'success!',
                'message' => __('user_messages.1'),
                "message_code" => 1,
                "promocode_list" => $promo_list
            ]);
        }
    }

    public function postApplyPromocodeCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|integer",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric",
            "promo_code" => "required",
            "order_amount" => "required|numeric"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
                "min_order_amount" => 0,
            ]);
        }

        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $service_cat_id = $request->get('service_category_id');
        $get_promo_code = $request->get('promo_code');
        $order_amount = $request->get('order_amount');
        $get_promo_code_id = 0;
        if($get_promo_code != "" && $get_promo_code!= Null )
        {
            if (!is_numeric($get_promo_code)) {
                $get_promocode = PromocodeDetails::query()->where('promo_code', '=',$get_promo_code)->where('service_cat_id','=', $request->get('service_category_id'))->where('status','=','1')->first();
                $get_promo_code_id = ($get_promocode != Null) ? $get_promocode->id : 0;
            }

            if($get_promo_code_id > 0) {
//                $check_promocode = $this->userClassapi->checkPromoCodeValid($request->get('promocode_id'), $request->get('order_amount'), $user_details->id, $service_cat_id);
                list($promo_code_status,$promo_message_code,$promo_code_message,$min_order_amount,$promo_code,$promo_code_name) = $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $order_amount, $user_details->id,$service_cat_id);
                return response()->json([
                    "status" =>$promo_code_status,
//                    "message" => $promo_code_message,
                    'message' => $promo_code_message,
                    "message_code" => $promo_message_code,
                    "discount_amount" => $promo_code,
                    "total_pay" => round($order_amount - $promo_code, 2),
                    "promocode_id" => $get_promo_code_id,
                    "promocode_name" => strtoupper($promo_code_name),
                    "min_order_amount" => $min_order_amount,
                ]);
            }else{
                return response()->json([
                    "status" =>0,
//                    "message" => "Promocode is not exists!",
                    'message' => __('user_messages.232'),
                    "message_code" => 232,
                ]);
            }
        }else{
            return response()->json([
                "status" =>0,
//                "message" => "Promocode is not exists!",
                'message' => __('user_messages.232'),
                "message_code" => 232,
            ]);
        }


        /*$get_promocode = PromocodeDetails::query()->where('id', $get_promo_code_id)->where('status', 1)->first();
        if ($get_promocode == Null) {
            return response()->json([
                "status" => 0,
                "message" => "something went to wrong!",
                "message_code" => 9,
                "min_order_amount" => 0,
            ]);
        }
        if ($check_promocode == 0) {
            return response()->json([
                "status" => 0,
                "message" => "something went to wrong!",
                "message_code" => 9,
                "min_order_amount" => 0,
            ]);
        } elseif ($check_promocode == 1) {
            return response()->json([
                "status" => 0,
                "message" => "Order min amount must be " . $user_details->currency . $get_promocode->min_order_amount,
                "message_code" => 127,
                "min_order_amount" => round($get_promocode->min_order_amount, 2),
            ]);
        } else {
            return response()->json([
                "status" => 1,
                "message" => "success!",
                "message_code" => 1,
                "discount_amount" => $check_promocode,
                "total_pay" => round($request->get('order_amount') - $check_promocode, 2),
                "promocode_id" => $get_promocode->id,
                "promocode_name" => strtoupper($get_promocode->promo_code),
                "min_order_amount" => 0,
            ]);
        }*/
    }

    public function postCountryAndCurrencyList(Request $request)
    {
//        $country_list = WorldCountry::query()->select('id as country_id', 'country_name', 'country_code')->where('status', 1)->get();
        $country_list = LanguageLists::query()->select('id as country_id', 'language_name as country_name', 'language_code as country_code')->where('status', 1)->orderBy('id','asc')->get();
        $currency_list = WorldCurrency::query()->select('id as currency_id', 'currency_code as currency_name', 'symbol as currency_symbol')->where('status', 1)->get();
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "country_list" => $country_list,
            "currency_list" => $currency_list
        ]);
    }

    public function postCustomerCouponList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|integer",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric|in:3"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_check->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency != Null ? $user_currency->ratio : 1;
        $currency_code = $user_currency != Null ? $user_currency->currency_code : '';

        $coupon_image = url('/assets/images/coupon_images');
        $date_time = date('Y-m-d H:i:s');
        $coupon_list = CouponDetails::query()->select('coupon_details.id as coupon_id',
            'coupon_category.category_name',
            'coupon_details.coupon_name',
            DB::raw("(CASE WHEN coupon_details.coupon_image != '' THEN concat('$coupon_image','/',coupon_details.coupon_image) ELSE '' END) as coupon_image"),
            'coupon_details.coupon_brand_name',
            'coupon_details.available_coupon as avail_limit',
            'coupon_details.coupon_validity as coupon_validity',
            'coupon_details.purchased_coupon',
            DB::raw('ROUND(coupon_details.coupon_amount  * ' . $currency . ',2) As coupon_amount')
//            'coupon_details.coupon_amount'
        )
            ->join('coupon_category', 'coupon_category.id', '=', 'coupon_details.category_id')
            ->where('coupon_details.status', 1)
//            ->where('coupon_details.coupon_limit', '>', 'coupon_details.coupon_usage')
            ->where('coupon_details.available_coupon', '>=', 1)
            ->where('coupon_category.status', 1)
//            ->where('coupon_details.expiry_date', '>=', $date_time)
            ->orderBy('coupon_details.purchased_coupon','DESC')
            ->get()->groupBy('category_name');

        $coupon_ist_array = [];
        if (!$coupon_list->isEmpty()) {
            foreach ($coupon_list as $key => $list) {
                $coupon_ist_array[] = [
                    "category_name" => $key,
                    "coupons" => $list
                ];
            }
        }

        return response()->json([
            "status" => 1,
//            "message" => "success",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "coupon_list" => $coupon_ist_array
        ]);
    }

    public function postCustomerCouponDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|integer",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric|in:3",
            "coupon_id" => "required|numeric"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $coupon_id = $request->get('coupon_id');
        $user_id = $request->get('user_id');
        $user_check = $this->userClassapi->checkUserAllow($user_id, $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_check->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency != Null ? $user_currency->ratio : 1;
        $currency_code = $user_currency != Null ? $user_currency->currency_code : '';

        $coupon_image = url('/assets/images/coupon_images');
        $date_time = date('Y-m-d H:i:s');
        $coupon_details = CouponDetails::query()->select('id as coupon_id',
            'coupon_name',
            DB::raw("(CASE WHEN coupon_image != '' THEN concat('$coupon_image','/',coupon_image) ELSE '' END) as coupon_image"),
            'coupon_brand_name',
            DB::raw('ROUND(coupon_details.coupon_amount  * ' . $currency . ',2) As coupon_amount'),
            'coupon_code',
            'available_coupon as avail_limit',
            'coupon_validity as coupon_validity',
            'purchased_coupon',
            'link',
            'terms_condition',
            'how_to_redeem')
            ->where('id', $coupon_id)
//            ->where('coupon_details.expiry_date', '>=', $date_time)
            ->first();

        $coupon_code_lists = [];
        $coupon_code_lists_arr = CouponCodeDetail::query()->where('coupon_id',$coupon_id)
            ->where('user_id',$user_id)
//            ->where('status','=',0)
            ->get();
        $total_purchase_coupon = count($coupon_code_lists_arr);
        if($total_purchase_coupon > 0){
//            $coupon_code_lists = $coupon_code_lists_arr;
            foreach ($coupon_code_lists_arr as $key=>$singel_coupon){
                $coupon_code = $singel_coupon->coupon_code;
                if($singel_coupon->status == "1"){
                    $coupon_status = $this->notificationClass->statusLangConvert('USED',$user_check->language) . ": ".$singel_coupon->code_used_date;
                }else {
                    $curr_date = date("Y-m-d H:i:s");
                    if ($singel_coupon->code_active_date == "0000-00-00 00:00:00" || $singel_coupon->code_active_date == Null ) {
                        $coupon_status = $this->notificationClass->statusLangConvert("Not Purchase",$user_check->language);
                    }else if (strtotime($curr_date) > strtotime($singel_coupon->code_expire_date)) {
                        $coupon_status = $this->notificationClass->statusLangConvert("EXPIRED",$user_check->language);
                    } else {
                        $to_date = strtotime($curr_date);
                        $from_date = strtotime($singel_coupon->code_expire_date);
                        $day_diff = $from_date -  $to_date;
                        $total_days =  ceil($day_diff/(60*60*24));
                        $coupon_status =($total_days > 1)?$total_days. " " . $this->notificationClass->statusLangConvert("DAYS REMAIN",$user_check->language) : $total_days." ".$this->notificationClass->statusLangConvert("DAYS REMAIN",$user_check->language);
                    }
                }
                $coupon_code_lists[] = ['coupon_code'=>$coupon_code,'coupon_status' => $coupon_status];
            }
        }

        return response()->json([
            "status" => 1,
//            "message" => "success",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "coupon_details" => $coupon_details,
            "coupon_code_lists"=>$coupon_code_lists,
            'total_purchase_coupon'=>$total_purchase_coupon,
            'total_amount'=>( $total_purchase_coupon * $coupon_details->coupon_amount )
        ]);
    }

    public function postCustomerPurchaseCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|integer",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric|in:3",
            "coupon_id" => "required",
            "coupon_qtys" => "required",
            "payment_type" => "required|in:1,2,3",
            //"card_id" => "nullable",
            //"paypal_nonce" => "nullable",
            //"payment_method_type" => "nullable|in:1,0",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $coupon_ids = explode(",", rtrim($request->get('coupon_id'),","));
        $coupon_qtys = explode(",", rtrim($request->get('coupon_qtys'),","));
        if(count($coupon_ids) != count($coupon_qtys)){
            return response()->json([
                "status" => 0,
//                "message" => "Please select proper coupon code quantity",
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $convert_to_currency = WorldCurrency::query()->where('symbol', "$")->first();
        if ($convert_to_currency == Null) {
            return response()->json([
                'status' => 0,
//                'message' => "Something went to wrong!",
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_check->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency != Null ? $user_currency->ratio : 1;
        $currency_code = $user_currency != Null ? $user_currency->currency_code : '';


        $purchased_ids = "";
        $purchased_qtys = "";
        $purchased_amounts = 0;
        $unavailable_product_coupons="";
        foreach ($coupon_ids as $key=>$coupon_id) {
            $coupon_total_qty = $coupon_qtys[$key];
            $coupon_details = CouponDetails::query()->where('id', $coupon_id)
                ->where('available_coupon','>=',$coupon_total_qty)
                ->first();
            if ($coupon_details != Null) {
                // if coupon code available
                //$purchased_ids .= $coupon_id.",";
                //$purchased_qtys .= $coupon_total_qty.",";
                $purchased_amounts = $purchased_amounts + ($coupon_details->coupon_amount * $coupon_total_qty );
            }else{
                // coupon not available
                $unavailable_product_coupons .= $coupon_id.",";
            }
        }

        if($unavailable_product_coupons != ""){
            return response()->json([
                "status" => 1,
//                "message" => "coupon code not available for specific product",
                'message' => __('user_messages.9'),
                "message_code" => 9,
                "unavailable_product_coupons" => rtrim($unavailable_product_coupons,","),
            ]);
        }


        $date_time = date('Y-m-d H:i:s');
        $user_id = $request->get('user_id');
        $service_cat_id = $request->get('service_cat_id');
        $amount = $purchased_amounts;


        if($request->get('payment_type') == 2)
        {
            $validator = Validator::make($request->all(), [
                "card_id" => "required",
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
//                    "message" => "card details not found",
                    'message' => __('user_messages.120'),
                    "message_code" => 120,
                ]);
            }

            $purchase_cop_id_arr = explode(",",$request->get('coupon_id'));
            $purchase_cop_qty_arr = explode(",",$request->get('coupon_qtys'));

            foreach ($purchase_cop_id_arr as $key=>$coupon_id) {
                $coupon_details = CouponDetails::query()->where('id', $coupon_id)->first();
                $coupon_validity =($coupon_details->coupon_validity > 0)?$coupon_details->coupon_validity:1;
                if ($coupon_details != Null) {
                    //if ($coupon_details->coupon_limit > $coupon_details->coupon_usage) {
                    $purchse_coupon_code_qty = $purchase_cop_qty_arr[$key];

                    //code for update couden code table assign user to code
                    for($i=1;$i<=$purchse_coupon_code_qty;$i++){
                        $get_coupon_code = CouponCodeDetail::query()->where('coupon_id',$coupon_details->id)
                            ->where('user_id','=',0)->first();
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
                    $user_purchase_coupon->user_id = $user_check->id;
                    $user_purchase_coupon->coupon_id = $coupon_details->id;
                    $user_purchase_coupon->coupon_name = $coupon_details->coupon_name;
                    $user_purchase_coupon->coupon_brand_name = $coupon_details->coupon_brand_name;
                    $user_purchase_coupon->coupon_amount = $coupon_details->coupon_amount;
                    $user_purchase_coupon->coupon_purchase_qtys = $purchse_coupon_code_qty;
                    //$user_purchase_coupon->coupon_code = $coupon_details->coupon_code;
                    $user_purchase_coupon->coupon_code = "";
                    $user_purchase_coupon->purchase_date = date('Y-m-d H:i:s');
                    $user_purchase_coupon->expiry_date = date('Y-m-d H:i:s',strtotime("+".$coupon_validity." day"));
                    $user_purchase_coupon->status = 1;
                    $user_purchase_coupon->save();
                    //$coupon_details->coupon_usage = $coupon_details->coupon_usage + 1;
                    $coupon_details->available_coupon = $coupon_details->available_coupon - $purchse_coupon_code_qty;
                    $coupon_details->purchased_coupon = $coupon_details->purchased_coupon + $purchse_coupon_code_qty;
                    $coupon_details->save();
                    //}
                }
            }

           //$user_purchase_coupon_details_ids = [];
           //$coupon_details_usage_ids = [];
           //$total_amount = 0;
           //$order_gen_id = $this->userClassapi->generateBookingNo();
           //$purchased_ids = "";
           //foreach ($coupon_ids as $coupon_id) {
           //    $coupon_details = CouponDetails::query()->where('id', $coupon_id)->where('expiry_date', '>=', $date_time)->first();
           //    if ($coupon_details != Null) {
           //        if ($coupon_details->coupon_limit > $coupon_details->coupon_usage) {
           //            $user_purchase_coupon = new UserPurchaseCouponDetails();
           //            $user_purchase_coupon->user_id = $user_check->id;
           //            $user_purchase_coupon->trans_id = $order_gen_id;
           //            $user_purchase_coupon->coupon_id = $coupon_details->id;
           //            $user_purchase_coupon->coupon_name = $coupon_details->coupon_name;
           //            $user_purchase_coupon->coupon_brand_name = $coupon_details->coupon_brand_name;
           //            $user_purchase_coupon->coupon_amount = $coupon_details->coupon_amount;
           //            $user_purchase_coupon->coupon_code = $coupon_details->coupon_code;
           //            $user_purchase_coupon->purchase_date = date('Y-m-d H:i:s');
           //            $user_purchase_coupon->expiry_date = $coupon_details->expiry_date;
           //            $user_purchase_coupon->payment_status = 1;
           //            $user_purchase_coupon->status = 1;
           //            $user_purchase_coupon->save();
           //            $coupon_details->coupon_usage = $coupon_details->coupon_usage + 1;
           //            $coupon_details->save();
           //            array_push($user_purchase_coupon_details_ids, $user_purchase_coupon->id);
           //            array_push($coupon_details_usage_ids, $coupon_details->id);
           //            $purchased_ids .= $coupon_id . ",";
           //            $total_amount = round($total_amount + $coupon_details->coupon_amount, 2);
           //        }
           //    }
           //}
        }else{
            $wallet_transaction = UserWalletTransaction::query()->select('remaining_balance')
                ->where('user_id',$request->get('user_id'))
                ->orderBy('id','desc')->limit(1)->first();
            if($wallet_transaction != Null && $wallet_transaction->remaining_balance != Null){
                $balance = $wallet_transaction->remaining_balance;
            } else{
                $balance = 0;
            }
            if($amount > $balance){
                return response()->json([
                    'status' => 0,
//                    'message' => 'You can\'t pay order amount through Wallet because your wallet balance is insufficient.',
                    'message' => __('user_messages.110'),
                    'message_code' => 110,
                ]);
            }
            if($amount > 0){
                $add_balance = new UserWalletTransaction();
                $add_balance->user_id = $request->get('user_id');
                $add_balance->transaction_type = 2;
                $add_balance->amount = $amount;
                $add_balance->subject = "Purchased coupons";
                $add_balance->subject_code = 9;
                $add_balance->order_no = '';
                $add_balance->remaining_balance = round($balance - $amount, 2);
                $add_balance->save();
            }

            $purchase_cop_id_arr = explode(",",$request->coupon_id);
            $purchase_cop_qty_arr = explode(",",$request->coupon_qtys);

            foreach ($purchase_cop_id_arr as $key=>$coupon_id) {
                $coupon_details = CouponDetails::query()->where('id', $coupon_id)->first();
                $coupon_validity =($coupon_details->coupon_validity > 0)?$coupon_details->coupon_validity:1;
                if ($coupon_details != Null) {
                    //if ($coupon_details->coupon_limit > $coupon_details->coupon_usage) {
                    $purchse_coupon_code_qty = $purchase_cop_qty_arr[$key];

                    //code for update couden code table assign user to code
                    for($i=1;$i<=$purchse_coupon_code_qty;$i++){
                        $get_coupon_code = CouponCodeDetail::query()->where('coupon_id',$coupon_details->id)
                            ->where('user_id','=',0)->first();
                        $get_coupon_code->user_id = $user_check->id;
                        $get_coupon_code->code_active_date = date('Y-m-d H:i:s');
                        $get_coupon_code->code_expire_date = date('Y-m-d H:i:s',strtotime("+".$coupon_validity." day"));
                        $get_coupon_code->save();
                    }
                    //update table entry to remain and available on purchse coun code entry
                    $user_purchase_coupon = new UserPurchaseCouponDetails();
                    $user_purchase_coupon->user_id = $user_check->id;
                    $user_purchase_coupon->coupon_id = $coupon_details->id;
                    $user_purchase_coupon->coupon_name = $coupon_details->coupon_name;
                    $user_purchase_coupon->coupon_brand_name = $coupon_details->coupon_brand_name;
                    $user_purchase_coupon->coupon_amount = $coupon_details->coupon_amount;
                    $user_purchase_coupon->coupon_purchase_qtys = $purchse_coupon_code_qty;
                    //$user_purchase_coupon->coupon_code = $coupon_details->coupon_code;
                    $user_purchase_coupon->coupon_code = "";
                    $user_purchase_coupon->purchase_date = date('Y-m-d H:i:s');
                    $user_purchase_coupon->expiry_date = date('Y-m-d H:i:s',strtotime("+".$coupon_validity." day"));
                    $user_purchase_coupon->status = 1;
                    $user_purchase_coupon->save();
                    //$coupon_details->coupon_usage = $coupon_details->coupon_usage + 1;
                    $coupon_details->available_coupon = $coupon_details->available_coupon - $purchse_coupon_code_qty;
                    $coupon_details->purchased_coupon = $coupon_details->purchased_coupon + $purchse_coupon_code_qty;
                    $coupon_details->save();
                    //}
                }
            }
            //$user_purchase_coupon_details_ids = [];
            //$coupon_details_usage_ids = [];
            //$total_amount = 0;
            //$order_gen_id = $this->userClassapi->generateBookingNo();
            //$purchased_ids = "";
            //foreach ($coupon_ids as $coupon_id) {
            //    $coupon_details = CouponDetails::query()->where('id', $coupon_id)->where('expiry_date', '>=', $date_time)->first();
            //    if ($coupon_details != Null) {
            //        if ($coupon_details->coupon_limit > $coupon_details->coupon_usage) {
            //            $user_purchase_coupon = new UserPurchaseCouponDetails();
            //            $user_purchase_coupon->user_id = $user_check->id;
            //            $user_purchase_coupon->trans_id = $order_gen_id;
            //            $user_purchase_coupon->coupon_id = $coupon_details->id;
            //            $user_purchase_coupon->coupon_name = $coupon_details->coupon_name;
            //            $user_purchase_coupon->coupon_brand_name = $coupon_details->coupon_brand_name;
            //            $user_purchase_coupon->coupon_amount = $coupon_details->coupon_amount;
            //            $user_purchase_coupon->coupon_code = $coupon_details->coupon_code;
            //            $user_purchase_coupon->purchase_date = date('Y-m-d H:i:s');
            //            $user_purchase_coupon->expiry_date = $coupon_details->expiry_date;
            //            $user_purchase_coupon->payment_status = 1;
            //            $user_purchase_coupon->status = 1;
            //            $user_purchase_coupon->save();
            //            $coupon_details->coupon_usage = $coupon_details->coupon_usage + 1;
            //            $coupon_details->save();
            //            array_push($user_purchase_coupon_details_ids, $user_purchase_coupon->id);
            //            array_push($coupon_details_usage_ids, $coupon_details->id);
            //            $purchased_ids .= $coupon_id . ",";
            //            $total_amount = round($total_amount + $coupon_details->coupon_amount, 2);
            //        }
            //    }
            //}

        }
        return response()->json([
//            'message' => 'payment successfull done',
            'status' => 1,
            'message' => __('user_messages.225'),
            'message_code' => 225,
            //'resp_code' => $tempUserBooking->resp_code
        ]);

    }

    public function postCustomerPurchaseCouponList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|integer",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric|in:3"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $coupon_image = url('/assets/images/coupon_images');
        $purchase_coupon_list = UserPurchaseCouponDetails::query()->select('coupon_details.id as coupon_id',
            DB::raw("(CASE WHEN coupon_details.coupon_image != '' THEN concat('$coupon_image','/',coupon_details.coupon_image) ELSE '' END) as coupon_image"),
            'user_purchase_coupon_details.coupon_name as coupon_name',
            'user_purchase_coupon_details.coupon_amount as coupon_amount',
            'user_purchase_coupon_details.coupon_brand_name as coupon_brand_name')
            ->join('coupon_details', 'coupon_details.id', '=', 'user_purchase_coupon_details.coupon_id')
            ->where('user_purchase_coupon_details.user_id', $user_check->id)
            ->groupBy('user_purchase_coupon_details.coupon_id')
            ->get();

        return response()->json([
            "status" => 1,
//            "message" => "success",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "coupons" => $purchase_coupon_list
        ]);
    }

    //start code for testing payment
    public function postTempTransportRideBooking(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required",
            "service_cat_id" => "required|numeric",
            "amount" => "required|numeric",
            'coupon_ids' => "nullable"
        ]);

//        return $request->root();
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($user_check != null) {
            if ($decoded['status'] = json_decode($user_check) == false) {
                return $user_check;
            }
            //code for insert record into temp user booking

        } else {
            return response()->json([
                "status" => 0,
//                "message" => "User not found!",
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function postTempTransportRideBookingStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required",
            "service_cat_id" => "required|numeric",
            'temp_book_id' => "required|numeric",
            'coupon_ids' => "nullable"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($user_check != null) {
            if ($decoded['status'] = json_decode($user_check) == false) {
                return $user_check;
            }
            //code for insert record into temp user booking
            $user_id = $request->get('user_id');
            $service_cat_id = $request->get('service_cat_id');
            $temp_book_id = $request->get('temp_book_id');
//            $amount = $request->get('amount');

            $tempUserBooking = TempUserBooking::query()->where('id', $temp_book_id)
                ->where('user_id', $user_id)->where('service_cat_id', $service_cat_id)->first();
            if ($tempUserBooking != null) {

                if ($tempUserBooking->payment_status == 1 && $tempUserBooking->resp_code == 00) {
                    //code insert record in coupon code
                    if ($tempUserBooking->service_cat_id == "3") {   //code revove coupon from user purchas details
                        if ($tempUserBooking->coupon_ids != "" && $tempUserBooking->coupon_ids != "0" && $tempUserBooking->coupon_ids != null) {
                            $purchase_cop_id_arr = explode(",", $tempUserBooking->coupon_ids);
//
                            foreach ($purchase_cop_id_arr as $coupon_id) {
                                $coupon_details = CouponDetails::query()->where('id', $coupon_id)->first();
                                if ($coupon_details != Null) {
                                    if ($coupon_details->coupon_limit > $coupon_details->coupon_usage) {
                                        $user_purchase_coupon = new UserPurchaseCouponDetails();
                                        $user_purchase_coupon->user_id = $user_check->id;
                                        $user_purchase_coupon->coupon_id = $coupon_details->id;
                                        $user_purchase_coupon->coupon_name = $coupon_details->coupon_name;
                                        $user_purchase_coupon->coupon_brand_name = $coupon_details->coupon_brand_name;
                                        $user_purchase_coupon->coupon_amount = $coupon_details->coupon_amount;
                                        $user_purchase_coupon->coupon_code = $coupon_details->coupon_code;
                                        $user_purchase_coupon->purchase_date = date('Y-m-d H:i:s');
                                        $user_purchase_coupon->expiry_date = $coupon_details->expiry_date;
                                        $user_purchase_coupon->status = 1;
                                        $user_purchase_coupon->save();
                                        $coupon_details->coupon_usage = $coupon_details->coupon_usage + 1;
                                        $coupon_details->save();
                                    }
                                }
                            }
                        }
                    }

                    return response()->json([
//                        'message' => 'payment successfull done',
                        'message' => __('user_messages.225'),
                        'message_code' => 225,
                        'status' => 1,
                        'temp_book_status' => $tempUserBooking->payment_status,
                        'resp_code' => $tempUserBooking->resp_code
                    ]);
                } else if ($tempUserBooking->payment_status == 0 && $tempUserBooking->resp_code != "" && $tempUserBooking->resp_code != null) {

                    /* code for coupon code check
                    if($tempUserBooking->service_cat_id == "3")
                    {
                        if($tempUserBooking->coupon_ids !="" && $tempUserBooking->coupon_ids !="0" && $tempUserBooking->coupon_ids !=null)
                        {
                            $purchase_cop_id_arr = explode(",",$tempUserBooking->coupon_ids);
                            foreach ($purchase_cop_id_arr  as $single_id)
                            {
                                $user_purchase_coupon = UserPurchaseCouponDetails::query()->where('id',$single_id)->first();
                                if( $user_purchase_coupon !="")
                                {
                                    $coupon_details = CouponDetails::query()->where('id',$user_purchase_coupon->coupon_id)->first();
                                    //                                return $coupon_details;
                                    if($coupon_details !=null)
                                    {
                                        $coupon_details->coupon_usage = $coupon_details->coupon_usage - 1;
                                        $coupon_details->save();
                                    }
                                }
                                $user_purchase_coupon->delete();
                            }
                        }
                    }*/

                    return response()->json([
                        'message' => 'payment failed',
                        'status' => 1,
                        'temp_book_status' => $tempUserBooking->payment_status,
                        'resp_code' => $tempUserBooking->resp_code
                    ]);
                } else {

                    return response()->json([
                        'status' => 0,
                        'temp_book_status' => $tempUserBooking->payment_status,
                        'resp_code' => $tempUserBooking->resp_code
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'something went to wrong',
                    'temp_book_status' => isset($tempUserBooking->payment_status) ? $tempUserBooking->payment_status : "0",
                    'resp_code' => isset($tempUserBooking->resp_code) ? $tempUserBooking->resp_code : ""
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
                "message" => "User not found!",
                "message_code" => 9,
            ]);
        }
    }

    //end code for testing payment

    public function postSupportPages()
    {
        $page_settings = PageSettings::get();
        if (!$page_settings->isEmpty()) {
            $support_pages = [];
            foreach ($page_settings as $page_setting) {
                $support_pages[] = [
                    'id' => $page_setting->id,
                    'page_name' => $page_setting->name,
                    'page_title' => ucwords(strtolower(str_replace('-', " ", $page_setting->name))),
                    'description' => $page_setting->description != Null ? $page_setting->description : ''
                ];
            }
            return response()->json([
                "status" => 1,
                "message" => "success!",
                "message_code" => 1,
                "pages" => $support_pages
            ]);
        } else {
            return response()->json([
                "status" => 0,
                "message" => "data not found!",
                "message_code" => 0
            ]);
        }
    }

    public function postMyCheckoutSupportPages(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }

        $lang_prefix = $this->adminClass->get_langugae_fields($user_check->language);
        $name = $lang_prefix != 'en_' ? $lang_prefix.'name' : 'name';
        $description = $lang_prefix != 'en_' ? $lang_prefix.'description' : 'description';
        $page_settings = PageSettings::query()
            ->select('id',$name,$description)
            ->where('type', 1)->get();
        if (!$page_settings->isEmpty()) {
            $support_pages = [];
            foreach ($page_settings as $page_setting) {
                $support_pages[] = [
                    'id' => $page_setting->id,
                    'page_name' => $page_setting->$name,
                    'page_title' => $user_check->language == 'en' ? (str_replace('-', " ", $page_setting->name)) : $page_setting->$name,
                    'description' => $page_setting->$description != Null ? $page_setting->$description : ''
                ];
            }
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "pages" => $support_pages
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "data not found!",
                "message" => __('user_messages.0'),
                "message_code" => 0
            ]);
        }
    }

    public function postMyDriverSupportPages(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                //"driver_service_id" => "required|numeric",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $driver_id = $request->get('driver_id');
        /*$driver_check = $this->driverClassApi->checkDriverRegisterAllow($driver_id, $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_check) == false) {
            return $driver_check;
        }*/
        $driver_check = Provider::query()->where('id',$driver_id)->first();
        $lang_prefix = $this->adminClass->get_langugae_fields($driver_check->language);
        $name = $lang_prefix != 'en_' ? $lang_prefix.'name' : 'name';
        $description = $lang_prefix != 'en_' ? $lang_prefix.'description' : 'description';
        $page_settings = PageSettings::query()
            ->select('id',$name,$description)
            ->where('type', 3)->get();
        if (!$page_settings->isEmpty()) {
            $support_pages = [];
            foreach ($page_settings as $page_setting) {
                $support_pages[] = [
                    'id' => $page_setting->id,
                    'page_name' => $page_setting->$name,
                    'page_title' => $driver_check->language == 'en' ? (str_replace('-', " ", $page_setting->name)) : $page_setting->$name,
                    'description' => $page_setting->$description != Null ? $page_setting->$description : ''
                ];
            }
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('driver_messages.1'),
                "message_code" => 1,
                "pages" => $support_pages
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "data not found!",
                "message" => __('driver_messages.0'),
                "message_code" => 0
            ]);
        }
    }

    public function postMyStoreSupportPages(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "store_id" => "required|integer",
                "access_token" => "required|numeric",
                //"store_service_id" => "required|integer",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        /*$store_check = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_check) == false) {
            return $store_check;
        }*/
        $store_check = Provider::query()->where('id',$request->get('store_id'))->first();
        $lang_prefix = $this->adminClass->get_langugae_fields($store_check->language);
        $name = $lang_prefix != 'en_' ? $lang_prefix.'name' : 'name';
        $description = $lang_prefix != 'en_' ? $lang_prefix.'description' : 'description';
        $page_settings = PageSettings::query()
            ->select('id',$name,$description)
            ->where('type', 4)->get();
        if (!$page_settings->isEmpty()) {
            $support_pages = [];
            foreach ($page_settings as $page_setting) {
                $support_pages[] = [
                    'id' => $page_setting->id,
                    'page_name' => $page_setting->$name,
                    'page_title' => $store_check->language == 'en' ? (str_replace('-', " ", $page_setting->name)) : $page_setting->$name,
                    'description' => $page_setting->$description != Null ? $page_setting->$description : ''
                ];
            }
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" =>   __('store_messages.1'),
                "message_code" => 1,
                "pages" => $support_pages
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "data not found!",
                "message" =>   __('store_messages.0'),
                "message_code" => 0
            ]);
        }
    }

    public function postMyServiceSupportPages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "provider_id" => "nullable|integer",
            "access_token" => "nullable|numeric",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        if($request->get('provider_id') != Null) {
            $provider_details = $this->onDemandClassApi->providerRegisterAllow($request->get('provider_id'), $request->get('access_token'));
            if ($decoded['status'] = json_decode($provider_details) == false) {
                return $provider_details;
            }
            $lang_prefix = $this->adminClass->get_langugae_fields($provider_details->language);
        } else{
            $lang_prefix = 'en_';
        }
        $name = $lang_prefix != 'en_' ? $lang_prefix.'name' : 'name';
        $description = $lang_prefix != 'en_' ? $lang_prefix.'description' : 'description';
        $page_settings = PageSettings::query()
            ->select('id',$name,$description)
            ->where('type', 2)->get();
        if (!$page_settings->isEmpty()) {
            $support_pages = [];
            foreach ($page_settings as $page_setting) {
                $support_pages[] = [
                    'id' => $page_setting->id,
                    'page_name' => $page_setting->$name,
                    'page_title' => $lang_prefix == 'en_' ? (str_replace('-', " ", $page_setting->name)) : $page_setting->$name,
                    'description' => $page_setting->$description != Null ? $page_setting->$description : ''
                ];
            }
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('provider_messages.1'),
                "message_code" => 1,
                "pages" => $support_pages
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "data not found!",
                "message" => __('provider_messages.0'),
                "message_code" => 0
            ]);
        }
    }

    public function postMyShopperSupportPages()
    {
        $page_settings = PageSettings::query()->where('type', 5)->get();
        if (!$page_settings->isEmpty()) {
            $support_pages = [];
            foreach ($page_settings as $page_setting) {
                $support_pages[] = [
                    'id' => $page_setting->id,
                    'page_name' => $page_setting->name,
                    'page_title' => (str_replace('-', " ", $page_setting->name)),
                    'description' => $page_setting->description != Null ? $page_setting->description : ''
                ];
            }
            return response()->json([
                "status" => 1,
                "message" => "success!",
                "message_code" => 1,
                "pages" => $support_pages
            ]);
        } else {
            return response()->json([
                "status" => 0,
                "message" => "data not found!",
                "message_code" => 0
            ]);
        }
    }


    public function postFirebaseSecurityRules(Request $request)
    {
        $general_settings = GeneralSettings::select('map_key')->first();
        if ($general_settings == Null || $general_settings->map_key == Null) {
            return "";
        }

        $server_key = "AIzaSyC17S8daiq4q8ySSQGvlvj3adp97tyiAqU";
        $url = $request->get('url');
        if ($url == Null) {
            return "";
        }
        //$fcmUrl = $url . $server_key . "&components=country:ph";
        $fcmUrl = $url . $server_key;
        $final_url = str_replace(' ', '%20', $fcmUrl);

        //$url2 = parse_url($fcmUrl);
        //\Log::info("url");
        //\Log::info($url2);
        //$scheme = $url2["scheme"];
        //$host = $url2["host"];
        //$path = $url2["path"];
        //$url3 = str_replace(' ', '%20', $url2["query"]);
        //$query1 = $url3;
        //$final_url= $scheme."://".$host.$path."?".$query1;

        $fcmData = [];
        $headers = [
//            'Authorization: key=' . $server_key,
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $final_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmData));
        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result, true);


        return $response;
    }

    public function getHomePageBanner(Request $request){
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
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
        $banner = HomePageBanner::query()->where('type','=',1)->where('status',1)->get();
        if($banner == Null){
            return response()->json([
                'status' => 0,
//                'message' => 'No banner found',
                'message' => __('user_messages.0'),
                'message_code' => 0
            ]);
        }
        $banner_data = [];
        foreach ($banner as $item) {
            //$service_name = $user_details->language != NUll ? $user_details->language.'_name' : 'en_name';
            //$service_name = $user_details->language != NUll ? $this->adminClass->get_langugae_fields($user_details->language) . 'name' : 'en_name';
            $language = $user_details->language;
            if($language !="en" && $language != "" && $language != "Null" ){
                $service_name = $language."_name";
                $title_name = $language."_name";
                $title_desc = $language."_description";
            }else{
                $service_name ="name";
                $title_name = "name";
                $title_desc = "description";
            }
            $service_icon = ServiceCategory::query()->select('icon_name',$service_name)->where('id',$item->service_id)->first();
            $banner_data[] = [
                "service_category_id" => $item->service_id,
                "service_category_name" => $service_icon->$service_name,
                "service_category_icon" => isset($service_icon) ? asset('assets/images/service-category/'.$service_icon->icon_name) : '',
                "service_category_banner_img" => asset('assets/images/home-banner/'.$item->banner_image),
                "title" =>isset($item->$title_name)?$item->$title_name:$item->name,
                "description" => isset($item->$title_desc)?$item->$title_desc:$item->description,
            ];
        }
        return response()->json([
            'status' => 1,
//            'message' => 'success!',
            'message' => __('user_messages.1'),
            'message_code' => 1,
            'banner_data' =>$banner_data,
        ]);
    }

    public function postCustomerReviewList(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
                "store_id" => "required|numeric"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        try {
            $store_ratings = StoreRatings::select('store_rating.id', 'store_rating.rating', 'store_rating.comment', 'users.first_name')
                ->join('users', 'users.id', '=', 'store_rating.user_id')
                ->where('store_rating.store_id', $request->get('store_id'))
                ->orderBy('store_rating.id', 'desc')
                ->get();
            if ($store_ratings->isEmpty()) {
                $store_ratings = [];
            }
        } catch (Exception $exception) {
            return response()->json([
                "status" => 0,
//                "message" => 'No reviews yet!',
                'message' => __('user_messages.96'),
                "message_code" => 96,
            ]);
        }
        return response()->json([
            "status" => 1,
            "message" => "success!",
            'message' => __('user_messages.1'),
            "store_rating_list" => $store_ratings,
            "message_code" => 1,
        ]);
    }

    public function postFacebookDriverDataDeletion(Request $request){
        $reference = Str::random(10);
        $domain_url = route('front');
        $status_url = $domain_url.'/deletion/'.$reference;
        $confirmation_code = $reference;
        try{
            $signed_request = $request->get('signed_request');
            $data = $this->userClassapi->parse_signed_request($signed_request,'driver');
            $user_id = $data['user_id'];

            $provider = Provider::query()->where('login_type','facebook')->where('login_id',$user_id)->first();

            if($provider != NUll){
                $provider->login_id = $reference;
                $provider->email = Null;
                $provider->contact_number = Null;
                $provider->access_token = Null;
                $provider->device_token = Null;
                $provider->save();
            }

            $data = [
                'url' => $status_url,
                'confirmation_code' => $confirmation_code
            ];
        } catch(\Exception $e){
            $data = [
                'url' => $status_url,
                'confirmation_code' => $confirmation_code
            ];
        }

        return response()->json($data);
    }

    public function postFacebookProviderDataDeletion(Request $request){
        $reference = Str::random(10);
        $domain_url = route('front');
        $status_url = $domain_url.'/deletion/'.$reference;
        $confirmation_code = $reference;
        try{
            $signed_request = $request->get('signed_request');
            $data = $this->userClassapi->parse_signed_request($signed_request,'provider');
            $user_id = $data['user_id'];

            $provider = Provider::query()->where('login_type','facebook')->where('login_id',$user_id)->first();

            if($provider != NUll){
                $provider->login_id = $reference;
                $provider->email = Null;
                $provider->contact_number = Null;
                $provider->access_token = Null;
                $provider->device_token = Null;
                $provider->save();
            }

            $data = [
                'url' => $status_url,
                'confirmation_code' => $confirmation_code
            ];
        } catch(\Exception $e){
            $data = [
                'url' => $status_url,
                'confirmation_code' => $confirmation_code
            ];
        }

        return response()->json($data);
    }

    public function postFacebookUserDataDeletion(Request $request){
        $reference = Str::random(10);
//        $status_url = 'https://fox-jek.startuptrinity.com/deletion/'.$reference;
        $domain_url = route('front');
        $status_url = $domain_url.'/deletion/'.$reference;
        $confirmation_code = $reference;
        try{
            $signed_request = $request->get('signed_request');
            $data = $this->userClassapi->parse_signed_request($signed_request,'user');
            $user_id = $data['user_id'];

            $user = User::query()->where('login_type','facebook')->where('login_id',$user_id)->first();

            if($user != Null){
                $user->login_id = $reference;
                $user->email = Null;
                $user->contact_number = Null;
                $user->access_token = Null;
                $user->device_token = Null;
                $user->save();
            }

            $data = [
                'url' => $status_url,
                'confirmation_code' => $confirmation_code
            ];
        } catch(\Exception $e){
            $data = [
                'url' => $status_url,
                'confirmation_code' => $confirmation_code
            ];
        }

        return response()->json($data);
    }

    //api for get feature store data
    public function getHomePageFeatureStore(Request $request){
        $validator = Validator::make($request->all(), [
            "user_id" => "nullable|numeric",
            "access_token" => "nullable|numeric",
            "lat" => "required",
            "long" => "required",
            "service_category_id"=>"required|numeric"
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
        $address_lat = $request->get('lat');
        $address_long = $request->get('long');
        $service_category_id = $request->get('service_category_id');

        $language = 'en'; // set default en language
        $user_currency = Null;

        if ($user_id != Null && $access_token != Null) {
            $user_details = $this->userClassapi->checkUserAllow($user_id, $access_token);
            if ($decoded['status'] = json_decode($user_details) == false) {
                return $user_details;
            }
            $language = $user_details->language;
            $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        }

        $store_image_path = url('/assets/images/store-images/');
        $service_category_icon_url = url('/assets/images/service-category/');

        if($language !="en" && $language != "" && $language != "Null" ){
            $lang_prefix = $language."_";
        }else{
            $lang_prefix ="";
        }

        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;
        $currency_symbol = $user_currency->symbol;

        $feature_const = config('global.lang_constant.FEATURED.'.$lang_prefix.'value') ." ";
        $get = config('global.lang_constant.GET.'.$lang_prefix.'value') ." ";
        $off_on_all_orders = " ".config('global.lang_constant.OFF_ON_ALL_ORDERS.'.$lang_prefix.'value')." " ;
        $off_on_orders_above = " ".config('global.lang_constant.OFF_ON_ORDERS_ABOVE.'.$lang_prefix.'value')." " ;

        $home_page_feature_all_store_lists['all_feature_store'] = [];
        $home_page_feature_store_lists = [];
        $home_page_feature_category = HomepageFeatureStore::query()->select('service_category.id as service_category_id', 'service_category.' . $lang_prefix . 'name as service_category_name',
            DB::raw("(CASE WHEN service_category.icon_name != '' THEN (concat('$service_category_icon_url','/',service_category.icon_name,'?v=0.3')) ELSE '' END) as service_category_icon")
        )
            ->join('service_category', 'service_category.id', '=', 'home_page_feature_store.service_cat_id')
            ->where('home_page_feature_store.status', '=', 1);
        if($service_category_id > 0) {
            $home_page_feature_category= $home_page_feature_category->where('home_page_feature_store.service_cat_id','=',$service_category_id);
        }
            $home_page_feature_category= $home_page_feature_category->groupBy('home_page_feature_store.service_cat_id')
            ->orderBy('home_page_feature_store.id', 'asc')
            ->get();
        $time = Date('H:i:s');

        foreach ($home_page_feature_category as $key=>$singel_home_category) {
            $store_details = HomepageFeatureStore::query()->select('home_page_feature_store.id','home_page_feature_store.store_id','home_page_feature_store.service_cat_id',
                'store_details.name as store_name','store_details.rating as average_ratings','store_details.eta_delivery_time',
                'store_details.offer_min_amount', 'store_details.offer_amount',
                'store_details.offer_type', 'store_details.order_min_amount',
                DB::raw("(CASE WHEN store_details.image != '' THEN (concat('$store_image_path','/',store_details.image)) ELSE '' END) as store_image"),
                DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                +COS( ' . $address_lat . ' * pi()/180)
                * COS(store_details.address_lat * pi()/180)
                * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance'),'providers.service_radius')
                ->join('store_details','store_details.id','=','home_page_feature_store.store_id')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                /*->leftJoin('store_timings', 'store_timings.store_id', '=', 'store_details.id')
                ->where('store_timings.day', substr(date('l'), 0, 3))
                ->where('provider_services.current_status', 1)
                ->where('store_timings.store_open_time', "<=", $time)
                ->where('store_timings.store_close_time', ">=", $time)*/
                ->where('home_page_feature_store.status',1)
                ->where('providers.status',1)
                ->where('provider_services.status',1)
                ->where('home_page_feature_store.service_cat_id','=',$singel_home_category->service_category_id);

            $this->userClassapi->checkDayShowRecord($store_details,0);
                //->havingRaw('providers.service_radius > distance')
            $store_details = $store_details->orderByRaw("distance ASC")
                ->limit($this->feature_store_display_limit)
                ->get();

                //->toArray();
                $store_slider_data = [];
            foreach ($store_details as $keys => $store_detail) {
                $product_cat_name = array_unique(StoreProductDetails::query()->select('store_product_category.name')
                    ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                    ->where('store_product_details.store_id', $store_detail->store_id)->where('store_product_category.status', 1)->where('store_product_details.status', 1)->get()->pluck('name')->toArray());
                //$product_cat_count = StoreProductDetails::query()->where('store_id', $store_detail->id)->where('status', 1)->count();
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
                $store_slider_data[$keys] = [
                    "id" => $store_detail->id,
                    "store_id" => $store_detail->store_id,
                    "service_category_id" => $store_detail->service_cat_id,
                    "store_name" => $store_detail->store_name,
                    "store_products" => $product_cat_name,
                    "average_ratings" => ($store_detail->average_ratings > 0) ? $store_detail->average_ratings : 0,
                    "eta_delivery_time" => ($store_detail->eta_delivery_time!="")?$store_detail->eta_delivery_time-0:0,
                    "store_image" => ($store_detail->store_image != "") ? $store_detail->store_image : "",
                    "distance" => $store_detail->distance,
                    "service_radius" => $store_detail->service_radius,
                    "offer" => $offer,
                ];
            }
            $home_page_feature_store_lists[$key]['service_category_id']= $singel_home_category->service_category_id;
            $home_page_feature_store_lists[$key]['service_category_name']= $singel_home_category->service_category_name;
            $home_page_feature_store_lists[$key]['service_category_icon']= $singel_home_category->service_category_icon;
            $home_page_feature_store_lists[$key]['display_title_name']= $feature_const."". ucwords(strtolower($singel_home_category->service_category_name));
            $home_page_feature_store_lists[$key]['store_lists']= $store_slider_data;
            $home_page_feature_store_lists[$key]['view_all_btn']= count($store_slider_data) < $this->feature_store_display_limit?0:1;
        }

        $home_page_feature_all_store_lists['status'] = 1;
//        $home_page_feature_all_store_lists['message'] = "success!";
        $home_page_feature_all_store_lists['message'] = __('user_messages.1');
        $home_page_feature_all_store_lists['message_code'] = 1;
        $home_page_feature_all_store_lists['all_feature_store'] = $home_page_feature_store_lists;
        return $home_page_feature_all_store_lists;
    }

    //code for view all feature store list
    public function getViewAllFeatureStore(Request $request){
        $validator = Validator::make($request->all(), [
            "user_id" => "nullable|numeric",
            "access_token" => "nullable|numeric",
            "lat" => "required",
            "long" => "required",
            "service_category_id" => "required",
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
        $address_lat = $request->get('lat');
        $address_long = $request->get('long');
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
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }

        $feature_const = config('global.lang_constant.FEATURED.'.$lang_prefix.'value') ." ";
        $get = config('global.lang_constant.GET.'.$lang_prefix.'value') ." ";
        $off_on_all_orders = " ".config('global.lang_constant.OFF_ON_ALL_ORDERS.'.$lang_prefix.'value')." " ;
        $off_on_orders_above = " ".config('global.lang_constant.OFF_ON_ORDERS_ABOVE.'.$lang_prefix.'value')." " ;
        $per_page_rec = ($request->get('page') > 0)?$this->feature_store_display_limit:30;
        $time = Date('H:i:s');
        $store_details = HomepageFeatureStore::query()->select('home_page_feature_store.id', 'home_page_feature_store.store_id', 'home_page_feature_store.service_cat_id',
            'store_details.name as store_name', 'store_details.rating as average_ratings', 'store_details.eta_delivery_time',
            'store_details.offer_min_amount', 'store_details.offer_amount',
            'store_details.offer_type', 'store_details.order_min_amount',
            DB::raw("(CASE WHEN store_details.image != '' THEN (concat('$store_image_path','/',store_details.image)) ELSE '' END) as store_image"),
            DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
            +COS( ' . $address_lat . ' * pi()/180)
            * COS(store_details.address_lat * pi()/180)
            * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance'), 'providers.service_radius')
            ->join('store_details', 'store_details.id', '=', 'home_page_feature_store.store_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            /*->leftJoin('store_timings', 'store_timings.store_id', '=', 'store_details.id')
            ->where('store_timings.day', substr(date('l'), 0, 3))
            ->where('provider_services.current_status', 1)
            ->where('store_timings.store_open_time', "<=", $time)
            ->where('store_timings.store_close_time', ">=", $time)*/
            ->where('home_page_feature_store.status', 1)
            ->where('providers.status', 1)
            ->where('provider_services.status', 1)
            ->where('home_page_feature_store.service_cat_id', '=', $service_category_id);
        $this->userClassapi->checkDayShowRecord($store_details,0);
            //->havingRaw('providers.service_radius > distance')
        $store_details = $store_details->orderByRaw("distance ASC")
            //->get();
            //->toArray();
            ->paginate($per_page_rec);

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

            $store_slider_data[$keys] = [
                "id" => $store_detail->id,
                "store_id" => $store_detail->store_id,
                "service_category_id" => $store_detail->service_cat_id,
                "store_name" => $store_detail->store_name,
                "store_products" => $product_cat_name,
                "average_ratings" => ($store_detail->average_ratings > 0) ? $store_detail->average_ratings : 0,
                "eta_delivery_time" => ($store_detail->eta_delivery_time !="")?$store_detail->eta_delivery_time-0:0,
                "store_image" => ($store_detail->store_image != "") ? $store_detail->store_image : "",
                "distance" => $store_detail->distance,
                "service_radius" => $store_detail->service_radius,
                "offer" => $offer,
            ];
        }

        $home_page_feature_all_store_lists['status'] = 1;
//        $home_page_feature_all_store_lists['message'] = "success!";
        $home_page_feature_all_store_lists['message'] = __('user_messages.1');
        $home_page_feature_all_store_lists['message_code'] = 1;
        $home_page_feature_all_store_lists['service_category_id'] = $service_category_id;
        $home_page_feature_all_store_lists['service_category_name'] = $service_category_data->service_category_name;
        $home_page_feature_all_store_lists['service_category_icon'] = $service_category_data->service_category_icon;
        $home_page_feature_all_store_lists['display_title_name'] = $feature_const . "" . ucwords(strtolower($service_category_data->service_category_name));
        $home_page_feature_all_store_lists['store_lists'] = $store_slider_data;

        $home_page_feature_all_store_lists['total_page'] = ($store_details->total() > 0) ? ceil($store_details->total() / $per_page_rec) : 0;
        $home_page_feature_all_store_lists['per_page'] = ($store_details->perPage() > 0) ? $store_details->perPage() : 0;
        $home_page_feature_all_store_lists['current_page'] = ($store_details->currentPage() > 0) ? $store_details->currentPage() : 0;
        return $home_page_feature_all_store_lists;
    }

    //api for home page spot light list
    public function postHomePageSpotLightList(Request $request){
        $validator = Validator::make($request->all(), [
            "user_id" => "nullable|numeric",
            "access_token" => "nullable|numeric",
            "lat" => "required",
            "long" => "required",
            "view_all" => "required|in:0,1",
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
        $address_lat = $request->get('lat');
        $address_long = $request->get('long');
        $view_all = $request->get('view_all') - 0;

        $language = "";
        $user_currency = Null;
        if ($user_id != Null && $access_token != Null) {
            $user_details = $this->userClassapi->checkUserAllow($user_id, $access_token);
            if ($decoded['status'] = json_decode($user_details) == false) {
                return $user_details;
            }
            $language = $user_details->language;
            $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
        }

        if ($language != "en" && $language != "" && $language != "Null") {
            $lang_prefix = $language . "_";
        } else {
            $lang_prefix = "";
        }
        $store_image_path = url('/assets/images/store-images/');
        $service_category_icon_url = url('/assets/images/service-category/');

        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;
        $currency_symbol = $user_currency->symbol;

        $feature_const = config('global.lang_constant.IN_SPOT_LIGHT.'.$lang_prefix.'value') ."";

        $home_page_feature_all_store_lists['status'] = 1;
//        $home_page_feature_all_store_lists['message'] = "success!";
        $home_page_feature_all_store_lists['message'] = __('user_messages.1');
        $home_page_feature_all_store_lists['message_code'] =1;
        $home_page_feature_all_store_lists['display_title_name'] = $feature_const;

         $get_home_page_spot_light_list = HomepageSpotLight::query()
            ->select("home_page_spot_light.*",
                "service_category." . $lang_prefix . "name as service_category_name",
                "service_category.category_type",
                DB::raw("(CASE WHEN service_category.icon_name != '' THEN (concat('$service_category_icon_url','/',service_category.icon_name,'?v=0.3')) ELSE '' END) as service_category_icon")
            )
            ->join('service_category','service_category.id','=','home_page_spot_light.service_cat_id')
//             ->join('provider_services','provider_services.provider_id','=','home_page_spot_light.provider_id')
//             ->join('provider_services', function($join)
//             {
//                 $join->on('provider_services.service_cat_id', '=', 'home_page_spot_light.service_cat_id')
//                     ->on('provider_services.provider_id', '=', 'home_page_spot_light.provider_id');
//             })
//             ->where('provider_services.current_status','=',1)
             ->where('home_page_spot_light.status','=',1)
             ->orderBy('home_page_spot_light.id','desc')
            ->get();
        $home_page_spot_light_list = [];
        if ($get_home_page_spot_light_list != Null){
            foreach ($get_home_page_spot_light_list as $key => $get_home_page_spot_light_detail){


                if(in_array($get_home_page_spot_light_detail->category_type, $this->delivery_category_type)) {

                    $store_details = StoreDetails::query()->select("store_details.id as store_id", "store_details.name as provider_name",
                        'store_details.rating as average_ratings', 'store_details.eta_delivery_time',
                        DB::raw("(CASE WHEN store_details.image != '' THEN (concat('$store_image_path','/',store_details.image)) ELSE '' END) as store_image"),
                        'providers.service_radius',
                        DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                        +COS( ' . $address_lat . ' * pi()/180)
                        * COS(store_details.address_lat * pi()/180)
                        * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
                    )
                        ->join('provider_services','provider_services.id','=','store_details.provider_service_id')
                        ->join('providers','providers.id','=','provider_services.provider_id')
                        ->where('providers.id', '=', $get_home_page_spot_light_detail->provider_id)
                        ->where('providers.status', '=',1)
                        ->where('provider_services.status','=',1);
                    $this->userClassapi->checkDayShowRecord($store_details,0);
                        //->havingRaw('providers.service_radius > distance')
                    $store_details = $store_details->first();

                    if ($store_details != Null){
                        $home_page_spot_light_list[] = [
                            "id" => $get_home_page_spot_light_detail->id,
                            "service_category_id" => $get_home_page_spot_light_detail->service_cat_id,
                            "service_category_name" => $get_home_page_spot_light_detail->service_category_name,
                            "service_category_icon" => $get_home_page_spot_light_detail->service_category_icon,
                            "provider_id" => $store_details->store_id,
                            "provider_name" => $store_details->provider_name,
                            "average_ratings" => ($store_details->average_ratings > 0) ? $store_details->average_ratings : 0,
                            "eta_delivery_time" => ($store_details->eta_delivery_time !="")?$store_details->eta_delivery_time-0:0,
                            //"total_completed_order" => 0,
                            "distance" => 0.0,
                            "image" => ($store_details->store_image != "") ? $store_details->store_image : "",
                        ];
                    }

                }
                elseif(in_array($get_home_page_spot_light_detail->category_type, $this->on_demand_category_type)){

                    $provider_details = OtherServiceProviderDetails::query()->select('providers.id as provider_id',
                        'other_service_provider_details.rating as average_ratings','other_service_provider_details.total_completed_order as total_completed_order',
                        DB::raw("CONCAT(providers.first_name,' ') AS provider_name"),
                        'providers.avatar',
                        'provider_services.id as provider_service_id',
                        'providers.service_radius',
                        'other_service_provider_details.time_slot_status',
                        DB::raw(('other_service_provider_details.lat,other_service_provider_details.long, ( round( 6367 * acos( cos( radians(' . $address_lat . ') ) * cos( radians( other_service_provider_details.lat ) ) * cos( radians( other_service_provider_details.long ) - radians(' . $address_long . ') ) + sin( radians(' . $address_lat . ') ) * sin( radians( other_service_provider_details.lat ) ) ),2 ) ) AS distance'))
                    )
                        ->join('providers','providers.id','=','other_service_provider_details.provider_id')
                        ->join('provider_services','provider_services.provider_id','=','providers.id')
                        ->where('other_service_provider_details.provider_id', '=', $get_home_page_spot_light_detail->provider_id)
                        ->where('providers.status','=',1)
                        ->where('provider_services.status','=',1)
                        ->where('provider_services.current_status','=',1)
//                        ->havingRaw('providers.service_radius >= distance')
                        //                        ->where('other_service_provider_details.time_slot_status','=',1)
                        ->where('provider_services.service_cat_id','=',$get_home_page_spot_light_detail->service_cat_id);
//                    $this->userClassapi->checkDayShowRecord($provider_details,0);
                        //->havingRaw('providers.service_radius > distance')
                    $provider_details = $provider_details->first();

                    if ($provider_details != ''){
                        if ($provider_details->avatar != Null && $provider_details != Null) {
                            if (filter_var($provider_details->avatar, FILTER_VALIDATE_URL) == true) {
                                $provider_profile_image = $provider_details->avatar;
                            } else {
                                $provider_profile_image = url('/assets/images/profile-images/provider/' . $provider_details->avatar);
                            }
                        } else {
                            $provider_profile_image = "";
                        }
                        $package_check=OtherServiceProviderPackages::where('provider_service_id','=',$provider_details->provider_service_id)
                                                                        ->where('service_cat_id','=',$get_home_page_spot_light_detail->service_cat_id)
                                                                        ->where('status','=',1)->count();
                        if($package_check>=1)
                        {
                            $home_page_spot_light_list[] = [
                                "id" => $get_home_page_spot_light_detail->id,
                                "service_category_id" => $get_home_page_spot_light_detail->service_cat_id,
                                "service_category_name" => $get_home_page_spot_light_detail->service_category_name,
                                "service_category_icon" => $get_home_page_spot_light_detail->service_category_icon,
                                "provider_id" => $provider_details->provider_id,
                                "provider_name" => $provider_details->provider_name,
                                "average_ratings" => ($provider_details->average_ratings > 0) ? $provider_details->average_ratings : 0,
                                "eta_delivery_time" => 0,
                                "total_completed_order" => ($provider_details->total_completed_order > 0) ? $provider_details->total_completed_order - 0 : 0,
                                "distance" => ($provider_details->service_radius > 0) ? $provider_details->service_radius  : 0,
                                "image" => $provider_profile_image,
                                "time_slot_status" => $provider_details->time_slot_status
                            ];
                        }

                    }
                }
                 if(($view_all == 0) && count($home_page_spot_light_list) == $this->spot_display_limit){
                     break;
                 }
            }
        }

        $home_page_feature_all_store_lists['spot_light_list'] = $home_page_spot_light_list;
        $home_page_feature_all_store_lists['view_all_btn'] = count($home_page_spot_light_list) < $this->spot_display_limit?0:1;
        return $home_page_feature_all_store_lists;
    }
    //code check api splash screen data
    public function postAppVersionCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "app_type" => "required|in:0,1,2,3",
            "login_device" => "required|in:1,2,3,4",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $app_type = $request->get('app_type');;
        $login_device = $request->get('login_device');
        if($app_type != "" && $login_device > 0)
        {
            $app_version_details = AppVersionSetting::query()
                ->where("app_type","=",$app_type)
                ->where("app_device_type", "=", $login_device)
                //            ->where("forcefully_type", "=", 1)
                ->orderBy("id","desc")
                ->first();
            $is_forcefully_update = 0;
            $version_name = "";

            if ($app_version_details != Null){
                $is_forcefully_update = $app_version_details->forcefully_type - 0;
                $version_name = $app_version_details->version_name;
            }

            return response()->json([
                "status" => 1,
//                "message" => "success!",
                'message' => __('user_messages.1'),
                "message_code" => 1,
                "app_version" => $version_name. "",
                "is_forcefully_update" => $is_forcefully_update,

            ]);
        }else{
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                'message' => __('user_messages.1'),
                "message_code" => 1,
                "app_version" => "",
                "is_forcefully_update" => 0,
            ]);
        }

    }

    //remove provider account(softdelete)
    public function postUserRemoveAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_id = $request->get('user_id');
        $user_check = $this->userClassapi->checkUserAllow($user_id, $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }

        $user_running_packges = UserPackageBooking::query()->where('user_id', '=', $user_id)->whereNotIn('status', [4, 5, 9, 10])->count();

        if ($user_running_packges > 0) {
            return response()->json([
                "status" => 0,
                "message" => __('user_messages.301'),
                "message_code" => 5,
            ]);
        }

        $get_provider_details = User::query()->where('id', $request->get("user_id"))->first();
        if ($get_provider_details == Null) {
            return response()->json([
                "status" => 0,
                "message" => __('user_messages.5'),
                "message_code" => 5,
            ]);
        }
        $get_provider_details->delete();

        return response()->json([
            "status" => 1,
            "message" => __('user_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postProviderRemoveAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "provider_id" => "required|integer",
            "access_token" => "required|numeric",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $provider_id = $request->get('provider_id');
        $access_token = $request->get('access_token');
        $notificationCls = new NotificationClass();
        $adminCls = new AdminClass();
        $onDemandClass = new OnDemandClassApi($notificationCls, $adminCls);
        $provider_details = $onDemandClass->providerRegisterAllow($provider_id, $access_token);
        if ($decoded['status'] = json_decode($provider_details) == false) {
            return $provider_details;
        }

//        $user_running_delivery = 0;
//        $provider_details_id = OtherServiceProviderDetails::query()->where('provider_id','=',$provider_id)->first();
//        if($provider_details_id != Null)
//        {
//            $provider_detail_id = $provider_details_id->id;
//            $user_running_packges = UserPackageBooking::query()->where('provider_id','=',$provider_detail_id)->whereNotIn('status',[4,5,9,10])->count();
//        }
        $user_running_packges = UserPackageBooking::query()->where('provider_id', '=', $provider_id)->whereNotIn('status', [4, 5, 9, 10])->count();
        if ($user_running_packges > 0) {
            return response()->json([
                "status" => 0,
                "message" => __('provider_messages.264'),
                "message_code" => 5,
            ]);
        }

        $get_provider_details = Provider::query()->where('id', $provider_id)->first();
        if ($get_provider_details == Null) {
            return response()->json([
                "status" => 0,
                "message" => __('provider_messages.73'),
                "message_code" => 73,
            ]);
        }
        OtherServiceRatings::where('provider_id',$provider_id)->delete();
        $get_provider_details->delete();
        return response()->json([
            "status" => 1,
            "message" => __('provider_messages.1'),
            "message_code" => 1,
        ]);
    }
}
