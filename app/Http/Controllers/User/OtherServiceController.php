<?php

namespace App\Http\Controllers\user;

use App\Http\Requests\OtherServiceSubCategoryRequest;
use App\Jobs\AutoMail;
use App\model\GoPrimeServices;
use App\model\OtherServiceCategory;
use App\model\OtherServiceProviderDetails;
use App\model\OtherServiceProviderPackages;
use App\model\ProviderCommissionLimit;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserPackageBooking;
use App\model\UserPackageBookingQuantity;
use App\model\UserPurchaseGoPrimeDetails;
use App\model\UserRatings;
use App\model\UserReferHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Classes\TokenClassApi;
use App\Classes\UserClassApi;
use App\model\GeneralSettings;
use App\model\PageSettings;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\WorldCurrency;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Twilio\Rest\Client;

class OtherServiceController extends Controller
{
    //
    private $on_demand_service_id_array;
    private $userClassapi;
    private $order_status;
    private $notificationClass;

    public function __construct(AdminClass $adminClass, NotificationClass $notificationClass,UserClassApi $userClassapi)
    {
        $this->userClassapi = $userClassapi;
        $service_cat = ServiceCategory::query()->whereIN('category_type', [3, 4])->where('status', 1)->get()->pluck('id')->toArray();
        $this->on_demand_service_id_array = $service_cat;
        $this->order_status = [2, 3, 6, 7, 8];
        $this->notificationClass = $notificationClass;
    }
    public function getServiceBooking($service_name ="")
    {
        $exitCode = Artisan::call('cache:clear');
        if(!Auth::guard("user")->check()){
            Session::flash('error', 'Please Login before use provider service..!');
            return redirect()->route('get:homepage');
        }

        if($service_name !="")
        {
            if (!Auth::guard("user")->check()) {
                Auth::logout();
            }
            $singel_service_cat  = ServiceCategory::query()->whereIN('category_type', [3, 4])->where('status', 1)->where('slug',$service_name)->first();

            if($singel_service_cat != Null)
            {
                $current_address = Cookie::get('current_address');
                $current_latitude = Cookie::get('current_latitude');
                $current_longitude =  Cookie::get('current_longitude');

                $service_cat_id = $singel_service_cat->id;
                $service_cat_name = $singel_service_cat->name;
                $service_cat_slug = $singel_service_cat->slug;

                $sub_service_category_lists =OtherServiceCategory::query()->where('service_cat_id',$service_cat_id)->where('status','1')->get();

                return view('front.pages.other_service.service-booking',compact('current_address','current_latitude','current_longitude','sub_service_category_lists','service_cat_name','service_cat_slug'));
            }else{
                Session::flash('error', 'Please select proper select!.');
                return redirect()->back();
            }

        }else{
            Session::flash('error', 'Please select proper select!.');
            return redirect()->back();
        }

    }

    public function postgetProviderLists(Request $request){

//            dd($request->all());
            $sub_category_id = $request->get('category');
            $source_address = $request->get('source_address');
            $source_lat = $request->get('source_lat');
            $source_long = $request->get('source_long');
            $provider_not_found_html ="";
            $provider_not_found_html .= view('front.pages.other_service.provider-not-found');

            if($source_lat !="" && $source_long !="")
            {

                $restrict_area_id = $this->userClassapi->checkServiceAvailableLocationNew($source_lat, $source_long);

                if ($restrict_area_id == 1) {
                    $data = array(
                        "success" => false,
                        "errorMsg" => "Service is not available in your region, please try with other location!",
                        'html_data'=>$provider_not_found_html
                    );
                    return json_encode($data);
                }

                $get_sub_category_details = OtherServiceCategory::query()->where('id',$sub_category_id)->first();
                if($get_sub_category_details != Null)
                {
                    $service_category_id = $get_sub_category_details->service_cat_id;

                    $provider_commission_limit_amount = "zero";
                    /*$area_wise_provider_commission_limit = ProviderCommissionLimit::query()->where("area_id", $restrict_area_id)->first();
                    if ($area_wise_provider_commission_limit != Null) {
                        $provider_commission_limit_amount = ($area_wise_provider_commission_limit->provider_commission_limit != Null) ? $area_wise_provider_commission_limit->provider_commission_limit : 0;
                    }*/
                    $htmlData = "";

                    $get_providers = Provider::query()->select('providers.id',
//                        'providers.provider_commission_limit_amount',
                        'providers.first_name as name',
                        'providers.gender',
                        'providers.avatar',
                        'providers.service_radius',
                        'provider_services.id as provider_service_id',
                        'other_service_provider_details.rating',
                        'other_service_provider_details.total_completed_order',
                        'other_service_provider_details.lat',
                        'other_service_provider_details.long',
                        DB::raw(('other_service_provider_details.lat,other_service_provider_details.long, ( round( 6367 * acos( cos( radians(' . $source_lat . ') ) * cos( radians( other_service_provider_details.lat ) ) * cos( radians( other_service_provider_details.long ) - radians(' . $source_long . ') ) + sin( radians(' . $source_lat . ') ) * sin( radians( other_service_provider_details.lat ) ) ),2 ) ) AS distance'))
                    )
                        ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                        ->join('other_service_provider_packages', 'other_service_provider_packages.provider_service_id', '=', 'provider_services.id')
                        ->join('other_service_provider_details', 'other_service_provider_details.provider_id', '=', 'providers.id')
                        ->where('provider_services.service_cat_id', $service_category_id)
                        ->where('other_service_provider_packages.sub_cat_id', $sub_category_id)
                        ->where('provider_services.current_status', 1)
                        ->where('other_service_provider_packages.status', 1)
                        ->whereNull('providers.deleted_at')
                        ->where('provider_services.status', 1);
                    /*if ($provider_commission_limit_amount != "zero") {
                        $get_providers->where('providers.provider_commission_limit_amount', "<", $provider_commission_limit_amount);
                    }*/
                    //->havingRaw('providers.service_radius >= distance')
                    //->orderByRaw("distance")
                    $providers = $get_providers->distinct()
                        ->get();

                    if ($providers->isEmpty()) {
                        $data = array(
                            "success" => false,
                            "errorMsg" => "providers not found !",
                            'html_data'=>$provider_not_found_html
                        );
                        return json_encode($data);
                    }
                    $provider_list = Null;
                    foreach ($providers as $provider) {
//                        if ($provider->distance <= $provider->service_radius) {
                            $total_rating_count = UserRatings::query()->join('user_service_package_booking', 'user_service_package_booking.id', '=', 'user_rating.package_book_id')
                                ->where('user_service_package_booking.provider_id', $provider->id)->count();

                            $provider_rating = 0;
                            $provider_rating_count = UserRatings::query()->select(DB::raw('avg(rating) as ratings'))->join('user_service_package_booking', 'user_service_package_booking.id', '=', 'user_rating.package_book_id')
                                ->where('user_service_package_booking.provider_id', $provider->id)->first();
                            if ($provider_rating_count != Null) {
                                $provider_rating = round($provider_rating_count->ratings, 1) - 0;
                            }

                            if ($provider->avatar != Null) {
//                                $avatar = url(\App\Classes\NotificationClass::getWebCompanyWiseImage("provider_avatar", $provider->avatar));
//                                $path = \App\Classes\NotificationClass::getWebCompanyWiseImage("provider_avatar", $provider->avatar);
                                $path = '/assets/images/profile-images/provider/'.$provider->avatar;
                                if(public_path($path)){
                                    $avatar = url($path);
                                }else{
                                    $avatar = asset('/assets/images/profile-images/provider/default.png');
                                }
                            } else {
                                $avatar = asset('/assets/images/profile-images/provider/default.png');
                            }

                            $distance = ($provider->distance != 0) ? round($provider->distance, 1) : 0;

                            $provider_id = $provider->id;
                            $provider_name=  trim($provider->name);
                            $provider_profile_image = $avatar;
                            $average_rating =  $provider_rating;
                            $num_of_rating = $total_rating_count;
                            $distance=  $distance . " km Away";
                            $total_completed_order =  $provider->total_completed_order;
                            $provider_service_id =  $provider->provider_service_id;


                            $htmlData .= view('front.pages.other_service.singel-provider',compact('provider_id','provider_service_id','provider_name',
                                            'provider_profile_image','average_rating','num_of_rating','distance','total_completed_order'
                                             ));

                            $provider_list[] = array(
                                "title"=> trim($provider->name),
                                "lat"=> ($provider->lat !="")?$provider->lat:0,
                                "lng"=>($provider->long !="")?$provider->long:0,
                            );
//                        }
                    }

                    $markers  =  $provider_list;
                    if ($markers != null){
                        $dataArray =  array(
                            'success'=>true,
                            'providerLatLong'=>$markers,
                            'html_data'=>($htmlData!="")?$htmlData:$provider_not_found_html
                        );
                        return json_encode($dataArray);
                    }else{
                        $data = array(
                            "success" => false,
                            "errorMsg" => "providers not found !",
                            'html_data'=>$provider_not_found_html
                        );
                        return json_encode($data);
                    }
                }else{
                    $data = array(
                        'success'=>false,
                        'errorMsg'=>"Please select proper category",
                        'html_data'=>$provider_not_found_html
                    );
                    return json_encode($data);
                }
            }else{
                $data = array(
                    'success'=>false,
                    'errorMsg'=>"user address not found!",
                    'html_data'=>$provider_not_found_html
                );
                return json_encode($data);
            }
    }

    public function getProviderWithPackageLists(Request $request){

        $provider_service_id = $request->get('provider_service_id');
        $sub_cat_id = $request->get('sub_cat_id');
        $source_lat = ($request->get('source_lat')!="")?$request->get('source_lat'):0;
        $source_long = ($request->get('source_long') !="")?$request->get('source_long'):0;
        $provider_not_found_html ="";
        $provider_not_found_html .= view('front.pages.other_service.provider-not-found');

        if($provider_service_id != "" || $sub_cat_id !="" ){

            $get_provider = ProviderServices::query()->where('id',$provider_service_id)->first();
            if($get_provider != Null){

                $provider_id = $get_provider->provider_id;
                $service_cat_id = $get_provider->service_cat_id;

                $get_provider_package = OtherServiceProviderPackages::query()->where('provider_service_id',$provider_service_id)
                                        ->where('service_cat_id',$service_cat_id)->where('status','=',1)->get();

                if($get_provider_package != Null){

                    $get_providers = Provider::query()->select('providers.id',
//                        'providers.provider_commission_limit_amount',
                        'providers.first_name as name',
                        'providers.gender',
                        'providers.avatar',
                        'providers.service_radius',
                        'provider_services.id as provider_service_id',
                        'other_service_provider_details.rating',
                        'other_service_provider_details.total_completed_order',
                        'other_service_provider_details.lat',
                        'other_service_provider_details.long',
                        DB::raw(('other_service_provider_details.lat,other_service_provider_details.long, ( round( 6367 * acos( cos( radians(' . $source_lat . ') ) * cos( radians( other_service_provider_details.lat ) ) * cos( radians( other_service_provider_details.long ) - radians(' . $source_long . ') ) + sin( radians(' . $source_lat . ') ) * sin( radians( other_service_provider_details.lat ) ) ),2 ) ) AS distance'))
                    )
                        ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                        ->join('other_service_provider_packages', 'other_service_provider_packages.provider_service_id', '=', 'provider_services.id')
                        ->join('other_service_provider_details', 'other_service_provider_details.provider_id', '=', 'providers.id')
                        ->where('provider_services.service_cat_id', $service_cat_id)
                        ->where('other_service_provider_packages.sub_cat_id', $sub_cat_id)
                        ->where('provider_services.current_status', 1)
                        ->where('provider_services.status', 1)
                        ->where('providers.id', $provider_id)
                        ->whereNull('providers.deleted_at')
                        ->first();

                    if($get_providers != Null){

                        $total_rating_count = UserRatings::query()->join('user_service_package_booking', 'user_service_package_booking.id', '=', 'user_rating.package_book_id')
                            ->where('user_service_package_booking.provider_id', $get_providers->id)->count();

                        $provider_rating = 0;
                        $provider_rating_count = UserRatings::query()->select(DB::raw('avg(rating) as ratings'))->join('user_service_package_booking', 'user_service_package_booking.id', '=', 'user_rating.package_book_id')
                            ->where('user_service_package_booking.provider_id', $get_providers->id)->first();
                        if ($provider_rating_count != Null) {
                            $provider_rating = round($provider_rating_count->ratings, 1) - 0;
                        }

                        if ($get_providers->avatar != Null) {
//                            $path = \App\Classes\NotificationClass::getWebCompanyWiseImage("provider_avatar", $get_providers->avatar);
                            $path = '/assets/images/profile-images/provider/'.$get_providers->avatar;
                            if(public_path($path)){
                                $avatar = url($path);
                            }else{
                                $avatar = asset('/assets/images/profile-images/provider/default.png');
                            }
                        } else {
                            $avatar = asset('/assets/images/profile-images/provider/default.png');
                        }

                        $distance = ($get_providers->distance != 0) ? round($get_providers->distance, 1) : 0;

                        $provider_id = $get_providers->id;
                        $provider_name=  trim($get_providers->name);
                        $provider_profile_image = $avatar;
                        $average_rating =  $provider_rating;
                        $num_of_rating = $total_rating_count;
                        $distance=  $distance . " km Away";
                        $total_completed_order =  $get_providers->total_completed_order;
                        $provider_service_id =  $get_providers->provider_service_id;

                        //code for provider package list

                        $provider_package_category = $provider_package_list = OtherServiceProviderPackages::query()
                                            ->select(
                                                    'other_service_sub_category.id as sub_cat_id','other_service_sub_category.name as sub_cat_name',
                                                    'other_service_provider_packages.id as id','other_service_provider_packages.name as package_name',
                                                    'other_service_provider_packages.price as package_price','other_service_provider_packages.max_book_quantity as package_max_book_quantity'
                                            )
                                            ->join('other_service_sub_category','other_service_sub_category.id','other_service_provider_packages.sub_cat_id')
                                            ->where('other_service_provider_packages.provider_service_id',$provider_service_id)
                                            ->where('other_service_provider_packages.service_cat_id',$service_cat_id)
                                            ->where('other_service_provider_packages.status',1);

                        $provider_package_lists =$provider_package_list->orderBy('sub_cat_id','desc')->get()->groupBy('sub_cat_id');

                        $provider_package_category = $provider_package_category->orderBy('sub_cat_id','desc')->groupBy('sub_cat_id')->get();

                        $htmlData  = "";
                        $htmlData .= view('front.pages.other_service.singel-provider-with-package-list',compact('provider_id','provider_service_id','provider_name',
                            'provider_profile_image','average_rating','num_of_rating','distance','total_completed_order','provider_package_category','provider_package_lists'
                        ));



                        //get provider with its packagiest
                        $data = array(
                            'success'=>true,
                            'provider_with_package_list'=>$htmlData
                        );
                        return json_encode($data);

                    }else{
                        //get provider with its packagiest
                        $data = array(
                            'success'=>false,
                            'errorMsg'=>"Provider not found!",
                            'html_data'=>$provider_not_found_html
                        );
                        return json_encode($data);
                    }
                }else{
                    $data = array(
                        'success'=>false,
                        'errorMsg'=>"Provider not found!",
                        'html_data'=>$provider_not_found_html
                    );
                    return json_encode($data);
                }
            }else{
                $data = array(
                    'success'=>false,
                    'errorMsg'=>"Provider not found!",
                    'html_data'=>$provider_not_found_html
                );
                return json_encode($data);
            }
        }else{
            $data = array(
                'success'=>false,
                'errorMsg'=>"Provider not found!",
                'html_data'=>$provider_not_found_html
            );
            return json_encode($data);
        }

    }

    public function getAddUpdateProviderPackage(Request $request)
    {
//        return $request;
        $userPackageList = $request->get('userPackageList');
        $current_selected_package = $request->get('current_selected_package');
        $action = $request->get('action');
        $provider_service_id = $request->get('provider_service_id');
        $service_sub_category = $request->get('service_sub_category');
        $promocode = $request->get('promocode');
        $user_id = Auth::guard("user")->user()->id;

        if($user_id > 0)
        {
            if(!empty($userPackageList))
            {
                $get_provider_service =  ProviderServices::query()->where('id',$provider_service_id)->where('status',1)->first();
                if($get_provider_service != Null)
                {
                    $service_cat_id = $get_provider_service->service_cat_id;

                    $promocode_dicount = 0;
                    if($action == "applyPromocode"){
                        $total_package = 0;
                        if($promocode != ""){
                            //check all promocode

                            list($get_fare_html,$message,$message_code)= $this->provider_fare_calculator_html($userPackageList,$promocode,$user_id,$service_cat_id);
                            if($message !="" && $message_code > 0)
                            {
                                $data = array(
                                    'success'=>false,
                                    'total_package'=>$total_package,
                                    'get_fare_html'=>$get_fare_html,
                                    'errorMsg'=>$message,
                                );
                                return json_encode($data);
                            }else{
                                $data = array(
                                    'success'=>true,
                                    'total_package'=>$total_package,
                                    'get_fare_html'=>$get_fare_html,
                                );
                                return json_encode($data);
                            }
                        }else{
                            /*$data = array(
                                'success'=>false,
                                'errorMsg'=>"Invalid Promocode",
                            );
                            return json_encode($data);*/
                        }

                    }elseif($action == "remove")
                    {
                        $total_package = $userPackageList[$service_sub_category][$current_selected_package]-1;
                        $userPackageList[$service_sub_category][$current_selected_package] = intval($total_package);
                    }else{
                        $total_package = $userPackageList[$service_sub_category][$current_selected_package]+1;
                        $userPackageList[$service_sub_category][$current_selected_package] = intval($total_package);
                    }


                    //check pakcge total qty si grater than or not
                    $get_current_package_details = OtherServiceProviderPackages::query()->where('id',$current_selected_package)->first();
                    if($get_current_package_details == Null){
                        $data = array(
                            'success'=>false,
                            'errorMsg'=>"Please Provider package not availabel!",
                        );
                        return json_encode($data);
                    }else{
                        if($total_package >$get_current_package_details->max_book_quantity )
                        {
                            $data = array(
                                'success'=>false,
                                'errorMsg'=>"Sorry you have not purchase more than max booking qunatity!",
                            );
                            return json_encode($data);
                        }
                    }


//                    $promocode = "";
                    list($get_fare_html,$message,$message_code) = $this->provider_fare_calculator_html($userPackageList,$promocode,$user_id,$service_cat_id);

                    $data = array(
                        'success'=>true,
                        'total_package'=>$total_package,
                        'get_fare_html'=>$get_fare_html

                    );
                    return json_encode($data);
                }else{
                    $data = array(
                        'success'=>false,
                        'errorMsg'=>"Please select Provider package!",
                    );
                    return json_encode($data);
                }

            }else{
                $data = array(
                    'success'=>false,
                    'errorMsg'=>"Please select Provider package!",
                );
                return json_encode($data);
            }
        }else{
            $data = array(
                'success'=>false,
                'errorMsg'=>"Please login to use provider service!",
            );
            return json_encode($data);
        }


    }

    public function provider_fare_calculator_html($userPackageList=[],$promocode="",$user_id=0,$service_cat_id=0){
        $fareHtml = "";
        $item_total = 0;
        $message = "";
        $message_code = 0;
        if(!empty($userPackageList))
        {
            $all_pkg_with_qty = array_replace_recursive($userPackageList);
            $final_package_with_qty = array_map('array_filter', $userPackageList);
            $final_package_with_qty = array_filter($final_package_with_qty);
            $fareHtml.='<div class="col-12">
                            <div class="padding-10">
                            </div>
                        <div class="faremainSetion ">';

            foreach ($final_package_with_qty as $sub_cat_id=>$single_cat_pkg)
            {
                $get_sub_cat_data = OtherServiceCategory::query()->where('id',$sub_cat_id)->first();
                $fareHtml .='<div class="col-12 fareLabel pt-2">
                                <label class="labelFld font-weight-bold">'.$get_sub_cat_data->name.'</label>
                            </div>';
                        foreach($single_cat_pkg as $pkg_id=>$single_package){

                            $get_package_data = OtherServiceProviderPackages::query()->where('id',$pkg_id)->first();
                            $service_cat_id = $get_package_data->service_cat_id;

                            $fareHtml .='<div class="col-12 fareLabel pt-1">
                                            <label class="labelFld">'.$get_package_data->name.' <span class="pl-3 font-weight-bold"> x '.$single_package.'</span></label>
                                            <label class="labelvalue currency">'.round($get_package_data->price,2).'</label>
                                        </div>';
                            $item_total = $item_total + ($get_package_data->price * $single_package) ;

                        }
                $fareHtml .='<div class="col-12 fareLabel borderBtmCls pt-2"></div>';
            }

            $get_tax_data = ServiceSettings::query()->where('service_cat_id', $service_cat_id)->first();
            if ($get_tax_data != Null) {
                $get_tax = $get_tax_data->tax;
                $admin_commission = $get_tax_data->admin_commission;
            } else {
                $get_tax = 0;
                $admin_commission = 0;
            }

            $promocode_dicount = 0;

            if($promocode != "")
            {
                //code for check promocode if apply

                $check_promocode = $this->userClassapi->checkNewPromoCodeValid($promocode, $item_total, $user_id, $service_cat_id);

                if ($check_promocode == "zero") {

                        $message= "something went to wrong!";
                        $message_code =  9;

                } elseif ($check_promocode == "one") {
                    $message= "Promocode not allowed in this order!";
                    $message_code =  93;

                }elseif ($check_promocode == "two") {
                    $message= "Promocode usage limit exceeded!";
                    $message_code =  110;

                } elseif ($check_promocode == "three") {
                    $message= "Promocode can not be anymore apply this code it was reached the limit of use!";
                    $message_code =  109;

                }elseif ($check_promocode == "four") {
                    $message= "Promocode is not exists!";
                    $message_code =  111;

                }elseif ($check_promocode == "five") {
                    $message= "The promocode doesnot match with product";
                    $message_code =  112;

                }elseif ($check_promocode == "six") {
                    $message= "The Code is expired";
                    $message_code =  113;

                }elseif ($check_promocode == "seven") {
                    $message= "The promotional code only applies if the order amount is greater than amount ";
                    $message_code =  114;

                }elseif ($check_promocode == "eight") {
                    $message= "It can not be anymore apply this code it was reached the limit of use";
                    $message_code =  115;
                }
                else {
                    $promocode_dicount = $check_promocode;
                }
            }



            $total_item_cost = round($item_total,2);
            $get_taxable_item_cost = round($total_item_cost - $promocode_dicount, 1);

            $tax = round((($get_taxable_item_cost * $get_tax) / 100), 1);
            $total_pay = round(($total_item_cost + $tax) - $promocode_dicount, 1);

            if($promocode_dicount > 0){
                $fareHtml .='<div class="col-12 fareLabel  pt-2">
                            <label class="labelFld">Promo Discount ('.$promocode.') </label>
                            <label class="labelvalue currency">'.$promocode_dicount.'</label>
                        </div>';
            }

            $fareHtml .='<div class="col-12 fareLabel  pt-2">
                            <label class="labelFld">Sub Total</label>
                            <label class="labelvalue currency">'.$get_taxable_item_cost.'</label>
                        </div>
                        <div class="col-12 fareLabel borderBtmCls pt-2">
                            <label class="labelFld">Tax</label>
                            <label class="labelvalue currency">'.$tax.'</label>
                        </div>
                        <div class="col-12 fareLabel  font-weight-bold pt-2">
                            <label class="labelFld">Total</label>
                            <label class="labelvalue currency">'.$total_pay.'</label>
                        </div>
                    </div>
            </div>';

            return array($fareHtml,$message,$message_code);
        }else{
            return array($fareHtml,$message,$message_code);
        }

    }

    public function postOtherServicePlaceOrder(Request $request){

        $array = $request->get('userPackageList');
        //code return only that data who have atleast one pacckage selected by user
        $all_pkg_with_qty = array_replace_recursive($array);
        $final_package_with_qty = array_map('array_filter', $array);
        $final_package_with_qty = array_filter($final_package_with_qty);
        $package_id_list = [];
        $package_quantity_list = [];

        foreach ($final_package_with_qty as $key=>$value) {
            $package_quantity_list = array_merge($package_quantity_list, $value);
            $package_id_list = array_merge($package_id_list, array_keys($value));
        }

        $user_id = Auth::guard("user")->user()->id;
        $provider_service_id = $request->get('provider_service_id');
        $source_lat = $request->get('source_lat');
        $source_long = $request->get('source_long');
        $delivery_address = $request->get('source_address');
        $payment_type = $request->get('payment_type');
        $promocode = $request->get('promocode');
        //0 mean now and 1: mean schedule
        $book_type = $request->get('book_type');
        $schedule_date_time = $request->get('pickup_date_time');


        $user_details = User::query()->where('id',$user_id)->first();
        if($user_details == Null){
            return response()->json([
                "status" => 0,
                "message" => "User not found!",
            ]);
        }
        $user_lang = $user_details->language;
        if ($source_lat == Null || $source_long == Null || $delivery_address == Null) {
            //if ($address['address'] == Null || $address['lat'] == Null || $address['long'] == Null || $address['flat_no'] == Null || $address['landmark'] == Null) {
            return response()->json([
                "status" => 0,
                "message" => "user address not found!",
                "message_code" => 9,
            ]);
        }
        $package_id_list = $package_id_list;

        $package_quantity_list = $package_quantity_list;
        if (count($package_quantity_list) != count($package_id_list)) {
            return response()->json([
                "status" => 0,
                "message" => "something went to wrong!",
                "message_code" => 9,
            ]);
        }
        $provider_service_details = ProviderServices::query()->where('id', $provider_service_id)->where('status',1)->first();
        if ($provider_service_details == Null) {
            return response()->json([
                "status" => 0,
                "message" => "provider not found!",
                "message_code" => 9,
            ]);
        }
        $address_lat = trim($source_lat);
        $address_long = trim($source_long);
        $provider_id = $provider_service_details->provider_id;
        $service_category_id = $provider_service_details->service_cat_id;

        $provider = Provider::query()->where('id',$provider_id)->first();
        if ($provider == Null) {
            return response()->json([
                "status" => 0,
                "message" => "provider not found!",
                "message_code" => 9,
            ]);
        }

        $check_near_delivery_address = OtherServiceProviderDetails::query()->select('other_service_provider_details.id', 'providers.service_radius','other_service_provider_details.min_order'
            , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - other_service_provider_details.lat) *  pi()/180 / 2), 2)
                +COS( ' . $address_lat . ' * pi()/180)
                * COS(other_service_provider_details.lat * pi()/180)
                * POWER(SIN(( ' . $address_long . ' - other_service_provider_details.long) * pi()/180 / 2), 2) ))) as distance')
        )
            //->join('provider_services', 'provider_services.id', '=', 'other_service_provider_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'other_service_provider_details.provider_id')
            //->where('provider_services.service_cat_id', $request->get('service_category_id'))
            ->where('providers.id', $provider_id)
//            ->havingRaw('providers.service_radius > distance')
            ->whereNull('providers.deleted_at')
            ->first();
        if ($check_near_delivery_address == Null) {
            return response()->json([
                "status" => 0,
                "message" => "The provider not offer his/her service in your delivery location! Please select your nearest provider.",
                "message_code" => 116,
            ]);
        }


        $package_book = new UserPackageBooking();
        $package_book->user_id = $user_id;
        $package_book->provider_id = $provider_id;
        $package_book->provider_name = $provider->first_name.' '.$provider->last_name ;
        $package_book->user_name = $user_details->first_name ;

        //$package_book->address_id = $request->get('address_id');
        $package_book->service_cat_id = $service_category_id;
        $package_book->payment_type = $payment_type;
        //$package_book->delivery_address = $request->get('delivery_address');
        //$package_book->lat_long = $request->get('lat_long');
        //$package_book->flat_no = $request->get('flat_no');
        //$package_book->landmark = $request->get('landmark');
        $package_book->delivery_address = $delivery_address;
        $package_book->lat_long = $address_lat . ',' . $address_long;
        /*$package_book->flat_no = ($address['flat_no'] != Null) ? $address['flat_no'] : "";
        $package_book->landmark = ($address['landmark'] != Null) ? $address['landmark'] : "";*/
        $package_book->flat_no =  "";
        $package_book->landmark =  "";
//        $package_book->remark = $request->get('remark') != Null ? $request->get('remark') : Null;
        $package_book->remark ="";
        $package_book->order_from =1;

        if ($schedule_date_time != Null) {
            $package_book->order_type = 1;
            $package_book->service_date_time = date('Y-m-d H:i:s', strtotime($schedule_date_time));
        } else {
            $package_book->order_type = 0;
            $package_book->service_date_time = date('Y-m-d H:i:s');
        }
        if ($payment_type != Null) {
            $package_book->payment_type = $payment_type;
        }
        $package_book->save();
        $package_book->generateBookingNo();
        $booking_price = 0;
        $order_package_list = [];
        $unavailable_packages_names = "";
        foreach ($package_id_list as $key => $package_id) {
            $check_duplicate = UserPackageBookingQuantity::query()->where('order_id', $package_book->id)->where('package_id', $package_id)->first();
            if ($check_duplicate == Null) {
//                $find_package = OtherServiceProviderPackages::query()->where('id', $package_id)->where('status', 1)->first();
                $find_package = OtherServiceProviderPackages::query()->where('id', $package_id)->first();
                if ($find_package != Null && $find_package->status == 1) {
                    $sub_category = OtherServiceCategory::query()->where('id', $find_package->sub_cat_id)->where('status', 1)->first();
                    if ($sub_category != Null) {
                        $package_quantity = new UserPackageBookingQuantity();
                        $package_quantity->order_id = $package_book->id;
                        $package_quantity->package_id = $package_id;
                        $package_quantity->package_name = $find_package->name;
                        $package_quantity->sub_category_name = $sub_category->name;
                        $package_quantity->ar_sub_category_name = $sub_category->ar_name;
                        /*$package_quantity->de_sub_category_name = $sub_category->de_name;
                        $package_quantity->zh_sub_category_name = $sub_category->zh_name;*/
                        /*$package_quantity->fr_sub_category_name = $sub_category->fr_name;*/

                        $package_quantity->num_of_items = $package_quantity_list[$key];
                        $package_quantity->price_for_one = $find_package->price;
                        $package_quantity->save();
                        array_push($order_package_list, $package_quantity->package_name . ' x ' . $package_quantity->num_of_items);
                        $booking_price = ($booking_price) + (($package_quantity->price_for_one) * ($package_quantity->num_of_items));
                    } else {
                        UserPackageBooking::query()->where('id', $package_book->id)->delete();
                        return response()->json([
                            "status" => 0,
                            "message" => "something went to wrong!",
                            "message_code" => 9,
                        ]);
                    }
                } else {
                    $unavailable_packages_names .= "<b> ".$find_package->name ."  </b>,";
                }
            }
        }

        if($unavailable_packages_names != Null) {
            $unavailable_packages_names = trim($unavailable_packages_names,' ,');
            $unavailable_packages_names .= " Package is not abvailable at this moment!";
            UserPackageBooking::query()->where('id', $package_book->id)->delete();
            return response()->json([
                "status" => 0,
                "message" => $unavailable_packages_names,
                "message_code" => 9,
            ]);
        }
        if ($check_near_delivery_address->min_order > $booking_price) {
            UserPackageBooking::query()->where('id', $package_book->id)->delete();
            return response()->json([
                "status" => 0,
                "message" => "Order min amount " . round(($check_near_delivery_address->min_order), 2),
                "message_code" => 9,
            ]);
        }

        $package_book->order_package_list = implode(", ", $order_package_list);
        $package_book->save();
        $find_tax = ServiceSettings::query()->where('service_cat_id', $package_book->service_cat_id)->first();
        if ($find_tax != Null) {
            $get_tax = $find_tax->tax;
            $admin_commission = $find_tax->admin_commission;
        } else {
            $get_tax = 5;
            $admin_commission = 5;
        }

        $promo_code_discount = 0;
        if ($promocode != 0) {
            $discount_on_amount = $booking_price;
            $promo_code = $this->userClassapi->checkPromoCodeValid($promocode, $discount_on_amount,$user_id, $service_category_id);
            if ($promo_code != 0) {
                if ($promo_code != 1) {
                    $get_prmocode = PromocodeDetails::query()->where('id', $promocode)->where('status', 1)->first();
                    if ($get_prmocode != Null) {
                        $promo_code_discount = $promo_code;
                        $add_promocode_details = new UsedPromocodeDetails();
                        $add_promocode_details->service_cat_id = $service_category_id;
                        $add_promocode_details->user_id = $user_id;
                        $add_promocode_details->promocode_id = $get_prmocode->id;
                        $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                        $add_promocode_details->discount_amount = $promo_code;
                        $add_promocode_details->save();

                        $package_book->promo_code = $add_promocode_details->id;
                        //$package_book->total_pay = round($package_book->total_pay - $promo_code, 2);
                        $package_book->save();
                    }
                }
            }
        }

        $package_book->BookingCost($booking_price, $get_tax, $admin_commission, $promo_code_discount);


        if ($user_details->pending_refer_discount > 0) {
            $user = User::query()->where('id', $user_details->id)->first();
            if ($user != Null) {
                $user_refer_history = UserReferHistory::query()->where('user_id', $user_details->id)->where('user_status', 0)->first();
                $total_price = $package_book->total_pay;
                if ($user_refer_history != Null) {
                    if ($user_refer_history->user_discount_type == 1) {
                        $refer_discount_price = round($total_price - $user_refer_history->user_discount);
                    } else {
                        $refer_discount_price = round((($total_price * $user_refer_history->user_discount) / 100), 1);
                        $refer_discount_price = round($total_price - $refer_discount_price);
                    }
                    if ($refer_discount_price < 0) {
                        $refer_discount_price = 0;
                    }
                    $package_book->total_pay = $refer_discount_price;
                    $package_book->refer_discount = round($user_refer_history->user_discount, 1);
                    $package_book->save();
                    $user_refer_history->user_status = 1;
                    $user_refer_history->save();
                    $user->pending_refer_discount = $user->pending_refer_discount - 1;
                    $user->save();
                } else {
                    $user_refer_history = UserReferHistory::query()->where('refer_id', $user_details->id)->where('refer_status', 0)->first();
                    if ($user_refer_history != Null) {
                        if ($user_refer_history->refer_discount_type == 1) {
                            $refer_discount_price = round($total_price - $user_refer_history->refer_discount);
                        } else {
                            $refer_discount_price = round((($total_price * $user_refer_history->refer_discount) / 100), 1);
                            $refer_discount_price = round($total_price - $refer_discount_price);
                        }
                        if ($refer_discount_price < 0) {
                            $refer_discount_price = 0;
                        }
                        $package_book->total_pay = $refer_discount_price;
                        $package_book->refer_discount = round($user_refer_history->user_discount, 1);
                        $package_book->save();
                        $user_refer_history->refer_status = 1;
                        $user_refer_history->save();
                        $user->pending_refer_discount = $user->pending_refer_discount - 1;
                        $user->save();
                    }
                }
            }
        }

        //add order_status parameter in response
        Session::flash('success', 'Provider successfully booked');
        return $this->userClassapi->OtherServiceOrderDetails($package_book->id, true,Null,$user_lang);

    }

    //funcion for distapl order details page
    public function getProviderOrderDetails(Request $request,$order_id){

        $user_id =  Auth::guard("user")->user()->id;

        $order_details = UserPackageBooking::query()->select('user_service_package_booking.id',
            'user_service_package_booking.order_no',
            'user_service_package_booking.user_name', 'user_service_package_booking.provider_name',
            'user_service_package_booking.service_date_time',
            'service_category.slug as slug',
            'user_service_package_booking.total_item_cost',
            'user_service_package_booking.delivery_address',
            'user_service_package_booking.tax',
            'user_service_package_booking.created_at',
            'user_service_package_booking.order_type',
            'user_service_package_booking.payment_status',
            'used_promocode_details.discount_amount',
            'user_service_package_booking.remark',
            'user_service_package_booking.tip',
            'user_service_package_booking.payment_type',
            'user_service_package_booking.total_pay', 'user_service_package_booking.status'
            , 'users.contact_number'
            , 'user_service_package_booking.cancel_by'
            , 'user_service_package_booking.cancel_reason'
            , 'user_service_package_booking.refund_amount', 'user_service_package_booking.cancel_charge',
            'user_service_package_booking.user_refund_status',
            'user_service_package_booking.status as status'
        )
            ->join('users', 'users.id', '=', 'user_service_package_booking.user_id')
            ->join('service_category', 'service_category.id', '=', 'user_service_package_booking.service_cat_id')
            ->leftJoin('used_promocode_details', 'used_promocode_details.id', 'user_service_package_booking.promo_code')
            ->where('user_service_package_booking.id', $order_id)
            ->where('user_service_package_booking.user_id', $user_id)
            ->whereNull('users.deleted_at')
            ->first();


        if ($order_details != Null) {
            $package_list = UserPackageBookingQuantity::query()->select('num_of_items', 'package_name', 'sub_category_name', 'price_for_one')
                ->where('order_id', $order_details->id)
                ->get();
            $total_package_cost = 0;
            foreach ($package_list as $key => $package) {
                $total_package_item_cost = round($package->price_for_one * $package->num_of_items, 1);
                $total_package_cost = $total_package_cost + $total_package_item_cost;
            }

            $user_open_tab = "provider-detail";
            $title_code = "Provider Order Details";
            $slug = $order_details->slug;

            $view = view('front.pages.user_details_tabview', compact('slug', 'title_code','user_open_tab', 'order_details', 'package_list'));
        } else {
            $view = view('front.pages.user_details_tabview', compact('slug', 'title_code','user_open_tab'));
        }

        return $view;
    }

    public function getUpdateOtherServiceOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "id" => "required|numeric",
                "update_status" => "required|numeric|in:5,9"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first()
            ]);
        }
        $id = $request->get('id');
        $update_status = $request->get('update_status');
        if ($update_status == 5) {
            $validator = Validator::make($request->all(), [
                    "reason" => "required"
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => $validator->errors()->first()
                ]);
            }
        }
        $order_details = UserPackageBooking::query()->where('id', $id)->first();
        if ($order_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $user_details = User::query()->where('id', $order_details->user_id)->whereNull('users.deleted_at')->first();
        //$user_details = User::select('device_token', 'login_device')->where('id', $order_details->user_id)->first();
        if ($order_details->status == 4 || $order_details->status == 5) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        switch ($update_status) {
            case 5:
                if ($order_details->status != 4 && $order_details->status != 5 && $order_details->status != 9 && $order_details->status != 10) {
                    $order_details->status = $update_status;
                    $order_details->cancel_by = "user";
                    $order_details->cancel_reason = $request->get('reason');
                    $order_details->save();
                    if ($user_details != Null) {
                        $this->notificationClass->userProviderPackageNotification($order_details->id, $user_details->device_token, $order_details->status, $user_details->login_device, $user_details->language);
                    }

                    $other_provider_details = Provider::query()->where('id', $order_details->provider_id)->whereNull('providers.deleted_at')->first();
                    if ($other_provider_details != Null) {
                        $this->notificationClass->providerOrderCancelRequestNotification($order_details->id, $order_details->status, $other_provider_details->device_token, $other_provider_details->login_device, $other_provider_details->language);
                    }

                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ]);
                }
                break;
//            case 9:
//                if ($order_details->status > 1 && $order_details->status < 9) {
//                    if ($order_details->status != 4 && $order_details->status != 5 && $order_details->status != 9 && $order_details->status != 10) {
//                        $order_details->status = $update_status;
//                        $order_details->payment_status = 1;
////                        $order_details->provider_rating_status = 1;
//                        $order_details->save();
//                        $other_provider_details = OtherServiceProviderDetails::where('provider_id', $order_details->provider_id)->first();
//                        if ($other_provider_details != Null) {
//                            $other_provider_details->total_completed_order = $other_provider_details->total_completed_order + 1;
//                            $other_provider_details->save();
//                        }
//                        $service_category = ServiceCategory::where('id', $order_details->service_cat_id)->first();
//                        if ($user_details != Null) {
//                            $this->notificationClass->userProviderPackageNotification($order_details->id, $user_details->device_token, $order_details->status, $user_details->login_device);
//                        }
//                    } else {
//                        return response()->json([
//                            'success' => false,
//                            'message' => 'Data not found'
//                        ]);
//                    }
//                } else {
//                    return response()->json([
//                        'success' => false,
//                        'message' => 'Data not found'
//                    ]);
//                }
//                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ]);
        }
        return response()->json([
            'success' => true
        ]);
    }
}
