<?php

namespace App\Http\Controllers;

use App\Classes\AdminClass;
use App\Http\Requests\CouponDealsCategoryRequest;
use App\Http\Requests\CouponDealsCouponDetailsRequest;
use App\model\CouponCategory;
use App\model\CouponCodeDetail;
use App\model\CouponDetails;
use App\model\ServiceCategory;
use App\model\UserPurchaseCouponDetails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CouponDealsController extends Controller
{
    private $adminClass;
    private $is_restricted = 0;

    public function __construct(AdminClass $adminClass)
    {
        $this->middleware('auth');
        $this->adminClass = $adminClass;

        $this->middleware( function ($request, $next) {
            $is_restrict_admin = $request->get('is_restrict_admin');
            $this->is_restricted = $is_restrict_admin;
            return $next($request);
        });
    }

    public function getCouponDealsServiceList(Request $request)
    {
        $coupon_deals_categories = ServiceCategory::query()->select('id', 'name', 'slug', 'icon_name', 'category_type', 'status')->where('id', 3)->get();
        if ($coupon_deals_categories->isEmpty()) {
            $view = view('admin.pages.coupon_deals.service_list');
        } else {
            $view = view('admin.pages.coupon_deals.service_list', compact('coupon_deals_categories'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsDashboard(Request $request, $slug)
    {
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        if ($service_category != Null) {

            $date = date('Y-m-d H:i:s');
            $total_coupon = CouponDetails::query()->count();
            $active_coupon = CouponDetails::query()->where('status', 1)->where('expiry_date', '>=', $date)->count();
            $purchase_coupon = UserPurchaseCouponDetails::query()->count();
            $total_category = CouponCategory::query()->count();
            $view = view('admin.pages.super_admin.service_dashboard.coupon_deals_dashboard', compact('service_category', 'slug',
                'total_coupon',
                'active_coupon',
                'purchase_coupon',
                'total_category'));
        } else {
            $view = view('admin.pages.super_admin.service_dashboard.coupon_deals_dashboard', compact('slug'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsCategoryList(Request $request, $slug)
    {
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        if ($service_category != Null) {
            $categories = CouponCategory::query()->get();
            $view = view('admin.pages.coupon_deals.category.manage', compact('service_category', 'slug',
                'categories'));
        } else {
            $view = view('admin.pages.coupon_deals.category.manage', compact('slug'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsAddCategory(Request $request, $slug)
    {
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        if ($service_category != Null) {
            $view = view('admin.pages.coupon_deals.category.form', compact('service_category', 'slug'));
        } else {
            $view = view('admin.pages.coupon_deals.category.form', compact('slug'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsEditCategory(Request $request, $slug, $id)
    {
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        $category = CouponCategory::query()->where('id', $id)->first();
        if ($service_category != Null) {
            $view = view('admin.pages.coupon_deals.category.form', compact('service_category', 'slug', 'category'));
        } else {
            $view = view('admin.pages.coupon_deals.category.form', compact('slug', 'category'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsUpdateCategory(CouponDealsCategoryRequest $request, $slug)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        if ($service_category != Null) {
            $id = $request->get('id');
            if ($id > 0) {
                $coupon_category = CouponCategory::query()->where('id', $id)->first();
                Session::flash('success', 'Category Updated successfully!');
            } else {
                Session::flash('success', 'Category Added successfully!');
                $coupon_category = new CouponCategory();
                $coupon_category->status = 1;
            }
            $coupon_category->category_name = $request->get('category_name');
            $coupon_category->save();
        }
        return redirect()->route('get:admin:coupon_deals_category_list', $slug);
    }

    public function getAdminUpdateCouponDealsCategoryStatus(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $id = $request->get('id');
        if ($id == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $coupon_category = CouponCategory::query()->where('id', '=', $id)->first();
        if ($coupon_category == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($coupon_category->status == 1) {
            $coupon_category->status = 0;
        } else {
            $coupon_category->status = 1;
        }
        $coupon_category->save();
        return response()->json([
            'success' => true,
            'status' => $coupon_category->status,
        ]);
    }

    public function getCouponDealsPurchaseCouponHistory(Request $request, $slug)
    {
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();

        $coupon_history = UserPurchaseCouponDetails::query()->select('user_purchase_coupon_details.coupon_name',
            'user_purchase_coupon_details.coupon_brand_name',
            'user_purchase_coupon_details.coupon_amount',
            'user_purchase_coupon_details.coupon_purchase_qtys',
            'users.first_name',
            'users.last_name')
            ->join('users', 'users.id', 'user_purchase_coupon_details.user_id')
            ->get();
        if ($service_category != Null) {
            $view = view('admin.pages.coupon_deals.purchase_coupon_history', compact('service_category', 'slug', 'coupon_history'));
        } else {
            $view = view('admin.pages.coupon_deals.purchase_coupon_history', compact('slug', 'coupon_history'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsUserPurchaseCouponHistory(Request $request, $slug, $id)
    {
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();

        $coupon_history = UserPurchaseCouponDetails::query()->select('user_purchase_coupon_details.coupon_name',
            'user_purchase_coupon_details.coupon_brand_name',
            'user_purchase_coupon_details.coupon_amount',
            'users.first_name',
            'users.last_name')
            ->join('users', 'users.id', 'user_purchase_coupon_details.user_id')
            ->where('user_purchase_coupon_details.coupon_id', $id)
            ->get();
        if ($service_category != Null) {
            $view = view('admin.pages.coupon_deals.purchase_coupon_history', compact('service_category', 'slug', 'coupon_history'));
        } else {
            $view = view('admin.pages.coupon_deals.purchase_coupon_history', compact('slug', 'coupon_history'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsCouponCodeList(Request $request, $slug, $id)
    {
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        $coupon_id = $id;
//        $coupon_code_list_data = CouponCodeDetail::query()->select('coupon_code_details.id','coupon_code_details.coupon_code',
//            'coupon_code_details.user_id',
//            'coupon_code_details.status',
//            'coupon_code_details.code_active_date',
//            'coupon_code_details.code_expire_date',
//            'coupon_code_details.code_used_date',
//            'users.first_name',
//            'users.last_name')
//            ->leftJoin('users', 'users.id', 'coupon_code_details.user_id')
//            ->where('coupon_code_details.coupon_id', $id)
//            ->orderBy('coupon_code_details.code_active_date', 'desc')
//            ->get();
//        $coupon_code_list = [];
//        foreach ($coupon_code_list_data as $key=>$single_code){
//            $coupon_code_list[$key]['id'] = $single_code->id;
//            $coupon_code_list[$key]['coupon_code'] = $single_code->coupon_code;
//            $coupon_code_list[$key]['user_id'] = $single_code->user_id;
//            $coupon_code_list[$key]['status'] = $single_code->status;
//            $coupon_code_list[$key]['code_active_date'] = $single_code->code_active_date;
//            $coupon_code_list[$key]['code_expire_date'] = $single_code->code_expire_date;
//            if($single_code->status == "1"){
//                $coupon_code_list[$key]['coupon_status'] = "Used At ".$single_code->code_used_date;
//            }else {
//                $curr_date = date("Y-m-d H:i:s");
//                if ($single_code->code_active_date == "0000-00-00 00:00:00" || $single_code->code_active_date == Null ) {
//                    $coupon_code_list[$key]['coupon_status'] = "Not Purchase";
//                }else if (strtotime($curr_date) > strtotime($single_code->code_expire_date)) {
//                    $coupon_code_list[$key]['coupon_status'] = "Expired";
//                } else {
//                    $to_date = strtotime($curr_date);
//                    $from_date = strtotime($single_code->code_expire_date);
//                    $day_diff = $from_date -  $to_date;
//                    $total_days =  ceil($day_diff/(60*60*24));
//                    $coupon_code_list[$key]['coupon_status'] =($total_days > 1)?$total_days." Days Remain":$total_days." Day Remain";
//                }
//            }
//            $coupon_code_list[$key]['first_name'] = $single_code->first_name;
//            $coupon_code_list[$key]['last_name'] = $single_code->last_name;
//        }
//        /*dd($coupon_code_list);*/
//        if ($service_category != Null) {
//            $view = view('admin.pages.coupon_deals.coupon_code_list', compact('service_category', 'coupon_id','slug', 'coupon_code_list'));
//        } else {
//            $view = view('admin.pages.coupon_deals.coupon_code_list', compact('slug', 'coupon_id','coupon_code_list'));
//        }
//        if ($request->ajax()) {
//            $view = $view->renderSections();
//            return $this->adminClass->renderingResponce($view);
//        }
//        return $view;

        $view = view('admin.pages.coupon_deals.coupon_code_list', compact('slug', 'coupon_id'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsCouponCodeListNew(Request $request, $slug, $id)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page
        $rowperpage = (isset($rowperpage) && $rowperpage > 0)?$rowperpage:25;
        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value


        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        $coupon_id = $id;


        $couponCodeDetailsData = CouponCodeDetail::query()->select('coupon_code_details.id','coupon_code_details.coupon_code',
            'coupon_code_details.user_id',
            'coupon_code_details.status',
            'coupon_code_details.code_active_date',
            'coupon_code_details.code_expire_date',
            'coupon_code_details.code_used_date',
            'users.first_name',
            'users.last_name'
            )
            ->leftJoin('users', 'users.id', 'coupon_code_details.user_id')
            ->where('coupon_code_details.coupon_id','=', $coupon_id);

         $totalRecords = $couponCodeDetailsData->count();


        if($searchValue != ""){
            DB::enableQueryLog();
            $totalRecordswithFilter =$couponCodeDetailsData
                ->where(function ($query) use ($searchValue) {
                    $query->Where('users.first_name', 'like', '%' .$searchValue . '%')
                        ->orWhere('users.last_name', 'like', '%' .$searchValue . '%')
                        ->orWhere('coupon_code_details.coupon_code', 'like', '%' .$searchValue . '%');
                });

            $totalRecordswithFilter= $totalRecordswithFilter->count();

        }else{
            $totalRecordswithFilter = $totalRecords;
        }

        $records = $couponCodeDetailsData->orderBy($columnName,$columnSortOrder);
        if($searchValue != ""){
            $records =$records
                ->where(function ($query) use ($searchValue) {
                    $query->Where('users.first_name', 'like', '%' .$searchValue . '%')
                        ->orWhere('users.last_name', 'like', '%' .$searchValue . '%')
                        ->orWhere('coupon_code_details.coupon_code', 'like', '%' .$searchValue . '%');
                });
        }
        $records =$records
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = [];
        $cnt = 1;
        foreach ($records as $record){

            $id = $record->id;
            $username = $record->first_name . " " . $record->last_name;
            $email = $record->email;
            $coupon_code = $record->coupon_code;
            $code_active_date = $record->code_active_date;
            $code_expire_date = $record->code_expire_date;
            if($record->status == "1"){
                $coupon_status = "Used At ".$record->code_used_date;
            }else {
                $curr_date = date("Y-m-d H:i:s");
                if ($record->code_active_date == "0000-00-00 00:00:00" || $record->code_active_date == Null ) {
                    $coupon_status = "Not Purchase";
                }else if (strtotime($curr_date) > strtotime($record->code_expire_date)) {
                    $coupon_status = "Expired";
                } else {
                    $to_date = strtotime($curr_date);
                    $from_date = strtotime($record->code_expire_date);
                    $day_diff = $from_date -  $to_date;
                    $total_days =  ceil($day_diff/(60*60*24));
                    $coupon_status =($total_days > 1)?$total_days." Days Remain":$total_days." Day Remain";
                }
            }

            $action ='<a class="delete" couponcodeid="' . $record->id . '" style="margin: 0 7px;">
                            <img src="' . asset('/assets/images/template-images/remove-1.png') . '" style="width:20px; height: 20px;" title="Delete">
                        </a>';


            $data_arr[] = array(
                "DT_RowId" =>"delete_coupon_code_".$record->id,
                "id" => $cnt++,
                "first_name" => (trim($username) != "")?$username:"--",
                "coupon_code" => (trim($coupon_code) != Null)?$coupon_code:"--",
                "code_active_date" => $code_active_date,
                "coupon_status" => $coupon_status,
                'action'=>$action
            );

        }
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        return json_encode($response);

    }

    //delete Delete Coupon Code
    public function getCouponDealsDeleteCouponCode(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Coupon Code Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $CouponCodeDetails = CouponCodeDetail::query()->where('id', $id)->where('user_id',0)->first();
        if ($CouponCodeDetails == Null) {
//            Session::flash('error', 'Coupon Code Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'data not found'
            ]);
        }
        $coupon_id = $CouponCodeDetails->coupon_id;

        $couponDetail = CouponDetails::query()->where('status','=',1)->where('id',$coupon_id)->first();

        if($couponDetail !=  Null){

            $couponDetail->total_coupon =$couponDetail->total_coupon - 1;
            $couponDetail->available_coupon =$couponDetail->available_coupon - 1;
            $couponDetail->save();

            $CouponCodeDetails->delete();
//            Session::flash('success', 'Coupon Code  remove successfully!');
            return response()->json([
                'success' => true,
                'message' => 'Coupon Code  remove successfully!'
            ]);
        }else{
//            Session::flash('success', 'Coupon Code Not found!');
            return response()->json([
                'success' => true,
                'message' => 'Coupon Code Not found!'
            ]);
        }

    }

    //delete Add Coupon Code
    public function getCouponDealsAddCouponCode(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $coupon_id = $request->get('coupon_id');
        $total_coupon = $request->get('total_coupon');
        if ($coupon_id == Null) {
//            Session::flash('error', 'Coupon Code Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Coupon Code Not Found!'
            ]);
        }
        if(is_numeric($total_coupon)){
            if($total_coupon <= 999)
            {
                $couponDetail = CouponDetails::query()->where('id',$coupon_id)->first();
                if($couponDetail !=  Null){
                    try {
                        $this->insertCouponCodeDetails($total_coupon,$couponDetail->id,$couponDetail->coupon_name);
                        $couponDetail->total_coupon =$couponDetail->total_coupon + floor($total_coupon);
                        $couponDetail->available_coupon =$couponDetail->available_coupon + floor($total_coupon);
                        $couponDetail->save();
                        Session::flash('success', 'Coupon Code  added successfully!');
                        return response()->json([
                            'success' => true,
                            'message' => 'Coupon Code  added successfully!'
                        ]);
                    }catch (\Exception $e){
                        //Session::flash('success', 'Coupon Code Not added please try again!');
                        return response()->json([
                            'success' => false,
                            "message" =>'Coupon Code Not added please try again!',
                        ]);
                    }
                }else{
                    //Session::flash('success', 'Coupon Code Not added please try again!');
                    return response()->json([
                        'success' => false,
                        "message" =>'Coupon Code Not added please try again!'
                    ]);
                }
            }else{
                return response()->json([
                    'success' => false,
                    "message" =>'Please enter valid coupon code max limit 999!'
                ]);
            }

        }else{
//            Session::flash('success', 'Coupon Code Not added please try again!');
            return response()->json([
                'success' => false,
                "message" =>'Please enter valid coupon number in digit!'
            ]);
        }


    }

    public function getCouponList(Request $request, $slug)
    {
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        $coupon_list = CouponDetails::query()->get();
//        dd($categories);
        if ($service_category != Null) {
            $view = view('admin.pages.coupon_deals.coupon.manage', compact('service_category', 'slug',
                'coupon_list'));
        } else {
            $view = view('admin.pages.coupon_deals.coupon.manage', compact('slug'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsAddCoupon(Request $request, $slug)
    {
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        $category_list = CouponCategory::query()->where('status', 1)->get();
        if ($service_category != Null) {
            $view = view('admin.pages.coupon_deals.coupon.form', compact('service_category', 'slug', 'category_list'));
        } else {
            $view = view('admin.pages.coupon_deals.coupon.form', compact('slug', 'category_list'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsEditCoupon(Request $request, $slug, $id)
    {
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        $category_list = CouponCategory::query()->where('status', 1)->get();
        $coupon_details = CouponDetails::query()->where('id', $id)->first();
        if ($service_category != Null) {
            $view = view('admin.pages.coupon_deals.coupon.form', compact('service_category', 'coupon_details', 'slug', 'category_list'));
        } else {
            $view = view('admin.pages.coupon_deals.coupon.form', compact('slug', 'coupon_details', 'category_list'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getCouponDealsUpdateCoupon(CouponDealsCouponDetailsRequest $request, $slug)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type')->where('slug', $slug)->first();
        if ($service_category != Null) {
            $id = $request->get('id');
            if ($id > 0) {
                $coupon_details = CouponDetails::query()->where('id', $id)->first();
                Session::flash('success', 'Coupon Updated successfully!');
            } else {
                $coupon_details = new CouponDetails();
//                $coupon_details->active_date = Date('Y-m-d H:i:s', strtotime($request->get('active_date')));
                $coupon_details->total_coupon = $request->get('total_coupon');

                $coupon_details->available_coupon = $request->get('total_coupon');
                Session::flash('success', 'Coupon Added successfully!');
            }
            if ($request->file('coupon_image')) {
                if (\File::exists(public_path('/assets/images/coupon_images/' . $coupon_details->image))) {
                    \File::delete(public_path('/assets/images/coupon_images/' . $coupon_details->image));
                }
                $file = $request->file('coupon_image');
                $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path() . '/assets/images/coupon_images/', $file_new);
                $coupon_details->coupon_image = $file_new;
            }

//            $coupon_details->coupon_code = $request->get('coupon_code');
            $coupon_details->category_id = $request->get('category_id');
            $coupon_details->coupon_name = $request->get('coupon_name');
//            if ($request->get('coupon_brand_name') != Null){
                $coupon_details->coupon_brand_name = $request->get('coupon_brand_name');
//            }
            $coupon_details->coupon_amount = $request->get('coupon_amount');
//            $coupon_details->coupon_limit = $request->get('coupon_limit');
            $coupon_details->coupon_validity = $request->get('coupon_validity');

            $coupon_details->link = $request->get('link');
            $coupon_details->terms_condition = $request->get('terms_condition');
            $coupon_details->how_to_redeem = $request->get('how_to_redeem');

//            $coupon_details->expiry_date = Date('Y-m-d H:i:s', strtotime($request->get('expiry_date')));

            $coupon_details->status = $request->get('status');
            $coupon_details->save();
        }
        if ($id == 0 || $id == "") {
            if ($request->get('total_coupon') > 0) {
                $total_coupon = $request->get('total_coupon');

                $this->insertCouponCodeDetails($total_coupon,$coupon_details->id,$coupon_details->coupon_name);
                /*for ($i = 1; $i <= $total_coupon; $i++) {
                    $coupon_code_details = new CouponCodeDetail();
                    $coupon_code_details->coupon_id = $coupon_details->id;
                    $coupon_code_details->coupon_code = $this->RandomCouponGenerate($coupon_details->coupon_name);
                    $coupon_code_details->save();
                }*/
            }
        }
        return redirect()->route('get:admin:coupon_deals:coupon_list', $slug);
    }

    public function insertCouponCodeDetails($total_coupon=0,$coupon_id=0,$coupon_name=""){
        for ($i = 1; $i <= $total_coupon; $i++) {
            $coupon_code_details = new CouponCodeDetail();
            $coupon_code_details->coupon_id = $coupon_id;
            $coupon_code_details->coupon_code = $this->RandomCouponGenerate($coupon_name);
            $coupon_code_details->save();
        }
    }

    public function RandomCouponGenerate($coupon_name){
        $str_result = time();
        $coupon_name =  str_replace(' ', '', $coupon_name);;
        $random_srt= substr(str_shuffle($str_result),
            0, 4);
        $coupon_code = strtoupper(substr($coupon_name,0,4).$random_srt);
        return $coupon_code;
    }

    public function getCouponDealsUpdateCouponStatus(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $id = $request->get('id');
        if ($id == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $coupon_details = CouponDetails::query()->where('id', '=', $id)->first();
        if ($coupon_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($coupon_details->status == 1) {
            $coupon_details->status = 0;
        } else {
            $coupon_details->status = 1;
        }
        $coupon_details->save();
        return response()->json([
            'success' => true,
            'status' => $coupon_details->status,
        ]);
    }
}
