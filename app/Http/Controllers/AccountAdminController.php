<?php

namespace App\Http\Controllers;

use App\Classes\AdminClass;
use App\model\Admin;
use App\model\ServiceCategory;
use App\model\StoreDetails;
use App\model\TransportCourierDetails;
use App\model\TransportDriverDetails;
use App\model\TransportRideBook;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserPackageBooking;
use App\model\UserPackageBookingQuantity;
use App\model\UserProductBooking;
use App\model\UserProductBookingQuantity;
use App\model\UserProductBookingStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AccountAdminController extends Controller
{

    private $adminClass;

    private $on_demand_service_id_array;
    private $transport_service_id_array;
    private $store_service_id_array;
    private $store_order_status;
    private $transport_ride_status;
//    private $on_demand_order_status;
    private $bike_ride;
    private $taxi_ride;
    private $courier_service;
    private $grocery_delivery;
    private $food_delivery;

    private $package_status;
    private $courier_service_id_array;
    private $delivery_service_id_array;
    private $is_restricted = 0;

    private $order_status;

    public function __construct(AdminClass $adminClass)
    {
        $this->middleware('auth');
        $this->adminClass = $adminClass;
//        $this->on_demand_service_id_array = [11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30];
        $service_cat = ServiceCategory::query()->whereIN('category_type', [3, 4])->where('status', 1)->get()->pluck('id')->toArray();
        $this->on_demand_service_id_array = $service_cat;
        $this->transport_service_id_array = [1, 2, 4];
        $this->courier_service_id_array = [4];
        $this->delivery_service_id_array = [5, 6, 7, 8, 9, 10];

        $this->store_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->transport_ride_status = ['pending', 'accepted', 'schedule-accepted', 'arrived', 'cancelled', 'running', 'drop', 'payment', 'rating', 'completed', 'failed'];
        $this->store_order_status = ['', 'pending', 'approved', 'rejected', 'cancelled', 'processing', 'approved-by-driver', 'arrived-driver', 'ongoing', 'completed', 'failed'];
        $this->package_status = ['', 'pending', 'approved', 'schedule-approved', 'rejected', 'cancelled', 'ongoing', 'arrived', 'processing', 'completed', 'failed'];
//        $this->on_demand_order_status = ['', 'pending', 'accepted', 'schedule-accepted', 'rejected', 'cancelled', 'ongoing', 'arrived', 'processing', 'completed', 'failed'];
        $this->bike_ride = 1;
        $this->taxi_ride = 2;
        $this->courier_service = 4;
        $this->food_delivery = 5;
        $this->grocery_delivery = 6;

        $this->order_status = [2, 3, 6, 7, 8];

        $this->middleware( function ($request, $next) {
            $is_restrict_admin = $request->get('is_restrict_admin');
            $this->is_restricted = $is_restrict_admin;
            return $next($request);
        });
    }

    //dashboard
    public function getAdminDashboard(Request $request)
    {
        if (Auth::guard("admin")->user()->roles == 3) {
            $date = date('Y-m-d');
            $total_user = User::query()->whereDate('created_at', '>=', $date)->whereNull('users.deleted_at')->count();
            $total_transport_sales = TransportRideBook::query()->where('status', 9)->whereDate('created_at', '>=', $date)->count();
            $total_delivery_sales = UserProductBooking::query()->where('status', 9)->whereDate('created_at', '>=', $date)->count();
            $total_ondemand_sales = UserPackageBooking::query()->where('status', 9)->whereDate('created_at', '>=', $date)->count();
            $total_cancel_sales = UserPackageBooking::query()->where('status', 5)->whereDate('created_at', '>=', $date)->count();
            $total_sales = ($total_transport_sales + $total_delivery_sales + $total_ondemand_sales);

            $transport_rev = TransportRideBook::query()
//        selectRaw('SUM(total_pay) as total_pay')
                ->whereDate('created_at', '>=', $date)
                ->where('status', 9)
                ->sum('total_pay');
//        $temp_total_earning = 0;
//        if ($transport_rev != Null) {
//            $temp_total_earning = $transport_rev->total_pay;
//        }
//        $n_tran = (0 + str_replace(",", "", number_format($temp_total_earning, 2)));
            $n_tran = (0 + str_replace(",", "", number_format($transport_rev, 2)));

            $delivery_rev = UserProductBooking::query()
//        selectRaw('SUM(total_pay) as total_pay')
                ->whereDate('created_at', '>=', $date)
                ->where('status', 9)
//            ->first();
                ->sum('total_pay');
//        $temp_delivery_rev = 0;
//        if ($delivery_rev != Null) {
//            $temp_delivery_rev = $delivery_rev->total_pay;
//        }
//        $n_deli = (0 + str_replace(",", "", number_format($temp_delivery_rev, 2)));
            $n_deli = (0 + str_replace(",", "", number_format($delivery_rev, 2)));

            $ondemand_rev = UserPackageBooking::query()
//        selectRaw('SUM(total_pay) as total_pay')
                ->whereDate('created_at', '>=', $date)
                ->where('status', 9)
//            ->first();
                ->sum('admin_commission');
//        $temp_delivery_rev = 0;
//        if ($ondemand_rev != Null) {
//            $temp_ondemand_rev = $ondemand_rev->total_pay;
//        }
//        $n_ondemand = (0 + str_replace(",", "", number_format($temp_ondemand_rev, 2)));
            $n_ondemand = (0 + str_replace(",", "", number_format($ondemand_rev, 2)));

            $n = ($n_tran + $n_deli + $n_ondemand);
            if (!is_numeric($n)) {
                false;
            }
            if ($n > 1000000000000) {
                $total_revenue = round(($n / 1000000000000), 2) . ' T';
            } elseif ($n > 1000000000) {

                $total_revenue = round(($n / 1000000000), 2) . ' B';
            } elseif ($n > 1000000) {
                $total_revenue = round(($n / 1000000), 2) . ' M';
            } elseif ($n > 1000) {
                $total_revenue = round(($n / 1000), 2) . ' K';
            } else {
                $total_revenue = $n;
            }

            $total_order_count = UserPackageBooking::query()->whereDate('created_at', '>=', $date)->count();

            $service_category = ServiceCategory::query()->where('status', 1)->get();
            foreach ($service_category as $key => $category) {
                if (in_array($category->category_type, [1, 5])) {
                    $order_count[$category->id] = TransportRideBook::query()->where('service_cat_id', $category->id)->whereDate('created_at', '>=', $date)->count();
                } elseif (in_array($category->category_type, [2])) {
                    $order_count[$category->id] = UserProductBooking::query()->where('service_cat_id', $category->id)->whereDate('created_at', '>=', $date)->count();
                } elseif (in_array($category->category_type, [3, 4])) {
                    $order_count[$category->id] = UserPackageBooking::query()->where('service_cat_id', $category->id)->whereDate('created_at', '>=', $date)->count();
                } else {
                    $order_count[$category->id] = 0;
                }
            }
//        $bike_ride_total_order = TransportRideBook::where('service_cat_id', $this->bike_ride)->where('created_at', '>=', $date)->count();
//        $taxi_ride_total_order = TransportRideBook::where('service_cat_id', $this->taxi_ride)->where('created_at', '>=', $date)->count();
//        $courier_service_total_order = TransportRideBook::where('service_cat_id', $this->courier_service)->where('created_at', '>=', $date)->count();
//        $food_delivery_total_order = UserProductBooking::where('service_cat_id', $this->food_delivery)->where('created_at', '>=', $date)->count();
//        $grocery_delivery_total_order = UserProductBooking::where('service_cat_id', $this->grocery_delivery)->where('created_at', '>=', $date)->count();
//        $view = view('admin.pages.super_admin.dashboard', compact('total_user', 'total_sales', 'total_revenue', 'bike_ride_total_order', 'taxi_ride_total_order', 'courier_service_total_order', 'food_delivery_total_order', 'grocery_delivery_total_order'));
            $view = view('admin.pages.super_admin.dashboard', compact('total_user',
                'total_sales','total_ondemand_sales',
                'total_revenue',
                'service_category','total_order_count','total_cancel_sales', 'order_count'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        } elseif (Auth::guard("admin")->user()->roles == 1) {
            return redirect()->route('get:admin:dashboard');
        } elseif (Auth::guard("admin")->user()->roles == 2) {
            return redirect()->route('get:dispatcher:manual_ride_booking');
        } else {
            Auth::guard('admin')->logout();
            return redirect()->route('get:admin:login');
        }
    }

    //othre service order list
    public function getOtherServiceOrderList(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
//            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service category not found!');
//            return redirect()->back();
//        }
//        if ($status == "all") {
//            $status = [];
//        } elseif ($status == "pending") {
//            $status = [1];
//        } elseif ($status == "approved") {
//            $status = [2, 3];
//        } elseif ($status == "rejected") {
//            $status = [4];
//        } elseif ($status == "ongoing") {
//            $status = [6, 7, 8];
//        } elseif ($status == "completed") {
//            $status = [9];
//        } elseif ($status == "cancelled") {
//            $status = [5, 10];
//        } else {
//            $status = [];
//        }
//        if (count($status) > 0) {
//            $order_list = UserPackageBooking::select('id', 'user_name', 'provider_name', 'total_pay', 'status')->where('service_cat_id', $service_category->id)->whereIN('status', $status)->get();
//        } else {
//            $order_list = UserPackageBooking::select('id', 'user_name', 'provider_name', 'total_pay', 'status')->where('service_cat_id', $service_category->id)->get();
//        }
        $order_list = UserPackageBooking::select('user_service_package_booking.id',
            'user_service_package_booking.user_name', 'user_service_package_booking.provider_name',
            'user_service_package_booking.total_pay', 'user_service_package_booking.status',
            'service_category.name as service_category_name')
//            ->where('service_cat_id', $service_category->id)
            ->join('service_category', 'service_category.id', '=', 'user_service_package_booking.service_cat_id')
            ->whereIN('user_service_package_booking.service_cat_id', $this->on_demand_service_id_array)
            ->get();
        $order_status = $this->package_status;
        if ($request->ajax()) {
            $view = view('admin.pages.other_services.billing_admin_other_service_order_list', compact( 'order_list', 'order_status'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.other_services.billing_admin_other_service_order_list', compact( 'order_list', 'order_status'));
    }

    public function getOtherServiceOrderDetails(Request $request, $order_id)
    {
//        $service_category = ServiceCategory::select('id', 'name')->where('slug', $slug)->first();
//        if ($service_category == Null) {
//            return redirect()->back();
//        }
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }

        $orders_details = UserPackageBooking::query()->select('user_service_package_booking.id', 'user_service_package_booking.order_no',
            'user_service_package_booking.user_name', 'user_service_package_booking.provider_name','user_service_package_booking.booking_time_zone',
            'user_service_package_booking.service_date_time',
            'user_service_package_booking.service_date',
            'user_service_package_booking.service_time',
            'user_service_package_booking.total_item_cost',
            'user_service_package_booking.delivery_address',
            'user_service_package_booking.promo_code',
            'user_service_package_booking.tax',
            'user_service_package_booking.tip',
            'user_service_package_booking.created_at',
            'user_service_package_booking.remark',
            'user_service_package_booking.refer_discount',
            'user_service_package_booking.service_date',
            'user_service_package_booking.service_time',
            'user_service_package_booking.book_start_time',
            'user_service_package_booking.book_end_time',
            'used_promocode_details.discount_amount as promo_code_discount',
            'used_promocode_details.promocode_name as promo_code_name',
            'user_service_package_booking.total_pay', 'user_service_package_booking.status'
            , 'users.country_code'
            , 'users.contact_number'
            , 'user_service_package_booking.cancel_by'
            , 'user_service_package_booking.cancel_reason',
            'user_service_package_booking.payment_status',
            'user_service_package_booking.payment_type',
        )
            ->join('users', 'users.id', '=', 'user_service_package_booking.user_id')
            ->join('service_category', 'service_category.id', '=', 'user_service_package_booking.service_cat_id')
            ->leftJoin('used_promocode_details', 'used_promocode_details.id', 'user_service_package_booking.promo_code')
            ->where('user_service_package_booking.id', $order_id)
            ->whereNull('users.deleted_at')
//            ->where('service_category.slug', $slug)
            ->first();
        $orders_status = "----";
        $orders_status_array = $this->order_status;
        if ($orders_details != Null) {
            if ($orders_details->promo_code != 0) {
                try {
                    $get_discount = UsedPromocodeDetails::query()->where('id', $orders_details->promo_code)->first();
                    $promo_code_discount = $get_discount->discount_amount;
                    $promocode_name = $get_discount->promocode_name;
                } catch (\Exception $e) {
                    $promo_code_discount = round($orders_details->total_item_cost - $orders_details->discount_cost + $orders_details->tax_cost + $orders_details->delivery_cost + $orders_details->packaging_cost - $orders_details->total_pay, 2);
                    $promocode_name = 'promo';
                }
            } else {
                $promo_code_discount = 0;
                $promocode_name = 'promo';
            }


            $orders_details->promo_code_discount=$promo_code_discount;
            $orders_details->promocode_name=$promocode_name;
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
            $view = view('admin.pages.other_services.billing_admin_other_service_order_details', compact('orders_status', 'orders_status_array', 'orders_details', 'package_list'));
        } else {
            $view = view('admin.pages.other_services.billing_admin_other_service_order_details', compact('orders_status', 'orders_status_array'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //store order list
    public function getStoreOrderList(Request $request)
    {

        if (Auth::guard("admin")->user()->roles != 3) {
//            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service category not found!');
//            return redirect()->back();
//        }
//        if ($status == "all") {
//            $status = [];
//        } elseif ($status == "pending") {
//            $status = [1];
//        } elseif ($status == "approved") {
//            $status = [2];
//        } elseif ($status == "rejected") {
//            $status = [3];
//        } elseif ($status == "ongoing") {
//            $status = [5, 6, 7, 8];
//        } elseif ($status == "completed") {
//            $status = [9];
//        } elseif ($status == "cancelled") {
//            $status = [4, 10];
//        } else {
//            $status = [];
//        }
//        if (count($status) > 0) {
//            $order_list = UserProductBooking::select('id', 'user_name', 'store_name', 'total_pay', 'status')->where('service_cat_id', $service_category->id)->whereIN('status', $status)->get();
//        } else {
//            $order_list = UserProductBooking::select('id', 'user_name', 'store_name', 'total_pay', 'status')->where('service_cat_id', $service_category->id)->get();
//        }
        $order_list = UserProductBooking::select('user_store_product_booking.id',
            'user_store_product_booking.user_name', 'user_store_product_booking.store_name',
            'user_store_product_booking.total_pay', 'user_store_product_booking.status', 'service_category.name as service_category_name')
            ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->where('user_store_product_booking.status', '!=', 11)
            ->get();
        $order_status = $this->store_order_status;
        if ($request->ajax()) {
            $view = view('admin.pages.store_services.billing_admin_store_order_list', compact('order_list', 'order_status'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }

        return view('admin.pages.store_services.billing_admin_store_order_list', compact( 'order_list', 'order_status'));
    }

    public function getStoreOrderDetails(Request $request, $order_id)
    {
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service category not found!');
//            return redirect()->back();
//        }
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }

        $order_details = UserProductBooking::select('id', 'store_id', 'user_id', 'delivery_people_id', 'order_no', 'user_name',
            'store_name', 'delivery_address', 'created_at', 'total_item_cost', 'discount_cost', 'tax_cost', 'delivery_cost', 'packaging_cost',
            'total_pay', 'payment_type', 'payment_status', 'cancel_by','status', 'cancel_reason', 'additional_request','order_type','order_date_time')
            ->where('id', $order_id)->first();
        if ($order_details == Null) {
            return redirect()->back();
        }
        $store_details = StoreDetails::select('address')->where('id', $order_details->store_id)->first();
        $user_details = User::select('contact_number')->where('id', $order_details->user_id)->first();
        $get_order_status = UserProductBookingStatus::select('status', 'created_at')->where('order_id', $order_details->id)->get();
        $order_status = $this->store_order_status;
        $product_details = UserProductBookingQuantity::select('product_name', 'product_cat_name', 'num_of_items', 'price_for_one')->where('order_id', $order_details->id)->get()->groupBy('product_cat_name');

        $driver_details = TransportDriverDetails::select(
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
            ->whereNull('providers.deleted_at')
            ->where('transport_driver_details.id', $order_details->delivery_people_id)
            ->first();
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
        }else{
            $orders_status = "--";
        }

        if ($request->ajax()) {
            $view = view('admin.pages.store_services.store_details.billing_admin_store_order_details', compact('order_details', 'get_order_status', 'order_status', 'product_details', 'store_details', 'user_details', 'driver_details','orders_status'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.store_services.store_details.billing_admin_store_order_details', compact('order_details', 'get_order_status', 'order_status', 'product_details', 'store_details', 'user_details', 'driver_details','orders_status'));
    }

    //get Transport Service Category Ride List
    public function getTransportServiceRideList(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Category ride list not found!');
//            return redirect()->back();
//        }
        $ride_lists = TransportRideBook::select('user_ride_booking.id', 'user_ride_booking.ride_no',
            'user_ride_booking.total_pay', 'user_ride_booking.status', 'user_ride_booking.pickup_address',
            'user_ride_booking.destination_address',
//            'user_ride_booking.vehicle_type_name as vehicle_name',
            'user_ride_booking.user_name',
            'user_ride_booking.driver_name',
            'user_ride_booking.service_cat_id',
            'transport_vehicle_type.name as vehicle_name',
            'service_category.name as service_category_name'
        )
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_ride_booking.vehicle_type_id')
            ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
            ->whereIn('user_ride_booking.service_cat_id', $this->transport_service_id_array)
            ->whereIn('user_ride_booking.status', [0, 1, 2, 4, 5, 9])
            ->orderBy('user_ride_booking.pickup_datetime', 'desc')
            ->get();
        $status = "all";

        $transport_services = $this->transport_service_id_array;
        $courier_service = $this->courier_service_id_array;
        $delivery_service = $this->delivery_service_id_array;

        $view = view('admin.pages.transport_services.billing_admin_ride_list', compact('transport_services', 'courier_service', 'delivery_service', 'status', 'ride_lists'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getTransportRideDetails($id, Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service category not found!');
//            return redirect()->back();
//        }
        $ride_Details = TransportRideBook::select(
            'user_ride_booking.id', 'user_ride_booking.ride_no',
            'user_ride_booking.service_cat_id',
            'user_ride_booking.user_id', 'user_ride_booking.driver_id',
            'user_ride_booking.user_name', 'user_ride_booking.driver_name',
            'user_ride_booking.pickup_datetime', 'user_ride_booking.destination_datetime',
            'user_ride_booking.pickup_address', 'user_ride_booking.destination_address',
//            'user_ride_booking.vehicle_type_name',
            'user_ride_booking.vehicle_cost_for_km',
            'user_ride_booking.base_fare',
            'user_ride_booking.additional_request',
            'user_ride_booking.total_distance_amount',
            'user_ride_booking.time_fare_amount',
            'user_ride_booking.created_at',
            'user_ride_booking.total_distance',
            'user_ride_booking.tax',
            'user_ride_booking.total_pay', 'user_ride_booking.payment_type', 'user_ride_booking.payment_status',
            'user_ride_booking.promo_code',
            'user_ride_booking.status',
            'user_ride_booking.ride_type',
            'user_ride_booking.surcharge_cost',
            'transport_vehicle_type.name as vehicle_type_name'
        )
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_ride_booking.vehicle_type_id')
            ->where('user_ride_booking.id', $id)->first();

        if ($ride_Details == Null) {
            return redirect()->back();
        }

        $user_details = User::select('id', 'contact_number', 'email')->where('id', $ride_Details->user_id)->whereNull('users.deleted_at')->first();
        $driver_details = TransportDriverDetails::select('transport_driver_details.id', 'providers.email', 'providers.contact_number')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->where('transport_driver_details.id', $ride_Details->driver_id)
            ->whereNull('providers.deleted_at')
            ->first();
        $courier_details = Null;
        if ($ride_Details != Null && in_array($ride_Details->service_cat_id, $this->courier_service_id_array)) {
//        if ($slug == "courier-service") {
            $courier_details = TransportCourierDetails::Select(
                'user_courier_service_details.recipient_name',
                'user_courier_service_details.house_name',
                'user_courier_service_details.recipient_contact_number',
                'user_courier_service_details.goods_height',
                'user_courier_service_details.goods_width',
                'user_courier_service_details.goods_weight',
                'user_courier_service_details.description',
                'user_courier_service_details.delivery_instruction'
            )->where('ride_id', $ride_Details->id)->first();
        }
        /*$total = round($ride_Details->total_distance_amount + $ride_Details->time_fare_amount + $ride_Details->base_fare + $ride_Details->tax, 2);*/
        $total = round($ride_Details->total_distance_amount + $ride_Details->time_fare_amount + $ride_Details->base_fare + $ride_Details->surcharge_cost, 2);
        $discount = round(0, 2);
        $grand_total = round($ride_Details->total_pay - $discount, 2);

        if (in_array($ride_Details->service_cat_id, $this->courier_service_id_array)) {
            $view = view('admin.pages.transport_services.ride.billing_admin_courier_delivery_details', compact( 'ride_Details', 'user_details', 'driver_details', 'courier_details', 'grand_total', 'total', 'discount'));
        } else {
            $view = view('admin.pages.transport_services.ride.billing_admin_ride_details', compact( 'ride_Details', 'user_details', 'driver_details', 'grand_total', 'total', 'discount'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getTransportStoreDeliveryDetails(Request $request, $order_id)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service category not found!');
//            return redirect()->back();
//        }

        $order_details = UserProductBooking::select('id', 'store_id', 'user_id', 'order_no', 'user_name',
            'store_name', 'delivery_address', 'created_at', 'total_item_cost', 'discount_cost', 'tax_cost',
            'delivery_cost', 'packaging_cost', 'total_pay', 'payment_type', 'payment_status', 'status', 'cancel_by', 'additional_request',
            'cancel_reason')
            ->where('id', $order_id)->first();
        if ($order_details == Null) {
            return redirect()->back();
        }
        $store_details = StoreDetails::select('address')->where('id', $order_details->store_id)->first();
        $user_details = User::select('contact_number')->where('id', $order_details->store_id)->whereNull('users.deleted_at')->first();
        //$get_order_status = UserProductBookingStatus::select('status', 'created_at')->where('order_id', $order_details->id)->get();
        $order_status = $this->store_order_status;
        $product_details = UserProductBookingQuantity::select('product_name', 'product_cat_name', 'num_of_items', 'price_for_one')->where('order_id', $order_details->id)->get()->groupBy('product_cat_name');
        $view = view('admin.pages.transport_services.ride.billing_admin_delivery_details', compact('slug', 'order_details', 'get_order_status', 'order_status', 'product_details', 'store_details', 'user_details', 'service_category'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //earning report
    //transport get search earning report
    public function getTransportEarningReport(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        //$service_category = ServiceCategory::where('slug', $slug)->first();
        //if ($service_category == Null) {
        //    Session::flash('error', 'Service Category Not Found!');
        //    return redirect()->back();
        //}
        $user_list = TransportRideBook::query()->select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_ride_booking.user_id')
            ->where('user_ride_booking.status', '=', 9)
            ->whereIn('user_ride_booking.service_cat_id', $this->transport_service_id_array)
            //->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->whereNull('users.deleted_at')
            ->groupBy('user_ride_booking.user_id')
            ->get();
        $driver_list = TransportRideBook::query()->select('transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_ride_booking.driver_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_ride_booking.driver_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_ride_booking.status', '=', 9)
            //->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->whereIn('user_ride_booking.service_cat_id', $this->transport_service_id_array)
            ->whereNull('providers.deleted_at')
            ->groupBy('user_ride_booking.driver_id')
            ->get();
        $from_date = Null;
        $to_date = Null;
        $driver = Null;
        $user = Null;
        $payment_type = Null;
        $driver_pay_type = Null;

        $view = view('admin.pages.transport_services.billing_admin_earings_reports', compact('from_date', 'to_date', 'driver', 'user', 'payment_type', 'driver_pay_type', 'user_list', 'driver_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;

    }

    public function PostTransportSearchEarningReport(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service Category Not Found!');
//            return redirect()->back();
//        }

        $user_list = TransportRideBook::select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_ride_booking.user_id')
            ->where('user_ride_booking.status', '=', 9)
            ->whereIn('user_ride_booking.service_cat_id', $this->transport_service_id_array)
            //->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->whereNull('users.deleted_at')
            ->groupBy('user_ride_booking.user_id')
            ->get();
        $driver_list = TransportRideBook::select('transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_ride_booking.driver_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_ride_booking.driver_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_ride_booking.status', '=', 9)
            //->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->whereIn('user_ride_booking.service_cat_id', $this->transport_service_id_array)
            ->whereNull('providers.deleted_at')
            ->groupBy('user_ride_booking.driver_id')
            ->get();

        $from_date = Null;
        $to_date = Null;
        $driver = Null;
        $user = Null;
        $payment_type = Null;
        $driver_pay_type = Null;
        $admin_report_details = TransportRideBook::query()->select(
            'user_ride_booking.id',
            'user_ride_booking.ride_no',
            'user_ride_booking.driver_pay_settle_status',
            'user_ride_booking.pickup_datetime',
            'user_ride_booking.ride_type', 'user_ride_booking.hail_ride',
            'user_ride_booking.user_name', 'user_ride_booking.driver_name',
            'user_ride_booking.base_fare',
            'user_ride_booking.total_distance_amount',
            'user_ride_booking.time_fare_amount',
            'user_ride_booking.adjustment_amount',
            'user_ride_booking.tip',
            //'user_ride_booking.discount_amount',
            'user_ride_booking.tax',
            'user_ride_booking.status',
            'user_ride_booking.payment_type',
            'user_ride_booking.total_pay',
            'user_ride_booking.driver_amount',
            'user_ride_booking.admin_commission',
            'user_ride_booking.service_cat_id',
            'user_ride_booking.promo_code',
            'service_category.name as service_category_name'
        )
            ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
            ->where('user_ride_booking.status', '=', 9)
            ->whereIn('user_ride_booking.service_cat_id', $this->transport_service_id_array);
        if ($request->get('from_date') != Null && $request->get('to_date') != Null) {
            $from_date = $request->get('from_date');
            $to_date = $request->get('to_date');
            $admin_report_details->whereBetween('user_ride_booking.pickup_datetime', [$from_date . " 00:00:00", $to_date . " 23:59:59"]);
        }
        if ($request->get('driver') != Null) {
            $driver = $request->get('driver');
            $admin_report_details->where('user_ride_booking.driver_id', '=', $request->get('driver'));
        }
        if ($request->get('user') != Null) {
            $user = $request->get('user');
            $admin_report_details->where('user_ride_booking.user_id', '=', $request->get('user'));
        }
        if ($request->get('payment_type') != Null) {
            //cash & card
            $payment_type = $request->get('payment_type');
            //if ($payment_type != 2) {
            $admin_report_details->where('user_ride_booking.payment_type', '=', $request->get('payment_type'));
            //}
        }
        if ($request->get('driver_pay_type') != Null) {
            $driver_pay_type = $request->get('driver_pay_type');
            if ($driver_pay_type != 2) {
                $admin_report_details->where('user_ride_booking.driver_pay_settle_status', '=', $request->get('driver_pay_type'));
            }
        }
        $payment_reports = $admin_report_details->orderBy("user_ride_booking.id", "desc")
            ->get();
        if (!$payment_reports->isEmpty()) {
            $used_promocode_amount = [];
            $total_ride_check = 0;
            $site_earning_check = 0;
            $driver_earning = 0;
            $total_used_promocode = 0;
            $total_driver_pay_earning = [];
            $total_driver_pay_amount = 0;
            $total_collect_payment_check = 0;
            $collect_payment_total = [];
            foreach ($payment_reports as $key => $report) {
                $promocode = UsedPromocodeDetails::where('id', $report->promo_code)->where('service_cat_id', '=', $report->service_cat_id)->first();
                if ($promocode != Null) {
                    $used_promocode = $promocode->discount_amount;
                    $used_promocode_amount[$report->ride_no] = number_format($used_promocode, 2);
                    $total_used_promocode = array_sum($used_promocode_amount);
                }
                $total_ride_earning[$report->ride_no] = $report->total_pay;
                $total_ride_check = array_sum($total_ride_earning);

                $site_earning[$report->ride_no] = $report->admin_commission;
                $site_earning_check = array_sum($site_earning);

                $driver_commission[$report->ride_no] = $report->driver_amount;
                $driver_earning = array_sum($driver_commission);

                if ($report->payment_type == 2 || $report->payment_type == 3) {
                    $total_driver_pay_earning[$report->ride_no] = $report->driver_amount;
                    $total_driver_pay_amount = array_sum($total_driver_pay_earning);
                }

                $collect_payment[$report->ride_no] = ($report->total_pay - $driver_commission[$report->ride_no]);
                if ($report->payment_type == 1) {
                    $collect_payment_total[$report->ride_no] = ($report->total_pay - $driver_commission[$report->ride_no]);
                    $total_collect_payment_check = array_sum($collect_payment_total);
                }
            }
            $total_ride = number_format($total_ride_check, 2);
            $total_site_earning = number_format($site_earning_check, 2);
            $total_driver_earning = number_format($driver_earning, 2);
            $total_discount = number_format($total_used_promocode, 2);
            $total_pay_driver = number_format($total_driver_pay_amount, 2);
            $total_collect_payment = number_format($total_collect_payment_check, 2);
            $total_outstanding = $total_driver_pay_amount - $total_collect_payment_check;
            $total_driver_outstanding_amount = number_format($total_outstanding, 2);

            $view = view('admin.pages.transport_services.billing_admin_earings_reports', compact('from_date', 'to_date', 'driver', 'user', 'payment_type', 'driver_pay_type', 'user_list', 'driver_list',
                'payment_reports',
                'used_promocode_amount', 'driver_commission', 'collect_payment',
                'total_ride', 'total_site_earning', 'total_driver_earning', 'total_discount', 'total_pay_driver', 'total_collect_payment', 'total_driver_outstanding_amount'));
        } else {
            $view = view('admin.pages.transport_services.billing_admin_earings_reports', compact( 'from_date', 'to_date', 'driver', 'user', 'payment_type', 'driver_pay_type', 'user_list', 'driver_list'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;

    }

    //transport driver payment settled
    public function postTransportDriverPaymentSettled(Request $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        $driver_pay_details = $request->get('payment_settled');
        if ($driver_pay_details != Null) {
            foreach ($driver_pay_details as $key => $pay) {
                $pay_store = TransportRideBook::where('id', $pay)->first();
                if ($pay_store != Null && $pay_store->driver_pay_settle_status == 0) {
                    $pay_store->driver_pay_settle_status = 1;
                    $pay_store->save();
                }
            }
        }
        return redirect()->route('get:account:transport_earning_report');
    }

    //store get earning report
    public function getStoreEarningReport(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service Category Not Found!');
//            return redirect()->back();
//        }
        $user_list = UserProductBooking::select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
            ->where('user_store_product_booking.status', '=', 9)
            //->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->whereNull('users.deleted_at')
            ->groupBy('user_store_product_booking.user_id')
            ->get();
        $driver_list = UserProductBooking::select('transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.delivery_people_id as driver_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_store_product_booking.delivery_people_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            //->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->whereNull('providers.deleted_at')
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->groupBy('user_store_product_booking.delivery_people_id')
            ->get();
        $store_list = UserProductBooking::select('store_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.store_id')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->whereNull('providers.deleted_at')
            //->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->groupBy('user_store_product_booking.store_id')
            ->get();
        $view = view('admin.pages.store_services.earning_report.billing_admin_earning_report', compact('user_list', 'driver_list', 'store_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //store post earning report
    public function postStoreEarningReport(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service Category Not Found!');
//            return redirect()->back();
//        }
        $user_list = UserProductBooking::select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->whereNull('users.deleted_at')
            //->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->groupBy('user_store_product_booking.user_id')
            ->get();
        $driver_list = UserProductBooking::select('transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.delivery_people_id as driver_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_store_product_booking.delivery_people_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->whereNull('providers.deleted_at')
            //->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->groupBy('user_store_product_booking.delivery_people_id')
            ->get();
        $store_list = UserProductBooking::select('store_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.store_id')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->whereNull('providers.deleted_at')
            //->where('user_store_product_booking.service_cat_id', '=', $service_category->id)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
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
            'user_store_product_booking.store_name',
            'user_store_product_booking.shopper_name',
            'user_store_product_booking.user_name',
            'user_store_product_booking.delivery_people_name',
            'user_store_product_booking.driver_pay_settle_status',
            'user_store_product_booking.store_pay_settle_status',
            'service_category.name as service_category_name')
            ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
            ->where('user_store_product_booking.status', 9)
            //->where('service_cat_id', $service_category->id);
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array);
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
            //cash & card
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
        $store_earning = $store_order_list->sum('store_amount');
        //$total_pay_to_store = $store_order_list->sum('store_amount');
        $collect_from_store = $store_order_list->where('payment_type', 1)->where('taken_away_type', 2)->sum('total_pay');

        $total_pay_to_store = ($collect_from_store >= $store_earning) ? 0 : round(($store_earning - $collect_from_store), 2);
        $collect_from_store = ($collect_from_store >= $store_earning) ? round(($collect_from_store - $store_earning), 2) : 0;

        $total_pay_to_shopper = $store_order_list->sum('shopper_earring');

        $view = view('admin.pages.store_services.earning_report.billing_admin_earning_report', compact('store_order_list', 'user_list', 'driver_list', 'store_list',
            'from_date', 'to_date', 'driver', 'store', 'user', 'payment_type', 'driver_pay_type', 'store_pay_type',
            'total_amount', 'site_commission', 'driver_earning', 'collect_from_driver', 'total_pay_to_driver',
            'total_pay_to_store', 'store_earning', 'collect_from_store', 'total_pay_to_shopper'
        ));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //order store payment settled
    public function postStoreOrderPaymentSettle(Request $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service Category Not Found!');
//            return redirect()->back();
//        }
        if ($request->get('order_id') != Null) {
            $order_id_settle = $request->get('order_id');
            foreach ($order_id_settle as $key => $order_id) {
                $order_details = UserProductBooking::where('id', $key)->first();
                if ($order_details != Null) {
                    $order_details->driver_pay_settle_status = 1;
                    $order_details->store_pay_settle_status = 1;
                    $order_details->save();
                }
            }
        }
        return redirect()->back();
    }

    public function getAdminProfile(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        $admin_details = Admin::where('id', Auth::guard('admin')->user()->id)->first();
        if ($admin_details != Null) {
            $view = view('admin.pages.super_admin.profile.form', compact('admin_details'));
        } else {
            $view = view('admin.pages.super_admin.profile.form');
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }

        return $view;
    }

    public function postAdminProfile(Request $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        $admin = Admin::where('id', Auth::guard('admin')->user()->id)->first();
        if ($admin != Null) {
            $admin->name = $request->get("name");
            $admin->save();
            Session::flash('success', 'Billing Admin profile update successfully!');
            return redirect()->route('get:account:profile');
        }
        Session::flash('error', 'Admin Details Not Found!');
        return redirect()->back();
    }

    public function getOtherServiceEarningReport(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }

//        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service Category Not Found!');
//            return redirect()->back();
//        }
        $user_list = UserPackageBooking::query()->select('users.id',
            'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_service_package_booking.user_id')
            ->where('user_service_package_booking.status', '=', 9)
            ->whereIn('user_service_package_booking.service_cat_id', $this->on_demand_service_id_array)
            ->whereNull('users.deleted_at')
            ->groupBy('user_service_package_booking.user_id')
            ->get();
        $provider_list = UserPackageBooking::query()->select('providers.id',
            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_service_package_booking.provider_id')
            ->join('provider_services', 'provider_services.id', '=', 'user_service_package_booking.provider_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_service_package_booking.status', '=', 9)
            ->whereIn('user_service_package_booking.service_cat_id', $this->on_demand_service_id_array)
            ->whereNull('providers.deleted_at')
            ->groupBy('user_service_package_booking.provider_id')
            ->get();
        $view = view('admin.pages.other_services.earning_report.billing_admin_earning_report', compact('user_list', 'provider_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function postOtherServiceEarningReport(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }

//        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service Category Not Found!');
//            return redirect()->back();
//        }
        $user_list = UserPackageBooking::query()->select('users.id',
            'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_service_package_booking.user_id')
            ->where('user_service_package_booking.status', '=', 9)
            ->whereIn('user_service_package_booking.service_cat_id', $this->on_demand_service_id_array)
            ->whereNull('users.deleted_at')
            ->groupBy('user_service_package_booking.user_id')
            ->get();
        $provider_list = UserPackageBooking::query()->select('providers.id',
            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_service_package_booking.provider_id')
            ->join('provider_services', 'provider_services.id', '=', 'user_service_package_booking.provider_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_service_package_booking.status', '=', 9)
            ->whereIn('user_service_package_booking.service_cat_id', $this->on_demand_service_id_array)
            ->whereNull('providers.deleted_at')
            ->groupBy('user_service_package_booking.provider_id')
            ->get();

        $from_date = ($request['from_date'] != Null && $request['to_date'] != Null) ? $request['from_date'] : Null;
        $to_date = ($request['from_date'] != Null && $request['to_date'] != Null) ? $request['to_date'] : Null;
        $provider = $request['provider'] != Null ? $request['provider'] : Null;
        $user = $request['user'] != Null ? $request['user'] : Null;
        $payment_type = $request['payment_type'] != Null ? $request['payment_type'] : Null;
        $provider_pay_type = $request['provider_pay_type'] != Null ? $request['provider_pay_type'] : Null;

        $package_order_list = UserPackageBooking::query()->select('user_service_package_booking.id',
            'user_service_package_booking.order_no',
            'user_service_package_booking.order_type',
            'user_service_package_booking.service_date_time',
            'user_service_package_booking.service_time',
            'user_service_package_booking.user_name',
            'user_service_package_booking.promo_code',
            'user_service_package_booking.provider_name',
            'user_service_package_booking.total_item_cost',
            'user_service_package_booking.tax',
            'user_service_package_booking.tip',
            'user_service_package_booking.total_pay',
            'user_service_package_booking.provider_amount',
            'user_service_package_booking.refer_discount',
            'user_service_package_booking.created_at',
            'user_service_package_booking.user_id',
            'user_service_package_booking.admin_commission',
            'user_service_package_booking.provider_pay_settle_status',
            'user_service_package_booking.provider_id',
            'user_service_package_booking.payment_type',
            'service_category.name as service_category_name')
            ->join('service_category', 'service_category.id', '=', 'user_service_package_booking.service_cat_id')
            ->where('user_service_package_booking.status', 9)
            ->whereIn('user_service_package_booking.service_cat_id', $this->on_demand_service_id_array);
        if ($from_date != Null && $to_date != Null) {
            $from = Date('Y-m-d', strtotime($from_date)) . " 00:00:00";
            $to = Date('Y-m-d', strtotime($to_date)) . " 23:59:59";
            $package_order_list->whereDate('user_service_package_booking.created_at', '>=', $from);
            $package_order_list->whereDate('user_service_package_booking.created_at', '<=', $to);
        }
        if ($user != Null) {
            $package_order_list->where('user_service_package_booking.user_id', $user);
        }
        if ($provider != Null) {
            $package_order_list->where('user_service_package_booking.provider_id', $provider);
        }
        if ($payment_type != Null) {
            //cash & card
            $package_order_list->where('user_service_package_booking.payment_type', $payment_type);
        }
        if ($provider_pay_type != Null) {
            $package_order_list->where('user_service_package_booking.provider_pay_settle_status', $provider_pay_type);
        }
        $package_order_list = $package_order_list->orderBy('user_service_package_booking.id', "desc")->get();
        $promo_total=0;
        $used_promo_cods = [];
        $collect_payment = [];
        $pay_payment = [];
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
        $total_amount = +$package_order_list->sum('total_pay');
        $site_commission = $package_order_list->sum('admin_commission');
        $provider_earning = $package_order_list->sum('provider_amount');
        $refer_total = $package_order_list->sum('refer_discount');
        $collect_from_provider = ($package_order_list->where('payment_type', 1)->sum('total_pay')) - ($package_order_list->where('payment_type', 1)->sum('provider_amount'));


        $total_outstanding = $provider_earning - $collect_from_provider;
        $total_provider_outstanding_amount = number_format($total_outstanding, 2);


//        dd($package_order_list);
        $view = view('admin.pages.other_services.earning_report.billing_admin_earning_report', compact(
            'user_list', 'provider_list', 'from_date', 'to_date', 'provider', 'user', 'payment_type', 'provider_pay_type',
            'package_order_list', 'total_amount','used_promo_cods', 'site_commission', 'provider_earning', 'collect_from_provider', 'refer_total','promo_total', 'total_provider_outstanding_amount'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function postOtherServiceOrderPaymentSettle(Request $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        if (Auth::guard("admin")->user()->roles != 3) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }

        if ($request->get('order_id') != Null) {
            $order_id_settle = $request->get('order_id');
            foreach ($order_id_settle as $key => $order_id) {
//                dd($order_id);
                $order_details = UserPackageBooking::where('id', $order_id)->first();
                if ($order_details != Null) {
                    $order_details->provider_pay_settle_status = 1;
                    $order_details->save();
                }
            }
        }
        return response()->json([
            "success" => true,
            "message" => "success",
        ]);
    }
}
