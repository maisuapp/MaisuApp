<?php

namespace App\Http\Controllers\User;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Classes\UserClassApi;
use App\model\CouponCategory;
use App\model\CouponCodeDetail;
use App\model\CouponDetails;
use App\model\GeneralSettings;
use App\model\ServiceCategory;
use App\model\TempUserBooking;
use App\model\User;
use App\model\UserCardDetails;
use App\model\UserCouponCart;
use App\model\UserPurchaseCouponDetails;
use App\model\UserWalletTransaction;
use App\model\WorldCurrency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CouponServiceController extends Controller
{
    //

    private $userClassapi;

    private $notificationClass;

    public function __construct(AdminClass $adminClass, NotificationClass $notificationClass,UserClassApi $userClassapi)
    {
        $this->userClassapi = $userClassapi;
        $this->notificationClass = $notificationClass;
    }
    public function getCouponList(Request $request){
        $exitCode = Artisan::call('cache:clear');
        /*if(!Auth::guard("user")->check()){
            Session::flash('error', 'Please Login before use provider service..!');
            return redirect()->route('get:homepage');
        }*/

        $coupon_category_lists = CouponCategory::query()->where('status',1)->get();
        $first_coupon_category_list = CouponCategory::query()->where('status',1)->first();

        $type="coupon_lists";

        $cat_wise_coupon_lists_html = $this->categoryWiseCouponListView($first_coupon_category_list);
        $coupon_cart = $this->getCouponCartDetails();
        $user_id = isset(Auth::guard("user")->user()->id)?Auth::guard("user")->user()->id:0;
        $card_list = UserCardDetails::query()->select('card_details.id', 'card_details.holder_name as card_holder_name', 'card_details.card_number as card_number')
            ->where('user_id', '=',$user_id)
            ->orderBy('id','desc')
            ->get();

        return view('front.pages.coupon_deals.coupon_lists',compact('type','card_list','coupon_category_lists','first_coupon_category_list','cat_wise_coupon_lists_html','coupon_cart'));
    }

    public function getCouponDetails(Request $request ,$coupon_id=0){

        $get_coupon_details = CouponDetails::query()->where('id',$coupon_id)->first();
        if($get_coupon_details == Null){
            return redirect()->route('get:coupon-list')->with('error','Please select proper coupon for view details');
        }

        $coupon_category_lists = CouponCategory::query()->where('status',1)->get();
        $first_coupon_category_list = CouponCategory::query()->where('id',$get_coupon_details->category_id)->where('status',1)->first();

        $coupon_detail_html = $this->CouponDetailsView($coupon_id);
        $coupon_cart = $this->getCouponCartDetails();
        $user_id = isset(Auth::guard("user")->user()->id)?Auth::guard("user")->user()->id:0;
        $card_list = UserCardDetails::query()->select('card_details.id', 'card_details.holder_name as card_holder_name', 'card_details.card_number as card_number')
            ->where('user_id', '=',$user_id)
            ->orderBy('id','desc')
            ->get();

        $type="coupon_details";

        return view('front.pages.coupon_deals.coupon_lists',compact('type','card_list','coupon_category_lists','first_coupon_category_list','coupon_detail_html','coupon_cart'));
    }

    //function for make list of coupon category wise
    public function categoryWiseCouponListView($first_coupon_category_list=[]){

            $coupon_list = "";
//            $first_cat_all_coupon_lists = CouponDetails::query()->where('category_id',$first_coupon_category_list->id)->where('status',1)->get();
            $user_id = isset(Auth::guard("user")->user()->id)?$user_id = Auth::guard("user")->user()->id:0;
            $first_cat_all_coupon_lists = CouponDetails::query()->select('coupon_details.*','user_coupon_cart.coupon_qty')

                                            ->leftJoin('user_coupon_cart', function ($join) use ($user_id) {
                                                $join->on('user_coupon_cart.coupon_id', '=', 'coupon_details.id')
                                                    ->where('user_coupon_cart.user_id', '=', $user_id);
                                            })
                                            ->where('coupon_details.category_id',$first_coupon_category_list->id)
                                            ->where('coupon_details.available_coupon','>',0)
                                            ->where('coupon_details.status',1)
                                            ->orderBy('coupon_details.id','desc')
                                            ->get();
            if($first_cat_all_coupon_lists != Null)
            {

                $get_user_coupon_cart =UserCouponCart::query()->where('user_id',$user_id)->where('category_id',$first_coupon_category_list->id)->get();

                $coupon_list = view('front.pages.coupon_deals.cat_wise_coupon_list',compact('first_coupon_category_list','first_cat_all_coupon_lists','get_user_coupon_cart'));
            }
            return $coupon_list;

    }

    //function for via ajax make list of coupon category wise
    public function getSingelCatCoupons(Request $request){

        $cat_id = $request->get('cat_id');
        $first_coupon_category_list = CouponCategory::query()->where('id',$cat_id)->where('status',1)->first();
        $cat_wise_coupon_lists_html = $this->categoryWiseCouponListView($first_coupon_category_list);

        return response()->json([
            'success' => true,
            "htmlData"=> "".$cat_wise_coupon_lists_html.""
        ]);
    }
    //function for make view of details page
    public function CouponDetailsView($coupon_id){

        $user_id = isset(Auth::guard("user")->user()->id)?Auth::guard("user")->user()->id:0;
        $coupon_details = "";
        $first_cat_all_coupon_lists = CouponDetails::query()->select('coupon_details.*','coupon_category.category_name','user_coupon_cart.coupon_qty')
                                        ->leftJoin('coupon_category','coupon_category.id','coupon_details.category_id')
                                        ->leftJoin('user_coupon_cart', function ($join) use ($user_id) {
                                            $join->on('user_coupon_cart.coupon_id', '=', 'coupon_details.id')
                                                ->where('user_coupon_cart.user_id', '=', $user_id);
                                        })
                                        ->where('coupon_details.id',$coupon_id)
                                        ->where('coupon_details.status',1)->first();

        if($first_cat_all_coupon_lists != Null)
        {
            $coupon_details = view('front.pages.coupon_deals.coupon_detail',compact('first_cat_all_coupon_lists'));
        }
        return $coupon_details;

    }

    //code for add coupon in user card table
    public function getAddCouponCart(Request $request){

        $user_id = Auth::guard("user")->user()->id;
        $coupon_id = $request->get('coupon_id');
        $action = $request->get('action');
        if($user_id  > 0){

            $check_coupon_exist  = CouponDetails::query()->where('id',$coupon_id)->where('status',1)->first();

            if($check_coupon_exist != Null)
            {
                    //cart table with user purchase coupon
                    $total_avail_coupon = $check_coupon_exist->available_coupon;
                    $coupon_category_id = $check_coupon_exist->category_id;
                    if($total_avail_coupon == 0)
                    {
                        return response()->json([
                            'success' => false,
                            "htmlData"=> "",
                            "errmessage" => "Sorry, Coupon Code is currently not available."

                        ]);
                    }

                    $get_card_data = UserCouponCart::query()->where('user_id',$user_id)->where('coupon_id',$coupon_id)->first();
                    $user_coupon_cart_qty = 0;
                    if($get_card_data != Null){
                        $user_coupon_cart_qty = $get_card_data->coupon_qty;
                    }else{
                        $get_card_data = New UserCouponCart();
                        $get_card_data->user_id = $user_id;
                        $get_card_data->category_id = $coupon_category_id;
                        $get_card_data->coupon_id = $coupon_id;
                    }

                    if($action == "delete"){
                        $user_coupon_cart_qty = 0;
                    }elseif($action == "minus"){
                        $user_coupon_cart_qty = $user_coupon_cart_qty - 1;
                        $user_coupon_cart_qty = ($user_coupon_cart_qty > 0)?$user_coupon_cart_qty:0;

                    }else{
                        $user_coupon_cart_qty = $user_coupon_cart_qty + 1;
                        if($user_coupon_cart_qty  > $total_avail_coupon){
                            return response()->json([
                                'success' => false,
                                "htmlData"=> "",
                                "errmessage" => "Sorry, you have reached to max coupon limit."
                            ]);
                        }
                    }

                    //code save data in database coupon cart table
                    $get_card_data->coupon_qty = $user_coupon_cart_qty;
                    $get_card_data->save();
                    if($user_coupon_cart_qty == 0){
                        $get_card_data->delete();
                    }


                    return response()->json([
                        'success' => true,
                        "htmlData"=> "",
                        "cartHtmlData"=> "".$this->getCouponCartDetails()."",
                        "user_coupon_cart_qty" => $user_coupon_cart_qty,
                        "errmessage" => ""

                    ]);
            }else{
                return response()->json([
                    'success' => false,
                    "htmlData"=> "",
                    "errmessage" => "Sorry, Coupon Code is  currently not available."

                ]);
            }

        }else{
            return response()->json([
                'success' => false,
                "htmlData"=> "",
                "errmessage" => "You can not use this service without login"

            ]);
        }
    }

    public function getCouponCartDetails(){
        //code for generate user coupon cart

        $user_id = isset(Auth::guard("user")->user()->id)?$user_id = Auth::guard("user")->user()->id:0;
        if($user_id > 0){
            $get_user_coupon_cart = UserCouponCart::query()
                                    ->select('user_coupon_cart.id','user_coupon_cart.user_id','user_coupon_cart.coupon_id','user_coupon_cart.category_id',
                                        'user_coupon_cart.coupon_qty','coupon_details.coupon_name','coupon_details.coupon_amount','coupon_details.coupon_brand_name'
                                    )
                                    ->join('coupon_details','coupon_details.id','user_coupon_cart.coupon_id')
                                    ->where('user_coupon_cart.user_id',$user_id)
                                    ->get();

            if(count($get_user_coupon_cart) > 0)
            {
                return $coupon_cart = view('front.pages.coupon_deals.coupon_cart',compact('get_user_coupon_cart'));
            }else{
                return  "";
            }
        }else{
            return  "";
        }
    }

    public function getPlaceOrderCoupon(Request $request){

        $user_id = isset(Auth::guard("user")->user()->id)? Auth::guard("user")->user()->id:0;

        $validator = Validator::make($request->all(), [
            'payment_type' => 'Required|in:2,3',
            'card_id' => 'required_if:payment_type,==,2',
        ]);


        if ($validator->fails()) {

            $failedRules = $validator->failed();
            if (isset($failedRules['payment_type']['Required'])) {
//                Session::flash('error', __('messages.155'));
                Session::flash('error', 'Please add payment type before place order');
            }
            if (isset($failedRules['card_id']['RequiredIf'])) {
//                Session::flash('error', __('messages.156'));
                Session::flash('error', 'Please select proper card for payment');
            }
            return redirect()->back();
        }

        if($user_id > 0){
            $user_details = User::query()->where('id',$user_id)->where('status',1)->first();
            if($user_details != Null)
            {
                $payment_type = $request->get('payment_type');
                if($payment_type == 3 || $payment_type == "3" || $payment_type == 2 || $payment_type == "2" || $payment_type == 1 || $payment_type == "1"  )
                {
                    //code for payment type wallet
                    $get_user_wallet_details  = UserWalletTransaction::query()
                        ->where('user_id',$user_id)
                        ->orderBy('id','desc')
                        ->first();

//                    if($get_user_wallet_details != Null)
//                    {


                        $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
                        if ($user_currency == Null) {
                            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
                        }
                        $currency = $user_currency != Null ? $user_currency->ratio : 1;
                        $currency_code = $user_currency != Null ? $user_currency->currency_code : '';

                        //get cart data
                        $get_user_coupon_cart_data =  UserCouponCart::query()->where('user_id',$user_id)->get();
                        $errFlag = 0;
                        $allError = "";
                        $all_coupon_total = 0;
                        if(count($get_user_coupon_cart_data) > 0){

                            foreach($get_user_coupon_cart_data as $singel_coupon_cart_data){

                                    $coupon_id = $singel_coupon_cart_data->coupon_id;
                                    $coupon_qty = $singel_coupon_cart_data->coupon_qty;
                                    $data = $this->getSingelCouponAvailablity($coupon_id,$coupon_qty);
                                    /*\Log::info('data');
                                    \Log::info($data);*/
                                    $success = $data['success'];
                                    $errorMsg = $data['errorMsg'];
                                    $singel_coupon_total = $data['singel_coupon_total'];

                                    if($success == true || $success == "true")
                                    {
                                        $all_coupon_total = $all_coupon_total + $singel_coupon_total;

                                    }else{
                                        $errFlag = 1;
                                        $allError .=$errorMsg;
                                    }
                            }

                            if($errFlag == 0){


                                    //add code for coupon purchasse to user account
                                    if($payment_type == 3 || $payment_type == "3")
                                    {
                                        $get_user_wallet_details  = UserWalletTransaction::query()
                                            ->where('user_id',$user_id)
                                            ->orderBy('id','desc')
                                            ->first();
                                        if($get_user_wallet_details != Null)
                                        {
                                            $avail_wallet_balance = $get_user_wallet_details->remaining_balance;
                                            $all_coupon_total = round($all_coupon_total,2);
                                            //check for wallet payment
                                            if($avail_wallet_balance >= $all_coupon_total  ){

                                                $order_gen_id = $this->userClassapi->generateBookingNo();
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
                                                            $get_coupon_code->user_id = $user_id;
                                                            $get_coupon_code->code_active_date = date('Y-m-d H:i:s');
                                                            $get_coupon_code->code_expire_date = date('Y-m-d H:i:s', strtotime("+" . $coupon_validity . " day"));
                                                            $get_coupon_code->save();

                                                        }
                                                        //update table entry to remain and available on purchse coun code entry
                                                        $user_purchase_coupon = new UserPurchaseCouponDetails();
                                                        $user_purchase_coupon->user_id = $user_id;
                                                        $user_purchase_coupon->coupon_id = $coupon_details->id;
                                                        $user_purchase_coupon->coupon_name = $coupon_details->coupon_name;
                                                        $user_purchase_coupon->coupon_brand_name = $coupon_details->coupon_brand_name;
                                                        $user_purchase_coupon->coupon_amount = $coupon_details->coupon_amount;
                                                        $user_purchase_coupon->trans_id =$order_gen_id;
                                                        //                                            $user_purchase_coupon->coupon_purchase_qtys = $purchse_coupon_code_qty;
                                                        //                                        $user_purchase_coupon->coupon_code = $coupon_details->coupon_code;
                                                        $user_purchase_coupon->coupon_code = "";
                                                        $user_purchase_coupon->payment_status = 1;
                                                        $user_purchase_coupon->payment_type = 3;
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
                                                $add_balance = new UserWalletTransaction();
                                                $add_balance->user_id = $user_id;
                                                $add_balance->transaction_type = 2;
                                                $add_balance->amount = $all_coupon_total;
                                                $add_balance->subject = "Paid For - Purchase Coupon " ;
                                                $add_balance->remaining_balance = round($avail_wallet_balance - $all_coupon_total, 2);
                                                $add_balance->subject_code = 9;
                                                $add_balance->order_no = '';
                                                $add_balance->save();
                                                return redirect()->route('get:my_coupons')->with('success','coupon successfully purchased');
                                            }else{
                                                return redirect()->route('get:coupon-list')->with('error','Sorry, You have not enough balance in wallet');
                                            }
                                        }else{
                                                return redirect()->route('get:coupon-list')->with('error','Sorry, You have not enough balance in wallet');
                                        }

                                    }else if($payment_type == 2 || $payment_type == "2" ){

                                        $card_id = $request->get('card_id') - 0;
                                        $check_user_card = UserCardDetails::query()->where('id','=',$card_id)->where('user_id','=',$user_id)->first();
                                        if($check_user_card != Null){
                                            $order_gen_id = $this->userClassapi->generateBookingNo();
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
                                                        if($get_coupon_code != Null)
                                                        {
                                                            $get_coupon_code->user_id = $user_id;
                                                            $get_coupon_code->code_active_date = date('Y-m-d H:i:s');
                                                            $get_coupon_code->code_expire_date = date('Y-m-d H:i:s', strtotime("+" . $coupon_validity . " day"));
                                                            $get_coupon_code->save();
                                                        }else{
                                                            return redirect()->route('get:coupon-list')->with('error','something went to wrong..');
                                                        }


                                                    }
                                                    //update table entry to remain and available on purchse coun code entry
                                                    $user_purchase_coupon = new UserPurchaseCouponDetails();
                                                    $user_purchase_coupon->user_id = $user_id;
                                                    $user_purchase_coupon->coupon_id = $coupon_details->id;
                                                    $user_purchase_coupon->coupon_name = $coupon_details->coupon_name;
                                                    $user_purchase_coupon->coupon_brand_name = $coupon_details->coupon_brand_name;
                                                    $user_purchase_coupon->coupon_amount = $coupon_details->coupon_amount;
                                                    $user_purchase_coupon->trans_id =$order_gen_id;
                                                    //                                            $user_purchase_coupon->coupon_purchase_qtys = $purchse_coupon_code_qty;
                                                    //                                        $user_purchase_coupon->coupon_code = $coupon_details->coupon_code;
                                                    $user_purchase_coupon->coupon_code = "";
                                                    $user_purchase_coupon->payment_status = 1;
                                                    $user_purchase_coupon->payment_type = 2;
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
                                            return redirect()->route('get:my_coupons')->with('success','coupon successfully purchased');
                                        }else{
                                            return redirect()->route('get:coupon-list')->with('error','Please select proper card for payment.');
                                        }
                                    }else{
                                        return redirect()->route('get:coupon-list')->with('error','Please select proper payment method.');
                                    }
                            }else{
                                return redirect()->route('get:coupon-list')->with('error',$allError);
                            }
                        }else{
                            return redirect()->route('get:coupon-list')->with('error','Sorry, your coupon cart has been empty');
                        }
                    /*}else{
                        return redirect()->route('get:coupon-list')->with('error','Sorry, You have not enough balance in wallet');
                    }*/
                }
                else{
                    return redirect()->route('get:coupon-list')->with('error','Please select proper payment method.');
                }
            }else{
                return redirect()->route('get:coupon-list')->with('error','Sorry, You are not verify. please verify your account');
            }
        }else{
            Session::flash('error', 'Please Login before Purchase Coupon codes..!');
            return redirect()->route('get:coupon-list');
        }
    }

    public function getSingelCouponAvailablity($coupon_id=0,$coupon_qty=0){

            if($coupon_id > 0 && $coupon_qty > 0 ){

                $get_singel_coupon_data = CouponDetails::query()
                                            ->where('id',$coupon_id)
//                                            ->where('available_coupon','>=',$coupon_qty)
                                            ->first();

                if($get_singel_coupon_data != Null){
                    $coupon_name = $get_singel_coupon_data->coupon_name;
                    if($get_singel_coupon_data->status == 1)
                    {
                        $total_avail_coupon = $get_singel_coupon_data->available_coupon;
                        $coupon_amount = $get_singel_coupon_data->coupon_amount;

                        if($total_avail_coupon >= $coupon_qty){
                            return array(
                                'success' => true,
                                "errorMsg"=> "",
                                "singel_coupon_total" => round(($coupon_amount * $coupon_qty),2)
                            );
                        }else{
                            return array(
                                'success' => false,
                                "errorMsg"=> "<b>".$coupon_name."</b> : Currently enough coupon quantity is  not avaialable <br/>",
                                "singel_coupon_total" => 0
                            );
                        }
                    }else{
                        return array(
                            'success' => false,
                            "errorMsg"=> "<b>".$coupon_name."</b> : Currently enough coupon code is  not avaialable at this moment  <br/>",
                            "singel_coupon_total" => 0
                        );
                    }

                }else{
                    return array(
                        'success' => false,
                        "errorMsg"=> "",
                        "singel_coupon_total" => 0
                    );
                }

            }else{
                return array(
                    'success' => false,
                    "errorMsg"=> "",
                    "singel_coupon_total" => 0
                );
            }
    }
}

