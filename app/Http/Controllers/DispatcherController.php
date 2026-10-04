<?php

namespace App\Http\Controllers;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Http\Requests\PlaceOrderCreateCustomerRequest;
use App\Http\Requests\TransportManualRideBookRequest;
use App\model\Admin;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\ShopperProviderDetails;
use App\model\StoreDetails;
use App\model\TransportCourierDetails;
use App\model\TransportDriverDetails;
use App\model\TransportRideBook;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserPackageBooking;
use App\model\UserProductBooking;
use App\model\UserProductBookingOptions;
use App\model\UserProductBookingQuantity;
use App\model\UserProductBookingSize;
use App\model\UserProductBookingStatus;
use App\model\UserProductBookingToppings;
use App\model\UserRunningRide;
use App\model\UserWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\model\UserAddress;
use App\model\UserOrderCart;
use Illuminate\Support\Arr;
use App\Classes\UserClassApi;
use App\model\GeneralSettings;
use App\model\StoreProductDetails;
use App\model\StoreProductOptions;
use App\model\StoreProductSize;
use App\model\StoreProductToppings;
use App\model\StoreRatings;
use App\model\StoreTimings;
use App\model\DeliveryCharges;
use App\model\StoreProductCategory;


class DispatcherController extends Controller
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
    private $medicineDelivery;
    private $notificationClass;


    private $courier_service_id_array;
    private $delivery_service_id_array;

    private $all;
    private $available;
    private $ride_start;
    private $reached;
    private $enroute;
    private $is_restricted = 0;

    public function __construct(AdminClass $adminClass, NotificationClass $notificationClass)
    {
        $this->middleware('auth');
        $this->adminClass = $adminClass;
//        $this->on_demand_service_id_array = [11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30];
        $service_cat = ServiceCategory::query()->whereIN('category_type', [3, 4])->where('status', 1)->get()->pluck('id')->toArray();
        $this->on_demand_service_id_array = $service_cat;
        $this->transport_service_id_array = [1, 2, 4, 31, 32];
        $this->courier_service_id_array = [4];
        $this->delivery_service_id_array = [5, 6, 7, 8, 9, 10];

        $this->store_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->transport_ride_status = ['pending', 'accepted', 'schedule-accepted', 'arrived', 'cancelled', 'running', 'drop', 'payment', 'rating', 'completed', 'failed'];
        $this->store_order_status = ['', 'pending', 'approved', 'rejected', 'cancelled', 'processing', 'approved-by-driver', 'arrived-driver', 'ongoing', 'completed', 'failed'];
//        $this->on_demand_order_status = ['', 'pending', 'accepted', 'schedule-accepted', 'rejected', 'cancelled', 'ongoing', 'arrived', 'processing', 'completed', 'failed'];
        $this->bike_ride = 1;
        $this->taxi_ride = 2;
        $this->courier_service = 4;
        $this->food_delivery = 5;
        $this->grocery_delivery = 6;
        $this->medicine_delivery = 10;

        $this->notificationClass = $notificationClass;
        $this->all = [0, 4, 9, 10];
        $this->available = [0, 4, 9, 10];
        $this->ride_start = [5, 6, 7, 8];
        $this->reached = [3];
        $this->enroute = [1, 2,];

        $this->middleware( function ($request, $next) {
            $is_restrict_admin = $request->get('is_restrict_admin');
            $this->is_restricted = $is_restrict_admin;
            return $next($request);
        });
    }

    public function getAdminDashboard(Request $request)
    {
        if (Auth::guard("admin")->user()->roles == 1) {
            return redirect()->route('get:admin:dashboard');
        } elseif (Auth::guard("admin")->user()->roles == 3) {
            return redirect()->route('get:account:dashboard');
        } elseif (Auth::guard("admin")->user()->roles == 2) {
            return redirect()->route('get:dispatcher:manual_ride_booking');
        } else {
            Auth::guard('admin')->logout();
            return redirect()->route('get:admin:login');
        }
    }

    //get Transport Manual Ride Booking
    public function getTransportManualRideBooking(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 2) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        $service_category = ServiceCategory::where('id', $this->bike_ride)->first();
        $expire_date_time = date('Y-m-d H:i:s', strtotime("-10 minutes"));
        $provider_lists = TransportDriverDetails::query()->select(
            'providers.avatar', DB::raw("CONCAT(COALESCE(providers.first_name,'')) as driver_name"), 'providers.contact_number',
            'service_category.slug as service_cat_slug', 'service_category.id as service_cat_id',
            'transport_driver_details.id as driver_details_id', 'transport_driver_details.vehicle_type_id',
            'transport_vehicle_type.name as vehicle_type_name', 'transport_vehicle_type.cost_for_km',
            'transport_vehicle_type.time_fare'
        )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            ->where('service_category.id', $this->bike_ride)
            ->where('provider_services.status', '=', 1)
            ->where('providers.status', '=', 1)
            ->where('transport_vehicle_type.status', '=', 1)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->where('provider_services.current_status', 1)
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token')
            ->get();
        $service_settings = ServiceSettings::query()->where('service_cat_id', $service_category->id)->first();
        if ($service_settings != Null) {
            $admin_commission = $service_settings->admin_commission;
            $get_tax = $service_settings->tax;
        } else {
            $admin_commission = 0;
            $get_tax = 0;
        }
        $service_category_list = ServiceCategory::whereIn('id', [$this->bike_ride, $this->taxi_ride])->get();
        $view = view('admin.pages.transport_services.ride_book.dispatcher_admin_form', compact('get_tax', 'admin_commission', 'service_category', 'provider_lists', 'service_category_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Ajax Search Drivers Manual Ride Book
    public function getAjaxTransportSearchDriversManualRideBook(Request $request)
    {
        $search = $request->get('search_drivers');
        $service_cat_id = $request->get('slug');
        if ($service_cat_id == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Drivers not found'
            ]);
        }
        $expire_date_time = date('Y-m-d H:i:s', strtotime("-10 minutes"));
        $provider_lists_check = TransportDriverDetails::query()
            ->select(
                'providers.avatar', DB::raw("CONCAT(COALESCE(providers.first_name,'')) as driver_name"), 'providers.contact_number',
                'service_category.slug as service_cat_slug', 'service_category.id as service_cat_id',
                'transport_driver_details.id as driver_details_id', 'transport_driver_details.vehicle_type_id',
                'transport_vehicle_type.name as vehicle_type_name', 'transport_vehicle_type.cost_for_km',
                'transport_vehicle_type.time_fare'
            )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            ->where('service_category.id', $service_cat_id)
            ->where('provider_services.status', '=', 1)
            ->where('providers.status', '=', 1)
            ->where('transport_driver_details.driver_service', 1)
            ->where('transport_vehicle_type.status', '=', 1)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->where('provider_services.current_status', 1)
            ->where('transport_driver_details.driver_service', 1)
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token');
        if ($search == Null) {
            $provider_lists = $provider_lists_check->get();
        } else {
            $provider_lists = $provider_lists_check->where(DB::raw("CONCAT(providers.first_name,' ',providers.last_name) as name"), 'LIKE', "%$search%")
                ->get();
        }
        if ($provider_lists->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Drivers not found'
            ]);
        }
        $providers = [];
        foreach ($provider_lists as $key => $provider) {
            $providers[$key] = [
                "avatar" => $provider->avatar,
                "driver_name" => $provider->driver_name,
                "contact_number" => User::ContactNumber2Stars($provider->contact_number),
                "service_cat_slug" => $provider->service_cat_slug,
                "service_cat_id" => $provider->service_cat_id,
                "driver_details_id" => $provider->driver_details_id,
                "vehicle_type_id" => $provider->vehicle_type_id,
                "vehicle_type_name" => $provider->vehicle_type_name,
                "cost_for_km" => $provider->cost_for_km,
                "time_fare" => $provider->time_fare,
            ];
        }
        return response()->json([
            'success' => true,
            'drivers' => $providers,
        ]);
//        return response()->json([
//            'success' => true,
//            'drivers' => $provider_lists,
//        ]);
    }

    //get Ajax Drivers Manual Ride Book Change Category
    public function getAjaxTransportDriversManualRideBookChangeCategory(Request $request)
    {
        $service_cat_id = $request->get('slug');
        if ($service_cat_id == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Drivers not found'
            ]);
        }
        $expire_date_time = date('Y-m-d H:i:s', strtotime("-10 minutes"));
        $provider_lists = TransportDriverDetails::query()
            ->select(
                'providers.avatar', DB::raw("CONCAT(COALESCE(providers.first_name,'')) as driver_name"), 'providers.contact_number',
                'service_category.slug as service_cat_slug', 'service_category.id as service_cat_id',
                'transport_driver_details.id as driver_details_id', 'transport_driver_details.vehicle_type_id',
                'transport_vehicle_type.name as vehicle_type_name', 'transport_vehicle_type.cost_for_km',
                'transport_vehicle_type.time_fare'
            )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            ->where('service_category.id', $service_cat_id)
            ->where('provider_services.status', '=', 1)
            ->where('providers.status', '=', 1)
            //->where('transport_driver_details.driver_service', 1)
            ->where('transport_vehicle_type.status', '=', 1)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            //->where('provider_services.current_status', 1)
            //->whereNotNull('transport_driver_details.current_lat')
            //->whereNotNull('transport_driver_details.current_long')
            //->whereNotNull('providers.access_token')
            //->whereNotNull('providers.device_token')
            ->get();

        if ($provider_lists->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Drivers not found'
            ]);
        }
        return response()->json([
            'success' => true,
            'drivers' => $provider_lists,
        ]);
    }


    public function getAjaxServiceSetting(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == Null) {
            return response()->json([
                'success' => false,
                'tax' => 0,
                'admin_commission' => 0,
            ]);
        }
        $service_settings = ServiceSettings::where('service_cat_id', $service_cat_id)->first();
        if ($service_settings != Null) {
            $admin_commission = $service_settings->admin_commission;
            $get_tax = $service_settings->tax;
        } else {
            $admin_commission = 0;
            $get_tax = 0;
        }

        return response()->json([
            'success' => true,
            'tax' => $get_tax,
            'admin_commission' => $admin_commission,
        ]);
    }

    //Update or Store Manual Ride of Transport
    public function postUpdateStoreManualRide(TransportManualRideBookRequest $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        if (Auth::guard("admin")->user()->roles != 2) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        try {
            $transport_ride_book = new TransportRideBook();
            $transport_ride_book->user_id = $request->get('user_id');
            $transport_ride_book->service_cat_id = $request->get('service_cat_id');
            $transport_ride_book->driver_id = $request->get('driver_id');
            $transport_ride_book->vehicle_type_id = $request->get('vehicle_type_id');
            $transport_ride_book->ride_no = $transport_ride_book->generateRideNo();
            $transport_ride_book->pickup_datetime = $request->get('pickup_datetime');
            $transport_ride_book->destination_datetime = date('Y-m-d H:i:s', strtotime($request->get('eta'), strtotime($request->get('pickup_datetime'))));
            $transport_ride_book->hail_ride = 0;
            $transport_ride_book->ride_type = 1;
            $transport_ride_book->pickup_address = $request->get('pickup_address');
            $transport_ride_book->pickup_latlong = $request->get('pickup_lat') . ',' . $request->get('pickup_long');
            $transport_ride_book->destination_address = $request->get('destination_address');
            $transport_ride_book->destination_latlong = $request->get('destination_lat') . ',' . $request->get('destination_long');
            $transport_ride_book->user_name = $request->get('customer_name');
            $transport_ride_book->driver_name = $request->get('driver_name');
            $transport_ride_book->vehicle_type_name = $request->get('vehicle_type_name');
            $transport_ride_book->vehicle_cost_for_km = $request->get('vehicle_cost_for_km');
            $transport_ride_book->total_distance = $request->get('total_distance');
            $transport_ride_book->eta = $request->get('eta');
            $transport_ride_book->time_fare_amount = $request->get('time_fare_amount');
            $transport_ride_book->total_distance_amount = $request->get('base_fare');
//        $transport_ride_book->base_fare = $request->get('base_fare');
            $transport_ride_book->tax = $request->get('tax');
            $transport_ride_book->total_pay = $request->get('total_pay');
            $transport_ride_book->driver_amount = $request->get('driver_amount');

            $transport_ride_book->payment_type = 1;
//        $transport_ride_book->payment_status = 0;
//        $transport_ride_book->promo_code = 0;
            //2 = scheduled-accepted
            $transport_ride_book->status = 2;
            $transport_ride_book->save();

            $user_details = User::query()->where('id', $transport_ride_book->user_id)->first();
            if ($user_details != Null) {
                $this->notificationClass->userTransportNotification($transport_ride_book->id, $user_details->device_token, $transport_ride_book->status, $user_details->login_device,$user_details->language);
                $driver_details = Provider::query()->select('providers.id', 'providers.login_device', 'providers.device_token', 'providers.language')
                    ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                    ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                    ->where('transport_driver_details.id', $transport_ride_book->driver_id)->first();
                if ($driver_details != Null) {
                    ProviderUserRunningService::query()->where('provider_id', $driver_details->id)->where('user_id', $transport_ride_book->user_id)->where('service_cat_id', $transport_ride_book->service_cat_id)->where('booking_id', $transport_ride_book->id)->delete();
                    $provider_running_service = new ProviderUserRunningService();
                    $provider_running_service->provider_id = $driver_details->id;
                    $provider_running_service->user_id = $transport_ride_book->user_id;
                    $provider_running_service->service_cat_id = $transport_ride_book->service_cat_id;
                    $provider_running_service->booking_id = $transport_ride_book->id;
                    $provider_running_service->save();
                    $this->notificationClass->driverManualAssignTransportRequestNotification($transport_ride_book->id, $driver_details->device_token, $driver_details->login_device, $driver_details->language);
                }
            }
            Session::flash('success', 'Ride booking successfully!');
            return redirect()->back();
        } catch (\Exception $exception) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
    }

    public function getTransportServiceRideList(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 2) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null && $status_check == Null) {
//            Session::flash('error', 'Category ride list not found!');
//            return redirect()->back();
//        }
        $ride_lists = TransportRideBook::query()
            ->select('user_ride_booking.id', 'user_ride_booking.ride_no', 'user_ride_booking.service_cat_id',
                'user_ride_booking.total_pay', 'user_ride_booking.status', 'user_ride_booking.pickup_address',
                'user_ride_booking.destination_address',
//                'user_ride_booking.vehicle_type_name as vehicle_name',
                'user_ride_booking.user_name',
                'user_ride_booking.driver_name',
                'transport_vehicle_type.name as vehicle_name'
            )
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_ride_booking.vehicle_type_id')
//            ->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->orderBy('user_ride_booking.pickup_datetime', 'desc')
            ->whereIn('user_ride_booking.service_cat_id', [$this->bike_ride, $this->taxi_ride])
            ->whereIn('user_ride_booking.status', [0, 2, 4, 5, 9])
            ->where('user_ride_booking.ride_type', '=', 1)
            ->get()->toArray();
        $status = "scheduled";

        $transport_services = $this->transport_service_id_array;
        $courier_service = $this->courier_service_id_array;
        $delivery_service = $this->delivery_service_id_array;

        $view = view('admin.pages.transport_services.dispatcher_admin_ride_list', compact('transport_services', 'courier_service', 'delivery_service',  'status', 'ride_lists'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //provider display on map in My Bantay view function
    public function getAdminTransportProviderLocation(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 2) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        $expire_date_time = date('Y-m-d h:i:s', strtotime("-120 minutes"));
        $running_service = ProviderUserRunningService::all();
        $transport_running_service = [];
        $delivery_running_service = [];
        foreach ($running_service as $key => $running) {
            if (in_array($running->service_cat_id, $this->transport_service_id_array)) {
                $transport_running_service[$running->provider_id] = TransportRideBook::select('status')->where('id', $running->booking_id)->first();
            } elseif (in_array($running->service_cat_id, $this->delivery_service_id_array)) {
                $delivery_running_service[$running->provider_id] = UserProductBooking::select('order_delivery_status')->where('id', $running->booking_id)->first();
            }
        }
        $providers_detials = TransportDriverDetails::select(
            'transport_driver_details.id',
            'providers.id as provider_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.last_name', 'providers.contact_number',
            'providers.avatar',
            'transport_driver_details.current_lat',
            'transport_driver_details.current_long',
            'transport_driver_details.availability_ride_status',
            'service_category.id as service_cat_id'
        )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->whereIn('service_category.id', $this->transport_service_id_array)
//            ->where('provider_services.current_status', 1)
            ->where('provider_services.status', 1)
            ->where('providers.status', 1)
            ->whereNotNull('providers.access_token')
            ->get();
        $availability_ride_status = 3;
        if ($providers_detials->isEmpty()) {
            $providers = [];
        } else {
            foreach ($providers_detials as $key => $provider) {
                $availability_ride_status = 3;
                if (array_key_exists($provider->provider_id, $transport_running_service)) {
                    if (in_array($transport_running_service[$provider->provider_id]['status'], $this->available)) {
                        $availability_ride_status = 3;
                    } elseif (in_array($transport_running_service[$provider->provider_id]['status'], $this->ride_start)) {
                        $availability_ride_status = 2;
                    } elseif (in_array($transport_running_service[$provider->provider_id]['status'], $this->reached)) {
                        $availability_ride_status = 1;
                    } elseif (in_array($transport_running_service[$provider->provider_id]['status'], $this->enroute)) {
                        $availability_ride_status = 0;
                    }
                } elseif (array_key_exists($provider->provider_id, $delivery_running_service)) {
                    if (in_array($delivery_running_service[$provider->provider_id]['order_delivery_status'], $this->available)) {
                        $availability_ride_status = 3;
                    } elseif (in_array($delivery_running_service[$provider->provider_id]['order_delivery_status'], $this->ride_start)) {
                        $availability_ride_status = 2;
                    } elseif (in_array($delivery_running_service[$provider->provider_id]['order_delivery_status'], $this->reached)) {
                        $availability_ride_status = 1;
                    } elseif (in_array($delivery_running_service[$provider->provider_id]['order_delivery_status'], $this->enroute)) {
                        $availability_ride_status = 0;
                    }
                }
                $providers[$key] = [
                    "id" => $provider->id,
                    "name" => $provider->name,
                    "last_name" => Null,
                    "image" => $provider->avatar,
                    "latitude" => $provider->current_lat,
                    "longitude" => $provider->current_long,
                    "address" => "",
                    "ride_status" => $availability_ride_status,
                    "phone" => $provider->contact_number,
                    "service_cat_id" => $provider->service_cat_id,
                ];
            }
        }
        $view = view('admin.pages.transport_services.dispatcher_admin_gods_view', compact( 'providers'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //ride details
    public function getTransportRideDetails($id, Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 2) {
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
            ->where('user_ride_booking.id', $id)
            ->whereIn('user_ride_booking.service_cat_id', [$this->bike_ride, $this->taxi_ride])
            ->first();

        if ($ride_Details == Null) {
            return redirect()->back();
        }
        $user_details = User::select('id', 'contact_number', 'email')->where('id', $ride_Details->user_id)->first();
        $driver_details = TransportDriverDetails::select('transport_driver_details.id', 'providers.email', 'providers.contact_number')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->where('transport_driver_details.id', $ride_Details->driver_id)
            ->first();

        $courier_details = Null;

        $total = round($ride_Details->total_distance_amount + $ride_Details->time_fare_amount + $ride_Details->base_fare + $ride_Details->surcharge_cost, 2);
//        $total = round($ride_Details->total_distance_amount + $ride_Details->time_fare_amount + $ride_Details->base_fare + $ride_Details->tax, 2);
        $discount = round(0, 2);
        $grand_total = round($ride_Details->total_pay - $discount, 2);


        $view = view('admin.pages.transport_services.ride.dispatcher_admin_ride_details', compact('ride_Details', 'driver_details', 'user_details', 'grand_total', 'total', 'discount'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }


    public function getAdminTransportAllProviderLocation(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == 0) {
            $check_type = 1;
//            return $providers = [];
        } else {
            $check_type = 0;
        }

        $service_category = ServiceCategory::where('id', $service_cat_id)->first();

        return $this->adminClass->AllProviderLocation($service_category, $check_type);

    }

    public function getAdminTransportAvailableProviderLocation(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == 0) {
            $check_type = 1;
//            return $providers = [];
        } else {
            $check_type = 0;
        }
        $service_category = ServiceCategory::where('id', $service_cat_id)->first();

        return $this->adminClass->AvailableProviderLocation($service_category, $check_type);
    }

    public function getAdminTransportRideStartProviderLocation(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == 0) {
            $check_type = 1;
//            return $providers = [];
        } else {
            $check_type = 0;
        }
        $service_category = ServiceCategory::where('id', $service_cat_id)->first();
        return $this->adminClass->RideStartProviderLocation($service_category, $check_type);
    }

    public function getAdminTransportRideReachedProviderLocation(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == 0) {
            $check_type = 1;
//            return $providers = [];
        } else {
            $check_type = 0;
        }
        $service_category = ServiceCategory::where('id', $service_cat_id)->first();
        return $this->adminClass->RideReachedProviderLocation($service_category, $check_type);

    }

    public function getAdminTransportRideEnrouteProviderLocation(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == 0) {
            $check_type = 1;
//            return $providers = [];
        } else {
            $check_type = 0;
        }
        $service_category = ServiceCategory::where('id', $service_cat_id)->first();
        return $this->adminClass->RideEnrouteProviderLocation($service_category, $check_type);
    }

    public function getAdminProfile(Request $request)
    {
        if (Auth::guard("admin")->user()->roles != 2) {
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
        if (Auth::guard("admin")->user()->roles != 2) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        $admin = Admin::where('id', Auth::guard('admin')->user()->id)->first();
        if ($admin != Null) {
            $admin->name = $request->get("name");
            $admin->save();
            Session::flash('success', 'Dispatcher Admin profile update successfully!');
            return redirect()->route('get:dispatcher:profile');
        }
        Session::flash('error', 'Admin Details Not Found!');
        return redirect()->back();
    }


    //store dispatcher
    public function getStoreServiceDispatcher(Request $request, $slug, $slug2)
    {
        if (Auth::guard("admin")->user()->roles != 2) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
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
            'user_store_product_booking.is_driver_reassign',
            'user_store_product_booking.created_at',
            'user_store_product_booking.taken_away_type',
            'user_store_product_booking.order_type',
            'store_details.image as store_banner',
            'store_details.name as store_name',
            'store_details.address as pickup_address')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->where('user_store_product_booking.status', '!=', 11)
            ->where('user_store_product_booking.service_cat_id', $service_category->id)
            ->whereIN('user_store_product_booking.status', $status)
            ->where('user_store_product_booking.created_at', '>=', $date)
            ->orderBy('user_store_product_booking.id', "desc")
            ->get();
        $pending_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $new)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $approved_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $approved)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $processing_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $processing)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $ongoing_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $ongoing)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $completed_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $completed)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $cancelled_orders = UserProductBooking::where('service_cat_id', $service_category->id)->whereIN('user_store_product_booking.status', $cancelled)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $order_slug = $slug2;
        $view = view('admin.pages.store_services.dispatcher_admin_store_dispatcher', compact('slug', 'service_category', 'store_order_list', 'pending_orders', 'approved_orders', 'processing_orders', 'ongoing_orders', 'completed_orders', 'cancelled_orders', 'order_slug'));
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
                'message' => 'Data not found'
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
                //if ($store_order->status == 2) {
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
                            $notify = $this->notificationClass->driverCancelNotification($order_status->id,$driver_details->device_token, $store_order->service_cat_id, $driver_details->id, $driver_details->login_device, $driver_details->language,$cancel_by);
                        }
                    }
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
            case 5:
                if ($store_order->status == 2) {
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
            'success' => true
        ]);
    }

    public function getStoreOrderDetails(Request $request, $slug, $order_id)
    {
        if (Auth::guard("admin")->user()->roles != 2) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $order_details = UserProductBooking::query()->select("user_store_product_booking.*","used_promocode_details.promocode_name","used_promocode_details.discount_amount")
            ->leftJoin('used_promocode_details','used_promocode_details.id','user_store_product_booking.promo_code')
            ->where('user_store_product_booking.id', '=',$order_id)->first();
        if ($order_details == Null) {
            return redirect()->back();
        }
        $store_details = StoreDetails::select('address')->where('id', $order_details->store_id)->first();
        $user_details = User::select('contact_number')->where('id', $order_details->user_id)->first();
        $get_order_status = UserProductBookingStatus::select('status', 'created_at')->where('order_id', $order_details->id)->get();
        $order_status = $this->store_order_status;
        $product_details = UserProductBookingQuantity::select('id','product_name', 'product_cat_name', 'num_of_items', 'price_for_one')->where('order_id', $order_details->id)->get()->groupBy('product_cat_name');
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
            ->where('transport_driver_details.id', $order_details->delivery_people_id)
            ->first();
        $shopper_details = Null;
        /*if ($order_details->shopper_type == 1) {
            $shopper_details = ShopperProviderDetails::select(
                'providers.name as shopper_name', 'providers.contact_number as shopper_contact_number',
                'shopper_provider_details.id as shopper_details_id'
            )
                ->join('providers', 'providers.id', 'shopper_provider_details.provider_id')
                //->where('providers.status', '=', $status)
                ->where('providers.id', $order_details->shopper_id)
                ->first();
        }*/
        $view = view('admin.pages.store_services.dispatcher_admin_store_order_details', compact('slug', 'product_option_list','shopper_details', 'order_details', 'get_order_status', 'order_status', 'product_details', 'store_details', 'user_details', 'driver_details', 'service_category'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Admin Store Manual Assign Driver
    public function getStoreManualAssignDriver(Request $request, $slug, $order_id)
    {
        if (Auth::guard("admin")->user()->roles != 2) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
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

        $general_settings = $request->get("general_settings");

        // store manual assign so skip transport for multi driver
        $skip_ids = [1,2,4,31,32];
        $not_multi_delivery_provider_id = ProviderUserRunningService::query()->whereIn('service_cat_id',$skip_ids)->get()->pluck('provider_id');

        $lat = $store_details->address_lat;
        $long = $store_details->address_long;
        $expire_date_time = date('Y-m-d h:i:s', strtotime("-10 minutes"));
        $driver_list = Provider::query()->select('providers.id as driver_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'providers.contact_number')
            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
            ->where('service_category.id', 1)
            ->where('providers.status', 1)
            //->where('providers.login_device', 1)
            ->where('provider_services.status', 1)
            ->where('provider_services.current_status', 1)
            ->where('transport_driver_details.delivery_boy', 1)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token');

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

        $view = view('admin.pages.store_services.manual_assign_driver.form', compact('slug', 'service_category', 'order_id', 'driver_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;

    }

    //post Admin Store Manual Assign Driver
    public function postUpdateStoreManualAssignDriver(Request $request, $slug)
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
                    $driver_details = TransportDriverDetails::query()->select('providers.device_token','providers.language',
                        'providers.id as driver_id', 'transport_driver_details.id', 'transport_driver_details.delivery_boy',
                        DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"),
                        'provider_services.id as provider_service_id', 'providers.login_device')
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
                            if ($driver_details->delivery_boy != 1) {
                                $driver_service->current_status = 0;
                                $driver_service->save();
                            }

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

//                        dd($driver_assign_check);

                            Session::flash('success', 'Driver assign successfully!');
                            $order_slug = "processing";
                            return redirect()->route('get:dispatcher:store_service_dispatcher', [$slug, $order_slug]);

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

    //get Admin Store Manual Assign Driver
    public function getStoreManualAssignShopper(Request $request, $slug, $order_id)
    {
        if (Auth::guard("admin")->user()->roles != 2) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
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
        $store_details = StoreDetails::query()->where('id', $order_details->store_id)->first();
        if ($store_details == Null) {
            Session::flash('error', 'Store Details Not Found!');
            return redirect()->back();
        }
        $lat = $store_details->address_lat;
        $long = $store_details->address_long;
        $expire_date_time = date('Y-m-d h:i:s', strtotime("-60 minutes"));
        $shopper_list = Provider::select('providers.id as shopper_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as shopper_name"), 'providers.contact_number',
            'providers.service_radius',
            DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $lat . ' - shopper_provider_details.lat) *  pi()/180 / 2), 2)
                +COS( ' . $lat . ' * pi()/180)
                * COS(shopper_provider_details.lat * pi()/180)
                * POWER(SIN(( ' . $long . ' - shopper_provider_details.long) * pi()/180 / 2), 2) ))) as distance')
        )
            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('shopper_provider_details', 'shopper_provider_details.provider_id', '=', 'providers.id')
            ->where('service_category.id', $order_details->service_cat_id)
            ->where('providers.status', 1)
            ->where('provider_services.status', 1)
            ->where('provider_services.current_status', 1)
            ->where('shopper_provider_details.last_online_date_time', '>=', $expire_date_time)
            ->whereNotNull('shopper_provider_details.lat')
            ->whereNotNull('shopper_provider_details.long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token')
//            ->havingRaw('providers.service_radius > distance')
            ->get();
        $view = view('admin.pages.store_services.manual_assign_shopper.form', compact('slug', 'service_category', 'order_id', 'shopper_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;

    }

    public function postUpdateStoreManualAssignShopper(Request $request, $slug)
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
        $store_order = UserProductBooking::query()->where('id', $request->get('order_id'))->first();
        if ($store_order != Null) {
            if ($store_order->status == 3) {
                Session::flash('error', 'Sorry, order is rejected by store!');
                return redirect()->back();
            } elseif ($store_order->status == 4) {
                Session::flash('error', 'Sorry, order request is cancelled by user!');
                return redirect()->back();
            } elseif ($store_order->shopper_id != Null) {
                Session::flash('error', 'Sorry, order is already assign by shopper!');
                return redirect()->back();
            } else {
                if ($store_order->order_shopper_status == 2) {
                    Session::flash('error', 'Sorry, order is already assign!');
                    return redirect()->back();
                }
                $driver_details = ShopperProviderDetails::query()->select(DB::raw("CONCAT(providers.first_name,' ',providers.last_name) as shopper_name"), 'providers.id as shopper_id', 'provider_services.id as shopper_service_id', 'providers.device_token', 'providers.login_device')
                    ->join('providers', 'providers.id', 'shopper_provider_details.provider_id')
                    ->join('provider_services', 'provider_services.provider_id', 'providers.id')
                    ->where('providers.id', $request->get('shopper_id'))
                    ->where('providers.status', 1)
                    ->where('provider_services.status', 1)
                    ->where('provider_services.current_status', 1)
                    ->first();
                if ($driver_details != Null) {
                    $shopper_id = $driver_details->shopper_id;
                    $shopper_name = $driver_details->shopper_name;
                    $shopper_service_id = $driver_details->shopper_service_id;
                    $login_device = $driver_details->login_device;
                    $device_token = $driver_details->device_token;

                    $driver_service = ProviderServices::query()->where('id', $shopper_service_id)->first();
                    if ($driver_service != Null) {
                        $store_order->shopper_id = $shopper_id;
                        $store_order->shopper_name = $shopper_name;
                        $store_order->order_shopper_status = 2;
                        $store_order->save();
                        $store_details = StoreDetails::query()->select('providers.device_token', 'providers.login_device')
                            ->join('provider_services', 'provider_services.id', 'store_details.provider_service_id')
                            ->join('providers', 'providers.id', 'provider_services.provider_id')
                            ->where('providers.id', $store_order->store_id)
                            ->where('providers.status', 1)
                            ->where('provider_services.status', 1)
                            ->where('provider_services.current_status', 1)
                            ->first();
                        if ($store_details != Null) {
                            $this->notificationClass->storeDeliveryShopperNotification($store_order->id, $store_details->device_token, $store_order->order_shopper_status, $store_details->login_device);
                        }

                        $shopper_assign_check = $this->notificationClass->shoperManualAssignDeliveryRequestNotification($store_order->id, $device_token, $login_device);

                        Session::flash('success', 'Shopper assign successfully!');
                        $order_slug = "approved";
                        return redirect()->route('get:dispatcher:store_service_dispatcher', [$slug, $order_slug]);
                    } else {
                        Session::flash('error', 'shopper service not found!');
                        return redirect()->back();
                    }
                } else {
                    Session::flash('error', 'Sorry, shopper not found!');
                    return redirect()->back();
                }
            }
        } else {
            Session::flash('error', 'Order not found!');
            return redirect()->back();
        }
    }

    public function getTransportUpdateRideStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "update_status" => "required|numeric|in:4,9"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first()
            ]);
        }
        $id = $request->get('id');
        $update_status = $request->get('update_status');
        if ($update_status == 4) {
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
        $transport_ride = TransportRideBook::query()->where('id', $id)->first();
        if ($transport_ride == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $user_details = User::query()->where('id', $transport_ride->user_id)->first();
        if ($transport_ride->status == 4) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        switch ($update_status) {
            case 4:
                if ($transport_ride->status != 4 && $transport_ride->status != 9 && $transport_ride->status != 10) {
                    $ride_status = $transport_ride->status;
                    $transport_ride->otp = null;
                    $transport_ride->status = $update_status;
                    $transport_ride->cancel_by = "admin";
                    $transport_ride->cancel_reason = $request->get('reason');
                    $transport_ride->save();

                    UserRunningRide::query()->where('service_cat_id', $transport_ride->service_cat_id)->where('user_id', $transport_ride->user_id)->where('booking_id', $transport_ride->id)->delete();

                    if($transport_ride->service_cat_id == $this->courier_service) {
                        if ($transport_ride->payment_type == 2 || $transport_ride->payment_type == 3) {
                            $service_settings = ServiceSettings::query()->where('service_cat_id', $transport_ride->service_cat_id)->first();
                            if ($service_settings != Null) {
                                $cancel_charge = $service_settings->cancel_charge != Null ? $service_settings->cancel_charge : 0;
                                $count_cancel_charge = round((($transport_ride->total_pay * $cancel_charge) / 100), 2);
                                $charges = round($transport_ride->total_pay - $count_cancel_charge, 2);
                                $transport_ride->cancel_charge = $count_cancel_charge;
                                $transport_ride->refund_amount = $charges;
                                $transport_ride->save();
                            }
                        }
                    }

                    if ($ride_status < 6){
                        if ($transport_ride->promo_code > 0){
                            $used_promocode_details = UsedPromocodeDetails::query()->where('id', $transport_ride->promo_code)->first();
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
                    if ($user_details != Null) {
                        $this->notificationClass->userTransportNotification($transport_ride->id, $user_details->device_token, $transport_ride->status, $user_details->login_device,$user_details->language);
                    }
                    if ($transport_ride->driver_id != Null) {
                        ProviderUserRunningService::query()->where('service_cat_id', $transport_ride->service_cat_id)->where('user_id', $transport_ride->user_id)->where('booking_id', $transport_ride->id)->delete();
                        $driver_details = Provider::query()->select('providers.device_token', 'provider_services.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.login_device', 'providers.language')
                            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                            ->where('transport_driver_details.id', $transport_ride->driver_id)->first();

                        if ($driver_details != Null) {
                            $cancel_by = "admin";
                            $this->notificationClass->driverCancelNotification($transport_ride->id,$driver_details->device_token, $transport_ride->service_cat_id, $driver_details->id, $driver_details->login_device, $driver_details->language, $cancel_by);
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
                if ($transport_ride->status > 0) {
                    if ($transport_ride->status != 4 && $transport_ride->status != 9 && $transport_ride->status != 10) {
                        $update_driver_status = Null;
                        $driver_details = Null;
                        if ($transport_ride->driver_id != Null) {
                            $driver_details = Provider::query()
                                ->select('providers.id as provider_id', 'providers.email', 'providers.device_token', 'provider_services.id as provider_service_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.login_device', 'providers.language')
                                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                                ->where('transport_driver_details.id', $transport_ride->driver_id)
                                ->first();
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
                        }
                        $transport_ride->status = 9;
                        $transport_ride->payment_status = 1;
                        $transport_ride->completed_by = 1;
                        $transport_ride->save();
                        if ($transport_ride->promo_code > 0){
                            $used_promocode_details = UsedPromocodeDetails::query()->where('id', $transport_ride->promo_code)->first();
                            if ($used_promocode_details != Null) {
                                $used_promocode_details->status = 1;
                                $used_promocode_details->save();
                            }
                        }
                        if ($user_details != Null) {
                            $this->notificationClass->userTransportNotification($transport_ride->id, $user_details->device_token, $transport_ride->status, $user_details->login_device,$user_details->language);
                        }
                        UserRunningRide::query()->where('service_cat_id', $transport_ride->service_cat_id)->where('user_id', $transport_ride->user_id)->where('booking_id', $transport_ride->id)->delete();

                        if ($update_driver_status != Null) {
                            $update_driver_status->current_status = 1;
                            $update_driver_status->save();
                        }
                        if ($driver_details != Null) {
                            ProviderUserRunningService::query()->where('provider_id', $driver_details->provider_id)->where('user_id', $transport_ride->user_id)->where('service_cat_id', $transport_ride->service_cat_id)->where('booking_id', $transport_ride->id)->delete();
                            $this->notificationClass->driverCompletedNotification($driver_details->device_token, $transport_ride->service_cat_id, $driver_details->id, $driver_details->login_device,$driver_details->language);
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
        return redirect()->route('get:dispatcher:place_order', [ "slug" => $slug, "user_id" => $user_details->id ]);
    }

    public function getAdminPlaceOrder(Request $request, $slug, $user_id, $user_address_id = 0) {
        $service_category = ServiceCategory::query()->where('slug', '=', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->route('get:dispatcher:add_users', $slug);
        }
        $user_details = User::query()->where('id', $user_id)->first();
        if ($user_details == Null) {
            Session::flash('error', 'User detail not found!');
            return redirect()->route('get:dispatcher:add_users', $slug);
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
            ->where('provider_services.service_cat_id', $service_category->id);
//            ->havingRaw('providers.service_radius > distance');

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

                        $check_near_delivery_address = StoreDetails::query()->select('store_details.id', 'providers.service_radius'
                            , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                            + COS( ' . $address_lat . ' * pi()/180)
                            * COS(store_details.address_lat * pi()/180)
                            * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
                        )
                            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                            ->where('provider_services.service_cat_id', $service_category->id)
                            ->where('store_details.id', $store_details->id)
                            /*->havingRaw('providers.service_radius > distance')*/
                            ->first();

//                        if ($check_near_delivery_address == Null) {
//                            return response()->json([
//                                "success" => false,
//                                "message" => "Your delivery address out of $store_details->name store delivery range! Please change your delivery address.",
//                            ]);
//                        }

//                        $area_id = 0;
//
//                        if ($delivery_lat != Null && $delivery_long != Null) {
//                            $area_wise_del_charge = RestrictedArea::query()->where('status', '1')->orderBy('id', 'desc')->get();
//                            foreach ($area_wise_del_charge as $singel_area_wise_charge) {
//                                $vertices_x = explode(",", $singel_area_wise_charge->latitude);    // x-coordinates of the vertices of the polygon
//                                $vertices_y = explode(",", $singel_area_wise_charge->longitude); // y-coordinates of the
//
//                                $points_polygon = count($vertices_x);  // number vertices - zero-based array
//                                $longitude_x = $delivery_lat;  // x-coordinate of the point to test
//                                $latitude_y = $delivery_long;   // y-coordinate of the point to test
//
//
//                                if ($adminClassApi->is_in_restricted_area($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
//                                    $area_id = $singel_area_wise_charge->id;
//                                    $delivery_cost = $singel_area_wise_charge->delivery_charge;
//                                    break;
//                                }
//                            }
//                        }
//                        if ($area_id == 0) {
//                            return response()->json([
//                                "success" => false,
//                                "message" => "Service is not available in your region, please try with other location!",
//                            ]);
//                        }
                    }

                    $total_distance = (isset($check_near_delivery_address->distance) && $check_near_delivery_address->distance != Null) ? $check_near_delivery_address->distance : 0;

                    $get_delivery_charge =  DeliveryCharges::query()->where('service_cat_id',$order_cart_service_cat_id)
                        ->where('start_limit','>=',$total_distance)
                        ->where('close_limit','<=',$total_distance)
                        ->first();

                    $delivery_charge_per_km = 0;
                    $delivery_charge_per_km = $userClassapi->checkDeliveryCharge($total_distance,$order_cart_service_cat_id);
                    if($get_delivery_charge != Null) {
                        $delivery_charge_per_km = $get_delivery_charge->charges;
                    }

                    $origin = urlencode($store_details->address_lat . "," . $store_details->address_long);
                    $get_coordinates = $userClassapi->get_coordinates($origin, $address_lat . ',' . $address_long);
                    $coordinates_check = json_decode($get_coordinates, TRUE);

                    if ($coordinates_check == 0) {
                        return response()->json([
                            "success" => false,
                            "message" => "Sorry, Something went wrong!",
                            /*Sorry, Something went wrong!*/
                        ]);
                    }

                    $total_distance = 0;
                    $total_mins = 0;
                    if (count($coordinates_check['routes']) > 0) {
                        //$total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['text']);
                        $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);
                        /*$total_mins = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['duration']['value']);*/
                    }
                    $delivery_cost_km = round((($total_distance * 0.001) * $delivery_charge_per_km), 2);
                    //$delivery_cost_min = round((($total_mins / 60) * $delivery_charge_per_min), 2);
                    $delivery_cost_min = 0;
                    $delivery_charge = $delivery_cost_km + $delivery_cost_min;

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
            return redirect()->route('get:dispatcher:place_order', ['slug' => $slug, 'user_id' => $user_id]);
        }
        $user_details = User::query()->where('id', $user_id)->first();
        if ($user_details == Null) {
            Session::flash('error', 'User detail not found!');
            return redirect()->route('get:dispatcher:place_order', ['slug' => $slug, 'user_id' => $user_id]);
        }
        if ($user_address_id == 0) {
            $user_address_id = $request->get('user_address');
        }
        $address_list = UserAddress::query()->where("id", $user_address_id)->where("user_id", $user_details->id)->where('status', 1)->first();

        if ($address_list == Null) {
            Session::flash('error', 'Please select address!');
            return redirect()->route('get:dispatcher:place_order', ['slug' => $slug, 'user_id' => $user_id]);
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

            $delivery_lat = isset($address_lat) ? $address_lat : 0;
            $delivery_long = isset($address_long) ? $address_long : 0;

//            if ($delivery_lat != Null && $delivery_long != Null) {
//                $adminClassApi = new AdminClass();
//                $area_wise_del_charge = RestrictedArea::query()->where('status', '1')->orderBy('id', 'desc')->get();
//                foreach ($area_wise_del_charge as $singel_area_wise_charge) {
//                    $vertices_x = explode(",", $singel_area_wise_charge->latitude);    // x-coordinates of the vertices of the polygon
//                    $vertices_y = explode(",", $singel_area_wise_charge->longitude); // y-coordinates of the
//
//                    $points_polygon = count($vertices_x);  // number vertices - zero-based array
//                    $longitude_x = $delivery_lat;  // x-coordinate of the point to test
//                    $latitude_y = $delivery_long;   // y-coordinate of the point to test
//
//                    if ($adminClassApi->is_in_restricted_area($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
//                        $area_id = $singel_area_wise_charge->id;
//                        $delivery_charge = $singel_area_wise_charge->delivery_charge;
//                        break;
//                    }
//                }
//            }
//            if ($area_id == 0) {
//                Session::flash('error', 'Service is not available in your region, please try with other location!');
//                return redirect()->back();
//            }
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
                if ($user_taken_type != 2) {
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
                        ->where('provider_services.service_cat_id', $service_category_id)
                        ->where('store_details.id', $store_id)
//                        ->havingRaw('providers.service_radius > distance')
                        ->first();
                    if ($check_near_delivery_address == Null) {
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

                $total_distance = (isset($check_near_delivery_address->distance) && $check_near_delivery_address->distance != null) ? $check_near_delivery_address->distance : 0;
                $get_delivery_charge =  DeliveryCharges::query()->where('service_cat_id',$service_category_id)
                    ->where('start_limit','>=',$total_distance)
                    ->where('close_limit','<=',$total_distance)
                    ->first();
                $delivery_charge_per_km = 0;
                $delivery_charge_per_km = $userClassapi->checkDeliveryCharge($total_distance,$service_category_id);
                if($get_delivery_charge != Null) {
                    $delivery_charge_per_km = $get_delivery_charge->charges;
                }
                $origin = urlencode($store_details->address_lat . "," . $store_details->address_long);
                $get_coordinates = $userClassapi->get_coordinates($origin, $address_lat . ',' . $address_long);
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
                    //$total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['text']);
                    $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);
                    /*$total_mins = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['duration']['value']);*/
                }
                $delivery_cost_km = round((($total_distance * 0.001) * $delivery_charge_per_km), 2);
                //$delivery_cost_min = round((($total_mins / 60) * $delivery_charge_per_min), 2);
                $delivery_cost_min = 0;
                $delivery_charge = $delivery_cost_km + $delivery_cost_min;

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
        if ($service_category_id == $this->medicine_delivery) {
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
            $place_order->otp = $order_book_details['otp'];
            /*$place_order->other_comment = $order_book_details['other_comment'];*/
            try{
                if ($service_category_id == $this->medicine_delivery) {
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
        return redirect()->route('get:dispatcher:store_service_dispatcher', ["slug" => $slug, "slug2" => "new"]);
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
