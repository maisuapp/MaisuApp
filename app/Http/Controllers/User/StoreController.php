<?php

namespace App\Http\Controllers\User;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Classes\TokenClassApi;
use App\Classes\UserClassApi;
use App\Http\Requests\UserAddressRequest;
use App\model\AreaWiseVehicleCost;
use App\model\City;
use App\model\CouponCodeDetail;
use App\model\CouponDetails;
use App\model\DeliveryCharges;
use App\model\GeneralSettings;
use App\model\GoPrimeServices;
use App\model\PageSettings;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\RestrictedArea;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\StoreDetails;
use App\model\StoreProductCategory;
use App\model\StoreProductCustomizeCategory;
use App\model\StoreProductDetails;
use App\model\StoreProductOptions;
use App\model\StoreProductSize;
use App\model\StoreProductToppings;
use App\model\StoreTimings;
use App\model\TransportDriverDetails;
use App\model\TransportRideBook;
use App\model\UserOrderCart;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserAddress;
use App\model\UserCardDetails;
use App\model\UserFavouriteStore;
use App\model\UserPackageBooking;
use App\model\UserProductBooking;
use App\model\UserProductBookingOptions;
use App\model\UserProductBookingQuantity;
use App\model\UserProductBookingSize;
use App\model\UserProductBookingStatus;
use App\model\UserProductBookingToppings;
use App\model\UserPurchaseCouponDetails;
use App\model\UserPurchaseGoPrimeDetails;
use App\model\UserReferHistory;
use App\model\UserVerification;
use App\model\UserWalletTransaction;
use App\model\WorldCurrency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Twilio\Rest\Client;

class StoreController extends Controller
{
    private $food_delivery;
    private $store_order_status_int;
    private $store_order_status;

    private $userClassapi;
    private $notificationClass;
    Private $tokenClassApi;
    private $medicineDelivery;
    Private $perpageRec = 5;

    public function __construct(UserClassApi $userClassapi, NotificationClass $notificationClass, TokenClassApi $tokenClassApi)
    {

        $this->userClassapi = $userClassapi;
        $this->notificationClass = $notificationClass;
        $this->tokenClassApi = $tokenClassApi;
        $this->food_delivery = 5;
        $this->medicineDelivery = 10;
        $this->store_order_status = ['', 'pending', 'approved', 'rejected', 'cancelled', 'processing', 'approved-by-driver', 'arrived-driver', 'ongoing', 'completed', 'failed'];
        $this->store_order_status_int = [1, 2, 5, 7, 8, 9, 3, 4];


    }

    public function getHomepage()
    {
        /*if(Session::has('timezone')){
                dd(Session::get('timezone'));
        }*/
        $exitCode = Artisan::call('cache:clear');
        if (!Auth::guard("user")->check()) {
            Auth::logout();
        }
        $service_list  = ServiceCategory::query()->whereIN('category_type', [3, 4])->where('status', 1)->get();


        $service_category_list =  ServiceCategory::query()->whereIn('id',[5,6])->get();
        $store_details_new_data = [];
        foreach ($service_category_list as $keys=>$singel_cat){
            $store_details_new_data[$keys]['service_cat_id'] =$singel_cat->id;
            $store_details_new_data[$keys]['service_cat_name'] =$singel_cat->name;
            $store_details_new_data[$keys]['service_cat_slug'] =$singel_cat->slug;
            $store_details_new = StoreDetails::query()
                ->select('store_details.id', 'store_details.image', 'store_details.offer_amount', 'store_details.offer_min_amount',
                    'store_details.offer_type', 'store_details.offer_amount as offer_percentage', 'store_details.rating as ratings',
                    'store_details.slug', 'store_details.name',
                    DB::raw("CONCAT(providers.country_code,providers.contact_number) AS contact_details"),
                    'provider_services.service_cat_id','service_category.slug as service_cat_name')
                ->join('provider_services','provider_services.id','=','store_details.provider_service_id')
                ->join('providers','providers.id','=','provider_services.provider_id')
                ->join('service_category','service_category.id','=','provider_services.service_cat_id')
                ->where('provider_services.status', 1)
                ->where('provider_services.service_cat_id', $singel_cat->id)
                ->where('providers.status', 1)
                ->get();
            $store_details_new_data[$keys]['data'] = array();
            $flag_stop = 0;
            foreach ($store_details_new as $key => $restaurant) {
                $check_has_product = StoreProductDetails::query()->select('id')->where('store_id', $restaurant->id)->where('status', 1)->count();

                if ($check_has_product != 0) {
                    $cuisines[$key] = StoreProductDetails::query()->select('store_product_category.name','store_product_details.store_id')
                        ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                        ->where('store_product_details.store_id', $restaurant->id)
                        ->orderBy('name', 'asc')
                        ->groupBy('store_product_details.product_cate_id')
                        ->get()->toArray();
                    $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                        ->where('store_id', $restaurant->id)
                        ->where('day', 'all')
                        ->first();
                    if ($res_open_time == Null) {
                        $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                            ->where('store_id', $restaurant->id)
                            ->where('day', strtoupper(substr(date('l'), 0, 3)))
                            ->first();

                    }
                    if($res_open_time != Null)
                    {
                        $res_time[$keys][$restaurant->id] = date('g:i A', strtotime($res_open_time->res_opens)) . " to " . date('g:i A', strtotime($res_open_time->res_closes));
                    }else{
                        $res_time[$keys][$restaurant->id] = "";
                    }
                } else {
                    unset($store_details_new[$key]);
                }
                $store_details_new_data[$keys]['data'] = $store_details_new->toArray();
                $store_details_new_data[$keys]['cuisines'] = $cuisines;
                $store_details_new_data[$keys]['res_time'] = $res_time;

            }

            $store_details_new_data[$keys]['data'] = array_slice($store_details_new_data[$keys]['data'],0,3);
        }

        return view('front.layout.homepage',compact('service_list','store_details_new_data'));
    }
    public function getStoreHomepage($slug)
    {
        $exitCode = Artisan::call('cache:clear');
        if (!Auth::guard("user")->check()) {
//            if (Auth::user()->roles(Auth::user()->id)->first()->name == "admin" && Auth::user()->roles(Auth::user()->id)->first()->name == "res_owner") {
            Auth::logout();
//                return redirect()->route('get:homepage');
//            }
        }
//        $pop_home_restaurants = PopularHomeRestaurant::select('users.name', 'restaurants.id', 'restaurants.slug', 'restaurants.image')->join('restaurants', 'restaurants.id', '=', 'popular_rest_front.restaurant_id')->join('users', 'users.id', '=', 'restaurants.user_id')->orderby('popular_rest_front.id', 'asc')->get();
//        if ($pop_home_restaurants != Null) {
//            return view('front.pages.homepage', compact('pop_home_restaurants'));
//        } else {
        $service_cat_id = ServiceCategory::query()->select('id')->where('slug','=',$slug)->first();

        return view('front.pages.store.store-home',compact('service_cat_id'));
//        return view('homepage2');
//        }
    }



    public function getSearch(Request $request)
    {
    //    $location = strtolower($request->get('location'));
        $search = "";
        $location =  strtolower($request->get('search'));
        $service_category_id =(intval($request->get('service_category_id')) > 0)?intval($request->get('service_category_id')):5;
        $get_service_category =  ServiceCategory::query()->where('id',$service_category_id)->first();

        /*if ($search == Null && $location == Null && $get_service_category == Null ) {
            return redirect()->back();
        }*/
        $location = ($location !="")?$location:"all";
        if ( $get_service_category == Null || $location == "" ) {
            return redirect()->back();
        } else {
            $location = preg_replace('/\s+/', '-', $location);

            $service_category_name = $get_service_category->slug;
            return redirect()->route('get:search_restaurants_list', compact('service_category_name','location', 'search'));
        }
    }

    //search Location with Restaurants
    public function getSearchLocationRestaurantsList($service_category_name="",$location="")
    {

//        $city = WorldCities::where('name', $location)->where('parent_id', '!=', 0)->where('type', 'city')->first();
//        if ($city == Null) {
//            return redirect()->back();
//        }
//        $location = 0;
        $get_service_category =  ServiceCategory::query()->where('slug',$service_category_name)->first();
        if($get_service_category != Null){
            $service_category_id = $get_service_category->id;
            $service_category_slug =$get_service_category->slug;
        }else{
            $service_category_id = $this->food_delivery;
            $service_category_slug ="food-delivery";
        }

        $restaurants = StoreDetails::select('store_details.id', 'store_details.image', 'store_details.offer_amount', 'store_details.offer_min_amount', 'store_details.offer_type', 'store_details.offer_amount as offer_percentage', 'store_details.rating as ratings', 'store_details.slug', 'store_details.name', 'providers.contact_number as contact_details')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
//            ->where('provider_services.current_status', 1)
            ->where('provider_services.status', 1)
            ->where('providers.status', 1)
            ->where('provider_services.service_cat_id', $service_category_id)
            ->where('store_details.name', 'LIKE', "%{$location}%")
            ->groupBy('store_details.id')
            ->get();

        foreach ($restaurants as $key => $restaurant) {
            $check_has_product = StoreProductDetails::select('id')->where('store_id', $restaurant->id)->where('status', 1)->count();
            if ($check_has_product != 0) {
                $cuisines[$key] = StoreProductDetails::query()->select('store_product_category.name')
                    ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                    ->where('store_product_details.store_id', $restaurant->id)
                    ->orderBy('name', 'asc')
                    ->groupBy('store_product_details.product_cate_id')
                    ->get();
                $res_open_time = StoreTimings::select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                    ->where('store_id', $restaurant->id)
                    ->where('day', 'all')
                    ->first();
                if ($res_open_time == Null) {
                    $res_open_time = StoreTimings::select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                        ->where('store_id', $restaurant->id)
                        ->where('day', strtoupper(substr(date('l'), 0, 3)))
                        ->first();
                }
                if ($res_open_time == Null) {
                    $res_time[$key] = "close";
                } else {
                    $res_time[$key] = date('g:i A', strtotime($res_open_time->res_opens)) . " to " . date('g:i A', strtotime($res_open_time->res_closes));
                }
            } else {
                unset($restaurants[$key]);
            }
        }
        $cuisine_lists = StoreProductCategory::select('name')->orderBy('name', 'asc')->get();
        $count = count($restaurants);
        //$location = strtolower($city->name);

        if ($count >= 1) {
            return view('front.pages.restaurant_list', compact('restaurants', 'count', 'cuisines', 'res_time', 'cuisine_lists', 'location','service_category_slug'));
        } else {
            return view('front.pages.not_found_location');
        }
    }

    //search Location with Restaurants & Cuisines
    public function getSearchRestaurantsList($service_category_name="",$location="", $search="")
    {
//        $city = WorldCities::where('name', $location)->where('parent_id', '!=', 0)->where('type', 'city')->first();
//        if ($city == Null) {
//            return redirect()->back();
//        }
//        $location = $city->id;

        $search = str_replace('-', ' ', $search);
        $location = str_replace('-', ' ', $location);

//        $find_location = StoreDetails::select('id')->where('id', $location)->first();

        $find_cuisine = StoreProductCategory::query()->select('id')->where('parent_id','>',0)->where('name', '=', "$search")->first();


//        $cuisine_lists = StoreProductCategory::query()->select('name')->orderBy('name', 'asc')->get();

        $get_service_category =  ServiceCategory::query()->where('slug',$service_category_name)->first();

        if($get_service_category != Null){
            $service_category_id = $get_service_category->id;
            $service_category_slug =$get_service_category->slug;
        }else{
            $service_category_id = $this->food_delivery;
            $service_category_slug ="food-delivery";
        }

        $cuisine_lists = StoreProductCategory::query()->select('name')->where('service_cat_id',$service_category_id)->where('parent_id','>',0)->orderBy('name', 'asc')->get();

//        $find_restaurant = StoreDetails::query()->select('store_details.id')
//            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
//            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
//            ->where('provider_services.status', 1)
//            ->where('providers.status', 1)
//            ->where('provider_services.service_cat_id', $service_category_id);
//            if($search != "all")
//            {
//                $find_restaurant =$find_restaurant->where('store_details.name', 'LIKE', "%{$location}%");
//            }
//            $find_restaurant =$find_restaurant->get();

//        if ($find_cuisine != Null && $find_location != Null) {


        if ($find_cuisine == Null) {

//            $restaurant_list = StoreDetails::query()->select('store_details.id', 'store_details.image', 'store_details.offer_amount', 'store_details.offer_min_amount', 'store_details.offer_type', 'store_details.offer_amount as offer_percentage', 'store_details.rating as ratings', 'store_details.slug', 'store_details.name', 'providers.contact_number as contact_details')
//                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
//                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
//                ->where('provider_services.current_status', 1)
//                ->where('provider_services.status', 1)
//                ->where('providers.status', 1)
//                ->where('provider_services.service_cat_id', $service_category_id)
//                ->groupBy('store_details.id')
//                ->get();
//            $merged = $find_restaurant->merge($restaurant_list);
//            $restaurants = $merged->all();
//            //$count = count($restaurants);
//            foreach ($restaurants as $key => $restaurant) {
//                $check_has_product = StoreProductDetails::query()->select('id')->where('store_id', $restaurant->id)->where('status', 1)->count();
//                if ($check_has_product != 0) {
//                    $cuisines[$key] = StoreProductDetails::query()->select('store_product_category.name')
//                        ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
//                        ->where('store_product_details.store_id', $restaurant->id)
//                        ->orderBy('store_product_category.name', 'asc')
//                        ->groupBy('store_product_details.product_cate_id')
//                        ->get();
//                    $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
//                        ->where('store_id', $restaurant->id)
//                        ->where('day', 'all')
//                        ->first();
//                    if ($res_open_time == Null) {
//                        $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
//                            ->where('store_id', $restaurant->id)
//                            ->where('day', strtoupper(substr(date('l'), 0, 3)))
//                            ->first();
//                    }
//                    if ($res_open_time == Null) {
//                        $res_time[$key] = "close";
//                    } else {
//                        $res_time[$key] = date('g:i A', strtotime($res_open_time->res_opens)) . " to " . date('g:i A', strtotime($res_open_time->res_closes));
//                    }
//                } else {
//                    unset($restaurants[$key]);
//                }
//            }
//            $count = count($restaurants);
//
//            return view('front.pages.restaurant_list', compact('restaurants', 'count', 'cuisines', 'res_time', 'cuisine_lists', 'location','service_category_slug','search'));
            $restaurants = StoreDetails::query()->select('store_details.id', 'store_details.image', 'store_details.offer_amount', 'store_details.offer_min_amount', 'store_details.offer_type', 'store_details.offer_amount as offer_percentage', 'store_details.rating as ratings', 'store_details.slug', 'store_details.name', 'providers.contact_number as contact_details')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->join('store_product_details', 'store_product_details.store_id', '=', 'store_details.id')
                ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                ->where('provider_services.current_status', 1)
                ->where('provider_services.status', 1)
                ->where('providers.status', 1)
                ->where('provider_services.service_cat_id', $service_category_id);
                if($location != "all")
                {
                    $restaurants =$restaurants->where('store_details.name', 'LIKE', "%{$location}%");
                }
                $restaurants =$restaurants->groupBy('store_product_details.store_id')
                ->get();

            if (!$restaurants->isEmpty()) {
                foreach ($restaurants as $key => $restaurant) {
                    $check_has_product = StoreProductDetails::query()->select('id')->where('store_id', $restaurant->id)->where('status', 1)->count();
                    if ($check_has_product != 0) {
                        $cuisines[$key] = StoreProductDetails::query()->select('store_product_category.name')
                            ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                            ->where('store_product_details.store_id', $restaurant->id)
                            ->orderBy('store_product_category.name', 'asc')
                            ->groupBy('store_product_details.product_cate_id')
                            ->get();

                        $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                            ->where('store_id', $restaurant->id)
                            ->where('day', 'all')
                            ->first();
                        if ($res_open_time == Null) {
                            $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                                ->where('store_id', $restaurant->id)
                                ->where('day', strtoupper(substr(date('l'), 0, 3)))
                                ->first();
                        }
                        if ($res_open_time == Null) {
                            $res_time[$key] = "close";
                        } else {
                            $res_time[$key] = date('g:i A', strtotime($res_open_time->res_opens)) . " to " . date('g:i A', strtotime($res_open_time->res_closes));
                        }
                    } else {
                        unset($restaurants[$key]);
                    }
                }
                $count = count($restaurants);
                /*dd($cuisines);*/
//              $location = strtolower($city->name);
                return view('front.pages.restaurant_list', compact('restaurants', 'count',
                    'cuisines', 'res_time', 'cuisine_lists', 'location','service_category_slug','search'));
            } else {
//                $location = strtolower($city->name);
                return view('front.pages.not_found_cuisine', compact('service_category_slug','location' ,'search','cuisine_lists'));
            }
        }
        elseif ($find_cuisine != Null) {

            $restaurants = StoreDetails::query()->select('store_details.id', 'store_details.image', 'store_details.offer_amount', 'store_details.offer_min_amount', 'store_details.offer_type', 'store_details.offer_amount as offer_percentage', 'store_details.rating as ratings', 'store_details.slug', 'store_details.name', 'providers.contact_number as contact_details')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->join('store_product_details', 'store_product_details.store_id', '=', 'store_details.id')
                ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                ->where('provider_services.current_status', 1)
                ->where('provider_services.status', 1)
                ->where('providers.status', 1)
                ->where('provider_services.service_cat_id', $service_category_id)
                ->where('store_product_category.id', $find_cuisine->id)
                ->groupBy('store_product_details.store_id')
                ->get();

            if (!$restaurants->isEmpty()) {
                foreach ($restaurants as $key => $restaurant) {
                    $check_has_product = StoreProductDetails::query()->select('id')->where('store_id', $restaurant->id)->where('status', 1)->count();
                    if ($check_has_product != 0) {
                        $cuisines[$key] = StoreProductDetails::query()->select('store_product_category.name')
                            ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                            ->where('store_product_details.store_id', $restaurant->id)
                            ->orderBy('store_product_category.name', 'asc')
                            ->groupBy('store_product_details.product_cate_id')
                            ->get();
                        $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                            ->where('store_id', $restaurant->id)
                            ->where('day', 'all')
                            ->first();
                        if ($res_open_time == Null) {
                            $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                                ->where('store_id', $restaurant->id)
                                ->where('day', strtoupper(substr(date('l'), 0, 3)))
                                ->first();
                        }
                        if ($res_open_time == Null) {
                            $res_time[$key] = "close";
                        } else {
                            $res_time[$key] = date('g:i A', strtotime($res_open_time->res_opens)) . " to " . date('g:i A', strtotime($res_open_time->res_closes));
                        }
                    } else {
                        unset($restaurants[$key]);
                    }
                }
                $count = count($restaurants);

//              $location = strtolower($city->name);
                return view('front.pages.restaurant_list', compact('restaurants', 'count', 'cuisines', 'res_time', 'cuisine_lists', 'location','service_category_slug','search'));
            } else {

//                $location = strtolower($city->name);
                return view('front.pages.not_found_cuisine', compact('service_category_slug','location' ,'search','cuisine_lists'));
            }
        }
        else {
//            $location = strtolower($city->name);
//            return redirect()->route('get:search_location_restaurants_list', compact('service_category_slug','location','search'));
            return view('front.pages.not_found_cuisine', compact('service_category_slug','location' ,'search','cuisine_lists'));
        }
    }

    public function getSearchOfferRestaurantsList(Request $request,$service_category_name) {
        $location="all";
        $search="";
        $get_service_category =  ServiceCategory::query()->where('slug',$service_category_name)->first();

        if($get_service_category != Null){
            $service_category_id = $get_service_category->id;
            $service_category_slug = $get_service_category->slug;
        } else {
            $service_category_id = $this->food_delivery;
            $service_category_slug = "food-delivery";
        }

        $cuisine_lists = StoreProductCategory::query()->select('name')
            ->where('service_cat_id',$service_category_id)
            ->orderBy('name', 'asc')->get();


        $restaurants = StoreDetails::query()->select('store_details.id', 'store_details.image', 'store_details.offer_amount', 'store_details.offer_min_amount', 'store_details.offer_type', 'store_details.offer_amount as offer_percentage', 'store_details.rating as ratings', 'store_details.slug', 'store_details.name', 'providers.contact_number as contact_details')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->join('store_product_details', 'store_product_details.store_id', '=', 'store_details.id')
            ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
            ->where('provider_services.current_status', 1)
            ->where('provider_services.status', 1)
            ->where('providers.status', 1)
            ->where('provider_services.service_cat_id', $service_category_id)
            ->where('store_details.offer_type','!=',0)
            ->groupBy('store_product_details.store_id')
            ->get();

        if (!$restaurants->isEmpty()) {
            foreach ($restaurants as $key => $restaurant) {
                $check_has_product = StoreProductDetails::query()->select('id')->where('store_id', $restaurant->id)->where('status', 1)->count();
                if ($check_has_product != 0) {
                    $cuisines[$key] = StoreProductDetails::query()->select('store_product_category.name')
                        ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                        ->where('store_product_details.store_id', $restaurant->id)
                        ->orderBy('store_product_category.name', 'asc')
                        ->groupBy('store_product_details.product_cate_id')
                        ->get();

                    $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                        ->where('store_id', $restaurant->id)
                        ->where('day', 'all')
                        ->first();
                    if ($res_open_time == Null) {
                        $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                            ->where('store_id', $restaurant->id)
                            ->where('day', strtoupper(substr(date('l'), 0, 3)))
                            ->first();
                    }
                    if ($res_open_time == Null) {
                        $res_time[$key] = "close";
                    } else {
                        $res_time[$key] = date('g:i A', strtotime($res_open_time->res_opens)) . " to " . date('g:i A', strtotime($res_open_time->res_closes));
                    }
                } else {
                    unset($restaurants[$key]);
                }
            }
            $count = count($restaurants);

            if ($count >= 1) {
                return view('front.pages.restaurant_list', compact('restaurants', 'count', 'cuisines', 'res_time',
                    'cuisine_lists','service_category_slug','location', 'search'));
            } else {
                return view('front.pages.not_found_cuisine', compact('cuisine_lists','service_category_slug','location', 'search'));
            }
        }
        else {
            return view('front.pages.not_found_cuisine', compact( 'cuisine_lists','service_category_slug','location', 'search'));
        }
    }

    public function getRestaurantData($slug)
    {

        $restaurant = StoreDetails::query()->select('store_details.id', 'store_details.image',
//            'store_details.offer_type as res_type',
            'store_details.order_min_amount',
            'store_details.offer_amount', 'store_details.offer_min_amount', 'store_details.offer_type',
            'store_details.eta_delivery_time', 'store_details.description',
            'store_details.rating', 'store_details.address', 'store_details.name', 'providers.country_code', 'providers.contact_number','provider_services.service_cat_id as service_cat_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
//                ->where('provider_services.status', $provider_status)
//                ->where('providers.status', $status)
//            ->where('provider_services.service_cat_id', $this->food_delivery)
            ->where('store_details.id', $slug)
//                ->orderBy('store_details.id', '=', 'desc')
            ->first();

        if ($restaurant == Null) {
            return redirect()->back();
        }
        $time = Date('H:i:s');
        $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
            ->where('store_id', $restaurant->id)
            ->where('day', 'all')
            ->first();
//        dd($res_open_time);
        if ($res_open_time == Null) {
            $res_open_time = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                ->where('store_id', $restaurant->id)
                ->where('store_open_time', "<=", $time)
                ->where('store_close_time', ">=", $time)
                ->where('day', strtoupper(substr(date('l'), 0, 3)))
                ->first();
        }
        if ($res_open_time == Null) {
            $res_time = __('messages.122')."";
        } else {

            $res_time = __('messages.179')." ". date('g:i A', strtotime($res_open_time->res_opens)) . " to " . date('g:i A', strtotime($res_open_time->res_closes));
        }

        $cuisines_list = StoreProductDetails::query()->select('store_product_details.id', 'store_product_details.name',
            'store_product_details.food_type', 'store_product_details.image',
            'store_product_details.amount', 'store_product_details.discount_amount',
            'store_product_details.discount_type', 'store_product_details.slug', 'store_product_category.id as cuisine_id', 'store_product_category.name as cuisine_name')
            ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
            ->where('store_product_details.store_id', $restaurant->id)
            ->orderBy('name', 'asc');
        $product_lists = $cuisines_list->where('store_product_details.status', 1)->get();
        $cuisines = $cuisines_list->groupBy('store_product_details.product_cate_id')->get();
        $cart_item = 0;
        $success = "false";
        $cart_item_data = "";
        $cart_item_amount = 0;
        $product_with_cousine_lists =[];
        $cart_store_id = 0;
        foreach ($cuisines as $key=>$single_cousine){
            $product_with_cousine_lists[$key]['cuisine_id'] =$single_cousine->cuisine_id;
            $product_with_cousine_lists[$key]['cuisine_name'] =$single_cousine->cuisine_name;

            $prod_list = StoreProductDetails::query()->select(
                'store_product_details.id',
                'store_product_details.name',
                'store_product_details.food_type',
                'store_product_details.image',
                'store_product_details.amount',
                'store_product_details.discount_amount',
                'store_product_details.discount_type',
                'store_product_details.slug',
                'store_product_category.id as cuisine_id',
                'store_product_category.name as cuisine_name',
                'store_product_details.description'
            )
                ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                ->where('store_product_details.store_id', $restaurant->id)
                ->where('store_product_category.id', $single_cousine->cuisine_id)
                ->orderBy('store_product_category.id', 'asc');
            $product_lists_new = $prod_list->where('store_product_details.status', 1)->get();

            $product_with_cousine_lists[$key]['product'] =   $product_lists_new;
        }
        $service_categories = ServiceCategory::query()->where('id',$restaurant->service_cat_id)->first();
        if($service_categories == Null){
            return redirect()->back();
        }

        if (Auth::guard("user")->check()) {
            $user_id = Auth::guard("user")->user()->id;
            $user_cart_store_list = UserOrderCart::query()->where('user_id', $user_id)->where('order_add_by',1)->groupBy("store_id")->first();
            if($user_cart_store_list != Null){
                $cart_store_id = $user_cart_store_list->store_id;
            }else{
                $cart_store_id = 0;
            }
            $data = $this->cartItemHtmlWithData($user_id, "array");
            $success = $data['success'];
            $cart_item = $data['cart_item'];
            $cart_item_data = $data['cart_item_data'];
            $cart_item_amount = $data['cart_item_amount'];
            $cart_total_pay = $data['total_pay'];
            $all_store_total_pay = $data['all_store_total_pay'];
            $check_fev_restaurant = 0;

            return view('front.pages.restaurant_data', compact('restaurant', 'cuisines',
                'product_lists','product_with_cousine_lists', 'res_time', 'check_fev_restaurant', 'success',
                'cart_item', 'cart_item_data', 'cart_item_amount','service_categories','cart_store_id'));
        } else {

            return view('front.pages.restaurant_data', compact('restaurant','product_with_cousine_lists', 'cuisines', 'product_lists', 'res_time', 'success', 'cart_item', 'cart_item_data', 'cart_item_amount','service_categories','cart_store_id'));
        }
    }

    public function postTempCartStore(Request $request)
    {
        $prodId = $request->get('productId');
        $cartId = $request->get('cart_id');
        /*Session::flash('error', 'Store Limit!');
        return response()->json([
            'success' => false, 'msg' => 'Store Limit!',
        ]);*/

        //get product exists ot not
        $store_product_details = StoreProductDetails::query()->where('id', $prodId)->first();
        if ($store_product_details != Null) {

            $store_id = $store_product_details->store_id;
            $user_id = Auth::guard('user')->user()->id;
//            return $user_id;

            //max store order
            $total_store_order_user = UserOrderCart::query()->select(
                DB::raw('COUNT(DISTINCT(user_order_cart.store_id)) as total_unq_store'),
                'user_order_cart.store_id as first_store_id',
                'provider_services.service_cat_id'
            )
                ->join('store_details','store_details.id','user_order_cart.store_id')
                ->join('provider_services','provider_services.id','store_details.provider_service_id')
                ->where('user_id', $user_id)
                ->where('order_add_by',1)
                ->where('store_id', '!=', $store_id)
                ->orderBy('store_id','asc')
                ->first();
//            return $total_store_order_user;

            $get_current_store = StoreDetails::query()
                ->select('provider_services.service_cat_id')
                ->join('provider_services','provider_services.id','store_details.provider_service_id')
                ->where('store_details.id',$store_id)->first();
            $current_store_service_cat_id =  $get_current_store->service_cat_id;

            if($total_store_order_user != Null)
            {

                //code for check service category
                $first_store_service_cat_id = $total_store_order_user->service_cat_id;
                if($first_store_service_cat_id > 0)
                {

                    if($get_current_store != Null){
                        //code for check service category are same or not like food delivery, grocery services etc
                        /*if($get_current_store->service_cat_id != $total_store_order_user->service_cat_id)
                        {
                            Session::flash('error', 'Sorry You can not order in multiple service at a time !');
                            return response()->json([
                                'success' => false,
                                'msg' => 'Sorry You can not order in multiple service at a time !',
                            ]);
                        }*/
                    }else{
                        Session::flash('error', 'Something went to wrong. please try again 11!');
                        return response()->json([
                            'success' => false,
                            'msg' => 'Something went to wrong. please try again !',
                        ]);
                    }

                    //code for max 3 store allow
                    if($total_store_order_user->total_unq_store >= 3){
                        Session::flash('error', 'Sorry, You can place order in maximum 3 Store!');
                        return response()->json([
                            'success' => false,
                            'msg' => 'Sorry, You can place order in maximum 3 Store!',
                        ]);
                    }
                }/*else{
                    Session::flash('error', 'Something went to wrong. please try again 222!');
                    return response()->json([
                        'success' => false,
                        'msg' => 'Something went to wrong. please try again !',
                    ]);
                }*/
            }else{
                Session::flash('error', 'Something went to wrong. please try again !');
                return response()->json([
                    'success' => false,
                    'msg' => 'Something went to wrong. please try again !',
                ]);
            }

            //max store order

            //get quantity
            $product_qty = $request->get('qty_0');

            //get options with category
            $product_options = "";
            $get_product_options = StoreProductCustomizeCategory::query()->where('product_id', $prodId)->where('selection_type', 1)->get();
            if ($get_product_options != Null) {
//                return $get_product_options;
                foreach ($get_product_options as $singel_prod_opt) {
                    $id = $singel_prod_opt->id;
                    $product_options .= $request->get('opt_' . $id) . ",";
                }
                $product_options = trim($product_options, ",");
            }

            //get topping with category
            $product_toppings = "";
            $get_product_toppings = StoreProductCustomizeCategory::query()->where('product_id', $prodId)->where('selection_type', 2)->get();
            if ($get_product_toppings != Null) {
//                return $get_product_options;
                foreach ($get_product_toppings as $singel_prod_top) {
                    $id = $singel_prod_top->id;
//                    return $request->get('top_'.$id);
                    if ($request->get('top_' . $id) != "") {
                        $product_toppings .= implode(",", $request->get('top_' . $id)) . ",";
                    }
//                    return $product_toppings;
                }
                $product_toppings = trim($product_toppings, ",");
            }

            //remove oldstore record
            $remove_data = UserOrderCart::query()->select('id')->where('user_id', $user_id)->where('store_id', '!=', $store_id)->get();
            if (!$remove_data->isEmpty()) {
                foreach ($remove_data as $remove) {
                    $remove->delete();
                }
            }

            //before enter into database check same value exist or not

            $get_cart_value = UserOrderCart::query()->where('user_id', $user_id)->where('order_add_by',1);
            $count = $get_cart_value->count();

            if ($count == 0) {
                //code for new entry

                $add_temp_value = new UserOrderCart();
                $add_temp_value->user_id = $user_id;
                $add_temp_value->product_id = $prodId;
                $add_temp_value->quantity = 1;
                $add_temp_value->size_id = $product_qty;
                $add_temp_value->option_id = $product_options;
                $add_temp_value->topping_id = $product_toppings;
                $add_temp_value->store_id = $store_id;
                $add_temp_value->service_cat_id = $current_store_service_cat_id;
                $add_temp_value->order_add_by = 1;
                $add_temp_value->save();

            } else {
                //code for update or new entry

                $chk_exist_prod = UserOrderCart::query()
                    ->where('order_add_by',1)
                    ->where('user_id', $user_id)
                    ->where('product_id', $prodId)
                    ->where('size_id', $product_qty)
                    ->where('option_id', $product_options)
                    ->where('topping_id', $product_toppings)
                    ->first();

                if ($chk_exist_prod == Null) {
                    //new entry in db for new product with new toppping
                    if ($cartId > 0) {
                        //then update exit prod
                        $add_temp_value = UserOrderCart::query()->where('id', $cartId)->first();
                        $add_temp_value->size_id = $product_qty;
                        $add_temp_value->option_id = $product_options;
                        $add_temp_value->topping_id = $product_toppings;
                        $add_temp_value->save();
                    } else {
                        $add_temp_value = new UserOrderCart();
                        $add_temp_value->user_id = $user_id;
                        $add_temp_value->product_id = $prodId;
                        $add_temp_value->quantity = 1;
                        $add_temp_value->size_id = $product_qty;
                        $add_temp_value->option_id = $product_options;
                        $add_temp_value->topping_id = $product_toppings;
                        $add_temp_value->store_id = $store_id;
                        $add_temp_value->service_cat_id = $current_store_service_cat_id;
                        $add_temp_value->order_add_by = 1;
                        $add_temp_value->save();
                    }

                } else {
                    //updae entry in db with update quntity
                    $exist_quantity = $chk_exist_prod->quantity;
                    $upd_quantity = $exist_quantity + 1;

                    $add_temp_value = UserOrderCart::query()->where('id', $chk_exist_prod->id)->first();
                    $add_temp_value->quantity = $upd_quantity;
                    $add_temp_value->save();
//                    return $upd_quantity;
                }
            }
            //code generate html for display in cart
            return $this->cartItemHtmlWithData($user_id, "json");
        } else {
            return response()->json([
                'success' => false
            ]);
        }

    }

    public function postTempCartUpdate(Request $request)
    {

        $data = $request->get('data');
        $data_arr = explode("-", $data);
//        return $data_arr;
        $operation = $data_arr[0];
        $cartId = $data_arr[1];

        $address_id = $request->get('address_id');
        $address_id = (isset($address_id) && $address_id > 0)?$address_id:0;

        if (($operation != "" && ($operation == "remove" || $operation == "add" || $operation == "sub")) && $cartId > 0) {
            $user_id = Auth::guard('user')->user()->id;

            if ($operation == "remove") {
                //remove product from cart
                $chk_cart_item = UserOrderCart::query()->where('id', $cartId)
                    ->where('user_id', $user_id)->where('order_add_by',1)->first();
                if ($chk_cart_item != Null) {
                    UserOrderCart::query()->where('id', $cartId)->delete();
                    return $this->cartItemHtmlWithData($user_id, "json");
                } else {
                    return response()->json([
                        'success' => false
                    ]);
                }

            } elseif ($operation == "add") {
                //add +1 in qunatity
                $chk_cart_item = UserOrderCart::query()->where('id', $cartId)
                    ->where('user_id', $user_id)->where('order_add_by',1)->first();
                if ($chk_cart_item != Null) {
                    $exist_quantity = $chk_cart_item->quantity;
                    $upd_quantity = $exist_quantity + 1;

                    $add_temp_value = UserOrderCart::query()->where('id', $chk_cart_item->id)->first();
                    $add_temp_value->quantity = $upd_quantity;
                    $add_temp_value->save();
                    return $this->cartItemHtmlWithData($user_id, "json",$address_id);
                } else {
                    return response()->json([
                        'success' => false
                    ]);
                }

            } elseif ($operation == "sub") {
                //add -1 in qunatity
                $chk_cart_item = UserOrderCart::query()->where('id', $cartId)
                    ->where('order_add_by',1)
                    ->where('user_id', $user_id)->first();
                if ($chk_cart_item != Null) {
                    $exist_quantity = $chk_cart_item->quantity;
                    $upd_quantity = $exist_quantity - 1;

                    if ($upd_quantity == 0) {
                        UserOrderCart::query()->where('id', $chk_cart_item->id)->delete();
                    } else {
                        $add_temp_value = UserOrderCart::query()->where('id', $chk_cart_item->id)->first();
                        $add_temp_value->quantity = $upd_quantity;
                        $add_temp_value->save();
                    }
                    return $this->cartItemHtmlWithData($user_id, "json",$address_id);
                } else {
                    return response()->json([
                        'success' => false
                    ]);
                }

            } else {
                return response()->json([
                    'success' => false
                ]);
            }
        } else {
            return response()->json([
                'success' => false
            ]);
        }
    }

    public function postTempCartDelete(Request $request) {

        $temp_cart_id = $request->get('temp_cart_id');
        $temp_cart_id_array = json_decode($temp_cart_id);

        try {
            UserOrderCart::query()->whereIn('id',$temp_cart_id_array)->delete();
        } catch (\Exception $e) {
            return response()->json([
                "success"  => false,
            ]);
        }
        return response()->json([
            "success"  => true,
        ]);
    }

    public function cartItemHtmlWithData($user_id, $type = "json", $address_id = 0,$delivery_charge_per_km=0)
    {

        if ($user_id > 0) {

            $user_cart_store_list = UserOrderCart::query()->where('user_id', $user_id)
                ->where('order_add_by',1)
                ->groupBy("store_id")
                ->get();
            $html = "";
            $store_id_arr = array();
            if($user_cart_store_list != Null){
                $cart_item = 0;
                $html="";
                $all_store_total_pay = 0;

                foreach ($user_cart_store_list as $keys =>$single_cart_store_list){

                    $total_price= 0;
                    $store_discount_amount = 0;
                    $store_packaging_charges = 0;
                    $delivery_charges= 0;
                    $total_tax = 0;
                    $total_pay= 0;


                    $users_cart_item = UserOrderCart::query()->where('user_id', $user_id)
                        ->where('order_add_by',1)
                        ->where('store_id',$single_cart_store_list->store_id)
                        ->orderBy('id','ASC')
                        ->get();
                    /*$users_cart_item = $users_cart_item->groupBy("store_id");
                    dd($users_cart_item);*/


                    if (count($users_cart_item) > 0) {

                        $cart_item =$cart_item + count($users_cart_item);

                        /*$restaurant_id = $users_cart_item[0]['store_id'];
                        $restaurant_details = StoreDetails::select('id', 'order_min_amount', 'offer_min_amount', 'offer_type', 'offer_amount', 'offer_amount as offer_percentage', 'eta_delivery_time as estimated_delivery_time', 'packaging_charges', 'slug')->where('id', $restaurant_id)->first();*/
                        $setting = ServiceSettings::query()->where('service_cat_id', $this->food_delivery)->first();
                        if ($setting != Null) {
                            $tax = $setting->tax;
                        } else {
                            $tax = 0;
                        }
                        $delivery_charges = 0;

                        $restaurant_id = $single_cart_store_list->store_id;
                        $restaurant_details = StoreDetails::query()->select('store_details.id', 'store_details.name',
                            'store_details.address_lat',
                            'store_details.address_long',
                             'store_details.order_min_amount', 'store_details.offer_min_amount', 'store_details.offer_type', 'store_details.offer_amount', 'store_details.offer_amount as offer_percentage', 'store_details.eta_delivery_time as estimated_delivery_time', 'store_details.packaging_charges', 'store_details.slug',
                            'provider_services.service_cat_id as service_cat_id')
                            ->leftJoin('provider_services','provider_services.id','store_details.provider_service_id')
                            ->leftJoin('providers','providers.id','provider_services.provider_id')
                            ->where('store_details.id', $restaurant_id)->first();

                        if ($restaurant_details != Null){

                            $maintenance_charge = 0;
                            $base_fee = 0;
                            $total_extra_product_charge = 0;


                            foreach ($users_cart_item as $singe_cart_item) {

                                $get_product_details = StoreProductDetails::query()->where('id', $singe_cart_item->product_id)->first();
                                $product_type = ($get_product_details->food_type == 1) ? "veg" : (($get_product_details->food_type == 2) ? "non-veg" : "");
                                $prod_quantity = $singe_cart_item->quantity;

                                if ($get_product_details != Null){
                                    $extra_product_charge = $get_product_details->product_charge - 0;
                                    if ($get_product_details->product_charge_type == 1) {
                                        $extra_product_charge = $extra_product_charge * $prod_quantity;
                                    }
                                    $total_extra_product_charge = $total_extra_product_charge + $extra_product_charge;
                                }


                                //code for get qty price for toping quantity
                                if ($singe_cart_item->size_id == Null || $singe_cart_item->size_id == "") {
                                    $qty_title = "";
                                    if ($get_product_details != Null) {

                                        $qty_amount = StoreProductDetails::getDiscountPrice($get_product_details->amount,$get_product_details->discount_amount,$get_product_details->discount_type);//new add RV
//                            $qty_amount = $get_product_details->amount;
                                    } else {
                                        $qty_amount = 0;
                                    }
                                } else {
//                        dd($singe_cart_item->size_id);
                                    list($qty_title, $qty_amount) = $this->ProductQtyData($singe_cart_item->size_id,$get_product_details->discount_amount,$get_product_details->discount_type);
//                        list($qty_title, $qty_amount) = $this->ProductQtyData($singe_cart_item->size_id);
                                }
//                    dd($qty_amount,$qty_title);
                                $singe_product_qty_price = $qty_amount * $prod_quantity;
                                $all_qty_title = ($qty_title != "") ? $qty_title : "";


                                //code for get qty price for selected options

                                list($opt_title, $opt_amount) = $this->ProductOptData($singe_cart_item->option_id);
                                $singe_product_opt_price = $opt_amount * $prod_quantity;

                                $all_opt_title = ($opt_title != "") ? $opt_title : "";

                                //code for get qty price for selected toppingd
                                list($top_title, $top_amount) = $this->ProductTopData($singe_cart_item->topping_id);
                                $singe_product_top_price = $top_amount * $prod_quantity;
                                $all_top_title = ($top_title != "") ? $top_title : "";


                                $singe_prod_final_price = $singe_product_qty_price + $singe_product_opt_price + $singe_product_top_price;
                                $singe_prod_final_title = $all_qty_title . "" . $all_opt_title . "" . $all_top_title;
                                //singel prodct price without dicsount
                                $singe_prod_wihtout_price = $singe_prod_final_price;

                                $single_prod_final_price = $singe_prod_final_price;


                                //total pay amount
                                $total_price += $single_prod_final_price;


                                $unique_key = rand(000, 999) . time();

                                if(in_array($restaurant_id,$store_id_arr)){
                                    $store_name = "";
                                }else{
                                    $store_id_arr[] =$restaurant_id;
                                    $store_link = route('get:restaurant_data',$restaurant_id);
                                    $store_name ='<div class="cart-store-name-main-block"><div class="cart-store-name"><a href="'.$store_link.'"> '.$restaurant_details->name.'</a></div></div>';
                                }




                                $html .= '<div class="list-group sc-cart-item-list restaurant-1 5" id="cart-' . $unique_key . '">
                                    '.$store_name.'
                                    <div class="sc-cart-item list-group-item" id="' . $unique_key . '">
                                        <button type="button" class="sc-cart-remove removeProduct" id="remove-' . $singe_cart_item->id . '" >x</button>
                                        <div class="' . $product_type . '">
                                            <div class="info">
                                                <h4 class="list-group-item-heading productTitle" id="' . $singe_cart_item->id . '" data-storeId="' . $singe_cart_item->store_id . '" data-productId="' . $singe_cart_item->product_id . '" > ' . $get_product_details->name . '</h4>
                                                <span class="toppingsData"> ' . trim($singe_prod_final_title,","). '</span>
                                            </div>
                                        </div>
                                        <button class="minus cart-minus minusProduct" id="sub-' . $singe_cart_item->id . '"></button><input type="text" min="0" max="100" class="sc-cart-item-qty ' . $unique_key . '" id="qty-' . $unique_key . '" value="' . $prod_quantity . '" readonly=""> <button class="minus cart-plus plusProduct" id="add-' . $singe_cart_item->id . '"></button> <span class="sc-cart-item-amount amount currency amount' . $unique_key . '">' . $single_prod_final_price . '</span> <input type="hidden" class="cart_item" id="cart_item_' . $unique_key . '" >
                                    </div>
                                </div>';



                            }


                            //$html .= <div class="info" style="height: 45px;"> => class="info"
                            //calculation of store offer on final price
                            $offer_min_amount = $restaurant_details->offer_min_amount;
                            $offer_amount = $restaurant_details->offer_amount;
                            $offer_type = $restaurant_details->offer_type;
                            $store_packaging_charges = $restaurant_details->packaging_charges;
                            $delivery_charges = 0;
                            if($address_id > 0)
                            {
                                //code for calculate delivery charges
                                $address = UserAddress::query()->where('id', $address_id)->first();
                                if ($address != Null) {

                                    $address_lat_long = array_map('trim', explode(",", $address->lat_long));

                                    $address_lat = trim($address_lat_long[0]);
                                    $address_long = trim($address_lat_long[1]);

                                    $user_id = Auth::guard("user")->user()->id;

//                                    $delivery_charge_per_km = 0;
                                    $user_cart_store_list = UserOrderCart::query()->where('user_id', $user_id)->where('order_add_by',1)->groupBy("store_id")->get();
                                    if ($user_cart_store_list->isNotEmpty()){
                                        foreach ($user_cart_store_list as $key => $user_cart_store) {
                                            $delivery_lat = isset($address_lat) ? $address_lat : 0;
                                            $delivery_long = isset($address_long) ? $address_long : 0;
                                            $area_id = 0;
                                            $store_name = StoreDetails::query()->select('name')->where('id', $user_cart_store->store_id)->first();
                                            $name = "";
                                            if ($store_name != Null) {
                                                $name = $store_name->name;
                                            }

                                            $service_cat_id =  $user_cart_store->service_cat_id;
                                            $get_delivery_charge =  ServiceSettings::query()->where('service_cat_id',$service_cat_id)->first();
                                            $delivery_charge_per_km =  $delivery_charge_per_km;
                                            /*if($get_delivery_charge != Null)
                                            {
                                                $delivery_charge_per_km = $get_delivery_charge->delivery_charge;
                                            }*/
                                            /*if ($delivery_lat != Null && $delivery_long != Null) {
                                                $area_wise_del_charge = RestrictedArea::query()->where('status', '1')->orderBy('id', 'desc')->get();

                                                foreach ($area_wise_del_charge as $singel_area_wise_charge) {
                                                    $vertices_x = explode(",", $singel_area_wise_charge->address_lat);    // x-coordinates of the vertices of the polygon
                                                    $vertices_y = explode(",", $singel_area_wise_charge->address_long); // y-coordinates of the

                                                    $points_polygon = count($vertices_x);  // number vertices - zero-based array
                                                    $longitude_x = $delivery_lat;  // x-coordinate of the point to test
                                                    $latitude_y = $delivery_long;   // y-coordinate of the point to test

                                                    if ($this->userClassapi->is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
                                                        $area_id = $singel_area_wise_charge->id;
                                                        break;
                                                    }
                                                }
                                            }

                                            if ($area_id > 0) {
                                                $get_area = AreaWiseVehicleCost::query()->where('id', $area_id)->first();
                                                if ($get_area != Null) {
                                                    $delivery_charge_per_km = isset($get_area->cost_for_km)?$get_area->cost_for_km:0;
                                                }
                                            }*/
                                        }
                                    }


                                    $origin = urlencode($restaurant_details->address_lat . "," . $restaurant_details->address_long);
                                    $get_coordinates = $this->userClassapi->get_coordinates($origin, $address->lat_long);
                                    $coordinates_check = json_decode($get_coordinates, TRUE);
                                    if ($coordinates_check != 0) {
                                        $total_distance = 0;
                                        $delivery_charges = 0;
                                        if (count($coordinates_check['routes']) > 0) {
                                            $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);
                                        }
                                        $total_distance = ($total_distance * 0.001);
                                        $delivery_cost = $this->userClassapi->checkDeliveryCharge($total_distance,$service_cat_id);
//                                        $delivery_charges =  $total_distance * $delivery_charge_per_km;
                                        $delivery_charges =  $delivery_cost;
                                    }
                                } else{
                                    return  "error";
                                }
                            }



                            $delivery_cost = $delivery_charges ;
                            $partner_fee = $total_extra_product_charge + $restaurant_details->fixed_charge;
                            $extra_charges = $delivery_cost + $store_packaging_charges + $partner_fee + $maintenance_charge;

                            $store_discount_amount = 0;

                            if (($offer_min_amount != "0") && ($offer_amount != "0") && ($offer_type != "0")) {
                                if ($total_price >= $offer_min_amount) {
                                    if ($offer_type == 1) {
//                            $store_discount_amount = $total_price - $offer_amount;
                                        $store_discount_amount = $offer_amount;
                                    } else {
                                        $store_discount_amount = ((($total_price) * ($offer_amount)) / 100);
                                    }
                                }
                            }

                            if (($offer_min_amount == 0) && ($offer_amount != "0") && ($offer_type != "0")) {
                                if ($offer_type == 1) {
//                        $store_discount_amount = $total_price - $offer_amount;
                                    $store_discount_amount = $offer_amount;
                                } else {
                                    $store_discount_amount = ((($total_price) * ($offer_amount)) / 100);
                                }
                            }

                            //tax calucation for total price
//                            $total_pay = ($total_price - $store_discount_amount) + $delivery_charges + $base_fee + $store_packaging_charges;
                            $total_pay = ($total_price - $store_discount_amount) + $delivery_charges + $store_packaging_charges;

                            $total_tax = 0;
                            if ($tax > 0) {
                                $total_tax =round( ($total_pay * $tax) / 100,2);
                            }
                            $total_pay = $total_pay + $total_tax;

                            $all_store_total_pay = $all_store_total_pay + $total_pay;

                            $html .='<div class="panel-footer sc-cart-toolbar">
                                        <div class="sc-cart-summary">
                                            <div class="sc-cart-summary-subtotal">
                                                <span>'.__('messages.144').'</span>
                                                <span class="sc-cart-subtotal currency" id="item-total">'.$total_price.'</span>
                                            </div>
                                            <div class="sc-cart-summary-subtotal">
                                                <span>'.__('messages.145').'</span>
                                                <span class="sc-cart-subtotal currency" id="total-discount">'.round($store_discount_amount,2).'</span>
                                            </div>
                                            <div class="sc-cart-summary-subtotal">
                                                <span>'.__('messages.146').'</span>
                                                <span class="sc-cart-subtotal currency" id="item-packaging-charges">'.$total_tax.'</span>
                                            </div>
                                            <div class="sc-cart-summary-subtotal">
                                                <span>'.__('messages.147').'</span>
                                                <span class="sc-cart-subtotal currency" id="item-packaging-charges">'.round($delivery_cost,2).'</span>
                                            </div>
                                            <div class="sc-cart-summary-subtotal">
                                                <span>'.__('messages.259').'</span>
                                                <span class="sc-cart-subtotal currency" id="item-packaging-charges">'.round($store_packaging_charges,2).'</span>
                                            </div>
                                        </div>
                                    </div>';
                        } else {
                            //delete cart
                        }
                    }
                    else {
                        if ($type == "json") {
                            return response()->json([
                                'success' => true,
                                "cart_item" => 0,
                                "cart_item_data" => "",
                                "cart_item_amount" => 0,
                                'discount' => 0,
                                'packaging_charges' => 0,
                                'delivery_charges' => 0,
                                'tax' => 0,
                                'total_pay' => 0,
                                'all_store_total_pay' => 0,

                            ]);
                        } else {
                            return array('success' => true,
                                "cart_item" => 0,
                                "cart_item_data" => "",
                                "cart_item_amount" => 0,
                                'discount' => 0,
                                'packaging_charges' => 0,
                                'delivery_charges' => 0,
                                'tax' => 0,
                                'total_pay' => 0,
                                'all_store_total_pay' => 0
                            );
                        }
                    }
                }
                if ($type == "json") {
                    return response()->json([
                        'success' => true,
                        "cart_item" => isset($cart_item)?$cart_item:0,
                        "cart_item_data" => isset($html)?$html:"",
                        "cart_item_amount" => isset($total_price)?round($total_price, 2):0,
                        'discount' => isset($store_discount_amount)?round($store_discount_amount, 2):0,
                        'packaging_charges' => isset($store_packaging_charges)?round($store_packaging_charges, 2):0,
                        'delivery_charges' => isset($delivery_charges)?round($delivery_charges, 2):0,
                        'tax' => isset($total_tax)?round($total_tax, 2):0,
                        'total_pay' => isset($total_pay)?round($total_pay, 2):0,
                        'all_store_total_pay' => isset($all_store_total_pay)?round($all_store_total_pay, 2):0,
                    ]);
                } else {
                    return array('success' => true,
                        "cart_item" => isset($cart_item)?$cart_item:0,
                        "cart_item_data" => isset($html)?json_encode($html):"",
                        "cart_item_amount" => isset($total_price)?round($total_price, 2):0,
                        'discount' => isset($store_discount_amount)?round($store_discount_amount, 2):0,
                        'packaging_charges' => isset($store_packaging_charges)?round($store_packaging_charges, 2):0,
                        'delivery_charges' => isset($delivery_charges)?round($delivery_charges, 2):0,
                        'tax' => isset($total_tax)?round($total_tax, 2):0,
                        'total_pay' => isset($total_pay)?round($total_pay, 2):0,
                        'all_store_total_pay' => isset($all_store_total_pay)?round($all_store_total_pay, 2):0,
                    );
                }
            }
        } else {
            if ($type == "json") {
                return response()->json([
                    'success' => true,
                    "cart_item" => 0,
                    "cart_item_data" => "",
                    "cart_item_amount" => 0,
                    'discount' => 0,
                    'packaging_charges' => 0,
                    'delivery_charges' => 0,
                    'tax' => 0,
                    'total_pay' => 0,
                    'all_store_total_pay' => 0
                ]);
            } else {
                return array('success' => true,
                    "cart_item" => 0,
                    "cart_item_data" => "",
                    "cart_item_amount" => 0,
                    'discount' => 0,
                    'packaging_charges' => 0,
                    'delivery_charges' => 0,
                    'tax' => 0,
                    'total_pay' => 0,
                    'all_store_total_pay' => 0
                );
            }
        }
    }

//    public function ProductQtyData($prod_qty)
    public function ProductQtyData($prod_qty,$discount_amount,$discount_type)
    {

        $storeProdcutSize = StoreProductSize::query()->where('id', $prod_qty)->first();
        if ($storeProdcutSize != Null) {
            $qty_title = $storeProdcutSize->name;
            $qty_amount = StoreProductDetails::getDiscountPrice($storeProdcutSize->amount,$discount_amount,$discount_type);
//            $qty_amount = $storeProdcutSize->amount;
        } else {
            $qty_title = "";
            $qty_amount = "";
        }
        return array($qty_title, $qty_amount);
    }

    public function ProductOptData($prod_opt)
    {

        $prod_opt_arr = explode(",", $prod_opt);
        $opt_title = "";
        $opt_amount = 0;
        $total_opt = count($prod_opt_arr);
        if ($total_opt > 0) {
            for ($i = 0; $i < $total_opt; $i++) {
                $get_options_details = StoreProductOptions::query()->where('id', $prod_opt_arr[$i])->first();
                if ($get_options_details != Null) {
//                    $opt_title .= ", " . $get_options_details->name . ", ";
                    $opt_title .= ", " . $get_options_details->name;
                    $opt_amount += $get_options_details->amount;
                }
            }
        } else {
            $opt_title = "";
            $opt_amount = 0;
        }
        return array($opt_title, $opt_amount);
    }

    public function ProductTopData($prod_top)
    {

        $prod_top_arr = explode(",", $prod_top);
        $top_title = "";
        $top_amount = 0;
        $total_top = count($prod_top_arr);
        if ($total_top > 0) {
            for ($i = 0; $i < $total_top; $i++) {
                $get_toppings_details = StoreProductToppings::query()->where('id', $prod_top_arr[$i])->first();
                if ($get_toppings_details != Null) {
                    if ($i == $total_top - 1 ){
                        $top_title .= ", " . $get_toppings_details->name . ".";
                    }else{
                        $top_title .= ", " . $get_toppings_details->name . "";
                    }
                    $top_amount += $get_toppings_details->amount;
                }
            }
        } else {
            $top_title = "";
            $top_amount = 0;
        }
        return array($top_title, $top_amount);
    }

    public function getCheckout()
    {
        // The user is logged in...
        $temp_cart_data = UserOrderCart::query()->select('user_order_cart.user_id', 'user_order_cart.product_id', 'user_order_cart.quantity',
            'user_order_cart.store_id as restaurant_id', 'store_product_details.id', 'store_product_details.name', 'user_order_cart.service_cat_id as service_cat_id',
            'store_product_details.food_type as product_type', 'store_product_details.amount as price', 'store_product_details.discount_amount as discount',
            'store_product_details.discount_type')
            ->join('store_product_details', 'store_product_details.id', '=', 'user_order_cart.product_id')
            ->where('user_order_cart.user_id', Auth::guard("user")->user()->id)
            ->where('order_add_by',1)
            ->orderBy('user_order_cart.id', 'asc')
            ->where('store_product_details.status', 1)
            ->get();

        $temp_cart_count = count($temp_cart_data);
        $empty_cart = 0;
        if ($temp_cart_count == 0) {
            $temp_cart_data = UserOrderCart::query()->select('user_order_cart.id as temp_cart_id','user_order_cart.user_id', 'user_order_cart.product_id', 'user_order_cart.quantity', 'user_order_cart.store_id as restaurant_id', 'store_product_details.id', 'store_product_details.name', 'store_product_details.food_type as product_type', 'store_product_details.amount as price', 'store_product_details.discount_amount as discount', 'store_product_details.discount_type', 'store_product_details.name as product_name')
                ->join('store_product_details', 'store_product_details.id', '=', 'user_order_cart.product_id')
                ->where('user_order_cart.user_id', Auth::guard("user")->user()->id)
                ->where('order_add_by',1)
                ->orderBy('user_order_cart.id', 'asc')
                ->get()->toArray();

            $empty_cart = 1;
            $temp_cart_count = count($temp_cart_data);
            if($temp_cart_count  != 0)
            {
                $temp_cart_id = json_encode(array_column($temp_cart_data,'temp_cart_id'));

                $all_unavail_procduct = array_column($temp_cart_data,'product_name');
                $all_unavail_procduct_list =  implode(",",$all_unavail_procduct);
                $all_unavail_procduct_list = ($all_unavail_procduct_list != "")?$all_unavail_procduct_list:"";

                return redirect()->route('get:restaurant_data',$temp_cart_data[0]['restaurant_id'])->with('cart_error','Sorry, <b>'.$all_unavail_procduct_list.'</b> product is not avaialbel at this moment')
                    ->with('temp_cart_id',$temp_cart_id);
            }else{
                $empty_cart = 1;
                /*Session::flash('error', "Sorry, your cart is empty");
                return redirect()->route('get:homepage');*/
                return view('front.pages.checkout', compact('empty_cart'));
            }

        } else {

            $restaurant_id = $temp_cart_data[0]['restaurant_id'];
            $service_cat_id = $temp_cart_data[0]['service_cat_id'];

            $restaurant_details = StoreDetails::query()->select('id', 'order_min_amount', 'offer_min_amount', 'offer_type', 'offer_amount', 'offer_amount as offer_percentage', 'eta_delivery_time as estimated_delivery_time', 'packaging_charges', 'slug')->where('id', $restaurant_id)->first();
            $user_address = UserAddress::query()->where('user_id', Auth::guard("user")->user()->id)->where('status', 1)->get();
            $user_address_count = $user_address->count();



//            $setting = Settings::first();
            $setting = ServiceSettings::query()->where('service_cat_id', $service_cat_id)->first();

            if ($setting != Null) {
                $tax = $setting->tax;
            } else {
                $tax = 0;
            }
            $delivery_charges = 0;
            $data = $this->cartItemHtmlWithData(Auth::guard("user")->user()->id, "array");
            $success = $data['success'];
            $cart_item = $data['cart_item'];
            $cart_item_data = $data['cart_item_data'];
            $cart_item_amount = $data['cart_item_amount'];
            $discount = $data['discount'];
            $packaging_charges = $data['packaging_charges'];
            $delivery_charges = $data['delivery_charges'];
            $tax = $data['tax'];
            $total_pay = $data['total_pay'];
            $all_store_total_pay = $data['all_store_total_pay'];
            $medical_service = $this->medicineDelivery;

            $card_list = UserCardDetails::query()->select('card_details.id', 'card_details.holder_name as card_holder_name', 'card_details.card_number as card_number')
                ->where('user_id', Auth::guard("user")->user()->id)
                ->orderBy('id','desc')
                ->get();

            return view('front.pages.checkout', compact('success', 'card_list', 'cart_item', 'cart_item_data', 'discount', 'packaging_charges', 'cart_item_amount', 'tax', 'total_pay', 'delivery_charges', 'restaurant_details', 'user_address', 'user_address_count', 'restaurant_details','all_store_total_pay','service_cat_id','medical_service'));
        }
    }

    public function postNewAddress(UserAddressRequest $request)
    {
        $count = UserAddress::query()->where('user_id', Auth::guard("user")->user()->id)->count();
        $lat_long = $request->get('lat') . ',' . $request->get('long');
        $check_duplicate = UserAddress::query()->where('user_id', Auth::guard("user")->user()->id)
            ->where('lat_long', 'LIKE', "%{$lat_long}%")
            ->where('status','=',1)
//            ->where('latitude', $request->get('lat'))->where('longitude', $request->get('long'))
            ->first();
        if ($check_duplicate == Null) {
            if ($count <= 4) {
                $new_address = new UserAddress();
            } else {
                $new_address = UserAddress::query()->where('user_id', Auth::guard("user")->user()->id)->where('status', 0)->first();
            }
            $new_address->user_id = Auth::guard("user")->user()->id;
            $new_address->address = $request->get('new_address');

            $new_address->lat_long = $request->get('lat') . ',' . $request->get('long');
//            $new_address->longitude = $request->get('long');
            $new_address->address_type = $request->get('address_type');
            $new_address->landmark = $request->get('landmark');
            $new_address->flat_no = $request->get('flat_no');
            $new_address->status = 1;
            $new_address->save();
            $address = UserAddress::query()->where('id', $new_address->id)->first();
        } else {
            $check_duplicate->address = $request->get('new_address');
            $check_duplicate->address_type = $request->get('address_type');
            $check_duplicate->landmark = $request->get('landmark');
            $check_duplicate->flat_no = $request->get('flat_no');
            $check_duplicate->save();
            $address = UserAddress::query()->where('id', $check_duplicate->id)->first();
        }
        if ($check_duplicate == Null) {
            return response()->json([
                'success' => true,
                'address_id' => $address->id,
                'address_name' => $address->address,
                'address_type' => $address->address_type
            ]);
        } else {
            return response()->json([
                'success' => false,
                'address_id' => $address->id,
            ]);
        }

    }

    public function postDeliveryCost(Request $request) {

        if($request->get('user_taken_type') == "y")
        {
            $user_id = Auth::guard("user")->user()->id;
            $address_id = 0;
            $delivery_charge_per_km = 0;
            $data = $this->cartItemHtmlWithData($user_id, "json", $address_id,$delivery_charge_per_km);
            return $data;
        }else{
            $address = UserAddress::query()->where('id', $request->get('address_id'))->first();
            if ($address == Null) {
                Session::flash('error', __('messages.150'));
                return response()->json([
                    'success' => false,
                    //'msg' => 'Delivery Address not found!',
                    'msg' => __('messages.150'),
                ]);
            }

            $address_lat_long = array_map('trim', explode(",", $address->lat_long));
            if (count($address_lat_long) != 2) {
                Session::flash('error', __('messages.150'));
                return response()->json([
                    'success' => false,
                    'msg' => __('messages.150'),
                ]);
            }
            $address_lat = trim($address_lat_long[0]);
            $address_long = trim($address_lat_long[1]);

            $user_id = Auth::guard("user")->user()->id;
            $store_id = $request->get('store_id');
            $delivery_charge_per_km = 0;
            $user_cart_store_list = UserOrderCart::query()->where('user_id', $user_id)->where('order_add_by',1)->groupBy("store_id")->get();
            if ($user_cart_store_list->isNotEmpty()){
                /*foreach ($user_cart_store_list as $key => $user_cart_store) {
                    $delivery_lat = isset($address_lat) ? $address_lat : 0;
                    $delivery_long = isset($address_long) ? $address_long : 0;
                    $area_id = 0;
                    $store_name = StoreDetails::query()->select('name')->where('id', $user_cart_store->store_id)->first();
                    $name = "";
                    if ($store_name != Null) {
                        $name = $store_name->name;
                    }

                    if ($delivery_lat != Null && $delivery_long != Null) {
                        $area_wise_del_charge = RestrictedArea::query()->where('status', '1')->orderBy('id', 'desc')->get();

                        foreach ($area_wise_del_charge as $singel_area_wise_charge) {
                            $vertices_x = explode(",", $singel_area_wise_charge->address_lat);    // x-coordinates of the vertices of the polygon
                            $vertices_y = explode(",", $singel_area_wise_charge->address_long); // y-coordinates of the

                            $points_polygon = count($vertices_x);  // number vertices - zero-based array
                            $longitude_x = $delivery_lat;  // x-coordinate of the point to test
                            $latitude_y = $delivery_long;   // y-coordinate of the point to test

                            if ($this->userClassapi->is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
                                $area_id = $singel_area_wise_charge->id;
                                break;
                            }
                        }
                    }

                    if ($area_id == 0) {
                        Session::flash('error', '<b>' . $name . ' - ' . __('messages.151') . ' </b><br/> ' . __('messages.152'));
                        return response()->json([
                            'success' => false,
                            //'msg' => 'Your delivery address out of '.$name.' store delivery range! Please change your delivery address.',
                            'msg' => $name . ' - ' . __('messages.151') . ' ' . __('messages.152'),
                        ]);
                    }
                    $get_area = AreaWiseVehicleCost::query()->where('id', $area_id)->first();
                    if ($get_area == Null) {
                        Session::flash('error', '<b>' . $name . ' - ' . __('messages.151') . ' </b><br/> ' . __('messages.152'));
                        return response()->json([
                            'success' => false,
                            //'msg' => 'Your delivery address out of '.$name.' store delivery range! Please change your delivery address.',
                            'msg' => $name . ' - ' . __('messages.151') . ' ' . __('messages.152'),
                        ]);
                    }
                    $delivery_charge_per_km = $get_area->cost_for_km;
                }*/

                foreach ($user_cart_store_list as $key => $user_cart_store){
                    $check_near_delivery_address = StoreDetails::query()->select('store_details.id','store_details.name', 'store_details.address_lat', 'store_details.address_long', 'providers.service_radius'
                        , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                    +COS( ' . $address_lat . ' * pi()/180)
                    * COS(store_details.address_lat * pi()/180)
                    * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
                    )
                        ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                        ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                        //->where('provider_services.service_cat_id', $this->food_delivery)
                        ->where('store_details.id', $user_cart_store->store_id)
                        ->havingRaw('providers.service_radius > distance')
                        ->first();

                    if ($check_near_delivery_address == Null) {
                        $store_name = StoreDetails::query()->select('name')->where('id',$user_cart_store->store_id)->first();
                        $name ="";
                        if($store_name != Null){
                            $name = $store_name->name;
                        }

                        //Session::flash('error', '<b>'.$name.' - Store </b><br/> Your delivery address out of store delivery range! Please change your delivery address.');
                        Session::flash('error', '<b>'.$name.' - '.__('messages.151').' </b><br/> '.__('messages.152'));
                        return response()->json([
                            'success' => false,
                            //'msg' => 'Your delivery address out of '.$name.' store delivery range! Please change your delivery address.',
                            'msg' => $name. ' - '.__('messages.151').' '.__('messages.152'),
                        ]);
                    }
                    $total_distance = isset($check_near_delivery_address->distance)?$check_near_delivery_address->distance:0;
                    $service_cat_id =  $user_cart_store->service_cat_id;
                    $get_delivery_charge =  DeliveryCharges::query()->where('service_cat_id',$service_cat_id)
                                            ->where('start_limit','>=',$total_distance)
                                            ->where('close_limit','<=',$total_distance)
                                            ->first();
                    $delivery_charge_per_km = 0;
                    $delivery_charge_per_km = $this->userClassapi->checkDeliveryCharge($total_distance,$service_cat_id);
                    if($get_delivery_charge != Null)
                    {
                        $delivery_charge_per_km = $get_delivery_charge->charges;
                    }

                }
            }

            $address_id = $request->get('address_id');
            $data = $this->cartItemHtmlWithData($user_id, "json", $address_id,$delivery_charge_per_km);
            if ($data == "error"){
                return response()->json([
                    'success' => false,
                    //'msg' => 'please add proper delivery address!',
                    'msg' => __('messages.154'),
                ]);
            }
            return $data;
        }

    }

    public function postPlaceOrder(Request $request)
    {
        $user_id = Auth::guard("user")->user()->id;
        $validator = Validator::make($request->all(), [

            'form_payment_type' => 'Required',
            'form_card_id' => 'required_if:payment_type,==,2',
            'form_payment_method_type' => 'nullable|in:0,1,2',
            'user_taken_type' => 'nullable|in:1,2',
            'form_address_delivery' => 'Required|required_if:user_taken_type,==,1',
        ]);


        if ($validator->fails()) {
            $failedRules = $validator->failed();

            if (isset($failedRules['form_payment_type']['Required'])) {
                Session::flash('error', __('messages.155'));
                //Session::flash('error', 'Please add Payment type before place order');
            }
            if (isset($failedRules['form_address_delivery']['Required'])) {
                Session::flash('error', __('messages.156'));
                //Session::flash('error', 'Please add delivery address before place order');
            }
            return redirect()->back();
        }

        $get_cart_category = UserOrderCart::query()->where('user_id',$user_id)->where('order_add_by','1')->first();
        if($get_cart_category == Null){
            Session::flash('error', __('messages.157'));
            //Session::flash('error', 'Cart Product not found!');
            return redirect()->back();
        }

        $get_category = ServiceCategory::query()->where('id', $get_cart_category->service_cat_id)->first();
        $store_id = $get_cart_category->store_id;
        if ($get_category == Null) {
            //Session::flash('error', 'Service category not found!');
            Session::flash('error', __('messages.158'));
            return redirect()->back();
        }
        $service_category_id = $get_category->id;

        $address_id = $request->get('form_address_delivery');
        $payment_type = $request->get('form_payment_type');

        $card_id = $request->get('form_card_id') - 0;
        $payment_method_type = $request->get('form_payment_method_type') - 0;

        $mb_way_payment_contact_number = $request->get('mb_way_payment_contact_number');
        $promocode_id = 0;
        $user_taken_type = $request->get('user_taken_type') - 0;;

        $order_add_by = 1;
        $ret_type = "json";
        $product_list = $this->userClassapi->getCartProductList($user_id,$order_add_by,$ret_type);

        $product_list_array = json_decode($product_list,true);


        $settings = GeneralSettings::query()->first();
        if ($settings == Null) {
            Session::flash('error', __('messages.163'));
            return redirect()->back();
        }
        //comment code for payment gateway
        if ($payment_method_type == 2) {

            if ($settings->is_payment_gateway == 1) {
                if ($settings->paypal_sandbox == Null && $settings->paypal_client_id == Null && $settings->paypal_client_secret_key == Null) {
                    Session::flash('error', __('messages.163'));
                    return redirect()->back();
                }
            }
        }
        if ($service_category_id == $this->medicineDelivery) {
            $validator = Validator::make($request->all(), [
                "prescription" => "required",
            ]);

            if ($validator->fails()) {
                Session::flash('error',$validator->errors()->first());
                return redirect()->back();
            }
        }

        $user_details = User::query()->where('id',$user_id)->first();
        if($user_details != Null)
        {
            $store_details = StoreDetails::query()->where('id', $store_id)->first();
            if ($store_details != Null) {
                $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
                if ($user_currency == Null) {
                    $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
                }
                $currency = $user_currency != Null ? $user_currency->ratio : 1;
                $currency_code = $user_currency != Null ? $user_currency->currency_code : '';
                $currency_symbol = $user_currency != Null ? $user_currency->symbol : '';

                $service_status = ProviderServices::query()->where('id', $store_details->provider_service_id)->where('service_cat_id', $service_category_id)->where('status', 1)->where('current_status', 1)->first();
                if ($service_status == Null) {
                    Session::flash('error', __('messages.170'));
                    return redirect()->back();
                }
                $get_category = ServiceCategory::query()->where('id', $service_category_id)->first();
                if ($get_category == Null) {
                    Session::flash('error', __('messages.161'));
                    return redirect()->back();
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
                        Session::flash('error', __('messages.170'));
                        return redirect()->back();
                    }
                } else {
                    Session::flash('error', __('messages.170'));
                    return redirect()->back();
                }
                //check store open close

                if ($user_taken_type != 2) {
                    //                $address = json_decode($request->get('address'), true);
                    $address_data = UserAddress::query()->where('id',$address_id)->where('user_id',$user_id)->first();

                    if ($address_data ==  Null) {
                        Session::flash('error', __('messages.161'));
                        return redirect()->back();
                    }
                    $address = $address_data['address'];
                    $flat_no = ($address_data['flat_no']!="")?$address_data['flat_no']:"";
                    $landmark = ($address_data['landmark']!="")?$address_data['landmark']:"";


                    $address_latlong = explode(",",$address_data['lat_long']);

                    $address_lat = ($address_latlong[0])?$address_latlong[0]:"";
                    $address_lang = ($address_latlong[1])?$address_latlong[1]:"";

                    $area_details = RestrictedArea::query()->where('status',1)->get();
                    $latitudes = $longitudes = '';
                    foreach ($area_details as $area){
                        $latitudes = $latitudes . $area->latitude . ',';
                        $longitudes = $longitudes . $area->longitude . ',';
                    }
                    $restricted_lat = explode(',',substr($latitudes, 0, -1));
                    $restricted_long = explode(',',substr($longitudes, 0, -1));
                    $points_polygon = count($restricted_lat);
                    $adminClassApi = new AdminClass();
                    if($adminClassApi->is_in_restricted_area($points_polygon,$restricted_lat,$restricted_long,$address_lat,$address_lang)) {
                        Session::flash('error', __('messages.260'));
                        return redirect()->back();
                    }

                    if ($address == Null || $address_lat == Null || $address_lang == Null) {
                        //if ($address['address'] == Null || $address['lat'] == Null || $address['long'] == Null || $address['flat_no'] == Null || $address['landmark'] == Null) {
                        Session::flash('error', __('messages.161'));
                        return redirect()->back();
                    }
                    $address_lat = trim($address_lat);
                    $address_long = trim($address_lang);
                    $check_near_delivery_address = StoreDetails::select('store_details.id', 'providers.service_radius'
                        , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                                +COS( ' . $address_lat . ' * pi()/180)
                                * COS(store_details.address_lat * pi()/180)
                                * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
                    )
                        ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                        ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                        ->where('provider_services.service_cat_id', $service_category_id)
                        ->where('store_details.id', $store_details->id)
                        ->havingRaw('providers.service_radius > distance')
                        ->first();
                    if ($check_near_delivery_address == Null) {
                        Session::flash('error', __('messages.153'));
                        return redirect()->back();
                    }
                }

                $service_settings = ServiceSettings::query()->where('service_cat_id', $service_category_id)->first();
                if ($service_settings != Null) {
                    $admin_commission = $service_settings->admin_commission;
                    if ($store_details->store_wise_commission != 0) {
                        $admin_commission = $store_details->store_wise_commission;
                    }
                    $tax_cost = $service_settings->tax != 0 ? $service_settings->tax : 0;
                } else {
                    $admin_commission = 0;
                    $tax_cost = 0;
                }
//                $delivery_charge_per_km = isset($service_settings->delivery_charge)?$service_settings->delivery_charge:0;

//                $total_distance = isset($check_near_delivery_address->distance)?$check_near_delivery_address->distance:0;
                $service_cat_id =  $service_category_id;
//                $get_delivery_charge =  DeliveryCharges::query()->where('service_cat_id',$service_cat_id)
//                    ->where('start_limit','>=',$total_distance)
//                    ->where('close_limit','<=',$total_distance)
//                    ->first();
//                $delivery_charge_per_km = 0;
//                $delivery_charge_per_km = $this->userClassapi->checkDeliveryCharge($total_distance,$service_cat_id);
//                if($get_delivery_charge != Null)
//                {
//                    $delivery_charge_per_km = $get_delivery_charge->charges;
//                }

                $area_id = 0 ;
                if ($user_taken_type != 2) {
                    $delivery_lat = isset($address_lat) ? $address_lat : 0;
                    $delivery_long = isset($address_long) ? $address_long : 0;
                    /*$area_id = 0;
                    if ($delivery_lat != Null && $delivery_long != Null) {
                        $area_wise_del_charge = RestrictedArea::query()->where('status', '1')->orderBy('id', 'desc')->get();
                        foreach ($area_wise_del_charge as $singel_area_wise_charge) {
                            $vertices_x = explode(",", $singel_area_wise_charge->address_lat);    // x-coordinates of the vertices of the polygon
                            $vertices_y = explode(",", $singel_area_wise_charge->address_long); // y-coordinates of the

                            $points_polygon = count($vertices_x);  // number vertices - zero-based array
                            $longitude_x = $delivery_lat;  // x-coordinate of the point to test
                            $latitude_y = $delivery_long;   // y-coordinate of the point to test

                            if ($this->userClassapi->is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
                                $area_id = $singel_area_wise_charge->id;
                                break;
                            }
                        }
                    }*/
                    /*if ($area_id == 0) {

                        Session::flash('error', __('messages.253'));
                        return redirect()->back();

                    }
                    $get_area = AreaWiseVehicleCost::query()->where('id', $area_id)->first();
                    if ($get_area == Null) {

                        Session::flash('error', __('messages.253'));
                        return redirect()->back();

                    }*/
//                    $delivery_charge_per_km = $get_area->cost_for_km;
                }

                //            $product_list = json_decode($request->get('product_list'), true);
                $product_list = $product_list_array;
                $product_price = [];
                $order_product_list = [];
                $order_product_list_string = [];
                $discount_cost = 0;
                $total_price = 0;

                $unavailable_products = [];
                $unavailable_products_names = "";
                foreach ($product_list as $product) {
                    if ($product['product_id'] != Null && $product['product_quantity'] != Null) {
                        $product_details = StoreProductDetails::query()->where('id', $product['product_id'])->first();
//                        $product_details = StoreProductDetails::query()->where('id', $product['product_id'])->where('status', 1)->first();
                        if ($product_details != Null && ($product_details->status == 1 || $product_details->status == "1" )) {
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
                                                    "message" => "your not selected valid size",
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
                                                "message" => "your not selected valid size",
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
                        else{
                            UserOrderCart::query()->where('user_id','=',$user_id)->where('product_id','=',$product['product_id'])->delete();
                            $unavailable_products[] = $product['product_id'];
                            $unavailable_products_names .= $product_details->name ." ,";
                        }
                    }
                }

                $get_count_product = count($unavailable_products);
                $unavailable_products = implode(',',$unavailable_products);
                if(count($order_product_list) == 0 || $get_count_product > 0){

                    Session::flash('error'," <b>".trim($unavailable_products_names,",")." </b> ". __('messages.254'));
                    return redirect()->back();

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
                if ($store_details->order_min_amount > $total_price) {
                    Session::flash('error', __('messages.255'). $currency_symbol . " " . round(($store_details->order_min_amount * $currency), 1));
                    return redirect()->back();

                }

                $place_order = new UserProductBooking();
                $place_order->user_id = $user_id;
                $place_order->store_id = $store_details->id;
                $place_order->service_cat_id = $get_category->id;
//                $place_order->area_id = $area_id;
                $place_order->order_from = 1;
                //address json decode
                $delivery_charge = 0;
                if ($user_taken_type != 2) {
                    if ($user_taken_type != 2) {
                        $address_lat = trim($address_lat);
                        $address_long = trim($address_long);

                        $place_order->delivery_address = $address;
                        $place_order->lat_long = $address_lat . ',' . $address_long;
                        $place_order->flat_no = $flat_no;
                        $place_order->landmark = $landmark;

                        $origin = urlencode($store_details->address_lat . "," . $store_details->address_long);
                        $get_coordinates = $this->userClassapi->get_coordinates($origin, $address_lat . ',' . $address_long);
                        $coordinates_check = json_decode($get_coordinates, TRUE);
                        if ($coordinates_check == 0) {
                            Session::flash('error', __('messages.161'));
                            return redirect()->back();
                        }
                        $total_distance = 0;
                        $total_mins = 0;
                        if (count($coordinates_check['routes']) > 0) {
                            //$total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['text']);
                            $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);
                            $total_mins = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['duration']['value']);
                        }

                        $delivery_charge_per_km = 0;
                        $delivery_charge_per_km = $this->userClassapi->checkDeliveryCharge($total_distance,$service_cat_id);

//                        $delivery_cost_km = round((($total_distance * 0.001) * $delivery_charge_per_km), 2);
                        //$delivery_cost_min = round((($total_mins / 60) * $delivery_charge_per_min), 2);
                        $delivery_cost_min = 0;
                        $delivery_charge = $delivery_charge_per_km;
                    }
                }

                $place_order->delivery_cost = $delivery_charge;
                $place_order->packaging_cost = $store_details->packaging_charges;
                $place_order->status = 1;
                $place_order->store_name = $store_details->name;
                $place_order->user_name = $user_details['first_name'] . ' ' . $user_details['last_name'];
                $place_order->payment_type = $payment_type;
                //$place_order->payment_status = 0;
                //$place_order->payment_status = $request->get('payment_type') == 1 ? 1 : 0;
                if ($request->get('schedule_date_time') != Null) {
                    $place_order->order_type = 1;
                    $place_order->order_date_time = date('Y-m-d H:i:s', strtotime($request->get('schedule_date_time')));
                } else {
                    $place_order->order_type = 0;
                    $place_order->order_date_time = date('Y-m-d H:i:s');
                }
                $place_order->taken_away_type = ($user_taken_type != Null) ? $user_taken_type : 1;
                //$place_order->taken_away_type = $request->get('user_taken_type');

                if ($request->file('prescription')) {
                    $type = "prescription";
                    $exist_image = "";
                    /*$new_image = $request->file('prescription');
                    $image_upoad = $this->notificationClass->uploadCompanyWiseImage($type, $exist_image, $new_image);
                    $place_order->prescription = $image_upoad;*/
                    $destinationPath = public_path('/assets/images/prescription-images/');
                    $file = $request->file('prescription');
                    $img = Image::make($file->getRealPath());
                    $img->orientate();
                    $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
                    $img->resize(300, 300, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath . $file_new);
                    $place_order->prescription = $file_new;
                }
                $place_order->additional_request =  "";
                $place_order->save();
                $place_order->generateBookingNo();


                //$extra_charges = $place_order->delivery_cost + $place_order->packaging_cost;
                //$delivery_cost = $place_order->delivery_cost;
                $packaging_cost = $place_order->packaging_cost;
                $total_item_cost = round($total_price, 2);
                $promo_code_discount = 0;
                if ($promocode_id != 0) {
                    $discount_on_amount = (($total_price - $discount_cost) + $place_order->packaging_cost);
                    $promo_code = $this->userClassapi->checkPromoCodeValid($promocode_id, $discount_on_amount, $user_details->id, $service_category_id);
                    if ($promo_code != 0) {
                        if ($promo_code != 1) {
                            $get_prmocode = PromocodeDetails::where('id', $promocode_id)->where('status', 1)->first();
                            if ($get_prmocode != Null) {
                                $promo_code_discount = $promo_code;
                                //$place_order->total_pay = round($place_order->total_pay - $promo_code, 2);

                                $add_promocode_details = new UsedPromocodeDetails();
                                $add_promocode_details->service_cat_id = $service_category_id;
                                $add_promocode_details->user_id = $user_id;
                                $add_promocode_details->promocode_id = $promocode_id;
                                $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                                $add_promocode_details->discount_amount = $promo_code;
                                $add_promocode_details->save();

                                $place_order->promo_code = $add_promocode_details->id;
                                $place_order->save();
                            }
                        }
                    }
                }
                $go_prime_discount = 0;

                $goPrime_count = 0;


                $delivery_cost = $place_order->delivery_cost;
                $admin_delivery_commission = ($settings != Null) ? $settings->delivery_commission : 0;
                $place_order->totalPay($total_item_cost, $discount_cost, $promo_code_discount, $tax_cost, $delivery_cost, $packaging_cost, $admin_commission, $admin_delivery_commission, $goPrime_count);

                if ($user_details->pending_refer_discount > 0) {
                    $user = User::query()->where('id', $user_details->id)->first();
                    if ($user != Null) {
                        $user_refer_history = UserReferHistory::query()->where('user_id', $user_details->id)->where('user_status', 0)->first();
                        $total_price = $place_order->total_pay;
                        if ($user_refer_history != Null) {
                            if ($user_refer_history->user_discount_type == 1) {
                                $refer_discount_price = round($total_price - $user_refer_history->user_discount);
                                $user_discount = round($user_refer_history->user_discount, 1);
                            } else {
                                $user_discount = round((($total_price * $user_refer_history->user_discount) / 100), 1);
                                $refer_discount_price = round($total_price - $user_discount);
                            }
                            if ($refer_discount_price < 0) {
                                $refer_discount_price = 0;
                            }
                            $place_order->total_pay = $refer_discount_price;
                            $place_order->refer_discount = round($user_discount, 1);
                            $place_order->save();
                            $user_refer_history->user_status = 1;
                            $user_refer_history->save();
                            $user->pending_refer_discount = $user->pending_refer_discount - 1;
                            $user->save();
                        } else {
                            $user_refer_history = UserReferHistory::query()->where('refer_id', $user_details->id)->where('refer_status', 0)->first();
                            if ($user_refer_history != Null) {
                                if ($user_refer_history->refer_discount_type == 1) {
                                    $refer_discount_price = round($total_price - $user_refer_history->refer_discount);
                                    $user_discount = round($user_refer_history->user_discount, 1);
                                } else {
                                    $user_discount = round((($total_price * $user_refer_history->refer_discount) / 100), 1);
                                    $refer_discount_price = round($total_price - $user_discount);
                                }
                                if ($refer_discount_price < 0) {
                                    $refer_discount_price = 0;
                                }
                                $place_order->total_pay = $refer_discount_price;
                                $place_order->refer_discount = round($user_discount, 1);
                                $place_order->save();
                                $user_refer_history->refer_status = 1;
                                $user_refer_history->save();
                                $user->pending_refer_discount = $user->pending_refer_discount - 1;
                                $user->save();
                            }
                        }
                    }
                }

                //if ($place_order->payment_type != 2) {
                $product_status = new UserProductBookingStatus();
                $product_status->order_id = $place_order->id;
                $product_status->status = 1;
                $product_status->save();
                //}
                $place_order->order_product_list = implode(", ", $order_product_list_string);
                $place_order->save();



                if ($place_order->payment_type == 3) {
                    $wallet_balance = UserWalletTransaction::where('user_id', $user_id)->orderBy('id', 'desc')->first();
                    if ($wallet_balance != Null) {
                        $balance = $wallet_balance->remaining_balance;
                    } else {
                        $balance = 0;
                    }
                    if ($place_order->total_pay > $balance) {
                        $place_order->status = 10;
                        $place_order->save();

                        Session::flash('error', __('messages.175'));
                        return redirect()->back();

                    }
                    $add_balance = new UserWalletTransaction();
                    $add_balance->user_id = $user_id;
                    $add_balance->transaction_type = 2;
//                    $add_balance->wallet_transaction_type = 0;
                    $add_balance->amount = $place_order->total_pay;
                    $add_balance->order_no = $place_order->order_no;
                    $add_balance->subject = "Paid To Store - Order #" . $place_order->order_no;
                    $add_balance->subject_code = 3;
                    //                $add_balance->subject = "Paid to " . $store_details->name . " - " . $get_category->name . " Booking # " . $place_order->order_no;
                    $add_balance->remaining_balance = round($balance - $place_order->total_pay, 1);
                    $add_balance->save();
//                    $place_order->payment_type = $payment_method_type;
                    $place_order->payment_status = 1;
                    $place_order->save();
                }

                $success_url = "";
                $failed_url = "";
                $redirect_url = "";
                if ($place_order->payment_type == 2) {
//                    if ($settings->is_payment_gateway == 1) {
//                        //                    $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
//                        //                    if ($user_currency == Null) {
//                        //                        $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
//                        //                    }
//                        //                    $currency = $user_currency != Null ? $user_currency->ratio : 1;
//                        //                    $currency_code = $user_currency != Null ? $user_currency->currency_code : '';
//                        //
//                        //                    $card_number = $card_details->card_number;
//                        //                    $month = $card_details->month;
//                        //                    $year = $card_details->year;
//                        //                    $cvv = $card_details->cvv;
//                        //                    $holder_name = $card_details->holder_name;
//                        try {
//                            $success_url = \URL::route('paypal.success');
//                            $failed_url = \URL::route('paypal.failed');
//                            //$amount_to_usd = round($ride_details->total_pay * $convert_to_currency->ratio, 2);
//                            $amount_to_usd = round($place_order->total_pay, 2);
//                            $currency_code = "USD";
//                            $user_id = $request->get('user_id');
//                            $service_category_id = $get_category->id;
//                            $order_id = $place_order->id;
//
//                            $paypal_sandbox = $settings->paypal_sandbox . "";
//                            $paypal_client_id = $settings->paypal_client_id . "";
//                            $paypal_client_secret_key = $settings->paypal_client_secret_key . "";
//
//                            $api_context = new ApiContext(new OAuthTokenCredential($paypal_client_id, $paypal_client_secret_key));
//                            $paypal_settings = array(
//                                'mode' => $paypal_sandbox,
//                                'http.ConnectionTimeOut' => 30,
//                                'log.LogEnabled' => true,
//                                'log.FileName' => storage_path() . '/logs/paypal.log',
//                                'log.LogLevel' => 'ERROR'
//                            );
//                            $api_context->setConfig($paypal_settings);
//
//                            $payer = new Payer();
//                            $payer->setPaymentMethod('paypal');
//
//                            $item_1 = new Item();
//                            $item_1->setName('Item 1')
//                                ->setCurrency($currency_code)
//                                //->setCurrency('USD')
//                                ->setQuantity(1)
//                                ->setPrice($amount_to_usd);
//
//                            $item_list = new ItemList();
//                            $item_list->setItems(array($item_1));
//
//                            $amount = new Amount();
//                            $amount->setCurrency($currency_code)
//                                //->setCurrency('USD')
//                                ->setTotal($amount_to_usd);
//
//                            $transaction = new Transaction();
//                            $transaction->setAmount($amount)
//                                ->setItemList($item_list)
//                                ->setCustom($user_id . ',' . $service_category_id . ',' . $order_id)
//                                ->setDescription('Store delivery order transaction');
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
//
//                                    Session::flash('error', __('messages.256'));
//                                    return redirect()->back();
//
//                                } else {
//                                    $place_order->status = 10;
//                                    $place_order->save();
//                                    Session::flash('error', __('messages.257'));
//                                    return redirect()->back();
//
//                                }
//                            }
//                            foreach ($payment->getLinks() as $link) {
//                                if ($link->getRel() == 'approval_url') {
//                                    $redirect_url = $link->getHref();
//                                    break;
//                                }
//                            }
//
//                            \Session::put('paypal_payment_id', $payment->getId());
//                            if (isset($redirect_url)) {
//                                $place_order->status = 11;
//                                $place_order->transaction_id = $payment->getId();
//                                $place_order->save();
//                            } else {
//                                $place_order->status = 10;
//                                $place_order->save();
//                                Session::flash('error', __('messages.176'));
//                                return redirect()->back();
//
//                            }
//                        } catch (\Exception $e) {
//                            $place_order->status = 10;
//                            $place_order->save();
//                            Session::flash('error', __('messages.161'));
//                            return redirect()->back();
//                        }
//                    } else {
//                        $place_order->payment_type = $payment_method_type;
                        $place_order->payment_status = 1;
                        $place_order->save();
//                    }

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
                        //$add_product->food_type = $product['food_type'];
                        $add_product->save();
                        if ($product['size'] != Null) {
                            $product_size = new UserProductBookingSize();
                            $product_size->order_id = $place_order->id;
                            $product_size->product_id = $add_product->id;
                            $product_size->name = $product['size']['name'];
                            $product_size->amount = $product['size']['amount'];
                            $product_size->save();
                        }
                        if ($product['options'] != Null) {
                            foreach ($product['options'] as $options) {
                                $product_option = new UserProductBookingOptions();
                                $product_option->order_id = $place_order->id;
                                $product_option->product_id = $add_product->id;
                                $product_option->name = $options['name'];
                                $product_option->category = $options['category'];
                                $product_option->amount = $options['amount'];
                                $product_option->save();
                            }
                        }
                        if ($product['toppings'] != Null) {
                            foreach ($product['toppings'] as $toppings) {
                                $product_option = new UserProductBookingToppings();
                                $product_option->order_id = $place_order->id;
                                $product_option->product_id = $add_product->id;
                                $product_option->name = $toppings['name'];
                                $product_option->category = $toppings['category'];
                                $product_option->amount = $toppings['amount'];
                                $product_option->save();
                            }
                        }
                    }
                }

                if ($place_order->order_type != 1) {
                    if ($place_order->payment_type == 2 || $place_order->payment_type == 3) {
                        $service_settings = ServiceSettings::query()->where('service_cat_id', $place_order->service_cat_id)->first();
                        if ($service_settings != Null) {
                            $cancel_charge = $service_settings->cancel_charge != Null ? $service_settings->cancel_charge : 0;
                            $count_cancel_charge = round((($place_order->total_item_cost * $cancel_charge) / 100), 2);
                            $charges = round($place_order->total_pay - $count_cancel_charge, 2);
                            $place_order->cancel_charge = $count_cancel_charge;
                            $place_order->refund_amount = $charges;
                            $place_order->save();
                        }
                    }
                }

                //if ($place_order->payment_type != 2) {
                $store_provider_details = Provider::select('providers.id as provider_id', 'providers.device_token', 'providers.login_device', 'providers.email', 'providers.language')
                    ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                    ->join('store_details', 'store_details.provider_service_id', '=', 'provider_services.id')
                    ->where('store_details.id', $place_order->store_id)
                    ->whereNotNull('access_token')->first();
                if ($store_provider_details != Null) {
                    $this->notificationClass->storeDeliveryUserNotification($place_order->id, $store_provider_details->device_token, $place_order->status, $store_provider_details->login_device, $store_provider_details->language);

                    if ($request->get('user_taken_type') == 2) {
                        $provider_details = Provider::query()->where("id", $store_provider_details->provider_id)->first();
                        if ($provider_details != Null) {
                            $provider_details->provider_commission_limit_amount = $provider_details->provider_commission_limit_amount + $place_order->admin_commission;
                            $provider_details->save();
                        }
                    }
                }
                //}
                $order_cart = UserOrderCart::query()->where('user_id',$user_id)->where('order_add_by',$order_add_by)->delete();

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
                Session::flash('success', __('messages.177'));
                return redirect()->route('get:user_orders_all',['delivery','all']);

            } else {
                Session::flash('error', __('messages.258'));
                return redirect()->back();
            }
        }else{
                //user not found
                Session::flash('error', __('messages.55'));
                return redirect()->back();

        }

    }

    public function postPlaceOrder_old(Request $request)
    {
        $user_id = Auth::guard("user")->user()->id;
        $validator = Validator::make($request->all(), [
            'total_pay' => 'required',
            'address_delivery' => 'required',
            'payment_type' => 'required',
            'restaurant' => 'required',
            'item_total' => 'required',
            'item_discount' => 'required',
            'item_tax' => 'required',
            'item_delivery_charges' => 'required',
            'item_packaging_charges' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false
            ]);
        }


        $store_details = StoreDetails::query()->where('id', $request->get('restaurant'))->first();
        if ($store_details == Null) {
            Session::flash('error', 'Store not found!');
            return response()->json([
                'success' => false
            ]);
        }
        $service_status = ProviderServices::query()->where('id', $store_details->provider_service_id)->where('service_cat_id', $this->food_delivery)->where('status', 1)->where('current_status', 1)->first();
        if ($service_status == Null) {
            Session::flash('error', 'store currently not available!');
            return response()->json([
                'success' => false
            ]);
        }

        $store_provider = Provider::query()->where('id', $service_status->provider_id)->first();
        $store_device_token = "";
        $store_login_device = 1;
        $store_language = "en";
        if ($store_provider != Null) {
            $store_device_token = $store_provider->device_token;
            $store_login_device = $store_provider->login_device;
            $store_language = $store_provider->language;
        }
        $day = strtoupper(substr(date('l'), 0, 3));
        $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
            ->where('store_id', $store_details->id)
            ->where('day', 'all')
//                    ->where('day', strtoupper(substr(date('l'), 0, 3)))
            ->first();
        if ($store_time == Null) {
            $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                ->where('store_id', $store_details->id)
                //->where('day', 'all')
                //->where('day', strtoupper(substr(date('l'), 0, 3)))
                ->where('day', $day)
                ->first();
        }
        if ($store_time == Null) {
            Session::flash('error', 'store currently not available!');
            return response()->json([
                'success' => false
            ]);
        }
        $time = Date('H:i:s');
        if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time) {
        } else {
            Session::flash('error', 'store currently not available!');
            return response()->json([
                'success' => false
            ]);
        }
        $address = UserAddress::query()->where('id', $request->get('address_delivery'))->first();
        if ($address == Null) {
            Session::flash('error', 'Delivey Address not found!');
            return response()->json([
                'success' => false
            ]);
        }

        $address_lat_long = array_map('trim', explode(",", $address->lat_long));
        if (count($address_lat_long) != 2) {
            Session::flash('error', 'Delivey Address not found!');
            return response()->json([
                'success' => false
            ]);
        }
        $address_lat = trim($address_lat_long[0]);
        $address_long = trim($address_lat_long[1]);
        $check_near_delivery_address = StoreDetails::query()->select('store_details.id', 'providers.service_radius'
            , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                +COS( ' . $address_lat . ' * pi()/180)
                * COS(store_details.address_lat * pi()/180)
                * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
        )
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('provider_services.service_cat_id', $this->food_delivery)
            ->where('store_details.id', $store_details->id)
            ->havingRaw('providers.service_radius > distance')
            ->first();
        if ($check_near_delivery_address == Null) {
            Session::flash('error', 'Your delivery address out of store delivery range! Please change your delivery address.');
            return response()->json([
                'success' => false
            ]);
        }
        $origin = urlencode($store_details->address_lat . "," . $store_details->address_long);
        $get_coordinates = $this->userClassapi->get_coordinates($origin, $address->lat_long);
        $coordinates_check = json_decode($get_coordinates, TRUE);
        if ($coordinates_check == 0) {
            Session::flash('error', 'something went to wrong!');
            return response()->json([
                'success' => false
            ]);
        }
        $total_distance = 0;
        if (count($coordinates_check['routes']) > 0) {
            $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['text']);
        }
        $delivery_charge = $this->userClassapi->checkDeliveryCharge($total_distance);


        $service_settings = ServiceSettings::query()->where('service_cat_id', $this->food_delivery)->first();
        if ($service_settings != Null) {
            //$delivery_charge = $service_settings->delivery_charge != Null ? $service_settings->delivery_charge : 0;
            $admin_commission = $service_settings->admin_commission;
            $store_commission = $service_settings->store_wise_commission;
            $tax_cost = $service_settings->tax != 0 ? $service_settings->tax : 0;
        } else {
            $admin_commission = 0;
            $store_commission = 0;
            //$delivery_charge = 0;
            $tax_cost = 0;
        }
        if ($store_details->store_commission != 0) {
            $store_commission = $store_details->store_commission;
        }

        $temp_cart = UserOrderCart::query()->where('user_id', $user_id)->get();
        if ($temp_cart->isEmpty()) {
            Session::flash('error', 'something went to wrong!');
            return response()->json([
                'success' => false
            ]);
        }

        $place_order = new UserProductBooking();
        $place_order->user_id = Auth::guard("user")->user()->id;
        $place_order->store_id = $store_details->id;
        $place_order->booking_type = 1;
        $place_order->service_cat_id = $this->food_delivery;
        $place_order->packaging_cost = $store_details->packaging_charges;
        $place_order->status = 1;
        $place_order->store_name = $store_details->name;
        $place_order->user_name = Auth::guard("user")->user()->first_name . ' ' . Auth::guard("user")->user()->last_name;

        $place_order->delivery_cost = $delivery_charge;
        $place_order->delivery_address = $address->address;
        $place_order->lat_long = $address->lat_long;
        $place_order->flat_no = $address->flat_no;
        $place_order->landmark = $address->landmark;
        $place_order->payment_type = $request->get('payment_type');
//                $place_order->payment_status = 0;
        $place_order->payment_status = $request->get('payment_type') != 1 ? 1 : 0;
        $place_order->save();
        $place_order->generateBookingNo();

//        $place_order->total_item_cost = $request->get('item_total');
//        $place_order->discount_cost = $request->get('item_discount');
//        $place_order->tax_cost = $request->get('item_tax');
//        $place_order->total_pay = $request->get('total_pay');

        $order_product_list = [];
        $item_cost = 0;
        $discount_cost = 0;
        foreach ($temp_cart as $cart) {
            $get_product = StoreProductDetails::query()->where('id', $cart->product_id)->where('status', 1)->first();
            if ($get_product == Null) {
                UserProductBooking::query()->where('id', $place_order->id)->delete();
                Session::flash('error', 'something went to wrong!');
                return response()->json([
                    'success' => false
                ]);
            }

            if ($get_product->discount_type == 1) {
                $new_price = round((($get_product->amount) - ($get_product->discount_amount)), 2);
            } elseif ($get_product->discount_type == 2) {
                $new_price = round((($get_product->amount) - (($get_product->amount) * ($get_product->discount_amount)) / (100)), 2);
            } else {
                $new_price = $get_product->amount;
            }

            $check_duplicate = UserProductBookingQuantity::query()->where('order_id', $place_order->id)->where('product_id', $get_product->id)->first();
            if ($check_duplicate == Null) {
                $get_product_category = StoreProductCategory::query()->where('id', $get_product->product_cate_id)->where('status', 1)->first();
                if ($get_product_category == Null) {
                    UserProductBooking::query()->where('id', $place_order->id)->delete();
                    Session::flash('error', 'something went to wrong!');
                    return response()->json([
                        'success' => false
                    ]);
                }
                $order_quantity = new UserProductBookingQuantity();
                $order_quantity->order_id = $place_order->id;
                $order_quantity->product_id = $get_product->id;
                $order_quantity->product_name = $get_product->name;
                $order_quantity->product_cat_name = $get_product_category->name;
                $order_quantity->num_of_items = $cart->quantity;
                $order_quantity->price_for_one = $new_price;
                $order_quantity->save();

                array_push($order_product_list, $order_quantity->product_name . ' x ' . $order_quantity->num_of_items);
                $item_cost = round($item_cost + ($new_price * $cart->quantity), 2);
            }
            $cart->delete();
        }

        $discount_cost = 0;
        if ($store_details->offer_min_amount != 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
            if ($item_cost >= $store_details->offer_min_amount) {
                if ($store_details->offer_type == 1) {
                    $discount_cost = $item_cost - (($item_cost) - ($store_details->offer_amount));
                } else {
                    $discount_cost = ((($item_cost) * ($store_details->offer_amount)) / 100);
                }
            }
        }
        if ($store_details->offer_min_amount == 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
            if ($store_details->offer_type == 1) {
                $discount_cost = $item_cost - (($item_cost) - ($store_details->offer_amount));
            } else {
                $discount_cost = ((($item_cost) * ($store_details->offer_amount)) / 100);
            }
        }
        $extra_charges = $place_order->delivery_cost + $place_order->packaging_cost;
        $delivery_cost = $place_order->delivery_cost;
        $packaging_cost = $place_order->packaging_cost;
        $promo_code_discount = 0;
//        if ($request->get('promocode_id') != 0) {
//            $discount_on_amount = (($item_cost - $discount_cost) + $place_order->packaging_cost);
//
//            $promo_code = $this->userClassapi->checkPromoCodeValid($request->get('promocode_id'), $discount_on_amount, $user_details->id, $request->get('service_category_id'));
//            if ($promo_code != 0) {
//                $get_prmocode = PromocodeDetails::where('id', $request->get('promocode_id'))->where('status', 1)->first();
//                if ($get_prmocode != Null) {
//                    $promo_code_discount = $promo_code;
////                            $place_order->total_pay = round($place_order->total_pay - $promo_code, 2);
//                    $add_promocode_details = new UsedPromocodeDetails();
//                    $add_promocode_details->service_cat_id = $request->get('service_category_id');
//                    $add_promocode_details->user_id = $request->get('user_id');
//                    $add_promocode_details->promocode_id = $request->get('promocode_id');
//                    $add_promocode_details->promocode_name = $get_prmocode->promo_code;
//                    $add_promocode_details->discount_amount = $promo_code;
//                    $add_promocode_details->save();
//
//                    $place_order->promo_code = $add_promocode_details->id;
//                    $place_order->save();
//                }
//            }
//        }

        $place_order->totalPay($item_cost, $discount_cost, $promo_code_discount, $tax_cost, $delivery_cost, $packaging_cost, $admin_commission, $store_commission);

        $product_status = new UserProductBookingStatus();
        $product_status->order_id = $place_order->id;
        $product_status->status = 1;
        $product_status->save();
        $place_order->order_product_list = implode(", ", $order_product_list);
        $place_order->save();

//        $address = UserAddress::where('id', $request->get('address_delivery'))->first();
//        if ($address != Null) {
//            $count = substr_count($address->address, ",");
//            if ($count >= 2) {
//                $city_name = trim(explode(',', $address->address)[$count - 2]);
//            } else {
//                Session::flash('error', 'Address is not Valid, Change Your Address!');
//                return response()->json([
//                    'success' => false
//                ]);
//            }
//            $city_id = WorldCities::where('name', $city_name)->where('parent_id', '!=', 0)->first();
//            if ($city_id == Null) {
//                Session::flash('error', 'Address Out Of Range!');
//                return response()->json([
//                    'success' => false
//                ]);
//            }
//            $restaurant = Restaurant::select('id', 'city_id', 'current_status')->where('id', $request->get('restaurant'))->where('city_id', $city_id->id)->where('status', 1)->first();
//            if ($restaurant == Null) {
//                Session::flash('error', 'Restaurant not found!');
//                return response()->json([
//                    'success' => false
//                ]);
//            }
//            if ($restaurant->current_status == 0) {
//                Session::flash('error', 'Restaurant Close!');
//                return response()->json([
//                    'success' => false
//                ]);
//            }
//            if ($restaurant->city_id != $city_id->id) {
//                Session::flash('error', 'Address Out Of Range!');
//                return response()->json([
//                    'success' => false
//                ]);
//            }

//            $order_details = new OrderDeliveries();
//            $order_details->customer_id = $user_id;
//            $order_details->address_id = $request->get('address_delivery');
//            $order_details->store_id = $request->get('restaurant');
//            $order_details->item_cost = $request->get('item_total');
//            $order_details->discount_cost = $request->get('item_discount');
//            $order_details->tax_cost = $request->get('item_tax');
//            $order_details->delivery_cost = $request->get('item_delivery_charges');
//            $order_details->packaging_cost = $request->get('item_packaging_charges');
//            $order_details->total_cost = $request->get('total_pay');
//            $order_details->status = "pending";
//            $order_details->order_no = $order_details->generateOrderNo();
//            $order_details->payment_type = $request->get('payment_type');
//            $order_details->payment_status = "pending";
//            $order_details->save();

//            $order_status = new OrderStatus();
//            $order_status->order_id = $order_details->id;
//            $order_status->status = "pending";
//            $order_status->save();
//
//            $temp_cart = UserOrderCart::where('user_id', $user_id)->get();
//            foreach ($temp_cart as $cart) {
//                $price = Products::select('price')->where('id', $cart->product_id)->where('status', 1)->first();
//                if ($price != Null) {
//                    $order_list = new OrderList();
//                    $order_list->order_id = $order_details->id;
//                    $order_list->restaurant_id = $cart->restaurant_id;
//                    $order_list->product_id = $cart->product_id;
//                    $order_list->num_of_items = $cart->quantity;
//                    $order_list->price = $price->price;
//                    $order_list->save();
//                    $cart->delete();
//                }
//            }
        $this->notificationClass->storeDeliveryUserNotification($place_order->id, $store_device_token, $place_order->status,$store_login_device,$store_language);
        /*\Log::info("sucess");*/
        Session::flash('success', 'Order Placed successfully!');
        return response()->json([
            'success' => true
        ]);
//        } else {
//            Session::flash('error', 'Address Out Of Range!');
//            return response()->json([
//                'success' => false
//            ]);
//        }
    }

    public function getProfile()
    {

        $user_open_tab = "edit_profile";
        $title_code = "Edit Profile";
        return view('front.pages.user_details_tabview', compact('user_open_tab','title_code'));

    }

    public function getUserChangePassword()
    {
        $user_open_tab = "change_password";
        $title_code = "Change Password";
        return view('front.pages.user_details_tabview', compact('user_open_tab','title_code'));

    }

    public function getOrders()
    {

        $status = $this->store_order_status_int;
        $per_page_rec = $this->perpageRec;
        $get_orders_data = UserProductBooking::query()->select('user_store_product_booking.id',
            'user_store_product_booking.order_no',
            'user_store_product_booking.delivery_address as address',
            'user_store_product_booking.order_product_list',
            'user_store_product_booking.status',
            'user_store_product_booking.created_at',
            'user_store_product_booking.payment_type',
            'store_details.image',
            'store_details.name',
            'user_store_product_booking.total_pay as total_cost',
            'store_details.address as pickup_address')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->where('user_store_product_booking.user_id', Auth::guard("user")->user()->id)
//            ->where('user_store_order_booking.status','!=',11)
            ->orderBy('user_store_product_booking.id',  'desc')
            ->paginate($per_page_rec);
        $product_option_list = [];
        $get_orders = [];
        foreach ($get_orders_data as $key => $order) {
            $product_details = UserProductBookingQuantity::query()->select('id', 'product_name', 'product_cat_name', 'num_of_items', 'price_for_one')->where('order_id', $order->id)->get()->groupBy('product_cat_name');

            $prod_data = "";
            $prod_data_top = "";
            $final_toppings = "";
            foreach ($product_details as $product_list) {
                foreach ($product_list as $product) {
                    $size = UserProductBookingSize::query()->where('order_id', $order->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                    $options = UserProductBookingOptions::query()->where('order_id', $order->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                    $toppings = UserProductBookingToppings::query()->where('order_id', $order->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                    $add_ons = array_merge($size, $options, $toppings);
                    $add_ons = implode(', ', $add_ons);
//                    $product_option_list[$order->id] = $add_ons;
                    $prod_data = $product->product_name . " x " . $product->num_of_items;
                    $prod_data_top = ($add_ons != "") ? " <b><span style='font-size: 10px;'> ( " . $add_ons . " )</span></b> , " : " ,";
                    $final_toppings .= $prod_data . $prod_data_top;
                }
            }
            $get_orders[$key]['id'] = $order->id;
            $get_orders[$key]['order_no'] = $order->order_no;
            $get_orders[$key]['address'] = $order->address;
            $get_orders[$key]['status'] = $order->status;
            $get_orders[$key]['created_at'] = $order->created_at;
            $get_orders[$key]['payment_type'] = $order->payment_type;
            $get_orders[$key]['image'] = $order->image;
            $get_orders[$key]['name'] = $order->name;
            $get_orders[$key]['total_cost'] = $order->total_cost;
            $get_orders[$key]['pickup_address'] = $order->pickup_address;
            $get_orders[$key]['order_product_list'] = trim($final_toppings, " , ");
        }
//        dd($get_orders);
        $user_open_tab = "orders";
        return view('front.pages.user_details_tabview', compact('user_open_tab', 'get_orders', 'product_option_list','get_orders_data'));
        //return view('front.pages.user_details_tabview', compact('user_open_tab', 'get_orders', 'orders'));
    }

    public function getOrdersAll($service_type="transport",$filter_type="all",$page="")
    {
        if($service_type == "transport"){
            $service_type = $service_type;
            $title_code = "Transport Service";
        }elseif($service_type == "delivery"){
            $service_type = $service_type;
            $title_code = "Delivery Service";
        }elseif($service_type == "on-demand"){
            $service_type = $service_type;
            $title_code = "On Demand Service";
        }elseif($service_type == "coupon"){
            $service_type = $service_type;
            $title_code = "Coupon Service";
        }else{
            $service_type = "transport";
            $title_code = "Transport Service";
        }
        $per_page_rec = $this->perpageRec;
        $date = date('Y-m-d');
        if($filter_type == "today"){
            $filter_type = "today";
            $filter = "y";
            $start_date = $date . " 00:00:01";
            $end_date = $date . " 23:59:59";
        }elseif ($filter_type == "lastweek"){
            $filter_type = "lastweek";
            $filter = "y";
            //last 7 day
            $start_date = date('Y-m-d', strtotime('-7 days', strtotime($date)));
            $end_date = $date;
            $start_date = $start_date . " 00:00:01";
            $end_date = $end_date . " 23:59:59";
        }elseif ($filter_type == "lastmonth"){
            $filter_type = "lastmonth";
            $filter = "y";
            $start_date = date('Y-m-d', strtotime('-30 days', strtotime($date)));
            $end_date = $date;
            $start_date = $start_date . " 00:00:01";
            $end_date = $end_date . " 23:59:59";
        }elseif ($filter_type == "lastyear"){
            $filter_type = "lastyear";
            $filter = "y";
            $start_date = date("Y-01-01", strtotime($date));
            $end_date = date("Y-m-d", strtotime($date));
            $start_date = $start_date . " 00:00:01";
            $end_date = $end_date . " 23:59:59";
        }else{
            $filter_type = "all";
            $filter = "n";
        }


        $per_page_rec   = ($page > 0)?$per_page_rec:5;

        if($service_type == "delivery" )
        {
            //code get record of delviery services
            $get_orders_data = UserProductBooking::query()->select('user_store_product_booking.id',
                'user_store_product_booking.order_no',
                'user_store_product_booking.service_cat_id as service_cat_id',
                'user_store_product_booking.delivery_address as delivery_address',
                'user_store_product_booking.status',
                'user_store_product_booking.created_at',
                'store_details.name',
                'store_details.id as store_id',
                'user_store_product_booking.total_pay as total_cost',
                'store_details.address as pickup_address',
                'service_category.name as service_category_name',
                'service_category.icon_name as service_category_icon'
                )
                ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
                ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
                ->where('user_store_product_booking.user_id', Auth::guard("user")->user()->id);
                if($filter == "y")
                {
                    $get_orders_data= $get_orders_data->where('user_store_product_booking.order_date_time', '>=', $start_date)
                        ->where('user_store_product_booking.order_date_time', '<=', $end_date);
                }

                $get_orders_data= $get_orders_data->orderBy('user_store_product_booking.id',  'desc')
                ->paginate($per_page_rec);

            /*$get_orders = [];
            foreach ($get_orders_data as $key => $order) {

                $get_orders[$key]['id'] = $order->id;
                $get_orders[$key]['order_no'] = $order->order_no;
                $get_orders[$key]['delivery_address'] = $order->delivery_address;
                $get_orders[$key]['status'] = $order->status;
                $get_orders[$key]['created_at'] = $order->created_at;
                $get_orders[$key]['name'] = $order->name;
                $get_orders[$key]['total_cost'] = $order->total_cost;
                $get_orders[$key]['pickup_address'] = $order->pickup_address;
                $get_orders[$key]['service_category_name'] = $order->service_category_name;
                $get_orders[$key]['service_category_icon'] = $order->service_category_icon;

            }*/
        }elseif($service_type == "transport"){
            //code get record of transport services
            $get_orders_data = TransportRideBook::query()->select('user_ride_booking.id',
                'user_ride_booking.ride_no as order_no',
                'user_ride_booking.service_cat_id as service_cat_id',
                'user_ride_booking.destination_address as delivery_address',
                'user_ride_booking.status',
                'user_ride_booking.pickup_datetime as created_at',
                'user_ride_booking.total_pay as total_cost',
                'user_ride_booking.pickup_address as pickup_address',
                'service_category.name as service_category_name',
                'service_category.icon_name as service_category_icon'
            )
                ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->where('user_ride_booking.user_id', Auth::guard("user")->user()->id);
                if($filter == "y")
                {
                    $get_orders_data= $get_orders_data->where('user_ride_booking.pickup_datetime', '>=', $start_date)
                        ->where('user_ride_booking.pickup_datetime', '<=', $end_date);
                }
                //            ->where('user_store_order_booking.status','!=',11)
            $get_orders_data = $get_orders_data->orderBy('user_ride_booking.id',  'desc')
                ->paginate($per_page_rec);

            /*$get_orders = [];
            foreach ($get_orders_data as $key => $order) {

                $get_orders[$key]['id'] = $order->id;
                $get_orders[$key]['order_no'] = $order->order_no;
                $get_orders[$key]['delivery_address'] = $order->delivery_address;
                $get_orders[$key]['status'] = $order->status;
                $get_orders[$key]['created_at'] = $order->created_at;
                $get_orders[$key]['name'] = "";
                $get_orders[$key]['total_cost'] = $order->total_cost;
                $get_orders[$key]['pickup_address'] = $order->pickup_address;
                $get_orders[$key]['service_category_name'] = $order->service_category_name;
                $get_orders[$key]['service_category_icon'] = $order->service_category_icon;

            }*/
        }elseif($service_type == "on-demand")
        {

            //code get record of delviery services
            $get_orders_data = UserPackageBooking::query()->select('user_service_package_booking.id',
                'user_service_package_booking.order_no as order_no',
                'user_service_package_booking.service_cat_id as service_cat_id',
                'user_service_package_booking.order_no as order_no',
                'user_service_package_booking.delivery_address as delivery_address',
                'user_service_package_booking.status',
                'user_service_package_booking.service_date_time as created_at',
                'user_service_package_booking.total_pay as total_cost',
                'service_category.name as service_category_name',
                'service_category.icon_name as service_category_icon'
            )
                ->join('service_category', 'service_category.id', '=', 'user_service_package_booking.service_cat_id')
                ->where('user_service_package_booking.user_id', Auth::guard("user")->user()->id);
            if($filter == "y")
            {
                $get_orders_data= $get_orders_data->where('user_service_package_booking.service_date_time', '>=', $start_date)
                    ->where('user_service_package_booking.service_date_time', '<=', $end_date);
            }
            //            ->where('user_store_order_booking.status','!=',11)
            $get_orders_data = $get_orders_data->orderBy('user_service_package_booking.id',  'desc')
                ->paginate($per_page_rec);


        }elseif($service_type == "coupon"){
            $get_orders_data= array();
        }
        else{
            //code get record of delviery services
            $get_orders_data = UserProductBooking::query()->select('user_store_product_booking.id',
                'user_store_product_booking.order_no',
                'user_store_product_booking.delivery_address as delivery_address',
                'user_store_product_booking.status',
                'user_store_product_booking.service_cat_id as service_cat_id',
                'user_store_product_booking.created_at',
                'store_details.name',
                'user_store_product_booking.total_pay as total_cost',
                'store_details.address as pickup_address',
                'service_category.name as service_category_name',
                'service_category.icon_name as service_category_icon'
            )
                ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
                ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
                ->where('user_store_product_booking.user_id', Auth::guard("user")->user()->id)
                //            ->where('user_store_order_booking.status','!=',11)

                ->orderBy('user_store_product_booking.id',  'desc')
                ->paginate($per_page_rec);

            /*$get_orders = [];
            foreach ($get_orders_data as $key => $order) {

                $get_orders[$key]['id'] = $order->id;
                $get_orders[$key]['order_no'] = $order->order_no;
                $get_orders[$key]['delivery_address'] = $order->delivery_address;
                $get_orders[$key]['status'] = $order->status;
                $get_orders[$key]['created_at'] = $order->created_at;
                $get_orders[$key]['name'] = $order->name;
                $get_orders[$key]['total_cost'] = $order->total_cost;
                $get_orders[$key]['pickup_address'] = $order->pickup_address;
                $get_orders[$key]['service_category_name'] = $order->service_category_name;
                $get_orders[$key]['service_category_icon'] = $order->service_category_icon;

            }*/
        }

        /*dd($get_orders);*/

        $user_open_tab = "ride_orders_all";
        $title_code = "Orders - ".$title_code;
        return view('front.pages.user_details_tabview', compact('service_type','user_open_tab', 'title_code','get_orders_data','filter_type'));
        //return view('front.pages.user_details_tabview', compact('user_open_tab', 'get_orders', 'orders'));
    }

    public function getOffers()
    {
        $date = Date('Y-m-d');
        $offers_list = PromocodeDetails::query()->where('service_cat_id', $this->food_delivery)->where('expiry_date_time', '>=', $date)->where('status', 1)->get();
        $offers = [];
        foreach ($offers_list as $key => $offer) {
            try {
                $get_user_used = UsedPromocodeDetails::query()->where('user_id', Auth::guard("user")->user()->id)->where('promocode_id', $offer->id)->count();
            } catch (\Exception $e) {
                $get_user_used = 0;
            }
            if ($offer->coupon_limit != 0) {
                if ($offer->coupon_limit > $offer->total_usage) {
                    if ($offer->usage_limit > $get_user_used) {
                        $offers[] = $offer;
                    }
                }
            } else {
                if ($offer->usage_limit > $get_user_used) {
                    $offers[] = $offer;
                }
            }
//            if ($offer->code_limit != Null) {
//                if ($offer->code_limit == $offer->user_used || $offer->code_limit < $offer->user_used) {
//                    unset($offers[$key]);
//                }
//            }
//            $check = UsedPromocodeDetails::where('user_id', Auth::guard("user")->user()->id)->where('promocode_id', $offer->id)->first();
//            if ($check != Null) {
//                unset($offers[$key]);
//            }
        }

        $user_open_tab = "offers";
        return view('front.pages.user_details_tabview', compact('user_open_tab', 'offers'));
    }

    public function getPayments()
    {
        $user_open_tab = "payments";
        $title_code ="Manage Card";
        $payments = UserCardDetails::query()->select('id','holder_name', 'card_number')->where('user_id', Auth::guard("user")->user()->id)
                    ->orderBy('id','desc')
                    ->get();
        return view('front.pages.user_details_tabview', compact('user_open_tab', 'payments','title_code'));
    }

    public function getMyCoupons()
    {
        $user_open_tab = "my-coupons";
        $title_code ="My Coupons";
        $user_id = Auth::guard("user")->user()->id;
        $get_user_purchase_coupons = UserPurchaseCouponDetails::query()
                                        ->select("user_purchase_coupon_details.*",'coupon_details.coupon_image','coupon_details.coupon_name',
                                            'coupon_details.coupon_brand_name','coupon_details.coupon_validity',
                                            DB::raw('COUNT(user_purchase_coupon_details.id) as total_purchase_coupon')
                                        )
                                        ->join('coupon_details','coupon_details.id','user_purchase_coupon_details.coupon_id')
                                        ->where('user_purchase_coupon_details.user_id',$user_id)
                                        ->groupBy('user_purchase_coupon_details.coupon_id')
                                        ->orderBy('user_purchase_coupon_details.id','desc')
                                        ->get();
        return view('front.pages.user_details_tabview', compact('user_open_tab', 'get_user_purchase_coupons','title_code'));
    }
    public function getMyCouponDetails(Request $request,$coupon_id = 0)
    {
        $user_open_tab = "my-coupon-details";
        $title_code ="My Coupon Details";

        if($coupon_id > 0){
            $user_id = Auth::guard("user")->user()->id;

            $get_user_purchase_coupon_list = CouponCodeDetail::query()
                                       ->where('coupon_id',$coupon_id)
                                       ->where('user_id',$user_id)
                                       ->orderBy('id','desc')
                                        ->get();
            $get_coupon_details  =  CouponDetails::query()->where('id',$coupon_id)->first();

            if($get_coupon_details != Null){

                return view('front.pages.user_details_tabview', compact('user_open_tab','get_coupon_details','get_user_purchase_coupon_list',
                    'title_code'));

            }else{
                return redirect()->route('get:my_coupons')->with('error','Please select proper coupon');
            }
        }else{
            return redirect()->route('get:my_coupons')->with('error','Please select proper coupon');
        }


    }

    public function getFavourites()
    {
        $favourtie_restaurants = UserFavouriteStore::query()->where('service_cat_id', $this->food_delivery)->where('user_id', Auth::guard("user")->user()->id)->get();
        $restaurants = [];

        foreach ($favourtie_restaurants as $key => $favourtie_restaurant) {
            $restaurants[$key] = StoreDetails::query()->select('store_details.id',
//                'store_details.user_id',
                'store_details.image',
//                'store_details.res_type',
                'store_details.offer_min_amount'

                , 'store_details.offer_amount as offer_percentage', 'store_details.slug', 'store_details.address', 'store_details.name'

                , 'providers.contact_number as contact_details')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
//                ->where('provider_services.status', $provider_status)
//                ->where('providers.status', $status)
                ->where('provider_services.service_cat_id', $this->food_delivery)
                ->where('store_details.id', $favourtie_restaurant->store_id)
//                ->orderBy('store_details.id', '=', 'desc')
                ->first();


//            $restaurants[$key] = StoreDetails::select('store_details.id', 'store_details.user_id',
//                'store_details.image',
//                'store_details.res_type', 'store_details.offer_min_amount',
//                'store_details.offer_type as offer_percentage', 'store_details.slug', 'store_details.address', 'store_details.name'
//                , 'users.contact_details')
////                ->join('restaurants', 'restaurants.id', '=', 'res_cuisine.restaurant_id')
//                ->join('users', 'users.id', '=', 'restaurants.user_id')
//                ->where('restaurants.id', $favourtie_restaurant->restaurant_id)
//                ->first();

            $cuisines[$key] = StoreProductDetails::query()->select('store_product_category.name')
                ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                ->where('store_product_details.store_id', $favourtie_restaurant->store_id)
                ->orderBy('name', 'asc')
                ->get();

//            $res_time[$key] = StoreTimings::select('store_timings.id','store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
//                ->where('store_timings.store_id', $favourtie_restaurant->store_id)
//                ->where('day', 'all')
////                ->orwhere('day', date('l'))
//                ->first();
//
            $res_time[$key] = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                ->where('store_timings.store_id', $favourtie_restaurant->store_id)
                ->where('day', 'all')
//                    ->where('day', strtoupper(substr(date('l'), 0, 3)))
                ->first();
            if (empty($res_time[$key])) {
//                dd("D");
                $res_time[$key] = StoreTimings::query()->select('store_timings.id', 'store_timings.day', 'store_timings.store_open_time as res_opens', 'store_timings.store_close_time as res_closes')
                    ->where('store_timings.store_id', $favourtie_restaurant->store_id)
//                       ->where('day', 'all')
                    ->where('day', strtoupper(substr(date('l'), 0, 3)))
                    ->first();
            }

//            dd($res_time);
//            dd(strtoupper(substr(date('l'), 0, 3)));
            $location = strtolower($restaurants[$key]->address);
            $count = substr_count($location, ",");
            $locations[$key] = trim(explode(',', $restaurants[$key]->address)[$count - 2]);
        }
//        dd($res_time);
//        dd($locations[0]);
        $user_open_tab = "favourites";
        return view('front.pages.user_details_tabview', compact('user_open_tab', 'restaurants', 'cuisines', 'res_time', 'locations'));

    }

    public function getAddress()
    {
        $user_address = UserAddress::query()->where('user_id', Auth::guard("user")->user()->id)->where('status', 1)->get();
        $user_address_count = $user_address->count();

        $user_open_tab = "addresses";
        return view('front.pages.user_details_tabview', compact('user_open_tab', 'user_address', 'user_address_count'));
    }

    public function postEditUserAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false
            ]);
        }

        $lat_long = $request->get('lat') . ',' . $request->get('long');
        $check_duplicate = UserAddress::query()->where('user_id', Auth::guard("user")->user()->id)
            ->where('lat_long', 'LIKE', "%{$lat_long}%")
            ->where('id', '!=', $request->get('address_id'))->first();
        if ($check_duplicate == Null) {
            $edit_address = UserAddress::query()->where('id', $request->get('address_id'))->first();
            $edit_address->user_id = Auth::guard("user")->user()->id;
            $edit_address->address = $request->get('new_address');
            if ($request->get('lat') != Null && $request->get('long') != Null) {
                $edit_address->lat_long = $request->get('lat') . "," . $request->get('long');
            }
//            if ($request->get('long') != Null) {
//                $edit_address->longitude = $request->get('long');
//            }
            $edit_address->address_type = $request->get('address_type');
            $edit_address->landmark = $request->get('landmark');
            $edit_address->flat_no = $request->get('flat_no');
            $edit_address->save();
        } else {
            $edit_address = UserAddress::query()->where('id', $request->get('address_id'))->first();
            $edit_address->user_id = Auth::guard("user")->user()->id;
            $edit_address->address = $request->get('new_address');
            if ($request->get('lat') != Null && $request->get('long') != Null) {
                $edit_address->lat_long = $request->get('lat') . "," . $request->get('long');
            }
//            if ($request->get('long') != Null) {
//                $edit_address->longitude = $request->get('long');
//            }
            $edit_address->address_type = $request->get('address_type');
            $edit_address->landmark = $request->get('landmark');
            $edit_address->flat_no = $request->get('flat_no');
            $edit_address->save();
//            $check_duplicate->status = 0;
            $check_duplicate->save();
        }
        if ($check_duplicate == Null) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false
            ]);
        }

    }

    public function postDeleteUserAddress(Request $request)
    {
        /*Session::flash('warning', 'Edit / Delete Property has been disabled in the Demo Panel. We will provide the enabled features in the main clone script!');
        return response()->json([
            'success' => true
        ]);*/
        $validator = Validator::make($request->all(), [
            'address_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false
            ]);
        }

        $get_address = UserAddress::query()->where('id', $request->get('address_id'))->first();
        if ($get_address != Null) {
            $get_address->status = 0;
            $get_address->save();
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false
            ]);
        }
    }

    public function postDeleteUserProfileImage(Request $request)
    {
        $user = User::where('id', Auth::guard("user")->user()->id)->first();
//        if ($user->avatar != Null) {
//            if (\File::exists(public_path('/assets/images/profile-images/customer/' . $user->avatar))) {
//                \File::delete(public_path('/assets/images/profile-images/customer/' . $user->avatar));
//            }
//            $user->avatar = Null;
//            $user->save();
//            return response()->json([
//                'success' => true
//            ]);
//        }
        return response()->json([
            'success' => false
        ]);
    }

    public function postUpdateUserProfileDetails(Request $request)
    {
        /*Session::flash('warning', 'Edit / Delete Property has been disabled in the Demo Panel. We will provide the enabled features in the main clone script!');
        return redirect()->back();*/
//        dd($request->all());
        Session::flash('success', 'Profile Updated successfully');
        $user = User::query()->where('id', Auth::guard("user")->user()->id)->first();
        if ($request->file('user_avtar')) {
            if (\File::exists(public_path('/assets/images/profile-images/customer/' . $user->avatar))) {
                \File::delete(public_path('/assets/images/profile-images/customer/' . $user->avatar));
            }
            $file = $request->file('user_avtar');
            $filename = rand(1, 9) . date('sihYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . '/assets/images/profile-images/customer/', $filename);
            $user->avatar = $filename;
            $user->save();
        }
        if ($request->get('email') != Null) {
            $check_email = User::query()->where('email', $request->get('email'))->first();
            if ($check_email == Null) {
                $user->email = $request->get('email');
                $user->save();
            }else{
                if ($check_email->id != $user->id){
                    Session::flash('success', 'Email Already Exist');
                }
            }
        }

        $user->first_name =  $request->get('name');
        /*$user->last_name =  $request->get('lastname');*/
        $user->emergency_contact =  $request->get('full_number_emergency') != null ? $request->get('full_number_emergency') : '';
        $user->save();
        if ($request->get('contact_details') != Null &&$request->get('contact_numbers') != Null &&$request->get('country_code') != Null) {
            $contact_details = filter_var($request->get('contact_numbers'), FILTER_SANITIZE_NUMBER_INT);
            $check_contact_details = User::query()->where('contact_number', $contact_details)->where('country_code', $request->get('country_code'))->first();
            if ($check_contact_details == Null) {
                $user->contact_number = $contact_details;
                $user->country_code = $request->get('country_code');
                $user->save();
            }else{
                if ($check_contact_details->id != $user->id){
                    Session::flash('success', 'Contact Number Already Exist');
                }
            }
        }
//        Session::flash('success', 'Profile Updated successfully');
        return redirect()->route('get:user_profile');
    }

    public function postFevouriteRestaurant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
            'type' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false
            ]);
        }

        $user_get = User::query()->where('id', Auth::guard("user")->user()->id)->first();
        $restaurant_get = StoreDetails::query()->where('id', $request->get('restaurant_id'))->first();
        if ($user_get != Null && $restaurant_get != Null) {
            if ($request->get('type') == "add") {
                $add_fev_restaurant = new UserFavouriteStore();

                $add_fev_restaurant->user_id = Auth::guard("user")->user()->id;
                $add_fev_restaurant->store_id = $restaurant_get->id;
                $add_fev_restaurant->service_cat_id = $this->food_delivery;
                $add_fev_restaurant->status = 1;
                $add_fev_restaurant->save();
            }
            if ($request->get('type') == "remove") {
                UserFavouriteStore::query()->where('user_id', Auth::guard("user")->user()->id)->where('store_id', $restaurant_get->id)->delete();
            }
            return response()->json([
                'success' => true,
                'type' => $request->get('type')
            ]);
        } else {
            return response()->json([
                'success' => false
            ]);
        }
    }

    public function getCityList()
    {
        $cities = WorldCities::query()->select('id', 'name')->where('parent_id', '!=', 0)->where('type', 'city')->get();
        if ($cities != Null) {
            return $cities;
        } else {
            return response()->json([
                'success' => false
            ]);
        }
    }

    public function getAddUserCard(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'holder_name' => 'required',
            'card_number' => 'required',
            'expire_date' => 'required',
            'cvv' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false
            ]);
        }
        $exp_date_arr =  explode("-",$request->get('expire_date'));
        $year = isset($exp_date_arr[0])?$exp_date_arr[0]:0;
        $month = isset($exp_date_arr[1])?$exp_date_arr[1]:1;
        $user_id = Auth::guard("user")->user()->id;
        $get_total_card =UserCardDetails::query()->where('user_id',$user_id)->count();

        if($get_total_card < 5)
        {
            $card_details = new UserCardDetails();
            $card_details->user_id =$user_id ;
            $card_details->holder_name = $request->get('holder_name');
            $card_details->card_number = $request->get('card_number');
            $card_details->month = $month;
            $card_details->year = $year;
            $card_details->cvv = $request->get('cvv');
            $card_details->save();

            Session::flash('success', 'Card Details successfully addded...');
            return  redirect()->back();

        }else{
            Session::flash('error', 'sorry, you can add only max 5 cards...');
            return  redirect()->back();
        }

    }

    public function getDeleteUserCard(Request $request)
    {
        /*Session::flash('warning', 'Edit / Delete Property has been disabled in the Demo Panel. We will provide the enabled features in the main clone script!');
        return response()->json([
            'success' => true
        ]);*/
        $user_id = Auth::guard("user")->user()->id;
        $validator = Validator::make($request->all(), [
            'card_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errorMsg'=> "Card details not found :)",
            ]);
        }

        $card_details = UserCardDetails::query()->where('id', $request->get('card_id'))->where('user_id',$user_id)->first();
        if ($card_details != Null) {
            $card_details->delete();
            return response()->json([
                'success' => true,
                'errorMsg'=> "Your card details delete successfully :)",
            ]);
        } else {
            return response()->json([
                'success' => false,
                'errorMsg'=> "Card details not found :)",

            ]);
        }
    }

    public function getAboutUs()
    {

        $page_setting = PageSettings::query()->select('description')
            ->where('name', 'LIKE', "%about%")
//            ->where('name', 'about-us')
            ->first();
//        dd($page_setting);
        if ($page_setting != Null) {
            return view('front.pages.about_us', compact('page_setting'));
        } else {
            return view('front.pages.about_us');
        }
    }

    public function getFaq()
    {
        $page_setting = PageSettings::query()->select('description')->where('name', 'LIKE', "%faq%")->first();
        if ($page_setting != Null) {
            return view('front.pages.faq', compact('page_setting'));
        } else {
            return view('front.pages.faq');
        }
    }

    public function getTermsCondition()
    {

        $page_setting = PageSettings::query()->select('description')->where('name', 'LIKE', "%terms%")->first();
        if ($page_setting != Null) {
            return view('front.pages.terms', compact('page_setting'));
        } else {
            return view('front.pages.terms');
        }
    }

    public function getPrivacy()
    {
        $page_setting = PageSettings::query()->select('description')->where('name', 'LIKE', "%privacy%")->first();
        if ($page_setting != Null) {
            return view('front.pages.privacy', compact('page_setting'));
        } else {
            return view('front.pages.privacy');
        }
    }
    public function getContactus()
    {
        $page_setting = PageSettings::query()->select('name','description')->where('name', 'LIKE', "%contact%")->first();
        if ($page_setting != Null) {
            return view('front.pages.contact-us', compact('page_setting'));
        } else {
            return view('front.pages.contact-us');
        }
    }


    public function checkEmail(Request $request)
    {
        if ($request->input('id') == "user") {
            if ($request->input('check_for') == "edit") {
                $checkemail = User::query()->where('email', $request->input('email'))->first();
                if (count($checkemail) >= 1) {
                    if ($checkemail->id != $request->input('user_id')) {
                        return "taken";
                    } else {
                        return "not_taken";
                    }
                } else {
                    return "not_taken";
                }
            } else {
                $checkemail = User::query()->where('email', $request->input('email'))->first();
                if (count($checkemail) >= 1) {
                    return "taken";
                } else {
                    return "not_taken";
                }
            }
        }
    }

    public function postOrderStatus(Request $request)
    {
        $slug = $request->get('slug');
        $orderid = $request->get('orderid');
        $change_status = $request->get('order_status');

        $order_deliveries = UserProductBooking::query()->where('id', $orderid)->first();
        if ($order_deliveries == Null) {
            return response()->json([
                'success' => false
            ]);
        }
        if ($order_deliveries->status < 4) {
            if ($order_deliveries->status == 3) {
                return response()->json([
                    'success' => false
                ]);
            }

            $order_deliveries->status = 4;
            $order_deliveries->cancel_by = "user";
            if ($request->get('cancel_reason') != Null) {
                $order_deliveries->cancel_reason = $request->get('cancel_reason');
            }
            $order_deliveries->save();
            $order_status = UserProductBookingStatus::query()->where('order_id', $orderid)->where('status', 4)->first();
            if ($order_status == Null) {
                $order_status = new UserProductBookingStatus();
                $order_status->order_id = $order_deliveries->id;
                $order_status->status = 4;
                $order_status->save();
            }
            ProviderUserRunningService::query()->where('service_cat_id', $this->food_delivery)->where('user_id', $orderid)->where('booking_id', $order_deliveries->id)->delete();
        }
//        $order_deliveries->status = $change_status;
//        if ($change_status == 4) {
//            if (Auth::user()->roles(Auth::user()->id)->first()->name == "res_owner") {
//                $order_deliveries->cancel_by = "restaurant_admin";
//                $order_deliveries->cancel_reason = $request->get('cancel_reason');
//            }
//            if (Auth::user()->roles(Auth::user()->id)->first()->name == "admin") {
//                $order_deliveries->cancel_by = "admin";
//                $order_deliveries->cancel_reason = $request->get('cancel_reason');
//            }
//            if (Auth::user()->roles(Auth::user()->id)->first()->name == "user") {
//                $order_deliveries->cancel_by = "user";
//                $order_deliveries->cancel_reason = $request->get('cancel_reason');
//            }
//        }
//        $order_deliveries->save();

//        $check = OrderStatus::where('order_id', $order_deliveries->id)->where('status', $change_status)->first();
//        if ($check == Null) {
//            $order_status = new OrderStatus();
//            $order_status->order_id = $order_deliveries->id;
//            $order_status->status = $change_status;
//            $order_status->save();
//        }

//        $order_status = OrderStatus::where('order_id', $order_deliveries->id)->get();
//        $count = count($order_status);
//        if ($change_status == "cancelled") {
//            $order_status = new OrderStatus();
//            $order_status->order_id = $order_deliveries->id;
//            $order_status->status = $request->get('order_status');
//            $order_status->order_time = date('y-m-d h:i:s');
//            $order_status->save();
//        }
//        if ($count >= 1) {
//            if ($count >= 1 && $change_status == "pending") {
//                OrderStatus::where('order_id', $order_deliveries->id)->where('status', '!=', "pending")->delete();
//            }
//            if ($count >= 2 && $change_status == "approved") {
//                OrderStatus::where('order_id', $order_deliveries->id)->where('status', '=', "processing")->delete();
//                OrderStatus::where('order_id', $order_deliveries->id)->where('status', '=', "ongoing")->delete();
//                OrderStatus::where('order_id', $order_deliveries->id)->where('status', '=', "completed")->delete();
//            }
//            if ($count >= 3 && $change_status == "processing") {
//                OrderStatus::where('order_id', $order_deliveries->id)->where('status', '=', "ongoing")->delete();
//                OrderStatus::where('order_id', $order_deliveries->id)->where('status', '=', "completed")->delete();
//            }
//            if ($count >= 4 && $change_status == "ongoing") {
//                OrderStatus::where('order_id', $order_deliveries->id)->where('status', '=', "completed")->delete();
//            }
//            if ($count == 1 && $change_status == "approved" || $count == 2 && $change_status == "processing" || $count == 3 && $change_status == "ongoing" || $count == 4 && $change_status == "completed") {
//                $order_status = new OrderStatus();
//                $order_status->order_id = $order_deliveries->id;
//                $order_status->status = $request->get('order_status');
//                $order_status->order_time = date('y-m-d h:i:s');
//                $order_status->save();
//            }
//        } else {
//            $order_status = new OrderStatus();
//            $order_status->order_id = $order_deliveries->id;
//            $order_status->status = $request->get('order_status');
//            $order_status->order_time = date('y-m-d h:i:s');
//            $order_status->save();
//        }

//        if ($change_status == "approved") {
//            Session::flash('success', 'Order Approved successfully!');
//        }
//        if ($change_status == "processing") {
//            Session::flash('success', 'Order Processing!');
//        }
//        if ($change_status == "ongoing") {
//            Session::flash('success', 'Order Ongoing!');
//        }
//        if ($change_status == "completed") {
//            Session::flash('success', 'Order Completed!');
//        }
        Session::flash('success', 'Order Cancelled!');
        return response()->json([
            'success' => true
        ]);
    }

    //code for get product topping data
    public function postGetProductToppings(Request $request)
    {
//        return $request;
        $store_id = $request->get('rest_id');
        $prod_id = $request->get('prodId');
        $cart_id = $request->get('cart_id');

        if ($store_id > 0 && $prod_id > 0) {
            $cart_size_id = 0;
            $cart_option_id_arr = "";
            $cart_topping_id_arr = "";
            if ($cart_id > 0) {
                $temp_cart_data = UserOrderCart::query()->where('id', $cart_id)->where('order_add_by',1)->first();
                if ($temp_cart_data != Null) {
                    $cart_size_id = $temp_cart_data->size_id;
//                    $cart_option_id_arr = explode(",",$temp_cart_data->option_id);
                    $cart_option_id_arr = $temp_cart_data->option_id;
//                    $cart_topping_id_arr = explode(",",$temp_cart_data->topping_id);
                    $cart_topping_id_arr = $temp_cart_data->topping_id;
                }
            }

            $get_products = StoreProductDetails::query()->select('store_product_details.id',
                'store_product_details.image',
                'store_product_details.name',
                'store_product_details.food_type',
                'store_product_details.amount',
                'store_product_details.discount_amount',
                'store_product_details.discount_type',
                'store_product_details.description',
                'store_product_category.name as category_name',
                'store_product_category.id as category_id'

            )
                ->join('store_product_category', 'store_product_category.id', 'store_product_details.product_cate_id')
                ->where('store_product_details.store_id', $store_id)
                ->where('store_product_details.id', $prod_id)
                ->where('store_product_details.status', 1)
                ->where('store_product_category.status', 1)
                ->first();

//            $store_product_categories = array_unique(Arr::pluck($get_products, 'category_name', 'category_id'));
//            $get_products = $get_products->groupBy('category_id');
            if ($get_products != Null) {
                $product = $get_products;

                $category_product_data = [];
//                foreach ($get_products as $key => $product) {

                $count_product = 1;
//                    $get_count_product = count($product);
                $product_size_arr = [];
//                    foreach ($products as $Pkey=>$product) {

                $product_amount = $product->amount;
                $product_customize_arr = [];
                $product_size_arr = [];
                $product_size = StoreProductSize::query()->select('store_product_size.id',
                    'store_product_size.name',
                    'store_product_details.discount_amount',
                    'store_product_details.discount_type',
                    'store_product_size.amount')
                    ->join('store_product_details', 'store_product_details.id', 'store_product_size.product_id')
                    ->where('product_id', $product->id)
                    ->orderBy('amount', 'asc')
                    ->get();
                $checked = "";
                foreach ($product_size as $size_key => $size) {
                    if ($size_key == 0) {
                        $product_amount = $size->size_selling_price;
                    }
                    if ($size->id == $cart_size_id) {
                        $checked = "1";
                    } else {
                        $checked = "0";
                    }

                    $discount_amount = StoreProductDetails::getDiscountPrice($size->amount,$size->discount_amount,$size->discount_type);
                    $discount_amount_count = $size->amount;
                    $amount = $discount_amount;
//                    $discount_amount = $discount_amount;
                    if ($discount_amount == $size->amount){
                        $amount = $size->amount;
                        $discount_amount_count = 0;
                    }


                    $product_size_arr[] = [
                        "id" => $size->id,
                        "name" => $size->name,
//                        "amount" => isset($amount)?"$".round($amount,2):"",
                        "amount" => $amount,
//                        "amount" => $size->amount,
                        "discount_amount" => ($discount_amount_count > 0 )? round($discount_amount_count,2):"",
                        "category_name" => "Quantity",
                        "checked" => $checked,
                        "selection_type" => 1,
                    ];
                }

                if ($product->discount_type == 1) {
                    $price_discount = round((($product->amount) - ($product->discount_amount)), 2);
//                        $price_discount = $price_discount - 0;
                } elseif ($product->discount_type == 2) {
                    $price_discount = round((($product->amount) - (($product->amount) * ($product->discount_amount)) / (100)), 2);
//                        $price_discount = $price_discount - 0;
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

                if ($product_size_arr != Null) {
                    $product_customize_arr[] = [
                        "category_name" => "Quantity",
                        'category_id' => 0,
                        "type" => "qty",
                        "selection_type" => 1,
                        "customize_type" => 1,
                        "options" => $product_size_arr
                    ];
                }
                $total_size = count($product_size_arr);

                $product_options = StoreProductOptions::query()->select('store_product_options.id',
                    'store_product_options.name',
                    'store_product_options.amount',
                    DB::raw('(CASE
                        WHEN  FIND_IN_SET(store_product_options.id, "' . $cart_option_id_arr . '") THEN "1"
                        ELSE "0"
                        END) AS checked'),
                    'store_product_customize_category.id as category_id',
                    'store_product_customize_category.name as category_name',
                    'store_product_customize_category.selection_type')
                    ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_options.category_id')
                    ->where('store_product_options.product_id', $product->id)->get();

                $product_options_category = $product_options->pluck('selection_type', 'category_name')->toArray();
                $product_options_category_id = $product_options->pluck('category_id', 'category_name')->toArray();
                $product_options = $product_options->groupBy('category_name')->toArray();
                $checked_opt = "";
                foreach ($product_options as $option_key => $option) {

                    $product_customize_arr[] = [
                        'category_name' => $option_key,
                        'category_id' => $product_options_category_id[$option_key],
                        "type" => "opt",
                        'selection_type' => $product_options_category[$option_key],

                        "customize_type" => 2,
                        'options' => $option
                    ];
                }
                $total_options = count($product_customize_arr);

                $product_toppings = StoreProductToppings::query()->select('store_product_toppings.id',
                    'store_product_toppings.name',
                    'store_product_toppings.amount',
                    DB::raw('(CASE
                        WHEN  FIND_IN_SET(store_product_toppings.id, "' . $cart_topping_id_arr . '") THEN "1"
                        ELSE "0"
                        END) AS checked'),
                    'store_product_customize_category.id as category_id',
                    'store_product_customize_category.name as category_name',
                    'store_product_customize_category.selection_type')
                    ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')
                    ->where('store_product_toppings.product_id', $product->id)->get();

                $product_toppings_category = $product_toppings->pluck('selection_type', 'category_name')->toArray();
                $product_toppings_category_id = $product_toppings->pluck('category_id', 'category_name')->toArray();
                $product_toppings = $product_toppings->groupBy('category_name')->toArray();
                $checked_top = "";
                foreach ($product_toppings as $topping_key => $topping) {
                    $product_customize_arr[] = [
                        'category_name' => $topping_key,
                        'category_id' => $product_toppings_category_id[$topping_key],
                        "type" => "top",
                        'selection_type' => $product_toppings_category[$topping_key],
                        "customize_type" => 3,
                        'options' => $topping
                    ];
                }
                $total_toppings = count($product_customize_arr);
                $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
                $currency = $user_currency->ratio;

                $product_data = [
                    "product_id" => $product->id,
                    "product_image" => $product->image != Null ? url('/assets/images/store-images/product-images/' . $product->image) : '',
                    "product_name" => $product->name,
                    "food_type" => $product->food_type,
                    "product_amount" => round($product_price * $currency, 2),
//                    "discount_amount" => round($price_discount * $currency, 2),
//                    "offer_discount" => round($offer_calculation * $currency, 2),
                    "description" => $product->description ? $product->description : '',
                    "customize_list" => $product_customize_arr
                ];
                $count_product = $count_product + 1;
//                    }
                /*$category_product_data[] = [
                    /*"category_id" => $key,
                    "category_name" => $store_product_categories[$key],
                    "product_count" => $count_product,*/
//                        "product_list" => $product_data[$key]
                /*];*/
//                }
//                return $product_data;
                return response()->json([
                    'success' => true,
                    "product_data" => $product_data
                ]);
            } else {
                return response()->json([
                    'success' => false
                ]);
            }
        } else {
            return response()->json([
                'success' => false
            ]);
        }
    }

    public function postGetProductToppings_BKP(Request $request)
    {
//        return $request;
        $store_id = $request->get('rest_id');
        $prod_id = $request->get('prodId');
        $cart_id = $request->get('cart_id');

        if ($store_id > 0 && $prod_id > 0) {
            $cart_prod_qty = 0;
            $cart_prod_options_arr = "";
            $cart_prod_toppings_arr = "";
            if ($cart_id > 0) {
                $temp_cart_data = UserOrderCart::query()->where('id', $cart_id)->first();
                if ($temp_cart_data != Null) {
                    $cart_prod_qty = $temp_cart_data->prod_qty;
//                    $cart_prod_options_arr = explode(",",$temp_cart_data->prod_options);
                    $cart_prod_options_arr = $temp_cart_data->prod_options;
//                    $cart_prod_toppings_arr = explode(",",$temp_cart_data->prod_toppings);
                    $cart_prod_toppings_arr = $temp_cart_data->prod_toppings;
                }
            }

            $get_products = StoreProductDetails::query()->select('store_product_details.id',
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
                ->where('store_product_details.store_id', $store_id)
                ->where('store_product_details.id', $prod_id)
                ->where('store_product_details.status', 1)
                ->where('store_product_category.status', 1)
                ->first();

//            $store_product_categories = array_unique(Arr::pluck($get_products, 'category_name', 'category_id'));
//            $get_products = $get_products->groupBy('category_id');
            if ($get_products != Null) {
                $product = $get_products;

                $category_product_data = [];
//                foreach ($get_products as $key => $product) {

                $count_product = 1;
//                    $get_count_product = count($product);
                $product_size_arr = [];
//                    foreach ($products as $Pkey=>$product) {

                $product_amount = $product->amount;
                $product_customize_arr = [];
                $product_size_arr = [];
                $product_size = StoreProductSize::query()->select('store_product_size.id',
                    'store_product_size.name',
                    'store_product_details.discount_amount',
                    'store_product_details.discount_type',
                    'store_product_size.amount')
                    ->join('store_product_details', 'store_product_details.id', 'store_product_size.product_id')
                    ->where('product_id', $product->id)
                    ->orderBy('amount', 'asc')
                    ->get();
                $checked = "";
                foreach ($product_size as $size_key => $size) {
                    if ($size_key == 0) {
                        $product_amount = $size->amount;
                    }
                    if ($size->id == $cart_prod_qty) {
                        $checked = "1";
                    } else {
                        $checked = "0";
                    }

                    $discount_amount = StoreProductDetails::getDiscountPrice($size->amount,$size->discount_amount,$size->discount_type);
                    $discount_amount_count = $size->amount;
                    $amount = $discount_amount;
//                    $discount_amount = $discount_amount;
                    if ($discount_amount == $size->amount){
                        $amount = $size->amount;
                        $discount_amount_count = 0;
                    }


                    $product_size_arr[] = [
                        "id" => $size->id,
                        "name" => $size->name,
//                        "amount" => isset($amount)?"$".round($amount,2):"",
                        "amount" => $amount,
//                        "amount" => $size->amount,
                        "discount_amount" => ($discount_amount_count > 0 )? round($discount_amount_count,2):"",
                        "category_name" => "Quantity",
                        "checked" => $checked,
                        "selection_type" => 1,
                    ];
                }

                if ($product->discount_type == 1) {
                    $price_discount = round((($product->amount) - ($product->discount_amount)), 2);
//                        $price_discount = $price_discount - 0;
                } elseif ($product->discount_type == 2) {
                    $price_discount = round((($product->amount) - (($product->amount) * ($product->discount_amount)) / (100)), 2);
//                        $price_discount = $price_discount - 0;
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

                if ($product_size_arr != Null) {
                    $product_customize_arr[] = [
                        "category_name" => "Quantity",
                        'category_id' => 0,
                        "type" => "qty",
                        "selection_type" => 1,
                        "customize_type" => 1,
                        "options" => $product_size_arr
                    ];
                }
                $total_size = count($product_size_arr);

                $product_options = StoreProductOptions::query()->select('store_product_options.id',
                    'store_product_options.name',
                    'store_product_options.amount',
                    DB::raw('(CASE
                        WHEN  FIND_IN_SET(store_product_options.id, "' . $cart_prod_options_arr . '") THEN "1"
                        ELSE "0"
                        END) AS checked'),
                    'store_product_customize_category.id as category_id',
                    'store_product_customize_category.name as category_name',
                    'store_product_customize_category.selection_type')
                    ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_options.category_id')
                    ->where('store_product_options.product_id', $product->id)->get();

                $product_options_category = $product_options->pluck('selection_type', 'category_name')->toArray();
                $product_options_category_id = $product_options->pluck('category_id', 'category_name')->toArray();
                $product_options = $product_options->groupBy('category_name')->toArray();
                $checked_opt = "";
                foreach ($product_options as $option_key => $option) {

                    $product_customize_arr[] = [
                        'category_name' => $option_key,
                        'category_id' => $product_options_category_id[$option_key],
                        "type" => "opt",
                        'selection_type' => $product_options_category[$option_key],

                        "customize_type" => 2,
                        'options' => $option
                    ];
                }
                $total_options = count($product_customize_arr);

                $product_toppings = StoreProductToppings::query()->select('store_product_toppings.id',
                    'store_product_toppings.name',
                    'store_product_toppings.amount',
                    DB::raw('(CASE
                        WHEN  FIND_IN_SET(store_product_toppings.id, "' . $cart_prod_toppings_arr . '") THEN "1"
                        ELSE "0"
                        END) AS checked'),
                    'store_product_customize_category.id as category_id',
                    'store_product_customize_category.name as category_name',
                    'store_product_customize_category.selection_type')
                    ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')
                    ->where('store_product_toppings.product_id', $product->id)->get();

                $product_toppings_category = $product_toppings->pluck('selection_type', 'category_name')->toArray();
                $product_toppings_category_id = $product_toppings->pluck('category_id', 'category_name')->toArray();
                $product_toppings = $product_toppings->groupBy('category_name')->toArray();
                $checked_top = "";
                foreach ($product_toppings as $topping_key => $topping) {
                    $product_customize_arr[] = [
                        'category_name' => $topping_key,
                        'category_id' => $product_toppings_category_id[$topping_key],
                        "type" => "top",
                        'selection_type' => $product_toppings_category[$topping_key],
                        "customize_type" => 3,
                        'options' => $topping
                    ];
                }
                $total_toppings = count($product_customize_arr);
                $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
                $currency = $user_currency->ratio;

                $product_data = [
                    "product_id" => $product->id,
                    "product_image" => $product->image != Null ? url('/assets/images/store-images/product-images/' . $product->image) : '',
                    "product_name" => $product->name,
                    "food_type" => $product->food_type,
                    "product_amount" => round($product_price * $currency, 2),
//                    "discount_amount" => round($price_discount * $currency, 2),
//                    "offer_discount" => round($offer_calculation * $currency, 2),
//                    "description" => $product->description ? $product->description : '',
                    "customize_list" => $product_customize_arr
                ];
                $count_product = $count_product + 1;
//                    }
                /*$category_product_data[] = [
                    /*"category_id" => $key,
                    "category_name" => $store_product_categories[$key],
                    "product_count" => $count_product,*/
//                        "product_list" => $product_data[$key]
                /*];*/
//                }
//                return $product_data;
                return response()->json([
                    'success' => true,
                    "product_data" => $product_data
                ]);
            } else {
                return response()->json([
                    'success' => false
                ]);
            }


        } else {
            return response()->json([
                'success' => false
            ]);
        }

    }

    public function getUserOtpVerify()
    {
        $user_id = Auth::guard('user')->user()->id;
        $verify = Auth::guard('user')->user()->verified_at;

        if ($verify == Null) {
            $view = view('front.pages.otp_verify', compact('verify', 'user_id'));
            //return view('front.pages.user_details_tabview', compact('user_open_tab', 'restaurants', 'cuisines', 'res_time', 'locations'));
            /*if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }*/
            return $view;
        }
    }

    public function postUserOtpVerify(Request $request)
    {
//        return $request;
        $validator = Validator::make($request->all(), [
                "user_id" => "required",
                "otp" => "required|numeric",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
//        return strlen($request->get('otp')) ;
        if (strlen($request->get('otp')) != 4  ) {
            return response()->json([
                'success' => false,
                'message' => 'Opt Must be 4 Digit Numeric!',
                "message_code" => 9,
            ]);
        }
        $user_details = User::query()->where('id', $request->get('user_id'))->first();
        if ($user_details != Null) {
            $otp = $request->get('otp');
            if ($otp == "1234") {
                UserVerification::query()->where('user_id', $user_details->id)->delete();
                $user_details->status = 1;
                $user_details->verified_at = date('Y-m-d H:i:s');
                $user_details->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully Verify OTP',
                ]);
            } else {
                return response()->json([
                        'success' => false,
                        'message' => 'Entered OTP is Incorrect!',
                    ]);
            }

            //$get_token = UserVerification::where('user_id', $user_details->id)->first();
            //if ($get_token == Null) {
            //    return response()->json([
            //        'success' => false,
            //        'message' => 'Something went to wrong!',
            //    ]);
            //}

            //$settings = GeneralSettings::first();
            //if ($settings == Null || $settings->twilio_service_key == Null || $settings->twilio_auth_token == Null || $settings->twilio_verify_service_key == Null) {
            //    return response()->json([
            //        'success' => false,
            //        'message' => 'Something went to wrong!',
            //    ]);
            //}
            //try {
            //    $twilio = new Client($settings->twilio_service_key, $settings->twilio_auth_token);
            //    $option = [
            //        'To' => $user_details->contact_number,
            //        'VerificationSid' => $get_token->token,
            //    ];
            //    $verification_check = $twilio->verify->v2->services($settings->twilio_verify_service_key)
            //        ->verificationChecks
            //        ->create($otp, $option);
            //    if ($verification_check->status == "approved") {
            //        $verification_sid = $verification_check->sid;
            //        if ($verification_sid != $get_token->token) {
            //            Session::flash('error', 'Something went to wrong!');
            //            return redirect()->back();
            //        }
            //        UserVerification::where('user_id', $user_details->id)->delete();
            //        $user_details->status = 1;
            //        $user_details->verified_at = date('Y-m-d H:i:s');
            //        $user_details->save();
            //        return response()->json([
            //            'success' => true,
            //            'message' => 'Successfully Verify OTP',
            //        ]);
            //    } else {
            //        return response()->json([
            //            'success' => false,
            //            'message' => 'Entered OTP is Incorrect!',
            //        ]);
            //    }
            //} catch (\Exception $e) {
            //    return response()->json([
            //        'success' => false,
            //        "message" => $e->getMessage(),
            //    ]);
            //}
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found!.',
            ]);
        }
    }

    public function postResendOtp(Request $request)
    {
//        return $request;
        $validator = Validator::make($request->all(), [
                "user_id" => "required",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_details = User::query()->where('id', $request->get('user_id'))->first();
        if ($user_details != Null) {

            $new_otp = random_int(1000, 9999);
            $retdata = $this->tokenClassApi->sendUserSmsVerification($user_details->id, $new_otp);

            if ($retdata == "success") {
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully Sent OTP',
                    "message_code" => 9,
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Please try again later..',
                    "message_code" => 9,
                ]);
            }

        } else {
            /*Session::flash('error', 'User not found!.');
            return redirect()->back();*/
            return response()->json([
                'success' => false,
                'message' => 'User not found!.',
                "message_code" => 9,
            ]);
        }
    }

    public function getStoreOrderDetails(Request $request,$order_id)
    {
        $user_id =  Auth::guard("user")->user()->id;
        $order_details = UserProductBooking::query()->select('id', 'store_id', 'user_id', 'user_refund_status','service_cat_id',
            'delivery_people_id', 'order_no', 'user_name', 'store_name', 'delivery_address','payment_type',
            'created_at', 'total_item_cost', 'discount_cost', 'tax_cost', 'delivery_cost', 'order_date_time',
            'packaging_cost', 'total_pay', 'cancel_charge', 'payment_type', 'refund_amount',
            'payment_status', 'status', 'cancel_by', 'cancel_reason','taken_away_type')
            ->where('user_id',$user_id)
            ->where('id', $order_id)
            ->first();

        if ($order_details == Null) {
            return redirect()->back();
        }
        $store_details = StoreDetails::query()->select('store_details.image','store_details.address','providers.contact_number','providers.country_code')
            ->leftJoin('provider_services','provider_services.id','store_details.provider_service_id')
            ->leftJoin('providers','providers.id','provider_services.provider_id')
            ->where('store_details.id', $order_details->store_id)
            ->first();

        $user_details = User::query()->select('contact_number')->where('id', $order_details->user_id)->first();

        $product_details = UserProductBookingQuantity::query()->select('id', 'product_id', 'product_name','product_cat_name', 'num_of_items', 'price_for_one')->where('order_id', $order_details->id)->get()->groupBy('product_cat_name');
        $product_option_list = [];
        foreach ($product_details as $product_list) {
            foreach ($product_list as $product) {
                $size = UserProductBookingSize::query()->where('order_id', $order_details->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                $options = UserProductBookingOptions::query()->where('order_id', $order_details->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                $toppings = UserProductBookingToppings::query()->where('order_id', $order_details->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                $add_ons = array_merge($size, $options, $toppings);
                $add_ons = implode(', ', $add_ons);
                $product_option_list[$product->id] = $add_ons;
            }
        }
        $driver_details = TransportDriverDetails::query()->select(
            'providers.first_name as driver_name', 'providers.contact_number', 'providers.avatar','providers.contact_number','providers.country_code',
            'transport_driver_details.id as driver_details_id',
            'transport_driver_details.vehicle_company', 'transport_driver_details.model_name',
            'transport_driver_details.plat_no', 'transport_driver_details.model_year',
            'transport_driver_details.vehicle_color',
            'transport_driver_details.rating',
            'transport_driver_details.current_lat',
            'transport_driver_details.current_long',
            'transport_vehicle_type.name as vehicle_type_name'
        )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            //->where('providers.status', '=', $status)
            ->where('transport_driver_details.id', $order_details->delivery_people_id)
            ->first();
        $order_status = $this->store_order_status;
        $cancel_charge = (isset($order_details->cancel_charge) && $order_details->cancel_charge>0 )?$order_details->cancel_charge:0;
        //if ($order->taken_away_type != 2){



        $user_open_tab = "order-detail";
        $title_code ="Store Order Details";
        $view = view('front.pages.user_details_tabview', compact('user_open_tab','order_details',
            'product_option_list','order_status', 'product_details', 'store_details', 'user_details', 'driver_details','title_code','cancel_charge'));

        return $view;
    }

    public function getLocation(Request  $request){

        $current_latitude = $request->get('latitude');
        $current_longitude = $request->get('longitude');
        $current_address = "";
        if(!empty($current_latitude) && !empty($current_longitude)){
            $general_settings = GeneralSettings::query()->select('map_key')->first();
            $map_key = trim($general_settings->map_key);

            try {
                $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($current_latitude).','.trim($current_longitude).'&key=' . $map_key;
                $json = file_get_contents($url);
                $data = json_decode($json);
                $status = $data->status;
                /*\Log::info("loc data");
                \Log::info($json);*/
                //if request status is successful
                if($status == "OK"){
                    //get address from json data
                    $current_address = $data->results[0]->formatted_address;

                }else{
                    $current_address =  '';
                }
            }catch (\Exception $e){
                $current_address =  '';
            }

            Cookie::queue("current_address", $current_address, 120);
            Cookie::queue("current_latitude", $current_latitude, 120);
            Cookie::queue("current_longitude", $current_longitude, 120);
        }else{
            $current_address = "";
            if (Cookie::get('current_address')){
                $current_address =  Cookie::get('current_address');
                $current_latitude =  Cookie::get('current_latitude');
                $current_longitude =  Cookie::get('current_longitude');
            }else{
                Cookie::queue("current_address", $current_address, 120);
                Cookie::queue("current_latitude", $current_latitude, 120);
                Cookie::queue("current_longitude", $current_longitude, 120);
            }
        }
        $location_arr = array(
            "current_address"=>$current_address,
            "current_latitude"=>$current_latitude,
            "current_longitude"=>$current_longitude,
        );
        return $location_arr;

    }

    public function getWallet($page=1)
    {

        $user_open_tab = "wallet";
        $title_code ="Manage Wallet";
        $user_id =  Auth::guard("user")->user()->id;
        $per_page_rec = $this->perpageRec;
        $per_page_rec   = ($page > 0)?$per_page_rec:5;

        $get_wallet_history = UserWalletTransaction::query()
                                ->where('user_id',$user_id)
                                ->orderBy('id','desc')
                                ->paginate($per_page_rec);
//                                ->get();
        $total_avail_bal = UserWalletTransaction::query()->select('remaining_balance')
                                ->where('user_id',$user_id)
                                ->orderBy('id','desc')
                                ->first();

        $card_list = UserCardDetails::query()->select('card_details.id', 'card_details.holder_name as card_holder_name', 'card_details.card_number as card_number')
            ->where('user_id','=',$user_id )
            ->orderBy('id','desc')
            ->get();


        return view('front.pages.user_details_tabview', compact('user_open_tab', 'card_list','total_avail_bal','get_wallet_history','title_code'));
    }

    public function postWallet(Request $request){

        $user_open_tab = "wallet";
        $title_code ="Manage Wallet";
        $user_id =  Auth::guard("user")->user()->id;
        $user_details = User::query()->where('id',$user_id)->first();
        if ($user_details != Null) {

            $amount = $request->get('amount');
            $card_id = $request->get('card_id');
            if($amount > 0){
                $check_user_card = UserCardDetails::query()->where('user_id','=',$user_id)->where('id','=',$card_id)->first();
                if($check_user_card != Null) {

                    $get_current_wallet_data = UserWalletTransaction::query()->where('user_id', $user_id)->orderBy('id', 'desc')->first();

                    $add_wallet = new UserWalletTransaction();
                    $add_wallet->user_id = $user_id;
//                    $add_wallet->wallet_transaction_type = 0;
                    $add_wallet->transaction_type = 1;
                    $add_wallet->amount = $amount;
                    $add_wallet->subject = "Credited";
                    if ($get_current_wallet_data != Null) {
                        $add_wallet->remaining_balance = $get_current_wallet_data->remaining_balance + $amount;
                    } else {
                        $add_wallet->remaining_balance = $amount;
                    }
                    $add_balance->subject_code = 1;
                    $add_balance->order_no = 'user';
                    $add_wallet->save();

                    Session::flash('success', 'Amount Has been successfully added...');
                    return redirect()->back();
                }else{
                    Session::flash('warning', 'Invalid Card Detail!.');
                    return redirect()->back();
                }
            }else{
                Session::flash('warning', 'amount must be greater than 0!.');
                return redirect()->back();
            }
        } else {
            Session::flash('error', 'User not found!.');
            return redirect()->back();
        }
    }
}
