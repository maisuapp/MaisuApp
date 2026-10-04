<?php

namespace App\Http\Controllers;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Http\Requests\PromocodeDetailsRequest;
use App\Http\Requests\ProviderStoreRequest;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\StoreProductExcel;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\StoreRequest;
use App\Imports\StoreProductDetailsImport;
use App\model\DeliveryCharges;
use App\model\LanguageLists;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderBankDetails;
use App\model\ProviderDocuments;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ReassignedDriverList;
use App\model\RequiredDocuments;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\ServiceSliderBanner;
use App\model\ShopperProviderDetails;
use App\model\ShopperProviderDocuments;
use App\model\ShopperRequiredDocuments;
use App\model\StoreDetails;
use App\model\StoreProductCategory;
use App\model\StoreProductCustomizeCategory;
use App\model\StoreProductDetails;
use App\model\StoreProductOptions;
use App\model\StoreProductSize;
use App\model\StoreProductToppings;
use App\model\StoreRatings;
use App\model\StoreTimings;
use App\model\TransportDriverDetails;
use App\model\TransportVehicleType;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserProductBooking;
use App\model\UserProductBookingOptions;
use App\model\UserProductBookingQuantity;
use App\model\UserProductBookingSize;
use App\model\UserProductBookingStatus;
use App\model\UserProductBookingToppings;
use App\model\UserRatings;
use App\model\UserReferHistory;
use App\model\UserWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RequiredDocumentsRequest;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PlaceOrderCreateCustomerRequest;
use App\model\UserAddress;
use App\model\UserOrderCart;
use Illuminate\Support\Arr;
use App\model\RestrictedArea;
use App\Classes\UserClassApi;
use App\model\GeneralSettings;

class StoreController extends Controller
{
    private $adminClass;
    private $store_order_status;
    private $notificationClass;
    private $food_delivery;
    private $bike_ride;
    private $is_restricted = 0;
    private $store_private_service_ids;
    private $medicineDelivery = 10;

    public function __construct(AdminClass $adminClass, NotificationClass $notificationClass)
    {
        $this->middleware('auth');
        $this->adminClass = $adminClass;
        $this->notificationClass = $notificationClass;
        $this->store_order_status = ['', 'pending', 'approved', 'rejected', 'cancelled', 'processing', 'approved-by-driver', 'arrived-driver', 'ongoing', 'completed', 'failed'];
        $this->food_delivery = 5;
        $this->bike_ride = 1;
        $this->store_private_service_ids = [5,6,7,8,9,10];

        $this->middleware( function ($request, $next) {
            $is_restrict_admin = $request->get('is_restrict_admin');
            $this->is_restricted = $is_restrict_admin;
            return $next($request);
        });
    }

    public function getStoreServiceDashboard($slug, Request $request)
    {
        $service_category = ServiceCategory::select('id', 'name', 'slug')->where('slug', $slug)->first();
        $approved_store = ProviderServices::where('service_cat_id', $service_category->id)
            ->join('store_details', 'store_details.provider_service_id', 'provider_services.id')
            ->where('status', 1)->count();
        $live_store = ProviderServices::where('service_cat_id', $service_category->id)
            ->join('store_details', 'store_details.provider_service_id', 'provider_services.id')
            ->where('status', 1)->where('current_status', 1)->count();
        $pending_store = ProviderServices::where('service_cat_id', $service_category->id)
            ->join('store_details', 'store_details.provider_service_id', 'provider_services.id')
            ->where('status', 0)->count();

        $total_orders = UserProductBooking::where('service_cat_id', $service_category->id)->where('status', '!=', 11)->count();
        $completed_orders = UserProductBooking::where('service_cat_id', $service_category->id)->where('status', 9)->count();
        $running_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('status', [5, 7, 8, 9])->count();
        $cancelled_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('status', [3, 4, 10])->count();

        $order_total = UserProductBooking::select(DB::raw('sum(total_pay) as total_pay'))
            ->groupBy('service_cat_id')
            ->where('service_cat_id', $service_category->id)
            ->where('status', 9)
            ->first();
        $store_total = UserProductBooking::select(DB::raw('sum(store_amount) as store_amount'))
            ->groupBy('service_cat_id')
            ->where('service_cat_id', $service_category->id)
            ->where('status', 9)
            ->first();

        $cancel_total = UserProductBooking::select(DB::raw('sum(cancel_charge) as cancel_charge'))
            ->groupBy('service_cat_id')
            ->where('service_cat_id', $service_category->id)
            ->where('status', 4)
            ->where('cancel_by', "user")
            ->first();
//        dd($cancel_total);
        $cancel_charge = 0;
        if ($cancel_total != Null) {
            $cancel_charge = $cancel_total->cancel_charge;
        }

        if ($order_total != Null && $store_total != Null) {
            $admin_earning = round((($order_total->total_pay) + ($cancel_charge)) - ($store_total->store_amount), 2);
//            $admin_earning = round((($order_total->total_pay) ) - ($store_total->store_amount), 2);
        } else {
            $admin_earning = 0.00;
        }

        if ($service_category != Null) {
            $view = view('admin.pages.super_admin.service_dashboard.store_dashboard', compact('service_category', 'slug', 'approved_store', 'live_store', 'pending_store', 'total_orders', 'completed_orders', 'cancelled_orders', 'running_orders', 'admin_earning'));
        } else {
            $view = view('admin.pages.super_admin.service_dashboard.store_dashboard', compact('slug'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //store service list
    public function getStoreServiceList(Request $request)
    {
        $store_service_categories = ServiceCategory::select('id', 'name', 'slug', 'icon_name', 'category_type', 'status')->where('category_type', 2)->get();
        if ($store_service_categories->isEmpty()) {
            $view = view('admin.pages.store_services.store_service_list');
        } else {
            $view = view('admin.pages.store_services.store_service_list', compact('store_service_categories'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //store provider list
    public function getStoreProviderList(Request $request, $slug, $status)
    {
        if ($status == "approved") {
            $status = 1;
            $provider_status = 1;
        } elseif ($status == "un-approved") {
            $status = 0;
            $provider_status = 0;
        } elseif ($status == "blocked") {
            $status = 2;
            $provider_status = 2;
        } elseif ($status == "rejected") {
            $status = 3;
            $provider_status = 3;
        } else {
            return redirect()->back();
        }

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        if ($status == 1 && $provider_status == 1) {
            $store_details = StoreDetails::query()->select('store_details.id as store_id', 'store_details.name as store_name', 'store_details.rating', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as provider_name"), 'providers.email as provider_email', 'providers.contact_number', 'providers.created_at','providers.app_version as store_app_version')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('provider_services.service_cat_id', $service_category->id)
                ->where('provider_services.status', $provider_status)
                ->where('providers.status', $status)
                ->get();
            $store_id = $store_details->pluck('store_id')->toArray();
            $products_count = StoreProductDetails::query()->select(array(DB::raw('COUNT(store_product_details.id) as products,store_id')))
                ->whereIN('store_id', $store_id)->groupBy('store_id')->pluck('products', 'store_id')->toArray();
            $store_driver_count = TransportDriverDetails::select(array(DB::raw('COUNT(transport_driver_details.id) as total_driver,store_id')))
                ->whereIN('store_id', $store_id)->groupBy('store_id')->pluck('total_driver', 'store_id')->toArray();
        } else {
            $store_details = StoreDetails::query()->select('store_details.id as store_id', 'store_details.name as store_name', 'store_details.rating', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as provider_name"), 'providers.email as provider_email', 'providers.contact_number', 'providers.created_at','providers.app_version as store_app_version')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('provider_services.service_cat_id', $service_category->id)
                ->where('provider_services.status', $provider_status)
//                ->orWhere('providers.status', $status)
                ->get();
            $store_id = $store_details->pluck('store_id')->toArray();
            $products_count = StoreProductDetails::query()->select(array(DB::raw('COUNT(store_product_details.id) as products,store_id')))
                ->whereIN('store_id', $store_id)->groupBy('store_id')->pluck('products', 'store_id')->toArray();
            $store_driver_count = TransportDriverDetails::query()->select(array(DB::raw('COUNT(transport_driver_details.id) as total_driver,store_id')))
                ->whereIN('store_id', $store_id)->groupBy('store_id')->pluck('total_driver', 'store_id')->toArray();
        }
        if ($request->ajax()) {
            $view = view('admin.pages.store_services.store_provider_list', compact('slug', 'status', 'service_category', 'store_details', 'products_count','store_driver_count'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.store_services.store_provider_list', compact('slug', 'status', 'service_category', 'store_details', 'products_count','store_driver_count'));
    }

    //store product category list
    public function getStoreCategoryList(Request $request, $slug)
    {
        $is_sub_cat_flow = 0;
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category != Null) {
            $is_sub_cat_flow = $service_category->is_sub_cat_flow - 0;
            $store_categories = StoreProductCategory::query()->where('service_cat_id', $service_category->id)->where('parent_id', '=', 0)->get();
            $category_id = $store_categories->pluck('id')->toArray();

            if($is_sub_cat_flow == 1)
            {
                $category_count = StoreProductCategory::query()
                    ->select(array(DB::raw('COUNT(store_product_category.id) as categorys,parent_id')))
                    ->whereIN('parent_id', $category_id)
                    ->groupBy('parent_id')
                    ->pluck('categorys', 'parent_id')
                    ->toArray();
            }else{
                $category_count = StoreProductCategory::query()
                    ->select(array(DB::raw('COUNT(store_product_category.id) as categorys,parent_id,id as sub_cat_id')))
                    ->whereIN('parent_id', $category_id)
                    ->groupBy('sub_cat_id')
                    ->pluck('sub_cat_id', 'parent_id')
                    ->toArray();
            }

            $view = view('admin.pages.store_services.product_category.manage', compact('slug','store_categories', 'category_count','is_sub_cat_flow'));
        } else {
            $view = view('admin.pages.store_services.product_category.manage', compact('slug','is_sub_cat_flow'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //store product add category
    public function getAddStoreCategory(Request $request, $slug)
    {
        $language_lists = LanguageLists::query()->orderBy('id','asc')->get();
        if ($request->ajax()) {
            $view = view('admin.pages.store_services.product_category.form', compact('slug','language_lists'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.store_services.product_category.form', compact('slug', 'language_lists'));
    }

    //store product edit category
    public function getEditStoreCategory(Request $request, $slug1, $slug2)
    {
        //slug1 is service category slug
        $slug = $slug1;
        $language_lists = LanguageLists::query()->orderBy('id','asc')->get();
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category != Null) {
            $store_category = StoreProductCategory::query()->where('slug', $slug2)->first();
            if ($store_category != Null) {
                $view = view('admin.pages.store_services.product_category.form', compact('slug','language_lists', 'store_category'));
            } else {
                $view = view('admin.pages.store_services.product_category.form', compact('slug','language_lists'));
            }
        } else {
            $view = view('admin.pages.store_services.product_category.form', compact('slug','language_lists'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //store product delete category
    public function getDeleteStoreCategory(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $category = StoreProductCategory::where('id', $id)->first();
        if ($category == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
//        $category->delete();
        StoreProductCategory::where('id', $id)->delete();
//        Session::flash('error', 'Other Service Package remove successfully!');
        return response()->json([
            'success' => true
        ]);
    }

    //store product change category status
    public function getStoreCategoryChangeStatus(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $id = $request->get('id');
        if ($id == Null) {
            Session::flash('error', 'Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $store_category = StoreProductCategory::where('id', $id)->first();
        if ($store_category == Null) {
            Session::flash('error', 'Service Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($store_category->status == 1) {
            $store_category->status = 0;
            $store_category->save();
//            Session::flash('success', $service_sub_category->name . ' category disabled successfully!');
        } else {
            $store_category->status = 1;
            $store_category->save();
//            Session::flash('success', $service_sub_category->name . ' category enable successfully!');
        }
        return response()->json([
            'success' => true,
            'status' => $store_category->status
        ]);
    }

    //store product save or update category
    public function postUpdateStoreCategory($slug, StoreProductCategoryRequest $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $view = redirect()->route('get:admin:store_category_list', compact('slug'));
        $category_name = ucwords(strtolower(trim($request->get('category_name'))));
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();

        if ($service_category == Null) {
            Session::flash('error', 'service category not found!');
            return redirect()->back();
        }
        $id = $request->get('id');
        if ($id != Null) {
            $check_name = StoreProductCategory::query()->where('name', $category_name)->where('service_cat_id', $service_category->id)->where('parent_id', '=',0)->where('id', '!=', $id)->first();
        } else {
            $check_name = StoreProductCategory::query()->where('name', $category_name)->where('service_cat_id', $service_category->id)->where('parent_id', '=',0)->first();
        }
        if ($check_name != Null) {
            Session::flash('error', 'category already exists!');
            return $view;
        }
        if ($id != Null) {
            $store_category = StoreProductCategory::query()->where('id', $id)->first();
            if ($store_category == Null) {
                Session::flash('error', 'category not found!');
                return $view;
            }
        } else {
            $store_category = new StoreProductCategory();
            $store_category->service_cat_id = $service_category->id;
        }
        $store_category->name = $category_name;
        try{
            $language_list = LanguageLists::query()->select('language_name as name',
                DB::raw("(CASE WHEN language_code != 'en' THEN  concat(language_code,'_name') ELSE 'name' END) as constant_val")
            )->where('status',1)->get();
            foreach ($language_list as $key => $language){
                if(Schema::hasColumn('store_product_category',$language->constant_val)  ) {
                    $store_category->{$language->constant_val} = $request->get($language->constant_val);
                }
            }
        } catch (\Exception $e) {
            Session::flash('error', 'category detail not found!');
            return redirect()->back();
        }
        $category_icon_file = $request->file('category_icon');

        if ($category_icon_file) {
            if (\File::exists(public_path('/assets/images/store-images/category-images/' . $store_category->category_icon))) {
                \File::delete(public_path('/assets/images/store-images/category-images/' . $store_category->category_icon));
            }
            $file = $category_icon_file;
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . '/assets/images/store-images/category-images/', $file_new);
            $store_category->category_icon = $file_new;
        }

        $store_category->status = $request->get('category_status');
        $store_category->slug = $store_category->generateSlug($category_name, $service_category->name);
        $store_category->save();

        if($service_category->is_sub_cat_flow == 0){
            //code for add sub category table based sub cat flow
            try{
                if ($id != Null) {
                    //edit exist sub category
                    $get_sub_category_data = StoreProductCategory::query()->where('parent_id',$store_category->id)->first();
                    if($get_sub_category_data != Null){
                        $get_sub_category_data->service_cat_id = $service_category->id;
                        $get_sub_category_data->name = $store_category->name;
                        try{
                            $language_list = LanguageLists::query()->select('language_name as name',
                                DB::raw("(CASE WHEN language_code != 'en' THEN  concat(language_code,'_name') ELSE 'name' END) as constant_val")
                            )->where('status',1)->get();
                            foreach ($language_list as $key => $language){
                                if(Schema::hasColumn('store_product_category',$language->constant_val)  ) {
                                    $get_sub_category_data->{$language->constant_val} = $request->get($language->constant_val);
                                }
                            }
                        } catch (\Exception $e) {
                            Session::flash('error', 'category detail not found!');
                            return redirect()->back();
                        }
                        $get_sub_category_data->category_icon = $store_category->category_icon;
                        $get_sub_category_data->status = $request->get('category_status');
                        $get_sub_category_data->slug = $store_category->generateSlug("sub-".$category_name, $service_category->name);
                        $get_sub_category_data->save();
                    }
                }else{
                    //new entry for sub category
                    $last_insert_store_cate = StoreProductCategory::query()->where('id',$store_category->id)->first();
                    $insert_sub_category = $last_insert_store_cate->replicate();
                    $insert_sub_category->parent_id = $last_insert_store_cate->id; // the new project_id
                    $insert_sub_category->slug = "sub-".$last_insert_store_cate->slug; // the new project_id
                    $insert_sub_category->save();
                }

            }catch (\Exception $e){
                if (\File::exists(public_path('/assets/images/store-images/category-images/' . $store_category->category_icon))) {
                    \File::delete(public_path('/assets/images/store-images/category-images/' . $store_category->category_icon));
                }
                StoreProductCategory::query()->where('id','=',$store_category->id)->delete();
            }

        }

        if ($id == Null) {
            Session::flash('success', 'category add successfully!');
        } else {
            Session::flash('success', 'category updated successfully!');
        }
        return $view;
    }

    //store product sub category list
    public function getStoreSubCategoryList(Request $request, $slug, $category_id)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null){
            Session::flash('error', 'Service Category details not found!');
            return redirect()->back();
        }
        $store_category_details = StoreProductCategory::query()->where('service_cat_id', $service_category->id)->where('parent_id', '=', 0)->where('id', '=', $category_id)->first();
        if ($store_category_details == Null){
            Session::flash('error', 'Category details not found!');
            return redirect()->back();
        }

        $store_categories = StoreProductCategory::query()->where('service_cat_id', $service_category->id)->where('parent_id', '=', $category_id)->get();
        $view = view('admin.pages.store_services.product_category.sub_category.manage', compact('slug','store_category_details','store_categories'));

        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }
    public function getAddStoreSubCategory(Request $request, $slug, $category_id)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null){
            Session::flash('error', 'Service Category details not found!');
            return redirect()->back();
        }
        $store_category_details = StoreProductCategory::query()->where('service_cat_id', $service_category->id)->where('parent_id', '=', 0)->where('id', '=', $category_id)->first();
        if ($store_category_details == Null){
            Session::flash('error', 'Category details not found!');
            return redirect()->back();
        }
        $language_lists = LanguageLists::query()->orderBy('id','asc')->get();
        $view = view('admin.pages.store_services.product_category.sub_category.form', compact('slug', 'language_lists', 'store_category_details'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }
    public function getEditStoreSubCategory(Request $request, $slug, $subcategory_id)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null){
            Session::flash('error', 'Service Category details not found!');
            return redirect()->back();
        }
        $store_category = StoreProductCategory::query()->where('id', $subcategory_id)->first();
        if ($store_category == Null) {
            Session::flash('error', 'Sub category details not found!');
            return redirect()->back();
        }
        $category_id = $store_category->parent_id - 0;
        $store_category_details = StoreProductCategory::query()->where('service_cat_id', $service_category->id)->where('parent_id', '=', 0)->where('id', '=', $category_id)->first();
        if ($store_category_details == Null){
            Session::flash('error', 'Category details not found!');
            return redirect()->back();
        }
        $language_lists = LanguageLists::query()->orderBy('id','asc')->get();
        $view = view('admin.pages.store_services.product_category.sub_category.form', compact('slug', 'language_lists', 'store_category_details', 'store_category'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }
    public function postUpdateStoreSubCategory(StoreProductCategoryRequest $request, $slug)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'service category not found!');
            return redirect()->back();
        }
        $category_id = $request->get('category_id');
        $store_category_details = StoreProductCategory::query()->where('service_cat_id','=', $service_category->id)->where('parent_id', '=', 0)->where('id', '=', $category_id)->first();
        if ($store_category_details == Null){
            Session::flash('error', 'Category details not found!');
            return redirect()->back();
        }

        $category_name = ucwords(strtolower(trim($request->get('category_name'))));
        $id = $request->get('id');
        if ($id != Null) {
            $check_name = StoreProductCategory::query()->where('name', $category_name)->where('service_cat_id', $service_category->id)->where('parent_id', '=', $store_category_details->id)->where('id', '!=', $id)->first();
        } else {
            $check_name = StoreProductCategory::query()->where('name', $category_name)->where('service_cat_id', $service_category->id)->where('parent_id', '=', $store_category_details->id)->first();
        }
        if ($check_name != Null) {
            Session::flash('error', 'category already exists!');
            return redirect()->back();
        }
        if ($id != Null) {
            $store_category = StoreProductCategory::query()->where('id', $id)->first();
            if ($store_category == Null) {
                Session::flash('error', 'category not found!');
                return redirect()->back();
            }
        } else {
            $store_category = new StoreProductCategory();
            $store_category->service_cat_id = $service_category->id;
        }

        $store_category->name = $category_name;
        try{
            $language_list = LanguageLists::query()->select('language_name as name',
                DB::raw("(CASE WHEN language_code != 'en' THEN  concat(language_code,'_name') ELSE 'name' END) as constant_val")
            )->where('status',1)->get();
            foreach ($language_list as $key => $language){
                if(Schema::hasColumn('store_product_category',$language->constant_val)  ) {
                    $store_category->{$language->constant_val} = $request->get($language->constant_val);
                }
            }
        } catch (\Exception $e) {
            Session::flash('error', 'category detail not found!');
            return redirect()->back();
        }
        $store_category->status = $request->get('category_status');
        $store_category->parent_id = $store_category_details->id;

        /*if ($request->file('category_icon')) {
            if (\File::exists(public_path('/assets/images/store-images/category-images/' . $store_category->category_icon))) {
                \File::delete(public_path('/assets/images/store-images/category-images/' . $store_category->category_icon));
            }
            $file = $request->file('category_icon');
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . '/assets/images/store-images/category-images/', $file_new);
            $store_category->category_icon = $file_new;
        }*/

        $store_category->slug = $store_category->generateSlug($category_name, $service_category->name);
        $store_category->save();
        if ($id == Null) {
            Session::flash('success', 'category add successfully!');
        } else {
            Session::flash('success', 'category updated successfully!');
        }
        $category_id = $store_category_details->id;

        return redirect()->route('get:admin:store_sub_category_list', [ 'slug'=> $slug, 'category_id' => $category_id]);
    }


    public function getStoreProductSubCategoryList(Request $request)
    {

        $service_category_id = $request->get('service_category');
        $parent_category_id = $request->get('parent_category_id');
        $service_category = ServiceCategory::query()
            ->where('status','=',1)
            ->where('id','=',$service_category_id)
            ->first();
        if ($service_category == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Service category not found'
            ]);
        }
        $is_sub_cat_flow = $service_category->is_sub_cat_flow- 0;
        if ($is_sub_cat_flow == 0){
            $product_sub_categories = StoreProductCategory::query()->select('id', 'name', 'status')
                ->where('service_cat_id','=', $service_category->id)
                ->where('parent_id','!=', 0)
                ->get();
        } else {
            $product_sub_categories = StoreProductCategory::query()->select('id', 'name', 'status')
                ->where('service_cat_id','=', $service_category->id)
                ->where('parent_id','!=', 0)
                ->where('parent_id','=', $parent_category_id)
                ->get();
        }
        return response()->json([
            'success' => true,
            'product_sub_categories' => $product_sub_categories
        ]);
    }

    //product list
    //store product list
    public function getStoreProductList(Request $request, $slug, $id)
    {
        $service_category = ServiceCategory::query()->where('slug','=', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::query()->select('store_details.id', 'store_details.name')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->where('store_details.id','=', $id)->where('provider_services.service_cat_id', $service_category->id)->first();
        if ($store_details == Null) {
            return redirect()->back();
        }
        $store_products = StoreProductDetails::query()->select('store_product_details.id', 'store_product_details.name', 'store_product_details.status','store_product_details.is_recommended', 'store_product_category.name as category_name')
            ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
            ->where('store_product_details.store_id','=', $store_details->id)->orderBy('store_product_details.id','desc')->get();
        if ($request->ajax()) {
            $view = view('admin.pages.store_services.products.manage', compact('slug', 'service_category', 'store_details', 'store_products'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.store_services.products.manage', compact('slug', 'service_category', 'store_details', 'store_products'));
    }

    //add store product
    public function getAddStoreProduct(Request $request, $slug, $store_id)
    {
        $service_category = ServiceCategory::query()->where('slug','=', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $is_sub_cat_flow = $service_category->is_sub_cat_flow- 0;
        $product_categories = StoreProductCategory::query()->select('id', 'name', 'status')->where('service_cat_id','=', $service_category->id)->where('parent_id','=', 0)->get();
        $view = view('admin.pages.store_services.products.form', compact('slug', 'service_category', 'store_id', 'is_sub_cat_flow', 'product_categories'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //edit store product
    public function getEditStoreProduct(Request $request, $slug, $id)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $is_sub_cat_flow = $service_category->is_sub_cat_flow- 0;
        $product_details = StoreProductDetails::query()->where('id', $id)->first();
        if ($product_details == Null) {
            Session::flash('error', 'Product Details not found!');
            return redirect()->back();
        }
        $get_parent_category_id = StoreProductCategory::query()->select('id', 'name', 'status','parent_id')
            ->where('service_cat_id','=', $service_category->id)
            ->where('parent_id','!=', 0)
            ->where('id','=', $product_details->product_cate_id)
            ->pluck("parent_id")->toArray();
        $parent_category_id = 0;
        if ($get_parent_category_id != Null){
            $parent_category_id = $get_parent_category_id[0];
        }
        $product_categories = StoreProductCategory::query()->select('id', 'name',
            DB::raw("(CASE WHEN id != 0 THEN (CASE WHEN id = $parent_category_id THEN 1 ELSE 0 END) ELSE 0 END) as is_selected"), 'status')
            ->where('service_cat_id', $service_category->id)
            ->where('parent_id','=', 0)->get();

        $store_id = $product_details->store_id;
        if ($service_category->id == $this->food_delivery) {
            $customize_category = StoreProductCustomizeCategory::query()->where('product_id', $product_details->id)->get();
            $customize_size = StoreProductSize::query()->where('product_id', $product_details->id)->get();
            $customize_options = StoreProductOptions::query()->where('product_id', $product_details->id)->get();
            $customize_toppings = StoreProductToppings::query()->where('product_id', $product_details->id)->get();
            $view = view('admin.pages.store_services.products.form', compact('slug', 'service_category', 'store_id', 'is_sub_cat_flow', 'product_categories', 'product_details', 'customize_category', 'customize_size', 'customize_options', 'customize_toppings'));
        } else {
            $view = view('admin.pages.store_services.products.form', compact('slug', 'service_category', 'store_id', 'is_sub_cat_flow', 'product_categories', 'product_details'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getUpdateStoreProduct(StoreProductRequest $request, $slug)
    {
        //dd($request->all());
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $product_id = $request->get('id');
        $store_details = StoreDetails::query()->where('id', '=',$request->get('store_id'))->first();
        $service_category = ServiceCategory::query()->where('id','=', $request->get('service_cat_id'))->first();
        if ($store_details == Null || $service_category == Null) {
            Session::flash('error', 'store id not exists!');
            return redirect()->back();
        }
        $id = $store_id = $store_details->id;
        if ($product_id != Null) {
            Session::flash('success', 'product details update successfully!');
            $product_details = StoreProductDetails::query()->where('id','=', $product_id)->first();
        } else {
            Session::flash('success', 'product details add successfully!');
            $product_details = new StoreProductDetails();
            $product_details->store_id = $store_id;
        }
        $product_details->product_cate_id = $request->get('product_category');
        $product_details->name = ucwords(trim(strtolower($request->get('product_name'))));
        if ($request->file('product_image')) {
            if (\File::exists(public_path('/assets/images/store-images/product-images/' . $product_details->image))) {
                \File::delete(public_path('/assets/images/store-images/product-images/' . $product_details->image));
            }
            $file = $request->file('product_image');
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . '/assets/images/store-images/product-images/', $file_new);
            $product_details->image = $file_new;
        }
        $product_details->amount = round($request->get('product_amount'), 2);
        $product_details->discount_amount = ($request->get('discount_amount') != Null && $request->get('discount_type') != Null) ? round($request->get('discount_amount'), 2) : 0;
        $product_details->discount_type = ($request->get('discount_amount') != Null && $request->get('discount_type') != Null) ? $request->get('discount_type') : 0;
        $product_details->food_type = ($service_category->id == 5) ? (($request->get('food_type') == 1) ? 1 : 2) : 0;
        $product_details->description = ($request->get('description') != Null) ? $request->get('description') : Null;
        $product_details->status = $request->get('status');
        $product_details->save();

        if ($service_category->id == $this->food_delivery) {
            $product_id = $product_details->id;
            $customize_category = $request->get('customize_category');
            $customize_size = $request->get('size');
            $customize_options = $request->get('options');
            $customize_toppings = $request->get('toppings');
            if ($customize_category != Null) {
                $fetch_customize_category = (array_column($customize_category, 'type', 'name'));
                $fetch_customize_category_id = (array_column($customize_category, 'id', 'name'));
                //$fetch_customize_category_max_selection = (array_column($customize_category, 'max_selection', 'name'));
            }
            if ($customize_size != Null) {
                foreach ($customize_size as $size) {
                    $size_id = $size['id'];
                    if ($size_id != 0) {
                        $add_size = StoreProductSize::query()->where('id', $size_id)->first();
                        if ($add_size == Null) {
                            $add_size = new StoreProductSize();
                        }
                    } else {
                        $add_size = new StoreProductSize();
                    }
                    $add_size->product_id = $product_id;
                    $add_size->name = ucwords(strtolower(trim($size['name'])));
                    $add_size->amount = $size['amount'];
                    $add_size->save();
                }
            }
            if ($customize_options != Null) {
                foreach ($customize_options as $options) {
                    $category = $options['category'];
                    $options_id = $options['id'];
                    if (array_key_exists($category, $fetch_customize_category) !== false) {
                        $category_type = $fetch_customize_category[$category];
                        $category_id = $fetch_customize_category_id[$category];
                        //$max_selection = $fetch_customize_category_max_selection[$category];
                        $category = ucwords(strtolower(trim($category)));

                        $category_details = StoreProductCustomizeCategory::query()->where('name', $category)->where('product_id', $product_id)->first();
                        if ($category_details == Null) {
                            if ($category_id != 0) {
                                $category_details = StoreProductCustomizeCategory::query()->where('id', $category_id)->first();
                                if ($category_details == Null) {
                                    $category_details = new StoreProductCustomizeCategory();
                                }
                            } else {
                                $category_details = new StoreProductCustomizeCategory();
                            }

                        }
                        $category_details->product_id = $product_id;
                        $category_details->selection_type = $category_type;
                        //$category_details->max_selection = 0;
                        $category_details->name = $category;
                        $category_details->save();


                        if ($options_id != 0) {
                            $add_options = StoreProductOptions::query()->where('id', $options_id)->first();
                            if ($add_options == Null) {
                                $add_options = new StoreProductOptions();
                            }
                        } else {
                            $add_options = new StoreProductOptions();
                        }
                        $add_options->product_id = $product_id;
                        $add_options->category_id = $category_details->id;
                        $add_options->name = ucwords(strtolower(trim($options['name'])));
                        $add_options->amount = $options['amount'];
                        $add_options->save();
                    }
                }
            }
            if ($customize_toppings != Null) {
                foreach ($customize_toppings as $toppings) {
                    $category = $toppings['category'];
                    $toppings_id = $toppings['id'];
                    if (array_key_exists($category, $fetch_customize_category) !== false) {
                        $category_type = $fetch_customize_category[$category];
                        $category_id = $fetch_customize_category_id[$category];
                        //$max_selection = $fetch_customize_category_max_selection[$category];
                        $category = ucwords(strtolower(trim($category)));

                        $category_details = StoreProductCustomizeCategory::query()->where('name', $category)->where('product_id', $product_id)->first();
                        if ($category_details == Null) {
                            if ($category_id != 0) {
                                $category_details = StoreProductCustomizeCategory::query()->where('id', $category_id)->first();
                                if ($category_details == Null) {
                                    $category_details = new StoreProductCustomizeCategory();
                                }
                            } else {
                                $category_details = new StoreProductCustomizeCategory();
                            }
                        }
                        $category_details->product_id = $product_id;
                        $category_details->selection_type = $category_type;
                        $category_details->name = $category;
//                        $category_details->max_selection = ($max_selection > 0) ? $max_selection : 0;
                        $category_details->max_selection =  0;
                        $category_details->save();

                        if ($toppings_id != 0) {
                            $add_toppings = StoreProductToppings::query()->where('id', $toppings_id)->first();
                            if ($add_toppings == Null) {
                                $add_toppings = new StoreProductToppings();
                            }
                        } else {
                            $add_toppings = new StoreProductToppings();
                        }
                        $add_toppings->product_id = $product_id;
                        $add_toppings->category_id = $category_details->id;
                        $add_toppings->name = ucwords(strtolower(trim($toppings['name'])));
                        $add_toppings->amount = $toppings['amount'];
                        $add_toppings->save();
                    }
                }
            }
        }

        return redirect()->route('get:admin:store_product_list', compact('slug', 'id'));
    }

    public function getStoreProductChangeStatus(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $id = $request->get('id');
        if ($id == Null) {
            Session::flash('error', 'product Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $product_details = StoreProductDetails::where('id', $id)->first();
        if ($product_details == Null) {
            Session::flash('error', 'product Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($product_details->status == 1) {
            $product_details->status = 0;
            $product_details->save();
//            Session::flash('success', $service_sub_category->name . ' category disabled successfully!');
        } else {
            $product_details->status = 1;
            $product_details->save();
//            Session::flash('success', $service_sub_category->name . ' category enable successfully!');
        }
        return response()->json([
            'success' => true,
            'status' => $product_details->status
        ]);
    }

    public function getStoreProductChangeRecommendedStatus(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $id = $request->get('id');
        if ($id == Null) {
            Session::flash('error', 'product Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $product_details = StoreProductDetails::where('id', $id)->first();
        if ($product_details == Null) {
            Session::flash('error', 'product Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($product_details->is_recommended == 1) {
            $product_details->is_recommended = 0;
            $product_details->save();
        } else {
            $product_details->is_recommended = 1;
            $product_details->save();
        }
        return response()->json([
            'success' => true,
            'status' => $product_details->is_recommended
        ]);
    }

    public function getDeleteStoreProduct(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $product_details = StoreProductDetails::where('id', $id)->first();
        if ($product_details == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        StoreProductDetails::where('id', $id)->delete();
//        Session::flash('error', 'Other Service Package remove successfully!');
        return response()->json([
            'success' => true
        ]);
    }

    //code for excelsheet product upload in store
    public function getAddStoreProductExcel(Request $request, $slug, $store_id)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $view = view('admin.pages.store_services.products.excel', compact('slug', 'service_category', 'store_id'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getUpdateStoreProductExcel(StoreProductExcel $request, $slug)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $store_id = $request->get('store_id');
        $store_details = StoreDetails::query()->where('id', $store_id)->first();
        $service_category = ServiceCategory::query()->where('id','=', $request->get('service_cat_id'))->first();
        if ($store_details == Null || $service_category == Null) {
            Session::flash('error', 'Store id not exists!');
            return redirect()->back();
        }

        try {
            Excel::import(new StoreProductDetailsImport($store_id),$request->file('product_excel_file'));
            Session::flash('success', 'Product Import successfully!');
        } catch (\Exception $e) {
            Session::flash('error', 'Check and correct data in spreadsheet!');
        }

        return redirect()->route('get:admin:store_product_list', ['slug'=>$slug, 'id'=>$store_id]);
    }

    //code for topping
    public function postRemoveStoreProductCustomizeCategory(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        StoreProductCustomizeCategory::query()->where('id', $id)->delete();
        return response()->json([
            'success' => true
        ]);
    }

    public function postRemoveStoreProductCustomizeSize(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        StoreProductSize::query()->where('id', $id)->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function postRemoveStoreProductCustomizeOptions(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        StoreProductOptions::query()->where('id', $id)->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function postRemoveStoreProductCustomizeToppings(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        StoreProductToppings::query()->where('id', $id)->delete();

        return response()->json([
            'success' => true
        ]);
    }

    //store provider document list
    public function getStoreProviderDocument(Request $request, $slug, $store_id)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::where('id', $store_id)->first();
        if ($store_details == Null) {
            return redirect()->back();
        }
        $required_documents = RequiredDocuments::where('service_cat_id', $service_category->id)->get();
        foreach ($required_documents as $key => $required_document) {
            $document_details = ProviderDocuments::where('provider_service_id', '=', $store_details->provider_service_id)->where('req_document_id', $required_document->id)->first();
            if ($required_document->service_cat_id == $service_category->id) {
                $provider_documents[$key] = $document_details;
            } else {
                $provider_documents[$key] = Null;
            }
        }
        $view = view('admin.pages.store_services.document.manage', compact('slug', 'upload_document', 'required_documents', 'provider_documents'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //store order list
    public function getStoreOrderList(Request $request, $slug, $status)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $cancel_status_type = 0;
        if ($status == "all") {
            $status = [];
        } elseif ($status == "pending") {
            $status = [1];
        } elseif ($status == "approved") {
            $status = [2];
        } elseif ($status == "rejected") {
            $status = [3];
        } elseif ($status == "ongoing") {
            $status = [5, 6, 7, 8];
        } elseif ($status == "completed") {
            $status = [9];
        } elseif ($status == "cancelled") {
            $cancel_status_type = 1;
            $status = [4, 10];
        } else {
            $status = [];
        }
        if (count($status) > 0) {
            $order_list = UserProductBooking::select('id', 'user_name', 'store_name', 'total_pay', 'status', 'payment_type', 'payment_status', 'user_refund_status')->where('service_cat_id', $service_category->id)->where('status', '!=', 11)->whereIN('status', $status)->orderBy('id','desc')->get();
        } else {
            $order_list = UserProductBooking::select('id', 'user_name', 'store_name', 'total_pay', 'status', 'payment_type', 'payment_status', 'user_refund_status')->where('service_cat_id', $service_category->id)->where('status', '!=', 11)->orderBy('id','desc')->get();
        }
        $order_status = $this->store_order_status;
        $view = view('admin.pages.store_services.store_order_list', compact('slug', 'order_list', 'service_category', 'order_status', 'cancel_status_type'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getStoreWiseOrderList(Request $request, $slug, $store_id)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::select('id', 'name')->where('id', $store_id)->first();
        if ($store_details == Null) {
            return redirect()->back();
        }
        $order_status = $this->store_order_status;
        $order_list = UserProductBooking::select('id', 'user_name', 'order_product_list', 'total_pay', 'status', 'payment_type', 'payment_status', 'user_refund_status')->where('status', '!=', 11)->where('store_id', $store_id)->get();

        $view = view('admin.pages.store_services.store_order_list', compact('slug', 'store_details', 'order_list', 'order_status'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getStoreOrderDetails(Request $request, $slug, $order_id)
    {
        $general_settings = $request->get("general_settings");
        if ($general_settings == Null){
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $service_category = ServiceCategory::query()->where('slug','=', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $max_driver_reassign = $general_settings->max_driver_reassign;
        $order_details = UserProductBooking::query()->select("user_store_product_booking.*","used_promocode_details.promocode_name","used_promocode_details.discount_amount")
            ->leftJoin('used_promocode_details','used_promocode_details.id','user_store_product_booking.promo_code')
            ->where('user_store_product_booking.id', '=',$order_id)->first();
        if ($order_details == Null) {
            return redirect()->back();
        }

        $store_details = StoreDetails::query()->select('address')->where('id','=', $order_details->store_id)->first();
        $user_details = User::query()->select('contact_number')->where('id','=', $order_details->user_id)->first();
        $get_order_status = UserProductBookingStatus::query()->select('status', 'created_at')->where('order_id','=', $order_details->id)->get();
        $order_status = $this->store_order_status;

        $product_details = UserProductBookingQuantity::query()->select('id', 'product_name', 'product_id','product_cat_name', 'num_of_items', 'price_for_one')->where('order_id','=', $order_details->id)->get()->groupBy('product_cat_name');
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
            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'providers.contact_number', 'providers.avatar',
            'transport_driver_details.id as driver_details_id',
            'transport_driver_details.vehicle_company', 'transport_driver_details.model_name',
            'transport_driver_details.plat_no', 'transport_driver_details.model_year',
            'transport_driver_details.vehicle_color',
            'transport_driver_details.rating',
            'transport_vehicle_type.name as vehicle_type_name'
        )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            //->where('providers.status', '=', $status)
            ->where('transport_driver_details.id', $order_details->delivery_people_id)
            ->first();

        $shopper_details = Null;

        if ($order_details->status == 1) {
            $orders_status = "Pending";
        } elseif ($order_details->status == 2) {
            $orders_status = "Confirmed";
        } elseif ($order_details->status == 3) {
            $orders_status = "Rejected";
        } elseif ($order_details->status == 4) {
            $orders_status = "Cancelled";
        } elseif ($order_details->status == 5) {
            $orders_status = "Processing";
        } elseif ($order_details->status == 6) {
            $orders_status = "Approved By Driver";
        } elseif ($order_details->status == 7) {
            $orders_status = "Arrived";
        } elseif ($order_details->status == 8) {
            $orders_status = "Ongoing";
        } elseif ($order_details->status == 9) {
            $orders_status = "Completed";
        } elseif ($order_details->status == 10) {
            $orders_status = "Failed";
        }
//        dd($product_details);
        $view = view('admin.pages.store_services.store_order_details', compact('slug', 'product_option_list','shopper_details',
            'order_details', 'get_order_status', 'order_status', 'product_details', 'store_details', 'user_details',
            'driver_details', 'service_category', 'orders_status'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getDeleteAdminStoreOrder(Request $request, $slug, $product_id)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $check_duplicate = UserProductBookingQuantity::where('id', $product_id)->first();
        if ($check_duplicate == Null) {
            Session::flash('error', 'Product details not found!');
            return redirect()->back();
        }
        $place_order = UserProductBooking::where('id', $check_duplicate->order_id)->first();
        if ($place_order == Null) {
            Session::flash('error', 'Order details not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::where('id', $place_order->store_id)->first();
        $check_duplicate->delete();

        if ($store_details == Null) {
            Session::flash('error', 'Restaurants not found!');
            return redirect()->back();
        }

        $order_product_list = [];
        $service_settings = ServiceSettings::where('service_cat_id', $service_category->id)->first();
        if ($service_settings != Null) {
            $admin_commission = $service_settings->admin_commission;
            $store_commission = $service_settings->store_wise_commission;
            $tax_cost = $service_settings->tax != 0 ? $service_settings->tax : 0;
        } else {
            $admin_commission = 0;
            $store_commission = $service_settings->store_wise_commission;
            $tax_cost = 0;
        }
        if ($store_details->store_commission != 0) {
            $store_commission = $store_details->store_commission;
        }

        $item_cost = 0;
        $get_product_booking_quantity = UserProductBookingQuantity::where('order_id', $place_order->id)->get();
        foreach ($get_product_booking_quantity as $key => $get_product_booking_qty) {
            array_push($order_product_list, $get_product_booking_qty->product_name . ' x ' . $get_product_booking_qty->num_of_items);
            $item_cost = round($item_cost + ($get_product_booking_qty->price_for_one * $get_product_booking_qty->num_of_items), 2);
            UserProductBookingSize::where('order_id',$get_product_booking_qty->order_id)->where('product_id',$get_product_booking_qty->product_id)->delete();
            UserProductBookingToppings::where('order_id',$get_product_booking_qty->order_id)->where('product_id',$get_product_booking_qty->product_id)->delete();
            UserProductBookingOptions::where('order_id',$get_product_booking_qty->order_id)->where('product_id',$get_product_booking_qty->product_id)->delete();
        }

        $discount_cost = 0;
        if ($store_details->offer_min_amount != 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
            if ($item_cost >= $store_details->offer_min_amount) {
                if ($store_details->offer_type == 1) {
                    $discount_cost = $item_cost - (($item_cost) - ($store_details->offer_amount));
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
        if ($place_order->promo_code != 0) {
            $discount_on_amount = (($item_cost - $discount_cost) + $place_order->packaging_cost);
            $promo_code = $this->adminClass->checkPromoCodeValid($place_order->promo_code, $discount_on_amount, $place_order->user_id, $service_category->id);
            if ($promo_code != 0) {
                $add_promocode_details = UsedPromocodeDetails::where('user_id', $place_order->user_id)->where('id', $place_order->promo_code)->where('service_cat_id', $service_category->id)->first();
                $get_prmocode = PromocodeDetails::where('id', $add_promocode_details->id)->where('status', 1)->first();
                if ($get_prmocode != Null) {
                    $promo_code_discount = $promo_code;

                    $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                    $add_promocode_details->discount_amount = $promo_code;
                    $add_promocode_details->save();

                    $place_order->promo_code = $add_promocode_details->id;
                    $place_order->save();
                }
            }
        }
        $place_order->totalPay($item_cost, $discount_cost, $promo_code_discount, $tax_cost, $delivery_cost, $packaging_cost, $admin_commission, $store_commission);

        $place_order->order_product_list = implode(", ", $order_product_list);
        $place_order->save();
        return redirect()->route('get:admin:store_order_details', [$slug, $place_order->id]);
    }

    public function getAddStoreOrderProduct(Request $request, $slug, $order_id)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $order_details = UserProductBooking::select('id', 'store_id', 'user_id', 'delivery_people_id', 'order_no', 'user_name', 'store_name', 'delivery_address', 'created_at', 'total_item_cost', 'discount_cost', 'tax_cost', 'delivery_cost', 'packaging_cost', 'total_pay', 'payment_type', 'payment_status', 'status', 'cancel_by', 'cancel_reason')
            ->where('id', $order_id)->first();
        if ($order_details == Null) {
            return redirect()->back();
        }
        $store_details = StoreDetails::select('id', 'address')->where('id', $order_details->store_id)->first();
        if ($store_details == Null) {
            return redirect()->back();
        }
        $product_details = UserProductBookingQuantity::select('id', 'product_name', 'product_cat_name', 'num_of_items', 'price_for_one')
            ->where('order_id', $order_details->id)->get()->groupBy('product_cat_name');

        $store_product_array_id = StoreProductDetails::where('store_id', $store_details->id)->groupBy('product_cate_id')->pluck('product_cate_id');
        $store_product_details = StoreProductDetails::where('store_id', $store_details->id)->get();
        $product_categories = StoreProductCategory::select('id', 'name', 'status')
            ->where('service_cat_id', $service_category->id)
            ->whereIn('id', $store_product_array_id)
            ->get();
//        dd($store_product_details);
        $view = view('admin.pages.store_services.add_order_product', compact('slug', 'service_category',
            'order_details', 'store_details', 'product_details', 'store_product_details', 'product_categories'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getEditStoreOrderDetails(Request $request, $slug, $order_id){
        $order_details = UserProductBooking::select('refund_amount', 'cancel_charge', 'user_refund_status', 'shopper_id', 'shopper_type', 'id', 'store_id', 'user_id', 'delivery_people_id', 'order_no', 'user_name', 'store_name', 'delivery_address', 'created_at', 'total_item_cost', 'discount_cost', 'tax_cost', 'delivery_cost', 'packaging_cost', 'total_pay', 'payment_type', 'payment_status', 'status', 'cancel_by', 'cancel_reason', 'additional_request')
            ->where('id', $order_id)->first();
        if ($order_details == Null) {
            return redirect()->back();
        }

        $get_order_status = UserProductBookingStatus::select('status', 'created_at')->where('order_id', $order_details->id)->get();
        $order_status = $this->store_order_status;

        $product_details = UserProductBookingQuantity::select('id', 'product_name', 'product_id','product_cat_name', 'num_of_items', 'price_for_one')->where('order_id', $order_details->id)->get()->groupBy('product_cat_name');
        $product_option_list = [];
        foreach ($product_details as $product_list) {
            foreach ($product_list as $product) {
                $size = UserProductBookingSize::query()->where('order_id', $order_details->id)->where('product_id', $product->product_id)->get()->pluck('name')->toArray();
                $options = UserProductBookingOptions::query()->where('order_id', $order_details->id)->where('product_id', $product->product_id)->get()->pluck('name')->toArray();
                $toppings = UserProductBookingToppings::query()->where('order_id', $order_details->id)->where('product_id', $product->product_id)->get()->pluck('name')->toArray();
                $add_ons = array_merge($size, $options, $toppings);
                $add_ons = implode(', ', $add_ons);
                $product_option_list[$product->id] = $add_ons;
            }
        }
        $view = view('admin.pages.store_services.edit_store_order_details', compact('slug', 'product_option_list','shopper_details', 'order_details', 'get_order_status', 'order_status', 'product_details', 'store_details', 'user_details', 'driver_details', 'service_category'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function postAdminStoreOrder(Request $request, $slug)
    {
        //dd($request->all());
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $validator = Validator::make($request->all(), [
            "order_id" => "required",
            "product_category" => "required",
            "product_id" => "required",
            "product_quantity" => "required",
        ]);
        if ($validator->fails()) {
            Session::flash('error', 'All is required!');
            return redirect()->back();
        }

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $place_order = UserProductBooking::query()->where('id', $request->get('order_id'))->first();
        if ($place_order == Null) {
            Session::flash('error', 'Order details not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::query()->where('id', $place_order->store_id)->first();

        if ($store_details == Null) {
            Session::flash('error', 'Restaurants not found!');
            return redirect()->back();
        }
        //$booking_type 1 =now, 2 = schedule
        $product_id_list = array_map('trim', explode(",", $request->get('product_id')));
        $product_quantity_list = array_map('trim', explode(",", $request->get('product_quantity')));
        $order_product_list = [];

        if (count($product_id_list) == count($product_quantity_list)) {
            $service_settings = ServiceSettings::query()->where('service_cat_id', $service_category->id)->first();
            if ($service_settings != Null) {
                $admin_commission = $service_settings->admin_commission;
                $store_commission = $service_settings->store_wise_commission;
                $tax_cost = $service_settings->tax != 0 ? $service_settings->tax : 0;
            } else {
                $admin_commission = 0;
                $store_commission = $service_settings->store_wise_commission;
                $tax_cost = 0;
            }
            if ($store_details->store_commission != 0) {
                $store_commission = $store_details->store_commission;
            }
            $item_cost = 0;
            $price = 0;
            $total_price = 0;
            $get_product = StoreProductDetails::query()->where('id', $request->get('product_id'))->where('status', 1)->first();
            if ($get_product == Null) {
                Session::flash('error', 'Product details not found!');
                return redirect()->back();
            }
            $check_duplicate = UserProductBookingQuantity::query()->where('order_id', $place_order->id)->where('product_id', $get_product->id)->first();
            if ($check_duplicate == Null) {
                $get_product_category = StoreProductCategory::query()->where('id', $get_product->product_cate_id)->where('status', 1)->first();
                if ($get_product_category == Null) {
                    Session::flash('error', 'Product details not found!');
                    return redirect()->back();
                }
                $size_data = [];
                $size = $request->get('size');
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
                        $size_get = StoreProductSize::query()->where('product_id', $get_product->id)->get();
                        if (count($size_get) > 2) {
                            Session::flash('error', 'your not selected valid size');
                            return redirect()->back();
                        } elseif (count($size_get) == 1) {
                            $price = $size_get[0]->amount;
                            $size_data = [
                                "id" => $size_get[0]->id,
                                "name" => $size_get[0]->name,
                                "amount" => $size_get[0]->amount
                            ];
                        } else {
                            $price = $get_product->amount;
                        }
                    }
                } else {
                    $size_get = StoreProductSize::query()->where('product_id', $get_product->id)->get();
                    if (count($size_get) > 2) {
                        Session::flash('error', 'your not selected valid size');
                        return redirect()->back();
                    } elseif (count($size_get) == 1) {
                        $price = $size_get[0]->amount;
                        $size_data = [
                            "id" => $size_get[0]->id,
                            "name" => $size_get[0]->name,
                            "amount" => $size_get[0]->amount
                        ];
                    } else {
                        $price = $get_product->amount;
                    }
                }

                if ($get_product->discount_amount != Null && $get_product->discount_type != Null) {
                    if ($get_product->discount_type == 1) {
                        $price = $price - $get_product->discount_amount;
                    } elseif ($get_product->discount_type == 2) {
                        $discount_amount = round((($price * $get_product->discount_amount) / 100), 2);
                        $price = $price - $discount_amount;
                    }
                }

                $options = $request->get('options');
                $options_data = Null;
                $options_data = [];
                if (!empty($options)) {
                    foreach ($options as $option) {
                        $options_details = StoreProductOptions::query()->select('store_product_options.id',
                            'store_product_options.name',
                            'store_product_options.amount',
                            'store_product_customize_category.name as category_name')
                            ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_options.category_id')
                            ->where('store_product_options.id', $option)
                            ->where('store_product_options.product_id', $get_product->id)->first();
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
                $toppings = $request->get('toppings');
                $toppings_data = Null;
                $toppings_data = [];
                if (!empty($toppings)) {
                    foreach ($toppings as $topping) {
                        $toppings_details = StoreProductToppings::query()->select('store_product_toppings.id',
                            'store_product_toppings.name',
                            'store_product_toppings.amount',
                            'store_product_customize_category.name as category_name')
                            ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')
                            ->where('store_product_toppings.id', $topping)
                            ->where('store_product_toppings.product_id', $get_product->id)->first();
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

                $price_for_one = $price;
                $price = round($price * $request->get('product_quantity'), 2);
                //$item_cost = round($total_price + $price, 2);

                $order_quantity = new UserProductBookingQuantity();
                $order_quantity->order_id = $place_order->id;
                $order_quantity->product_id = $get_product->id;
                $order_quantity->product_name = $get_product->name;
                $order_quantity->product_cat_name = $get_product_category->name;
                $order_quantity->num_of_items = $request->get('product_quantity');
                $order_quantity->price_for_one = $price_for_one;
                $order_quantity->save();

                if ($request->get('size') != Null) {
                    $product_size = new UserProductBookingSize();
                    $product_size->order_id = $place_order->id;
                    $product_size->product_id = $get_product->id;
                    $product_size->name = $size_data['name'];
                    $product_size->amount = $size_data['amount'];
                    $product_size->save();
                }
                if ($request->get('options') != Null) {
                    foreach ($options_data as $options) {
                        $product_option = new UserProductBookingOptions();
                        $product_option->order_id = $place_order->id;
                        $product_option->product_id = $get_product->id;
                        $product_option->name = $options['name'];
                        $product_option->category = $options['category'];
                        $product_option->amount = $options['amount'];
                        $product_option->save();
                    }
                }
                if($request->get('toppings') != Null){
                    foreach ($toppings_data as $toppings) {
                        $product_topping = new UserProductBookingToppings();
                        $product_topping->order_id = $place_order->id;
                        $product_topping->product_id = $get_product->id;
                        $product_topping->name = $toppings['name'];
                        $product_topping->category = $toppings['category'];
                        $product_topping->amount = $toppings['amount'];
                        $product_topping->save();
                    }
                }
            }
            $get_product_booking_quantity = UserProductBookingQuantity::query()->where('order_id', $place_order->id)->get();
            foreach ($get_product_booking_quantity as $key => $get_product_booking_qty) {
                array_push($order_product_list, $get_product_booking_qty->product_name . ' x ' . $get_product_booking_qty->num_of_items);
                $item_cost = round($item_cost + ($get_product_booking_qty->price_for_one * $get_product_booking_qty->num_of_items), 2);
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
            if ($place_order->promo_code != 0) {
                $discount_on_amount = (($item_cost - $discount_cost) + $place_order->packaging_cost);
                $promo_code = $this->adminClass->checkPromoCodeValid($place_order->promo_code, $discount_on_amount, $place_order->user_id, $service_category->id);
                if ($promo_code != 0) {
                    $add_promocode_details = UsedPromocodeDetails::query()->where('user_id', $place_order->user_id)->where('id', $place_order->promo_code)->where('service_cat_id', $service_category->id)->first();
                    $get_prmocode = PromocodeDetails::query()->where('id', $add_promocode_details->id)->where('status', 1)->first();
                    if ($get_prmocode != Null) {
                        $promo_code_discount = $promo_code;

                        $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                        $add_promocode_details->discount_amount = $promo_code;
                        $add_promocode_details->save();

                        $place_order->promo_code = $add_promocode_details->id;
                        $place_order->save();
                    }
                }
            }
            $place_order->totalPay($item_cost, $discount_cost, $promo_code_discount, $tax_cost, $delivery_cost, $packaging_cost, $admin_commission, $store_commission);

            $place_order->order_product_list = implode(", ", $order_product_list);
            $place_order->save();
            return redirect()->route('get:admin:store_order_details', [$slug, $place_order->id]);
        } else {
            Session::flash('error', 'Restaurants not found!');
            return redirect()->back();
        }
    }

    public function getAjaxStoreOrderProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "store_id" => "required",
                "category_id" => "required",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first()
            ]);
        }
        $store_id = $request->get('store_id');
        $category_id = $request->get('category_id');
        if ($store_id == Null || $category_id == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $store_details = StoreDetails::where('id', $store_id)->first();
        if ($store_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $store_product_details = StoreProductDetails::where('store_id', $store_details->id)
            ->where('product_cate_id', $category_id)
            ->get();
        if ($store_product_details->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'product' => $store_product_details,
        ]);
    }

    public function getAjaxStoreOrderProductToppings(Request $request){
        $store_id = $request->get('store_id');
        $product_id = $request->get('product_id');
        if ($store_id == Null || $product_id == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $store_details = StoreDetails::where('id', $store_id)->first();
        if ($store_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $store_product_details = StoreProductDetails::where('id', $product_id)->first();
        if ($store_product_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }

        $customize_size = StoreProductSize::query()->where('product_id', $store_product_details->id)->get();
        $customize_options = StoreProductOptions::query()->where('product_id', $store_product_details->id)->get();
        $customize_toppings = StoreProductToppings::query()->where('product_id', $store_product_details->id)->get();

        return response()->json([
            'success' => true,
            'size' => $customize_size,
            'size_count' => count($customize_size),
            'options' => $customize_options,
            'options_count' => count($customize_options),
            'toppings' => $customize_toppings,
            'toppings_count' => count($customize_toppings),
        ]);
    }

    //refund order
    public function getUserRefundAmountSettle(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $order_details = UserProductBooking::query()->where('id', $id)->first();
        if ($order_details == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ]);
        }
        if ($order_details->user_refund_status == 0) {
            if ($order_details->payment_type == 2 || $order_details->payment_type == 3) {
                $store_details = StoreDetails::query()->where('id', $order_details->store_id)->first();
                if ($store_details == Null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Store details not found'
                    ]);
                }
                $service_category = ServiceCategory::query()->where('id', $order_details->service_cat_id)->first();
                if ($service_category == Null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Store details not found'
                    ]);
                }
                try {
                    $get_last_transaction = UserWalletTransaction::query()->where('user_id', $order_details->user_id)->orderBy('id', 'desc')->first();
                    if ($get_last_transaction != Null) {
                        $last_amount = $get_last_transaction->remaining_balance;
                    } else {
                        $last_amount = 0;
                    }
                } catch (\Exception $e) {
                    $last_amount = 0;
                }
                $add_balance = new UserWalletTransaction();
                $add_balance->user_id = $order_details->user_id;
                $add_balance->transaction_type = 1;
                $add_balance->amount = $order_details->refund_amount;
                //$add_balance->subject = "Order Refund Amount";
                $add_balance->subject = "Refund from " . $store_details->name . " - " . $service_category->name . " #" . $order_details->order_no;
                $add_balance->remaining_balance = floatval($last_amount + $order_details->refund_amount);
                $add_balance->subject_code = 10;
                $add_balance->order_no = '';
                $add_balance->save();

                $order_details->user_refund_status = 1;
                $order_details->save();
            }
        }
        return response()->json([
            'success' => true
        ]);
    }

    //store review list
    public function getStoreReviewList(Request $request, $slug, $store_id)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::where('id', $store_id)->first();
        if ($store_details == Null) {
            return redirect()->back();
        }
        $review_list = StoreRatings::select('store_rating.id', 'users.first_name', 'users.last_name', 'store_rating.rating', 'store_rating.comment', 'store_rating.status')
            ->join('users', 'users.id', '=', 'store_rating.user_id')
            ->where('store_rating.store_id', $store_id)
//            ->where('provider_services.service_cat_id', $service_category->id)
            ->get();

//        $provider_reviews = StoreRatings::select('transport_driver_rating.id', 'transport_driver_rating.rating',
//            'transport_driver_rating.comment', 'transport_driver_rating.status', 'users.first_name', 'users.last_name',
//            'providers.name', 'user_ride_booking.pickup_datetime', 'user_ride_booking.ride_no'
//        )
//            ->join('user_ride_booking', 'user_ride_booking.id', '=', 'transport_driver_rating.ride_id')
//            ->join('users', 'users.id', '=', 'transport_driver_rating.user_id')
//            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'transport_driver_rating.driver_id')
//            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
//            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
//            ->where('transport_driver_rating.driver_id', '=', $driver_id)
//            ->where('user_ride_booking.service_cat_id', '=', $service_category->id)
//            ->orderBy('transport_driver_rating.id', 'desc')
//            ->get();
        if ($request->ajax()) {
            $view = view('admin.pages.store_services.store_review_list', compact('slug', 'service_category', 'review_list', 'store_details'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.store_services.store_review_list', compact('slug', 'service_category', 'review_list', 'store_details'));
    }

    public function getStoreReviewChangeStatus(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $id = $request->get('id');
        if ($id == Null) {
            Session::flash('error', 'Review Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $review = StoreRatings::where('id', $id)->first();
        if ($review == Null) {
            Session::flash('error', 'Review Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($review->status == 1) {
            $review->status = 0;
            $review->save();
//            Session::flash('success', $service_sub_category->name . ' category disabled successfully!');
        } else {
            $review->status = 1;
            $review->save();
//            Session::flash('success', $service_sub_category->name . ' category enable successfully!');
        }
        return response()->json([
            'success' => true,
            'status' => $review->status
        ]);
    }

    public function getDeleteStoreReview(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $review = StoreRatings::where('id', $id)->first();
        if ($review == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        StoreRatings::where('id', $id)->delete();
//        Session::flash('error', 'Other Service Package remove successfully!');
        return response()->json([
            'success' => true
        ]);
    }

    //get add store
    public function getAddStore($slug, Request $request)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        if ($request->ajax()) {
            $view = view('admin.pages.store_services.store.form', compact('slug', 'service_category'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.store_services.store.form', compact('slug', 'service_category'));
    }

    public function getEditStore($slug, Request $request, $id)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $store_details = StoreDetails::query()->select('store_details.id as store_id',
            'provider_services.id as provider_service_id',
            'providers.id as provider_id',
            'providers.login_type as login_type',
            'store_details.name as store_name',
            'providers.service_radius as delivery_radius',
            'store_details.eta_delivery_time',
            'store_details.image as store_banner',
            'store_details.offer_type',
            'store_details.taken_away_type',
            'store_details.offer_amount',
            'store_details.offer_min_amount',
            'store_details.order_min_amount',
            'store_details.packaging_charges',
            'store_details.description',
            'store_details.address as store_address',
            'store_details.address_lat',
            'store_details.address_long',
            'store_details.store_code',
            'store_details.is_private_driver_notification as is_private_driver_notification',
            'providers.first_name',
            'providers.last_name',
            'providers.email',
            'providers.avatar as profile_image',
            'providers.country_code',
            'providers.contact_number',
            'providers.gender',
            'provider_services.current_status',
            'provider_services.status'
        )
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('provider_services.service_cat_id', $service_category->id)
            ->where('store_details.id', $id)->first();
        $bank_details = ProviderBankDetails::query()->where('provider_id','=', $store_details->provider_id)->first();
        $store_timings = StoreTimings::query()->select('day', 'store_open_time', 'store_close_time')->where('store_id','=', $store_details->store_id)->get();
        $store_open_timings = $store_timings->pluck('store_open_time', 'day')->toArray();
        $store_close_timings = $store_timings->pluck('store_close_time', 'day')->toArray();

        if ($request->ajax()) {
            $view = view('admin.pages.store_services.store.form', compact('slug', 'service_category', 'store_details', 'bank_details', 'store_open_timings', 'store_close_timings'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.store_services.store.form', compact('slug', 'service_category', 'store_details', 'bank_details', 'store_open_timings', 'store_close_timings'));
    }

    public function getUpdateStore($slug, StoreRequest $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_id = $request->get('id');
        $store_open_times = $request->get('open_time');
        $store_close_times = $request->get('close_time');
//        $provider_id = $request->get('provider_id');



        $bank_name = $request->get('bank_name');
        if ($store_id != Null) {
            $store_details = StoreDetails::where('id', $store_id)->first();
            $provider_service = ProviderServices::where('id', $store_details->provider_service_id)->first();
            $provider_service->current_status = $request->get('current_status');
//            $provider_service->status = $request->get('status');
            $provider_service->save();
            $provider_details = Provider::where('id', $provider_service->provider_id)->first();
            if ($store_details == Null || $provider_details == Null) {
                Session::flash('error', 'store details not found!');
                return redirect()->back();
            }
            $provider_details->first_name = ucwords(trim(strtolower($request->get('first_name'))));
//            $provider_details->last_name = ucwords(trim(strtolower($request->get('last_name'))));
            $provider_details->email = $request->get('email');
            $provider_details->country_code = $request->get('country_code');
            $provider_details->contact_number = $request->get('contact_number');

            $provider_details->provider_type = 1;
            $provider_details->service_radius = $request->get('delivery_radius');
            $provider_details->gender = $request->get('gender');
            $provider_details->provider_type = 1;
            $provider_details->save();
        }
        else {
            $provider_details = new Provider();
            $provider_details->first_name = ucwords(trim(strtolower($request->get('first_name'))));
//            $provider_details->last_name = ucwords(trim(strtolower($request->get('last_name'))));
            $provider_details->email = $request->get('email');
            $provider_details->country_code = $request->get('country_code');
            $provider_details->contact_number = $request->get('contact_number');
//            $provider_details->contact_number = $request->get('full_number');
            $provider_details->provider_type = 1;
            $provider_details->verified_at = Date('Y-m-d H:i:s');
            $provider_details->last_active = Date('Y-m-d H:i:s');
            $provider_details->password = Hash::make($request->get('password'));
            $provider_details->login_type = "email";
            $provider_details->status = 1;
            $provider_details->service_radius = $request->get('delivery_radius');
            $provider_details->gender = $request->get('gender');
            $provider_details->save();

            $provider_service = new ProviderServices();
            $provider_service->provider_id = $provider_details->id;
            $provider_service->service_cat_id = $service_category->id;
            $provider_service->current_status = $request->get('current_status');
            $provider_service->status = 1;
            $provider_service->save();

            $store_details = new StoreDetails();
            $store_details->provider_service_id = $provider_service->id;
        }
        if ($request->file('profile_image')) {
            if (\File::exists(public_path('/assets/images/profile-images/provider/' . $provider_details->avatar))) {
                \File::delete(public_path('/assets/images/profile-images/provider/' . $provider_details->avatar));
            }
            $file = $request->file('profile_image');
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . '/assets/images/profile-images/provider/', $file_new);
            $provider_details->avatar = $file_new;
            $provider_details->save();
        }
        $store_details->name = ucwords($request->get('store_name'));
        if ($request->file('store_banner')) {
            if (\File::exists(public_path('/assets/images/store-images/' . $store_details->image))) {
                \File::delete(public_path('/assets/images/store-images/' . $store_details->image));
            }
            $file = $request->file('store_banner');
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . '/assets/images/store-images/', $file_new);
            $store_details->image = $file_new;
        }
//        if ($request->get('order_min_amount') != Null || $request->get('order_min_amount') != 0) {
//            $store_details->order_min_amount = $request->get('order_min_amount');
//        } else {
//            $store_details->order_min_amount = 0;
//        }
//        if ($request->get('offer_min_amount') != Null && $request->get('offer_amount') != Null && $request->get('offer_type') != Null) {
//            $store_details->offer_min_amount = $request->get('offer_min_amount');
//        } else {
//            $store_details->offer_min_amount = 0;
//        }
//        if ($request->get('offer_amount') != Null && $request->get('offer_type') != Null) {
//            $store_details->offer_amount = $request->get('offer_amount');
//            $store_details->offer_type = $request->get('offer_type');
//        } else {
//            $store_details->offer_amount = 0;
//            $store_details->offer_type = 0;
//        }
//        if ($request->get('packaging_charges') != Null) {
//            $store_details->packaging_charges = $request->get('packaging_charges');
//        } else {
//            $store_details->packaging_charges = 0;
//        }
        $store_details->taken_away_type = 1;
        if ($request->get('taken_away_type') != Null) {
            $store_details->taken_away_type = 2;
        }

        $store_details->order_min_amount = ($request->get('order_min_amount') != Null || $request->get('order_min_amount') != 0) ? $request->get('order_min_amount') : 0;
        $store_details->offer_min_amount = ($request->get('offer_min_amount') != Null && $request->get('offer_amount') != Null && $request->get('offer_type') != Null) ? $request->get('offer_min_amount') : 0;
        $store_details->offer_amount = ($request->get('offer_amount') != Null && $request->get('offer_type') != Null) ? $request->get('offer_amount') : 0;
        $store_details->offer_type = ($request->get('offer_amount') != Null && $request->get('offer_type') != Null) ? $request->get('offer_type') : 0;
        $store_details->packaging_charges = ($request->get('packaging_charges') != Null) ? $request->get('packaging_charges') : 0;
        $store_details->address = $request->get('store_address');
        $store_details->address_lat = $request->get('address_lat');
        $store_details->address_long = $request->get('address_long');
        $store_details->eta_delivery_time = $request->get('eta_delivery_time');
        $store_details->description = ($request->get('description') != Null) ? $request->get('description') : Null;
        $store_details->is_private_driver_notification = ($request->get('is_private_driver_notification') != Null ) ? $request->get('is_private_driver_notification') : 0;
        $store_details->slug = "";
//        $store_details->store_code = $request->get('store_code');
        $store_details->save();

        $request_days = [];
        if ($request->get('day')) {
            foreach ($request->get('day') as $key => $day) {
                $request_days[] = $key;
            }
        }
        $days = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
        if (in_array("all", $request_days) && $store_open_times[0] != Null && $store_close_times[0] != Null) {
            for ($i = 0; $i < 7; $i++) {
                try {
                    $update_store_timing = StoreTimings::where('store_id', $store_details->id)->where('day', $days[$i])->first();
                    if ($update_store_timing == Null) {
                        $update_store_timing = new StoreTimings();
                        $update_store_timing->store_id = $store_details->id;
                        $update_store_timing->day = strtoupper(trim($days[$i]));
                    }
                    $update_store_timing->store_open_time = Date('H:i:s', strtotime($store_open_times[0]));
                    $update_store_timing->store_close_time = Date('H:i:s', strtotime($store_close_times[0]));
                    $update_store_timing->save();
                } catch (\Exception $e) {
                }
            }
        } else {
            for ($i = 0; $i < 7; $i++) {
                if (in_array($days[$i], $request_days) && $store_open_times[$i + 1] != Null && $store_close_times[$i + 1] != Null) {
                    try {
                        $update_store_timing = StoreTimings::where('store_id', $store_details->id)->where('day', $days[$i])->first();
                        if ($update_store_timing == Null) {
                            $update_store_timing = new StoreTimings();
                            $update_store_timing->store_id = $store_details->id;
                            $update_store_timing->day = strtoupper(trim($days[$i]));
                        }
                        $update_store_timing->store_open_time = Date('H:i:s', strtotime($store_open_times[$i + 1]));
                        $update_store_timing->store_close_time = Date('H:i:s', strtotime($store_close_times[$i + 1]));
                        $update_store_timing->save();
                    } catch (\Exception $e) {
                    }
                } else {
                    try {
//                        StoreTimings::where('store_id', $store_details->id)->where('day', $days[$i])->delete();
                        $update_store_timing = StoreTimings::where('store_id', $store_details->id)->where('day', $days[$i])->first();
                        if ($update_store_timing == Null) {
                            $update_store_timing = new StoreTimings();
                            $update_store_timing->store_id = $store_details->id;
                            $update_store_timing->day = strtoupper(trim($days[$i]));
                        }
                        $update_store_timing->store_open_time = "00:00:00";
                        $update_store_timing->store_close_time = "00:00:00";
                        $update_store_timing->save();
                    } catch (\Exception $e) {
                    }
                }
            }
        }
        if ($bank_name != Null) {
//            try {
            $provider_bank_account = ProviderBankDetails::where('provider_id', $provider_details->id)->count();
//            } catch (\Exception $e) {
//            }
//            dd($provider_bank_account);
        }
        $status = "approved";
        $view = redirect()->route('get:admin:store_list', compact('slug', 'status'));
        if ($store_id == Null) {
            Session::flash('success', 'store details add successfully!');
        } else {
            Session::flash('success', 'store details updated successfully!');
        }
        return $view;
    }

    public function getUpdateStoreStatus(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $validator = Validator::make($request->all(), [
                "id" => "required",
                "request_for" => "required",
                "service_cat_id" => "required",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first()
            ]);
        }
        $id = $request->get('id');
        $request_for = $request->get('request_for');
        $service_cat_id = $request->get('service_cat_id');
        //1 = approved
        //2 = blocked
        //3 = rejected
        if ($id == Null || $request_for == Null || $service_cat_id == Null || $service_cat_id == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $store_details = StoreDetails::query()->where('id', $id)->first();
        if ($store_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $provider_service = ProviderServices::query()->where('id', $store_details->provider_service_id)->first();
        if ($provider_service == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $provider = Provider::query()->where('id', $provider_service->provider_id)->first();
        if ($provider == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($request_for == 1) {
            $provider_service->status = 1;
            $provider_service->save();
            $provider->status = 1;
            $provider->save();

            $general_settings = request()->get("general_settings");
            if ($general_settings !=  Null) {
                if ($general_settings->send_mail == 1) {
                    $store_name = ucwords(strtolower($store_details->name));
                    try{
                        $mail_type = "store_account_approve";
                        $to_mail = $provider->email;
                        $subject = "Your pending account approved by admin";
                        $disp_data = array("##store_name##"=>$store_name );
                        $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                    }
                    catch (\Exception $e){}
                }
            }
        } elseif ($request_for == 2) {
            $provider_service->status = 2;
            $provider_service->save();
            $provider->status = 2;
            $provider->save();

            $general_settings = request()->get("general_settings");
            if ($general_settings !=  Null) {
                if ($general_settings->send_mail == 1) {
                    $store_name = ucwords(strtolower($store_details->name));
                    try{
                        $mail_type = "store_account_block";
                        $to_mail= $provider->email;
                        $subject = "Your Account has been Block by Admin";
                        $disp_data = array("##store_name##"=>$store_name );
                        $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                    }
                    catch (\Exception $e){}
                }
            }

        } elseif ($request_for == 3) {
            $validator = Validator::make($request->all(), [
                    "reject_reason" => "required"
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => $validator->errors()->first()
                ]);
            }
            $provider_service->status = 3;
            $provider_service->rejected_reason = $request->get('reason');
            $provider_service->save();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        return response()->json([
            'success' => true
        ]);
    }

    public function getStoreServiceSetting(Request $request, $slug)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $service_settings = ServiceSettings::where('service_cat_id', $service_category->id)->first();

        $view = view('admin.pages.store_services.store_service_setting', compact('slug', 'service_category', 'service_settings'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function postUpdateStoreServiceSetting(Request $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $service_category = $request->get('service_cat_id');
        $service_category = ServiceCategory::where('id', $service_category)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $id = $request->get('id');
        if ($id != Null) {
            Session::flash('success', 'Service Settings Updated Successfully!');
            $service_settings = ServiceSettings::where('id', $id)->first();
        } else {
            Session::flash('success', 'Service Settings Added Successfully!');
            $service_settings = new ServiceSettings();
        }
        $service_settings->provider_search_radius = $request->get('provider_search_radius');
        $service_settings->tax = $request->get('tax');
        $service_settings->admin_commission = $request->get('admin_commission');
        $service_settings->provider_accept_timeout = $request->get('provider_accept_timeout');
//        $service_settings->shopper_admin_commission = $request->get('shopper_admin_commission');
        $service_settings->cancel_charge = $request->get('cancel_charge');
        $service_settings->delivery_charge = $request->get('delivery_charge');
//        $service_settings->delivery_commission = $request->get('delivery_commission');
        $service_settings->save();
        $slug = $service_category->slug;
        return redirect()->route('get:admin:store_service_setting', compact('slug'));
    }

    public function getStoreRequiredDocumentList($slug, Request $request)
    {
        //transport service category find
        $service_category = ServiceCategory::where('slug', $slug)->first();
//
        $required_documents_list = RequiredDocuments::select('required_documents.id', 'required_documents.name as document_name',
            'required_documents.status', 'service_category.name')
            ->join('service_category', 'service_category.id', '=', 'required_documents.service_cat_id')
            ->where('service_category.slug', '=', $slug)
            ->get();
        if ($request->ajax()) {
            $view = view('admin.pages.super_admin.required-documents.manage', compact('slug', 'service_category', 'required_documents_list'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.super_admin.required-documents.manage', compact('slug', 'service_category', 'required_documents_list'));
    }

    //get add services category wise required documents form
    public function getStoreAddRequiredDocument($slug, Request $request)
    {
        //transport service category find
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Required document`s service category not found!');
            return redirect()->back();
        }
        if ($request->ajax()) {
            $view = view('admin.pages.super_admin.required-documents.form', compact('slug', 'service_category'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.super_admin.required-documents.form', compact('slug', 'service_category'));
    }

    //edit required documents
    public function getStoreEditRequiredDocument($slug, $id, Request $request)
    {
        $required_document = RequiredDocuments::where('id', $id)->first();
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($required_document != Null) {
            if ($request->ajax()) {
                $view = view('admin.pages.super_admin.required-documents.form', compact('slug', 'required_document', 'service_category'))->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return view('admin.pages.super_admin.required-documents.form', compact('slug', 'required_document', 'service_category'));
        } else {
            Session::flash('error', 'Required Document Not Found!');
            return redirect()->back();
        }
    }

    //save or update required documents
    public function postStoreUpdateRequiredDocument(RequiredDocumentsRequest $request)
    {
        $id = $request->get('id');
        if ($id != Null) {
            $required_document = RequiredDocuments::where('id', $request->get('id'))->first();
        } else {
            $required_document = new RequiredDocuments();
        }
        $required_document->name = $request->get('name');
        $required_document->service_cat_id = $request->get('service_cat_id');
        $required_document->status = $request->get('status');
        $required_document->save();

        $sevice_category = ServiceCategory::where('id', $required_document->service_cat_id)->first();
        if ($id != Null) {
            Session::flash('success', $sevice_category->slug . ' Required Document Updated successfully!');
        } else {
            Session::flash('success', $sevice_category->slug . ' Required Document Added successfully!');
        }
        return redirect()->route('get:admin:store_required_document_list', [$sevice_category->slug]);
    }

    public function getStoreServiceDispatcher(Request $request, $slug, $slug2)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        if ($slug2 == "new") {
            $status = [1];
        } elseif ($slug2 == "approved") {
            $status = [2];
        } elseif ($slug2 == "processing") {
            $status = [5, 7];
        } elseif ($slug2 == "ongoing") {
            $status = [8];
        } elseif ($slug2 == "completed") {
            $status = [9];
        } elseif ($slug2 == "cancelled") {
            $status = [3, 4];
        } else {
            return redirect()->back();
        }
        $new = [1];
        $approved = [2];
        $processing = [5, 7];
        $ongoing = [8];
        $completed = [9];
        $cancelled = [3, 4];
        $date = new \DateTime();
//        $date->modify('-24 hours');
        $date->modify('-30 days');
        $date = $date->format('Y-m-d H:i:s');
        $store_order_list = UserProductBooking::select('user_store_product_booking.id',
            'user_store_product_booking.order_no',
            'user_store_product_booking.delivery_people_id',
            'user_store_product_booking.shopper_id',
            'user_store_product_booking.shopper_type',
            'user_store_product_booking.order_shopper_status',
            'user_store_product_booking.order_delivery_status',
            'user_store_product_booking.delivery_address as destination_address',
            'user_store_product_booking.order_product_list',
            'user_store_product_booking.status',
            'user_store_product_booking.created_at',
            'user_store_product_booking.taken_away_type',
            'user_store_product_booking.order_type',
            'user_store_product_booking.payment_type',
            'user_store_product_booking.is_driver_reassign',
            'user_store_product_booking.user_refund_status',
            'store_details.image as store_banner',
            'store_details.name as store_name',
            'store_details.address as pickup_address')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->where('user_store_product_booking.status', '!=', 11)
            ->where('user_store_product_booking.service_cat_id', $service_category->id)
            ->whereIN('user_store_product_booking.status', $status)
//            ->where('user_store_product_booking.created_at', '>=', $date)
            ->orderBy('user_store_product_booking.id', "desc")
            ->get();
        $pending_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $new)->where('created_at', '>=', $date)->where('user_store_product_booking.status', '!=', 11)->count();
        $approved_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $approved)->where('created_at', '>=', $date)->where('user_store_product_booking.status', '!=', 11)->count();
        $processing_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $processing)->where('created_at', '>=', $date)->where('user_store_product_booking.status', '!=', 11)->count();
        $ongoing_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $ongoing)->where('created_at', '>=', $date)->where('user_store_product_booking.status', '!=', 11)->count();
        $completed_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $completed)->where('created_at', '>=', $date)->where('user_store_product_booking.status', '!=', 11)->count();
        $cancelled_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $cancelled)->where('created_at', '>=', $date)->where('user_store_product_booking.status', '!=', 11)->count();
        $order_slug = $slug2;
        $view = view('admin.pages.store_services.store_dispatcher', compact('slug', 'service_category', 'store_order_list', 'pending_orders', 'approved_orders', 'processing_orders', 'ongoing_orders', 'completed_orders', 'cancelled_orders', 'order_slug'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getStoreOrderStatusUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "update_status" => "required|numeric|in:2,3,4,5,8,9"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first()
            ]);
        }
        $id = $request->get('id');
        $update_status = $request->get('update_status');
        if ($update_status == 3 || $update_status == 4) {
            $validator = Validator::make($request->all(), [
                "reason" => "required"
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => $validator->errors()->first()
                ]);
            }
        }
        $store_order = UserProductBooking::query()->where('id', $id)->first();
        if ($store_order == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ]);
        }

        $user_details = User::query()->where('id', $store_order->user_id)->first();
        if ($store_order->status == 3 || $store_order->status == 4) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
        $notify = null;
        switch ($update_status) {
            case 2:
                if ($store_order->status == 1) {
                    $store_order->status = $update_status;
                    $store_order->save();
                    if ($user_details != Null) {
                        $this->notificationClass->userStoreDeliveryNotification($store_order->id, $user_details->device_token, $store_order->status, $user_details->login_device, $user_details->language);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ]);
                }
                break;
            case 3:
                if ($store_order->status == 1) {
                    $order_status = $store_order->status;
                    $store_order->otp = null;
                    $store_order->status = $update_status;
                    $store_order->cancel_by = "admin";
                    $store_order->cancel_reason = $request->get('reason');
                    $store_order->save();

                    if ($order_status < 5){
                        if ($store_order->promo_code > 0){
                            $used_promocode_details = UsedPromocodeDetails::query()->where('id', $store_order->promo_code)->first();
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
                    if ($store_order->payment_type == 2 || $store_order->payment_type == 3) {
                        //$service_settings = ServiceSettings::where('service_cat_id', $store_order->service_cat_id)->first();
                        //if ($service_settings != Null) {
                        //$cancel_charge = $service_settings->cancel_charge != Null ? $service_settings->cancel_charge : 0;
                        //$count_cancel_charge = round((($store_order->total_item_cost * $cancel_charge) / 100), 2);
                        //$charges = round($store_order->total_pay - $count_cancel_charge, 2);
                        $store_order->cancel_charge = 0;
                        $store_order->refund_amount = $store_order->total_pay;
                        $store_order->save();
                        //}
                    }
                    if ($user_details != Null) {
                        $this->notificationClass->userStoreDeliveryNotification($store_order->id, $user_details->device_token, $store_order->status, $user_details->login_device, $user_details->language);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ]);
                }
                break;
            case 4:
//                if ($store_order->status == 2) {
                if ($store_order->status != 3 && $store_order->status != 4 && $store_order->status != 9 && $store_order->status != 10) {
                    $order_status = $store_order->status;
                    $store_order->otp = null;
                    $store_order->status = $update_status;
                    $store_order->cancel_by = "admin";
                    $store_order->cancel_reason = $request->get('reason');
                    $store_order->save();

                    if ($order_status < 5){
                        if ($store_order->promo_code > 0){
                            $used_promocode_details = UsedPromocodeDetails::query()->where('id', $store_order->promo_code)->first();
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
                    if ($store_order->payment_type == 2 || $store_order->payment_type == 3) {
                        //$service_settings = ServiceSettings::where('service_cat_id', $store_order->service_cat_id)->first();
                        //if ($service_settings != Null) {
                        //$cancel_charge = $service_settings->cancel_charge != Null ? $service_settings->cancel_charge : 0;
                        //$count_cancel_charge = round((($store_order->total_item_cost * $cancel_charge) / 100), 2);
                        //$charges = round($store_order->total_pay - $count_cancel_charge, 2);
                        $store_order->cancel_charge = 0;
                        $store_order->refund_amount = $store_order->total_pay;
                        $store_order->save();
                        //}
                    }
                    if ($user_details != Null) {
                        $this->notificationClass->userStoreDeliveryNotification($store_order->id, $user_details->device_token, $store_order->status, $user_details->login_device, $user_details->language);
                    }
                    if ($store_order->delivery_people_id != Null) {
                        $order_status = UserProductBookingStatus::query()->where('order_id', $id)->where('status', 4)->first();
                        if ($order_status == Null) {
                            $order_status = new UserProductBookingStatus();
                            $order_status->order_id = $store_order->id;
                            $order_status->status = $update_status;
                            $order_status->save();
                        }
                        $store_order->order_delivery_status = $update_status;
                        $store_order->save();
                        ProviderUserRunningService::query()->where('service_cat_id', $store_order->service_cat_id)->where('user_id', $store_order->user_id)->where('booking_id', $store_order->id)->delete();

                        $driver_details = Provider::query()->select('providers.device_token', 'provider_services.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.login_device', 'providers.language')
                            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                            ->where('transport_driver_details.id', $store_order->delivery_people_id)->first();

                        if ($driver_details != Null) {
                            $cancel_by = "admin";
                            $notify = $this->notificationClass->driverCancelNotification($store_order->id,$driver_details->device_token, $store_order->service_cat_id, $driver_details->id, $driver_details->login_device,$driver_details->language,$cancel_by);
                        }
                    }
                    $store_provider_details = Provider::query()->select('providers.device_token', 'providers.login_device', 'providers.language')
                        ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                        ->join('store_details', 'store_details.provider_service_id', '=', 'provider_services.id')
                        ->where('store_details.id', $store_order->store_id)->first();

                    if ($store_provider_details != Null) {
                        $this->notificationClass->storeDeliveryUserNotification($store_order->id, $store_provider_details->device_token, $store_order->status, $store_provider_details->login_device,$store_provider_details->language);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ]);
                }
                break;
            case 5:
                if ($store_order->status == 2) {
                    //check schedule or not
                    if($store_order->order_type == 1){
                        $order_date_time = $store_order->order_date_time;
                        $current_date_time = strtotime(date("Y-m-d H:i:s"));
                        $beforeOneHour  = strtotime("-1 hour",strtotime($store_order->order_date_time));
                        //return $current_date_time."--".date("Y-m-d H:i:s",$beforeOneHour);
                        if($current_date_time < $beforeOneHour ){
                            return response()->json([
                                'success' => false,
                                'message' => 'Sorry, its Schedule Order. you can start before 1 hours of schedule time .'
                            ]);
                        }
                    }
                    $store_order->status = $update_status;
                    $store_order->save();
                    if ($user_details != Null) {
                        $this->notificationClass->userStoreDeliveryNotification($store_order->id, $user_details->device_token, $store_order->status, $user_details->login_device, $user_details->language);
                    }
                    if ($store_order->taken_away_type != 2) {
                        $this->notificationClass->driverRequestNotification($store_order->service_cat_id, $store_order->id);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ]);
                }
                break;
            case 8:
                if ($store_order->status >= 5) {
                    $store_order->status = $update_status;
                    $store_order->save();
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ]);
                }
                break;
            case 9:
                if ($store_order->status >= 8) {
                    if ($store_order->taken_away_type != 2) {
                        $driver_details = TransportDriverDetails::query()->where('id', $store_order->delivery_people_id)->first();
                        if ($driver_details == Null) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Data not found'
                            ]);
                        }

                        $update_driver_status = ProviderServices::query()->where('id', $driver_details->provider_service_id)->first();
                        if ($update_driver_status == Null) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Data not found'
                            ]);
                        }

                        $driver = Provider::query()->where('id', $update_driver_status->provider_id)->first();
                        if ($update_driver_status == Null) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Data not found'
                            ]);
                        }
                    }
                    if (in_array($store_order->status, [5, 6, 7, 8]) && $store_order->status < 9) {
                        $check_duplicate = UserProductBookingStatus::query()->where('order_id', $store_order->id)->where('status', 7)->first();
                        if ($check_duplicate == Null) {
                            $store_delivery_status = new UserProductBookingStatus();
                            $store_delivery_status->order_id = $store_order->id;
                            $store_delivery_status->status = 7;
                            $store_delivery_status->save();
                        }

                        $check_duplicate = UserProductBookingStatus::query()->where('order_id', $store_order->id)->where('status', 9)->first();
                        if ($check_duplicate == Null) {
                            $store_delivery_status = new UserProductBookingStatus();
                            $store_delivery_status->order_id = $store_order->id;
                            $store_delivery_status->status = 9;
                            $store_delivery_status->save();
                        }
                        $store_order->status = 9;
                        $store_order->save();

                    }

                    $store_order->status = $update_status;
                    $store_order->payment_status = 1;
                    $store_order->save();

                    $this->notificationClass->userStoreDeliveryNotification($store_order->id, $user_details->device_token, $store_order->status, $user_details->login_device, $user_details->language);

                    if ($store_order->taken_away_type != 2) {
                        $update_driver_status->current_status = 1;
                        $update_driver_status->save();

                        ProviderUserRunningService::query()->where('provider_id', $driver->id)->where('user_id', $store_order->user_id)->where('service_cat_id', $store_order->service_cat_id)->where('booking_id', $store_order->id)->delete();
                    }
                    $store_order->order_delivery_status = $update_status;
                    $store_order->save();

                    $store_provider_details = Provider::query()->select('providers.device_token', 'providers.login_device', 'providers.language')
                        ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                        ->join('store_details', 'store_details.provider_service_id', '=', 'provider_services.id')
                        ->where('store_details.id', $store_order->store_id)->first();

                    if ($store_provider_details != Null) {
                        $this->notificationClass->storeDeliveryUserNotification($store_order->id, $store_provider_details->device_token, $store_order->status, $store_provider_details->login_device, $store_provider_details->language);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ]);
                }
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ]);
        }

        $order_status = UserProductBookingStatus::query()->where('order_id', $store_order->id)->where('status', $update_status)->first();
        if ($order_status == Null) {
            $add_order_status = new UserProductBookingStatus();
            $add_order_status->order_id = $store_order->id;
            $add_order_status->status = $update_status;
            $add_order_status->save();
        }
        return response()->json([
            'success' => true,
            'notify' => $notify,
        ]);
    }

    public function getUpdateStoreChangePassword(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $validator = Validator::make($request->all(), [
                "store_id" => "required|numeric",
                "password" => "required|min:6",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "message" => $validator->errors()->first(),
            ]);
        }
        $provider_service = StoreDetails::query()->select('provider_services.provider_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->where('store_details.id', $request->get('store_id'))->first();
        if ($provider_service == Null) {
            return response()->json([
                'success' => false,
                'message' => 'something went wrong'
            ]);
        }
        $provider = Provider::query()->where('id', $provider_service->provider_id)->first();
        if ($provider == Null) {
            return response()->json([
                'success' => false,
                'message' => 'something went wrong'
            ]);
        }
        $provider->password = Hash::make($request->get('password'));
        $provider->save();
        return response()->json([
            'success' => true,
            'message' => 'Password changed Successfully'
        ]);
    }

    //store earning reports
    public function getStoreEarningReport(Request $request, $slug)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service Category Not Found!');
            return redirect()->back();
        }
        $user_list = UserProductBooking::select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_store_product_booking.user_id')
            ->get();
        $driver_list = UserProductBooking::select('transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.delivery_people_id as driver_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_store_product_booking.delivery_people_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_store_product_booking.delivery_people_id')
            ->get();
        $store_list = UserProductBooking::select('store_details.name as store_name','store_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.store_id')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_store_product_booking.store_id')
            ->get();
        $view = view('admin.pages.store_services.earning_report.admin_earning_report', compact('slug', 'user_list', 'driver_list', 'service_category', 'store_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function postStoreEarningReport(Request $request, $slug)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service Category Not Found!');
            return redirect()->back();
        }
        $user_list = UserProductBooking::select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_store_product_booking.user_id')
            ->get();
        $driver_list = UserProductBooking::select('transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.delivery_people_id as driver_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_store_product_booking.delivery_people_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_store_product_booking.delivery_people_id')
            ->get();
        $store_list = UserProductBooking::select('store_details.name as store_name','store_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.store_id')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_store_product_booking.store_id')
            ->get();

        $from_date = ($request['from_date'] != Null && $request['to_date'] != Null) ? $request['from_date'] : Null;
        $to_date = ($request['from_date'] != Null && $request['to_date'] != Null) ? $request['to_date'] : Null;
        $driver = $request['driver'] != Null ? $request['driver'] : Null;
        $store = $request['store'] != Null ? $request['store'] : Null;
        $user = $request['user'] != Null ? $request['user'] : Null;
        $payment_type = $request['payment_type'] != Null ? $request['payment_type'] : Null;
        $driver_pay_type = $request['driver_pay_type'] != Null ? $request['driver_pay_type'] : Null;
        $store_pay_type = $request['store_pay_type'] != Null ? $request['store_pay_type'] : Null;
        $store_order_list = UserProductBooking::query()->select('user_store_product_booking.id', 'user_store_product_booking.payment_status', 'user_store_product_booking.payment_type',
            'user_store_product_booking.taken_away_type',
            'user_store_product_booking.created_at',
            'user_store_product_booking.order_no',
            'user_store_product_booking.order_type',
            'user_store_product_booking.total_item_cost',
            'user_store_product_booking.discount_cost',
            'user_store_product_booking.packaging_cost',
            'user_store_product_booking.delivery_cost',
            'user_store_product_booking.tax_cost',
            'user_store_product_booking.total_pay',
            'user_store_product_booking.store_amount',
            'user_store_product_booking.admin_commission',
            'user_store_product_booking.delivery_admin_commission',
            'user_store_product_booking.shopper_earring',
            'user_store_product_booking.shopper_admin_commission',
            'user_store_product_booking.delivery_people_id',
            'user_store_product_booking.store_id',
            'user_store_product_booking.user_id',
            'user_store_product_booking.promo_code',
            'user_store_product_booking.tip',
            'used_promocode_details.discount_amount as promo_code_amount',
            'used_promocode_details.promocode_name as promocode_name',
            'user_store_product_booking.store_name',
            'user_store_product_booking.shopper_name',
            'user_store_product_booking.user_name',
            'user_store_product_booking.delivery_people_name',
            'user_store_product_booking.driver_pay_settle_status',
            'user_store_product_booking.store_pay_settle_status')
            ->leftJoin('used_promocode_details', 'used_promocode_details.id', 'user_store_product_booking.promo_code')
            ->where('user_store_product_booking.status', 9)
            ->where('user_store_product_booking.service_cat_id', $service_category->id);
        if ($from_date != Null && $to_date != Null) {
            $from = Date('Y-m-d', strtotime($from_date)) . " 00:00:00";
            $to = Date('Y-m-d', strtotime($to_date)) . " 23:59:59";
            $store_order_list->whereDate('user_store_product_booking.created_at', '>=', $from);
            $store_order_list->whereDate('user_store_product_booking.created_at', '<=', $to);
        }
        if ($driver != Null) {
            $store_order_list->where('user_store_product_booking.delivery_people_id', $driver);
        }
        if ($store != Null) {
            $store_order_list->where('user_store_product_booking.store_id', $store);
        }
        if ($user != Null) {
            $store_order_list->where('user_store_product_booking.user_id', $user);
        }
        if ($payment_type != Null) {
            //cash & card & wallet
            $store_order_list->where('user_store_product_booking.payment_type', $payment_type);
        }
        if ($driver_pay_type != Null) {
            $store_order_list->where('user_store_product_booking.driver_pay_settle_status', $driver_pay_type);
        }
        if ($store_pay_type != Null) {
            $store_order_list->where('user_store_product_booking.store_pay_settle_status', $store_pay_type);
        }
        $store_order_list = $store_order_list->orderBy('user_store_product_booking.id', "desc")->get();

        $total_amount = $store_order_list->sum('total_pay');
        $site_commission = $store_order_list->sum('admin_commission');
        $driver_earning = $store_order_list->sum('delivery_cost');
//        $discount_cost = $store_order_list->sum('discount_cost');

        //driver amount count
        $collect_from_driver = $store_order_list->where('payment_type', 1)->where('taken_away_type', 1)->sum('total_pay');
        $total_pay_to_driver = ($collect_from_driver >= $driver_earning) ? 0 : round(($driver_earning - $collect_from_driver), 2);
        $collect_from_driver = ($collect_from_driver >= $driver_earning) ? round(($collect_from_driver - $driver_earning), 2) : 0;

        //store amount count
        $total_pay_to_shopper = $store_order_list->sum('shopper_earring');
        $store_earning = $store_order_list->sum('store_amount');
        //$total_pay_to_store = $store_order_list->sum('store_amount');
        $collect_from_store = $store_order_list->where('payment_type', 1)->where('taken_away_type', 2)->sum('total_pay');

        $total_pay_to_store = ($collect_from_store >= $store_earning) ? 0 : round(($store_earning - $collect_from_store), 2);
        $collect_from_store = ($collect_from_store >= $store_earning) ? round(($collect_from_store - $store_earning), 2) : 0;

        $view = view('admin.pages.store_services.earning_report.admin_earning_report', compact('slug', 'service_category', 'store_order_list', 'user_list', 'driver_list', 'store_list',
            'from_date', 'to_date', 'driver', 'store', 'user', 'payment_type', 'driver_pay_type', 'store_pay_type',
            'total_amount', 'site_commission', 'driver_earning', 'collect_from_driver', 'total_pay_to_driver',
            'total_pay_to_store', 'store_earning', 'collect_from_store','total_pay_to_shopper'
        ));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function postDriverOrderPaymentSettle(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $slug = $request->get('slug');
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            return response()->json([
                "status" => false,
                "message" => 'Service Category Not Found!'
            ]);
        }
        if ($request->get('order_id') != Null) {
            $order_id_settle = $request->get('order_id');
            foreach ($order_id_settle as $key => $order_id) {
                $order_details = UserProductBooking::where('id', $order_id)->first();
                if ($order_details != Null) {
                    $order_details->driver_pay_settle_status = 1;
                    $order_details->save();
                }
            }
        }
        return response()->json([
            "success" => true,
            "message" => "success"
        ]);
    }

    public function postStoreOrderPaymentSettle(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
//        Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
//        return redirect()->back();
        $slug = $request->get('slug');
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            return response()->json([
                "status" => false,
                "message" => 'Service Category Not Found!'
            ]);
        }
        if ($request->get('order_id') != Null) {
            $order_id_settle = $request->get('order_id');
            foreach ($order_id_settle as $key => $order_id) {
                $order_details = UserProductBooking::where('id', $order_id)->first();
                if ($order_details != Null) {
                    $order_details->store_pay_settle_status = 1;
                    $order_details->save();
                }
            }
        }
        return response()->json([
            "success" => true,
            "message" => "success"
        ]);
    }

    //get Admin Store Manual Assign Driver
    public function getStoreManualAssignDriver(Request $request, $slug, $order_id, $redirect_type = "dispatcher")
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $order_details = UserProductBooking::query()->where('id', $order_id)->first();
        if ($order_details == Null) {
            Session::flash('error', 'Order Details Not Found!');
            return redirect()->back();
        }
        if (!in_array($order_details->status, [2,5,6,7,8])) {
            Session::flash('error', 'Order is not able to driver assign!');
            return redirect()->back();
        }
        $store_details = StoreDetails::query()->where('id', $order_details->store_id)->first();
        if ($store_details == Null) {
            Session::flash('error', 'Store Details Not Found!');
            return redirect()->back();
        }
        $service_setting = ServiceSettings::query()->where('service_cat_id', $service_category->id)->first();
        if ($service_setting != Null) {
            $radius = $service_setting->provider_search_radius;
        } else {
            $radius = 10;
        }
        $driver_id_list = [];
        $general_settings = $request->get("general_settings");
        if ($order_details->delivery_people_id != Null ){
            if (($general_settings != Null) && in_array($order_details->order_delivery_status,[1,2,3,5])) {
                if(($general_settings->max_driver_reassign > $order_details->is_driver_reassign)){
                    $reassigned_driver_list = ReassignedDriverList::query()->where("service_cat_id","=", $order_details->service_cat_id)->where("ride_id","=",$order_details->id)->pluck("driver_details_id")->toArray();
                    $old_driver_id = [$order_details->delivery_people_id];
                    $driver_id_list = array_merge($reassigned_driver_list, $old_driver_id);
                } else {
                    Session::flash('error', 'Order re-assign driver max limit over!');
                    return redirect()->back();
                }
            } else {
                Session::flash('error', 'Order not able to re-assign driver!');
                return redirect()->back();
            }
        }

        // store manual assign so skip transport for multi driver
        $skip_ids = [1,2,4,31,32];
        $not_multi_delivery_provider_id = ProviderUserRunningService::query()->whereIn('service_cat_id',$skip_ids)->get()->pluck('provider_id');

        $lat = $store_details->address_lat;
        $long = $store_details->address_long;
        $expire_date_time = date('Y-m-d H:i:s', strtotime("-60 minutes"));

        $driver_list = Provider::query()->select('providers.id as driver_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'providers.contact_number')
            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
            ->whereIn('service_category.id', [1,2])
            ->where('providers.status', 1)
            //->where('providers.login_device', 1)
            ->where('provider_services.status', 1)
            ->where('provider_services.current_status', 1)
            ->where('transport_driver_details.delivery_boy', 1)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token')
            ->whereNotIn("transport_driver_details.id",$driver_id_list);

        if ($not_multi_delivery_provider_id != Null) {
            $driver_list = $driver_list->whereNotIn('providers.id', $not_multi_delivery_provider_id);
        }

        if($general_settings->is_private_driver_module == 1)
        {
            if($store_details->is_private_driver_notification == 1) {
                // Only Personal Driver Notification
                $driver_list = $driver_list->where('transport_driver_details.store_id', '=', $store_details->id);
            } elseif ($store_details->is_private_driver_notification == 0) {
                // Both Personal & Public Driver Notification
                $driver_list = $driver_list->where(function ($query) use ($store_details) {
                    $query->where('transport_driver_details.store_id', '=', $store_details->id)
                        ->orWhere('transport_driver_details.store_id', '=', 0);
                });
            } else {
                // Only Public Driver Notification
                $driver_list = $driver_list->where('transport_driver_details.store_id', '=', 0);
            }
        }

        $driver_list = $driver_list->get();

        $view = view('admin.pages.store_services.manual_assign_driver.form', compact('slug', 'service_category', 'order_id', 'driver_list', 'redirect_type'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;

    }

    public function postUpdateStoreManualAssignDriver(Request $request, $slug)
    {
        $redirect_type = $request->get("redirect_type");
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        try {
            $order_slug = "approved";
            $service_category = ServiceCategory::query()->where('slug', $slug)->first();
            if ($service_category == Null) {
                Session::flash('error', 'Service category not found!');
                return redirect()->back();
            }
            $store_order = UserProductBooking::query()->where('id', $request->get('order_id'))->first();
            if ($store_order != Null) {

                if (in_array($store_order->status, [2,5,6,7,8])) {
                    //0=pending, 1=accepted, 2=schedule-accepted, 3=arrived, 4=cancelled, 5=running, 6=drop, 7=payment, 8=rating, 9=completed, 10=failed

                    if (in_array($store_order->order_delivery_status, [0,1,2,3,5])) {
                        $user = User::query()->where('id', $store_order->user_id)->first();
                        if ($user == Null) {
                            Session::flash('error', 'user not found!');
                            return redirect()->back();
                        }
                        $old_driver_details_id = $store_order->delivery_people_id;
                        $driver_details = TransportDriverDetails::query()->select('providers.device_token','providers.language',
                            'providers.id as driver_id', 'transport_driver_details.id','transport_driver_details.delivery_boy',
                            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'provider_services.id as provider_service_id', 'providers.login_device')
                            ->join('provider_services', 'provider_services.id', 'transport_driver_details.provider_service_id')
                            ->join('providers', 'providers.id', 'provider_services.provider_id')
                            ->where('providers.id', $request->get('driver_id'))
                            ->where('providers.status', 1)
                            ->where('provider_services.status', 1)
                            ->where('provider_services.current_status', 1)
                            ->first();

                        if ($driver_details != Null) {
                            $driver_id = $driver_details->driver_id;
                            $driver_service_id = $driver_details->provider_service_id;
                            $driver_details_id = $driver_details->id;

                            $driver_service = ProviderServices::query()->where('id', $driver_service_id)->first();
                            if ($driver_service != Null) {

                                $old_driver_service = Null;
                                $old_driver_details = Null;
                                $is_re_assign = 0;
                                $old_driver_id = 0;

                                if ($store_order->delivery_people_id != Null) {

                                    $general_settings = $request->get("general_settings");
                                    if (($general_settings != Null) && in_array($store_order->order_delivery_status,[1,2,3,5])) {
                                        if(($general_settings->max_driver_reassign > $store_order->is_driver_reassign)){
                                            $old_driver_details = TransportDriverDetails::query()->select('providers.device_token', 'providers.id as driver_id',
                                                'transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"),
                                                'provider_services.id as provider_service_id', 'providers.login_device', 'providers.language',
                                                'providers.country_code', 'providers.contact_number')
                                                ->join('provider_services', 'provider_services.id', 'transport_driver_details.provider_service_id')
                                                ->join('providers', 'providers.id', 'provider_services.provider_id')
                                                ->where('transport_driver_details.id', '=', $old_driver_details_id)
                                                ->where('providers.status', '=',1)
                                                ->where('provider_services.status', '=',1)
                                                ->first();
                                            if ($old_driver_details == Null){
                                                Session::flash('error', 'Sorry, Old driver not found!');
                                                return redirect()->back();
                                            }
                                            $old_driver_id = $old_driver_details->driver_id;
                                            $old_driver_service_id = $old_driver_details->provider_service_id;

                                            $old_driver_service = ProviderServices::query()->where('id', $old_driver_service_id)->first();
                                            if ($old_driver_service == Null){
                                                Session::flash('error', 'Old driver service not found!');
                                                return redirect()->back();
                                            }

                                            $reassigned_driver_list = ReassignedDriverList::query()
                                                ->where("service_cat_id","=", $store_order->service_cat_id)
                                                ->where("ride_id","=",$store_order->id)
                                                ->pluck("driver_details_id")
                                                ->toArray();
                                            $get_old_driver_id = [$old_driver_details_id];
                                            $old_driver_id_list = array_merge($reassigned_driver_list, $get_old_driver_id);
                                            if (in_array($driver_details_id,$old_driver_id_list)){
                                                Session::flash('error', 'sorry old driver and new driver both are same! so please change driver to re-assisng driver');
                                                return redirect()->back();
                                            }
                                            $is_re_assign = 1;
                                        } else {
                                            Session::flash('error', 'Order re-assign driver max limit over!');
                                            return redirect()->back();
                                        }
                                    } else {
                                        Session::flash('error', 'Order not able to re-assign driver!');
                                        return redirect()->back();
                                    }
                                }

                                if ($driver_details->delivery_boy != 1) {
                                    $driver_service->current_status = 0;
                                    $driver_service->save();
                                }

                                $store_order->delivery_people_id = $driver_details_id;
                                $store_order->delivery_people_name = $driver_details->driver_name;


                                if ($is_re_assign == 0) {
                                    $store_order->order_delivery_status = 1;
                                }
                                $store_order->save();

                                $check_duplicate = UserProductBookingStatus::query()->where('order_id', $store_order->id)->where('status', 6)->first();
                                if ($check_duplicate == Null) {
                                    $store_delivery_status = new UserProductBookingStatus();
                                    $store_delivery_status->order_id = $store_order->id;
                                    $store_delivery_status->status = 6;
                                    $store_delivery_status->save();
                                }

                                ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $store_order->user_id)->where('service_cat_id', $store_order->service_cat_id)->where('booking_id', $store_order->id)->delete();
                                $provider_running_service = new ProviderUserRunningService();
                                $provider_running_service->provider_id = $driver_id;
                                $provider_running_service->user_id = $store_order->user_id;
                                $provider_running_service->service_cat_id = $store_order->service_cat_id;
                                $provider_running_service->booking_id = $store_order->id;
                                $provider_running_service->save();

                                if ($is_re_assign == 0) {
                                    $this->notificationClass->userAssignDeliveryDriverNotification($store_order->id, $user->device_token, $store_order->order_delivery_status, $user->login_device, $user->language);
                                }
                                $driver_assign_check = $this->notificationClass->driverManualAssignDeliveryRequestNotification($store_order->id, $driver_details->device_token, $driver_details->login_device, $driver_details->language);

                                if ($is_re_assign == 1){
                                    ProviderUserRunningService::query()->where('provider_id', $old_driver_id)->where('user_id', $store_order->user_id)->where('service_cat_id', $store_order->service_cat_id)->where('booking_id', $store_order->id)->delete();
                                    if ($old_driver_service != Null){
                                        $old_driver_service->current_status = 1;
                                        $old_driver_service->save();
                                    }
                                    $store_order->is_driver_reassign = $store_order->is_driver_reassign + 1;
                                    $store_order->save();

                                    if ($old_driver_details != Null){
                                        $reassigned_driver_detail = ReassignedDriverList::query()
                                            ->where("service_cat_id","=", $store_order->service_cat_id)
                                            ->where("ride_id","=",$store_order->id)
                                            ->where("driver_details_id","=", $old_driver_details_id)
                                            ->first();
                                        if ($reassigned_driver_detail == Null){
                                            $reassigned_driver_detail = new ReassignedDriverList();
                                        }
                                        $reassigned_driver_detail->service_cat_id = $store_order->service_cat_id;
                                        $reassigned_driver_detail->ride_id = $store_order->id;
                                        $reassigned_driver_detail->driver_details_id = $old_driver_details->id;
                                        $reassigned_driver_detail->driver_name = $old_driver_details->driver_name;
                                        $reassigned_driver_detail->contact_number = $old_driver_details->country_code."".$old_driver_details->contact_number;
                                        $reassigned_driver_detail->save();

                                        $is_reassign = $store_order->is_driver_reassign - 0;
                                        $old_driver_assign_check = $this->notificationClass->driverManualAssignDeliveryRequestNotification($store_order->id, $old_driver_details->device_token, $old_driver_details->login_device, $old_driver_details->language, $is_reassign);
                                    }
                                }
                                Session::flash('success', 'Driver assign successfully!');
                                if ($redirect_type == "detail"){
                                    return redirect()->route('get:admin:store_order_details', [$slug, $store_order->id]);
                                } else{
                                    $order_slug = "processing";
                                    return redirect()->route('get:admin:store_service_dispatcher', [$slug, $order_slug]);
                                }

                            } else {
                                Session::flash('error', 'driver service not found!');
                                return redirect()->back();
                            }
                        } else {
                            Session::flash('error', 'Sorry, driver not found!');
                            return redirect()->back();
                        }
                    } else {
                        if ($store_order->order_delivery_status == 4) {
                            if ( trim(strtolower($store_order->cancel_by)) == "user"){
                                $cancel_by = "user";
                            } elseif (trim(strtolower($store_order->cancel_by)) == "store"){
                                $cancel_by = "store";
                            } else {
                                $cancel_by = "admin";
                            }
                            Session::flash('error', 'Sorry, order request is cancelled by '.$cancel_by.'!');
                        } elseif (in_array($store_order->order_delivery_status, [6,7,8])) {
                            Session::flash('error', 'Sorry, order is already droped!');
                        } elseif ($store_order->order_delivery_status == 9) {
                            Session::flash('error', 'Sorry, order is already completed!');
                        } elseif ($store_order->order_delivery_status == 10) {
                            Session::flash('error', 'Sorry, order request is failed!');
                        }
                        return redirect()->back();
                    }
                } else {
                    if ($store_order->status == 1) {
                        Session::flash('error', 'Sorry, order status is pending, first order approve then after you can assign driver!');
                    } elseif ($store_order->status == 9) {
                        Session::flash('error', 'Sorry, order is already completed!');
                    } elseif ($store_order->status == 3) {
                        if ( trim(strtolower($store_order->cancel_by)) == "user"){
                            $cancel_by = "user";
                        } elseif (trim(strtolower($store_order->cancel_by)) == "store"){
                            $cancel_by = "store";
                        } else {
                            $cancel_by = "admin";
                        }
                        Session::flash('error', 'Sorry, order is rejected by '.$cancel_by.'!');
                    } elseif ($store_order->status == 4) {
                        if ( trim(strtolower($store_order->cancel_by)) == "user"){
                            $cancel_by = "user";
                        } elseif (trim(strtolower($store_order->cancel_by)) == "store"){
                            $cancel_by = "store";
                        } else {
                            $cancel_by = "admin";
                        }
                        Session::flash('error', 'Sorry, order request is cancelled by '.$cancel_by.'!');
                    } elseif ($store_order->status == 10) {
                        Session::flash('error', 'Sorry, order is already failed!');
                    }
                    return redirect()->back();
                }

            }
            else {
                Session::flash('error', 'Order not found!');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong');
            return redirect()->back();
        }
    }

    //post Admin Store Manual Assign Driver
    public function postUpdateStoreManualAssignDriverBKP(Request $request, $slug)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        try {
            $order_slug = "approved";
            $service_category = ServiceCategory::query()->where('slug', $slug)->first();
            if ($service_category == Null) {
                Session::flash('error', 'Service category not found!');
                return redirect()->back();
            }
            $store_order = UserProductBooking::query()->where('id', $request->get('order_id'))->first();
            if ($store_order != Null) {
                if ($store_order->status == 3) {
                    Session::flash('error', 'Sorry, order is rejected by store!');
                    return redirect()->back();
                } elseif ($store_order->status == 4) {
                    Session::flash('error', 'Sorry, order request is cancelled by user!');
                    return redirect()->back();
                } elseif ($store_order->delivery_people_id != Null) {
                    Session::flash('error', 'Sorry, order is allready assign by driver!');
                    return redirect()->back();
                } else {
                    if ($store_order->order_delivery_status == 1) {
                        Session::flash('error', 'Sorry, order is allready assign!');
                        return redirect()->back();
                    } elseif ($store_order->order_delivery_status == 4) {
                        Session::flash('error', 'Sorry, order request is cancelled by user!');
                        return redirect()->back();
                    } elseif ($store_order->order_delivery_status == 10) {
                        Session::flash('error', 'Sorry, order request is failed!');
                        return redirect()->back();
                    }
                    $driver_details = TransportDriverDetails::query()->select('providers.device_token', 'providers.id as driver_id', 'transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'provider_services.id as provider_service_id', 'providers.login_device', 'providers.langauge')
                        ->join('provider_services', 'provider_services.id', 'transport_driver_details.provider_service_id')
                        ->join('providers', 'providers.id', 'provider_services.provider_id')
                        ->where('providers.id', $request->get('driver_id'))
                        ->where('providers.status', 1)
                        ->where('provider_services.status', 1)
                        ->where('provider_services.current_status', 1)
                        ->first();
                    if ($driver_details != Null) {
                        $driver_id = $driver_details->driver_id;
                        $driver_service_id = $driver_details->provider_service_id;
                        $driver_details_id = $driver_details->id;
                        $user = User::query()->where('id', $store_order->user_id)->first();
                        if ($user == Null) {
                            Session::flash('error', 'user not found!');
                            return redirect()->back();
                        }
                        //offline driver status
                        $driver_service = ProviderServices::query()->where('id', $driver_service_id)->first();
                        if ($driver_service != Null) {
                            $driver_service->current_status = 0;
                            $driver_service->save();

                            //set driver id in ride booking
                            $store_order->delivery_people_id = $driver_details_id;
                            $store_order->delivery_people_name = $driver_details->driver_name;
//                        $delivery->status = 6;
                            $store_order->order_delivery_status = 1;
                            $store_order->save();


                            $check_duplicate = UserProductBookingStatus::query()->where('order_id', $store_order->id)->where('status', 6)->first();
                            if ($check_duplicate == Null) {
                                $store_delivery_status = new UserProductBookingStatus();
                                $store_delivery_status->order_id = $store_order->id;
                                $store_delivery_status->status = 6;
                                $store_delivery_status->save();
                            }

                            ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $store_order->user_id)->where('service_cat_id', $store_order->service_cat_id)->where('booking_id', $store_order->id)->delete();
                            $provider_running_service = new ProviderUserRunningService();
                            $provider_running_service->provider_id = $driver_id;
                            $provider_running_service->user_id = $store_order->user_id;
                            $provider_running_service->service_cat_id = $store_order->service_cat_id;
                            $provider_running_service->booking_id = $store_order->id;
                            $provider_running_service->save();

                            //user assign delivery driver notification
                            $this->notificationClass->userAssignDeliveryDriverNotification($store_order->id, $user->device_token, $store_order->order_delivery_status, $user->login_device, $user->language);
                            $driver_assign_check = $this->notificationClass->driverManualAssignDeliveryRequestNotification($store_order->id, $driver_details->device_token, $driver_details->login_device, $driver_details->language);

                            Session::flash('success', 'Driver assign successfully!');
                            $order_slug = "processing";
                            return redirect()->route('get:admin:store_service_dispatcher', [$slug, $order_slug]);
                        } else {
                            Session::flash('error', 'driver service not found!');
                            return redirect()->back();
                        }
                    } else {
                        Session::flash('error', 'Sorry, driver not found!');
                        return redirect()->back();

                    }
                }
            } else {
                Session::flash('error', 'Order not found!');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong');
            return redirect()->back();
        }
    }

    //get Admin Store Manual Assign Shopper
    public function getStoreManualAssignShopper(Request $request, $slug, $order_id) {}

    //post Admin Store Manual Assign Shopper
    public function postUpdateStoreManualAssignShopper(Request $request, $slug) {}
    //Delivery Charges
    public function getStoreDeliveryCharges($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $delivery_charges = DeliveryCharges::query()->where('service_cat_id',$service_category->id)->get();
        $view = view('admin.pages.store_services.delivery_charges.manage', compact('slug', 'service_category', 'delivery_charges'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //post Save Delivery Charges
    public function getUpdateStoreDeliveryCharges($slug, Request $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $delivery_charges = DeliveryCharges::query()->where('service_cat_id',$service_category->id)->delete();
        for ($i = 0; $i < count($request->get('start_limit')); $i++) {
            if ($request->get('start_limit')[$i] != null && $request->get('close_limit')[$i] != null && $request->get('charges')[$i] != null){
                if ($request->get('close_limit')[$i] == 0){
                    $is_close = 1;
                }else{
                    $is_close = 0;
                }
                $delivery_charges = new DeliveryCharges();
                $delivery_charges->service_cat_id = $service_category->id;
                $delivery_charges->start_limit = $request->get('start_limit')[$i];
                $delivery_charges->close_limit = $request->get('close_limit')[$i];
                $delivery_charges->charges = $request->get('charges')[$i];
                $delivery_charges->save();
                if ($is_close == 1){
                    break;
                }
            } else {
                if ($request->get('start_limit')[$i] != null && $request->get('close_limit')[$i] != null){
                    if ($request->get('close_limit')[$i] == 0){
                        $is_close = 1;
                    }else{
                        $is_close = 0;
                    }
                    $delivery_charges = new DeliveryCharges();
                    $delivery_charges->service_cat_id = $service_category->id;
                    $delivery_charges->start_limit = $request->get('start_limit')[$i];
                    $delivery_charges->close_limit = $request->get('close_limit')[$i+1];
                    $delivery_charges->charges = $request->get('charges')[$i+1];
                    $delivery_charges->save();
                    $i++;
                    if ($is_close == 1){
                        break;
                    }
                }
            }
        }

        return redirect()->route('get:admin:store_delivery_charges', compact('slug'));

        //$service_category = ServiceCategory::where('slug', $slug)->first();
        //if ($service_category == Null) {
        //    Session::flash('error', 'Service category not found!');
        //    return redirect()->back();
        //}
        //for ($i = 0; $i < count($request->get('charges')); $i++) {
        //    try {
        //        $delivery_charges = DeliveryCharges::where('id', $i + 1)->first();
        //        //$update_store_timing = StoreTimings::where('store_id', $store_details->id)->where('day', $days[$i])->first();
        //        if ($delivery_charges != Null) {
        //            $delivery_charges->charges = $request->get('charges')[$i];
        //            $delivery_charges->save();
        //        }
        //    } catch (\Exception $e) {
        //    }
        //}
        //return redirect()->route('get:admin:store_delivery_charges', compact('slug'));
    }

    public function getAdminDeleteStoreDeliveryCharges(Request $request)
    {
        if($this->is_restricted == 1) {
            return response()->json([
                "status" => false,
                "message" => "Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script."
            ]);
        }
        $validator = Validator::make($request->all(), [
            "delivery_charge_id" => "required|numeric",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first()
            ]);
        }
        $delivery_charge_id = $request->get("delivery_charge_id");
        $old_delivery_charge_details = DeliveryCharges::query()->where('id','=',$delivery_charge_id)->first();
        if ($old_delivery_charge_details == Null) {
            return response()->json([
                "status" => false,
                "message" => 'Delivery charge details Not Found!'
            ]);
        }
        $delivery_charge_list = DeliveryCharges::query()
            ->where("id", '!=',$old_delivery_charge_details->id)
            ->where("service_cat_id", $old_delivery_charge_details->service_cat_id)
            ->where("start_limit", ">=", $old_delivery_charge_details->close_limit)
            ->first();
        if ($delivery_charge_list != Null) {
            try {
                $delivery_charge_list->start_limit = $old_delivery_charge_details->start_limit;
                $delivery_charge_list->save();
                $old_delivery_charge_details->delete();
            } catch (\Exception $e) {
                return response()->json([
                    "status" => false,
                    "message" => 'Delivery charge details not deleted!'
                ]);
            }
        }
        return response()->json([
            "success" => true,
            "message" => "success"
        ]);
    }

    public function getStoreServiceSliderList(Request $request ,$slug){
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $service_slider_list = ServiceSliderBanner::query()->select(
            'store_details.name as store_name',
            'service_slider_banner.banner_image',
            'service_slider_banner.id',
            'service_slider_banner.status'
        )
            ->join('store_details','store_details.id','service_slider_banner.store_id')
            ->where('service_cat_id','=',$service_category->id)
            ->get();

        $view = view('admin.pages.store_services.service_slider.service_slider_list', compact('service_slider_list','slug'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponse($view);
        }
        return $view;
    }

    public function getAddStoreServiceSlider(Request $request ,$slug){

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $service_slider_store_id = ServiceSliderBanner::query()
            ->where('service_cat_id','=',$service_category->id)
            ->pluck('store_id');

        $store_list = StoreDetails::query()->select(
            'store_details.id as store_id',
            'store_details.name as store_name'
        )
            ->join('provider_services','provider_services.id','store_details.provider_service_id')
            ->where('provider_services.service_cat_id','=',$service_category->id)
            ->where('provider_services.status','=',1)
            ->whereNotIn('store_details.id',$service_slider_store_id)
            ->get();

        $view = view('admin.pages.store_services.service_slider.service_slider', compact('store_list','slug'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponse($view);
        }
        return $view;
    }

    public function postUpdateStoreServiceSlider(Request $request ,$slug){
//        dd($request->all());
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $banner_id = $request->get('banner_id');

        if($banner_id != Null){
            $banner = ServiceSliderBanner::query()->where('id',$banner_id)->where('service_cat_id','=',$service_category->id)->first();
        } else{
            $banner = new ServiceSliderBanner();
            $banner->status = 1;
        }

        $banner->service_cat_id = $service_category->id;
        $banner->store_id = $request->get('store_id');
        $banner->save();

        if($request->hasFile('image') != Null){
            if (\File::exists(public_path('/assets/images/service-slider-banner/' . $banner->banner_image))) {
                \File::delete(public_path('/assets/images/service-slider-banner/' . $banner->banner_image));
            }
            $destinationPath = public_path('/assets/images/service-slider-banner/');
            $file = $request->file('image');
            $img = Image::make($file->getRealPath());
            $img->orientate();
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $img->resize(600, 240, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . $file_new);
            $banner->banner_image = $file_new;

            /*$file = $request->file('image');
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $file_new);
            $banner->banner_image = $file_new;*/
        }
        $banner->save();

        Session::flash('success', 'Slider added successfully!');
        return redirect()->route('get:admin:store_service_slider_list',$slug);
    }

    public function getEditStoreServiceSlider(Request $request ,$slug ,$id){
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $service_slider_banner = ServiceSliderBanner::query()
            ->where('id','=',$id)
            ->where('service_cat_id','=',$service_category->id)
            ->first();
        if ($service_slider_banner == Null) {
            Session::flash('error', 'Service Slider not found!');
            return redirect()->back();
        }

        $store_list = StoreDetails::query()->select(
            'store_details.id as store_id',
            'store_details.name as store_name'
        )
            ->join('provider_services','provider_services.id','store_details.provider_service_id')
            ->where('provider_services.service_cat_id','=',$service_category->id)
            ->where('provider_services.status','=',1)
//            ->where('store_details.id','!=',$service_slider_banner->store_id)
            ->get();

        $view = view('admin.pages.store_services.service_slider.service_slider', compact('store_list','service_slider_banner','slug'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponse($view);
        }
        return $view;
    }

    public function postUpdateStoreServiceSliderStatus(Request $request){
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
                'message' => 'Service Slider not found'
            ]);
        }
        $service_slider = ServiceSliderBanner::query()->where('id', $id)->first();
        if ($service_slider == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Home Slider not found'
            ]);
        }
        if ($service_slider->status == 1) {
            $service_slider->status = 0;
            $service_slider->save();
        } else {
            $service_slider->status = 1;
            $service_slider->save();
        }
        return response()->json([
            'success' => true,
            'status' => $service_slider->status
        ]);
    }

    public function postDeleteStoreServiceSlider(Request $request){
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
                'message' => 'Service Slider not found'
            ]);
        }
        $service_slider = ServiceSliderBanner::query()->where('id', $id)->first();
        if ($service_slider == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Service Slider not found'
            ]);
        }
        if (\File::exists(public_path('/assets/images/service-slider-banner/' . $service_slider->banner_image))) {
            \File::delete(public_path('/assets/images/service-slider-banner/' . $service_slider->banner_image));
        }
        $service_slider->delete();
        return response()->json([
            'success' => true
        ]);
    }


    //code for add store personal driver lists
    //store driver lists
    public function getStoreDriverList(Request $request, $slug, $store_id = 0){
        $status = 1;
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Category provider list not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::query()->where('id', $store_id)->first();
        if ($store_details == Null) {
            return redirect()->back();
        }
        if (in_array($service_category->id, $this->store_private_service_ids)) {
            $provider_lists = TransportDriverDetails::query()
                ->select(
                    'service_category.slug as service_cat_slug',
                    'service_category.id as service_cat_id',
                    'providers.id as provider_id',
                    'providers.first_name as driver_name', 'providers.email', 'providers.country_code','providers.contact_number',
                    'provider_services.status', 'provider_services.id as provider_service_id',
                    'transport_driver_details.id as driver_details_id', 'transport_driver_details.vehicle_company',
                    'transport_driver_details.plat_no', 'transport_driver_details.model_name', 'transport_driver_details.model_year',
                    'transport_driver_details.vehicle_color', 'transport_driver_details.rating', 'transport_driver_details.total_request',
                    'transport_driver_details.total_completed', 'transport_driver_details.total_cancelled',
                    'transport_vehicle_type.name as vehicle_type_name', 'transport_vehicle_type.icon_name as type_icon',
                    'transport_driver_details.driver_service',
                    'transport_driver_details.delivery_boy',
                    'transport_driver_details.courier_boy',
                    'providers.app_version as provider_app_version'
                )
                ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
//            ->where('provider_services.status', '=', $status)
                ->where('transport_driver_details.store_id', '=', $store_id)
                ->orderBy('transport_driver_details.id',  'desc')
                ->get();

            $view = view('admin.pages.store_services.delivery_people.manage_store_driver', compact('slug', 'service_category', 'status', 'provider_lists', 'store_details', 'store_id'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        } else {
            Session::flash('error', 'Store private driver can not work on this service!');
            return redirect()->back();
        }
    }

    public function getAddStoreDriver(Request $request, $slug, $store_id)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::where('id', $store_id)->first();
        if ($store_details == Null) {
            Session::flash('error', 'Store details not found!');
            return redirect()->back();
        }
        if (in_array($service_category->id, $this->store_private_service_ids)) {

            $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $this->bike_ride)->where('status', '=', 1)->get();

            $view = view('admin.pages.store_services.delivery_people.form_store_driver', compact('slug', 'store_id', 'service_category', 'vehicle_types'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        } else {
            Session::flash('error', 'Store private driver can not be work this service!');
            return redirect()->back();
        }
    }

    //get Edit Transport Vehicle Type
    public function getEditStoreDriver(Request $request, $slug, $store_id, $driver_details_id)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::where('id', $store_id)->first();
        if ($store_details == Null) {
            Session::flash('error', 'Store details not found!');
            return redirect()->back();
        }
        if (in_array($service_category->id, $this->store_private_service_ids)) {
            $driver_detials = TransportDriverDetails::query()->select('service_category.slug as service_cat_slug',
                'service_category.id as service_cat_id',
                'providers.id as provider_id', 'providers.first_name as name', 'providers.email', 'providers.country_code','providers.contact_number',
                'providers.avatar', 'providers.service_radius', 'providers.gender',
                'provider_services.status', 'provider_services.id as provider_service_id',
                'transport_driver_details.id as driver_details_id', 'transport_driver_details.rating',
                'transport_driver_details.vehicle_type_id', 'transport_driver_details.vehicle_company',
                'transport_driver_details.plat_no', 'transport_driver_details.model_year', 'transport_driver_details.model_name',
                'transport_driver_details.vehicle_color', 'transport_driver_details.driver_service', 'transport_driver_details.delivery_boy',
                'transport_driver_details.courier_boy', 'transport_driver_details.no_of_seat', 'transport_driver_details.support_weight'
            )
                ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('transport_driver_details.id', $driver_details_id)
                ->first();


            if ($driver_detials != Null) {
                $vehicle_types = TransportVehicleType::where('service_cat_id', $this->bike_ride)->where('status', '=', 1)->get();
                $view = view('admin.pages.store_services.delivery_people.form_store_driver', compact('slug', 'store_id', 'service_category', 'vehicle_types', 'driver_detials'));
                if ($request->ajax()) {
                    $view = $view->renderSections();
                    return $this->adminClass->renderingResponce($view);
                }
                return $view;
            } else {
                Session::flash('error', 'Driver Not Found!');
                return redirect()->back();
            }
        } else {
            Session::flash('error', 'Store private driver can not be work this service!');
            return redirect()->back();
        }

    }

    //Update or Store private Driver
    public function postUpdateStoreDriver(ProviderStoreRequest $request, $slug, $store_id, $is_store = 0)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::where('id', $store_id)->first();
        if ($store_details == Null) {
            Session::flash('error', 'Store details not found!');
            return redirect()->back();
        }
        return $this->updateStoreDriver($request, $slug, $store_id, 0);
    }

    public function updateStoreDriver($request, $slug, $store_id, $is_store = 0)
    {
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::where('id', $store_id)->first();
        if ($store_details == Null) {
            Session::flash('error', 'Store details not found!');
            return redirect()->back();
        }

        if (in_array($service_category->id, $this->store_private_service_ids)) {

            $provider_details = $this->adminClass->AddServiceProvider($request);
            if ($provider_details == Null) {
                Session::flash('error', 'Something want to wrong!');
                return redirect()->back();
            }
            $provider_service_id = $request->get('provider_service_id');
            $provider_services = ProviderServices::where('id', $provider_service_id)->first();
            if ($provider_services == Null) {
                $provider_services = new ProviderServices();
                $provider_services->provider_id = $provider_details->id;
                $provider_services->service_cat_id = $this->bike_ride;
                $provider_services->current_status = 1;
                $provider_services->status = 1;
                $provider_services->save();
            } else {
                $provider_services->provider_id = $provider_details->id;
                $provider_services->save();
            }

            $provider_details = Provider::query()->where('id', $provider_details->id)->first();
            $provider_details->provider_type = 2;
            $provider_details->save();

            $driver_details_id = $request->get('driver_details_id');
            $transport_driver = TransportDriverDetails::where('id', $driver_details_id)->first();
            if ($transport_driver == Null) {
                $transport_driver = new TransportDriverDetails();
                $transport_driver->provider_service_id = $provider_services->id;
                $transport_driver->vehicle_type_id = $request->get('vehicle_type_id');
                $transport_driver->total_request = 0;
                $transport_driver->total_completed = 0;
                $transport_driver->total_cancelled = 0;
                $transport_driver->rating = 0.00;
                $transport_driver->availability_ride_status = 1;
                $transport_driver->save();
            } else {
                $transport_driver->provider_service_id = $provider_services->id;
                $transport_driver->vehicle_type_id = $request->get('vehicle_type_id');
                $transport_driver->save();
            }
            $transport_driver->store_id = $store_details->id;
            $transport_driver->vehicle_company = $request->get('vehicle_company');
            $transport_driver->plat_no = $request->get('plat_no');
            $transport_driver->model_year = $request->get('model_year');
            $transport_driver->model_name = $request->get('model_name');
            $transport_driver->vehicle_color = $request->get('vehicle_color');

            $transport_driver->driver_service = 0;
            $transport_driver->delivery_boy = 1;
            $transport_driver->courier_boy = 0;
            $transport_driver->no_of_seat = 1;
            $transport_driver->save();
            $id = $request->get('id');
            if ($id != Null) {
                $msg = 'Store driver update successfully!';
            } else {
                $msg = 'Store driver add successfully!';
            }
            Session::flash('success', $msg);
            if ($is_store == 0) {
                return redirect()->route('get:admin:store_driver_list', [$slug, $store_id]);
            } else {
                return redirect()->route('get:store-admin:store_driver_list');
            }
        } else {
            Session::flash('error', 'Store private driver can not be work this service!');
            return redirect()->back();
        }
    }

    /* New Place Order Functions */
    public function getAdminAddUser(Request $request, $slug)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $view = view('admin.pages.store_services.order_book.store_user_form', compact('slug', 'service_category'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getAdminStoreDeliverySearchCustomerCreateOrder(Request $request)
    {
        $search = $request->get('contact_number');
        $country_code = $request->get('country_code');
        $slug = $request->get('slug');
        if ($search == Null && $slug == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Customers not found'
            ]);
        }
        $customers_lists = User::query()->select('users.id', 'users.first_name', 'users.contact_number', 'users.email', 'users.gender', 'users.country_code')
            ->where('users.contact_number', 'LIKE', "$search%")
            ->where('users.country_code', 'LIKE', "$country_code%")
            ->where('users.status', '=', 1)
            ->get();

        if ($customers_lists->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Customers not found'
            ]);
        }
        return response()->json([
            'success' => true,
            'customers' => $customers_lists,
        ]);
    }

    public function postAdminStoreUser(PlaceOrderCreateCustomerRequest $request, $slug)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $service_category = ServiceCategory::query()->where('slug', '=', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $user_id = (int)$request->get('id');
        if ($user_id == 0) {
            $user_details = User::query()->where('id', $user_id)->first();
            if ($user_details == Null) {
                $user_details = new User();
                $user_details->first_name = $request->get('first_name');
                //$user_details->last_name = $request->get('last_name');
                $user_details->email = $request->get('email');
                $user_details->contact_number = $request->get('contact_number');
                $user_details->country_code = $request->get('country_code');
                $user_details->login_type = "email";
                $user_details->gender = $request->get('gender');
                $user_details->password = Hash::make($request->get('password'));
                $user_details->status = 1;
                $user_details->verified_at = date('Y-m-d H:i:s');
                $user_details->save();

                $user_details->generateAccessToken($user_details->id);
                $user_details->InviteCode($user_details->id, $user_details->first_name);
            }
        } else {
            $user_details = User::query()->where('id', $user_id)->first();
            if ($user_details == Null) {
                Session::flash('error', 'User detail not found!');
                return redirect()->back();
            }
        }
        return redirect()->route('get:admin:place_order', [ "slug" => $slug, "user_id" => $user_details->id ]);
    }

    public function getAdminPlaceOrder(Request $request, $slug, $user_id, $user_address_id = 0) {
        $service_category = ServiceCategory::query()->where('slug', '=', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->route('get:admin:add_users', $slug);
        }
        $user_details = User::query()->where('id', $user_id)->first();
        if ($user_details == Null) {
            Session::flash('error', 'User detail not found!');
            return redirect()->route('get:admin:add_users', $slug);
        }
        $address_list = UserAddress::query()->where('user_id','=', $user_details->id)->where('status','=', 1)->get();
        $view = view('admin.pages.store_services.order_book.form', compact('slug', 'service_category', 'user_details', 'address_list', 'user_address_id'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getAdminAddUserAddress(Request $request) {
        $user_id = $request->get('user_id');
        $search_address = $request->get('search_address');
        $flat_no = $request->get('flat_no');
        $landmark = $request->get('landmark');
        $type = $request->get('type');
        $address_lat = $request->get('address_lat');
        $address_long = $request->get('address_long');

        $edit_address_id = $request->get('edit_address_id') - 0;

        if ($user_id == Null || $search_address == Null || $flat_no == Null || $landmark == Null || $type == Null || $address_lat == Null || $address_long == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $user_details = User::query()->where('id', $user_id)->first();
        if ($user_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'User details not found'
            ]);
        }

        $count_active_address = UserAddress::query()->where('user_id', $user_id)->where('status', 1)->count();
        $count_inactive_address = UserAddress::query()->where('user_id', $user_id)->where('status', 0)->count();
        $address_data = UserAddress::query()->where('id','=',$edit_address_id)
            ->where('user_id','=',$user_id)
            ->where('status','=',1)
            ->first();

        if ($address_data != Null) {
            $address_data->user_id = $user_id - 0;
            $address_data->address_type = "other";
            $address_data->address = $search_address;
            $address_data->lat_long = $address_lat . ',' . $address_long;
            $address_data->flat_no = $flat_no;
            $address_data->landmark = $landmark;
            $address_data->save();
        }
        else {
            if (($count_active_address + $count_inactive_address) < 5) {
                $address = new UserAddress();
                $address->user_id = $user_id - 0;
                $address->address_type = "other";
                $address->address = $search_address;
                $address->lat_long = $address_lat . ',' . $address_long;
                $address->flat_no = $flat_no;
                $address->landmark = $landmark;
                $address->save();
            }
            else {
                if ($count_active_address == 5) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User only add five address, you select any one of given option!'
                    ]);
                } elseif ($count_inactive_address > 0) {
                    $address = UserAddress::query()->where('user_id', $user_id)->where('status', 0)->first();
                    $address->user_id = $user_id - 0;
                    $address->address_type = "other";
                    $address->address = $search_address;
                    $address->lat_long = $address_lat . ',' . $address_long;
                    $address->flat_no = $flat_no;
                    $address->landmark = $landmark;
                    $address->status = 1;
                    $address->save();
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Something went to wrong!'
                    ]);
                }
            }
        }

        $address_list = UserAddress::query()->where("user_id", $user_details->id)->where('status', 1)->get();
        $address_count = 0;
        $data = '<option disabled selected value="">Choose Address</option>';
        if ($address_list != Null) {
            foreach ($address_list as $key => $get_address) {
                $address_count = $address_count + 1;
                $data .= '<option value="' . $get_address->id . '">' . $get_address->address . '</option>';
            }
            return response()->json([
                'success' => true,
                'data' => $data,
                'address_count' => $address_count
            ]);
        } else {
            return response()->json([
                'success' => false,
                'data' => $data,
                'address_count' => $address_count
            ]);
        }
    }

    public function getAdminEditUserAddress(Request $request) {
        $edit_address_id = $request->get('edit_address_id') - 0;

        $address_data = UserAddress::query()->where('id','=', $edit_address_id)->where('status','=', 1)->first();

        if ($address_data == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'address_data' => $address_data
        ]);
    }

    public function getAdminDeleteUserAddress(Request $request) {
        $edit_address_id = $request->get('edit_address_id') - 0;
        $user_id = $request->get('user_id') - 0;

        try {
            $address = UserAddress::query()->where('id', $edit_address_id)->where('user_id', $user_id)->first();
            $address->status = 0;
            $address->save();

            $address_list = UserAddress::query()->where("user_id", $user_id)->where('status', 1)->get();
            $address_count = 0;
            $data = '<option disabled selected value="">Choose Address</option>';
            if ($address_list != Null) {
                foreach ($address_list as $key => $get_address) {
                    $address_count = $address_count + 1;
                    $data .= '<option value="' . $get_address->id . '">' . $get_address->address . '</option>';
                }
                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'address_count' => $address_count
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => $data,
                    'address_count' => $address_count
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
    }

    public function getAdminCreateOrderStoreList(Request $request) {
        //$city_id = $request->get('city_id');
        $user_address_id = $request->get('user_address_id');
        $ignore_store_ids = $request->get('ignore_store_ids');
        $selected_option = $request->get('selected_option');
        $slug = $request->get('slug');
        if ($user_address_id == Null && $slug == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Customers not found'
            ]);
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Service category not found!'
            ]);
        }

        $address_list = UserAddress::query()->where("id", $user_address_id)->where('status', 1)->first();
        if ($address_list == Null) {
            return response()->json([
                'success' => false,
                'message' => 'user address not found!'
            ]);
        }

        $address_lat_long = array_map('trim', explode(",", $address_list->lat_long));
        if (count($address_lat_long) != 2) {
            return response()->json([
                'success' => false,
                'message' => 'user address not found!'
            ]);
        }
        $address_lat = trim($address_lat_long[0]);
        $address_long = trim($address_lat_long[1]);

        $store_list = StoreDetails::query()->select('store_details.id',
            'store_details.name', 'store_details.image',
            'store_details.offer_min_amount', 'store_details.offer_amount',
            'store_details.offer_type', 'store_details.order_min_amount',
            'store_details.rating', 'store_details.eta_delivery_time',
            'provider_services.current_status'
            , 'providers.email'
            , 'providers.country_code'
            , 'providers.contact_number'
            , 'providers.service_radius'
            , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
            +COS( ' . $address_lat . ' * pi()/180)
            * COS(store_details.address_lat * pi()/180)
            * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
        )
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('provider_services.status', 1)
            ->where('provider_services.service_cat_id', $service_category->id)
            ->havingRaw('providers.service_radius > distance');

        if ($ignore_store_ids != Null) {
            if (false !== $key = array_search($selected_option, $ignore_store_ids)) {
                unset($ignore_store_ids[$key]);
            }
            $store_list->whereNotIn('store_details.id', $ignore_store_ids);
        }
        $store_list = $store_list
            //->whereNull('providers.deleted_at')
            //->orderByRaw("distance")
            ->get();

        if ($store_list->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ]);
        }
        $store_data = [];
        foreach ($store_list as $key => $store_detail) {
            $product_cat_name = array_unique(StoreProductDetails::query()
                ->select('store_product_category.name')
                ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                ->where('store_product_details.store_id', $store_detail->id)
                ->where('store_product_category.status', 1)
                ->get()->pluck('name')->toArray());
            $product_cat_count = StoreProductDetails::query()->where('store_id', $store_detail->id)->where('status', 1)->count();
            $count_product_cat_name = count($product_cat_name);
            if ($count_product_cat_name > 3) {
                for ($n = 3; $n <= $count_product_cat_name; $n++) {
                    unset($product_cat_name[$n]);
                }
            }
            $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')->where('store_id', $store_detail->id)->where('day', 'all')->first();
            if ($store_time == Null) {
                $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')->where('store_id', $store_detail->id)->where('day', substr(date('l'), 0, 3))->first();
            }
            $time = Date('H:i:s');
            if ($count_product_cat_name > 0 && $product_cat_count > 0) {
                try {
                    if ($store_time != Null) {
                        if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time) {
                            $store_time_status = 1; //"open"
                        } else {
                            $store_time_status = 0; //"close"
                        }
                    } else {
                        $store_time_status = 0; //"close"
                    }
                    $store_today_time = date("H:i A", strtotime($store_time->store_open_time)) . " to " . date("H:i A", strtotime($store_time->store_close_time));// today time
                } catch (\Exception $e) {
                    $store_time_status = 0;
                    $store_today_time = "----";
                }
                $store_data[] = [
                    "id" => $store_detail->id,
                    "name" => $store_detail->name,
                    "email" => $store_detail->email,
                    "contact_number" => $store_detail->country_code . " " . $store_detail->contact_number,
                    "store_open_hour" => ($store_time_status == 1) ? "Open" : "Close",
                    "store_today_time" => $store_today_time,
                    //"store_today_time" => $store_today_time. "12:01 AM to 11:59 PM",
                ];
            }
        }
        return response()->json([
            'success' => true,
            'store_list' => $store_data,
        ]);
    }

    public function getAdminCreateOrderStoreProductList(Request $request) {
        $slug = $request->get('slug');
        $user_id = $request->get('user_id');
        $store_id = $request->get('store_id');
        $view_more = $request->get('view_more');
        $search_product = $request->get('search_product');
        if ($slug == Null && $store_id == Null) {
            return response()->json([
                'success' => false,
                'message' => 'store field required'
            ]);
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Service category not found!'
            ]);
        }
        $currency = 1;
        $product_limit = 10;
        $store_details = StoreDetails::query()->select('store_details.id',
            //'store_details.taken_away_type',
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
            'provider_services.service_cat_id'
            //, 'providers.city_id'
            )
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('store_details.id', $store_id)->where('provider_services.status', 1)->first();
        if ($store_details != Null) {
            $user_order_cart = UserOrderCart::query()->where('order_add_by', 0)->where('user_id', $user_id)->get()->keyBy("product_id")->toArray();
            $is_search_product = 0;
            if ($search_product != Null) {
                $is_search_product = 1;
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
                ->where('store_product_details.store_id', $store_id);
            if ($is_search_product == 1) {
                $get_products->where('store_product_details.name', "LIKE", "%$search_product%");
            }
            $get_products = $get_products->where('store_product_details.status', 1)->where('store_product_category.status', 1)->get();
            $store_product_categories = array_unique(Arr::pluck($get_products, 'category_name', 'category_id'));
            $get_products = $get_products->groupBy('category_id');
            $category_wise_product_list = [];
            if ($is_search_product == 1) {
                $product_list = '';
            }
            $key_count = 0;
            foreach ($get_products as $key => $products) {
                $get_count_product = count($products);
                if ($is_search_product == 0) {
                    $product_list = '';
                }
                foreach ($products as $Pkey => $product) {
                    $is_iwill_choose = 0;
                    $is_repeat = 0;
                    $cart_id = 0;
                    $user_order_cart_details = Null;
                    if ($user_order_cart != Null && isset($user_order_cart[$product->id])) {
                        $user_order_cart_details = $user_order_cart[$product->id];
                        $cart_id = $user_order_cart_details['id'];
                        $is_repeat = 1;
                    }
                    if ($is_search_product == 0) {
                        if ($view_more == 0) {
                            if ($Pkey >= $product_limit) {
                                break;
                            }
                        } else {
                            $product_limit = $get_count_product;
                        }
                    } else {
                        $product_limit = $get_count_product;
                    }
                    //$product_amount = $product->amount;
                    $product_size = StoreProductSize::query()->select('id', 'name', 'amount')->where('product_id', $product->id)->orderBy('amount', 'asc')->get();
                    foreach ($product_size as $size_key => $size) {
                        if (($user_order_cart_details != Null)) {
                            //return $user_order_cart_details['size_id'];
                            if ($size->id == $user_order_cart_details['size_id']) {
                                $is_iwill_choose = 1;
                            }
                        }
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
                            //$offer_calculation = round(($size->amount - $discount_price), 2);
                            $amount_price = $discount_price;
                            $discount_price = $size->amount;
                        } else {
                            $amount_price = $size->amount;
                            //$offer_calculation = 0;
                        }
                    }

                    if ($product->discount_type == 1) {
                        $price_discount = round((($product->amount) - ($product->discount_amount)), 2);
                        //$price_discount = $price_discount - 0;
                    } elseif ($product->discount_type == 2) {
                        $price_discount = round((($product->amount) - (($product->amount) * ($product->discount_amount)) / (100)), 2);
                        //$price_discount = $price_discount - 0;
                    } else {
                        $price_discount = 0;
                    }
                    if ($price_discount != 0) {
                        $offer_calculation = round(($product->amount - $price_discount), 2);
                        $product_price = $price_discount;
                        $price_discount = $product->amount;
                    } else {
                        $product_price = $product->amount;
                        $offer_calculation = 0;
                    }
                    $is_show_more = "";
                    if ($view_more == 0) {
                        $is_show_more = $get_count_product > $product_limit ? '<tr class="remove_view_more_' . $key . '"><td></td>
                             <td class="text-right">
                                  <p><a class="view_more" store_id="' . $store_id . '" view_more="' . $key . '">View More</a></p>
                              </td>
                        </tr>' : "";
                    }

                    $product_options_count = StoreProductOptions::query()->select('store_product_options.id')
                        ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_options.category_id')
                        ->where('store_product_options.product_id', $product->id);
                    if (($user_order_cart_details != Null) && $user_order_cart_details['option_id'] != Null) {
                        $option_array = explode(",", $user_order_cart_details['option_id']);
                        $product_options_count = $product_options_count->whereIn("store_product_options.id", $option_array)->first();
                        if ($product_options_count != Null) {
                            $is_iwill_choose = 1;
                        }
                    }
                    $product_options_count = $product_options_count->count();

                    $product_toppings_count = StoreProductToppings::query()->select('store_product_toppings.id')
                        ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')
                        ->where('store_product_toppings.product_id', $product->id);
                    if (($user_order_cart_details != Null) && $user_order_cart_details['topping_id'] != Null) {
                        $option_array = explode(",", $user_order_cart_details['topping_id']);
                        $product_toppings_count = $product_toppings_count->whereIn("store_product_toppings.id", $option_array)->first();
                        if ($product_toppings_count != Null) {
                            $is_iwill_choose = 1;
                        }
                    }
                    $product_toppings_count = $product_toppings_count->count();

                    $is_toppings = 0;
                    $is_toppings_str = "";
                    $product_size_count = StoreProductSize::query()->select('id', 'name', 'amount')->where('product_id', $product->id)->count();
                    //$product_options_count = StoreProductOptions::query()->select('store_product_options.id')->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_options.category_id')->where('store_product_options.product_id', $product->id)->count();
                    //$product_toppings_count = StoreProductToppings::query()->select('store_product_toppings.id')->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')->where('store_product_toppings.product_id', $product->id)->count();
                    if ($product_size_count > 0 || $product_options_count > 0 || $product_toppings_count > 0) {
                        $is_toppings = 1;
                        $is_toppings_str = 'data-toggle="modal" data-target="#large-Modal"';
                    }
                    //if ($is_iwill_choose > 0){
                    //    $is_toppings_str = 'data-toggle="modal" data-target="#i_will_choose_modal"';
                    //}
                    $offer_string = ($offer_calculation > 0) ? '<span class="list_product_amount list_product_discount"><strike><span class="currency"> ' . $offer_calculation . '</span></strike></span>' : '';
                    $product_list .= '<tr>
                                           <td>
                                               <div class="list_product_name"><b>' . $product->name . '</b></div>
                                               <div class="list_product_amount_sec">
                                                   ' . $offer_string . '
                                                   <span class="currency list_product_amount"> ' . $product_price . '</span>
                                               </div>
                                           </td>
                                           <td class="text-right">
                                                <div type="button" cart_id="' . $cart_id . '"  is_repeat="' . $is_repeat . '" is_toppings="' . $is_toppings . '" is_iwill_choose="' . $is_iwill_choose . '" store_id="' . $store_id . '" product_id="' . $product->id . '" product_amount="' . $product_price . '" class="btn btn-outline-secondary btn-sm waves-effect waves-light add_to_cart add_to_cart_' . $product->id . '" ' . $is_toppings_str . '>Add Cart</div>
                                            </td>
                                      </tr>' . $is_show_more;
                }

                if ($view_more == 0 && $is_search_product == 0) {
                    $category_wise_product_list[] = '<div class="accordion-panel">
                        <div class="accordion-heading" accordion_key="accordion_heading_' . $key . '" role="tab" id="accordion_heading_' . $key . '">
                            <h3 class="card-title accordion-title">
                                <a class="accordion-msg waves-effect waves-dark" data-toggle="collapse" data-parent="#accordion" href="#accordion_collapse_' . $key . '" aria-expanded="false" aria-controls="accordion_collapse_' . $key . '">
                                   ' . $store_product_categories[$key] . '
                                </a>
                            </h3>
                        </div>
                        <div id="accordion_collapse_' . $key . '" class="panel-collapse collapse" role="tabpanel" aria-labelledby="accordion_heading_' . $key . '">
                            <div class="accordion-content accordion-desc">
                                <div class="table-responsive">
                                    <table class="table m-b-0 without-header cart-table">
                                        <tbody class="accordion_table_body_' . $key . '">
                                        ' . $product_list . '
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>';
                } else {
                    $category_wise_product_list = $product_list;
                }
            }
            return response()->json([
                'success' => true,
                //'store_id' => $store_details->id,
                'check_search_product' => $is_search_product,
                'view_more' => $view_more - 0,
                'category_wise_product_list' => $category_wise_product_list
            ]);
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'Store Details Not Found!'
            ]);
        }
    }

    public function getAdminCreateOrderStoreProductDetails(Request $request) {
        $slug = $request->get('slug');
        $user_id = $request->get('user_id');
        $store_id = $request->get('store_id');
        $product_id = $request->get('product_id');

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Service category not found!'
            ]);
        }

        $store_details = StoreDetails::query()->select('store_details.id',
            //'store_details.taken_away_type',
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
            'provider_services.service_cat_id'
        //, 'providers.city_id'
        )
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('store_details.id', $store_id)->where('provider_services.status', 1)->first();
        if ($store_details != Null) {
            $get_product_details = StoreProductDetails::query()->select('store_product_details.id',
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
                ->where('store_product_details.id', $product_id)
                ->where('store_product_details.store_id', $store_id)
                ->where('store_product_details.status', 1)
                ->where('store_product_category.status', 1)
                ->first();
            if ($get_product_details == Null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found!'
                ]);
            }

            $total_product_price = 0;
            $base_amount_price = $get_product_details->amount;
            $product_size_arr = "";
            $product_size = StoreProductSize::query()->select('id', 'name', 'amount')->where('product_id', $get_product_details->id)->orderBy('amount', 'asc')->get();
            foreach ($product_size as $size_key => $size) {
                if ($get_product_details->discount_type == 1) {
                    $discount_price = round((($size->amount) - ($get_product_details->discount_amount)), 2);
                } elseif ($get_product_details->discount_type == 2) {
                    $discount_price = round((($size->amount) - (($size->amount) * ($get_product_details->discount_amount)) / (100)), 2);
                } else {
                    $discount_price = 0;
                }
                if ($discount_price != 0) {
                    $amount_price = $discount_price;
                    $discount_price = $size->amount;
                } else {
                    $amount_price = $size->amount;
                }
                if ($size_key == 0) {
                    $base_amount_price = $amount_price;
                }
                $checked = '';
                if ($size_key == 0) {
                    $checked = "checked";
                    $total_product_price = $total_product_price + $amount_price;
                }
                $size_discount_price_str = ($discount_price > 0) ? '<span class="list_product_amount list_product_discount"><strike><span class="currency"> ' . $discount_price . '</span></strike></span>' : '';
                $product_size_arr .= '<div class="row">
                                    <div class="offset-1 col-md-8">
                                        <div class="form-radio">
                                            <div class="radio">
                                                <label class="radio_label">
                                                    <input type="radio" value="' . $size->id . '" class="product_size radio_product_size_' . $size->id . '" size_id="' . $size->id . '" product_size_amount="' . $amount_price . '" name="product_size" ' . $checked . '>
                                                    <i class="helper"></i>' . $size->name . '
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3"> ' . $size_discount_price_str . ' <b><span class="currency">' . $amount_price . '</span></b></div>
                                </div>';
            }

            $product_option_arr = "";
            $product_options = StoreProductOptions::query()->select('store_product_options.id',
                'store_product_options.name',
                'store_product_options.amount',
                'store_product_customize_category.name as category_name',
                'store_product_customize_category.selection_type')
                ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_options.category_id')
                ->where('store_product_options.product_id', $get_product_details->id)->get();
            $product_options = $product_options->groupBy('category_name')->toArray();
            foreach ($product_options as $option_key => $option) {
                $option_detial_arr = '';
                foreach ($option as $op_key => $option_details) {
                    if ($op_key == 0) {
                        $checked = "checked";
                        $total_product_price = $total_product_price + $option_details['amount'];
                    } else {
                        $checked = "";
                    }
                    $option_detial_arr .= '<div class="row">
                                    <div class="offset-1 col-md-8">
                                        <div class="form-radio">
                                            <div class="radio">
                                                <label class="radio_label">
                                                    <input type="radio" value="' . $option_details['id'] . '" class="product_option radio_product_option_' . $option_details['id'] . '" option_id="' . $option_details['id'] . '" product_option_amount="' . $option_details['amount'] . '" name="product_option_' . $option_key . '" ' . $checked . '>
                                                    <i class="helper"></i>' . $option_details['name'] . '
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3"> <span class="currency"> ' . $option_details['amount'] . '</span></div>
                                </div>';
                }
                $product_option_arr .= '<div class="row m-t-15">
                            <div class="col-md-12 m-b-10"><h6><span class="product_add_ons">' . $option_key . '</span></h6></div>
                            <div class="col-md-12">
                                ' . $option_detial_arr . '
                            </div>
                        </div>';
            }

            $product_topping_arr = '';
            $product_toppings = StoreProductToppings::query()->select('store_product_toppings.id',
                'store_product_toppings.name',
                'store_product_toppings.amount',
                'store_product_customize_category.name as category_name',
                'store_product_customize_category.selection_type')
                ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')
                ->where('store_product_toppings.product_id', $get_product_details->id)->get();
            $product_toppings = $product_toppings->groupBy('category_name')->toArray();
            foreach ($product_toppings as $topping_key => $topping) {
                $topping_detial_arr = '';
                foreach ($topping as $top_key => $topping_details) {
                    $topping_detial_arr .= '<div class="row">
                                    <div class="offset-1 col-md-8">
                                        <div class="border-checkbox-section">
                                            <label for="checkbox_' . $topping_details['id'] . '">
                                                <div class="border-checkbox-group border-checkbox-group-primary" >
                                                    <input type="checkbox" value="' . $topping_details['id'] . '" class="border-checkbox product_topping radio_product_topping_' . $topping_details['id'] . '" topping_id="' . $topping_details['id'] . '" product_topping_amount="' . $topping_details['amount'] . '" name="product_topping_' . $topping_key . '" id="checkbox_' . $topping_details['id'] . '">
                                                    <label class="border-checkbox-label" for="checkbox_' . $topping_details['id'] . '"></label>
                                                    ' . $topping_details['name'] . '
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3"> <span class="currency"> ' . $topping_details['amount'] . '</span></div>
                                </div>';
                }
                $product_topping_arr .= '<div class="row m-t-15">
                            <div class="col-md-12 m-b-10"><h6><span class="product_add_ons">' . $topping_key . '</span></h6></div>
                            <div class="col-md-12">
                                ' . $topping_detial_arr . '
                            </div>
                        </div>';
            }

            $product_model_detials = '<div class="modal-header" style="display: block;">
                    <h4 class="modal-title" id="product_name' . $get_product_details->id . '">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-9"> ' . $get_product_details->name . '</div>
                                <div class="col-md-3"> <span class="currency"> ' . $base_amount_price . '</span></div>
                            </div>
                        </div>
                    </h4>
                    <button type="button" class="close model_close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body p-t-5">
                    <div class="container">
                        <div class="row m-t-15">
                            <div class="col-md-12 m-b-10"><h6><span class="product_add_ons">Quantity</span></h6></div>
                            <div class="col-md-12">
                                ' . (($product_size_arr != Null) ? $product_size_arr : "Quantity Not Available" ) . '
                            </div>
                        </div>
                        ' . $product_option_arr . '
                        ' . $product_topping_arr . '
                    </div>
                </div>
                <div class="modal-footer" style="display: block;">
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                            <button type="button" class="btn btn-primary waves-effect waves-light btn-block" id="add_product_cart" user_id="' . $user_id . '" store_id="' . $store_id . '" product_id="' . $get_product_details->id . '" >
                                <span class="mrgnLeftClass" style="float: left"> Add Item </span>
                                <span style="float: right" class="finalpriceAmtTxt mrgnRightClass">Total : <span class="currency total_product_amount"> ' . $total_product_price . '</span></span>
                            </button>
                        </div>
                    </div>
                </div>';

            return response()->json([
                'success' => true,
                'store_id' => $store_details->id,
                'product_model_detials' => $product_model_detials,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Store Not Found!'
            ]);
        }
    }

    public function getAdminCreateOrderAddCartStoreProduct(Request $request)
    {

        $slug = $request->get('slug');
        $repeat = $request->get('repeat');
        $cart_id = $request->get('cart_id') - 0;
        $user_id = $request->get('user_id');
        $store_id = $request->get('store_id');
        $product_id = $request->get('product_id');
        $quantity = $request->get('quantity');
        $product_size_id = $request->get('product_size_id');
        $is_toppings = $request->get('is_toppings');
        $product_option_ids = $request->get('product_option_ids');
        $product_toppings_ids = $request->get('product_toppings_ids');
        if ($user_id == 0 || $store_id == 0 || $product_id == 0) {
            return response()->json([
                'success' => false,
                'message' => 'data not found!'
            ]);
        }

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Service category not found!'
            ]);
        }

        $store_details = StoreDetails::query()->select('store_details.id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('store_details.id', $store_id)->where('provider_services.status', 1)->first();
        if ($store_details != Null) {
            $get_product_details = StoreProductDetails::query()->select('store_product_details.id')
                ->join('store_product_category', 'store_product_category.id', 'store_product_details.product_cate_id')
                ->where('store_product_details.id', $product_id)
                ->where('store_product_details.store_id', $store_id)
                ->where('store_product_details.status', 1)
                ->where('store_product_category.status', 1)
                ->first();
            if ($get_product_details == Null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found!'
                ]);
            }
            if ($repeat > 0) {
                if ($cart_id != NULL || $cart_id > 0) {
                    $user_order_cart = UserOrderCart::query()
                        ->where("id", $cart_id)
                        ->where("user_id", $user_id)
                        ->where("order_add_by", 0)
                        ->where("store_id", $store_id)
                        ->where("product_id", $product_id)
                        ->orderBy("id", "desc")
                        ->first();
                } else {
                    $user_order_cart = UserOrderCart::query()
                        ->where("user_id", $user_id)
                        ->where("order_add_by", 0)
                        ->where("store_id", $store_id)
                        ->where("product_id", $product_id)
                        ->orderBy("id", "desc")
                        ->first();
                }
                if ($user_order_cart != Null) {
                    if ($quantity == 1) {
                        $user_order_cart->quantity = $user_order_cart->quantity + $quantity;
                        $user_order_cart->save();
                    } else {
                        if ($user_order_cart->quantity <= 1) {
                            $user_order_cart->delete();
                        } else {
                            $user_order_cart->quantity = $user_order_cart->quantity - 1;
                            $user_order_cart->save();
                        }
                    }
                }
            } else {
                if ($is_toppings > 0) {
                    if ($product_size_id > 0) {
                        $product_size = StoreProductSize::query()->select('id')
                            ->where('id', $product_size_id)
                            ->where('product_id', $get_product_details->id)->first();
                        if ($product_size == Null) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Product quantity not found!'
                            ]);
                        }
                    }

                    if ($product_option_ids != Null) {
                        $product_option_ids_arr = explode(",", $product_option_ids);
                        if (count($product_option_ids_arr) > 0) {
                            $product_options = StoreProductOptions::query()->select('store_product_options.id')
                                ->whereIn('store_product_options.id', $product_option_ids_arr)
                                ->where('store_product_options.product_id', $get_product_details->id)
                                ->get();
                            if ($product_options->isEmpty()) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Product add ons not found!'
                                ]);
                            }
                        }
                    } else {
                        $product_option_ids = Null;
                    }

                    if ($product_toppings_ids != Null) {
                        $product_toppings_ids_arr = explode(",", $product_toppings_ids);
                        if (count($product_toppings_ids_arr) > 0) {
                            $product_toppings = StoreProductToppings::query()->select('store_product_toppings.id')
                                ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')
                                ->whereIn('store_product_toppings.id', $product_toppings_ids_arr)
                                ->where('store_product_toppings.product_id', $get_product_details->id)
                                ->get();
                            if ($product_toppings->isEmpty()) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Product add ons not found!'
                                ]);
                            }
                        }
                    } else {
                        $product_toppings_ids = Null;
                    }
                } else {
                    $product_size_id = Null;
                    $product_option_ids = Null;
                    $product_toppings_ids = Null;
                }


                $user_order_cart = new UserOrderCart();
                $user_order_cart->user_id = $user_id;
                $user_order_cart->store_id = $store_id;
                $user_order_cart->order_add_by = 0;
                $user_order_cart->service_cat_id = $service_category->id;
                $user_order_cart->product_id = $product_id;
                $user_order_cart->quantity = $quantity;
                $user_order_cart->size_id = $product_size_id;
                $user_order_cart->option_id = $product_option_ids;
                $user_order_cart->topping_id = $product_toppings_ids;
                $user_order_cart->save();
            }

            return response()->json([
                'success' => true,
                'user_order_cart' => $user_order_cart,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Store Not Found!'
            ]);
        }
    }

    public function getAdminCreateOrderGetCartDetails(Request $request)
    {
        $get_type = $request->get('get_type');
        $slug = $request->get('slug');
        $user_id = $request->get('user_id');
        $store_id = $request->get('store_id');
        if ($user_id == Null || $slug == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found!',
            ]);
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Service category not found!'
            ]);
        }
        $user_details = User::query()->where('id', $user_id)->first();
        if ($user_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'User not found!'
            ]);
        }

        $total_quantity = 0;
        $get_store_list = "";
        $city_id = 0;
        $total_item_cost = 0;
        $get_store_details = "";
        $set_store_count = 0;

        $user_order_cart_list = UserOrderCart::query()->where('order_add_by', 0)->where('user_id', $user_id)->get();
        if ($user_order_cart_list->isNotEmpty()) {
            $user_order_cart_list = $user_order_cart_list->groupBy('store_id');
            foreach ($user_order_cart_list as $cart_key => $user_order_cart) {
                $store_details = StoreDetails::query()->select('store_details.id',
                    'store_details.name', 'store_details.image',
                    'store_details.offer_min_amount', 'store_details.offer_amount',
                    'store_details.offer_type', 'store_details.order_min_amount',
                    'store_details.rating', 'store_details.eta_delivery_time',
                    'provider_services.current_status'
                    , 'providers.email'
                    , 'providers.country_code'
                    , 'providers.contact_number'
                    , 'providers.service_radius'
                //, 'providers.city_id'
                )
                    ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                    ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                    ->where('provider_services.status', 1)
                    ->where('store_details.id', $cart_key)
                    ->where('provider_services.service_cat_id', $service_category->id)
                    ->first();

                if ($store_details != Null) {
                    $store_id = $store_details->id;
                    $product_detail = "";
                    foreach ($user_order_cart as $key => $order_cart) {
                        $is_customize = 0;
                        $item_total_cost = 0;
                        $get_product_details = $get_product_details = StoreProductDetails::query()->select('store_product_details.id',
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
                            ->where('store_product_details.id', $order_cart->product_id)
                            ->where('store_product_details.store_id', $store_id)
                            ->where('store_product_details.status', 1)
                            ->where('store_product_category.status', 1)
                            ->first();
                        if ($get_product_details != Null) {

                            $price = $get_product_details->amount;
                            $product_size = StoreProductSize::query()->select('id', 'name', 'amount')->where('id', $order_cart->size_id)->where('product_id', $get_product_details->id)->first();
                            if ($product_size != Null) {
                                $price = $product_size->amount;
                                $is_customize = 1;
                            }
                            if ($get_product_details->discount_amount != Null && $get_product_details->discount_type != Null) {
                                if ($get_product_details->discount_type == 1) {
                                    $price = $price - $get_product_details->discount_amount;
                                } elseif ($get_product_details->discount_type == 2) {
                                    $discount_amount = round((($price * $get_product_details->discount_amount) / 100), 2);
                                    $price = $price - $discount_amount;
                                }
                            }
                            $item_total_cost = $item_total_cost + $price;

                            $product_option_ids_arr = explode(",", $order_cart->option_id);
                            if ($product_option_ids_arr != Null) {
                                foreach ($product_option_ids_arr as $option) {
                                    $options_details = StoreProductOptions::query()->where('id', $option)->where('product_id', $get_product_details->id)->first();
                                    if ($options_details != Null) {
                                        $is_customize = 1;
                                        $item_total_cost = $item_total_cost + $options_details->amount;
                                    }
                                }
                            }

                            $product_toppings_ids_arr = explode(",", $order_cart->topping_id);
                            if ($product_toppings_ids_arr != Null) {
                                foreach ($product_toppings_ids_arr as $topping) {
                                    $toppings_details = StoreProductToppings::query()->where('id', $topping)->where('product_id', $get_product_details->id)->first();
                                    if ($toppings_details != Null) {
                                        $is_customize = 1;
                                        $item_total_cost = $item_total_cost + $toppings_details->amount;
                                    }
                                }
                            }
                            $item_total_cost = $item_total_cost * $order_cart->quantity;
                            $total_quantity = $total_quantity + $order_cart->quantity;
                            $total_item_cost = $total_item_cost + $item_total_cost;


                            $is_customize_str = ($is_customize == 1) ? '<span class="font_sz_12 m-l-10">Customize</span>' : '';
                            $product_detail .= '<tr>
                                                <td>
                                                    <div class="font_sz_14 m-l-10"><b>' . $get_product_details->name . '</b></div>
                                                    ' . $is_customize_str . '
                                                </td>
                                                <td class="text-right">
                                                    <p>
                                                         <span class="btn waves-effect waves-light btn-danger m-r-5 cart_minus_plus remove_product" cart_id="' . $order_cart->id . '" quantity="' . $order_cart->quantity . '"  store_id="' . $store_id . '" product_id="' . $get_product_details->id . '"><i class="fas fa-minus" style="margin-right: 0;"></i></span>
                                                         <span style="width: 5px">' . $order_cart->quantity . '</span>
                                                         <span class="btn waves-effect waves-light btn-success m-l-5 cart_minus_plus plus_product" cart_id="' . $order_cart->id . '" store_id="' . $store_id . '" product_id="' . $get_product_details->id . '"><i class="fas fa-plus" style="margin-right: 0;"></i></span>
                                                    </p>
                                                </td>
                                                <td class="text-right">
                                                    <b><span class="currency" style="font-size: 15px"> ' . $item_total_cost . '</span></b>
                                                </td>
                                            </tr>';


                        }
                    }
                    $get_store_details .= '<tr style="background-color: #e5f2ff"><td colspan="3"><div><b>' . $store_details->name . '</b></div></td></tr>' . $product_detail;

                    if ($get_type == 1) {
                        $margin_top = "";
                        $store_active = "storeActive";
                        if ($set_store_count > 0) {
                            $margin_top = "m-t-15";
                            $store_active = "storeInActive";
                        } else {
                            $city_id = $store_details->city_id;
                        }
                        $set_store_count = $set_store_count + 1;
                        $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')->where('store_id', $store_details->id)->where('day', 'all')->first();
                        if ($store_time == Null) {
                            $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')->where('store_id', $store_details->id)->where('day', substr(date('l'), 0, 3))->first();
                        }
                        $time = Date('H:i:s');
                        try {
                            if ($store_time != Null) {
                                if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time && $store_details->current_status == 1) {
                                    $store_time_status = 1; //"open"
                                } else {
                                    $store_time_status = 0; //"close"
                                }
                            } else {
                                $store_time_status = 0; //"close"
                            }
                            $store_today_time = date("H:i A", strtotime($store_time->store_open_time)) . " to " . date("H:i A", strtotime($store_time->store_close_time));// today time
                        } catch (\Exception $e) {
                            $store_time_status = 0;
                            $store_today_time = "----";
                        }
                        $store_open_hour = ($store_time_status == 1) ? "Open" : "Close";
                        $get_store_list .= '<div class="store_wrapper ' . $margin_top . '">
                            <div class="form-group m-b-10 store_select" store_details_count="' . $set_store_count . '">
                                <select id="select_store_' . $set_store_count . '" store_id="' . $store_id . '" name="store" class="js-example-placeholder-single1 js-states form-control store_id store_id_' . $set_store_count . '" store_details_count="' . $set_store_count . '">
                                    <option disabled selected value="">Select a Store</option>
                                    <option value="' . $store_id . '" store_name="' . $store_details->name . '" store_email="' . $store_details->email . '" store_contect_number="' . $store_details->country_code . $store_details->contact_number . '" store_open_hour="' . $store_open_hour . '" store_today_time="' . $store_today_time . '" selected>' . $store_details->name . '</option>
                                </select>
                            </div>
                            <div class="storeDetails storeDetails_' . $set_store_count . " " . $store_active . '" store_details_count="' . $set_store_count . '">
                                <div><span class="store-attribute">Store Name : </span> <span id="store_name_' . $set_store_count . '">' . $store_details->name . '</span></div>
                                <div><span class="store-attribute">Email : </span> <span id="store_email_' . $set_store_count . ' ">' . $store_details->email . '</span></div>
                                <div><span class="store-attribute">Contect Number : </span> <span id="store_contect_number_' . $set_store_count . '">' . $store_details->country_code . $store_details->contact_number . '</span></div>
                                <div><span class="store-attribute">Opening Hours : </span> <span id="store_open_hour_' . $set_store_count . '">' . $store_open_hour . '</span></div>
                                <div><span>Today : </span> <span id="store_today_time_' . $set_store_count . '">' . $store_today_time . '</span></div>
                                <div style="text-align:end;"><span store_id="' . $store_id . '" id="product_browse_' . $set_store_count . '" class="product_browse m-t-5">Browse Product</span></div>
                            </div>
                        </div>';
                    }
                }
            }
        }
        if ($get_store_details == "") {
            $get_store_details = '<p class="text-center">Cart was empty!</p>';
        }

        $checkout_footer = '<div class="table-responsive">
                                <table class="table m-b-0 without-header cart-footer-table">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div><b><h4>SubTotal</h4></b></div>
                                            <span>Extra Charges may apply</span>
                                        </td>
                                        <td class="text-right">
                                            <span><b><h4 class="currency"> ' . $total_item_cost . '</h4></b></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                        <button type="submit" class="btn btn-success col-md-12">
                                        Checkout
                                        </button>

                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>';

        $cart_details = '<div class="card-block p-b-0">
                            <div class="table-responsive">
                                <table class="table m-b-0 without-header cart-table">
                                    <tbody>' . $get_store_details . '</tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer" style="border-top: 1px solid gray; background-color: #f7f7f7;">
                            ' . $checkout_footer . '
                        </div>';

        return response()->json([
            'success' => true,
            'total_quantity' => $total_quantity,
            'cart_details' => $cart_details,
            'store_list' => $get_store_list,
            'city_id' => $city_id,
            'set_store_count' => $set_store_count,
        ]);
    }

    public function getAdminCreateOrderGetStoreCheckoutCartDetails(Request $request)
    {
        $is_checkout = $request->get('is_checkout');
        $address_id = $request->get('address_id');
        $get_type = $request->get('get_type');
        $slug = $request->get('slug');
        $user_id = $request->get('user_id');
        if ($user_id == Null || $slug == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found!',
            ]);
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Service category not found!'
            ]);
        }
        $user_details = User::query()->where('id', $user_id)->first();
        if ($user_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'User not found!'
            ]);
        }

        $total_quantity = 0;
        $product_list_array = [];
        $get_store_list = "";
        $city_id = 0;

        $get_store_details = "";
        $set_store_count = 0;
        $total_total_pay = 0;
        $sub_total_pay = 0;
        $user_order_cart_list = UserOrderCart::query()->where('order_add_by', 0)->where('user_id', $user_id)->get();
        if ($user_order_cart_list->isNotEmpty()) {
            $user_order_cart_list = $user_order_cart_list->groupBy('store_id');
            $store_ids_array = $user_order_cart_list->keys("store_id");
            //$checkSameCityId = StoreDetails::query()
            //    ->select("store_details.*", "providers.city_id")
            //    ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            //    ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            //    ->whereIn('store_details.id', $store_ids_array)
            //    ->pluck('providers.city_id')
            //    ->toArray();
            //$store_city_id = array_unique($checkSameCityId)[0];
            $store_city_id = 0;
            //$sub_general_setting = SubGeneralSettings::query()->select('base_fee', 'maintenance_charge')->where('city_id', $store_city_id)->first();
            //if ($sub_general_setting != Null) {
            //    $base_fee = $sub_general_setting->base_fee;
            //} else {
            $maintenance_charge = 0;
            $base_fee = 0;
            //}

            $settings = ServiceSettings::query()->where('service_cat_id', $service_category->id)->first();//->where('city_id', $store_city_id)
            if ($settings != Null) {
                $tax_cost = $settings->tax != 0 ? $settings->tax : 0;
            } else {
                $tax_cost = 0;
            }

            $adminClassApi = new AdminClass();
            $userClassapi = new UserClassApi($this->notificationClass,$this->adminClass);
            foreach ($user_order_cart_list as $cart_key => $user_order_cart) {
                $total_item_cost = 0;
                $store_details = StoreDetails::query()->select('store_details.id',
                    'store_details.name', 'store_details.image',
                    'store_details.offer_min_amount',
                    'store_details.offer_amount',
                    'store_details.offer_type',
                    'store_details.order_min_amount',
                    'store_details.rating', 'store_details.eta_delivery_time',
                    'store_details.packaging_charges',
                    'store_details.address_lat',
                    'store_details.address_long',
                    'provider_services.current_status'
                    , 'providers.email'
                    , 'providers.country_code'
                    , 'providers.contact_number'
                    , 'providers.service_radius'
                //, 'providers.city_id'
                )
                    ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                    ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                    ->where('provider_services.status', 1)
                    ->where('store_details.id', $cart_key)
                    ->where('provider_services.service_cat_id', $service_category->id)
                    ->first();

                if ($store_details != Null) {

                    $store_id = $store_details->id;
                    $product_detail = "";
                    $get_product_list = [];
                    $order_cart_service_cat_id = $service_category->id;
                    foreach ($user_order_cart as $key => $order_cart) {
                        $order_cart_service_cat_id = $order_cart->service_cat_id;
                        $is_customize = 0;
                        $item_total_cost = 0;
                        $get_product_details = $get_product_details = StoreProductDetails::query()->select(
                            'store_product_details.id',
                            'store_product_details.name',
                            'store_product_details.food_type',
                            'store_product_details.amount',
                            'store_product_details.discount_amount',
                            'store_product_details.discount_type',
                            'store_product_details.description',
                            'store_product_category.name as category_name',
                            'store_product_category.id as category_id')
                            ->join('store_product_category', 'store_product_category.id', 'store_product_details.product_cate_id')
                            ->where('store_product_details.id', $order_cart->product_id)
                            ->where('store_product_details.store_id', $store_id)
                            ->where('store_product_details.status', 1)
                            ->where('store_product_category.status', 1)
                            ->first();
                        if ($get_product_details != Null) {
                            $product_size_id = 0;
                            $get_product_option_ids = [];
                            $get_product_toppings_ids = [];
                            $price = $get_product_details->amount;
                            $product_size = StoreProductSize::query()->select('id', 'name', 'amount')
                                ->where('id', $order_cart->size_id)
                                ->where('product_id', $get_product_details->id)
                                ->first();
                            if ($product_size != Null) {
                                $product_size_id = $product_size->id;
                                $price = $product_size->amount;
                                $is_customize = 1;
                            }
                            if ($get_product_details->discount_amount != Null && $get_product_details->discount_type != Null) {
                                if ($get_product_details->discount_type == 1) {
                                    $price = $price - $get_product_details->discount_amount;
                                } elseif ($get_product_details->discount_type == 2) {
                                    $discount_amount = round((($price * $get_product_details->discount_amount) / 100), 2);
                                    $price = $price - $discount_amount;
                                }
                            }
                            $item_total_cost = $item_total_cost + $price;
                            $product_option_ids_arr = explode(",", $order_cart->option_id);
                            if ($product_option_ids_arr != Null) {
                                foreach ($product_option_ids_arr as $option) {
                                    $options_details = StoreProductOptions::query()
                                        ->where('id', $option)
                                        ->where('product_id', $get_product_details->id)
                                        ->first();
                                    if ($options_details != Null) {
                                        $get_product_option_ids[] = $options_details->id - 0;
                                        $is_customize = 1;
                                        $item_total_cost = $item_total_cost + $options_details->amount;
                                    }
                                }
                            }
                            $product_toppings_ids_arr = explode(",", $order_cart->topping_id);
                            if ($product_toppings_ids_arr != Null) {
                                foreach ($product_toppings_ids_arr as $topping) {
                                    $toppings_details = StoreProductToppings::query()
                                        ->where('id', $topping)
                                        ->where('product_id', $get_product_details->id)
                                        ->first();
                                    if ($toppings_details != Null) {
                                        $get_product_toppings_ids[] = $toppings_details->id - 0;
                                        $is_customize = 1;
                                        $item_total_cost = $item_total_cost + $toppings_details->amount;
                                    }
                                }
                            }
                            $item_total_cost = round($item_total_cost * $order_cart->quantity, 2);

                            $total_quantity = $total_quantity + $order_cart->quantity;
                            $total_item_cost = $total_item_cost + $item_total_cost;


                            $is_customize_str = ($is_customize == 1) ? ' (<span class="font_sz_12">Meal (L), Veg Meal(L)</span>)' : '';
                            $product_detail .= '<tr>
                                                <td>
                                                    <div class="font_sz_14 m-l-10"><b>' . $get_product_details->name . '</b>' . $is_customize_str . '</div>
                                                </td>
                                                <td class="text-right">
                                                    <p class="cart_icon">
                                                    <span class="btn waves-effect waves-light btn-danger m-r-5 cart_minus_plus remove_product" cart_id="' . $order_cart->id . '" quantity="' . $order_cart->quantity . '"  store_id="' . $store_id . '" product_id="' . $get_product_details->id . '"><i class="fas fa-minus" style="margin-right: 0;"></i></span>
                                                     <span style="width: 5px">' . $order_cart->quantity . '</span>
                                                     <span class="btn waves-effect waves-light btn-success m-l-5 cart_minus_plus plus_product" cart_id="' . $order_cart->id . '" store_id="' . $store_id . '" product_id="' . $get_product_details->id . '"><i class="fas fa-plus" style="margin-right: 0;"></i></span> </p>
                                                </td>
                                                <td class="text-right">
                                                    <b><span class="currency" style="font-size: 15px"> ' . $item_total_cost . '</span></b>
                                                </td>
                                            </tr>
                                            ';


                            $get_product_list[] = [
                                'product_id' => $get_product_details->id - 0,
                                'product_quantity' => [
                                    [
                                        'quantity' => $order_cart->quantity,
                                        'size' => $product_size_id - 0,
                                        'options' => $get_product_option_ids,
                                        'toppings' => $get_product_toppings_ids
                                    ]
                                ]
                            ];
                        }
                    }

                    $delivery_cost = 0;
                    if ($address_id > 0) {
                        $address = UserAddress::query()->where('id', $address_id)->where('user_id', $user_details->id)->where('status', 1)->first();
                        if ($address == Null) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Users address not found!',
                            ]);
                        }
                        $address_lat_long = array_map('trim', explode(",", $address->lat_long));
                        if (count($address_lat_long) != 2) {
                            return response()->json([
                                'success' => false,
                                'message' => 'User address not found!',
                            ]);
                        }
                        $address_lat = trim($address_lat_long[0]);
                        $address_long = trim($address_lat_long[1]);

                        $delivery_lat = isset($address_lat) ? $address_lat : 0;
                        $delivery_long = isset($address_long) ? $address_long : 0;

                        $user_address = urlencode($address_lat.','.$address_long);
                        $origin = urlencode($store_details->address_lat . "," . $store_details->address_long);

                        $get_coordinates = $userClassapi->get_coordinates($origin, $user_address);
                        $coordinates_check = json_decode($get_coordinates, TRUE);
                        if ($coordinates_check == 0) {
                            return response()->json([
                                "status" => 0,
                                //"message" => "something went to wrong!",
                                "message" => __('user_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
                        $total_distance = 0;
                        if (count($coordinates_check['routes']) > 0) {
                            $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);
                            $total_distance = ($total_distance * 0.001);
                        }

                        $delivery_cost = $userClassapi->checkDeliveryCharge($total_distance,$service_category->id);
                    }

                    $delivery_cost_min = 0;
                    $delivery_charge = $delivery_cost + $delivery_cost_min;

                    $delivery_cost = $delivery_charge +  $base_fee;
                    //$delivery_cost = $delivery_cost +  $base_fee;
                    //$delivery_cost = $delivery_cost + $maintenance_charge  + $base_fee;

                    $discount_cost = 0;
                    if ($store_details->offer_min_amount != 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
                        if ($total_item_cost >= $store_details->offer_min_amount) {
                            if ($store_details->offer_type == 1) {
                                $discount_cost = $total_item_cost - (($total_item_cost) - ($store_details->offer_amount));
                            } else {
                                $discount_cost = ((($total_item_cost) * ($store_details->offer_amount)) / 100);
                            }
                        }
                    }
                    if ($store_details->offer_min_amount == 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
                        if ($store_details->offer_type == 1) {
                            $discount_cost = $total_item_cost - (($total_item_cost) - ($store_details->offer_amount));
                        } else {
                            $discount_cost = ((($total_item_cost) * ($store_details->offer_amount)) / 100);
                        }
                    }

                    $promo_discount_cost = 0;
                    $packaging_cost = $store_details->packaging_charges;
                    $extra_charges = $delivery_cost + $maintenance_charge + $packaging_cost;

                    //$extra_charges = $delivery_cost + $packaging_cost;
                    $tax_cost = round(((((($total_item_cost - $discount_cost) + $extra_charges) - $promo_discount_cost) * $tax_cost) / 100), 2);

                    $total_pay = round((((($total_item_cost - $discount_cost) + $extra_charges) - $promo_discount_cost) + $tax_cost), 2);

                    $sub_total_pay = $sub_total_pay + $total_pay;
                    $total_total_pay = $total_total_pay + $total_pay;

                    $product_list_array[] = [
                        'store_id' => $store_id - 0,
                        'user_taken_type' => 0,
                        'additional_info' => Null,
                        'product_list' => $get_product_list,
                    ];

                    $get_store_details .= '<tr style="background-color: #e5f2ff"><td colspan="3"><div><b>' . $store_details->name . '</b></div></td></tr>' . $product_detail
                        . '
                                            <tr class="cart-row" style="border-top: 1px solid gray">
                                                <td colspan="2" class="text-right"><div>Item Total</div></td>
                                                <td class="text-right"><span class="currency" style="font-size: 15px"> ' . $total_item_cost . '</span></td>
                                            </tr>
                                            <tr class="cart-row">
                                                <td colspan="2" class="text-right"><div>Offer Discount</div></td>
                                                <td class="text-right"><span class="currency" style="font-size: 15px"> ' . $discount_cost . '</span></td>
                                            </tr>
                                            <tr class="cart-row">
                                                <td></td>
                                                <td class="text-right"><div>Delivery Chrages </div></td>
                                                <td class="text-right"><span class="currency" style="font-size: 15px"> ' . round($delivery_cost + $maintenance_charge, 2) . '</span></td>
                                            </tr>
                                            <tr class="cart-row">
                                                <td colspan="2" class="text-right"><div>Packing Chrages</div></td>
                                                <td class="text-right"><span class="currency" style="font-size: 15px"> ' . $packaging_cost . '</span></td>
                                            </tr>
                                            <tr class="cart-row">
                                                <td colspan="2" class="text-right"><div>Tax</div></td>
                                                <td class="text-right"><span class="currency" style="font-size: 15px"> ' . $tax_cost . '</span></td>
                                            </tr>
                                            <tr class="cart-row">
                                                <td colspan="2" class="text-right"><div><b>Sub Total</b></div></td>
                                                <td class="text-right"><b><span class="currency" style="font-size: 15px"> ' . $total_pay . '</span></b></td>
                                            </tr>';

                    if ($get_type == 1) {
                        $margin_top = "";
                        $store_active = "storeActive";
                        if ($set_store_count > 0) {
                            $margin_top = "m-t-15";
                            $store_active = "storeInActive";
                        } else {
                            $city_id = $store_details->city_id;
                        }
                        $set_store_count = $set_store_count + 1;
                        $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')->where('store_id', $store_details->id)->where('day', 'all')->first();
                        if ($store_time == Null) {
                            $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')->where('store_id', $store_details->id)->where('day', substr(date('l'), 0, 3))->first();
                        }
                        $time = Date('H:i:s');
                        try {
                            if ($store_time != Null) {
                                if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time && $store_details->current_status == 1) {
                                    $store_time_status = 1; //"open"
                                } else {
                                    $store_time_status = 0; //"close"
                                }
                            } else {
                                $store_time_status = 0; //"close"
                            }
                            $store_today_time = date("H:i A", strtotime($store_time->store_open_time)) . " to " . date("H:i A", strtotime($store_time->store_close_time));// today time
                        } catch (\Exception $e) {
                            $store_time_status = 0;
                            $store_today_time = "----";
                        }
                        $store_open_hour = ($store_time_status == 1) ? "Open" : "Close";
                        $get_store_list .= '<div class="store_wrapper ' . $margin_top . '">
                            <div class="form-group m-b-10 store_select" store_details_count="' . $set_store_count . '">
                                <select id="select_store_' . $set_store_count . '" name="store" class="js-example-placeholder-single1 js-states form-control store_id store_id_' . $set_store_count . '" store_details_count="' . $set_store_count . '">
                                    <option disabled selected value="">Select a Store</option>
                                    <option value="' . $store_id . '" store_name="' . $store_details->name . '" store_email="' . $store_details->email . '" store_contect_number="' . $store_details->country_code . $store_details->contact_number . '" store_open_hour="' . $store_open_hour . '" store_today_time="' . $store_today_time . '" selected>' . $store_details->name . '</option>
                                </select>
                            </div>
                            <div class="storeDetails storeDetails_' . $set_store_count . " " . $store_active . '" store_details_count="' . $set_store_count . '">
                                <div><span class="store-attribute">Store Name : </span> <span id="store_name_' . $set_store_count . '">' . $store_details->name . '</span></div>
                                <div><span class="store-attribute">Email : </span> <span id="store_email_' . $set_store_count . ' ">' . $store_details->email . '</span></div>
                                <div><span class="store-attribute">Contect Number : </span> <span id="store_contect_number_' . $set_store_count . '">' . $store_details->country_code . $store_details->contact_number . '</span></div>
                                <div><span class="store-attribute">Opening Hours : </span> <span id="store_open_hour_' . $set_store_count . '">' . $store_open_hour . '</span></div>
                                <div><span>Today : </span> <span id="store_today_time_' . $set_store_count . '">' . $store_today_time . '</span></div>
                            </div>
                        </div>';
                    }
                }
            }
        }
        if ($get_store_details == "") {
            $get_store_details = '<p class="text-center">Cart was empty!</p>';
        }

        $get_product_list_array = json_encode($product_list_array, true);

        $checkout_footer = '<div class="row">
                                <div class="col-sm-4"></div>
                                <div class="col-sm-8">
                                    <div class="table-responsive">
                                        <table class="table m-b-0 without-header cart-footer-table">
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <div><b><h5>Sub Total</h5></b></div>
                                                </td>
                                                <td class="text-right">
                                                    <span><b><h5 class="currency"> ' . round($sub_total_pay, 2) . '</h5></b></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div><b><h4>Total</h4></b></div>
                                                </td>
                                                <td class="text-right">
                                                    <span><b><h4 class="currency"> ' . round($total_total_pay, 2) . '</h4></b></span>
                                                    <input id="product_list" type="hidden" name="product_list" class="form-control" value=' . $get_product_list_array . ' placeholder="Product list">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3">
                                                    <button type="submit" class="btn btn-success col-md-12">
                                                        Pay Now
                                                    </button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>';

        $cart_details = '<div class="card-block p-b-0">
                            <div class="table-responsive">
                                <table class="table m-b-0 without-header cart-table">
                                    <tbody>' . $get_store_details . '</tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer" style="border-top: 1px solid gray; background-color: #f7f7f7;">
                            ' . $checkout_footer . '
                        </div>';

        return response()->json([
            'success' => true,
            'total_quantity' => $total_quantity,
            'cart_details' => $cart_details,
            'store_list' => $get_store_list,
            'city_id' => $city_id,
        ]);
    }

    public function getAdminCheckoutOrder(Request $request, $slug, $user_id, $user_address_id = 0)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->route('get:admin:place_order', ['slug' => $slug, 'user_id' => $user_id]);
        }
        $user_details = User::query()->where('id', $user_id)->first();
        if ($user_details == Null) {
            Session::flash('error', 'User detail not found!');
            return redirect()->route('get:admin:place_order', ['slug' => $slug, 'user_id' => $user_id]);
        }
        if ($user_address_id == 0) {
            $user_address_id = $request->get('user_address');
        }
        $address_list = UserAddress::query()->where("id", $user_address_id)->where("user_id", $user_details->id)->where('status', 1)->first();

        if ($address_list == Null) {
            Session::flash('error', 'Please select address!');
            return redirect()->route('get:admin:place_order', ['slug' => $slug, 'user_id' => $user_id]);
        }
        //$admin_city = City::query()->where('type', '1')->get();

        $view = view('admin.pages.store_services.order_book.checkout', compact('slug', 'service_category', 'user_details', 'address_list', 'user_address_id'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function anyAdminCreatePlaceOrder($slug, Request $request) {

        $get_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($get_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $service_category_id = $get_category->id;
        $user_id = $request->get('user_id');
        $product_list = $request->get('product_list');
        $promocode_id = 0;
        $payment_type = 1;
        $user_taken_type = 1;
        $address_id = $request->get('address_id');

        if ($user_id == Null || $product_list == Null || $address_id == Null) {
            Session::flash('error', 'Required fields is empty!');
            return redirect()->back();
        }

        $user_details = User::query()->where('id', $user_id)->first();
        if ($user_details == Null) {
            Session::flash('error', 'User not found!');
            return redirect()->back();
        }

        $nif = 0;
        $user_nif = "";

        $general_settings = GeneralSettings::query()->first();
        if ($general_settings == Null) {
            Session::flash('error', 'Site setting not found!');
            return redirect()->back();
        }

        //get user address
        $address_lat_long = Null;
        $area_id = 0;
        $delivery_charge = 0;
        if ($user_taken_type != 2) {
            $address = UserAddress::query()->where('id', $address_id)->where('user_id', $user_details->id)->where('status', 1)->first();
            if ($address == Null) {
                Session::flash('error', 'User address not found!');
                return redirect()->back();
            }
            $address_lat_long = array_map('trim', explode(",", $address->lat_long));
            if (count($address_lat_long) != 2) {
                Session::flash('error', 'User address not found!');
                return redirect()->back();
            }

            $address_lat = trim($address_lat_long[0]);
            $address_long = trim($address_lat_long[1]);

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
            if($adminClassApi->is_in_restricted_area($points_polygon,$restricted_lat,$restricted_long,$address_lat,$address_long)) {
                Session::flash('error', 'Service is not available in your region, please try with other location!');
                return redirect()->back();
            }
        }

        try {
            $store_product_list = json_decode($product_list, true);
        } catch (\Exception $exception) {
            Session::flash('error', 'Product list json is not valid!');
            return redirect()->back();
        }
        if ($store_product_list == Null) {
            Session::flash('error', 'Product list json is not valid!');
            return redirect()->back();
        }

        //get user store details
        $get_store_details = StoreDetails::query()
            ->select("store_details.*")//, "providers.city_id"
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id');

        $store_ids_array = array_column($store_product_list, 'store_id');
        //$checkSameCityId = $get_store_details->whereIn('store_details.id', $store_ids_array)
        //    ->pluck('providers.city_id')->toArray();
        //if (!(count(array_unique($checkSameCityId)) <= 1)) {
        //    Session::flash('error', 'Please select all store in same city!');
        //    return redirect()->back();
        //}
        //if (count(array_unique($checkSameCityId)) > 3) {
        //    Session::flash('error', 'You can maximum 3 store allow to place order!');
        //    return redirect()->back();
        //}
        //$store_city_id = array_unique($checkSameCityId)[0];
        $store_city_id = 0;

        $user_taken_status = 0;
        //if ($user_taken_type == 2) {
        //    $checkSameTakeAwayOption = $get_store_details->whereIn('store_details.id', $store_ids_array)->pluck('store_details.taken_away_type')->toArray();
        //    if (!(count(array_unique($checkSameTakeAwayOption)) <= 1)) {
        //        Session::flash('error', 'Please select all store in given take away option!');
        //        return redirect()->back();
        //    }
        //    $user_taken_status = array_unique($checkSameTakeAwayOption)[0];
        //}
        $service_settings = ServiceSettings::query()->where('service_cat_id', $service_category_id)->first();//->where('city_id', $store_city_id)
        if ($service_settings != Null) {
            $admin_commission = $service_settings->admin_commission;
            $delivery_commission = $service_settings->delivery_commission;
            $tax_cost = $service_settings->tax != 0 ? $service_settings->tax : 0;
        }
        else {
            $admin_commission = 0;
            $delivery_commission = 0;
            $tax_cost = 0;
        }
        $order_date_time = date('Y-m-d H:i:s');
        if ($request->get('schedule_date_time') != Null) {
            $place_order_type = 1;
            $place_order_date_time = date('Y-m-d H:i:s', strtotime($request->get('schedule_date_time')));
        } else {
            $place_order_type = 0;
            $place_order_date_time = $order_date_time;
        }

        //type = 0 store unavailble
        //type = 1 product un available
        //type = 2 both unavailable
        //type = 3 min order amount

        //store and product counting
        $req_store_ids = [];
        $unavailable_store = [];
        $order_book_array = [];
        $all_total_pay = 0;
        $all_total_item_cost = 0;

        $userClassapi = new UserClassApi($this->notificationClass,$this->adminClass);

        foreach ($store_product_list as $store_key => $store_product_detils) {
            $next_status = 0;
            $store_id = $store_product_detils['store_id'];
            $unavailable_products = [];

            $store_details = StoreDetails::query()->where('id', $store_id)->first();
            if ($store_details != Null) {
                if ($next_status != 1) {
                    $service_status = ProviderServices::query()->where('id', $store_details->provider_service_id)->where('service_cat_id', $service_category_id)->where('status', 1)->first();
                    if ($service_status == Null) {
                        $next_status = 1;
                        $message = "store currently not available!";
                        $message_code = 69;
                        $unavailable_store[] = [
                            "type" => 0,
                            "store_id" => $store_id - 0,
                            "product_ids" => "",
                            "message" => $message,
                            "message_code" => $message_code - 0,
                        ];
                    }
                    if ($next_status != 1) {

                        $delivery_cost =  0;
                        if ($user_taken_type != 2) {
                            $address_lat = trim($address_lat_long[0]);
                            $address_long = trim($address_lat_long[1]);
                            //$check_near_delivery_address = StoreDetails::query()->select('store_details.id', 'providers.service_radius'
                            //    , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                            //    +COS( ' . $address_lat . ' * pi()/180)
                            //    * COS(store_details.address_lat * pi()/180)
                            //    * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
                            //)
                            //    ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                            //    ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                            //    ->where('provider_services.service_cat_id', $service_category_id)
                            //    ->where('store_details.id', $store_id)
                            //    ->havingRaw('providers.service_radius > distance')
                            //    ->first();
                            //if ($check_near_delivery_address == Null) {
                            //    $next_status = 1;
                            //    $message = "Your delivery address is out of store delivery range! Please change your delivery address.";
                            //    $message_code = 115;
                            //    $unavailable_store[] = [
                            //        "type" => 4,
                            //        "store_id" => $store_id - 0,
                            //        "product_ids" => "",
                            //        "message" => $message,
                            //        "message_code" => $message_code - 0,
                            //    ];
                            //}

                            $user_address = urlencode($address_lat.','.$address_long);
                            $origin = urlencode($store_details->address_lat . "," . $store_details->address_long);

                            $get_coordinates = $userClassapi->get_coordinates($origin, $user_address);
                            $coordinates_check = json_decode($get_coordinates, TRUE);
                            if ($coordinates_check == 0) {
                                $next_status = 1;
                                $message = "Your delivery address is out of store delivery range! Please change your delivery address.";
                                $message_code = 115;
                                $unavailable_store[] = [
                                    "type" => 4,
                                    "store_id" => $store_id - 0,
                                    "product_ids" => "",
                                    "message" => $message,
                                    "message_code" => $message_code - 0,
                                ];
                            }
                            $total_distance = 0;
                            $total_mins = 0;
                            if (count($coordinates_check['routes']) > 0) {
                                $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);
                                $total_distance = ($total_distance * 0.001);
                            }
                            if ($total_distance > 0 ) {
                                $check_near_delivery_address = Provider::query()->where("id","=",$service_status->provider_id)->where('providers.service_radius', '<',$total_distance)->first();
                                if ($check_near_delivery_address != Null) {
                                    $next_status = 1;
                                    $message = "Your delivery address is out of store delivery range! Please change your delivery address.";
                                    $message_code = 115;
                                    $unavailable_store[] = [
                                        "type" => 4,
                                        "store_id" => $store_id - 0,
                                        "product_ids" => "",
                                        "message" => $message,
                                        "message_code" => $message_code - 0,
                                    ];
                                }
                            }
                            $delivery_cost = $userClassapi->checkDeliveryCharge($total_distance,$service_category_id);
                        }
                        $delivery_cost_min = 0;
                        $delivery_charge = $delivery_cost + $delivery_cost_min;

                        $schedule_order_type = 0;
                        $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')->where('store_id', $store_details->id)->where('day', 'all')->first();
                        if ($store_time == Null) {
                            if ($request->get('schedule_date_time') != Null) {
                                $schedule_order_type = 1;
                                $order_date_time = date('Y-m-d H:i:s', strtotime($request->get('schedule_date_time')));
                            } else {
                                $order_date_time = date('Y-m-d H:i:s');
                            }
                            $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')->where('store_id', $store_details->id)->where('day', substr(date('l', strtotime($order_date_time)), 0, 3))->first();
                        }
                        $time = date('H:i:s', strtotime($order_date_time));
                        if ($store_time != Null) {
                            if (($time >= $store_time->store_open_time && $time <= $store_time->store_close_time)) {
                                //if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time) {
                                $store_time_status = 1; //"open"
                            } else {
                                $store_time_status = 0; //"close"
                            }
                        } else {
                            $store_time_status = 0; //"close"
                        }
                        if ($schedule_order_type != 1) {
                            if ($store_time_status == 1) {
                                if ($service_status->current_status == 1) {
                                    $store_time_status = 1; //"close"
                                } else {
                                    $store_time_status = 0; //"close"
                                }
                            }
                        }
                        if ($store_time_status == 0) {
                            $next_status = 1;
                            $message = "store currently not available!";
                            $message_code = 69;
                            $unavailable_store[] = [
                                "type" => 0,
                                "store_id" => $store_id - 0,
                                "product_ids" => "",
                                "message" => $message,
                                "message_code" => $message_code - 0,
                            ];
                        }
                        if ($next_status != 1) {

                            $product_price = [];
                            $order_product_list = [];
                            $order_product_list_string = [];
                            $discount_cost = 0;
                            $total_price = 0;

                            $product_list = $store_product_detils['product_list'];
                            foreach ($product_list as $product) {
                                if ($product['product_id'] != Null && $product['product_quantity'] != Null) {
                                    $product_details = StoreProductDetails::query()->where('id', $product['product_id'])->where('status', 1)->first();
                                    if ($product_details != Null) {
                                        if ($product['product_quantity'] != Null) {

                                            $get_product_tax = 0;
                                            $quantity_price = 0;
                                            $total_product_discount = 0;
                                            $quantity = 0;
                                            $order_product_quantity = [];

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
                                                            Session::flash('error', 'You not selected valid size!');
                                                            return redirect()->back();
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
                                                }
                                                else {
                                                    $size_get = StoreProductSize::query()->where('product_id', $product_details->id)->get();
                                                    if (count($size_get) > 2) {
                                                        Session::flash('error', 'You not selected valid size!');
                                                        return redirect()->back();
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
                                                $order_product_list[] = [
                                                    'product_id' => $product_details->id,
                                                    'product_name' => $product_details->name,
                                                    'food_type' => $product_details->food_type,
                                                    'product_category' => $product_category != Null ? $product_category->name : Null,
                                                    'product_quantity' => $product_quantity['quantity'] - 0,
                                                    'product_amount' => $price_for_one,
                                                    'product_discount' => $discount_amount,

                                                    'size' => $size_data,
                                                    'options' => $options_data,
                                                    'toppings' => $toppings_data
                                                ];
                                            }
                                            array_push($order_product_list_string, $product_details->name . ' x ' . $quantity);
                                        }
                                    }
                                    else {
                                        $unavailable_products[] = $product['product_id'];
                                    }
                                } else {
                                    $unavailable_products[] = $product['product_id'];
                                }
                            }
                            //unavailalbe product
                            $unavailable_product_list = implode(',',$unavailable_products);
                            if(count($order_product_list) > 0) {
                                if ($store_details->order_min_amount > $total_price) {
                                    $req_store_order_min_amount = round(($store_details->order_min_amount), 2) - 0;
                                    $next_status = 1;
                                    $message = "Order min amount " . $req_store_order_min_amount;
                                    $message_code = 127;
                                    $unavailable_store[] = [
                                        "type" => 3,
                                        "store_id" => $store_id - 0,
                                        "product_ids" => "",
                                        "order_min_amount" => $req_store_order_min_amount,
                                        "message" => $message,
                                        "message_code" => $message_code - 0,
                                    ];
                                }
                                if ($next_status != 1) {
                                    if (!empty($unavailable_product_list)) {
                                        $unavailable_store[] = [
                                            "type" => 1,
                                            "store_id" => $store_id - 0,
                                            "product_ids" => !empty($unavailable_product_list) ? $unavailable_product_list : '',
                                            "message" => "The added product is not available at this moment!",
                                            "message_code" => 157,
                                        ];
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
                                    elseif ($store_details->offer_min_amount == 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
                                        if ($store_details->offer_type == 1) {
                                            $discount_cost = $total_price - (($total_price) - ($store_details->offer_amount));
                                        } else {
                                            $discount_cost = ((($total_price) * ($store_details->offer_amount)) / 100);
                                        }
                                    }
                                    else {
                                        $discount_cost = 0;
                                    }

                                    //$delivery_charge = 0;
                                    //if ($user_taken_type != 2) {
                                    //    $delivery_charge = 0;
                                    //}

                                    $item_cost = round($total_price, 2);

                                    $promo_code_discount = 0;
                                    if ($promocode_id != 0) {
                                        $discount_on_amount = (($total_price - $discount_cost));
                                        $promo_code = $userClassapi->checkPromoCodeValid($promocode_id, $discount_on_amount, $user_details->id, $service_category_id);
                                        if ($promo_code != 0) {
                                            if ($promo_code != 1) {
                                                $get_prmocode = PromocodeDetails::query()->where('id', $promocode_id)->where('status', 1)->first();
                                                if ($get_prmocode != Null) {
                                                    $promo_code_discount = $promo_code;

                                                }
                                            }
                                        }
                                    }

                                    $promocode_discount = $promo_code_discount;

                                    $packaging_cost = $store_details->packaging_charges;
                                    $delivery_cost = $delivery_charge;

                                    $step_1 = $item_cost - $discount_cost;
                                    $step_2 = $step_1 + $packaging_cost;
                                    $step_3 = $step_2 - $promocode_discount;
                                    $step_4 = $step_3 + $delivery_cost;
                                    $step_5 = (($step_4 * $tax_cost) / 100);
                                    $step_6 = $step_4 + $step_5;

                                    $order_tax_cost = round($step_5, 2);
                                    $total_pay = round($step_6, 2);
                                    $store_amount = $step_2 + $discount_cost;
                                    $commission = (($store_amount * $admin_commission) / 100);
                                    $store_amount = round(($store_amount - ($commission) - $discount_cost), 2);
                                    $order_admin_commission = round(($commission - $promocode_discount), 2);

                                    $req_store_ids[] = $store_details->id;
                                    $order_book_array[] = [
                                        "user_id" => $user_id,
                                        "store_id" => $store_details->id,
                                        "service_cat_id" => $get_category->id,
                                        "delivery_people_id" => Null,
                                        "order_no" => random_int(1, 9) . date('siHYdm') . random_int(1, 9),
                                        "order_type" => $place_order_type, //scheduled or now
                                        "order_date_time" => $place_order_date_time, //order time scheduled or now
                                        "total_item_cost" => $item_cost,
                                        "discount_cost" => $discount_cost,
                                        "tax_cost" => $order_tax_cost,
                                        "delivery_cost" => $delivery_charge,
                                        "packaging_cost" => $store_details->packaging_charges,
                                        //"refer_discount" => 0,
                                        "total_pay" => $total_pay,
                                        "store_amount" => $store_amount,
                                        "admin_commission" => $order_admin_commission,
                                        "delivery_admin_commission" => 0,
                                        "status" => 1,
                                        "order_delivery_status" => 0,
                                        "store_name" => $store_details->name,
                                        "user_name" => $user_details['first_name'] . ' ' . $user_details['last_name'],
                                        "delivery_address" => isset($address->address) ? $address->address : "",
                                        "lat_long" => isset($address->lat_long) ? $address->lat_long : "",
                                        "flat_no" => isset($address->flat_no)? $address->flat_no : "",
                                        "landmark" => isset($address->landmark)? $address->landmark : "",
                                        "delivery_people_name" => Null,
                                        "payment_type" => $payment_type,
                                        "payment_status" => $payment_type == 1 ? 1 : 0,
                                        "promo_code" => $promo_code_discount,
                                        "cancel_by" => Null,
                                        "cancel_reason" => Null,
                                        "cancel_charge" => 0,
                                        "refund_amount" => 0,
                                        "user_refund_status" => 0,
                                        "order_product_list" => implode(", ", $order_product_list_string),
                                        "user_rating_status" => 0,
                                        "driver_pay_settle_status" => 0,
                                        "store_pay_settle_status" => 0,
                                        "prescription" => NULL,
                                        "additional_info" => $store_product_detils['additional_info'] != Null ? $store_product_detils['additional_info'] : Null,
                                        "otp" => Null,
                                        "other_comment" => Null,
                                        "store_order_product_list" => $order_product_list,
                                    ];
                                }
                            } else {
                                $message = "store currently not available!";
                                $message_code = 69;
                                $unavailable_store[] = [
                                    "type" => 2,
                                    "store_id" => $store_id - 0,
                                    "product_ids" => !empty($unavailable_product_list) ? $unavailable_product_list : '',
                                    "message" => $message,
                                    "message_code" => $message_code - 0,
                                ];
                            }
                        }
                    }
                }
            } else {
                $message = "store currently not available!";
                $message_code = 69;
                $unavailable_store[] = [
                    "type" => 0,
                    "store_id" => $store_id - 0,
                    "product_ids" => "",
                    "message" => $message,
                    "message_code" => $message_code - 0,
                ];
            }
        }

        //unavailable store list
        //$unavailable_store_list = implode(',', $unavailable_store);
        $unavailable_store_list = $unavailable_store;
        if (count($order_book_array) == 0 || $unavailable_store_list != Null) {
            Session::flash('error', $unavailable_store_list[0]['message']);
//            Session::flash('error', 'The added stores are not available at the moment!');
            return redirect()->back();
            //return response()->json([
            //    "status" => 0,
            //    "message" => "The added stores are not available at the moment!",
            //    "message_code" => 157,
            //    'unavailable_stores' => !empty($unavailable_store_list) ? $unavailable_store_list : []
            //]);
        }

        $count_req_store_ids= count($req_store_ids);
        $prescription_images = [];
        $save_prescription = [];
        if ($service_category_id == $this->medicineDelivery) {
            if ($request->has("prescription")){
                $get_prescription_img_count = count($request->file('prescription'));
                $get_prescription_store_ids = array_map('trim', explode(",", $request->get('prescription_id_list')));
                $count_prescription_store_id = count($get_prescription_store_ids);
                if ($count_prescription_store_id == $get_prescription_img_count) {
                    if ($count_req_store_ids <= $get_prescription_img_count) {
                        if ($request->hasfile('prescription')) {
                            foreach ($request->file('prescription') as $key => $image) {
                                $destinationPath = public_path('/assets/images/prescription-images/');

                                $img = Image::make($image->getRealPath());
                                $img->orientate();
                                $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $image->getClientOriginalExtension();
                                $img->resize(300, 300, function ($constraint) {
                                    $constraint->aspectRatio();
                                })->save($destinationPath . $file_new);
                                $prescription_images[] = $file_new;
                            }
                        }
                        $count_prescription_images_list = count($prescription_images);
                        if ($count_prescription_images_list == $count_prescription_store_id) {
                            foreach ($req_store_ids as $key => $prc_store_id) {
                                if (in_array($prc_store_id, $get_prescription_store_ids)) {
                                    $key_prc_id = array_search($prc_store_id, $get_prescription_store_ids);
                                    $save_prescription[$prc_store_id] = $prescription_images[$key_prc_id];
                                }
                            }
                            foreach ($prescription_images as $store_image) {
                                if (!in_array($store_image, $save_prescription)) {
                                    if (\File::exists(public_path('/assets/images/prescription-images/' . $store_image))) {
                                        \File::delete(public_path('/assets/images/prescription-images/' . $store_image));
                                    }
                                }
                            }
                        } else {
                            foreach ($prescription_images as $prescription) {
                                if (\File::exists(public_path('/assets/images/prescription-images/' . $prescription))) {
                                    \File::delete(public_path('/assets/images/prescription-images/' . $prescription));
                                }
                            }
                            Session::flash('error', 'Please upload all required prescription images');
                            return redirect()->back();
                        }
                    } else {
                        Session::flash('error', 'Please upload all required prescription images');
                        return redirect()->back();
                    }
                } else {
                    Session::flash('error', 'Prescription upload images list not match');
                    return redirect()->back();
                }
            }
        }

        //count promocode discount
        //$promo_discount_cost = 0;
        //$add_promocode_id = 0;
        //$all_total_item_cost = ($all_total_item_cost);
        //$all_discount_cost = round($all_discount_cost, 2);
        //if ($promocode_id != 0) {
        //    $discount_on_amount = (($all_total_item_cost - $all_discount_cost));
        //    $promo_code = $userClassapi->checkPromoCodeValid($promocode_id, $discount_on_amount, $user_details->id, $service_category_id, $store_city_id);
        //    if ($promo_code != 0) {
        //        if ($promo_code != 1) {
        //            $get_prmocode = PromocodeDetails::query()->where('id', $promocode_id)->where('status', 1)->where('city_id', $store_city_id)->first();
        //            if ($get_prmocode != Null) {
        //                $add_promocode_details = new UsedPromocodeDetails();
        //                $add_promocode_details->service_cat_id = $service_category_id;
        //                $add_promocode_details->user_id = $user_id;
        //                $add_promocode_details->promocode_id = $promocode_id;
        //                $add_promocode_details->promocode_name = $get_prmocode->promo_code;
        //                $add_promocode_details->discount_amount = $promo_code;
        //                $add_promocode_details->save();
        //                $add_promocode_id = $add_promocode_details->id;
        //                $promo_discount_cost = $promo_code;
        //                //$place_order->promo_code = $add_promocode_details->id;
        //                //$place_order->save();
        //            }
        //        }
        //    }
        //}


        //book order on store wise
        $user_product_booking_ids = [];
        foreach($order_book_array as $key => $order_book_details) {
            //book order on store wise
            $place_order = new UserProductBooking();
            $place_order->user_id = $order_book_details['user_id'];
            $place_order->store_id = $order_book_details['store_id'];
            $place_order->service_cat_id = $order_book_details['service_cat_id'];
            $place_order->delivery_people_id = $order_book_details['delivery_people_id'];
            $place_order->order_no = $order_book_details['order_no'];
            $place_order->order_type = $order_book_details['order_type'];
            $place_order->order_date_time = $order_book_details['order_date_time'];
            $place_order->total_item_cost = $order_book_details['total_item_cost'];
            $place_order->discount_cost = $order_book_details['discount_cost'];
            $place_order->tax_cost = $order_book_details['tax_cost'];
            $place_order->delivery_cost = $order_book_details['delivery_cost'];
            $place_order->packaging_cost = $order_book_details['packaging_cost'];
            //$place_order->refer_discount = $order_book_details['refer_discount'];
            $place_order->total_pay = $order_book_details['total_pay'];
            $place_order->store_amount = $order_book_details['store_amount'];
            $place_order->admin_commission = $order_book_details['admin_commission'];
            $place_order->delivery_admin_commission = $order_book_details['delivery_admin_commission'];
            $place_order->status = $order_book_details['status'];
            $place_order->order_delivery_status = $order_book_details['order_delivery_status'];
            $place_order->store_name = $order_book_details['store_name'];
            $place_order->user_name = $order_book_details['user_name'];
            //if ($user_taken_type != 2) {
            $place_order->delivery_address = $order_book_details['delivery_address'];
            $place_order->lat_long = $order_book_details['lat_long'];
            $place_order->flat_no = $order_book_details['flat_no'];
            $place_order->landmark = $order_book_details['landmark'];
            //}
            $place_order->delivery_people_name = $order_book_details['delivery_people_name'];
            $place_order->payment_type = $order_book_details['payment_type'];
            $place_order->payment_status = $order_book_details['payment_status'];
            //$place_order->promo_code = $order_book_details['promo_code'];
            $place_order->cancel_by = $order_book_details['cancel_by'];
            $place_order->cancel_reason = $order_book_details['cancel_reason'];
            $place_order->cancel_charge = $order_book_details['cancel_charge'];
            $place_order->refund_amount = $order_book_details['refund_amount'];
            $place_order->user_refund_status = $order_book_details['user_refund_status'];
            $place_order->order_product_list = $order_book_details['order_product_list'];
            $place_order->user_rating_status = $order_book_details['user_rating_status'];
            $place_order->driver_pay_settle_status = $order_book_details['driver_pay_settle_status'];
            $place_order->store_pay_settle_status = $order_book_details['store_pay_settle_status'];
            //$place_order->prescription = $order_book_details['prescription'];
            $place_order->additional_request = $order_book_details['additional_info'];
            $place_order->otp = $place_order->generateOtp(4);
//            $place_order->otp = $order_book_details['otp'];
            /*$place_order->other_comment = $order_book_details['other_comment'];*/
            try{
                if ($service_category_id == $this->medicineDelivery) {
                    if ($save_prescription != Null) {
                        if (array_key_exists($order_book_details['store_id'], $save_prescription)) {
                            $place_order->prescription = $save_prescription[$order_book_details['store_id']];
                        }
                    }
                }
            }
            catch (\Exception $exception){}

            $place_order->save();
            if ($user_details->pending_refer_discount > 0) {
                $user_refer_history = UserReferHistory::query()->where('user_id', $user_details->id)->where('user_status', 0)->first();
                $total_price = $place_order->total_pay;
                if ($user_refer_history != Null) {
                    if ($user_refer_history->user_discount_type == 1) {
                        $refer_discount_price = round($total_price - $user_refer_history->user_discount, 2);
                        $refer_discount_amount = round($user_refer_history->user_discount, 2);
                    } else {
                        $refer_discount_price = round((($total_price * $user_refer_history->user_discount) / 100), 2);
                        $refer_discount_amount = round($refer_discount_price, 2);
                        $refer_discount_price = round($total_price - $refer_discount_amount, 2);
                    }
                    if ($refer_discount_price < 0) {
                        $refer_discount_price = 0;
                    }
                    $place_order->total_pay = $refer_discount_price;
                    $place_order->refer_discount = round($refer_discount_amount, 2);
                    $place_order->save();
                    $user_refer_history->user_status = 1;
                    $user_refer_history->save();
                    $user_details->pending_refer_discount = $user_details->pending_refer_discount - 1;
                    $user_details->save();
                } else {
                    $user_refer_history = UserReferHistory::query()->where('refer_id', $user_details->id)->where('refer_status', 0)->first();
                    if ($user_refer_history != Null) {
                        if ($user_refer_history->refer_discount_type == 1) {
                            $refer_discount_price = round($total_price - $user_refer_history->refer_discount, 2);
                            $refer_discount_amount = round($user_refer_history->refer_discount, 2);
                        } else {
                            $refer_discount_price = round((($total_price * $user_refer_history->refer_discount) / 100), 2);
                            $refer_discount_amount = round($refer_discount_price, 2);
                            $refer_discount_price = round($total_price - $refer_discount_amount, 2);
                        }
                        if ($refer_discount_price < 0) {
                            $refer_discount_price = 0;
                        }
                        $place_order->total_pay = $refer_discount_price;
                        $place_order->refer_discount = round($refer_discount_amount, 2);
                        $place_order->save();
                        $user_refer_history->refer_status = 1;
                        $user_refer_history->save();
                        $user_details->pending_refer_discount = $user_details->pending_refer_discount - 1;
                        $user_details->save();
                    }
                }
            }

            if ($order_book_details["promo_code"] > 0) {
                $get_prmocode = PromocodeDetails::query()->where('id', $promocode_id)->where('status', 1)->first();
                if ($get_prmocode != Null) {
                    $add_promocode_details = new UsedPromocodeDetails();
                    $add_promocode_details->service_cat_id = $service_category_id;
                    $add_promocode_details->user_id = $user_details->id;
                    $add_promocode_details->promocode_id = $promocode_id;
                    $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                    $add_promocode_details->discount_amount = $order_book_details["promo_code"];
                    $add_promocode_details->save();

                    $get_prmocode->usage_limit = $get_prmocode->usage_limit + 1 ;
                    $get_prmocode->save();

                    $place_order->promo_code = $add_promocode_details->id;
                    $place_order->save();
                }
            }

            $user_product_booking_ids[] = $place_order->id;
            $store_order_product_list = $order_book_details['store_order_product_list'];

            if ($store_order_product_list != Null) {
                foreach ($store_order_product_list as $product) {
                    $add_product = new UserProductBookingQuantity();
                    $add_product->order_id = $place_order->id;
                    $add_product->product_id = $product['product_id'];
                    $add_product->product_name = $product['product_name'];
                    $add_product->food_type = $product['food_type'];
                    $add_product->product_cat_name = $product['product_category'];
                    $add_product->num_of_items = $product['product_quantity'];
                    $add_product->price_for_one = $product['product_amount'];
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

            $product_status = new UserProductBookingStatus();
            $product_status->order_id = $place_order->id;
            $product_status->status = 1;
            $product_status->save();

        }

        foreach ($user_product_booking_ids as $key => $product_booking_id) {
            $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
            if ($placed_order != Null) {
                $store_provider_details = Provider::query()->select('providers.device_token', 'providers.login_device', 'providers.email', 'providers.language')
                    ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                    ->join('store_details', 'store_details.provider_service_id', '=', 'provider_services.id')
                    ->where('store_details.id', $placed_order->store_id)
                    ->whereNotNull('providers.device_token')
                    ->whereNotNull('providers.access_token')
                    ->first();
                if ($store_provider_details != Null) {
                    $this->notificationClass->storeDeliveryUserNotification($placed_order->id, $store_provider_details->device_token, $placed_order->status, $store_provider_details->login_device, $store_provider_details->language);
                }
            }
        }

        $order_cart = UserOrderCart::query()->where('order_add_by', 0)->where('user_id', $user_id)->delete();

        if (isset($general_settings) && $general_settings->is_send_twilio_sms == 1) {
            if ($general_settings->twilio_service_key == Null || $general_settings->twilio_auth_token == Null || $general_settings->twilio_contact_number == Null) {
                return response()->json([
                    "status" => 0,
                    "message" => "something went to wrong!",
                    "message_code" => 9,
                ]);
            }
            if (isset($user_details) && $user_details->country_code != null && $user_details->contact_number != null){
                $user_contact_number = $user_details->country_code.$user_details->contact_number;
                $route = route('get:track_order',$place_order->order_no);
                try {
                    $client = new Client($general_settings->twilio_service_key, $general_settings->twilio_auth_token);
                    $client->messages->create(
                        $user_contact_number,
                        array(
                            'from' => $general_settings->twilio_contact_number,
                            'body' => 'Admin create order for you check more details '.$route,
                        )
                    );
//                    return "success";
                } catch (\Exception $e) {
//                    return $e;
                }
            }
        }

        Session::flash('success', 'Order created successfully!');
        return redirect()->route('get:admin:store_service_dispatcher', ["slug" => $slug, "slug2" => "new"]);
    }

    public function getAdminRemoveCartDetails(Request $request){

        $user_id = $request->get('user_id');
        $slug = $request->get('slug');
        //$city_id = $request->get('city_id');
        $store_id = $request->get('store_id');
        if ($user_id == Null || $slug == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found!',
            ]);
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Service category not found!'
            ]);
        }
        $user_details = User::query()->where('id', $user_id)->first();
        if ($user_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'User not found!'
            ]);
        }

        $user_order_cart_list = UserOrderCart::query()->where('order_add_by', 0)->where('user_id', $user_id);
        if ($store_id > 0) {
            $user_order_cart_list->where('store_id', $store_id);
        }
        $user_order_cart_list->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
