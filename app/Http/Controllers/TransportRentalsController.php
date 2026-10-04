<?php

namespace App\Http\Controllers;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Http\Requests\TransportVehicleTypeRequest;
use App\model\ProviderUserRunningService;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\TransportDriverDetails;
use App\model\TransportRatings;
use App\model\TransportRentalRideBooking;
use App\model\TransportRideBook;
use App\model\TransportVehicleType;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserProductBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TransportRentalsController extends Controller
{

    private $adminClass;
    private $notificationClass;
    private $bike_ride;
    private $taxi_ride;
    private $bike_rental;
    private $taxi_rental;
    private $courier_service;
    private $bike_rental_id_array = [1,31];
    private $car_rental_id_array= [2,32];
    private $is_restricted = 0;
    private $transport_service_id_array;
    private $courier_service_id_array;
    private $delivery_service_id_array;
    private $transport_vehicle_id_array;
    private $all = [0, 4, 9, 10];
    private $available = [0, 4, 9, 10];
    private $ride_start = [5, 6, 7, 8];
    private $reached = [3];
    private $enroute = [1, 2,];

    public function __construct(AdminClass $adminClass, NotificationClass $notificationClass)
    {
        $this->bike_ride = 1;
        $this->taxi_ride = 2;
        $this->courier_service = 4;
        $this->bike_rental = 31;
        $this->taxi_rental = 32;
        $this->transport_service_id_array = [1, 2];
        $this->courier_service_id_array = [4];
        $this->delivery_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->transport_vehicle_id_array =  [10, 14];
        $this->adminClass = $adminClass;
        $this->notificationClass = $notificationClass;

        $this->middleware( function ($request, $next) {
            $is_restrict_admin = $request->get('is_restrict_admin');
            $this->is_restricted = $is_restrict_admin;
            return $next($request);
        });
    }

    //transport service dashboard
    public function getTransportRentalServiceDashboard($slug, Request $request)
    {

        $service_category = ServiceCategory::query()->select('id', 'name', 'category_type', 'slug')->where('slug', $slug)->first();

        $date = date('Y-m-d', strtotime('-7 days'));

        if ($slug == "bike-rental") {
            $cat_array = $this->bike_rental_id_array;
        }else{
            $cat_array = $this->car_rental_id_array;
        }

        $approved_drivers = TransportDriverDetails::query()
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('providers.status', '=', 1)
            ->whereIn('service_category.id', $cat_array)
            ->where('transport_driver_details.rental_service', '=', 1)
            ->where('provider_services.status', '=', 1)
            ->count();


        $active_drivers = TransportDriverDetails::query()->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('providers.status', '=', 1)
            ->whereIn('service_category.id', $cat_array)
            ->where('transport_driver_details.rental_service', '=', 1)
            ->where('provider_services.status', '=', 1)
            ->where('provider_services.current_status', '=', 1)
            ->count();

        $un_approved_drivers = TransportDriverDetails::query()->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('providers.status', '=', 0)
            ->whereIn('service_category.id', $cat_array)
            ->where('transport_driver_details.rental_service', '=', 1)
            ->where('provider_services.status', '=', 0)
            ->count();

        $admin_commission = TransportRentalRideBooking::query()->selectRaw('
             SUM(total_pay) - SUM(driver_amount) as admin_commission')
            ->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')

//                ->whereIn('service_category.slug', [$slug, 'courier-service'])
            ->whereIn('service_category.id', $cat_array)
            ->where('user_rental_ride_booking.status', 9)
            ->groupBy('user_rental_ride_booking.service_cat_id')
            ->first();


        $total_ride = TransportRentalRideBooking::query()
            ->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')

            //->whereIn('service_category.slug', [$slug, 'courier-service'])
            ->whereIn('service_category.id', $cat_array)
            ->where('user_rental_ride_booking.pickup_date_time', '>=', $date)
            ->groupBy('user_rental_ride_booking.service_cat_id')
            ->count();


        $total_completed_ride = TransportRentalRideBooking::query()->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')
            ->whereIn('service_category.id', $cat_array)
            ->where('user_rental_ride_booking.pickup_date_time', '>=', $date)
            ->where('user_rental_ride_booking.status', 9)
            ->groupBy('user_rental_ride_booking.service_cat_id')
            ->count();

        $total_running_ride = TransportRentalRideBooking::query()->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')
            ->whereIn('service_category.id', $cat_array)
            ->where('user_rental_ride_booking.pickup_date_time', '>=', $date)
            ->where('user_rental_ride_booking.status', 5)
            ->groupBy('user_rental_ride_booking.service_cat_id')
            ->count();

        $total_cancelled_ride = TransportRentalRideBooking::query()->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')
            ->whereIn('service_category.id', $cat_array)
            ->where('user_rental_ride_booking.pickup_date_time', '>=', $date)
            ->where('user_rental_ride_booking.status', 4)
            ->groupBy('user_rental_ride_booking.service_cat_id')
            ->count();



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
        }
        elseif ($n > 1000000000) {
            $total_admin_earning = round(($n / 1000000000), 2) . ' B';
        }
        elseif ($n > 1000000) {
            $total_admin_earning = round(($n / 1000000), 2) . ' M';
        }
        elseif ($n > 1000) {
            $total_admin_earning = round(($n / 1000), 2) . ' K';
        }
        else {
            $total_admin_earning = number_format($total_earning, 2);
        }

        if ($service_category != Null) {
            $view = view('admin.pages.super_admin.service_dashboard.transport_rental_dashboard', compact('service_category', 'slug', 'approved_drivers', 'active_drivers', 'un_approved_drivers', 'total_admin_earning', 'total_ride', 'total_completed_ride', 'total_running_ride', 'total_cancelled_ride'));
        } else {
            $view = view('admin.pages.super_admin.service_dashboard.transport_rental_dashboard', compact('slug', 'approved_drivers', 'active_drivers', 'un_approved_drivers', 'total_admin_earning', 'total_ride', 'total_completed_ride', 'total_running_ride', 'total_cancelled_ride'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Transport Rental Service Ride List
    public function getTransportRentalServiceRideList(Request $request, $slug, $status_check) {

        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null && $status_check == Null) {
            Session::flash('error', 'Ride list not found!');
            return redirect()->back();
        }
        if ($status_check == "all") {
            $ride_status = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            $status = "all";
        }
        elseif ($status_check == "approved") {
            $ride_status = [1];
            $status = "approved";
        }
        elseif ($status_check == "pending") {
            $ride_status = [0];
            $status = "pending";
        }
        elseif ($status_check == "cancelled") {
            $ride_status = [2, 10];
            $status = "cancelled";
        }
        else {
            $ride_status = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            $status = "all";
        }

        $rental_ride_list = TransportRentalRideBooking::query()
            ->select('user_rental_ride_booking.id',
                'user_rental_ride_booking.ride_no',
                'user_rental_ride_booking.service_cat_id',
                'user_rental_ride_booking.total_pay',
                'user_rental_ride_booking.status',
                'user_rental_ride_booking.pickup_address',
                'user_rental_ride_booking.drop_address',
                'user_rental_ride_booking.user_name',
                'user_rental_ride_booking.driver_name',
                'transport_vehicle_type.name as vehicle_name'
            )
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_rental_ride_booking.vehicle_type_id')
            ->orderBy('user_rental_ride_booking.pickup_date_time', 'desc')
            ->where('user_rental_ride_booking.service_cat_id', '=', $service_category->id)
            ->whereIn('user_rental_ride_booking.status', $ride_status)
            ->get()->toArray();
        //$view = view('admin.pages.rental_services.transport_rental_ride_list', compact('transport_services', 'courier_service', 'delivery_service', 'slug', 'status', 'ride_lists', 'service_category','status_check'));
        $view = view('admin.pages.rental_services.transport_rental_ride_list', compact('service_category','slug', 'status', 'rental_ride_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getAdminTransportRentalServiceRideList(Request $request) {
        $status_check = $request->get('status_check');
        $slug = $request->get('slug');
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null && $status_check == Null) {
            Session::flash('error', 'Ride list not found!');
            return redirect()->back();
        }
        if ($status_check == "all") {
            $ride_status = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            $status = "all";
        }
        elseif ($status_check == "approved") {
            $ride_status = [1];
            $status = "scheduled";
        }
        elseif ($status_check == "pending") {
            $ride_status = [0];
            $status = "pending";
        }
        elseif ($status_check == "cancelled") {
            $ride_status = [2, 10];
            $status = "cancelled";
        }
        else {
            $ride_status = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            $status = "all";
        }


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


        $rental_ride_list = TransportRentalRideBooking::query()
            ->select('user_rental_ride_booking.id',
                'user_rental_ride_booking.ride_no',
                'user_rental_ride_booking.service_cat_id',
                'user_rental_ride_booking.total_pay',
                'user_rental_ride_booking.status',
                'user_rental_ride_booking.pickup_address',
                'user_rental_ride_booking.drop_address',
                'user_rental_ride_booking.user_name',
                'user_rental_ride_booking.driver_name',
                'transport_vehicle_type.name as vehicle_name'
            )
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_rental_ride_booking.vehicle_type_id')
            ->orderBy('user_rental_ride_booking.pickup_date_time', 'desc')
            ->where('user_rental_ride_booking.service_cat_id', '=', $service_category->id)
            ->whereIn('user_rental_ride_booking.status', $ride_status);
            //->get()->toArray();

        $get_ride_list_data = $rental_ride_list;
        $totalRecords = $rental_ride_list->count();
        if($searchValue != ""){
            $totalRecordswithFilter = $rental_ride_list->where(function ($query) use ($searchValue) {
                $query->orWhere('user_rental_ride_booking.ride_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('user_rental_ride_booking.user_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('user_rental_ride_booking.driver_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('user_rental_ride_booking.total_pay', 'like', '%' . $searchValue . '%');
            })->count();
        } else {
            $totalRecordswithFilter = $rental_ride_list->count();
        }
        $record = $get_ride_list_data;
        if($searchValue != ""){
            $record = $record->where(function($query) use ($searchValue){
                $query->orWhere('user_rental_ride_booking.ride_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('user_rental_ride_booking.user_name', 'like', '%' .$searchValue . '%')
                    ->orWhere('user_rental_ride_booking.driver_name', 'like', '%' .$searchValue . '%')
                    ->orWhere('user_rental_ride_booking.total_pay', 'like', '%' .$searchValue . '%');
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
            } else if($record->status == 1){
                $ride_status = "approved";
            } else if($record->status == 2){
                $ride_status = "cancelled";
            } else if($record->status == 3 || $record->status == 4 || $record->status == 5 || $record->status == 6 || $record->status == 7 || $record->status == 8){
                $ride_status = "running";
            } else if($record->status == 9){
                $ride_status = "completed";
            } else if($record->status == 10){
                $ride_status = "failed";
            } else {
                $ride_status = "";
            }


            $ride_status_html = '<span class="'.$ride_status.'">'.$ride_status.'</span>';


            $ride_details_html = '<a href="'.route('get:admin:transport_ride_details',[ $slug, $record->id]).'" class="render_link">
                                    <span class="icon-list-demo">
                                        <i class="fa fa-info-circle text-c-green" data-toggle="tooltip" data-placement="top" title="Ride Details"></i>
                                    </span>
                                   </a>';
            $address = '<i class="fa fa-map-marker text-success"></i>'.$record->pickup_address.'<br>
                        <i class="fa fa-map-marker text-danger"></i>'. $record->drop_address;
            $data_arr[] = array(
                "no" => $i,
                "ride_no" => $record->ride_no,
                "customer_name" => ($record->user_name != Null ? $record->user_name : "--" ),
                "driver_name" => ($record->driver_name != Null ? $record->driver_name : "--" ),
                "vehicle_type" => ($record->vehicle_name != Null ? $record->vehicle_name : "--" ),
                "total_cost" => '<span class="currency"></span>'.$record->total_pay,
                'status' =>$ride_status_html,
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

    public function getTransportRideList(Request $request){
        $status_check = $request->get('status_check');
        $slug = $request->get('slug');
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
            ->orderBy('user_ride_booking.pickup_datetime', 'desc');
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

    //get Transport Provider Ride review List
    public function getTransportProviderRideReviewList($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $service_cat_id = $service_category->id;
        $provider_reviews = [];
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
        }
        if ($request->ajax()) {
            $view = view('admin.pages.rental_services.transport_provider_review_list', compact('slug', 'service_category', 'provider_reviews'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.rental_services.transport_provider_review_list', compact('slug', 'service_category', 'provider_reviews'));
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


    //code for load rental view
    public function getTransportRentalEarningReport($slug, Request $request)
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
        $user_list = TransportRentalRideBooking::query()->select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_rental_ride_booking.user_id')
            ->where('user_rental_ride_booking.status', '=', 9)
            ->where('user_rental_ride_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_rental_ride_booking.user_id')
            ->get();
        $driver_list = TransportRentalRideBooking::query()->select('transport_driver_details.id',
            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"),
            'providers.contact_number',
            'user_rental_ride_booking.driver_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_rental_ride_booking.driver_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_rental_ride_booking.status', '=', 9)
            ->where('user_rental_ride_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_rental_ride_booking.driver_id')
            ->get();
        $from_date = Null;
        $to_date = Null;
        $driver = Null;
        $user = Null;
        $payment_type = Null;
        $driver_pay_type = Null;
        $view = view('admin.pages.rental_services.transport_rental_earning_reports', compact('slug', 'from_date', 'to_date', 'driver', 'user', 'payment_type', 'driver_pay_type', 'user_list', 'driver_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;

    }

    public function postTransportRentalDriverPaymentSettled(Request $request, $slug)
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
                $pay_store = TransportRentalRideBooking::query()->where('id', $pay)->first();
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
//        return redirect()->route('get:admin:transport_rental_earning_report', $slug);

    }

    public function PostTransportRentalSearchEarningReport($slug, Request $request)
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

        $user_list = TransportRentalRideBooking::query()->select('users.id',
            'users.first_name',
            'users.last_name',
            'users.contact_number')
            ->join('users', 'users.id', '=', 'user_rental_ride_booking.user_id')
            ->where('user_rental_ride_booking.status', '=', 9)
            ->where('user_rental_ride_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_rental_ride_booking.user_id')
            ->get();
        $driver_list = TransportRentalRideBooking::query()->select('transport_driver_details.id',
            DB::raw("providers.first_name as name"),
            'providers.contact_number',
            'user_rental_ride_booking.driver_id')
            ->join('transport_driver_details', 'transport_driver_details.id', '=', 'user_rental_ride_booking.driver_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_rental_ride_booking.status', '=', 9)
            ->where('user_rental_ride_booking.service_cat_id', '=', $service_category->id)
            ->groupBy('user_rental_ride_booking.driver_id')
            ->get();

        $from_date = Null;
        $to_date = Null;
        $driver = Null;
        $user = Null;
        $payment_type = Null;
        $driver_pay_type = Null;

        $admin_report_details = TransportRentalRideBooking::query()->select(
            'user_rental_ride_booking.id',
            'user_rental_ride_booking.ride_no',
            'user_rental_ride_booking.driver_pay_settle_status',
            'user_rental_ride_booking.pickup_date_time as pickup_datetime',
            'user_rental_ride_booking.user_name',
            'user_rental_ride_booking.driver_name',
            'user_rental_ride_booking.ride_fare as base_fare',
            //'user_ride_booking.discount_amount',
            'user_rental_ride_booking.tax',
            'user_rental_ride_booking.status',
            'user_rental_ride_booking.payment_type',
            'user_rental_ride_booking.total_pay',
            'user_rental_ride_booking.driver_amount',
            'user_rental_ride_booking.admin_commission',
            'user_rental_ride_booking.service_cat_id',
            'user_rental_ride_booking.promo_code'
        )
            ->where('user_rental_ride_booking.status', '=', 9)
            ->where('user_rental_ride_booking.service_cat_id', '=', $service_category->id);

        if ($request->get('from_date') != Null && $request->get('to_date') != Null) {
            $from_date = $request->get('from_date');
            $to_date = $request->get('to_date');
//            $admin_report_details->whereBetween('user_rental_ride_booking.pickup_date_time', [$from_date . " 00:00:00", $to_date . " 23:59:59"]);

            $from = Date('Y-m-d', strtotime($from_date)) . " 00:00:00";
            $to = Date('Y-m-d', strtotime($to_date)) . " 23:59:59";
            $admin_report_details->whereDate('user_rental_ride_booking.pickup_date_time', '>=', $from);
            $admin_report_details->whereDate('user_rental_ride_booking.pickup_date_time', '<=', $to);
        }
        if ($request->get('driver') != Null) {
            $driver = $request->get('driver');
            $admin_report_details->where('user_rental_ride_booking.driver_id', '=', $request->get('driver'));
        }
        if ($request->get('user') != Null) {
            $user = $request->get('user');
            $admin_report_details->where('user_rental_ride_booking.user_id', '=', $request->get('user'));
        }
        if ($request->get('payment_type') != Null) {
            //cash & card
            $payment_type = $request->get('payment_type');
            //if ($payment_type != 2) {
            $admin_report_details->where('user_rental_ride_booking.payment_type', '=', $request->get('payment_type'));
            //}
        }
        if ($request->get('driver_pay_type') != Null) {
            $driver_pay_type = $request->get('driver_pay_type');
            if ($driver_pay_type != 2) {
                $admin_report_details->where('user_rental_ride_booking.driver_pay_settle_status', '=', $request->get('driver_pay_type'));
            }
        }
        $payment_reports = $admin_report_details->orderBy("user_rental_ride_booking.id", "desc")
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

                if (in_array($report->payment_type, [2, 3])) {
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

            $view = view('admin.pages.rental_services.transport_rental_earning_reports', compact('slug',
                'from_date',
                'to_date',
                'driver',
                'user',
                'payment_type',
                'driver_pay_type',
                'user_list',
                'driver_list',
                'payment_reports',
                'used_promocode_amount', 'driver_commission', 'collect_payment',
                'total_ride', 'total_site_earning', 'total_driver_earning', 'total_discount', 'total_pay_driver', 'total_collect_payment', 'total_driver_outstanding_amount'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        } else {
            $view = view('admin.pages.rental_services.transport_rental_earning_reports', compact('slug', 'from_date', 'to_date', 'driver', 'user', 'payment_type', 'driver_pay_type', 'user_list', 'driver_list'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        }
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
            $view = view('admin.pages.rental_services.vehicle_type.manage_vehicle_type', compact('slug', 'service_category', 'vehicle_type_lists'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.rental_services.vehicle_type.manage_vehicle_type', compact('slug', 'service_category', 'vehicle_type_lists'));
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
            $view = view('admin.pages.rental_services.vehicle_type.manage_vehicle_type', compact('vehicle_type_lists'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.rental_services.vehicle_type.manage_vehicle_type', compact('vehicle_type_lists'));
    }

    //get Add Transport Vehicle Type
    public function getAddTransportVehicleType($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Required document`s service category not found!');
            return redirect()->back();
        }
        if ($request->ajax()) {
            $view = view('admin.pages.rental_services.vehicle_type.form_vehicle_type', compact('slug', 'service_category'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.rental_services.vehicle_type.form_vehicle_type', compact('slug', 'service_category'));
    }

    //get Edit Transport Vehicle Type
    public function getEditTransportVehicleType($slug, $id, Request $request)
    {
        $transport_vehicle_type = TransportVehicleType::query()->where('id', $id)->first();
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        if ($transport_vehicle_type != Null) {
            $view = view('admin.pages.rental_services.vehicle_type.form_vehicle_type', compact('slug', 'transport_vehicle_type', 'service_category'));
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
        $sevice_category = ServiceCategory::query()->where('id', $request->get('service_cat_id'))->first();
        return redirect()->route('get:admin:transport_rental_vehicle_type', [$sevice_category->slug]);
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

    //get Transport Service Category Site Setting
    public function getTransportServiceSetting($slug, Request $request)
    {
        $service_category = ServiceCategory::query()->where('slug', $slug)->first();
        $service_settings = ServiceSettings::query()->where('service_cat_id', $service_category->id)->first();
        if ($service_category != Null) {
            $view = view('admin.pages.rental_services.site_setting.form', compact('slug', 'service_category', 'service_settings'));
        } else {
            $view = view('admin.pages.rental_services.site_setting.form', compact('slug', 'service_category'));
        }
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //Save or Update Transport Service Setting
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
            return redirect()->route('get:admin:transport_rental_service_setting', ['slug' => $sevice_category->slug]);
        } else {
            return redirect()->route('get:admin:transport_rental_service_setting', ['slug' => $sevice_category->slug]);
        }
    }

    //code for gos view of rental services
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
                    "phone" => User::ContactNumber2Stars($provider->contact_number),
                    "service_cat_id" => $provider->service_cat_id,
                ];
            }
        }
        $view = view('admin.pages.rental_services.transport_gods_view', compact('slug', 'service_category', 'providers'));
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
}
