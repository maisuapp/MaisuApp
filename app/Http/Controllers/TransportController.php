<?php

namespace App\Http\Controllers;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Http\Requests\ProviderStoreRequest;
use App\Http\Requests\RequiredDocumentsRequest;
use App\Http\Requests\TransportManualRideBookRequest;
use App\Http\Requests\TransportVehicleTypeRequest;

use App\model\LanguageLists;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderDocuments;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ReassignedDriverList;
use App\model\RequiredDocuments;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\SurchargeTimings;
use App\model\TransportCategoryList;
use App\model\TransportDriverDetails;
use App\model\TransportHomePage;
use App\model\TransportRatings;
use App\model\TransportRentalRideBooking;
use App\model\TransportRideBook;
use App\model\TransportVehicleType;
use App\model\TransportWeightList;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserRunningRide;
use App\model\UserWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use App\model\TransportCourierDetails;
use App\model\UserProductBooking;
use App\model\UserProductBookingQuantity;
use App\model\UserProductBookingStatus;
use App\model\StoreDetails;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class TransportController extends Controller
{
    private $adminClass;
    private $bike_ride = 1;
    private $taxi_ride = 2;
    private $bike_rental = 31;
    private $taxi_rental = 32;
    private $courier_service = 4;
    private $driverClassApi;
    private $notificationClass;
    private $transport_service_id_array = [1, 2];
    private $courier_service_id_array = [4];
    private $delivery_service_id_array = [5, 6, 7, 8, 9, 10];
    private $courier_scooter = 10;
    private $courier_car = 14;

    private $store_order_status = ['', 'pending', 'approved', 'rejected', 'cancelled', 'processing', 'approved-by-driver', 'arrived-driver', 'ongoing', 'completed', 'failed'];

    private $all = [0, 4, 9, 10];
    private $available = [0, 4, 9, 10];
    private $ride_start = [5, 6, 7, 8];
    private $reached = [3];
    private $enroute = [1, 2,];
    private $transport_vehicle_id_array =  [10, 14];
    private $is_restricted = 0;

    public function __construct(AdminClass $adminClass, NotificationClass $notificationClass)
    {
        $this->adminClass = $adminClass;
        $this->notificationClass = $notificationClass;

        $this->middleware( function ($request, $next) {
            $is_restrict_admin = $request->get('is_restrict_admin');
            $this->is_restricted = $is_restrict_admin;
            return $next($request);
        });
    }

    //transport service dashboard
    public function getTransportServiceDashboard($slug, Request $request)
    {
        $service_category = ServiceCategory::select('id', 'name', 'category_type', 'slug')->where('slug', $slug)->first();
//        $provider_list_check = TransportDriverDetails::query()
//            ->select()
//            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
//            ->join('providers', 'providers.id', 'provider_services.provider_id')
//            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id');
//            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
        $date = date('Y-m-d', strtotime('-7 days'));
        if ($slug == "bike-ride") {
            $approved_drivers = TransportDriverDetails::join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('providers.status', '=', 1)
                ->whereIn('service_category.slug', [$slug, 'courier-service'])
                ->where('transport_driver_details.driver_service', '=', 1)
                ->where('provider_services.status', '=', 1)
                ->count();

            $active_drivers = TransportDriverDetails::join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('providers.status', '=', 1)
                ->whereIn('service_category.slug', [$slug, 'courier-service'])
                ->where('transport_driver_details.driver_service', '=', 1)
                ->where('provider_services.status', '=', 1)
                ->where('provider_services.current_status', '=', 1)
                ->count();

            $un_approved_drivers = TransportDriverDetails::join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('providers.status', '=', 0)
                ->whereIn('service_category.slug', [$slug, 'courier-service'])
                ->where('transport_driver_details.driver_service', '=', 1)
                ->where('provider_services.status', '=', 0)
                ->count();

            $admin_commission = TransportRideBook::selectRaw('
                 SUM(total_pay) - SUM(driver_amount) as admin_commission')
                ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
//                ->whereIn('service_category.slug', [$slug, 'courier-service'])
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.status', 9)
                ->first();

            $total_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                //->whereIn('service_category.slug', [$slug, 'courier-service'])
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->count();

            $total_completed_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->where('user_ride_booking.status', 9)
                ->count();

            $total_running_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->where('user_ride_booking.status', 5)
                ->count();

            $total_cancelled_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->where('user_ride_booking.status', 4)
                ->count();

        } elseif ($slug == "courier-service") {
            $approved_drivers = TransportDriverDetails::join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('providers.status', '=', 1)
                ->where('transport_driver_details.courier_boy', '=', 1)
                ->where('provider_services.status', '=', 1)
                ->count();

            $active_drivers = TransportDriverDetails::join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('providers.status', '=', 1)
                ->where('transport_driver_details.courier_boy', '=', 1)
                ->where('provider_services.status', '=', 1)
                ->where('provider_services.current_status', '=', 1)
                ->count();

            $un_approved_drivers = TransportDriverDetails::join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('providers.status', '=', 0)
                ->where('transport_driver_details.courier_boy', '=', 1)
                ->where('provider_services.status', '=', 0)
                ->count();

            $admin_commission = TransportRideBook::selectRaw('
                 SUM(total_pay) - SUM(driver_amount) as admin_commission')
                ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
//                ->whereIn('service_category.slug', [$slug, 'courier-service'])
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.status', 9)
                ->first();

            $total_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->count();

            $total_completed_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->where('user_ride_booking.status', 9)
                ->count();

            $total_running_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->where('user_ride_booking.status', 5)
                ->count();

            $total_cancelled_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->where('user_ride_booking.status', 4)
                ->count();

        } else {
            $approved_drivers = TransportDriverDetails::join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('providers.status', '=', 1)
                ->where('provider_services.status', '=', 1)
                ->where('service_category.slug', $slug)
                ->count();

            $active_drivers = TransportDriverDetails::join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('providers.status', '=', 1)
                ->where('provider_services.status', '=', 1)
                ->where('service_category.slug', $slug)
                ->where('provider_services.current_status', '=', 1)
                ->count();

            $un_approved_drivers = TransportDriverDetails::join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('providers.status', '=', 0)
                ->where('provider_services.status', '=', 0)
                ->where('service_category.slug', $slug)
                ->count();

            $admin_commission = TransportRideBook::selectRaw('
                 SUM(total_pay) - SUM(driver_amount) as admin_commission')
                ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.status', 9)
                ->first();


            $total_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->count();

            $total_completed_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->where('user_ride_booking.status', 9)
                ->count();

            $total_running_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->where('user_ride_booking.status', 5)
                ->count();

            $total_cancelled_ride = TransportRideBook::join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->groupBy('user_ride_booking.service_cat_id')
                ->where('service_category.slug', '=', $slug)
                ->where('user_ride_booking.pickup_datetime', '>=', $date)
                ->where('user_ride_booking.status', 4)
                ->count();
        }

        $total_earning = 0;
        if ($admin_commission != Null) {
            $total_earning = $admin_commission->admin_commission;
        }
        $n = (0 + str_replace(",", "", number_format($total_earning, 2)));
        if (!is_numeric($n)) {
            false;
        }
        if ($n > 1000000000000) {
            $total_admin_earning = round(($n / 1000000000000), 2) . ' T';
        } elseif ($n > 1000000000) {
            $total_admin_earning = round(($n / 1000000000), 2) . ' B';
        } elseif ($n > 1000000) {
            $total_admin_earning = round(($n / 1000000), 2) . ' M';
        } elseif ($n > 1000) {
            $total_admin_earning = round(($n / 1000), 2) . ' K';
        } else {
            $total_admin_earning = number_format($total_earning, 2);
        }

        if ($service_category != Null) {
            $view = view('admin.pages.super_admin.service_dashboard.transport_dashboard', compact('service_category', 'slug', 'approved_drivers', 'active_drivers', 'un_approved_drivers', 'total_admin_earning', 'total_ride', 'total_completed_ride', 'total_running_ride', 'total_cancelled_ride'));
        } else {
            $view = view('admin.pages.super_admin.service_dashboard.transport_dashboard', compact('slug', 'approved_drivers', 'active_drivers', 'un_approved_drivers', 'total_admin_earning', 'total_ride', 'total_completed_ride', 'total_running_ride', 'total_cancelled_ride'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Transport Service List
    public function getTransportServiceList(Request $request)
    {
        $transport_service_lists = ServiceCategory::query()->select('id', 'name', 'slug', 'icon_name', 'category_type', 'status')
            ->whereIn('category_type', [1, 5])
            ->where("id","!=",3)
            ->get();

        if ($transport_service_lists->isEmpty()) {
            $view = view('admin.pages.transport_services.transport_service_list');
        } else {
            $view = view('admin.pages.transport_services.transport_service_list', compact('transport_service_lists'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Transport Provider List
    public function getTransportProviderList($slug, Request $request)
    {

        $status = 1;
        $service_category = ServiceCategory::where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Category provider list not found!');
            return redirect()->back();
        }
        $service_category_id = $service_category->id;

        $provider_list_check = TransportDriverDetails::query()
            ->select(
                'service_category.slug as service_cat_slug',
                'service_category.id as service_cat_id',
                DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'providers.email', 'providers.contact_number',
                'providers.created_at',
                'provider_services.status', 'provider_services.id as provider_service_id',
                'transport_driver_details.id as driver_details_id', 'transport_driver_details.vehicle_company',
                'transport_driver_details.plat_no', 'transport_driver_details.model_name', 'transport_driver_details.model_year',
                'transport_driver_details.vehicle_color', 'transport_driver_details.rating', 'transport_driver_details.total_request',
                'transport_driver_details.total_completed', 'transport_driver_details.total_cancelled',
                'transport_vehicle_type.name as vehicle_type_name', 'transport_vehicle_type.icon_name as type_icon',
                'transport_driver_details.handicap',
                'transport_driver_details.child_seat'
            )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            //->where('providers.status', '=', $status)
            ->where('provider_services.status', '=', $status);
        if ($service_category_id == $this->bike_ride) {

            $provider_lists = $provider_list_check
                ->whereIn('service_category.slug', [$slug, 'courier-service'])
                ->where('transport_driver_details.driver_service', '=', 1)
                ->orderBy('transport_driver_details.id',  'desc')
                ->get();
        } elseif ($service_category_id == $this->courier_service) {
            $provider_lists = $provider_list_check->where('transport_driver_details.courier_boy', '=', 1)
                ->orderBy('transport_driver_details.id',  'desc')
                ->get();
        } elseif ($service_category_id == $this->bike_rental) {

            $provider_lists = $provider_list_check->where('service_category.id', $this->bike_ride)
                ->where('transport_driver_details.rental_service', '=', 1)
                ->orderBy('transport_driver_details.id',  'desc')
                ->get();
        } elseif ($service_category_id == $this->taxi_rental) {
            $provider_lists = $provider_list_check->where('service_category.id', $this->taxi_ride)
                ->where('transport_driver_details.rental_service', '=', 1)
                ->orderBy('transport_driver_details.id',  'desc')
                ->get();
        } else {
            $provider_lists = $provider_list_check->where('service_category.slug', $slug)
                ->orderBy('transport_driver_details.id',  'desc')
                ->get();
        }

        if ($request->ajax()) {
            $view = view('admin.pages.transport_services.transport_provider_list', compact('slug', 'service_category_id','service_category', 'status', 'provider_lists'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.transport_services.transport_provider_list', compact('slug', 'service_category_id','service_category', 'status', 'provider_lists'));
    }

    public function getTransportProviderListNew(Request $request){
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

        $status = $request->get('status');
        $service_category_id = $request->get('service_category_id');

        $provider_lists = TransportDriverDetails::query()->select(
            'service_category.slug as service_cat_slug',
            'service_category.id as service_cat_id',
            'service_category.name as service_cat_name',
            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"),
            'providers.id as provider_id',
            'providers.email',
            'providers.country_code',
            'providers.contact_number',
            'providers.created_at',
            'providers.app_version as provider_app_version',
            'provider_services.status',
            'provider_services.id as provider_service_id',
            'transport_vehicle_type.name as vehicle_type_name', 'transport_vehicle_type.icon_name as type_icon',
            'transport_driver_details.id as driver_details_id', 'transport_driver_details.vehicle_company',
            'transport_driver_details.plat_no', 'transport_driver_details.model_name', 'transport_driver_details.model_year',
            'transport_driver_details.vehicle_color', 'transport_driver_details.rating', 'transport_driver_details.total_request',
            'transport_driver_details.total_completed', 'transport_driver_details.total_cancelled',
            'transport_driver_details.handicap',
            'transport_driver_details.child_seat',
            'transport_driver_details.driver_service',
            'transport_driver_details.delivery_boy',
            'transport_driver_details.courier_boy',
            'transport_driver_details.rental_service'
        )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            //->where('providers.status', '=', $status)
            ->where('provider_services.status', '=', $status);
//            ->whereIn('service_category.id', [$this->bike_ride, $this->taxi_ride, $this->courier_service, $this->bike_rental, $this->taxi_rental])

                if ($service_category_id == $this->bike_ride) {
                    $provider_lists = $provider_lists->whereIn('service_category.id', [$this->bike_ride])
                        ->where('transport_driver_details.driver_service', '=', 1);
                } elseif ($service_category_id == $this->taxi_ride) {
                    $provider_lists = $provider_lists->whereIn('service_category.id', [$this->taxi_ride])
                        ->where('transport_driver_details.driver_service', '=', 1);
                } elseif ($service_category_id == $this->courier_service) {
                    $provider_lists = $provider_lists
                        ->where('transport_driver_details.courier_boy', '=', 1);
                } elseif ($service_category_id == $this->bike_rental) {
                    $provider_lists = $provider_lists->whereIn('service_category.id', [$this->bike_ride])
                        ->where('transport_driver_details.rental_service', '=', 1);
                } elseif ($service_category_id == $this->taxi_rental) {
                    $provider_lists = $provider_lists
                        ->whereIn('service_category.id', [$this->taxi_ride])
                        ->where('transport_driver_details.rental_service', '=', 1);
                }


                $provider_lists= $provider_lists->orderBy('transport_driver_details.id', 'desc');
//            ->get();

        $totalRecords = $provider_lists->count();
        if($searchValue != ""){
            $totalRecordswithFilter = $provider_lists->where(function($query) use ($searchValue){
                $query->orWhere('providers.first_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('providers.last_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('service_category.slug', 'like', '%' .$searchValue . '%')
                    ->orWhere('providers.email', 'like', '%' .$searchValue . '%')
                    ->orWhere('providers.contact_number', 'like', '%' .$searchValue . '%')
                    ->orWhere('transport_vehicle_type.name', 'like', '%' .$searchValue . '%');
            })->count();
        }else{
            $totalRecordswithFilter = $provider_lists->count();
        }

        $records = $provider_lists;
        if($searchValue != ""){
            $records = $records->where(function($query) use ($searchValue){
                $query->orWhere('providers.first_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('providers.last_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('service_category.slug', 'like', '%' .$searchValue . '%')
                    ->orWhere('providers.email', 'like', '%' .$searchValue . '%')
                    ->orWhere('providers.contact_number', 'like', '%' .$searchValue . '%')
                    ->orWhere('transport_vehicle_type.name', 'like', '%' .$searchValue . '%');
            });
        }
        $records =$records->skip($start)
            ->take($rowperpage)
            ->get();
        //dd(DB::getQueryLog());
        $data_arr = array();
        $i = 1;
        foreach($records as $record){
            $get_service_cat_Data =  ServiceCategory::query()->where('id',$service_category_id)->first();

            $service_cat_name = "--";
            if($record->service_cat_name != Null)
            {
                $service_cat_name = ucwords(strtolower($record->service_cat_name));
            }
            $provider_app_version = ($record->provider_app_version != Null )?$record->provider_app_version :0;
            if($record->service_cat_id == 2){
                $html_2 = 'handicap="'.($record->handicap==0?'No':'Yes').'" child_seat="'.($record->child_seat==0?'No':'Yes').'"';
            } else{
                $html_2 = '';
            }
            if($record->type_icon != Null){
                $icon = '<img id="vehicle_type_icon_'.$record->driver_details_id.'"
                               src="'.asset('/assets/images/service-category/transport-service-type/'.$record->type_icon ).'"
                               width="26" height="26">';
            } else{
                $icon = '----';
            }
            $vehical_type = '<span title="Vehicle Details" class="md-trigger md-trigger-vehicle"
                                data-modal="modal-1" data-toggle="tooltip"
                                model_type="0"
                                driver_name="'.$record->driver_name.'"
                                vehicle_type_name="'.$record->vehicle_type_name.'"
                                vehicle_company="'.$record->model_name.' '.$record->vehicle_company.'"
                                plat_no="'.$record->plat_no.'"
                                model_year="'.$record->model_year.'"
                                color="'.$record->color.'"
                                '.$html_2.'
                                url="'.route('get:admin:edit_transport_provider_vehicle_details',[$record->service_cat_slug, $record->driver_details_id ]).'"
                                vehicle_id="'. $record->driver_details_id.'">
                                '.$icon.'
                             </span>';

            $driver_name = '<span title="Vehicle Details" class="md-trigger"
                                    data-modal="modal-2" data-toggle="tooltip"
                                    model_type="1"
                                    driver_name="'.$record->driver_name.'"
                                    total_request="'.$record->total_request.'"
                                    total_completed="'.$record->total_completed.'"
                                    total_cancelled="'.$record->total_cancelled.'"
                                >
                                    '.ucwords($record->driver_name).'
                                </span>';
                $rating = '<a href="'. route('get:admin:transport_provider_review_list',['slug' => $record->service_cat_slug, 'driver_id' => $record->driver_details_id ]) .'"
                                class="render_link"
                                data-toggle="tooltip" data-placement="top"
                                title="Rating List">
                                    <span class="icon-list-demo">
                                        <i class="fa fa-star"></i>'.$record->rating.'
                                    </span>
                          </a>';

            $rides = '';
            if((isset($status) && $status == 1) || (isset($status) && $status == 2)){
                if($record->service_cat_slug == "courier-service"){
                    $icon = '<i class="fa fa-truck"></i>';
                } elseif($record->service_cat_slug == "bike-ride"){
                    $icon = '<i class="fa fa-motorcycle"></i>';
                } else{
                    $icon = ' <i class="fa fa-car"></i>';
                }
                $title = ($record->service_cat_slug == "courier-service") ? 'Delivery' : 'Ride';
                $rides = '<a href="'.route('get:admin:transport_provider_ride_list',['slug' => $record->service_cat_slug, 'driver_id' => $record->driver_details_id ]).'"
                            class="render_link"
                            title="'. $title .' List">
                                <span class="icon-list-demo">
                                   '.$icon.'
                                </span>
                          </a>';
            }

            $document = '<a href="'. route('get:admin:transport_provider_document',['slug' => $record->service_cat_slug, 'provider_service_id' => $record->provider_service_id]) .'"
                            class="render_link" data-toggle="tooltip"
                            data-placement="top" title="Document List">
                            <span class="icon-list-demo">
                                <i class="fa fa-file-text"></i>
                            </span>
                        </a>';
            if($status == 0){
                $status_html = '<a class="render_link">
                                <img src="'.asset('/assets/images/template-images/thumbs-up.png').'"
                                     style="width:20px; height: 20px;"
                                     data-toggle="tooltip" class="approve approve_element_'.$record->provider_service_id.'"
                                     provider_service_id="'.$record->provider_service_id.'"
                                     service_cat_id="'.$record->service_cat_id.'"
                                     data-placement="top" title="Approve">
                          </a>
                          <a class="render_link">
                                <img src="'.asset('/assets/images/template-images/thumb-down.png').'"
                                    style="width:20px; height: 20px;"
                                    data-toggle="tooltip" class="reject reject_element_'.$record->provider_service_id.'"
                                    provider_service_id="'.$record->provider_service_id.'"
                                    service_cat_id="'.$record->service_cat_id.'"
                                    data-placement="top" title="Reject">
                          </a>';
            } elseif($status == 1){
                $status_html = '<span class="toggle"><label>
                                <input name="manual_assign"
                                    class="form-control store_status block block_element_'.$record->provider_service_id.'"
                                    type="checkbox" checked
                                    provider_service_id="'.$record->provider_service_id.'"
                                    service_cat_id="'.$record->service_cat_id.'"><span
                                    class="button-indecator"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Active"></span></label>
                           </span>';
            } elseif($status == 2){
                $status_html = '<span class="toggle"><label>
                                <input name="manual_assign"
                                    class="form-control store_status unblock unblock_element_'.$record->provider_service_id.'"
                                    type="checkbox" checked
                                    service_cat_id="'.$record->service_cat_id.'"
                                    provider_service_id="'.$record->provider_service_id.'"><span
                                    class="button-indecator"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Active"></span></label>
                           </span>';
            } else{
                $status_html = '<span class="toggle"><label>
                                <input name="manual_assign"
                                    class="form-control store_status approve"
                                    type="checkbox" checked
                                    service_cat_id="'.$record->service_cat_id.'"
                                    provider_service_id="'.$record->provider_service_id.'"><span
                                    class="button-indecator"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Active"></span></label>
                           </span>';
            }
            $status_html .= '<a href="'.route('get:admin:edit_transport_service_driver',[ $record->driver_details_id]).'"
                            class="render_link edit">
                            <img src="'.asset('/assets/images/template-images/writing-1.png').'"
                                style="width:20px; height: 20px;"
                                data-toggle="tooltip"
                                data-placement="top" title="Edit">
                        </a>';
            if($status == 1){

                $status_html .= '<span title="Change Password"
                              class="md-trigger change-password text-c-orenge"
                              data-modal="modal-2" data-toggle="tooltip"
                              driver_name="'. $record->driver_name .'"
                              providerid="' . $record->provider_id . '"
                        >
                        <i class="fa fa-key"></i>';
            }

            if(isset($status)&& in_array($status, [0,2,3])){
                $register_name = date("d-M Y h:i A",strtotime($record->created_at));
            } else{
                $register_name = '';
            }
            $data_arr[] = array(
                "DT_RowId" => "hide_".$record->provider_service_id,
                "no" => $i,
                "vehicle_type" => $vehical_type,
                "service_cat_name" => $service_cat_name,
                "driver_name" => $driver_name,
                "email" => User::Email2Stars($record->email),
                "contact_number" => User::ContactNumber2Stars($record->country_code.$record->contact_number),
                "rating" => $rating,
                'rides' => $rides,
                'documents'=> $document,
                'sign_up_time'=> $register_name,
                'provider_app_version'=> $provider_app_version,
                'actions'=> $status_html,
            );
            $i++;
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        return json_encode($response);
    }

    //get Transport Un-approved Provider List
    public function getTransport_Blocked_ProviderList(Request $request, $slug, $status)
    {
        if ($status == "approved") {
            $status = 1;
        } elseif ($status == "un-approved") {
            $status = 0;
        } elseif ($status == "blocked") {
            $status = 2;
        } elseif ($status == "rejected") {
            $status = 3;
        } else {
            Session::flash('error', 'Category provider list not found!');
            return redirect()->back();
        }

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Category provider list not found!');
            return redirect()->back();
        }
        $service_category_id = $service_category->id;
        if ($service_category_id == $this->bike_rental) {
            $service_category_id = $this->bike_ride;
        }
        if ($service_category_id == $this->taxi_rental) {
            $service_category_id = $this->taxi_ride;
        }

        $provider_list_check = TransportDriverDetails::query()
            ->select('service_category.slug as service_cat_slug',
                'service_category.id as service_cat_id',
                DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'providers.id as provider_id', 'providers.email', 'providers.contact_number',
                'providers.created_at',
                'provider_services.status', 'provider_services.id as provider_service_id',
                'transport_driver_details.id as driver_details_id',
                'transport_driver_details.rating', 'transport_driver_details.total_request',
                'transport_driver_details.total_completed', 'transport_driver_details.total_cancelled',
                'transport_driver_details.vehicle_company', 'transport_driver_details.plat_no',
                'transport_driver_details.model_name', 'transport_driver_details.model_year',
                'transport_driver_details.vehicle_color',
                'transport_vehicle_type.name as vehicle_type_name',
                'transport_vehicle_type.icon_name as type_icon',
                'transport_driver_details.handicap',
                'transport_driver_details.child_seat'
            )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
//            ->where('providers.status', '=', $status)
            ->where('provider_services.status', '=', $status);

        if ($service_category->id == $this->bike_ride) {
            $provider_lists = $provider_list_check
                ->whereIn('service_category.slug', [$slug, 'courier-service'])
                ->where('transport_driver_details.driver_service', '=', 1)
                ->orderBy('transport_driver_details.id',  'desc')
                ->get();
        } elseif ($service_category->id == $this->courier_service) {
            $provider_lists = $provider_list_check
                ->where('transport_driver_details.courier_boy', '=', 1)
                ->orderBy('transport_driver_details.id',  'desc')
                ->get();
        } elseif ($service_category->id == $this->bike_rental) {
            $provider_lists = $provider_list_check
                ->where('service_category.id', $service_category_id)
                ->where('transport_driver_details.rental_service', '=', 1)
                ->orderBy('transport_driver_details.id', 'desc')
                ->get();
        } elseif ($service_category->id == $this->taxi_rental) {
            $provider_lists = $provider_list_check
                ->where('service_category.id', $service_category_id)
                ->where('transport_driver_details.rental_service', '=', 1)
                ->orderBy('transport_driver_details.id', 'desc')
                ->get();
        } else {
            $provider_lists = $provider_list_check
                ->where('service_category.id', $service_category_id)
                ->orderBy('transport_driver_details.id', 'desc')
                ->get();
        }
        if ($request->ajax()) {
            $view = view('admin.pages.transport_services.transport_provider_list', compact('slug', 'status', 'service_category', 'provider_lists'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.transport_services.transport_provider_list', compact('slug', 'status', 'service_category', 'provider_lists'));
    }


    //get Ajax Transport Provider Status Change
    public function getUpdateTransportProviderStatus(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
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
                'message' => 'Data not found1'
            ]);
        }

        $driver_services = ProviderServices::query()->where('id', '=', $id)->first();
        if ($driver_services == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found2'
            ]);
        }
        $driver_details = Provider::query()->where('id', $driver_services->provider_id)->first();
        if ($driver_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found3'
            ]);
        }
        if ($request_for == 1) {
            $driver_services->status = 1;
            $driver_services->save();
            $driver_details->status = 1;
            $driver_details->save();

            $general_settings = request()->get("general_settings");
            if ($general_settings !=  Null) {
                if ($general_settings->send_mail == 1) {
                    $driver_name = ($driver_details->first_name !="")?$driver_details->first_name : "";
                    try{
                        $mail_type = "driver_account_approve";
                        $to_mail = $driver_details->email;
                        $subject = "Your Pending Account Approved By Admin ";
                        $disp_data = array("##driver_name##"=>$driver_name );
                        $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                    }
                    catch (\Exception $e){}
                }
            }

        } elseif ($request_for == 2) {
            $driver_services->status = 2;
            $driver_services->save();

            $general_settings = request()->get("general_settings");
            if ($general_settings !=  Null) {
                if ($general_settings->send_mail == 1) {
                    $driver_name = $driver_details->first_name;
                    try {
                        $mail_type = "driver_account_block";
                        $to_mail = $driver_details->email;
                        $subject = "Your Account has been Block by Admin";
                        $disp_data = array("##driver_name##" => $driver_name);
                        $mail_return_data = $this->notificationClass->sendMail($subject, $to_mail, $mail_type, $disp_data);
                    }
                    catch (\Exception $e) {}
                }
            }
        } elseif ($request_for == 3) {
            $driver_services->status = 3;
            $driver_services->rejected_reason = $request->get('rejected_reason');
            $driver_services->save();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data not found4'
            ]);
        }
        return response()->json([
            'success' => true
        ]);
    }

    //get Transport Service Category Ride List
    public function getTransportServiceCategoryRideList($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Category ride list not found!');
            return redirect()->back();
        }
        $ride_lists = TransportRideBook::query()->select('user_ride_booking.id', 'user_ride_booking.ride_no', 'user_ride_booking.service_cat_id',
            'user_ride_booking.total_pay', 'user_ride_booking.status', 'user_ride_booking.pickup_address',
            'user_ride_booking.destination_address',
//            'user_ride_booking.vehicle_type_name as vehicle_name',
            'user_ride_booking.user_name',
            'user_ride_booking.driver_name',
            'transport_vehicle_type.name as vehicle_name'
        )
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_ride_booking.vehicle_type_id')
            ->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->whereIn('user_ride_booking.status', [0, 1, 2, 4, 5, 9])
            ->orderBy('user_ride_booking.pickup_datetime', 'desc')
            ->get();
        $status = "all";
        $status_check = 'all';
        $transport_services = $this->transport_service_id_array;
        $courier_service = $this->courier_service_id_array;
        $delivery_service = $this->delivery_service_id_array;

        $view = view('admin.pages.transport_services.transport_provider_ride_list', compact('transport_services', 'courier_service',
            'delivery_service', 'slug', 'status', 'ride_lists', 'service_category','status_check'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getTransportRideList(Request $request){
        $status_check = $request->get('status_check');
        $slug = $request->get('slug');
        $service_category = ServiceCategory::query()->where('slug', "=",$slug)->first();

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

        $ride_lists_check = TransportRideBook::query()
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
            ->orderBy('user_ride_booking.pickup_datetime', 'desc');
        if ($slug == "courier-service") {
            $ride_lists_check->whereIn('user_ride_booking.service_cat_id', [$this->courier_service]);
        } elseif ($slug == "bike-ride") {
            $ride_lists_check->whereIn('user_ride_booking.service_cat_id', [$this->bike_ride]);
        } else {
            $ride_lists_check->where('user_ride_booking.service_cat_id', '=', $service_category->id);
        }
        if ($status_check == "all") {
            $ride_lists = $ride_lists_check->whereIn('user_ride_booking.status', [0, 1, 2, 3, 4, 5, 6,7, 8, 9, 10]);
                //->get()->toArray();
            $status = "all";
        } elseif ($status_check == "scheduled") {
            $ride_lists = $ride_lists_check->whereIn('user_ride_booking.status', [0, 2, 4, 5, 9])
                ->where('user_ride_booking.ride_type', '=', 1);
                //->get()->toArray();
            $status = "scheduled";
        } elseif ($status_check == "pending") {
            $ride_lists = $ride_lists_check->where('user_ride_booking.status', '=', 0);
                //->get()->toArray();
            $status = "pending";
        } elseif ($status_check == "cancelled") {
            $ride_lists = $ride_lists_check->where('user_ride_booking.status', '=', 4);
               // ->get()->toArray();
            $status = 4;
        } else {
            $ride_lists = $ride_lists_check->whereIn('user_ride_booking.status', [0, 1, 2, 4, 5, 9]);
                //->get()->toArray();
            $status = "all";
        }

        $ride_list_data = $ride_lists;
        $totalRecords = $ride_lists->count();
        if($searchValue != ""){
            /*$totalRecordswithFilter = $ride_lists->where('user_ride_booking.ride_no','like','%' .$searchValue . '%')
                ->where('user_ride_booking.user_name','like','%' .$searchValue . '%')
                ->where('user_ride_booking.driver_name','like','%' .$searchValue . '%')
                ->where('user_ride_booking.total_pay','like','%' .$searchValue . '%')
                ->count();*/
            $totalRecordswithFilter = $ride_lists->where(function($query) use ($searchValue){
                $query->orWhere('user_ride_booking.ride_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('user_ride_booking.user_name', 'like', '%' .$searchValue . '%')
                    ->orWhere('user_ride_booking.driver_name', 'like', '%' .$searchValue . '%')
                    ->orWhere('user_ride_booking.total_pay', 'like', '%' .$searchValue . '%');
            })->count();
        } else{
            $totalRecordswithFilter = $ride_lists->count();
        }

        $record = $ride_list_data;
        if($searchValue != ""){
            /*$record = $record->orWhere('user_ride_booking.ride_no','like','%' .$searchValue . '%');
            $record = $record->orWhere('user_ride_booking.user_name','like','%' .$searchValue . '%');
            $record = $record->orWhere('user_ride_booking.driver_name','like','%' .$searchValue . '%');
            $record = $record->orWhere('user_ride_booking.total_pay','like','%' .$searchValue . '%');*/
            $record = $record->where(function($query) use ($searchValue){
                $query->orWhere('user_ride_booking.ride_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('user_ride_booking.user_name', 'like', '%' .$searchValue . '%')
                    ->orWhere('user_ride_booking.driver_name', 'like', '%' .$searchValue . '%')
                    ->orWhere('user_ride_booking.total_pay', 'like', '%' .$searchValue . '%');
            });
        }
        $records = $record->skip($start)
            ->take($rowperpage)
            ->get();

        $i = 1;
        $data_arr = [];
        foreach ($records as $record){
            if($record->status == 0){
                $ride_status = "pending";
            } else if($record->status == 1 || $record->status == 2 || $record->status == 3){
                $ride_status = "approved";
            } else if($record->status == 4){
                $ride_status = "cancelled";
            } else if($record->status == 5 || $record->status == 6 || $record->status == 7 || $record->status == 8){
                $ride_status = "running";
            } else if($record->status == 9){
                $ride_status = "completed";
            } else if($record->status == 10){
                $ride_status = "failed";
            }
            $ride_status_html = '<span class="'.$ride_status.'">'.$ride_status.'</span>';
            $transport_services = $this->transport_service_id_array;
            $courier_service = $this->courier_service_id_array;
            $delivery_service = $this->delivery_service_id_array;
            if(Auth::guard("admin")->check()){
                if(Auth::guard("admin")->user()->roles == 1){
                    if(in_array($record->service_cat_id, $transport_services)){
                        $ride_details_html = '<a href="'.route('get:admin:transport_ride_details',[ $slug, $record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Ride Details"></i>
                                                </span>
                                               </a>';
                    } elseif(in_array($record->service_cat_id, $courier_service)){
                        $ride_details_html = '<a href="'.route('get:admin:transport_ride_details',[ $slug, $record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Delivery Details"></i>
                                                </span>
                                               </a>';
                    } elseif(in_array($record->service_cat_id, $delivery_service)){
                        $ride_details_html = '<a href="'.route('get:admin:transport_delivery_details',[ $slug, $record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Order Details"></i>
                                                </span>
                                               </a>';
                    }
                } elseif(Auth::guard("admin")->user()->roles == 2){
                    if(in_array($record->service_cat_id, $transport_services)){
                        $ride_details_html = '<a href="'.route('get:dispatcher:ride_details',[ $slug, $record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Ride Details"></i>
                                                </span>
                                               </a>';
                    }
                } elseif(Auth::guard("admin")->user()->roles == 3){
                    if(in_array($record->service_cat_id, $transport_services)){
                        $ride_details_html = '<a href="'.route('get:account:ride_details',[$record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Ride Details"></i>
                                                </span>
                                               </a>';
                    } elseif(in_array($record->service_cat_id, $courier_service)){
                        $ride_details_html = '<a href="'.route('get:account:ride_details',[$record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Delivery Details"></i>
                                                </span>
                                               </a>';
                    } elseif(in_array($record->service_cat_id, $delivery_service)){
                        $ride_details_html = '<a href="'.route('get:account:delivery_details',[$record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Order Details"></i>
                                                </span>
                                               </a>';
                    }
                }
            }
            $address = '<i class="fa fa-map-marker text-success"></i>'.$record->pickup_address.'<br>
                        <i class="fa fa-map-marker text-danger"></i>'. $record->destination_address;
            $data_arr[] = array(
                "no" => $i,
                "ride_no" => $record->ride_no,
                "customer_name" => $record->user_name,
                "driver_name" => $record->driver_name,
                "vehicle_type" => $record->vehicle_name,
                "total_cost" => '<span class="currency"></span>'.$record->total_pay,
                'status' =>$ride_status,
                'details'=>$ride_details_html,
                'address'=>$address
            );
            $i++;
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );


        return json_encode($response);

    }

    //get Transport Service Category Ride List
    public function getTransportServiceRideList($slug, $status_check, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null && $status_check == Null) {
            Session::flash('error', 'Category ride list not found!');
            return redirect()->back();
        }
        $ride_lists_check = TransportRideBook::query()
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
            ->orderBy('user_ride_booking.pickup_datetime', 'desc');
        if ($slug == "courier-service") {
            $ride_lists_check->whereIn('user_ride_booking.service_cat_id', [$this->bike_ride, $this->courier_service]);
        } elseif ($slug == "bike-ride") {
            $ride_lists_check->whereIn('user_ride_booking.service_cat_id', [$this->bike_ride, $this->courier_service]);
        } else {
            $ride_lists_check->where('user_ride_booking.service_cat_id', '=', $service_category->id);
        }
        if ($status_check == "all") {
            $ride_lists = $ride_lists_check->whereIn('user_ride_booking.status', [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
                ->get()->toArray();
            $status = "all";
        } elseif ($status_check == "scheduled") {
            $ride_lists = $ride_lists_check->whereIn('user_ride_booking.status', [0, 2, 4, 5, 9])
                ->where('user_ride_booking.ride_type', '=', 1)
                ->get()->toArray();
            $status = "scheduled";
        } elseif ($status_check == "pending") {
            $ride_lists = $ride_lists_check->where('user_ride_booking.status', '=', 0)
                ->get()->toArray();
            $status = "pending";
        } elseif ($status_check == "cancelled") {
            $ride_lists = $ride_lists_check->where('user_ride_booking.status', '=', 4)
                ->get()->toArray();
            $status = 4;
        } else {
            $ride_lists = $ride_lists_check->whereIn('user_ride_booking.status', [0, 1, 2, 4, 5, 9])
                ->get()->toArray();
            $status = "all";
        }

        $transport_services = $this->transport_service_id_array;
        $courier_service = $this->courier_service_id_array;
        $delivery_service = $this->delivery_service_id_array;

        $view = view('admin.pages.transport_services.transport_provider_ride_list', compact('transport_services', 'courier_service', 'delivery_service',
            'slug', 'status', 'ride_lists', 'service_category','status_check'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Transport Provider Ride List
    public function getTransportProviderRideList($slug, $driver_id, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if (!is_numeric($driver_id) || $service_category == Null) {
            Session::flash('error', 'Category provider ride not found!');
            return redirect()->back();
        }
        $driver_details = $driver_details_check = TransportDriverDetails::query()->
        select(DB::raw("CONCAT(providers.first_name,' ',providers.last_name) as driver_name"), 'transport_driver_details.driver_service', 'transport_driver_details.delivery_boy', 'transport_driver_details.courier_boy')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->where('transport_driver_details.id', '=', $driver_id)
            ->first();
        if ($driver_details == Null) {
            Session::flash('error', 'Provider ride not found!');
            return redirect()->back();
        }
        $ride_lists_check = TransportRideBook::query()
            ->select('user_ride_booking.id', 'user_ride_booking.ride_no',
                'user_ride_booking.total_pay', 'user_ride_booking.status', 'user_ride_booking.pickup_address',
                'user_ride_booking.destination_address',
//                'user_ride_booking.vehicle_type_name as vehicle_name',
                'user_ride_booking.user_name',
                'user_ride_booking.driver_name',
                'user_ride_booking.service_cat_id',
                'transport_vehicle_type.name as vehicle_name'
            )
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_ride_booking.vehicle_type_id')
            ->where('user_ride_booking.driver_id', '=', $driver_id);
        if ($slug == "courier-service" || $slug == "bike-ride") {
            $ride_lists_check->whereIn('user_ride_booking.service_cat_id', [$this->bike_ride, $this->courier_service]);
        } else {
            $ride_lists_check->where('user_ride_booking.service_cat_id', '=', $service_category->id);
        }

        $ride_lists_details = $ride_lists_check->orderBy('user_ride_booking.pickup_datetime', 'desc')
            ->get()->toArray();
        $delivery_ride_lists = [];
        $ride_history = [];
        if ($driver_details->delivery_boy == 1) {
            $delivery_ride_lists = UserProductBooking::query()->select(
                'user_store_product_booking.id', 'user_store_product_booking.order_no as ride_no',
                'user_store_product_booking.total_pay', 'user_store_product_booking.order_delivery_status as status',
                'store_details.address as pickup_address',
                'user_store_product_booking.delivery_address as destination_address',
                'transport_vehicle_type.name as vehicle_name',
                'user_store_product_booking.user_name',
                'user_store_product_booking.delivery_people_name as driver_name',
                'user_store_product_booking.service_cat_id'
            )
                ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
                ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_store_product_booking.delivery_people_id')
                ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
                ->where('user_store_product_booking.delivery_people_id', $driver_id)
                ->get()->toArray();
        }
        $ride_history = array_merge($ride_history, $ride_lists_details);
        $ride_history = array_merge($ride_history, $delivery_ride_lists);
        $ride_lists = $ride_history;
        $status = "all";
        $status_check = "all";
        $transport_services = $this->transport_service_id_array;
        $courier_service = $this->courier_service_id_array;
        $delivery_service = $this->delivery_service_id_array;

        $view = view('admin.pages.transport_services.transport_single_provider_ride_list', compact('transport_services', 'courier_service', 'delivery_service',
            'driver_details', 'slug', 'status', 'service_category', 'ride_lists','status_check','driver_id'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Transport Ride Details
    public function getTransportRideDetails($slug, $id, Request $request) {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $ride_Details = TransportRideBook::query()->select(
            'user_ride_booking.id', 'user_ride_booking.ride_no',
            'user_ride_booking.service_cat_id',
            'user_ride_booking.user_id', 'user_ride_booking.driver_id',
            'user_ride_booking.user_name', 'user_ride_booking.driver_name',
            'user_ride_booking.pickup_datetime', 'user_ride_booking.destination_datetime',
            'user_ride_booking.pickup_address', 'user_ride_booking.destination_address',
            //'user_ride_booking.vehicle_type_name',
            'user_ride_booking.vehicle_cost_for_km',
            'user_ride_booking.base_fare',
            'user_ride_booking.eta',
            'user_ride_booking.total_distance_amount',
            'user_ride_booking.time_fare_amount',
            'user_ride_booking.adjustment_amount',
            'user_ride_booking.tip',
            'user_ride_booking.created_at',
            'user_ride_booking.total_distance',
            'user_ride_booking.tax',
            'user_ride_booking.total_pay', 'user_ride_booking.payment_type', 'user_ride_booking.payment_status',
            'user_ride_booking.promo_code',
            'user_ride_booking.status',
            'user_ride_booking.user_refund_status',
            'user_ride_booking.cancel_charge',
            'user_ride_booking.refund_amount',
            'user_ride_booking.ride_type',
            'user_ride_booking.is_driver_reassign',
            'user_ride_booking.surcharge_cost',
            'transport_vehicle_type.name as vehicle_type_name'
        )
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_ride_booking.vehicle_type_id')
            ->where('user_ride_booking.id', $id)->first();
        if ($ride_Details == Null) {
            return redirect()->back();
        }

        $user_details = User::query()->select('id', 'contact_number', 'email')->where('id', $ride_Details->user_id)->first();
        $driver_details = TransportDriverDetails::query()->select('transport_driver_details.id','providers.first_name as driver_name', 'providers.email', 'providers.contact_number')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->where('transport_driver_details.id', $ride_Details->driver_id)
            ->first();
        $courier_details = Null;
        if ($ride_Details != Null && in_array($ride_Details->service_cat_id, $this->courier_service_id_array)) {
            $courier_details = TransportCourierDetails::query()->Select(
                'user_courier_service_details.recipient_name',
                'user_courier_service_details.category_name',
                'user_courier_service_details.house_name',
                'user_courier_service_details.recipient_contact_number',
                'user_courier_service_details.recipient_landmark',
                'user_courier_service_details.goods_height',
                'user_courier_service_details.goods_width',
                'user_courier_service_details.goods_weight',
                'user_courier_service_details.description',
                'user_courier_service_details.delivery_instruction',
                'user_courier_service_details.weight_id',
                'user_courier_service_details.weight_start_limit',
                'user_courier_service_details.weight_close_limit',
                'user_courier_service_details.sender_name',
                'user_courier_service_details.sender_contact_number',
                'user_courier_service_details.sender_landmark'
            )->where('ride_id', $ride_Details->id)->first();
        }

        $promo_code = UsedPromocodeDetails::query()->where('id',$ride_Details->promo_code)->first();
        if($promo_code != Null){
            $promo_code_name = $promo_code->promocode_name;
            $promo_code_discount = $promo_code->discount_amount;
        } else{
            $promo_code_name = '';
            $promo_code_discount = 0;
        }
        $service_fare_fee= round($ride_Details->total_distance_amount + $ride_Details->time_fare_amount + $ride_Details->base_fare ,2);
        $total = round($ride_Details->total_distance_amount + $ride_Details->time_fare_amount + $ride_Details->base_fare + $ride_Details->surcharge_cost, 2);
        /*$total = round($ride_Details->total_distance_amount + $ride_Details->time_fare_amount + $ride_Details->base_fare + $ride_Details->tax, 2);*/
        $discount = round(0, 2);
        $grand_total = round($ride_Details->total_pay - $discount, 2);

        if (in_array($ride_Details->service_cat_id, $this->courier_service_id_array)) {
            $view = view('admin.pages.transport_services.ride.manage_courier_delivery_details', compact('service_category','slug', 'ride_Details', 'courier_details', 'user_details', 'driver_details', 'grand_total', 'total', 'discount','promo_code_name','promo_code_discount','service_fare_fee'));
        } else {
            $view = view('admin.pages.transport_services.ride.manage_ride_details', compact('service_category','slug', 'ride_Details', 'user_details', 'driver_details', 'grand_total', 'total', 'discount','promo_code_name','promo_code_discount'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Transport Ride Details

    public function getTransportStoreDeliveryDetails(Request $request, $slug, $order_id)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $order_details = UserProductBooking::query()->select('id', 'store_id', 'user_id', 'order_no', 'user_name', 'store_name',
            'delivery_address', 'created_at', 'total_item_cost', 'discount_cost', 'tax_cost', 'delivery_cost', 'packaging_cost',
            'total_pay', 'payment_type', 'payment_status', 'status', 'cancel_by', 'cancel_reason','order_type','order_date_time')
            ->where('id', $order_id)->first();
        if ($order_details == Null) {
            return redirect()->back();
        }
        $store_details = StoreDetails::query()->select('address')->where('id', $order_details->store_id)->first();
        $user_details = User::query()->select('contact_number')->where('id', $order_details->store_id)->first();
        //$get_order_status = UserProductBookingStatus::select('status', 'created_at')->where('order_id', $order_details->id)->get();
        $order_status = $this->store_order_status;
        $product_details = UserProductBookingQuantity::query()->select('product_name', 'product_cat_name', 'num_of_items', 'price_for_one')->where('order_id', $order_details->id)->get()->groupBy('product_cat_name');
        $view = view('admin.pages.transport_services.ride.manage_delivery_details', compact('slug', 'order_details', 'get_order_status', 'order_status', 'product_details', 'store_details', 'user_details', 'service_category'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Transport Manual Ride Booking
    public function getTransportManualRideBooking($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null){
            return redirect()->back();
        }
        $expire_date_time = date('Y-m-d H:i:s', strtotime("-10 minutes"));
        if (in_array($service_category->id, [5, 6, 7, 8, 9, 10])) {
            $skip_ids = [1,2,4,31,32];
        } else {
            $skip_ids = [1, 2, 4, 5, 6, 7, 8, 9, 10, 31, 32];
        }

        $not_multi_delivery_provider_id = ProviderUserRunningService::query()->whereIn('service_cat_id',$skip_ids)->get()->pluck('provider_id');
        $provider_lists = TransportDriverDetails::query()->select(
            'providers.avatar',
            DB::raw("providers.first_name as driver_name"),
            'providers.contact_number',
            'service_category.slug as service_cat_slug',
            'service_category.id as service_cat_id',
            'transport_driver_details.id as driver_details_id',
            'transport_driver_details.vehicle_type_id',
            'transport_vehicle_type.name as vehicle_type_name',
            'transport_vehicle_type.cost_for_km',
            'transport_vehicle_type.time_fare'
        )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            ->where('service_category.slug', $slug)
            ->where('provider_services.status', '=', 1)
            ->where('providers.status', '=', 1)
            ->where('transport_vehicle_type.status', '=', 1)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->where('provider_services.current_status', 1)
            ->where('transport_driver_details.driver_service', 1)
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token');
            if ($not_multi_delivery_provider_id != Null) {
                $provider_lists = $provider_lists->whereNotIn('providers.id', $not_multi_delivery_provider_id);
            }

        $provider_lists = $provider_lists->get();
        $service_settings = ServiceSettings::query()->where('service_cat_id', $service_category->id)->first();
        if ($service_settings != Null) {
            $admin_commission = $service_settings->admin_commission;
            $get_tax = $service_settings->tax;
        } else {
            $admin_commission = 0;
            $get_tax = 0;
        }
        $view = view('admin.pages.transport_services.ride_book.form', compact('slug', 'get_tax', 'admin_commission', 'service_category', 'provider_lists'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //Update or Store Manual Ride of Transport
    public function postUpdateStoreManualRide($slug, TransportManualRideBookRequest $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        try {
            $driver_details = Provider::query()->select('providers.id', 'providers.login_device', 'providers.device_token', 'providers.language')
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                ->where('transport_driver_details.id', '=', $request->get('driver_id'))
                ->where('provider_services.status', '=', 1)
                ->where('providers.status', '=', 1)
                ->where('provider_services.current_status', 1)
                ->where('transport_driver_details.driver_service', 1)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token')
                ->first();
            if ($driver_details == Null){
                Session::flash('success', 'Sorry, Currently Driver is not available!');
                return redirect()->back();
            }

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
            }

            ProviderUserRunningService::query()->where('provider_id', $driver_details->id)->where('user_id', $transport_ride_book->user_id)->where('service_cat_id', $transport_ride_book->service_cat_id)->where('booking_id', $transport_ride_book->id)->delete();
            $provider_running_service = new ProviderUserRunningService();
            $provider_running_service->provider_id = $driver_details->id;
            $provider_running_service->user_id = $transport_ride_book->user_id;
            $provider_running_service->service_cat_id = $transport_ride_book->service_cat_id;
            $provider_running_service->booking_id = $transport_ride_book->id;
            $provider_running_service->save();
            $this->notificationClass->driverManualAssignTransportRequestNotification($transport_ride_book->id, $driver_details->device_token, $driver_details->login_device, $driver_details->language);

            $sevice_category = ServiceCategory::query()->where('id', $transport_ride_book->service_cat_id)->first();
            Session::flash('success', 'Ride booking successfully!');

            return redirect()->route('get:admin:transport_ride_list', [$sevice_category->slug, "scheduled"]);
        } catch (\Exception $exception) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }

    }

    //get Ajax Search Drivers Manual Ride Book
    public function getAjaxTransportSearchDriversManualRideBook(Request $request)
    {
        $search = $request->get('search_drivers');
        $slug = $request->get('slug');
        if ($search == Null && $slug == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Drivers not found'
            ]);
        }
        $expire_date_time = date('Y-m-d H:i:s', strtotime("-10 minutes"));
        $provider_lists_check = TransportDriverDetails::query()
            ->select(
                'providers.avatar',
                DB::raw("CONCAT(COALESCE(providers.first_name,'')) as driver_name"),
                'providers.contact_number',
                'service_category.slug as service_cat_slug',
                'service_category.id as service_cat_id',
                'transport_driver_details.id as driver_details_id',
                'transport_driver_details.vehicle_type_id',
                'transport_vehicle_type.name as vehicle_type_name',
                'transport_vehicle_type.cost_for_km',
                'transport_vehicle_type.time_fare'
            )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            ->where('service_category.slug', $slug)
            ->where('provider_services.status', '=', 1)
            ->where('providers.status', '=', 1)
            ->where('transport_vehicle_type.status', '=', 1)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->where('provider_services.current_status', 1)
            ->where('transport_driver_details.driver_service', '=',1)
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token');
        if ($search == Null) {
            $provider_lists = $provider_lists_check->get();
        } else {
            $provider_lists = $provider_lists_check->whereRaw('driver_name', 'LIKE', "%$search%")
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

    //get Ajax Search Customer Manual RideBook
    public function getAjaxTransportSearchCustomerManualRideBook(Request $request)
    {
        $search = $request->get('contact_number');
        $slug = $request->get('slug');
        if ($search == Null && $slug == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Customers not found'
            ]);
        }
        $get_customers_lists = User::query()->select(
            'users.id', 'users.first_name', 'users.last_name',
            'users.contact_number',
//             User::ContactNumber2Stars("users.contact_number as cn"),
//             User::Email2Stars("users.email as cm")
            'users.email'
        );
        if (is_numeric($search)) {
            $customers_lists = $get_customers_lists
                ->where('users.contact_number', 'LIKE', "$search%")
                ->where('users.status', '=', 1)
                ->get();
        } else {
            $customers_lists = $get_customers_lists
                ->where('users.first_name', 'LIKE', "$search%")
                ->orWhere('users.last_name', 'LIKE', "$search%")
                ->where('users.status', '=', 1)
                ->get();
        }
//        ->where('users.contact_number', 'LIKE', "$search%")
//            ->where('users.status', '=', 1)
//            ->get();

        if ($customers_lists->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Customers not found'
            ]);
        }

        $get_customers_lists= [];
        foreach ($customers_lists as $key => $customers){
            $get_customers_lists[] =[
                "id" => $customers->id,
                "first_name" => $customers->first_name,
//                "last_name" => $customers->last_name,
                "contact_number" => User::ContactNumber2Stars($customers->contact_number),
                "email" => User::Email2Stars($customers->email)
            ];
        }
        return response()->json([
            'success' => true,
            'customers' => $get_customers_lists,
        ]);
    }

    //get Transport Provider Document
    public function getTransportProviderDocument($slug, $provider_service_id, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if (!is_numeric($provider_service_id) || $service_category == Null) {
            Session::flash('error', 'Driver documents details not found!');
            return redirect()->back();
        }
        $service_category_id = $service_category->id;
        if ($service_category->id == $this->courier_service) {
            $provider_service_details = ProviderServices::query()->where('id', $provider_service_id)->first();
            if (($provider_service_details != Null) && $provider_service_details->service_cat_id == $this->bike_ride) {
                $service_category_id = $provider_service_details->service_cat_id;
            }
        }

        $required_documents_lists = RequiredDocuments::query()->where('status', 1)->where('service_cat_id', $service_category_id)->get();
        foreach ($required_documents_lists as $key => $required_document) {
            $document_details = ProviderDocuments::query()->where('provider_service_id', '=', $provider_service_id)->where('req_document_id', $required_document->id)->first();
            if ($required_document->service_cat_id == $service_category_id) {
                $provider_documents[$key] = $document_details;
            } else {
                $provider_documents[$key] = Null;
            }
        }
        $view = view('admin.pages.transport_services.transport_provider_document', compact('slug', 'service_category', 'required_documents_lists', 'provider_documents'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Transport Provider Ride review List
    public function getTransportProviderRideReviewList($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $service_cat_id = $service_category->id;
        if (in_array($service_cat_id, [$this->bike_rental, $this->taxi_rental])) {
//            $service_cat_id = $this->bike_ride;
            $provider_reviews = TransportRatings::query()->select('transport_driver_rating.id',
                'transport_driver_rating.rating',
                'transport_driver_rating.comment',
                'transport_driver_rating.status',
                'users.first_name',
                'users.last_name',
                DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"),
                'user_rental_ride_booking.pickup_date_time as pickup_datetime',
                'user_rental_ride_booking.ride_no'
            )
                ->join('user_rental_ride_booking', 'user_rental_ride_booking.id', '=', 'transport_driver_rating.rental_id')
                ->join('users', 'users.id', '=', 'transport_driver_rating.user_id')
                ->join('transport_driver_details', 'transport_driver_details.id', '=', 'transport_driver_rating.driver_id')
                ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('user_rental_ride_booking.service_cat_id', '=', $service_cat_id)
                ->orderBy('transport_driver_rating.id', 'desc')
                ->get();
        } else {
            $provider_reviews = TransportRatings::query()->select(
                'transport_driver_rating.id', 'transport_driver_rating.rating',
                'transport_driver_rating.comment', 'transport_driver_rating.status',
                'users.first_name', 'users.last_name',
                DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'user_ride_booking.pickup_datetime', 'user_ride_booking.ride_no'
            )
                ->join('user_ride_booking', 'user_ride_booking.id', '=', 'transport_driver_rating.ride_id')
                ->join('users', 'users.id', '=', 'transport_driver_rating.user_id')
                ->join('transport_driver_details', 'transport_driver_details.id', '=', 'transport_driver_rating.driver_id')
                ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('user_ride_booking.service_cat_id', '=', $service_cat_id)
                ->orderBy('transport_driver_rating.id', 'desc')
                ->get();
        }
        if ($request->ajax()) {
            $view = view('admin.pages.transport_services.transport_provider_review_list', compact('slug', 'service_category', 'provider_reviews'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.transport_services.transport_provider_review_list', compact('slug', 'service_category', 'provider_reviews'));
    }

    //get Delete Transport Provider Ride Review
    public function getDeleteTransportProviderRideReview(Request $request)
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
                'success' => false
            ]);
        }
        $transport_review = TransportRatings::query()->where('id', '=', $id)->first();
        if ($transport_review == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $transport_review->delete();
        return response()->json([
            'success' => true
        ]);
    }

    //get Ajax Transport Provider Ride Review Status Change
    public function getAjaxUpdateProviderRideReviewStatus(Request $request)
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
        $transport_review = TransportRatings::query()->select('id', 'status')->where('id', '=', $id)->first();
        if ($transport_review == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($transport_review->status == 1) {
            $status = $transport_review->status = 0;
        } else {
            $status = $transport_review->status = 1;
        }
        $transport_review->save();
        return response()->json([
            'success' => true,
            'status' => $status,
        ]);
    }

    //get Transport Provider Ride Review List
    public function getTransportProviderReviewList($slug, $driver_id, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if (!is_numeric($driver_id) || $service_category == Null) {
            Session::flash('error', 'Provider not found!');
            return redirect()->back();
        }
        $driver_details = TransportDriverDetails::query()->select(DB::raw("CONCAT(providers.first_name,' ',providers.last_name) as driver_name"))
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->where('transport_driver_details.id', '=', $driver_id)
            ->first();
        if ($driver_details == Null) {
            Session::flash('error', 'Provider not found!');
            return redirect()->back();
        }
        $provider_reviews = TransportRatings::query()->select('transport_driver_rating.id', 'transport_driver_rating.rating',
            'transport_driver_rating.comment', 'transport_driver_rating.status', 'users.first_name', 'users.last_name',
            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'user_ride_booking.pickup_datetime', 'user_ride_booking.ride_no'
        )
            ->join('user_ride_booking', 'user_ride_booking.id', '=', 'transport_driver_rating.ride_id')
            ->join('users', 'users.id', '=', 'transport_driver_rating.user_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'transport_driver_rating.driver_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('transport_driver_rating.driver_id', '=', $driver_id)
            ->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->orderBy('transport_driver_rating.id', 'desc')
            ->get();
        if ($request->ajax()) {
            $view = view('admin.pages.transport_services.transport_provider_review_list', compact('driver_details', 'slug', 'service_category', 'provider_reviews'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.transport_services.transport_provider_review_list', compact('driver_details', 'slug', 'service_category', 'provider_reviews'));
    }

    //get Transport Service Category Vehicle Type
    public function getTransportServiceCategoryVehicleType($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Vehicle types not found!');
            return redirect()->back();
        }
        $service_cat_id = $service_category->id;
        if ($service_cat_id == 31) {
            $service_cat_id = $this->bike_ride;
        }
        if ($service_cat_id == 32) {
            $service_cat_id = $this->taxi_ride;
        }
        $vehicle_type_lists = TransportVehicleType::query()->select(
            'transport_vehicle_type.id', 'transport_vehicle_type.name',
            'transport_vehicle_type.icon_name', 'transport_vehicle_type.cost_for_km', 'transport_vehicle_type.status',
            'service_category.slug')
            ->join('service_category', 'service_category.id', '=', 'transport_vehicle_type.service_cat_id')
            ->where('service_category.id', $service_cat_id)
            ->orderBy('transport_vehicle_type.id', 'desc')
            ->get();
        if ($request->ajax()) {
            $view = view('admin.pages.transport_services.vehicle_type.manage_vehicle_type', compact('slug', 'service_category', 'vehicle_type_lists'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.transport_services.vehicle_type.manage_vehicle_type', compact('slug', 'service_category', 'vehicle_type_lists'));
    }

    //get Transport Vehicle Type
    public function getTransportVehicleType(Request $request)
    {
        $vehicle_type_lists = TransportVehicleType::query()->select('transport_vehicle_type.id', 'transport_vehicle_type.name',
            'transport_vehicle_type.icon_name', 'transport_vehicle_type.cost_for_km', 'transport_vehicle_type.status')
            ->join('service_category', 'service_category.id', '=', 'transport_vehicle_type.service_cat_id')
            ->whereIn('service_category.category_type', [1, 5])
            ->orderBy('transport_vehicle_type.id', 'desc')
            ->get();
        if ($request->ajax()) {
            $view = view('admin.pages.transport_services.vehicle_type.manage_vehicle_type', compact('vehicle_type_lists'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.transport_services.vehicle_type.manage_vehicle_type', compact('vehicle_type_lists'));
    }

    //get Add Transport Vehicle Type
    public function getAddTransportVehicleType($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $transport_weight_limit = TransportWeightList::query()->where('status','=',1)->get();
        if ($service_category == Null) {
            Session::flash('error', 'Required document`s service category not found!');
            return redirect()->back();
        }
        if ($request->ajax()) {
            $view = view('admin.pages.transport_services.vehicle_type.form_vehicle_type', compact('slug', 'service_category','transport_weight_limit'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.transport_services.vehicle_type.form_vehicle_type', compact('slug', 'service_category','transport_weight_limit'));
    }

    //get Edit Transport Vehicle Type
    public function getEditTransportVehicleType($slug, $id, Request $request)
    {
        $transport_vehicle_type = TransportVehicleType::query()->where('id', $id)->first();
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $get_selected_weight_list = TransportVehicleType::query()->select('weight_limit')->where('id', '!=',$id)->where('weight_limit','>',0)->get()->toArray();
        $transport_weight_limit = TransportWeightList::query()
                            ->where('transport_weight_list.status','=',1)
                            ->whereNotIn('transport_weight_list.id',$get_selected_weight_list)
                            ->get();

        $surcharge_timings = SurchargeTimings::query()->select('day', 'start_time', 'end_time', 'price')->where('service_cat_id', $service_category->id)->where('transport_vehicle_type_id','=',$id)->get();

        $surcharge_open_timings = $surcharge_timings->pluck('start_time', 'day')->toArray();
        $surcharge_close_timings = $surcharge_timings->pluck('end_time', 'day')->toArray();
        $price = $surcharge_timings->pluck('price', 'day')->toArray();

        if ($transport_vehicle_type != Null) {
            $view = view('admin.pages.transport_services.vehicle_type.form_vehicle_type', compact('slug', 'transport_vehicle_type', 'service_category','transport_weight_limit',
                'surcharge_timings', 'surcharge_open_timings', 'surcharge_close_timings', 'price'
            ));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        } else {
            Session::flash('error', 'Vehicle Type Not Found!');
            return redirect()->back();
        }
    }

    //Update or Store Transport Vehicle Type
    public function postUpdateTransportVehicleType(TransportVehicleTypeRequest $request)
    {

        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $id = $request->get('id');
        if ($id != Null) {
            $vehicle_type = TransportVehicleType::query()->where('id', $request->get('id'))->first();
            Session::flash('success', 'Service Category Updated successfully!');
        } else {
            $vehicle_type = new TransportVehicleType();
            Session::flash('success', 'Service Category Added successfully!');
        }
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == 31) {
            $service_cat_id = $this->bike_ride;
        }
        if ($service_cat_id == 32) {
            $service_cat_id = $this->taxi_ride;
        }
        $vehicle_type->service_cat_id = $service_cat_id;
        if ($id != 10) {
            $vehicle_type->name = $request->get('name');
        }
        if ($request->file('icon')) {
            if (\File::exists(public_path('/assets/images/service-category/transport-service-type/' . $vehicle_type->icon_name))) {
                \File::delete(public_path('/assets/images/service-category/transport-service-type/' . $vehicle_type->icon_name));
            }
            $file = $request->file('icon');
            $file_new = $request->get('service_cat_id') . date('siHYdm') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . '/assets/images/service-category/transport-service-type/', $file_new);
            $vehicle_type->icon_name = $file_new;
        }
        $vehicle_type->cost_for_km = $request->get('cost_for_km');
        $vehicle_type->weight_limit = $request->get('weight_limit');
//        $vehicle_type->dimension_limit = $request->get('dimension_limit');
        $vehicle_type->width_limit = $request->get('width_limit');
        $vehicle_type->height_limit = $request->get('height_limit');
        $vehicle_type->base_fare = $request->get('base_fare');
        $vehicle_type->time_fare = $request->get('time_fare');
        $vehicle_type->min_fare_amount = $request->get('min_fare_amount');
        $vehicle_type->rental_cost_for_km = $request->get('rental_cost_for_km') != Null ? $request->get('rental_cost_for_km') : 0;
        $vehicle_type->rental_amount_for_1hour = $request->get('rental_amount_for_1hour') != Null ? $request->get('rental_amount_for_1hour') : 0;
        $vehicle_type->rental_km_limit_for_1hour = $request->get('rental_km_limit_for_1hour') != Null ? $request->get('rental_km_limit_for_1hour') : 0;
        $vehicle_type->status = $request->get('status');
        $vehicle_type->save();
        $service_category = ServiceCategory::query()->where('id', $request->get('service_cat_id'))->first();


        $service_settings = ServiceSettings::query()->where('service_cat_id','=', $service_category->id)->first();
        if ($service_settings == Null){
            Session::flash('error', 'Some details not found!');
            return redirect()->back();
        }

        $surcharge_open_timings = $request->get('open_time');
        $surcharge_close_timings = $request->get('close_time');
        $price = $request->get('price');
        $request_days = [];
        if ($request->get('day')) {
            foreach ($request->get('day') as $key => $day) {
                $request_days[] = $key;
            }
        }

        $days = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
        if (in_array("all", $request_days) && $surcharge_open_timings[0] != Null && $surcharge_close_timings[0] != Null) {
            for ($i = 0; $i < 7; $i++) {
                try {
                    $update_surcharge_timing = SurchargeTimings::query()->where('service_cat_id', $service_category->id)->where('transport_vehicle_type_id','=',$vehicle_type->id)->where('day', $days[$i])->first();
                    if ($update_surcharge_timing == Null) {
                        $update_surcharge_timing = new SurchargeTimings();
                        $update_surcharge_timing->service_cat_id = $service_category->id;
                        $update_surcharge_timing->transport_vehicle_type_id = $vehicle_type->id;
                        $update_surcharge_timing->day = strtoupper(trim($days[$i]));
                    }
                    $update_surcharge_timing->start_time = Date('H:i:s', strtotime($surcharge_open_timings[0]));
                    $update_surcharge_timing->end_time = Date('H:i:s', strtotime($surcharge_close_timings[0]));
                    $update_surcharge_timing->price = $price[0];
                    $update_surcharge_timing->save();
                } catch (\Exception $e) {}
            }
        }
        else {
            for ($i = 0; $i < 7; $i++) {
                if (in_array($days[$i], $request_days) && $surcharge_open_timings[$i + 1] != Null && $surcharge_close_timings[$i + 1] != Null) {
                    try {
                        $update_surcharge_timing = SurchargeTimings::query()->where('service_cat_id', $service_category->id)->where('transport_vehicle_type_id','=',$vehicle_type->id)->where('day', $days[$i])->first();
                        if ($update_surcharge_timing == Null) {
                            $update_surcharge_timing = new SurchargeTimings();
                            $update_surcharge_timing->service_cat_id = $service_category->id;
                            $update_surcharge_timing->transport_vehicle_type_id = $vehicle_type->id;
                            $update_surcharge_timing->day = strtoupper(trim($days[$i]));
                        }
                        $update_surcharge_timing->start_time = Date('H:i:s', strtotime($surcharge_open_timings[$i + 1]));
                        $update_surcharge_timing->end_time = Date('H:i:s', strtotime($surcharge_close_timings[$i + 1]));
                        $update_surcharge_timing->price = $price[$i+1];
                        $update_surcharge_timing->save();
                    } catch (\Exception $e) {}
                } else {
                    try {
                        SurchargeTimings::query()->where('service_cat_id', $service_category->id)->where('transport_vehicle_type_id','=',$vehicle_type->id)->where('day', $days[$i])->delete();
                    } catch (\Exception $e) {}
                }
            }
        }

        if (isset($request->surcharge_timings_status) && $request->get('day') != Null) {
            $vehicle_type->surcharge_timings_status = 1;
        } else {
            $vehicle_type->surcharge_timings_status = 0;
            $dd = SurchargeTimings::query()->where('service_cat_id', $service_category->id)->where('transport_vehicle_type_id','=',$vehicle_type->id)->update([
                "start_time" => Null,
                "end_time" => Null,
                "price" => 0
            ]);
        }
        $vehicle_type->save();

        return redirect()->route('get:admin:transport_vehicle_type', [$service_category->slug]);
    }

    //get Delete Transport Vehicle Type
    public function getDeleteTransportVehicleType(Request $request)
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
                'success' => false
            ]);
        }
//        if ($id == 10) {
//            return response()->json([
//                'success' => 0,
//                'message' => 'Data not found'
//            ]);
//        }
        $vehicle_type = TransportVehicleType::query()->where('id', '=', $id)->first();
        if ($vehicle_type == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }

        if (in_array($vehicle_type->id, $this->transport_vehicle_id_array)){
            return response()->json([
                'success' => 11,
                'message' => 'Data not Delete'
            ]);
        }

        $check = TransportDriverDetails::query()->where('vehicle_type_id', $vehicle_type->id)->first();
        if ($check != Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not Delete'
            ]);
        }
        if (\File::exists(public_path('/assets/images/service-category/transport-service-type/' . $vehicle_type->icon_name))) {
            \File::delete(public_path('/assets/images/service-category/transport-service-type/' . $vehicle_type->icon_name));
        }
        $vehicle_type->delete();
        return response()->json([
            'success' => true
        ]);
    }

    //get Ajax Transport Vehicle Type Status Change
    public function getAjaxUpdateTransportVehicleTypeStatus(Request $request)
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
        $vehicle_type_details = TransportVehicleType::query()->select('id', 'status')->where('id', '=', $id)->first();
        if ($vehicle_type_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($vehicle_type_details->status == 1) {
            $status = $vehicle_type_details->status = 0;
        } else {
            $status = $vehicle_type_details->status = 1;
        }
        $vehicle_type_details->save();
        return response()->json([
            'success' => true,
            'status' => $status,
        ]);
    }


    //get Edit Transport Vehicle Type
    public function getEditTransportProviderVehicleDetails($slug, $id, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null || $id == Null) {
            Session::flash('error', 'Driver Vehicle Details Not Found!');
            return redirect()->back();
        }
        $transport_vehicle_type = TransportVehicleType::query()->where('service_cat_id', $service_category->id)->get();
        $transport_provider = TransportDriverDetails::query()->where('id', $id)->first();
        $view = view('admin.pages.transport_services.driver_vehicle_type_details.form', compact('slug', 'transport_vehicle_type', 'service_category', 'transport_provider'));
        if ($transport_provider != Null) {
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
//            return view('admin.pages.transport_services.driver_vehicle_type_details.form', compact('slug', 'transport_vehicle_type', 'service_category', 'transport_provider'));
        } else {
            Session::flash('error', 'Driver Vehicle Details Not Found!');
            return redirect()->back();
        }
    }

    //Update or Store Transport Vehicle Type
    public function postUpdateTransportProviderVehicleDetails(Request $request, $slug)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $validator = $request->validate([
            'model_year' => 'required',
        ]);
        $id = $request->get('id');
        if (!is_numeric($id) || $id == Null) {
            Session::flash('error', 'Driver Vehicle Details Not Updated!');
            return redirect()->back();
        }
        $transport_driver_details = TransportDriverDetails::query()->where('id', $id)->first();
        if ($transport_driver_details == Null) {
            Session::flash('error', 'Driver Vehicle Details Not Updated!');
            return redirect()->back();
        }
        $transport_driver_details->vehicle_type_id = $request->get('vehicle_type_id');
        $transport_driver_details->vehicle_company = $request->get('vehicle_company');
        $transport_driver_details->model_name = $request->get('model_name');
        $transport_driver_details->plat_no = $request->get('plat_no');
        $transport_driver_details->model_year = $request->get('model_year');
        $transport_driver_details->vehicle_color = $request->get('vehicle_color');
        $transport_driver_details->handicap = ($request->get('handicap') != Null) ? 1 : 0;
        $transport_driver_details->child_seat = ($request->get('child_seat') != Null) ? 1 : 0;
        $transport_driver_details->save();

        $provider_list_check = TransportDriverDetails::query()->select('provider_services.status')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->where('transport_driver_details.id', '=', $id)
            ->first();
        $status = "approved";
        if ($provider_list_check != Null) {
            if ($provider_list_check->status == 1) {
                $status = "approved";
            } elseif ($provider_list_check->status == 0) {
                $status = "un-approved";
            } elseif ($provider_list_check->status == 2) {
                $status = "blocked";
            } elseif ($provider_list_check->status == 3) {
                $status = "rejected";
            }
        }
        Session::flash('success', 'Driver Vehicle Details Updated successfully!');
        return redirect()->route('get:admin:transport_service_provider_list', [$slug, $status]);
    }

    //get Transport Service Category Site Setting
    public function getTransportServiceSetting($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $service_settings = ServiceSettings::query()->where('service_cat_id', $service_category->id)->first();
        if ($service_category != Null) {
            $view = view('admin.pages.transport_services.site_setting.form', compact('slug', 'service_category', 'service_settings'));
        } else {
            $view = view('admin.pages.transport_services.site_setting.form', compact('slug', 'service_category'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //Save or Update Sransport Service Setting
    public function postUpdateTransportServiceSetting(Request $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $service_settings = $this->adminClass->ServiceSettingsStore($request);
        $sevice_category = ServiceCategory::query()->where('id', $service_settings->service_cat_id)->first();
        $id = $request->get('id');
        if ($id != Null) {
            Session::flash('success', 'Service Settings Updated successfully!');
        } else {
            Session::flash('success', 'Service Settings Added successfully!');
        }
        if ($sevice_category->category_type == 1 || $sevice_category->category_type == 5) {
            return redirect()->route('get:admin:transport_service_setting', ['slug' => $sevice_category->slug]);
        } else {
            return redirect()->route('get:admin:transport_service_setting', ['slug' => $sevice_category->slug]);
        }
    }

    //get Add Providers
    public function getAddProvider($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        if ($service_category->category_type == 5) {
            $vehicle_types = TransportVehicleType::query()->whereIn('service_cat_id', [4])->where('status', '=', 1)->get();
        } else {
            $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $service_category->id)->where('status', '=', 1)->get();
        }
        if ($slug != "taxi-ride") {
            if ($slug == "bike-ride") {
                $display_category = 1;
            } elseif ($slug == "courier-service") {
                $display_category = 3;
            } else {
                $display_category = 2;
            }
        } else {
            $display_category = 0;
        }
        if ($vehicle_types->isEmpty()) {
            $view = view('admin.pages.transport_services.providers.form', compact('slug', 'service_category', 'display_category'));
        } else {
            $view = view('admin.pages.transport_services.providers.form', compact('slug', 'service_category', 'display_category', 'vehicle_types'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Edit Transport Vehicle Type
    public function getEditTransportProvider($slug, $driver_details_id, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $driver_detials = TransportDriverDetails::query()->select('service_category.slug as service_cat_slug',
            'service_category.id as service_cat_id',
            'providers.id as provider_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.email', 'providers.contact_number','providers.country_code',
            'providers.avatar', 'providers.service_radius', 'providers.gender','providers.first_name','providers.last_name',
            'provider_services.status', 'provider_services.id as provider_service_id',
            'transport_driver_details.id as driver_details_id', 'transport_driver_details.rating',
            'transport_driver_details.vehicle_type_id', 'transport_driver_details.vehicle_company',
            'transport_driver_details.plat_no', 'transport_driver_details.model_year', 'transport_driver_details.rental_service',
            'transport_driver_details.model_name',
            'transport_driver_details.vehicle_color', 'transport_driver_details.driver_service', 'transport_driver_details.delivery_boy',
            'transport_driver_details.courier_boy', 'transport_driver_details.no_of_seat', 'transport_driver_details.support_weight'
        )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('transport_driver_details.id', $driver_details_id)
            ->first();

        if ($driver_detials != Null) {
            if ($service_category->category_type == 5) {
                if ($driver_detials->driver_service == 1) {
                    $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $this->bike_ride)->where('status', '=', 1)->get();
                } else {
//                    $vehicle_types = TransportVehicleType::query()->whereIn('service_cat_id', [1, 4])->where('status', '=', 1)->get();
                    $vehicle_types = TransportVehicleType::query()->whereIn('service_cat_id', [4])->where('status', '=', 1)->get();
                }
//                $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $service_category->id)->where('status', '=', 1)->get();
            } elseif ($service_category->id == $this->bike_rental) {
                $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $this->bike_ride)->where('status', '=', 1)->get();
            } elseif ($service_category->id == $this->taxi_rental) {
                $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $this->taxi_ride)->where('status', '=', 1)->get();
            } else {
                $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $service_category->id)->where('status', '=', 1)->get();
            }
            if ($slug != "taxi-ride") {
                if ($slug == "bike-ride") {
                    $display_category = 1;
                } elseif ($slug == "courier-service") {
                    $display_category = 3;
                } else {
                    $display_category = 2;
                }
            } else {
                $display_category = 0;
            }
            if ($request->ajax()) {
                $view = view('admin.pages.transport_services.providers.form', compact('slug', 'display_category', 'service_category', 'vehicle_types', 'driver_detials'))->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return view('admin.pages.transport_services.providers.form', compact('slug', 'display_category', 'service_category', 'vehicle_types', 'driver_detials'));
        } else {
            Session::flash('error', 'Driver Not Found!');
            return redirect()->back();
        }
    }

    //Update or Store Transport Provider
    public function postUpdateTransportProvider($slug, ProviderStoreRequest $request)
    {

        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $provider_details = $this->adminClass->AddServiceProvider($request);
        if ($provider_details == Null) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        $provider_service_id = $request->get('provider_service_id');
        $service_cat_id = $service_category->id;
        if ($service_cat_id == 31) {
            $service_cat_id = $this->bike_ride;
        }
        if ($service_cat_id == 32) {
            $service_cat_id = $this->taxi_ride;
        }
        //update provider type status
        $provider_data =  Provider::query()->where('id','=',$provider_details->id)->first();
        if($provider_data != Null){
            $provider_data->provider_type = 2;
            $provider_data->save();
        }

        $provider_services = ProviderServices::query()->where('id', $provider_service_id)->first();
        if ($provider_services == Null) {
            $provider_services = new ProviderServices();
            $provider_services->provider_id = $provider_details->id;
            $provider_services->service_cat_id = $service_cat_id;
            $provider_services->current_status = 1;
            $provider_services->status = 1;
            $provider_services->save();
        } else {
            $provider_services->provider_id = $provider_details->id;
            $provider_services->save();
        }

        $driver_details_id = $request->get('driver_details_id');
        $transport_driver = TransportDriverDetails::query()->where('id', $driver_details_id)->first();
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
        $transport_driver->vehicle_company = $request->get('vehicle_company');
        $transport_driver->plat_no = $request->get('plat_no');
        $transport_driver->model_year = $request->get('model_year');
        $transport_driver->model_name = $request->get('model_name');
        $transport_driver->vehicle_color = $request->get('vehicle_color');
        if ($service_category->id == $this->taxi_ride) {
            $transport_driver->driver_service = 1;
            $transport_driver->delivery_boy = 0;
            $transport_driver->courier_boy = 0;
            $transport_driver->courier_boy = 0;
            $transport_driver->rental_service = 0;
            $transport_driver->no_of_seat = $request->get('no_of_seat');
        } elseif ($service_category->id == $this->taxi_rental) {
            $transport_driver->driver_service = 1;
            $transport_driver->delivery_boy = 0;
            $transport_driver->courier_boy = 0;
            $transport_driver->courier_boy = 0;
            $transport_driver->rental_service = 1;
            $transport_driver->no_of_seat = $request->get('no_of_seat');
        } elseif ($service_category->id == $this->bike_ride || $service_category->id == $this->bike_rental) {
            if ($request->get('driver_service') == 1) {
                $transport_driver->driver_service = 1;
            } else {
                $transport_driver->driver_service = 0;
            }
            if ($request->get('delivery_boy') == 1) {
                $transport_driver->delivery_boy = 1;
            } else {
                $transport_driver->delivery_boy = 0;
            }
            if ($request->get('courier_boy') == 1) {
                $transport_driver->courier_boy = 1;
            } else {
                $transport_driver->courier_boy = 0;
            }
            if ($request->get('rental_service') == 1) {
                $transport_driver->rental_service = 1;
            } else {
                $transport_driver->rental_service = 0;
            }
            $transport_driver->no_of_seat = 1;
        } elseif ($service_category->id == $this->courier_service) {
            if ($request->get('driver_service') == 1) {
                $transport_driver->driver_service = 1;
            } else {
                $transport_driver->driver_service = 0;
            }
            if ($request->get('courier_boy') == 1) {
                $transport_driver->courier_boy = 1;
            } else {
                $transport_driver->courier_boy = 0;
            }
            $transport_driver->rental_service = 0;
            $transport_driver->support_weight = $request->get('support_weight');
        }
        $transport_driver->save();
        $id = $request->get('id');
        if ($id != Null) {
            $msg = 'Provider update successfully!';
        } else {
            $msg = 'Provider add successfully!';
        }
        $status = "approved";
        if ($provider_services != Null) {
            if ($provider_services->status == 1) {
                $status = "approved";
            } elseif ($provider_services->status == 0) {
                $status = "un-approved";
            } elseif ($provider_services->status == 2) {
                $status = "blocked";
            }
        }
        Session::flash('success', $msg);
        return redirect()->route('get:admin:transport_service_provider_list', [$slug, $status]);
    }


    public function getTransportRequiredDocumentList($slug, Request $request)
    {
        //transport service category find
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
//
        $required_documents_list = RequiredDocuments::query()->select('required_documents.id', 'required_documents.name as document_name',
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
    public function getTransportAddRequiredDocument($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
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
    public function getTransportEditRequiredDocument($slug, $id, Request $request)
    {
        $required_document = RequiredDocuments::query()->where('id', $id)->first();
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
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
    public function postTransportUpdateRequiredDocument(RequiredDocumentsRequest $request)
    {
        $id = $request->get('id');
        if ($id != Null) {
            $required_document = RequiredDocuments::query()->where('id', $request->get('id'))->first();
        } else {
            $required_document = new RequiredDocuments();
        }
        $required_document->name = $request->get('name');
        $required_document->service_cat_id = $request->get('service_cat_id');
        $required_document->status = $request->get('status');
        $required_document->save();

        $sevice_category = ServiceCategory::query()->where('id', $required_document->service_cat_id)->first();
        if ($id != Null) {
            Session::flash('success', $sevice_category->slug . ' Required Document Updated successfully!');
        } else {
            Session::flash('success', $sevice_category->slug . ' Required Document Added successfully!');
        }
        return redirect()->route('get:admin:transport_required_document_list', [$sevice_category->slug]);
    }


    public function getTransportEarningReport($slug, Request $request)
    {
        if (Auth::guard("admin")->check()) {
            if (Auth::guard("admin")->user()->roles == 1) {
            } elseif (Auth::guard("admin")->user()->roles == 2) {
                return redirect()->route('get:dispatcher:manual_ride_booking');
            } elseif (Auth::guard("admin")->user()->roles == 3) {
                return redirect()->route('get:account:dashboard');
            } else {
                Auth::guard('admin')->logout();
                return redirect()->route('get:admin:login');
            }
        } else {
            Auth::guard('admin')->logout();
            return redirect()->route('get:admin:login');
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service Category Not Found!');
            return redirect()->back();
        }
        $user_list = TransportRideBook::query()->select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_ride_booking.user_id')
            ->where('user_ride_booking.status', '=', 9)
            ->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_ride_booking.user_id')
            ->get();
        $driver_list = TransportRideBook::query()->select('transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_ride_booking.driver_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_ride_booking.driver_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_ride_booking.status', '=', 9)
            ->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_ride_booking.driver_id')
            ->get();

        $from_date = Null;
        $to_date = Null;
        $driver = Null;
        $user = Null;
        $payment_type = Null;
        $driver_pay_type = Null;

        $view = view('admin.pages.transport_services.transport_earings_reports', compact('slug', 'service_category', 'from_date', 'to_date', 'driver', 'user', 'payment_type', 'driver_pay_type', 'user_list', 'driver_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //post transport search earning report
    public function PostTransportSearchEarningReport($slug, Request $request)
    {
        if (Auth::guard("admin")->check()) {
            if (Auth::guard("admin")->user()->roles == 1) {

            } elseif (Auth::guard("admin")->user()->roles == 2) {
                return redirect()->route('get:dispatcher:manual_ride_booking');
            } elseif (Auth::guard("admin")->user()->roles == 3) {
                return redirect()->route('get:account:dashboard');
            } else {
                Auth::guard('admin')->logout();
                return redirect()->route('get:admin:login');
            }
        } else {
            Auth::guard('admin')->logout();
            return redirect()->route('get:admin:login');
        }
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service Category Not Found!');
            return redirect()->back();
        }
        $user_list = TransportRideBook::query()->select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_ride_booking.user_id')
            ->where('user_ride_booking.status', '=', 9)
            ->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_ride_booking.user_id')
            ->get();
        $driver_list = TransportRideBook::query()->select('transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_ride_booking.driver_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_ride_booking.driver_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_ride_booking.status', '=', 9)
            ->where('user_ride_booking.service_cat_id', '=', $service_category->id)
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
            'user_ride_booking.promo_code'
        )
            ->where('user_ride_booking.status', '=', 9)
            ->where('user_ride_booking.service_cat_id', '=', $service_category->id);
        if ($request->get('from_date') != Null && $request->get('to_date') != Null) {
            $from_date = $request->get('from_date');
            $to_date = $request->get('to_date');
            /*$admin_report_details->whereBetween('user_ride_booking.pickup_datetime', [$from_date . " 00:00:00", $to_date . " 23:59:59"]);*/

            $from = Date('Y-m-d', strtotime($from_date)) . " 00:00:00";
            $to = Date('Y-m-d', strtotime($to_date)) . " 23:59:59";
            $admin_report_details->whereDate('user_ride_booking.pickup_datetime', '>=', $from);
            $admin_report_details->whereDate('user_ride_booking.pickup_datetime', '<=', $to);
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
//            if ($payment_type != 2) {
            $admin_report_details->where('user_ride_booking.payment_type', '=', $request->get('payment_type'));
//            }
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
                $promocode = UsedPromocodeDetails::query()->where('id', $report->promo_code)->where('service_cat_id', '=', $report->service_cat_id)->first();
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

            $view = view('admin.pages.transport_services.transport_earings_reports', compact('slug', 'service_category', 'from_date', 'to_date', 'driver', 'user', 'payment_type', 'driver_pay_type', 'user_list', 'driver_list',
                'payment_reports',
                'used_promocode_amount', 'driver_commission', 'collect_payment',
                'total_ride', 'total_site_earning', 'total_driver_earning', 'total_discount', 'total_pay_driver', 'total_collect_payment', 'total_driver_outstanding_amount'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        } else {
            $view = view('admin.pages.transport_services.transport_earings_reports', compact('slug', 'service_category', 'from_date', 'to_date', 'driver', 'user', 'payment_type', 'driver_pay_type', 'user_list', 'driver_list'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        }

    }





    //post transport driver payment settled
    public function postTransportDriverPaymentSettled(Request $request, $slug)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
//        if (Auth::guard("admin")->check()) {
//            if (Auth::guard("admin")->user()->roles == 1) {
//
//            } elseif (Auth::guard("admin")->user()->roles == 2) {
//                return redirect()->route('get:dispatcher:manual_ride_booking');
//            } elseif (Auth::guard("admin")->user()->roles == 3) {
//                return redirect()->route('get:account:dashboard');
//            } else {
//                Auth::guard('admin')->logout();
//                return redirect()->route('get:admin:login');
//            }
//        } else {
//            Auth::guard('admin')->logout();
//            return redirect()->route('get:admin:login');
//        }
        if (Auth::guard("admin")->check()) {
            if (Auth::guard("admin")->user()->roles != 1) {
                return response()->json([
                    "status" => false,
                    "message" => 'Something went to wrong!'
                ]);
            }
        } else {
            return response()->json([
                "status" => false,
                "message" => 'Something went to wrong!'
            ]);
        }
        $driver_pay_details = $request->get('ride_id');
        if ($driver_pay_details != Null) {
            foreach ($driver_pay_details as $key => $pay) {
                $pay_store = TransportRideBook::query()->where('id', $pay)->first();
                if ($pay_store != Null && $pay_store->driver_pay_settle_status == 0) {
                    $pay_store->driver_pay_settle_status = 1;
                    $pay_store->save();
                }
            }
        }
        return response()->json([
            "success" => true,
            "message" => "success"
        ]);
//        return redirect()->route('get:admin:transport_earning_report', $slug);

    }



    //provider display on map in My Bantay function
    public function getAdminTransportProviderLocation($slug, Request $request)
    {
//        $this->bike_ride = 1;
//        $this->taxi_ride = 2;
//        $this->transport_service_id_array = [1, 2];
//        $this->delivery_service_id_array = [5, 6, 7, 8, 9, 10];
//
//        $this->all = [0, 4, 9, 10];
//        $this->available = [0, 4, 9, 10];
//        $this->ride_start = [5, 6, 7, 8];
//        $this->reached = [3];
//        $this->enroute = [1, 2,];

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $expire_date_time = date('Y-m-d h:i:s', strtotime("-120 minutes"));
        $running_service = ProviderUserRunningService::all();
        $transport_running_service = [];
        $delivery_running_service = [];
        foreach ($running_service as $key => $running) {
            if (in_array($running->service_cat_id, $this->transport_service_id_array)) {
                $transport_running_service[$running->provider_id] = TransportRideBook::query()->select('status')->where('id', $running->booking_id)->first();
            } elseif (in_array($running->service_cat_id, $this->delivery_service_id_array)) {
                $delivery_running_service[$running->provider_id] = UserProductBooking::query()->select('order_delivery_status')->where('id', $running->booking_id)->first();
            }
        }
        $providers_detials = TransportDriverDetails::query()->select(
            'transport_driver_details.id',
            'providers.id as provider_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.last_name as last_name', 'providers.contact_number',
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
//            ->where('service_category.id', $service_category->id)
//            ->where('provider_services.current_status', 1)
            ->where('provider_services.status', 1)
            ->where('providers.status', 1)
            ->whereNotNull('providers.access_token');

        if ($service_category->id == $this->bike_rental) {
            $providers_detials = $providers_detials->whereIn('service_category.id', [$service_category->id, $this->bike_ride])
                ->where('rental_service', 1)->get();
        } elseif ($service_category->id == $this->taxi_rental) {
            $providers_detials = $providers_detials->whereIn('service_category.id', [$service_category->id, $this->taxi_ride])
                ->where('rental_service', 1)->get();
        } else {
            if ($service_category->id == 4) {
                $providers_detials->where('service_category.id', $service_category->id, $this->bike_ride)
                    ->where('transport_driver_details.courier_boy', 1);
            } else {
                $providers_detials->where('service_category.id', $service_category->id);
            }
            $providers_detials = $providers_detials->get();
        }
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
                    "phone" =>User::ContactNumber2Stars ($provider->contact_number),
                    "service_cat_id" => $provider->service_cat_id,
                ];
            }
        }
        $view = view('admin.pages.transport_services.transport_gods_view', compact('slug', 'service_category', 'providers'));
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
            return $providers = [];
        }
        $service_category = ServiceCategory::query()->where('id', $service_cat_id)->first();
        $check_type = 0;
        return $this->adminClass->AllProviderLocation($service_category, $check_type);

    }

    public function getAdminTransportAvailableProviderLocation(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == 0) {
            return $providers = [];
        }
        $service_category = ServiceCategory::query()->where('id', $service_cat_id)->first();

        $check_type = 0;
        return $this->adminClass->AvailableProviderLocation($service_category, $check_type);
    }

    public function getAdminTransportRideStartProviderLocation(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == 0) {
            return $providers = [];
        }
        $service_category = ServiceCategory::query()->where('id', $service_cat_id)->first();
        $check_type = 0;
        return $this->adminClass->RideStartProviderLocation($service_category, $check_type);
    }

    public function getAdminTransportRideReachedProviderLocation(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == 0) {
            return $providers = [];
        }
        $service_category = ServiceCategory::query()->where('id', $service_cat_id)->first();
        $check_type = 0;
        return $this->adminClass->RideReachedProviderLocation($service_category, $check_type);

    }

    public function getAdminTransportRideEnrouteProviderLocation(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == 0) {
            return $providers = [];
        }
        $service_category = ServiceCategory::query()->where('id', $service_cat_id)->first();
        $check_type = 0;
        return $this->adminClass->RideEnrouteProviderLocation($service_category, $check_type);
    }

    //using ajax provider display on map in My Bantay function
    public function getAdminTransportLocationOnMap(Request $request)
    {
        $service_cat_id = $request->get('service_cat_id');
        $current_status = $request->get('current_status');
        if ($current_status == Null) {
            return Null;
        }
        if ($service_cat_id == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $check_type = 0;
        $service_category = ServiceCategory::query()->where('id', $service_cat_id)->first();
        if ($current_status != "all") {
            if ($current_status == "available") {
                return $this->adminClass->AvailableProviderLocation($service_category, $check_type);
            } elseif ($current_status == "ride_start") {
                return $this->adminClass->RideStartProviderLocation($service_category, $check_type);
            } elseif ($current_status == "reached_pickup") {
                return $this->adminClass->RideReachedProviderLocation($service_category, $check_type);
            } elseif ($current_status == "enroute_pickup") {
                return $this->adminClass->RideEnrouteProviderLocation($service_category, $check_type);
            } else {
                return $this->adminClass->AllProviderLocation($service_category, $check_type);
            }
        } else {
            return $this->adminClass->AllProviderLocation($service_category, $check_type);
        }
    }

    //using ajax provider display on map in My Bantay function
    public function getAdminTransportSearchProviderOnMap(Request $request)
    {
        $search_provider_name = $request->get('provider');
        $service_cat_id = $request->get('service_cat_id');
        $current_status = $request->get('driver_current_status');
        if ($current_status == Null) {
            return $providers = [];
        }
        if ($service_cat_id == 0) {
            return $providers = [];
//            return response()->json([
//                'success' => false,
//                'message' => 'Data not found'
//            ]);
        }
        $check_type = 0;
        $service_category = ServiceCategory::query()->where('id', $service_cat_id)->first();
        if ($current_status != "all") {
            if ($current_status == "available") {
                return $this->adminClass->AvailableProviderLocation($service_category, $check_type, $search_provider_name);
            } elseif ($current_status == "ride_start") {
                return $this->adminClass->RideStartProviderLocation($service_category, $check_type, $search_provider_name);
            } elseif ($current_status == "reached_pickup") {
                return $this->adminClass->RideReachedProviderLocation($service_category, $check_type, $search_provider_name);
            } elseif ($current_status == "enroute_pickup") {
                return $this->adminClass->RideEnrouteProviderLocation($service_category, $check_type, $search_provider_name);
            } else {
                return $this->adminClass->AllProviderLocation($service_category, $check_type, $search_provider_name);
            }
        } else {
            return $this->adminClass->AllProviderLocation($service_category, $check_type, $search_provider_name);
        }
    }


    public function getTransportUpdateRideStatus(Request $request)
    {
        if($this->is_restricted == 1){
            return response()->json([
                'success' => false,
                'message' => 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.'
            ]);
        }
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
        if ($transport_ride->status == 4) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $user_details = User::query()->where('id', $transport_ride->user_id)->first();
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

                    //cancel chanrge calculate only for courier service
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
                            $this->notificationClass->driverCancelNotification($transport_ride->id,$driver_details->device_token, $transport_ride->service_cat_id, $driver_details->id, $driver_details->login_device,$driver_details->language,$cancel_by);
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
                        //$service_category = ServiceCategory::query()->where('id', $transport_ride->service_cat_id)->first();
                        if ($driver_details != Null){
                            ProviderUserRunningService::query()->where('provider_id', $driver_details->provider_id)->where('user_id', $transport_ride->user_id)->where('service_cat_id', $transport_ride->service_cat_id)->where('booking_id', $transport_ride->id)->delete();
                            $this->notificationClass->driverCompletedNotification($driver_details->device_token, $transport_ride->service_cat_id, $driver_details->provider_service_id, $driver_details->login_device,$driver_details->language);
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

    //refund order
    public function getUserCourierRefundAmountSettle(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $order_details = TransportRideBook::query()->where('id', $id)->first();
        if ($order_details == Null) {
//            Session::flash('error', 'Store Category Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ]);
        }
        if ($order_details->user_refund_status == 0) {
            if ($order_details->payment_type == 2 || $order_details->payment_type == 3) {

                $service_category = ServiceCategory::query()->where('id', $order_details->service_cat_id)->first();
                if ($service_category == Null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service category details not found'
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
                $add_balance->subject = "Refund from  - " . $service_category->name . " #" . $order_details->ride_no;
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

    public function getTransportSingleProviderRideList(Request $request){
        $status_check = $request->get('status_check');
        $slug = $request->get('slug');
        $driver_id = $request->get('driver_id');

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();

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


        $driver_all_ride_list =  TransportDriverDetails::query()
                                    ->select('transport_driver_details.id')
                                    ->join('provider_services','provider_services.id','=','transport_driver_details.provider_service_id')
                                    ->join('providers','providers.id','=','provider_services.provider_id')
                                    ->leftJoin('user_store_product_booking','user_store_product_booking.delivery_people_id','=','transport_driver_details.id')
                                    ->leftJoin('user_ride_booking','user_ride_booking.driver_id','=','transport_driver_details.id')
                                    ->leftJoin('user_rental_ride_booking','user_rental_ride_booking.driver_id','=','transport_driver_details.id')
                                    ->where('transport_driver_details.id','=',$driver_id)
                                    ->get();

//        return $driver_all_ride_list;


        $ride_lists_check = TransportRideBook::query()
            ->select('user_ride_booking.id', 'user_ride_booking.ride_no', 'user_ride_booking.service_cat_id',
                'user_ride_booking.total_pay', 'user_ride_booking.status', 'user_ride_booking.pickup_address',
                'user_ride_booking.destination_address',
//                'user_ride_booking.vehicle_type_name as vehicle_name',
                'user_ride_booking.user_name',
                'user_ride_booking.driver_name',
                'transport_vehicle_type.name as vehicle_name'
            )
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_ride_booking.vehicle_type_id')
            ->where('user_ride_booking.driver_id', '=', $driver_id)
//            ->where('user_ride_booking.service_cat_id', '=', $service_category->id)
            ->orderBy('user_ride_booking.pickup_datetime', 'desc');
        /*if ($slug == "courier-service") {
            $ride_lists_check->whereIn('user_ride_booking.service_cat_id', [$this->courier_service]);
        } elseif ($slug == "bike-ride") {
            $ride_lists_check->whereIn('user_ride_booking.service_cat_id', [$this->bike_ride]);
        } else {
            $ride_lists_check->where('user_ride_booking.service_cat_id', '=', $service_category->id);
        }*/
        $ride_lists = $ride_lists_check;

        $ride_list_data = $ride_lists;
        $totalRecords = $ride_lists->count();
        if($searchValue != ""){
            $totalRecordswithFilter = $ride_lists->where(function($query) use ($searchValue){
                $query->orWhere('user_ride_booking.ride_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('user_ride_booking.user_name', 'like', '%' .$searchValue . '%')
                    ->orWhere('user_ride_booking.driver_name', 'like', '%' .$searchValue . '%')
                    ->orWhere('user_ride_booking.total_pay', 'like', '%' .$searchValue . '%');
            })->count();
        } else{
            $totalRecordswithFilter = $ride_lists->count();
        }

        $record = $ride_list_data;
        if($searchValue != ""){
            $record = $record->where(function($query) use ($searchValue){
                $query->orWhere('user_ride_booking.ride_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('user_ride_booking.user_name', 'like', '%' .$searchValue . '%')
                    ->orWhere('user_ride_booking.driver_name', 'like', '%' .$searchValue . '%')
                    ->orWhere('user_ride_booking.total_pay', 'like', '%' .$searchValue . '%');
            });
        }
        $records = $record->skip($start)
            ->take($rowperpage)
            ->get();

        $i = 1;
        $data_arr = [];
        foreach ($records as $record){
            if($record->status == 0){
                $ride_status = "pending";
            } else if($record->status == 1 || $record->status == 2 || $record->status == 3){
                $ride_status = "approved";
            } else if($record->status == 4){
                $ride_status = "cancelled";
            } else if($record->status == 5 || $record->status == 6 || $record->status == 7 || $record->status == 8){
                $ride_status = "running";
            } else if($record->status == 9){
                $ride_status = "completed";
            } else if($record->status == 10){
                $ride_status = "failed";
            }
            $ride_status_html = '<span class="'.$ride_status.'">'.$ride_status.'</span>';
            $transport_services = $this->transport_service_id_array;
            $courier_service = $this->courier_service_id_array;
            $delivery_service = $this->delivery_service_id_array;
            if(Auth::guard("admin")->check()){
                if(Auth::guard("admin")->user()->roles == 1){
                    if(in_array($record->service_cat_id, $transport_services)){
                        $ride_details_html = '<a href="'.route('get:admin:transport_ride_details',[ $slug, $record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Ride Details"></i>
                                                </span>
                                               </a>';
                    } elseif(in_array($record->service_cat_id, $courier_service)){
                        $ride_details_html = '<a href="'.route('get:admin:transport_ride_details',[ $slug, $record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Delivery Details"></i>
                                                </span>
                                               </a>';
                    } elseif(in_array($record->service_cat_id, $delivery_service)){
                        $ride_details_html = '<a href="'.route('get:admin:transport_delivery_details',[ $slug, $record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Order Details"></i>
                                                </span>
                                               </a>';
                    }
                } elseif(Auth::guard("admin")->user()->roles == 2){
                    if(in_array($record->service_cat_id, $transport_services)){
                        $ride_details_html = '<a href="'.route('get:dispatcher:ride_details',[ $slug, $record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Ride Details"></i>
                                                </span>
                                               </a>';
                    }
                } elseif(Auth::guard("admin")->user()->roles == 3){
                    if(in_array($record->service_cat_id, $transport_services)){
                        $ride_details_html = '<a href="'.route('get:account:ride_details',[$record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Ride Details"></i>
                                                </span>
                                               </a>';
                    } elseif(in_array($record->service_cat_id, $courier_service)){
                        $ride_details_html = '<a href="'.route('get:account:ride_details',[$record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Delivery Details"></i>
                                                </span>
                                               </a>';
                    } elseif(in_array($record->service_cat_id, $delivery_service)){
                        $ride_details_html = '<a href="'.route('get:account:delivery_details',[$record->id]).'" class="render_link">
                                                <span class="icon-list-demo">
                                                    <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Order Details"></i>
                                                </span>
                                               </a>';
                    }
                }
            }
            $address = '<i class="fa fa-map-marker text-success"></i>'.$record->pickup_address.'<br>
                        <i class="fa fa-map-marker text-danger"></i>'. $record->destination_address;
            $data_arr[] = array(
                "no" => $i,
                "ride_no" => $record->ride_no,
                "customer_name" => $record->user_name,
                "driver_name" => $record->driver_name,
                "vehicle_type" => $record->vehicle_name,
                "total_cost" => '<span class="currency"></span>'.$record->total_pay,
                'status' =>$ride_status,
                'details'=>$ride_details_html,
                'address'=>$address
            );
            $i++;
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );


        return json_encode($response);

    }

    public function getTransportProvidersApprovedList(Request $request) {
        $status = "approved";
        return $this->getTransportProvidersList($request, $status);
    }

    public function getTransportProvidersUnApprovedList(Request $request){
        $status = "un-approved";
        return $this->getTransportProvidersList($request, $status);
    }

    public function getTransportProvidersBlockedList(Request $request){
        $status = "blocked";
        return $this->getTransportProvidersList($request, $status);
    }

    public function getTransportProvidersRejectedList(Request $request){
        $status = "rejected";
        return $this->getTransportProvidersList($request, $status);
    }

    public function getTransportProvidersList($request, $status) {
        if ($status == "approved") {
            $status = 1;
        }
        elseif ($status == "un-approved") {
            $status = 0;
        }
        elseif ($status == "blocked") {
            $status = 2;
        }
        elseif ($status == "rejected") {
            $status = 3;
        }
        else {
            Session::flash('error', 'Category provider list not found!');
            return redirect()->back();
        }
        $provider_lists = null;
//        $provider_lists = TransportDriverDetails::query()->select(
//            'service_category.slug as service_cat_slug',
//            'service_category.id as service_cat_id',
//            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"),
//            'providers.email',
//            'providers.contact_number',
//            'providers.created_at',
//            'provider_services.status',
//            'provider_services.id as provider_service_id',
//            'transport_vehicle_type.name as vehicle_type_name', 'transport_vehicle_type.icon_name as type_icon',
//            'transport_driver_details.id as driver_details_id', 'transport_driver_details.vehicle_company',
//            'transport_driver_details.plat_no', 'transport_driver_details.model_name', 'transport_driver_details.model_year',
//            'transport_driver_details.vehicle_color', 'transport_driver_details.rating', 'transport_driver_details.total_request',
//            'transport_driver_details.total_completed', 'transport_driver_details.total_cancelled',
//            'transport_driver_details.handicap',
//            'transport_driver_details.child_seat',
//            'transport_driver_details.driver_service',
//            'transport_driver_details.delivery_boy',
//            'transport_driver_details.courier_boy',
//            'transport_driver_details.rental_service',
//        )
//            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
//            ->join('providers', 'providers.id', 'provider_services.provider_id')
//            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
//            //->where('providers.status', '=', $status)
//            ->where('provider_services.status', '=', $status)
//            ->whereIn('service_category.id', [$this->bike_ride, $this->taxi_ride, $this->courier_service, $this->bike_rental, $this->taxi_rental])
//            ->orderBy('transport_driver_details.id', 'desc')
//            ->get();
        $service_category_id = 0;
        $view = view('admin.pages.transport_services.transport_provider_list', compact('status','provider_lists','service_category_id'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getAddTransportDriver(Request $request) {
        //$service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $service_category = ServiceCategory::query()->whereIn('id', [$this->bike_ride, $this->taxi_ride, $this->courier_service])->get();
        if ($service_category->isEmpty()) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $this->bike_ride)->where('status', '=', 1)->get();

        if ($vehicle_types->isEmpty()) {
            $view = view('admin.pages.transport_services.providers.driver_form', compact( 'service_category'));
        } else {
            $view = view('admin.pages.transport_services.providers.driver_form', compact('service_category', 'vehicle_types'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getTransportVehicleTypeList(Request $request)
    {

        $service_cat_id = $request->get('service_cat_id');
        if ($service_cat_id == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }

        $service_category = ServiceCategory::query()->where('id', $service_cat_id)->first();
        if ($service_category == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Service category not found'
            ]);
        }

        if ($service_category->category_type == 5) {
            $get_vehicle_type_list = TransportVehicleType::query()->whereIn('service_cat_id', [4])->whereNotIn('id', $this->transport_vehicle_id_array)->where('status', '=', 1)->get();
        } else {
            $get_vehicle_type_list = TransportVehicleType::query()->where('service_cat_id', $service_category->id)->where('status', '=', 1)->get();
        }
        $vehicle_type_list[] = "<option disabled selected>Select Vehicle Type</option>";

        if ($get_vehicle_type_list->isNotEmpty()){
            foreach ($get_vehicle_type_list as $key => $get_vehicle_type){
                $vehicle_type_list[] = "<option value='".$get_vehicle_type->id ."'>".$get_vehicle_type->name."</option>";
            }
        }
        return response()->json([
            'success' => true,
            'vehicle_type_list' => $vehicle_type_list
        ]);
    }

    //Update or Store Transport Provider
    public function postUpdateTransportDriver(ProviderStoreRequest $request)
    {
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        $service_cat_id = $request->get('service_cat_id');
        $service_category = ServiceCategory::query()->where('id', $service_cat_id)->first();
        if ($service_category == Null){
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $service_cat_id = $service_category->id;
        //$provider_id = $request->get('id');
        //$provider_service_id = $request->get('provider_service_id');
        $driver_details_id = $request->get('driver_details_id');

        if ($driver_details_id != Null){
            $transport_driver = TransportDriverDetails::query()->where('id', $driver_details_id)->first();
            if ($transport_driver == Null) {
                Session::flash('error', 'Driver details not found!');
                return redirect()->back();
            }
            $provider_services = ProviderServices::query()->where('id', $transport_driver->provider_service_id)->first();
            if ($provider_services == Null) {
                Session::flash('error', 'Driver service details not found!');
                return redirect()->back();
            }
            $provider_details = Provider::query()->where('id', $provider_services->provider_id)->first();
            if ($provider_details == Null) {
                Session::flash('error', 'Driver service details not found!');
                return redirect()->back();
            }
        }
        else {
            $provider_details = new Provider();
            $provider_details->verified_at = date('Y-m-d H:i:s');
            $provider_details->status = 1;
            $provider_details->password = Hash::make($request->get('pass'));
//            $provider_details->provider_type = 2;
            $provider_details->save();

            $provider_services = new ProviderServices();
            $provider_services->provider_id = $provider_details->id;
            $provider_services->service_cat_id = $service_cat_id;
            $provider_services->current_status = 1;
            $provider_services->status = 1;
            $provider_services->save();

            $transport_driver = new TransportDriverDetails();
            $transport_driver->provider_service_id = $provider_services->id;
            $transport_driver->total_request = 0;
            $transport_driver->total_completed = 0;
            $transport_driver->total_cancelled = 0;
            $transport_driver->rating = 0.00;
            $transport_driver->availability_ride_status = 1;
        }
        $provider_details->first_name = ucwords(trim($request->get('name')));
//        $provider_details->last_name = ucwords(trim($request->get('lname')));
        $provider_details->email = $request->get('email');
        $provider_details->contact_number = $request->get('contact_number');
        $provider_details->provider_type = 2;
        $get_country_code_arr = explode($request->get('contact_number'),$request->get('full_number'));
        $provider_details->country_code = isset($get_country_code_arr[0])?$get_country_code_arr[0]:+1;
        $provider_details->service_radius = $request->get('service_radius');
        $provider_details->gender = $request->get('gender');
//        $provider_details->default_pDetail = $request->get('payment_method');

        if ($request->file('avatar')) {
//            $type = "provider_avatar";
//            $exist_image = ($provider_details != Null && $provider_details->avatar != Null)? $provider_details->avatar : "";
//            $new_image = $request->file('avatar');
//            $image_upoad = $this->notificationClass->uploadCompanyWiseImage($type, $exist_image, $new_image);
//            $provider_details->avatar = $image_upoad;

            if (\File::exists(public_path('/assets/images/profile-images/provider/' . $provider_details->avatar))) {
            \File::delete(public_path('/assets/images/profile-images/provider/' . $provider_details->avatar));
            }
            $file = $request->file('avatar');
            $file_new = random_int(1, 99) . date('sihYdm') . random_int(1, 99) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . '/assets/images/profile-images/provider/', $file_new);
            $provider_details->avatar = $file_new;
        }
        $provider_details->save();

        $transport_driver->vehicle_type_id = $request->get('vehicle_type_id');
        $transport_driver->vehicle_company = $request->get('vehicle_company');
        $transport_driver->plat_no = $request->get('plat_no');
        $transport_driver->model_year = $request->get('model_year');
        $transport_driver->model_name = $request->get('model_name');
        $transport_driver->vehicle_color = $request->get('vehicle_color');

        $transport_driver->driver_service = $request->get('driver_service') == "on" ? 1 : 0;
        $transport_driver->delivery_boy = $request->get('delivery_boy') == "on" ? 1 : 0;
        $transport_driver->courier_boy = $request->get('courier_boy') == "on" ? 1 : 0;
        if($request->get('driver_service') == "on"){
            $transport_driver->rental_service = $request->get('rental_service') == "on" ? 1 : 0;
        }else{
            $transport_driver->rental_service = 0;
        }

        $transport_driver->child_seat = $request->get('child_seat') == "on" ? 1 : 0;
        $transport_driver->handicap = $request->get('handicap') == "on" ? 1 : 0;

        if ($service_category->id == $this->taxi_ride || $service_category->id == $this->taxi_rental) {
            $transport_driver->no_of_seat = $request->get('no_of_seat');
            $transport_driver->child_seat = $request->get('child_seat') == "on" ? 1 : 0;
            $transport_driver->handicap = $request->get('handicap') == "on" ? 1 : 0;
        }
        elseif ($service_category->id == $this->bike_ride || $service_category->id == $this->bike_rental) {
            $transport_driver->no_of_seat = 1;
            $transport_driver->child_seat =  0;
            $transport_driver->handicap =  0;
        }
        elseif ($service_category->id == $this->courier_service) {
//          $transport_driver->support_weight = $request->get('support_weight');
            $transport_driver->support_weight = 0;
            $transport_driver->child_seat =  0;
            $transport_driver->handicap =  0;
        }
        $transport_driver->save();


        $id = $request->get('id');
        if ($id != Null) {
            $msg = 'Provider update successfully!';
        } else {
            $msg = 'Provider add successfully!';
        }
        $status = "approved";
        Session::flash('success', $msg);
        if ($provider_services != Null) {
            if ($provider_services->status == 1) {
                $status = "approved";
                return redirect()->route('get:admin:transport_service_approved_providers_list');
            } elseif ($provider_services->status == 0) {
                $status = "un-approved";
                return redirect()->route('get:admin:transport_service_un_approved_providers_list');
            } elseif ($provider_services->status == 2) {
                $status = "blocked";
                return redirect()->route('get:admin:transport_service_blocked_providers_list');
            }
        }
        $slug = $service_category->slug;
        return redirect()->route('get:admin:transport_service_provider_list', [$slug, $status]);
    }

    public function getEditTransportDriver(Request $request, $driver_details_id)
    {
        //$service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $driver_detials = TransportDriverDetails::query()->select('service_category.slug as service_cat_slug',
            'service_category.id as service_cat_id',
            'service_category.category_type',
            'providers.id as provider_id', 'providers.first_name as name','providers.last_name as lname', 'providers.email', 'providers.contact_number','providers.country_code',
            'providers.avatar',
            'providers.login_type',
            'providers.service_radius', 'providers.gender',
            'provider_services.status', 'provider_services.id as provider_service_id',
            'transport_driver_details.id as driver_details_id', 'transport_driver_details.rating',
            'transport_driver_details.vehicle_type_id', 'transport_driver_details.vehicle_company',
            'transport_driver_details.plat_no', 'transport_driver_details.model_year', 'transport_driver_details.model_name',
            'transport_driver_details.vehicle_color', 'transport_driver_details.driver_service',
            'transport_driver_details.rental_service',
            'transport_driver_details.delivery_boy',
            'transport_driver_details.courier_boy', 'transport_driver_details.no_of_seat','transport_driver_details.handicap','transport_driver_details.child_seat',
            'transport_driver_details.support_weight',
            'transport_driver_details.store_id'
        )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('transport_driver_details.id', $driver_details_id)
            ->first();

        if ($driver_detials != Null) {
            if ($driver_detials->category_type == 5) {
//                if ($driver_detials->driver_service == 1) {
                if($driver_detials->service_cat_id == 4){
                    $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $driver_detials->service_cat_id)->whereNotIn('id',$this->transport_vehicle_id_array)->where('status', '=', 1)->get();
                }else{
                    $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $driver_detials->service_cat_id)->where('status', '=', 1)->get();
                }

//                } else {
//                    //$vehicle_types = TransportVehicleType::query()->whereIn('service_cat_id', [1, 4])->where('status', '=', 1)->get();
//                    $vehicle_types = TransportVehicleType::query()->whereIn('service_cat_id', [4])->whereNotIn('id',$this->transport_vehicle_id_array)->where('status', '=', 1)->get();
//
//                }
                //$vehicle_types = TransportVehicleType::query()->where('service_cat_id', $service_category->id)->where('status', '=', 1)->get();
            }
            elseif ($driver_detials->service_cat_id == $this->bike_rental) {
                $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $this->bike_ride)->where('status', '=', 1)->get();
            }
            elseif ($driver_detials->service_cat_id == $this->taxi_rental) {
                $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $this->taxi_ride)->where('status', '=', 1)->get();
            }
            else {
                $vehicle_types = TransportVehicleType::query()->where('service_cat_id', $driver_detials->service_cat_id)->where('status', '=', 1)->get();
            }

            $view = view('admin.pages.transport_services.providers.driver_form', compact( 'vehicle_types', 'driver_detials'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        }
        else {
            Session::flash('error', 'Driver Not Found!');
            return redirect()->back();
        }
    }

    public function getEditTransportHomePageDetails(Request $request,$slug){
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $transport_home_page = TransportHomePage::query()
            ->where('service_cat_id','=',$service_category->id)
            ->first();

        $language_lists = LanguageLists::query()->where('status','=','1')->get();
//        if ($transport_home_page == Null) {
//            Session::flash('error', 'Service detail not found!');
//            return redirect()->back();
//        }

        $view = view('admin.pages.transport_services.service_list.home_page_details', compact('transport_home_page','language_lists','slug','service_category'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponse($view);
        }
        return $view;
    }

    public function postUpdateTransportHomePageDetails(Request $request,$slug){
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

        $id = $request->get('id');

        if($id != Null){
            $transport_home_page = TransportHomePage::query()->where('id',$id)->where('service_cat_id','=',$service_category->id)->first();
        } else{
            $transport_home_page = new TransportHomePage();
            $transport_home_page->service_cat_id = $service_category->id;
        }
        $transport_home_page->save();

        $transport_home_page->description = $request->get('description');
        $transport_home_page->save();

        try{
            $language_list = LanguageLists::query()->select('language_name as name',
                DB::raw("(CASE WHEN language_code != 'en' THEN  concat(language_code,'_description') ELSE 'description' END) as service_col_description")
            )->where('status',1)->get();
            foreach ($language_list as $key => $language){
                if(Schema::hasColumn('transport_home_page',$language->service_col_description)) {
                    $transport_home_page->{$language->service_col_description} = $request->get($language->service_col_description);
                    $transport_home_page->save();
                }
            }
        } catch (\Exception $e) {}

        $transport_home_page->instruction = $request->get('instruction');
        $transport_home_page->save();

        try{
            $language_list = LanguageLists::query()->select('language_name as name',
                DB::raw("(CASE WHEN language_code != 'en' THEN  concat(language_code,'_instruction') ELSE 'instruction' END) as service_col_instruction")
            )->where('status',1)->get();
            foreach ($language_list as $key => $language){
                if(Schema::hasColumn('transport_home_page',$language->service_col_instruction)) {
                    $transport_home_page->{$language->service_col_instruction} = $request->get($language->service_col_instruction);
                    $transport_home_page->save();
                }
            }
        } catch (\Exception $e) {}

        if($request->hasFile('image') != Null){
            if (\File::exists(public_path('/assets/images/service-slider-banner/' . $transport_home_page->banner_image))) {
                \File::delete(public_path('/assets/images/service-slider-banner/' . $transport_home_page->banner_image));
            }
            $destinationPath = public_path('/assets/images/service-slider-banner/');
            $file = $request->file('image');
            $img = Image::make($file->getRealPath());
            $img->orientate();
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $img->resize(490, 200, function ($constraint) {
                //$constraint->aspectRatio();
            })->save($destinationPath . $file_new);
            $transport_home_page->banner_image = $file_new;

            /*$file = $request->file('image');
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $file_new);
            $transport_home_page->banner_image = $file_new;*/
        }
        $transport_home_page->save();

        Session::flash('success', 'Updated successfully!');
        return redirect()->route('get:admin:transport_home_page_details',$slug);
    }

    public function getEditTransportWeightList(Request $request,$slug){
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $transport_weight_list = TransportWeightList::query()
            ->where('service_cat_id','=',$service_category->id)
//            ->where('status','=',1)
            ->get();

        $view = view('admin.pages.transport_services.service_list.manage_weight_list', compact('transport_weight_list','slug','service_category'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponse($view);
        }
        return $view;
    }

    public function postUpdateTransportWeightList(Request $request,$slug){
        /*dd($request->all());*/
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

//        $transport_weight_list = TransportWeightList::query()->where('service_cat_id',$service_category->id)->delete();

        for ($i = 0; $i < count($request->get('start_limit')); $i++) {
            if( isset($request->get('weight_id')[$i])  && $request->get('weight_id')[$i]> 0)
            {
                $transport_weight_list = TransportWeightList::query()->where('id','=',$request->get('weight_id')[$i])->first();
            }else{
                $transport_weight_list = new TransportWeightList();
            }

            $transport_weight_list->service_cat_id = $service_category->id;
            $transport_weight_list->start_limit = $request->get('start_limit')[$i];
            $transport_weight_list->close_limit = $request->get('close_limit')[$i];
            $transport_weight_list->save();
            /*if ($request->get('start_limit')[$i] != null && $request->get('close_limit')[$i] != null){
                if ($request->get('close_limit')[$i] == 0){
                    $is_close = 1;
                }else{
                    $is_close = 0;
                }
                $transport_weight_list = new TransportWeightList();
                $transport_weight_list->service_cat_id = $service_category->id;
                $transport_weight_list->start_limit = $request->get('start_limit')[$i];
                $transport_weight_list->close_limit = $request->get('close_limit')[$i];
                $transport_weight_list->save();
                if ($is_close == 1){
                    break;
                }
            }*/
        }
        Session::flash('success', 'Updated successfully!');
        return redirect()->route('get:admin:transport_weight_list',$slug);
    }

    public function getEditTransportCategoryList(Request $request,$slug){
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $transport_category_list = TransportCategoryList::query()
            ->where('service_cat_id','=',$service_category->id)
//            ->where('status','=',1)
            ->get();

        $view = view('admin.pages.transport_services.service_list.manage_category_list', compact('transport_category_list','slug','service_category'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponse($view);
        }
        return $view;
    }

    public function postUpdateTransportCategoryList(Request $request,$slug){
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $transport_category_list = TransportCategoryList::query()->where('service_cat_id',$service_category->id)->delete();

        for ($i = 0; $i < count($request->get('category_name')); $i++) {
            if ($request->get('category_name')[$i] != null){
                $transport_category_list = new TransportCategoryList();
                $transport_category_list->service_cat_id = $service_category->id;
                $transport_category_list->category_name = $request->get('category_name')[$i];
                $transport_category_list->save();
            }
        }
        Session::flash('success', 'Updated successfully!');
        return redirect()->route('get:admin:transport_category_list',$slug);
    }

    //get Admin Store Manual Assign Driver
    public function getCourierManualAssignDriver(Request $request, $slug, $ride_id) {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $ride_details = TransportRideBook::query()->where('id','=', $ride_id)->first();
        if ($ride_details == Null) {
            Session::flash('error', 'Courier Details Not Found!');
            return redirect()->back();
        }
        $service_setting = ServiceSettings::query()->where('service_cat_id', $service_category->id)->first();
        if ($service_setting != Null) {
            $radius = $service_setting->provider_search_radius;
        } else {
            $radius = 10;
        }
        $latlong = array_map('trim', explode(",", $ride_details->pickup_latlong));
        $lat = isset($latlong[0])?$latlong[0]:0;
        $long = isset($latlong[1])?$latlong[1]:0;

        $expire_date_time = date('Y-m-d H:i:s', strtotime("-60 minutes"));

        if($ride_details->service_cat_id == 4)
        {

            $get_weight_data_ride = TransportCourierDetails::query()->select('user_courier_service_details.id','user_courier_service_details.ride_id',
                'user_courier_service_details.weight_id','user_courier_service_details.weight_start_limit','user_courier_service_details.weight_close_limit',
                'transport_vehicle_type.id as vehicle_req_id'
                )
                                    ->join('transport_weight_list','transport_weight_list.id','=','user_courier_service_details.weight_id')
                                    ->join('transport_vehicle_type','transport_vehicle_type.weight_limit','=','transport_weight_list.id')
                                    ->where('ride_id','=',$ride_details->id)->first();
            $vehicle_arr = [];
            if($get_weight_data_ride != Null){
                    $weight_id= $get_weight_data_ride->weight_id;
                    $weight_start_limit= $get_weight_data_ride->weight_start_limit;
                    $weight_close_limit= $get_weight_data_ride->weight_close_limit;
                    $vehicle_req_id= $get_weight_data_ride->vehicle_req_id;
                    if($vehicle_req_id == $this->courier_scooter){
                        $vehicle_type_list = TransportVehicleType::query()->where('service_cat_id', $this->bike_ride)->get()->pluck('id')->toArray();
                        $vehicle_arr =array_merge($vehicle_type_list,[$vehicle_req_id]) ;
                    }elseif($vehicle_req_id == $this->courier_car){
                        $vehicle_type_list = TransportVehicleType::query()->where('service_cat_id', $this->taxi_ride)->get()->pluck('id')->toArray();
                        $vehicle_arr =array_merge($vehicle_type_list,[$vehicle_req_id]) ;
                    }else{
                        $vehicle_arr = [$vehicle_req_id];
                    }
            }else{

                $weight_id = 0;
                $weight_start_limit = 0;
                $weight_close_limit = 0;
                $vehicle_req_id= 0;

            }

            $driver_list = Provider::query()->select('providers.id as driver_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'providers.contact_number')
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->whereIn('service_category.id', [1,2,4])
                ->where('providers.status', 1)
                //->where('providers.login_device', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
//                ->where('transport_driver_details.vehicle_type_id','=', $vehicle_req_id)
                ->whereIn('transport_driver_details.vehicle_type_id', $vehicle_arr)
                ->where('transport_driver_details.courier_boy', 1)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token')
                ->get();

        }else{
            $driver_list = Provider::query()->select('providers.id as driver_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'providers.contact_number')
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->whereIn('service_category.id', [1,2,4])
                ->where('providers.status', 1)
                //->where('providers.login_device', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
                ->where('transport_driver_details.courier_boy', 1)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token')
                ->get();
        }


        $view = view('admin.pages.other_services.manual_assign_driver.form', compact('slug', 'service_category', 'ride_id', 'driver_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //post Admin Store Manual Assign Driver
    public function postUpdateCourierManualAssignDriver(Request $request, $slug)
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
            $ride_id =  $request->get('ride_id');
            $ride_order = TransportRideBook::query()->where('id', $ride_id)->where('service_cat_id','=',$service_category->id)->first();
            if ($ride_order != Null) {
                if ($ride_order->status == 0 && $ride_order->driver_id == Null) {
                    $driver_details = TransportDriverDetails::query()->select('providers.device_token',
                        'providers.id as driver_id', 'transport_driver_details.id','transport_driver_details.delivery_boy',
                        DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'provider_services.id as provider_service_id', 'providers.login_device', 'providers.language')
                        ->join('provider_services', 'provider_services.id', 'transport_driver_details.provider_service_id')
                        ->join('providers', 'providers.id', 'provider_services.provider_id')
                        ->where('providers.id', $request->get('driver_id'))
                        ->where('providers.status', 1)
                        ->where('provider_services.status', 1)
                        ->where('provider_services.current_status', 1)
                        ->first();
                    if ($driver_details != Null) {
                        $user = User::query()->where('id', $ride_order->user_id)->first();
                        if ($user == Null) {
                            Session::flash('error', 'user not found!');
                            return redirect()->back();
                        }
                        $driver_id = $driver_details->driver_id;
                        $driver_service_id = $driver_details->provider_service_id;
                        $driver_details_id = $driver_details->id;
                        $driver_service = ProviderServices::query()->where('id', $driver_service_id)->first();
                        if ($driver_service != Null) {
                            if ($driver_details->delivery_boy != 1) {
                                $driver_service->current_status = 0;
                                $driver_service->save();
                            }

                            //set driver id in ride booking
                            $ride_order->driver_id = $driver_details_id;
                            $ride_order->status = 1;
                            $ride_order->save();

                            ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride_order->user_id)->where('service_cat_id', $ride_order->service_cat_id)->where('booking_id', $ride_order->id)->delete();
                            $provider_running_service = new ProviderUserRunningService();
                            $provider_running_service->provider_id = $driver_id;
                            $provider_running_service->user_id = $ride_order->user_id;
                            $provider_running_service->service_cat_id = $ride_order->service_cat_id;
                            $provider_running_service->booking_id = $ride_order->id;
                            $provider_running_service->save();

                            //send user assign driver notification
                            $log  = $this->notificationClass->userTransportNotification($ride_id, $user->device_token, 1, $user->login_device,$user->language);
                            //send driver assign driver notification
                            $driver_assign_check = $this->notificationClass->driverManualAssignTransportRequestNotification($ride_order->id, $driver_details->device_token, $driver_details->login_device, $driver_details->language);

                            Session::flash('success', 'Driver assign successfully!');

                            $id= $ride_id;
                            return redirect()->route('get:admin:transport_ride_details', [$slug, $id]);
                        } else {
                            Session::flash('error', 'Driver service not found!');
                            return redirect()->back();
                        }
                    } else {
                        Session::flash('error', 'Sorry, Driver not found!');
                        return redirect()->back();
                    }
                } else {
                    //0=pending, 1=accepted, 2=schedule-accepted, 3=arrived, 4=cancelled, 5=running, 6=drop, 7=payment, 8=rating, 9=completed, 10=failed
                    if (in_array($ride_order->status,[1,2])) {
                        Session::flash('error', 'Sorry, Ride is already approved by driver!');
                    } elseif ($ride_order->status == 4) {
                        if ( trim(strtolower($ride_order->cancel_by)) == "user"){
                            $cancel_by = "user";
                        } else {
                            $cancel_by = "admin";
                        }
                        Session::flash('error', 'Sorry, Ride is cancelled by '.$cancel_by.'!');
                    } elseif (in_array($ride_order->status,[3,5,6,7,8])) {
                        Session::flash('error', 'Sorry, Ride is already assign by driver!');
                    } elseif ($ride_order->status == 9) {
                        Session::flash('error', 'Sorry, Ride is already is completed!');
                    } elseif ($ride_order->status == 10) {
                        Session::flash('error', 'Sorry, Ride request is failed!');
                    }
                    return redirect()->back();
                }
            } else {
                Session::flash('error', 'Ride not found!');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Session::flash('error', 'Something went wrong');
            return redirect()->back();
        }
    }

    public function getTransportReAssignDriver(Request $request, $slug, $ride_id)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $ride_details = TransportRideBook::query()->where('id', $ride_id)->first();
        if ($ride_details == Null) {
            Session::flash('error', 'Ride Details Not Found!');
            return redirect()->back();
        }
        //0=pending, 1=accepted, 2=schedule-accepted, 3=arrived, 4=cancelled, 5=running, 6=drop, 7=payment, 8=rating, 9=completed, 10=failed
        if (!in_array($ride_details->status, [1, 2, 3, 5])) {
            Session::flash('error', 'Ride is not able to driver re-assign!');
            return redirect()->back();
        }
        $service_setting = ServiceSettings::query()->where('service_cat_id', $service_category->id)->first();
        if ($service_setting != Null) {
            $radius = $service_setting->provider_search_radius;
        } else {
            $radius = 10;
        }
        $driver_id_list = [];
        if ($ride_details->driver_id != Null) {
            $general_settings = $request->get("general_settings");
            if (($general_settings != Null) && in_array($ride_details->status, [1, 2, 3, 5])) {
                if (($general_settings->max_driver_reassign > $ride_details->is_driver_reassign)) {
                    $reassigned_driver_list = ReassignedDriverList::query()->where("service_cat_id", "=", $ride_details->service_cat_id)->where("ride_id", "=", $ride_details->id)->pluck("driver_details_id")->toArray();
                    $old_driver_id = [$ride_details->driver_id];
                    $driver_id_list = array_merge($reassigned_driver_list, $old_driver_id);
                } else {
                    Session::flash('error', 'Ride re-assign driver max limit over!');
                    return redirect()->back();
                }
            } else {
                Session::flash('error', 'Ride not able to re-assign driver!');
                return redirect()->back();
            }
        } else {
            Session::flash('error', 'Ride not able to re-assign driver!');
            return redirect()->back();
        }
        $latlong = array_map('trim', explode(",", $ride_details->pickup_latlong));
        if (!isset($latlong[0]) || !isset($latlong[1])) {
            Session::flash('error', 'Ride Details Not Found!');
            return redirect()->back();
        }
        $lat = isset($latlong[0]) ? $latlong[0] : 0;
        $long = isset($latlong[1]) ? $latlong[1] : 0;

        $expire_date_time = date('Y-m-d H:i:s', strtotime("-60 minutes"));

        if($ride_details->service_cat_id == 4)
        {

            $get_weight_data_ride = TransportCourierDetails::query()->select('user_courier_service_details.id','user_courier_service_details.ride_id',
                'user_courier_service_details.weight_id','user_courier_service_details.weight_start_limit','user_courier_service_details.weight_close_limit',
                'transport_vehicle_type.id as vehicle_req_id'
            )
                ->join('transport_weight_list','transport_weight_list.id','=','user_courier_service_details.weight_id')
                ->join('transport_vehicle_type','transport_vehicle_type.weight_limit','=','transport_weight_list.id')
                ->where('ride_id','=',$ride_details->id)->first();
            $vehicle_arr = [];
            if($get_weight_data_ride != Null){
                $weight_id= $get_weight_data_ride->weight_id;
                $weight_start_limit= $get_weight_data_ride->weight_start_limit;
                $weight_close_limit= $get_weight_data_ride->weight_close_limit;
                $vehicle_req_id= $get_weight_data_ride->vehicle_req_id;
                if($vehicle_req_id == $this->courier_scooter){
                    $vehicle_type_list = TransportVehicleType::query()->where('service_cat_id', $this->bike_ride)->get()->pluck('id')->toArray();
                    $vehicle_arr =array_merge($vehicle_type_list,[$vehicle_req_id]) ;
                }elseif($vehicle_req_id == $this->courier_car){
                    $vehicle_type_list = TransportVehicleType::query()->where('service_cat_id', $this->taxi_ride)->get()->pluck('id')->toArray();
                    $vehicle_arr =array_merge($vehicle_type_list,[$vehicle_req_id]) ;
                }else{
                    $vehicle_arr = [$vehicle_req_id];
                }
            }else{

                $weight_id = 0;
                $weight_start_limit = 0;
                $weight_close_limit = 0;
                $vehicle_req_id= 0;

            }

            $driver_list = Provider::query()->select('providers.id as driver_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'providers.contact_number')
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->whereIn('service_category.id', [1,2,4])
                ->where('providers.status', 1)
                //->where('providers.login_device', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
//                ->where('transport_driver_details.vehicle_type_id','=', $vehicle_req_id)
                ->whereIn('transport_driver_details.vehicle_type_id', $vehicle_arr)
                ->where('transport_driver_details.courier_boy', 1)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token')
                ->get();

        }else{
                $driver_list = Provider::query()->select('providers.id as driver_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'providers.contact_number')
                    ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                    ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                    ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                    ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                    ->whereIn('service_category.id', [$this->bike_ride, $this->taxi_ride, $this->courier_service])
                    ->where('providers.status', 1)
                    //->where('providers.login_device', 1)
                    ->where('provider_services.status', 1)
                    ->where('provider_services.current_status', 1);
                if ($ride_details->service_cat_id == $this->courier_service) {
                    $driver_list->where('transport_driver_details.courier_boy', 1);
                } else {
                    $driver_list->where('transport_driver_details.driver_service', 1);
                }
                $driver_list = $driver_list->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                    ->whereNotNull('transport_driver_details.current_lat')
                    ->whereNotNull('transport_driver_details.current_long')
                    ->whereNotNull('providers.access_token')
                    ->whereNotNull('providers.device_token')
                    ->whereNotIn("transport_driver_details.id", $driver_id_list)
                    ->get();
        }
        //dd($driver_list);
        $view = view('admin.pages.transport_services.re_assign_driver.form', compact('slug', 'service_category', 'ride_id', 'driver_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function postUpdateTransportReAssignDriver(Request $request, $slug)
    {
        $redirect_type = $request->get("redirect_type");
        if($this->is_restricted == 1) {
            Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
            return redirect()->back();
        }
        try {
            $service_category = ServiceCategory::query()->where('slug', $slug)->first();
            if ($service_category == Null) {
                Session::flash('error', 'Service category not found!');
                return redirect()->back();
            }
            $ride_details = TransportRideBook::query()->where('id', $request->get("ride_id"))->first();
            if ($ride_details != Null) {
                if (in_array($ride_details->status, [1,2,3,5])) {
                    if ($ride_details->driver_id == Null) {
                        Session::flash('error', 'Ride not able to re-assign driver!');
                        return redirect()->back();
                    }
                    //0=pending, 1=accepted, 2=schedule-accepted, 3=arrived, 4=cancelled, 5=running, 6=drop, 7=payment, 8=rating, 9=completed, 10=failed
                    $user = User::query()->where('id', '=', $ride_details->user_id)->first();
                    if ($user == Null) {
                        Session::flash('error', 'user not found!');
                        return redirect()->back();
                    }
                    $old_driver_details_id = $ride_details->driver_id;
                    $driver_details = TransportDriverDetails::query()
                        ->select('providers.device_token', 'providers.id as driver_id', 'transport_driver_details.id',
                            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"),
                            'provider_services.id as provider_service_id', 'providers.login_device', 'providers.language')
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

                            if ($ride_details->driver_id != Null) {
                                $general_settings = $request->get("general_settings");
                                if (($general_settings != Null) && in_array($ride_details->status,[1,2,3,5])) {
                                    if(($general_settings->max_driver_reassign > $ride_details->is_driver_reassign)){
                                        $old_driver_details = TransportDriverDetails::query()->select('providers.device_token', 'providers.id as driver_id', 'transport_driver_details.id',
                                            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'provider_services.id as provider_service_id', 'providers.login_device', 'providers.country_code', 'providers.contact_number', 'providers.language')
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
                                            ->where("service_cat_id","=", $ride_details->service_cat_id)
                                            ->where("ride_id","=",$ride_details->id)
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
                                        Session::flash('error', 'Ride re-assign driver max limit over!');
                                        return redirect()->back();
                                    }
                                } else {
                                    Session::flash('error', 'Ride not able to re-assign driver!');
                                    return redirect()->back();
                                }
                            } else {
                                Session::flash('error', 'Ride not able to re-assign driver!');
                                return redirect()->back();
                            }

                            $driver_service->current_status = 0;
                            $driver_service->save();

                            $ride_details->driver_id = $driver_details_id;
                            $ride_details->driver_name = $driver_details->driver_name;
                            $ride_details->save();


                            ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride_details->user_id)->where('service_cat_id', $ride_details->service_cat_id)->where('booking_id', $ride_details->id)->delete();
                            $provider_running_service = new ProviderUserRunningService();
                            $provider_running_service->provider_id = $driver_id;
                            $provider_running_service->user_id = $ride_details->user_id;
                            $provider_running_service->service_cat_id = $ride_details->service_cat_id;
                            $provider_running_service->booking_id = $ride_details->id;
                            $provider_running_service->save();

                            //if ($is_re_assign == 0) {
                            //    $this->notificationClass->userTransportNotification($ride_details->id, $user->device_token, 1, $user->login_device);
                            //}
                            //send driver assign driver notification
                            $driver_assign_check = $this->notificationClass->driverManualAssignTransportRequestNotification($ride_details->id, $driver_details->device_token, $driver_details->login_device, $driver_details->language);

                            if ($is_re_assign == 1){
                                ProviderUserRunningService::query()->where('provider_id', $old_driver_id)->where('user_id', $ride_details->user_id)->where('service_cat_id', $ride_details->service_cat_id)->where('booking_id', $ride_details->id)->delete();
                                if ($old_driver_service != Null){
                                    $old_driver_service->current_status = 1;
                                    $old_driver_service->save();
                                }
                                $ride_details->is_driver_reassign = $ride_details->is_driver_reassign + 1;
                                $ride_details->save();

                                if ($old_driver_details != Null){
                                    $reassigned_driver_detail = ReassignedDriverList::query()
                                        ->where("service_cat_id","=", $ride_details->service_cat_id)
                                        ->where("ride_id","=",$ride_details->id)
                                        ->where("driver_details_id","=", $old_driver_details_id)
                                        ->first();
                                    if ($reassigned_driver_detail == Null){
                                        $reassigned_driver_detail = new ReassignedDriverList();
                                    }
                                    $reassigned_driver_detail->service_cat_id = $ride_details->service_cat_id;
                                    $reassigned_driver_detail->ride_id = $ride_details->id;
                                    $reassigned_driver_detail->driver_details_id = $old_driver_details->id;
                                    $reassigned_driver_detail->driver_name = $old_driver_details->driver_name;
                                    $reassigned_driver_detail->contact_number = $old_driver_details->country_code."".$old_driver_details->contact_number;
                                    $reassigned_driver_detail->save();

                                    $is_reassign = $ride_details->is_driver_reassign - 0;
                                    $old_driver_assign_check = $this->notificationClass->driverManualAssignTransportRequestNotification($ride_details->id, $old_driver_details->device_token, $old_driver_details->login_device, $old_driver_details->language, $is_reassign);
                                }
                            }
                            Session::flash('success', 'Driver re-assign successfully!');
                            return redirect()->route('get:admin:transport_ride_details', [ "slug" => $slug, "id" => $ride_details->id]);
                        } else {
                            Session::flash('error', 'driver service not found!');
                            return redirect()->back();
                        }
                    } else {
                        Session::flash('error', 'Sorry, driver not found!');
                        return redirect()->back();
                    }
                } else {
                    //0=pending, 1=accepted, 2=schedule-accepted, 3=arrived, 4=cancelled, 5=running, 6=drop, 7=payment, 8=rating, 9=completed, 10=failed
                    if ($ride_details->status == 0) {
                        Session::flash('error', 'Sorry, Ride status is pending, first ride assign driver then after you can re-assign driver!');
                    } elseif ($ride_details->status == 9) {
                        Session::flash('error', 'Sorry, Ride is already completed!');
                    } elseif ($ride_details->status == 4) {
                        if ( trim(strtolower($ride_details->cancel_by)) == "user"){
                            $cancel_by = "user";
                        } else {
                            $cancel_by = "admin";
                        }
                        Session::flash('error', 'Sorry, Ride request is cancelled by '.$cancel_by.'!');
                    } elseif (in_array($ride_details->status, [6,7,8])) {
                        Session::flash('error', 'Sorry, Ride is already droped!');
                    } elseif ($ride_details->status == 10) {
                        Session::flash('error', 'Sorry, Ride is already failed!');
                    }
                    return redirect()->back();
                }
            } else {
                Session::flash('error', 'Ride not found!');
                return redirect()->back();
            }
        }
        catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            return redirect()->back();
        }
    }
}
