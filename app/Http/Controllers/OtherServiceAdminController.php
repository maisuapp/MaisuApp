<?php

namespace App\Http\Controllers;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Http\Requests\OtherServicePackagesRequest;
use App\Http\Requests\OtherServiceProviderStoreRequest;
use App\Http\Requests\ProviderServiceRequest;
use App\model\OtherServiceCategory;
use App\model\OtherServiceProviderDetails;
use App\model\OtherServiceProviderPackages;
use App\model\OtherServiceProviderTimings;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderAcceptedPackageTime;
use App\model\ProviderBankDetails;
use App\model\ProviderDocuments;
use App\model\ProviderServices;
use App\model\RequiredDocuments;
use App\model\ServiceCategory;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserPackageBooking;
use App\model\UserPackageBookingQuantity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class OtherServiceAdminController extends Controller
{
    private $adminClass;

    private $package_status;
    private $order_status;
    private $is_restricted = 0;

    public function __construct(AdminClass $adminClass)
    {
        $this->middleware('auth');
        $this->adminClass = $adminClass;
        $this->package_status = ['', 'pending', 'approved', 'schedule-approved', 'rejected', 'cancelled', 'ongoing', 'arrived', 'processing', 'completed', 'failed'];
//        $this->order_status = [2, 3, 6, 7, 8];
        $this->order_status = [8];
        $this->middleware( function ($request, $next) {
            $is_restrict_admin = $request->get('is_restrict_admin');
            $this->is_restricted = $is_restrict_admin;
            return $next($request);
        });
    }

    public function getDashboard(Request $request)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $slug="";
        $provider_id = Auth::guard('on_demand')->user()->id;
        /*$provider_service = ProviderServices::query()->where('provider_id', Auth::guard('on_demand')->user()->id)->first();

        if ($provider_service == Null) {
            return redirect()->back();
        }*/
        $total_sales = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('status', 9)->count();
        $total_revenue = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('status', 9)->sum('provider_amount');
        $total_orders = UserPackageBooking::query()->where('provider_id','=',$provider_id)->count();
        $total_cancel_orders = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('status', 5)->count();
        $service_category = ProviderServices::query()
            ->select('service_category.name as service_name',
                'service_category.category_type',
                'service_category.slug',
                'service_category.icon_name',
                'service_category.id',
                'provider_services.service_cat_id',
                'provider_services.current_status',
                'provider_services.status',
                'provider_services.rejected_reason')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('service_category.status', '=', 1)
            ->where('provider_services.provider_id', '=', $provider_id)->get();
        $order_count[] = 0;
        foreach ($service_category as $key => $category) {
            $order_count[$category->id] = UserPackageBooking::query()->where('service_cat_id', $category->id)->where('provider_id',$provider_id)->count();
        }
        $view = view('admin.pages.other_services.other_service_dashboard', compact('total_cancel_orders','order_count','service_category','total_sales', 'total_revenue', 'total_orders'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
//        $slug="";
//        $provider_status = $this->adminClass->checkProviderStatus();
//
//        if ($provider_status != Null) {
//            return $provider_status;
//        }
//        $provider_id = Auth::guard('on_demand')->user()->id;
//
//        /*$provider_service = ProviderServices::query()->where('provider_id', Auth::guard('on_demand')->user()->id)->first();
//
//        if ($provider_service == Null) {
//            return redirect()->back();
//        }*/
//        $total_sales = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('status', 9)->count();
//        $total_revenue = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('status', 9)->sum('provider_amount');
//        $total_orders = UserPackageBooking::query()->where('provider_id','=',$provider_id)->count();
//
//        $view = view('admin.pages.other_services.provider.service_dashboard', compact('total_sales', 'total_revenue', 'total_orders', 'slug'));
//        if ($request->ajax()) {
//            $view = $view->renderSections();
//            return $this->adminClass->renderingResponce($view);
//        }
//        return $view;
    }

    public function getProviderServiceRegister(Request $request)
    {
        $service_category = ServiceCategory::query()->whereIn('category_type', [3, 4])->where('status', 1)->get();
        if ($service_category != Null) {
            if ($request->ajax()) {
                $view = view('admin.pages.other_services.other_service_register', compact('service_category'))->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return view('admin.pages.other_services.other_service_register', compact('service_category'));
        } else {
            Session::flash('error', 'Service Category Not Found!');
            return redirect()->back();
        }
    }

    public function postProviderServiceRegister(ProviderServiceRequest $request)
    {
        $provider = Provider::query()->where('id', Auth::guard('on_demand')->user()->id)->whereNull('providers.deleted_at')->first();
        if ($provider != Null) {
            $provider_status = $this->adminClass->checkProviderStatus();
            if ($provider_status != Null) {
                return $provider_status;
            }
            $provider_services = $request->get('provider_services');
            foreach ($provider_services as $provider_service) {
                $check_provider_service = ProviderServices::query()->where('provider_id', $provider->id)->where('service_cat_id', $provider_service)->first();
                if ($check_provider_service == Null) {
                    $add_service = new ProviderServices();
                    $add_service->provider_id = $provider->id;
                    $add_service->service_cat_id = $provider_service;
                    $add_service->current_status = 0;
                    $add_service->status = 0;
                    $add_service->save();
                    if ($provider->status == 3) {
                        $provider->status = 1;
                        $provider->save();
                    }
                }
            }
            $provider_details = OtherServiceProviderDetails::where('provider_id', $provider->id)->first();
            if ($provider_details == Null) {
                $provider_details = new OtherServiceProviderDetails();
                $provider_details->provider_id = $provider->id;
            }
            $provider_details->address = $request->get('address');
            $provider_details->lat = $request->get('address_lat');
            $provider_details->long = $request->get('address_long');

            $all_day_open_time = "09:00:00";
            $all_day_close_time = "19:00:00";
            $new_array = ["09:00 AM - 10:00 AM", "10:00 AM - 11:00 AM", "11:00 AM - 12:00 PM", "12:00 PM - 01:00 PM", "01:00 PM - 02:00 PM", "02:00 PM - 03:00 PM", "03:00 PM - 04:00 PM", "04:00 PM - 05:00 PM", "05:00 PM - 06:00 PM", "06:00 PM - 07:00 PM"];
            $provider_details->start_time = $all_day_open_time;
            $provider_details->end_time = $all_day_close_time;
            $provider_details->time_list = implode(',', $new_array);

            $days = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
            foreach ($days as $day) {
                $get_open_timing = OtherServiceProviderTimings::query()->where('provider_id', $provider->id)->where('day', $day)->first();
                if ($get_open_timing == Null) {
                    $get_open_timing = new OtherServiceProviderTimings();
                    $get_open_timing->provider_id = $provider->id;
                    $get_open_timing->day = strtoupper(trim($day));
                }
                $get_open_timing->open_time_list = $provider_details->time_list;
                $get_open_timing->save();
            }

            $provider_details->save();
            return redirect()->route('get:provider-admin:dashboard')->with("success", "Your service registered successfully!.");
        } else {
            Auth::logout();
            return redirect()->route('post:provider-admin:login')->with("error", "You are not register as on-demand provider.");
        }
    }

    public function getProviderServices(Request $request)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $services = ProviderServices::query()
            ->select('service_category.name as service_name',
                'service_category.category_type',
                'service_category.slug',
                'service_category.icon_name',
                'provider_services.id',
                'provider_services.service_cat_id',
                'provider_services.current_status',
                'provider_services.status',
                'provider_services.rejected_reason')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('service_category.status', '=', 1)
            ->where('provider_services.provider_id', '=', Auth::guard('on_demand')->user()->id)->get();
        if ($request->ajax()) {
            $view = view('admin.pages.other_services.provider.service_list', compact('services'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.other_services.provider.service_list', compact('services'));
    }

    public function getProviderAddServices(Request $request)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $provider_services = ProviderServices::query()->where('provider_id', Auth::guard('on_demand')->user()->id)->get()->pluck('service_cat_id')->toArray();
        $service_category_multiple = OtherServiceCategory::query()
            ->select('service_category.*')
            ->join('service_category', 'service_category.id', '=', 'other_service_sub_category.service_cat_id')
            ->whereNotIn('service_category.id', $provider_services)
            ->whereIn('service_category.category_type', [3, 4])
            ->where('service_category.status',1)->groupBy('other_service_sub_category.service_cat_id')->get();
        if ($request->ajax()) {
            $view = view('admin.pages.super_admin.provider.form_provider_service', compact('service_category_multiple'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.super_admin.provider.form_provider_service', compact('service_category_multiple'));
    }

    public function postProviderAddServices(Request $request)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $services = $request->get('provider_services');
        $provider_id = Auth::guard('on_demand')->user()->id;

        //update provider details complete status
        $provider = Provider::query()->where('id', $provider_id)->first();
        if ($provider != null && $provider->completed_step != 5) {
            $provider->completed_step = 5;
            $provider->save();
        }
        $status = 0;
        $provider_has_other_approved_service = ProviderServices::query()->where('provider_id',$provider_id)
            ->where('status',1)->first();
        if ($provider_has_other_approved_service == null) {
            $status = 0;
        } else {
            $status = 1;
        }
        foreach ($services as $service) {
            $check_duplicate = ProviderServices::query()->where('provider_id', $provider_id)->where('service_cat_id', $service)->first();
            $service_category = ServiceCategory::query()->where('id', $service)->first();
            if ($check_duplicate == Null && $service_category != Null) {
                $add_service = new ProviderServices();
                $add_service->provider_id = $provider_id;
                $add_service->service_cat_id = $service_category->id;
                $add_service->current_status = 0;
                $add_service->status = $status;
                $add_service->save();
                Session::flash('success', 'Service Added Successfully!');
            }
        }
        return redirect()->route('get:provider-admin:services');
    }

    public function postProviderDeleteService(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Other Service Package Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Something Went to Wrong!'
            ]);
        }
        $service = ProviderServices::query()->where('id', $id)->first();
        if ($service == Null) {
//            Session::flash('error', 'Other Service Package Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ]);
        }
        $order_status=['6','7','8'];
        $package_booking=UserPackageBooking::where('provider_id','=',$service->provider_id)
                                            ->where('service_cat_id','=',$service->service_cat_id)
                                            ->whereIn('status',$order_status)->get();

        if (count($package_booking) > 0) {
//            Session::flash('error', 'Other Service Package Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Order is running'
            ]);
        }
        ProviderServices::query()->where('id', $id)->delete();
//        Session::flash('error', 'Other Service Package remove successfully!');
        return response()->json([
            'success' => true
        ]);
    }

    public function getProviderUpdateServiceCurrentStatus(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
            Session::flash('error', 'something went to wrong!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $provider_service = ProviderServices::where('id', $id)->first();
        if ($provider_service == Null) {
            Session::flash('error', 'provider Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($provider_service->current_status == 1) {
            $provider_service->current_status = 0;
            $provider_service->save();
//            Session::flash('success', $service_sub_category->name . ' category disabled successfully!');
        } else {
            $provider_service->current_status = 1;
            $provider_service->save();
//            Session::flash('success', $service_sub_category->name . ' category enable successfully!');
        }
        return response()->json([
            'success' => true,
            'status' => $provider_service->current_status
        ]);
    }

    public function getProviderServiceDashboard(Request $request, $slug)
    {

        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            return redirect()->back();
        }
        $provider_id = Auth::guard('on_demand')->user()->id;
        $provider_service = ProviderServices::query()->where('provider_id', Auth::guard('on_demand')->user()->id)->where('service_cat_id', $service_category->id)->first();
        if ($provider_service == Null) {
            return redirect()->back();
        }

//        $total_sales = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('service_cat_id', $service_category->id)->where('status', 9)->count();
//        $total_revenue = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('service_cat_id', $service_category->id)->where('status', 9)->sum('provider_amount');
//        $total_orders = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('service_cat_id', $service_category->id)->count();

        $total_sales = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('service_cat_id', $service_category->id)->where('status', 9)->count();
        $total_revenue = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('service_cat_id', $service_category->id)->where('status', 9)->sum('provider_amount');
        $total_orders = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('service_cat_id', $service_category->id)->count();
        $total_cancel_orders = UserPackageBooking::query()->where('provider_id','=',$provider_id)->where('service_cat_id', $service_category->id)->where('status', 5)->count();
        $service_category = ProviderServices::query()
            ->select('service_category.name as service_name',
                'service_category.category_type',
                'service_category.slug',
                'service_category.icon_name',
                'service_category.id',
                'provider_services.service_cat_id',
                'provider_services.current_status',
                'provider_services.status',
                'provider_services.rejected_reason')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('service_category.status', '=', 1)
            ->where('provider_services.provider_id', '=', $provider_id)->where('service_cat_id', $service_category->id)->get();
        $order_count[] = 0;
        foreach ($service_category as $key => $category) {
            $order_count[$category->id] = UserPackageBooking::query()->where('service_cat_id', $category->id)->where('provider_id',$provider_id)->count();
        }
        $view = view('admin.pages.other_services.provider.service_dashboard', compact('total_cancel_orders','order_count','total_sales', 'total_revenue', 'total_orders', 'slug'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getProviderServicePackageList(Request $request, $slug)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }

        $package_list = OtherServiceProviderPackages::query()->select('other_service_provider_packages.id',
            'other_service_sub_category.name as sub_cat_name',
            'other_service_provider_packages.name as package_name',
            'other_service_provider_packages.max_book_quantity',
            'other_service_provider_packages.price',
            'other_service_provider_packages.status')
            ->join('other_service_sub_category', 'other_service_sub_category.id', '=', 'other_service_provider_packages.sub_cat_id')
            ->join('provider_services', 'provider_services.id', '=', 'other_service_provider_packages.provider_service_id')
            ->join('service_category', 'service_category.id', '=', 'other_service_provider_packages.service_cat_id')
            ->where('provider_services.provider_id', Auth::guard('on_demand')->user()->id)
            ->where('service_category.slug', $slug)
            ->get();
        if (!$package_list->isEmpty()) {
            $view = view('admin.pages.other_services.packages.manage', compact('package_list', 'slug'));
        } else {
//            Session::flash('error', 'category not found!');
            $view = view('admin.pages.other_services.packages.manage', compact('slug'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getProviderServiceAddPackage(Request $request, $slug)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $service_category = ServiceCategory::select('id', 'name')->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'category not found!');
            return redirect()->back();
        }
        $provider_id = Auth::guard('on_demand')->user()->id;
        $service_category_list = OtherServiceCategory::select('id', 'name', 'status')->where('service_cat_id', $service_category->id)->get();
        if ($request->ajax()) {
            $view = view('admin.pages.other_services.packages.form', compact('slug', 'service_category', 'service_category_list', 'provider_id'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.other_services.packages.form', compact('slug', 'service_category', 'service_category_list', 'provider_id'));
    }

    public function getProviderServiceEditPackage(Request $request, $slug, $id)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $package = OtherServiceProviderPackages::where('id', $id)->first();
        $service_category = ServiceCategory::select('id', 'name')->where('slug', $slug)->first();
        if($package != Null)
        {
            $service_category_list = OtherServiceCategory::select('id', 'name', 'status')->where("service_cat_id",$package->service_cat_id)->get();
        }else{
            Session::flash('error', 'sub category not found!');
            return redirect()->back();
        }
        if ($package != Null) {
            $view = view('admin.pages.other_services.packages.form', compact('package', 'service_category', 'service_category_list', 'slug'));
        } else {
            Session::flash('error', 'category not found!');
            $view = view('admin.pages.other_services.packages.form', compact('slug', 'service_category', 'service_category_list'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getProviderServiceUpdatePackage(OtherServicePackagesRequest $request, $slug)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }

        //update provider details complete status
        $provider_data =  Provider::query()->where('id','=',$request->get('provider_id'))->whereNull('providers.deleted_at')->first();
        if($provider_data != Null){
            if($provider_data->completed_step == 2)
            {
                $provider_data->completed_step=3;
            }
            $provider_data->save();
        }

        if ($request->get('id') == Null) {
            if ($request->get('provider_id') != Null) {
                $service_category = ServiceCategory::where('slug', $slug)->first();
                if ($service_category != Null) {
                    $provider_service = ProviderServices::where('provider_id', $request->get('provider_id'))->where('service_cat_id', $service_category->id)->first();
                    if ($provider_service != Null) {
                        $add_package = new OtherServiceProviderPackages();
                        $add_package->provider_service_id = $provider_service->id;
                        $add_package->sub_cat_id = $request->get('category');
                        $add_package->service_cat_id = $service_category->id;
                        $add_package->name = $request->get('package_name');
                        $add_package->description = $request->get('description');
                        $add_package->price = $request->get('package_price');
                        $add_package->max_book_quantity = $request->get('max_book_quantity');
                        $add_package->status = 1;
                        $add_package->save();
                        Session::flash('success', 'package add successfully!');
                        return redirect()->route('get:provider-admin:service-package-list', [$service_category->slug]);
                    } else {
                        Session::flash('error', 'something went to wrong!');
                        return redirect()->back();
                    }
                } else {
                    Session::flash('error', 'something went to wrong!');
                    return redirect()->back();
                }
            } else {
                Session::flash('error', 'something went to wrong!');
                return redirect()->back();
            }
        } else {
            $edit_package = OtherServiceProviderPackages::where('id', $request->get('id'))->first();
            if ($edit_package == Null) {
                Session::flash('error', 'something went to wrong!');
                return redirect()->back();
            }
            $service_category = ServiceCategory::where('slug', $slug)->first();
            if ($service_category == Null) {
                Session::flash('error', 'something went to wrong!');
                return redirect()->back();
            }
            $edit_package->service_cat_id = $service_category->id;
            $edit_package->name = $request->get('package_name');
            $edit_package->description = $request->get('description');
            $edit_package->price = $request->get('package_price');
            $edit_package->max_book_quantity = $request->get('max_book_quantity');
            $edit_package->status = $request->get('status');
            $edit_package->save();
            Session::flash('success', 'package updated successfully!');
            return redirect()->route('get:provider-admin:service-package-list', [$service_category->slug]);
        }
    }

    public function getProviderServiceUpdatePackageStatus(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
            Session::flash('error', 'Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $service_package = OtherServiceProviderPackages::where('id', $id)->first();
        if ($service_package == Null) {
            Session::flash('error', 'Service Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($service_package->status == 1) {
            $service_package->status = 0;
            $service_package->save();
//            Session::flash('success', $service_sub_category->name . ' category disabled successfully!');
        } else {
            $service_package->status = 1;
            $service_package->save();
//            Session::flash('success', $service_sub_category->name . ' category enable successfully!');
        }
        return response()->json([
            'success' => true,
            'status' => $service_package->status
        ]);
    }

    public function getProviderServiceDeletePackage(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
            Session::flash('error', 'Other Service Package Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $package = OtherServiceProviderPackages::query()->where('id', $id)->first();
        if ($package == Null) {
            Session::flash('error', 'Other Service Package Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        OtherServiceProviderPackages::query()->where('id', $id)->delete();
//        Session::flash('error', 'Other Service Package remove successfully!');
        return response()->json([
            'success' => true
        ]);
    }

    public function getProviderOtherServiceOrderList(Request $request, $slug, $status)
    {

        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();

        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        if ($status == "all") {
            $status = [];
        } elseif ($status == "pending") {
            $status = [1];
        } elseif ($status == "approved") {
            $status = [2, 3];
        } elseif ($status == "rejected") {
            $status = [4];
        } elseif ($status == "ongoing") {
            $status = [6, 7, 8];
        } elseif ($status == "completed") {
            $status = [9];
        } elseif ($status == "cancelled") {
            $status = [5, 10];
        } else {
            $status = [];
        }
        $provider_details = Provider::select('id', 'first_name')->where('id', Auth::guard('on_demand')->user()->id)->whereNull('providers.deleted_at')->first();
        if ($provider_details == Null) {
            return redirect()->back();
        }
        if (count($status) > 0) {
            $order_list = UserPackageBooking::query()->select('id', 'user_name', 'order_package_list', 'total_pay', 'status')->where('service_cat_id', $service_category->id)->where('provider_id', $provider_details->id)->whereIN('status', $status)->orderBy('id','desc')->get();
        } else {
            $order_list = UserPackageBooking::query()->select('id', 'user_name', 'order_package_list', 'total_pay', 'status')->where('service_cat_id', $service_category->id)->where('provider_id', $provider_details->id)->orderBy('id','desc')->get();
        }
        $order_status = $this->package_status;
        if ($request->ajax()) {
            $view = view('admin.pages.other_services.other_service_order_list', compact('slug', 'order_list', 'order_status', 'provider_details','service_category'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.other_services.other_service_order_list', compact('slug', 'order_list', 'order_status', 'provider_details','service_category'));
    }

    public function getProviderOtherServiceOrderDetails(Request $request, $slug, $order_id)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $service_category = ServiceCategory::select('id', 'name')->where('slug', $slug)->first();
        if ($service_category == Null) {
            return redirect()->back();
        }
        $orders_details = UserPackageBooking::query()->select('user_service_package_booking.id', 'user_service_package_booking.order_no',
            'user_service_package_booking.user_name', 'user_service_package_booking.provider_name', 'user_service_package_booking.booking_time_zone',
            'user_service_package_booking.service_date_time',
            'user_service_package_booking.total_item_cost',
            'user_service_package_booking.delivery_address',
            'user_service_package_booking.tax',
            'user_service_package_booking.order_type',
            'user_service_package_booking.service_date',
            'user_service_package_booking.service_time',
            'user_service_package_booking.created_at',
            'user_service_package_booking.book_start_time',
            'user_service_package_booking.book_end_time',
            'user_service_package_booking.refer_discount',
            'user_service_package_booking.remark',
            'user_service_package_booking.total_pay', 'user_service_package_booking.status',
            'used_promocode_details.discount_amount as promo_code_discount',
            'used_promocode_details.promocode_name as promo_code_name'
            , 'users.contact_number'
            ,'user_service_package_booking.cancel_by'
            ,'user_service_package_booking.cancel_reason'
            , 'user_service_package_booking.payment_type'
            , 'user_service_package_booking.payment_status'
            , 'user_service_package_booking.refund_amount', 'user_service_package_booking.cancel_charge', 'user_service_package_booking.user_refund_status'
        )
            ->join('users', 'users.id', '=', 'user_service_package_booking.user_id')
            ->join('service_category', 'service_category.id', '=', 'user_service_package_booking.service_cat_id')
            ->leftJoin('used_promocode_details', 'used_promocode_details.id', 'user_service_package_booking.promo_code')
            ->where('user_service_package_booking.id', $order_id)
            ->where('service_category.slug', $slug)
            ->whereNull('users.deleted_at')
            ->first();
        $orders_status = "----";
        $orders_status_array = $this->order_status;
        if ($orders_details != Null) {
            $package_list = UserPackageBookingQuantity::select('num_of_items', 'package_name', 'sub_category_name', 'price_for_one')
                ->where('order_id', $orders_details->id)
                ->get();
            $total_package_cost = 0;
            foreach ($package_list as $key => $package) {
                $total_package_item_cost = round($package->price_for_one * $package->num_of_items, 2);
                $total_package_cost = $total_package_cost + $total_package_item_cost;
            }
            if ($orders_details->status == 1) {
                $orders_status = "Pending";
            } elseif ($orders_details->status == 2 || $orders_details->status == 3) {
                $orders_status = "Confirmed";
            } elseif ($orders_details->status == 4) {
                $orders_status = "Rejected";
            } elseif ($orders_details->status == 5) {
                $orders_status = "Cancelled";
            } elseif ($orders_details->status == 6) {
                $orders_status = "Ongoing";
            } elseif ($orders_details->status == 7) {
                $orders_status = "Arrived";
            } elseif ($orders_details->status == 8) {
                $orders_status = "Processing";
            } elseif ($orders_details->status == 9) {
                $orders_status = "Completed";
            } elseif ($orders_details->status == 10) {
                $orders_status = "Failed";
            }
            $view = view('admin.pages.other_services.other_service_order_details', compact('slug', 'orders_status', 'orders_status_array', 'orders_details', 'package_list'));
        } else {
            $view = view('admin.pages.other_services.other_service_order_details', compact('slug', 'orders_status', 'orders_status_array'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getProviderOtherServiceDocument(Request $request, $slug)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }

        $service_category = ServiceCategory::select('id', 'name')->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Something went to wrong!');
            return redirect()->back();
        }

        $provider_service_details = ProviderServices::query()->where('provider_id', Auth::guard('on_demand')->user()->id)->where('service_cat_id', $service_category->id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Something went to wrong!');
            return redirect()->back();
        }

        $required_documents = RequiredDocuments::query()
            ->where('service_cat_id', $service_category->id)
            ->where('status',1)
            ->get();
        $provider_documents=[];

        foreach ($required_documents as $key => $required_document) {
            $document_details = ProviderDocuments::where('provider_service_id', '=', $provider_service_details->id)->where('req_document_id', $required_document->id)->first();
            if ($required_document->service_cat_id == $service_category->id) {
                $provider_documents[$key] = $document_details;
            } else {
                $provider_documents[$key] = Null;
            }
        }
        if ($request->ajax()) {
            $view = view('admin.pages.other_services.document.form', compact('required_documents', 'provider_documents', 'service_category', 'slug'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.other_services.document.form', compact('required_documents', 'provider_documents', 'service_category', 'slug'));
    }

    public function postProviderOtherServiceDocument(Request $request, $slug)
    {
        $service_category = ServiceCategory::select('id', 'name')->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Something went to wrong!');
            return redirect()->back();
        }
        $provider_service_details = ProviderServices::query()->where('provider_id', Auth::guard('on_demand')->user()->id)->where('service_cat_id', $service_category->id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Something went to wrong!');
            return redirect()->back();
        }
        $id = $request->get('id');
        $get_document = RequiredDocuments::where('id', $id)->where('service_cat_id', $service_category->id)->first();
        if ($get_document != Null) {
            $find_document = ProviderDocuments::where('provider_service_id', $provider_service_details->id)->where('req_document_id', $get_document->id)->first();
            if ($find_document != Null) {
                if ($request->file('document_file')) {
                    if (\File::exists(public_path('/assets/images/provider-documents/' . $find_document->file_name))) {
                        \File::delete(public_path('/assets/images/provider-documents/' . $find_document->file_name));
                    }
                    $image = $request->file('document_file');
                    $destinationPath = public_path('/assets/images/provider-documents/');
                    $img = Image::make($image->getRealPath());
                    $img->orientate();
                    $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $image->getClientOriginalExtension();
                    $img->resize(500, 500, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath . $file_new);
                    $find_document->req_document_id = $get_document->id;
                    $find_document->document_file = $file_new;
                    $find_document->status = 0;
                    $find_document->save();
                } else Session::flash('error','Please provider an documents');
                Session::flash('success', $get_document->name . ' Updated Successfully!');
            } else {
                if ($request->file('document_file')) {
                    $image = $request->file('document_file');
                    $destinationPath = public_path('/assets/images/provider-documents/');
                    $img = Image::make($image->getRealPath());
                    $img->orientate();
                    $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $image->getClientOriginalExtension();
                    $img->resize(500, 500, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath . $file_new);
                    $documents = new ProviderDocuments();
                    $documents->provider_service_id = $provider_service_details->id;
                    $documents->req_document_id = $get_document->id;
                    $documents->document_file = $file_new;
                    $documents->status = 0;
                    $documents->save();
                    Session::flash('success', $get_document->name . ' Upload Successfully!');
                } else Session::flash('error','Please provider an documents');
            }
        } else {
            Session::flash('error', 'Document Name Not Found!');
        }
        return redirect()->route('get:provider-admin:other_service_provider_document', $slug);
    }

    public function getProviderOtherServiceAllOrderList(Request $request, $status)
    {

        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        if ($status == "all") {
            $status = [];
        } elseif ($status == "pending") {
            $status = [1];
        } elseif ($status == "approved") {
            $status = [2, 3];
        } elseif ($status == "rejected") {
            $status = [4];
        } elseif ($status == "ongoing") {
            $status = [6, 7, 8];
        } elseif ($status == "completed") {
            $status = [9];
        } elseif ($status == "cancelled") {
            $status = [5, 10];
        } else {
            $status = [];
        }
        $provider_details = Provider::query()->select('id', 'first_name')->where('id', Auth::guard('on_demand')->user()->id)->whereNull('providers.deleted_at')->first();
        if ($provider_details == Null) {
            return redirect()->back();
        }
        if (count($status) > 0) {
            $order_list = UserPackageBooking::query()->select('id', 'user_name', 'order_package_list', 'total_pay', 'status')->where('provider_id', $provider_details->id)->whereIN('status', $status)->get();
        } else {
            $order_list = UserPackageBooking::query()->select('id', 'user_name', 'order_package_list', 'total_pay', 'status')->where('provider_id', $provider_details->id)->get();
        }
        $order_status = $this->package_status;
        if ($request->ajax()) {
            $view = view('admin.pages.other_services.other_service_order_list', compact('order_list', 'order_status', 'provider_details'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.other_services.other_service_order_list', compact('order_list', 'order_status', 'provider_details'));
    }

    public function getProviderOtherServiceAllOrderDetails(Request $request, $order_id)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $orders_details = UserPackageBooking::select('user_service_package_booking.id', 'user_service_package_booking.order_no','user_service_package_booking.order_type',
            'user_service_package_booking.user_name', 'user_service_package_booking.provider_name','user_service_package_booking.payment_type',
            'user_service_package_booking.service_date_time',
            'user_service_package_booking.total_item_cost',
            'user_service_package_booking.delivery_address',
            'user_service_package_booking.service_date',
            'user_service_package_booking.payment_status',
            'user_service_package_booking.service_time',
            'user_service_package_booking.booking_time_zone',
            'user_service_package_booking.book_start_time',
            'user_service_package_booking.book_end_time',
            'user_service_package_booking.tax',
            'user_service_package_booking.refer_discount',
            'user_service_package_booking.tip',
            'user_service_package_booking.remark','user_service_package_booking.created_at',
            'user_service_package_booking.total_pay', 'user_service_package_booking.status',
            'used_promocode_details.discount_amount as promo_code_discount',
            'used_promocode_details.promocode_name as promo_code_name'
            , 'users.country_code'
            , 'users.contact_number'
            ,'user_service_package_booking.cancel_by'
            ,'user_service_package_booking.cancel_reason'
//            , 'providers.time_zone as booking_time_zone'
        )
            ->join('users', 'users.id', '=', 'user_service_package_booking.user_id')
            ->join('providers', 'providers.id', '=', 'user_service_package_booking.provider_id')
            ->join('service_category', 'service_category.id', '=', 'user_service_package_booking.service_cat_id')
            ->leftJoin('used_promocode_details', 'used_promocode_details.id', 'user_service_package_booking.promo_code')
            ->where('user_service_package_booking.id', $order_id)
            ->whereNull('users.deleted_at')
            ->first();
        $orders_status = "----";
        $orders_status_array = $this->order_status;
        if ($orders_details != Null) {
            $package_list = UserPackageBookingQuantity::select('num_of_items', 'package_name', 'sub_category_name', 'price_for_one')
                ->where('order_id', $orders_details->id)
                ->get();
            $total_package_cost = 0;
            foreach ($package_list as $key => $package) {
                $total_package_item_cost = round($package->price_for_one * $package->num_of_items, 2);
                $total_package_cost = $total_package_cost + $total_package_item_cost;
            }
            if ($orders_details->status == 1) {
                $orders_status = "Pending";
            } elseif ($orders_details->status == 2 || $orders_details->status == 3) {
                $orders_status = "Confirmed";
            } elseif ($orders_details->status == 4) {
                $orders_status = "Rejected";
            } elseif ($orders_details->status == 5) {
                $orders_status = "Cancelled";
            } elseif ($orders_details->status == 6) {
                $orders_status = "Ongoing";
            } elseif ($orders_details->status == 7) {
                $orders_status = "Arrived";
            } elseif ($orders_details->status == 8) {
                $orders_status = "Processing";
            } elseif ($orders_details->status == 9) {
                $orders_status = "Completed";
            } elseif ($orders_details->status == 10) {
                $orders_status = "Failed";
            }
            $view = view('admin.pages.other_services.other_service_order_details', compact('orders_status', 'orders_status_array', 'orders_details', 'package_list'));
        } else {
            $view = view('admin.pages.other_services.other_service_order_details', compact('orders_status', 'orders_status_array'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getProviderServiceEditProfileBKP(Request $request)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $provider_id = Auth::guard('on_demand')->user()->id;
        $provider = Provider::query()->where('id', $provider_id)->whereNull('providers.deleted_at')->first();
        if ($provider == Null) {
            Session::flash('error', 'Something went to wrong!');
            return redirect()->back();
        }
        $provider_other_details = OtherServiceProviderDetails::query()->where('provider_id', $provider_id)->first();
        $bank_details = ProviderBankDetails::query()->where('provider_id', $provider_id)->first();
        $sun_day_open_time = OtherServiceProviderTimings::query()->where('provider_id', '=', $provider->id)->where('day', '=', 'SUN')->first();
        $mon_day_open_time = OtherServiceProviderTimings::query()->where('provider_id', '=', $provider->id)->where('day', '=', 'MON')->first();
        $tue_day_open_time = OtherServiceProviderTimings::query()->where('provider_id', '=', $provider->id)->where('day', '=', 'TUE')->first();
        $wed_day_open_time = OtherServiceProviderTimings::query()->where('provider_id', '=', $provider->id)->where('day', '=', 'WED')->first();
        $thu_day_open_time = OtherServiceProviderTimings::query()->where('provider_id', '=', $provider->id)->where('day', '=', 'THU')->first();
        $fri_day_open_time = OtherServiceProviderTimings::query()->where('provider_id', '=', $provider->id)->where('day', '=', 'FRI')->first();
        $sat_day_open_time = OtherServiceProviderTimings::query()->where('provider_id', '=', $provider->id)->where('day', '=', 'SAT')->first();
        $all_day_open_time = [];

        $count = 0;
        $check_get_data = 0;
        $time_status='';
        if($provider_other_details != NULL)
            $time_status = $provider_other_details->time_slot_status;
        if ($sun_day_open_time != Null) {
            $check_get_data = 1;
            $sun_day_open_time = explode(',', $sun_day_open_time->open_time_list);
        }
        else{
            $sun_day_open_time = [];
        }
        if ($mon_day_open_time != Null) {
            $check_get_data = 1;
            $mon_day_open_time = explode(',', $mon_day_open_time->open_time_list);
        }
        else{
            $mon_day_open_time = [];
        }
        if ($tue_day_open_time != Null) {
            $check_get_data = 1;
            $tue_day_open_time = explode(',', $tue_day_open_time->open_time_list);
        }
        else{
            $tue_day_open_time = [];
        }
        if ($wed_day_open_time != Null) {
            $check_get_data = 1;
            $wed_day_open_time = explode(',', $wed_day_open_time->open_time_list);
        }
        else{
            $wed_day_open_time = [];
        }
        if ($thu_day_open_time != Null) {
            $check_get_data = 1;
            $thu_day_open_time = explode(',', $thu_day_open_time->open_time_list);
        }
        else{
            $thu_day_open_time = [];
        }
        if ($fri_day_open_time != Null) {
            $check_get_data = 1;
            $fri_day_open_time = explode(',', $fri_day_open_time->open_time_list);
        }
        else{
            $fri_day_open_time = [];
        }
        if ($sat_day_open_time != Null) {
            $check_get_data = 1;
            $sat_day_open_time = explode(',', $sat_day_open_time->open_time_list);
        }
        else{
            $sat_day_open_time = [];
        }

        if ($sun_day_open_time == $mon_day_open_time) {
            $count = $count + 1;
        }
        if ($sun_day_open_time == $tue_day_open_time) {
            $count = $count + 1;
        }
        if ($sun_day_open_time == $wed_day_open_time) {
            $count = $count + 1;
        }
        if ($sun_day_open_time == $thu_day_open_time) {
            $count = $count + 1;
        }
        if ($sun_day_open_time == $fri_day_open_time) {
            $count = $count + 1;
        }
        if ($sun_day_open_time == $sat_day_open_time) {
            $count = $count + 1;
        }

        $all_day = 0;
        if ($count === 6) {
            $all_day = 1;
            $all_day_open_time = $sun_day_open_time;
            $sun_day_open_time = [];
            $mon_day_open_time = [];
            $tue_day_open_time = [];
            $wed_day_open_time = [];
            $thu_day_open_time = [];
            $fri_day_open_time = [];
            $sat_day_open_time = [];
        }
        if ($check_get_data == 0) {
            $all_day = 1;
        }
        $view = view('admin.pages.other_services.provider.form', compact('provider', 'provider_other_details', 'bank_details',
            'all_day', 'all_day_open_time', 'sun_day_open_time', 'mon_day_open_time', 'tue_day_open_time', 'wed_day_open_time', 'thu_day_open_time', 'fri_day_open_time', 'sat_day_open_time','time_status'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        } else {
            return $view;
        }
    }

    public function getProviderServiceEditProfile(Request $request)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $provider_id = Auth::guard('on_demand')->user()->id;
        $provider = Provider::query()->where('id', $provider_id)->whereNull('providers.deleted_at')->first();
        if ($provider == Null) {
            Session::flash('error', 'Something went to wrong!');
            return redirect()->back();
        }
        $provider_other_details = OtherServiceProviderDetails::query()->where('provider_id', $provider_id)->first();
        $bank_details = ProviderBankDetails::query()->where('provider_id', $provider_id)->first();

        $time_status = isset($provider_other_details->time_slot_status)?$provider_other_details->time_slot_status:0;
        $all_day = isset($provider_other_details->all_day)?$provider_other_details->all_day:0;

        $general_settings = request()->get("general_settings");
        $default_start_time= ($general_settings->default_start_time != Null)?$general_settings->default_start_time:"00:00:00";
        $default_end_time= ($general_settings->default_end_time != Null)?$general_settings->default_end_time:"23:00:00";
        $notificationClass = New NotificationClass();
        $default_open_close_time =  $notificationClass->defaultProviderOpenCloseTime($default_start_time,$default_end_time);
        $default_slot= isset($default_open_close_time['default_provider_slot'])?$default_open_close_time['default_provider_slot']:[];

        $days_arr = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
        $time_slot_list =[];
        foreach ($days_arr as $single_day) {
            foreach ($default_slot as $key => $single_default_slot) {
                $get_open_timings = OtherServiceProviderTimings::query()->where('provider_id', $provider->id)->where('day', strtolower($single_day))->get();
                $selected = 0;
                foreach ($get_open_timings as $get_single_open_timing) {
                    if ($single_default_slot['start_time'] == $get_single_open_timing->provider_open_time && $single_default_slot['end_time'] == $get_single_open_timing->provider_close_time){
                        $selected = 1;
                    }
                }
                $time_slot_list[strtolower($single_day)][] = array(
                    'start_time' => $single_default_slot['start_time'],
                    'end_time' => $single_default_slot['end_time'],
                    'display_start_time' => date("h:i A", strtotime($single_default_slot['start_time'])),
                    'display_end_time' => date("h:i A", strtotime($single_default_slot['end_time'])),
                    'selected' => $selected,
                );
            }
        }

        $view = view('admin.pages.other_services.provider.form', compact('provider', 'provider_other_details', 'bank_details', 'time_slot_list','days_arr','time_status','all_day'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        } else {
            return $view;
        }
    }

    public function postProviderServiceEditProfileBKP(OtherServiceProviderStoreRequest $request)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $provider_id = Auth::guard('on_demand')->user()->id;
        $provider = $this->adminClass->AddServiceProvider($request);
        $provider_details = OtherServiceProviderDetails::query()->where('provider_id', $provider_id)->first();
        if ($provider_details == Null) {
            $provider_details = new OtherServiceProviderDetails();
            $provider_details->provider_id = $provider_id;
        }

        //update provider type status
        $provider_data =  Provider::query()->where('id','=',$provider->id)->whereNull('providers.deleted_at')->first();
        if($provider_data != Null){
            $provider_data->provider_type = 3;
            $provider_services=ProviderServices::query()
                ->select(
                    DB::raw("(CASE WHEN count(other_service_provider_packages.id) > 0 THEN count(other_service_provider_packages.id) ELSE 0 END) as provider_service_package_count"),
                    DB::raw("(CASE WHEN count(provider_services.id) > 0 THEN count(provider_services.id) ELSE 0 END) as provider_service_count"),
                )
                ->where('provider_id','=',$provider_data->id)
                ->leftJoin('other_service_provider_packages','other_service_provider_packages.provider_service_id','=','provider_services.id')
                ->first();
            if($provider_services->provider_service_count > 0)
            {
                if ($provider_services->provider_service_package_count > 0)
                {
                     $provider_data->completed_step=3;
                }
                else{
                    $provider_data->completed_step=2;
                }
            }
            else
            {
                $provider_data->completed_step=1;
            }
            $provider_data->save();
        }

        $provider_details->address = $request->get('address');
        $provider_details->landmark = $request->get('landmark');
        $provider_details->min_order = $request->get('min_order');
        $provider_details->lat = $request->get('lat');
        $provider_details->long = $request->get('long');
        $open_time_status = $request->get('open_time_status') != Null ? 1 : 0;
        $provider_details->time_slot_status = $open_time_status;
        $provider_details->save();
        $all_day = $request->get('all_day');
        $all_day_open_time = $request->get('all_day_open_time');

        $time = [
            "SUN" => ($all_day == 1 && $all_day_open_time != Null) ?  $all_day_open_time : $request->get('sun_day_open_time'),
            "MON" => ($all_day == 1 && $all_day_open_time != Null) ?  $all_day_open_time : $request->get('mon_day_open_time'),
            "TUE" => ($all_day == 1 && $all_day_open_time != Null) ?  $all_day_open_time : $request->get('tue_day_open_time'),
            "WED" => ($all_day == 1 && $all_day_open_time != Null) ?  $all_day_open_time : $request->get('wed_day_open_time'),
            "THU" => ($all_day == 1 && $all_day_open_time != Null) ?  $all_day_open_time : $request->get('thu_day_open_time'),
            "FRI" => ($all_day == 1 && $all_day_open_time != Null) ?  $all_day_open_time : $request->get('fri_day_open_time'),
            "SAT" => ($all_day == 1 && $all_day_open_time != Null) ?  $all_day_open_time : $request->get('sat_day_open_time'),
        ];
        $days = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
        for($i=0; $i<=6; $i++)
        {
            $day = $days[$i];
            if ($time[$day] != Null) {
                $get_timings = OtherServiceProviderTimings::query()->where('provider_id', '=', $provider->id)->where('day', '=', $day)->first();
                if ($get_timings == Null) {
                    $get_timings = new OtherServiceProviderTimings();
                    $get_timings->provider_id = $provider->id;
                    $get_timings->day = $day;
                }
                $get_timings->open_time_list = implode(',', $time[$day]);
                $get_timings->save();
            }
            else{
                OtherServiceProviderTimings::query()->where('provider_id', '=', $provider->id)->where('day', '=', $day)->delete();
            }
        }
        if ($request->get('account_number') != Null && $request->get('holder_name') != Null && $request->get('bank_name') != Null && $request->get('bank_location') != Null && $request->get('payment_email') != Null && $request->get('bic_swift_code') != Null) {
            $provider_bank_details = ProviderBankDetails::query()->where('provider_id', $provider_id)->first();
            if ($provider_bank_details == Null) {
                $provider_bank_details = new ProviderBankDetails();
                $provider_bank_details->provider_id = $provider_id;
            }
            $provider_bank_details->account_number = $request->get('account_number');
            $provider_bank_details->holder_name = $request->get('holder_name');
            $provider_bank_details->bank_name = $request->get('bank_name');
            $provider_bank_details->bank_location = $request->get('bank_location');
            $provider_bank_details->payment_email = $request->get('payment_email');
            $provider_bank_details->bic_swift_code = $request->get('bic_swift_code');
            $provider_bank_details->save();
        }
        Session::flash('success', 'Provider profile updated successfully!');
        return redirect()->route('get:provider-admin:edit-profile');
    }

    public function postProviderServiceEditProfile(OtherServiceProviderStoreRequest $request)
    {
        $provider_status = $this->adminClass->checkProviderStatus();
        if ($provider_status != Null) {
            return $provider_status;
        }
        $provider_id = Auth::guard('on_demand')->user()->id;
        $provider = $this->adminClass->AddServiceProvider($request);
        $provider_details = OtherServiceProviderDetails::query()->where('provider_id', $provider_id)->first();
        if ($provider_details == Null) {
            $provider_details = new OtherServiceProviderDetails();
            $provider_details->provider_id = $provider_id;
        }

        //update provider type status
        $provider_data =  Provider::query()->where('id','=',$provider->id)->whereNull('providers.deleted_at')->first();
        if($provider_data != Null){
            $provider_data->provider_type = 3;
            $provider_data->save();
        }

        $provider_details->address = $request->get('address');
        $provider_details->lat = $request->get('lat');
        $provider_details->long = $request->get('long');
        $provider_details->min_order = $request->get('min_order');
        $provider_details->landmark = $request->get('landmark');
        $open_time_status = $request->get('open_time_status') != Null ? 1 : 0;
        $provider_details->time_slot_status = $open_time_status;
        $all_day = ($request->get('all_day') != Null)?$request->get('all_day'):0;
        $provider_details->all_day = $all_day;
        $provider_details->save();

        $day_open_time = $request->get('day_open_time');

        //slot inseert and update
        $not_del_id_array=[];
        $i=0;

        if($all_day == 1){
            $activeday = strtolower($request->get('activeday'));
            $day_array  = array("SUN","MON","TUE","WED","THU","FRI","SAT");
            foreach ($day_array as $key => $day) {
                if( count($day_array) > 0){
                    if (isset($day_open_time[$activeday])) {
                        foreach ($day_open_time[$activeday] as $singel_day) {
                            $single_time_arr = explode("-",$singel_day);
                            $provider_open_time =  $single_time_arr[0];
                            $provider_close_time =  $single_time_arr[1];

                            $get_open_timing = OtherServiceProviderTimings::query()->where('provider_id', $provider->id)->where('day', strtoupper(trim(($day))))->where('provider_open_time', $provider_open_time)->where('provider_close_time', $provider_close_time)->first();
                            if ($get_open_timing == Null) {
                                $get_open_timing_ins = new OtherServiceProviderTimings();
                                $get_open_timing_ins->provider_id = $provider->id;
                                $get_open_timing_ins->day = strtoupper(trim(($day)));
                                $get_open_timing_ins->provider_open_time = $provider_open_time;
                                $get_open_timing_ins->provider_close_time = $provider_close_time;
                                $get_open_timing_ins->open_time_list = "";
                                $get_open_timing_ins->save();
                                $not_del_id_array[$i] = $get_open_timing_ins->id;
                            } else {
                                //get not delete id
                                $not_del_id_array[$i] = $get_open_timing->id;
                            }
                            $i++;
                        }
                        if(count($not_del_id_array) > 0){
                            OtherServiceProviderTimings::query()->where('provider_id', $provider->id)->where('day', strtoupper(trim(($day))))->whereNotIn('id', $not_del_id_array)->delete();
                        }
                    } else {
                        OtherServiceProviderTimings::query()->where('provider_id', $provider->id)->delete();
                    }
                }
            }
        } else {
            $day_array  = array("SUN","MON","TUE","WED","THU","FRI","SAT");
            if($day_open_time != Null) {
                foreach ($day_open_time as $key => $day) {
                    $day_name = strtoupper($key);
                    if (($key_data = array_search($day_name, $day_array)) !== false) {
                        unset($day_array[$key_data]);
                    }
                    if( count($day) > 0){
                        foreach($day as $singel_day) {
                            $single_time_arr = explode("-",$singel_day);
                            $provider_open_time =  $single_time_arr[0];
                            $provider_close_time =  $single_time_arr[1];
                            $get_open_timing = OtherServiceProviderTimings::query()->where('provider_id', $provider->id)->where('day', strtoupper(trim(($key))))->where('provider_open_time', $provider_open_time)->where('provider_close_time', $provider_close_time)->first();
                            if ($get_open_timing == Null) {
                                $get_open_timing_ins = new OtherServiceProviderTimings();
                                $get_open_timing_ins->provider_id = $provider->id;
                                $get_open_timing_ins->day = strtoupper(trim(($key)));
                                $get_open_timing_ins->provider_open_time = $provider_open_time;
                                $get_open_timing_ins->provider_close_time = $provider_close_time;
                                $get_open_timing_ins->open_time_list = "";
                                $get_open_timing_ins->save();
                                $not_del_id_array[$i] = $get_open_timing_ins->id;
                            } else {
                                //get not delete id
                                $not_del_id_array[$i] = $get_open_timing->id;
                            }
                            $i++;
                        }
                        if(count($not_del_id_array) > 0){
                            OtherServiceProviderTimings::query()->where('provider_id', $provider->id)->where('day', strtoupper(trim(($key))))->whereNotIn('id', $not_del_id_array)->delete();
                        }
                    }
                }
                if(count($day_array) > 0){
                    OtherServiceProviderTimings::query()->where('provider_id', $provider->id)->whereIn('day',$day_array)->whereNotIn('id', $not_del_id_array)->delete();
                }
            } else {
                OtherServiceProviderTimings::query()->where('provider_id', $provider->id)->delete();
            }
        }

        if ($request->get('account_number') != Null && $request->get('holder_name') != Null && $request->get('bank_name') != Null && $request->get('bank_location') != Null && $request->get('payment_email') != Null && $request->get('bic_swift_code') != Null && $request->get('branch_number') != null && $request->get('account_type') != null && $request->get('remark') != null) {
            $provider_bank_details = ProviderBankDetails::query()->where('provider_id', $provider_id)->first();
            if ($provider_bank_details == Null) {
                $provider_bank_details = new ProviderBankDetails();
                $provider_bank_details->provider_id = $provider_id;
            }
            $provider_bank_details->account_number = $request->get('account_number');
            $provider_bank_details->holder_name = $request->get('holder_name');
            $provider_bank_details->bank_name = $request->get('bank_name');
            $provider_bank_details->bank_location = $request->get('bank_location');
            $provider_bank_details->payment_email = $request->get('payment_email');
            $provider_bank_details->bic_swift_code = $request->get('bic_swift_code');
            $provider_bank_details->branch_number = $request->get('branch_number');
            $provider_bank_details->account_type = $request->get('account_type');
            $provider_bank_details->remark = $request->get('remark');
            $provider_bank_details->save();
        }
        Session::flash('success', 'Provider profile updated successfully!');
        return redirect()->route('get:provider-admin:edit-profile');
    }

    public function getProviderOtherServiceEarningReport(Request $request, $slug)
    {
        $service_category = ServiceCategory::query()->select('id', 'name')->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Something went to wrong!');
            return redirect()->back();
        }
        $view = view('admin.pages.other_services.earning_report.provider_earning_report', compact('slug', 'service_category'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function postProviderOtherServiceEarningReport(Request $request, $slug)
    {
        $service_category = ServiceCategory::query()->select('id', 'name')->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Something went to wrong!');
            return redirect()->back();
        }
        $from_date = ($request['from_date'] != Null && $request['to_date'] != Null) ? $request['from_date'] : Null;
        $to_date = ($request['from_date'] != Null && $request['to_date'] != Null) ? $request['to_date'] : Null;
        $package_order_list = UserPackageBooking::query()->select('id',
            'order_no',
            'order_type',
            'service_date_time',
            'service_time',
            'user_name',
            'provider_name',
            'total_item_cost',
            'promo_code',
            'tax',
            'tip',
            'total_pay',
            'provider_amount',
            'refer_discount',
            'created_at',
            'user_id',
            'admin_commission',
            'provider_pay_settle_status',
            'provider_id',
            'payment_type')
            ->where('status', 9)
            ->where('service_cat_id', $service_category->id)
            ->where('provider_id', Auth::guard('on_demand')->user()->id);
        if ($from_date != Null && $to_date != Null) {
            $from = Date('Y-m-d', strtotime($from_date)) . " 00:00:00";
            $to = Date('Y-m-d', strtotime($to_date)) . " 23:59:59";
            $package_order_list->whereDate('created_at', '>=', $from);
            $package_order_list->whereDate('created_at', '<=', $to);
        }
        $used_promo_cods = [];
        $collect_payment = [];
        $pay_payment = [];

        $package_order_list = $package_order_list->orderBy('id', "desc")->get();
        $promo_total=0;
        foreach($package_order_list as $order){
            $promo = UsedPromocodeDetails::query()->where('id',$order->promo_code)->first();
            if($promo != Null){
                $used_promo_cods[$order->id] = $promo->discount_amount;
                $promo_total=$promo_total+$promo->discount_amount;
            }
            if($order->payment_type == 1)
            {
                $collect_payment[$order->id] = $order->total_pay - $order->provider_amount;
                $pay_payment[$order->id] = 0;
            }
            else{
                $collect_payment[$order->id] = 0;
                $pay_payment[$order->id] = $order->provider_amount;
            }
        }
        $total_amount = $package_order_list->sum('total_pay');
        $site_commission = $package_order_list->sum('admin_commission');
        $provider_earning = $package_order_list->sum('provider_amount');
        $refer_total = $package_order_list->sum('refer_discount');
        $collect_from_provider = ($package_order_list->where('payment_type', 1)->sum('total_pay')) - ($package_order_list->where('payment_type', 1)->sum('provider_amount'));
        $view = view('admin.pages.other_services.earning_report.provider_earning_report', compact('slug',
            'service_category','used_promo_cods' ,'from_date', 'to_date', 'package_order_list', 'total_amount', 'site_commission',
            'provider_earning', 'collect_from_provider', 'promo_total','refer_total'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;

    }

    public function getUpdateOtherServiceOrderStatus(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "update_status" => "required|numeric|in:5,9"
        ]);
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
            ]);
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
        $user_details = User::query()->where('id', $order_details->user_id)->first();
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
                    $order_status = $order_details->status;
                    $order_details->status = $update_status;
                    $order_details->cancel_by = "admin";
                    $order_details->cancel_reason = $request->get('reason');
                    $order_details->save();
                    if ($user_details != Null) {
                        $notificationClss = New NotificationClass();
                        $notificationClss->userProviderPackageNotification($order_details->id, $user_details->device_token, $order_details->status, $user_details->login_device, $user_details->language);
                    }

                    ProviderAcceptedPackageTime::query()->where('provider_id', '=', $order_details->provider_id)->where('order_id', '=', $order_details->id)->delete();

                    $other_provider_details = Provider::query()->where('id', $order_details->provider_id)->whereNull('providers.deleted_at')->first();
                    if ($other_provider_details != Null) {
                        $notificationClss = New NotificationClass();
                        $notificationClss->providerOrderCancelRequestNotification($order_details->id, $order_details->status, $other_provider_details->device_token, $other_provider_details->login_device, $other_provider_details->language);
                    }
                    if ($order_status < 8){
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
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ]);
                }
                break;
            case 9:
                if ($order_details->status > 1 && $order_details->status < 9) {
                    if ($order_details->status != 4 && $order_details->status != 5 && $order_details->status != 9 && $order_details->status != 10) {
                        $order_details->status = $update_status;
                        $order_details->payment_status = 1;
                        //$order_details->provider_rating_status = 1;
                        $order_details->save();

                        if ($order_details->promo_code > 0){
                            $used_promocode_details = UsedPromocodeDetails::query()->where('id', $order_details->promo_code)->first();
                            if ($used_promocode_details != Null) {
                                $used_promocode_details->status = 1;
                                $used_promocode_details->save();
                            }
                        }

                        ProviderAcceptedPackageTime::query()->where('provider_id', '=', $order_details->provider_id)->where('order_id', '=', $order_details->id)->delete();

                        $other_provider_details = OtherServiceProviderDetails::query()->where('provider_id', $order_details->provider_id)->first();
                        if ($other_provider_details != Null) {
                            $other_provider_details->total_completed_order = $other_provider_details->total_completed_order + 1;
                            $other_provider_details->save();
                        }
                        $notificationClass = new NotificationClass();
                        if ($user_details != Null) {
                            $notificationClass->userProviderPackageNotification($order_details->id, $user_details->device_token, $order_details->status, $user_details->login_device, $user_details->language);
                        }
                        $other_provider_details_data = Provider::query()->where('id', $order_details->provider_id)->first();
                        if ($other_provider_details_data != Null) {
                            $notificationClass->providerOrderPackageNotification($order_details->id, $other_provider_details_data->device_token,$order_details->status,$other_provider_details_data->login_device, $other_provider_details_data->language,1);
                        }

                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Data not found'
                        ]);
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
        return response()->json([
            'success' => true
        ]);
    }

}
