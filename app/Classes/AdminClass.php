<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01-01-2019
 * Time: 04:32 PM
 */

namespace App\Classes;


use App\model\EmailTemplates;
use App\model\GeneralSettings;
use App\model\OtherServiceProviderDetails;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\TransportDriverDetails;
use App\model\TransportRideBook;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserProductBooking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminClass
{
    private $store_service_id_array;

    private $bike_ride;
    private $taxi_ride;
    private $bike_rental;
    private $taxi_rental;

    private $transport_service_id_array;
    private $delivery_service_id_array;

    private $all;
    private $available;
    private $ride_start;
    private $reached;
    private $enroute;

    public function __construct()
    {
        $this->store_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->bike_ride = 1;
        $this->taxi_ride = 2;
        $this->bike_rental = 31;
        $this->taxi_rental = 32;
        $this->transport_service_id_array = [1, 2, 4, 31, 32];

        $this->delivery_service_id_array = [5, 6, 7, 8, 9, 10];

        $this->all = [0, 4, 9, 10];
        $this->available = [0, 4, 9, 10];
        $this->ride_start = [5, 6, 7, 8];
        $this->reached = [3];
        $this->enroute = [1, 2,];
    }

    public function renderingResponce($view)
    {
        return response()->json([
            'content' => $view['page-content'],
            'title' => $view['title'],
            'extra_css' => $view['page-css'],
            'extra_js' => $view['page-js'],
        ]);
    }

    public function checkProviderStatus()
    {
        $provider = Provider::query()->where('id', Auth::guard('on_demand')->user()->id)->whereNull('providers.deleted_at')->first();
        if ($provider->status == 2) {
            Auth::logout();
            return redirect()->route('post:provider-admin:login')->with("error", "Your account is currently blocked, so not authorised to allow any activity!");
        }
    }

    // Transport SC Service Category Find
    public function transportSCFind($service_cat_id)
    {
        $service_category_details = ServiceCategory::where('slug', '=', $service_cat_id)->whereIn('category_type', [1, 5])->first();
//        if ($service_category_details == Null) {
//            $service_category = $service_category_details;
//        } else {
//            if ($service_category_details->icon_name == Null) {
//                $image_path = Null;
//            } else {
//                $image_path = asset('assets/images/service-category/' . $service_category_details->icon_name);
//            }
//            $service_category = [
//                "id" => $service_category_details->id,
//                "name" => $service_category_details->name,
//                "slug" => $service_category_details->slug,
//                "icon_name" => $image_path,
//                "display_order" => $service_category_details->display_order,
//                "category_type" => $service_category_details->category_type,
//                "status" => $service_category_details->status
//            ];
//        }
        return $service_category_details;
    }

    // Transport SCW Service Category Wise Provider Find
    public function transportSCWProvicerFind($service_cat_id, $driver_id)
    {
//        dd($driver_id);
        $driver_details_check = TransportDriverDetails::query()
            ->select(DB::raw("CONCAT(providers.first_name,' ',providers.last_name) as driver_name"), 'providers.avatar', 'providers.contact_number', 'transport_driver_details.rating', 'transport_vehicle_type.name as vehicle_type_name')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            ->whereNull('providers.deleted_at')
            ->where('transport_driver_details.id', '=', $driver_id);
        if ($service_cat_id != Null) {
            $driver_details = $driver_details_check->where('provider_services.service_cat_id', '=', $service_cat_id)->first();
        } else {
            $driver_details = $driver_details_check->first();
        }
        return $driver_details;
    }

    // Transport SCW Service Category Wise Provider Find
    public function ServiceSettingsStore($request)
    {
        $id = $request->get('id');
        if ($id != Null) {
            $service_settings = ServiceSettings::where('id', $request->get('id'))->first();
        } else {
            $service_settings = new ServiceSettings();
        }
        $service_settings->service_cat_id = $request->get('service_cat_id');
        $service_settings->provider_accept_timeout = $request->get('provider_accept_timeout');
        $service_settings->provider_search_radius = $request->get('provider_search_radius');
        $service_settings->tax = $request->get('tax');
        $service_settings->admin_commission = $request->get('admin_commission');
        $service_settings->cancel_charge = $request->get('cancel_charge');
        //$service_settings->status = $request->get('status');
        $service_settings->save();
        return $service_settings;
    }

    // Add Service Provider
    public function AddServiceProvider($request)
    {
        $id = $request->get('id');
        if ($id != Null) {
            $provider = Provider::where('id', $id)->whereNull('providers.deleted_at')->first();
//            $provider->status = 1;
        } else {
            $provider = new Provider();
            $provider->verified_at =  date('Y-m-d H:i:s');
            $provider->status = 1;
            $provider->password = Hash::make($request->get('pass'));
//            $provider->email = $request->get('email');
//            $provider->country_code = $request->get('country_code');
////        $provider->contact_number = $request->get('full_number');
//            $provider->contact_number = trim($request->get('contact_number'));
        }
        $provider->email = $request->get('email');
        $provider->country_code = $request->get('country_code');
//        $provider->contact_number = $request->get('full_number');
        $provider->contact_number = trim($request->get('contact_number'));
        $provider->first_name = ucwords(trim($request->get('first_name')));
//        $provider->last_name = ucwords(trim($request->get('last_name')));

//        $provider->service_radius = $request->get('service_radius');
        $provider->gender = $request->get('gender');
        //$provider->status = 1;

        if ($request->file('avatar')) {
            if (\File::exists(public_path('/assets/images/profile-images/provider/' . $provider->avatar))) {
                \File::delete(public_path('/assets/images/profile-images/provider/' . $provider->avatar));
            }
            $file = $request->file('avatar');
            $file_new = random_int(1, 99) . date('sihYdm') . random_int(1, 99) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . '/assets/images/profile-images/provider/', $file_new);
            $provider->avatar = $file_new;
        }
        $provider->save();
        return $provider;
    }

    public function checkStoreProviderStatus()
    {
        $store_service = Auth::guard('store')->user()->checkStoreProviderId();
        $provider_id = Auth::guard('store')->user()->id;
        $provider_status = Auth::guard('store')->user()->status;
        $verified = Auth::guard('store')->user()->verified_at;
//        if ($verified == Null) {
//            return redirect()->route('get:store-admin:not_verified');
//        }
//        $status = $store_service['status'];
        if ($provider_status != 1) {
            if ($provider_status == 0) {
                return redirect()->route('get:store-admin:account_approval_pending');
            } elseif ($provider_status == 3) {
                return redirect()->route('get:store-admin:service-register');
            } elseif ($provider_status == 2) {
                Auth::logout();
                Session::flash('error', 'Your account is currently blocked, so not authorised to allow any activity!!');
                return redirect()->route('post:store-admin:login');
            } else {
                Auth::logout();
                Session::flash('error', 'something went to wrong!');
                return redirect()->route('post:store-admin:login');
            }
        }
        $store_id = $store_service['id'];
        if ($store_id == 0) {
            return redirect()->route('get:store-admin:service-register');
        } else {
            $provider_service = ProviderServices::whereIN('service_cat_id', $this->store_service_id_array)->where('provider_id', $provider_id)->first();
            if ($provider_service != Null) {
                if ($provider_service->status != 1) {
                    if ($provider_service->status == 0) {
                        return redirect()->route('get:store-admin:account_approval_pending');
                    } elseif ($provider_service->status == 3) {
                        return redirect()->route('get:store-admin:account_rejected');
                    } elseif ($provider_service->status == 2) {
                        Auth::logout();
                        Session::flash('error', 'Your account is currently blocked, so not authorised to allow any activity!!');
                        return redirect()->route('post:store-admin:login');
                    } else {
                        Auth::logout();
                        Session::flash('error', 'something went to wrong!');
                        return redirect()->route('post:store-admin:login');
                    }
                }
            }
        }
    }


    public function AllProviderLocation($service_category, $check_type = Null, $search_provider_name = Null)
    {
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
        $providers_detials_check = TransportDriverDetails::query()->select(
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
            ->whereNull('providers.deleted_at');
        if ($check_type != Null && $check_type == 1) {
            $providers_detials_check->whereIn('service_category.id', $this->transport_service_id_array);
        } else {
            if ($service_category->id == $this->bike_rental) {
                $providers_detials_check->whereIn('service_category.id', [$service_category->id, $this->bike_ride])
                    ->where('rental_service', 1);
            } elseif ($service_category->id == $this->taxi_rental) {
                $providers_detials_check->whereIn('service_category.id', [$service_category->id, $this->taxi_ride])
                    ->where('rental_service', 1);
            } else {
                $providers_detials_check->where('service_category.id', $service_category->id);
            }
        }
        if ($search_provider_name != Null) {
            //$providers_detials_check->where('providers.name', 'LIKE', "%$search_provider_name%");
            $providers_detials_check->where(function($query) use ($search_provider_name){
                $query->orWhere('providers.first_name', 'LIKE', "%$search_provider_name%")
                    ->orWhere('providers.last_name', 'LIKE', "%$search_provider_name%");
            });
        }
        $providers_detials = $providers_detials_check->where('provider_services.status', 1)
            ->where('providers.status', 1)
            ->whereNotNull('providers.access_token')
            ->get();
        $availability_ride_status = 3;
        $providers = [];
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
        return $providers;
    }

    public function AvailableProviderLocation($service_category, $check_type = Null, $search_provider_name = Null)
    {
        $expire_date_time = date('Y-m-d h:i:s', strtotime("-120 minutes"));

        $running_service = ProviderUserRunningService::all();
        $transport_running_service = [];
        $delivery_running_service = [];

        $transport_provider_check = [];
        $delivery_provider_check = [];
        $driver_array = [];

        $check_available = array_merge($this->ride_start, $this->reached, $this->enroute);
        foreach ($running_service as $key => $running) {
            if (in_array($running->service_cat_id, $this->transport_service_id_array)) {
                $transport_running_service[$running->provider_id] = TransportRideBook::where('id', $running->booking_id)
                    ->whereIn('status', $check_available)
                    ->first();
                if ($transport_running_service[$running->provider_id] != Null) {
                    $transport_provider_check[] = $running->provider_id;
                }
            } elseif (in_array($running->service_cat_id, $this->delivery_service_id_array)) {
                $delivery_running_service[$running->provider_id] = UserProductBooking::where('id', $running->booking_id)
                    ->whereIn('order_delivery_status', $check_available)
                    ->first();
                if ($delivery_running_service[$running->provider_id] != Null) {
                    $delivery_provider_check[] = $running->provider_id;
                }
            }
        }
        $driver_array = array_merge($driver_array, $transport_provider_check);
        $driver_array = array_merge($driver_array, $delivery_provider_check);

        //$driver_array =  array_merge($transport_running_service, $delivery_running_service);
//        if (!count($driver_array) > 0) {
//            return $providers = [];
//        }

        $providers_detials_check = TransportDriverDetails::query()->select(
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
            ->whereNull('providers.deleted_at');
        if ($check_type != Null && $check_type == 1) {
            $providers_detials_check->whereIn('service_category.id', $this->transport_service_id_array);
        } else {
            if ($service_category->id == $this->bike_rental) {
                $providers_detials_check->whereIn('service_category.id', [$service_category->id, $this->bike_ride])
                    ->where('rental_service', 1);
            } elseif ($service_category->id == $this->taxi_rental) {
                $providers_detials_check->whereIn('service_category.id', [$service_category->id, $this->taxi_ride])
                    ->where('rental_service', 1);
            } else {
                $providers_detials_check->where('service_category.id', $service_category->id);
            }
        }
        if ($search_provider_name != Null) {
            //$providers_detials_check->where('providers.name', 'LIKE', "%$search_provider_name%");
            $providers_detials_check->where(function($query) use ($search_provider_name){
                $query->orWhere('providers.first_name', 'LIKE', "%$search_provider_name%")
                    ->orWhere('providers.last_name', 'LIKE', "%$search_provider_name%");
            });
        }

        $providers_detials = $providers_detials_check->where('provider_services.current_status', 1)
            ->where('provider_services.status', 1)
            ->where('providers.status', 1)
            ->whereNotIn('providers.id', $driver_array)
            ->whereNotNull('providers.access_token')
            ->get();
        $availability_ride_status = 3;
        $providers = [];
        $use_tran = [];
        $use_delivery = [];
        if ($providers_detials->isEmpty()) {
            $providers = [];
        } else {

            foreach ($providers_detials as $key => $provider) {
                $providers[] = [
                    "id" => $provider->id,
                    "name" => $provider->name,
                    "last_name" => Null,
                    "image" => $provider->avatar,
                    "latitude" => $provider->current_lat,
                    "longitude" => $provider->current_long,
                    "address" => "",
                    "ride_status" => $availability_ride_status,
                    "phone" =>  User::ContactNumber2Stars($provider->contact_number),
                    "service_cat_id" => $provider->service_cat_id,
                ];
            }
        }
        return $providers;
    }

    public function RideStartProviderLocation($service_category, $check_type = Null, $search_provider_name = Null)
    {
        $expire_date_time = date('Y-m-d h:i:s', strtotime("-120 minutes"));
        $running_service = ProviderUserRunningService::all();
        $transport_running_service = [];
        $delivery_running_service = [];
        $driver_array = [];
        foreach ($running_service as $key => $running) {
            if (in_array($running->service_cat_id, $this->transport_service_id_array)) {
                $transport_running_service[$running->provider_id] = TransportRideBook::query()->where('id', $running->booking_id)
                    ->whereIn('status', $this->ride_start)
                    ->first();
            } elseif (in_array($running->service_cat_id, $this->delivery_service_id_array)) {
                $delivery_running_service[$running->provider_id] = UserProductBooking::query()->where('id', $running->booking_id)
                    ->whereIn('order_delivery_status', $this->ride_start)
                    ->first();
            }
        }
        $driver_array = array_merge($driver_array, $transport_running_service);
        $driver_array = array_merge($driver_array, $delivery_running_service);
        if (!count($driver_array) > 0) {
            return $providers = [];
        }
        $use_tran = [];
        $use_delivery = [];
        foreach ($driver_array as $key => $driver) {
            if ($driver != Null) {
                if (in_array($driver->service_cat_id, $this->delivery_service_id_array)) {
                    $providers_tran_check = TransportDriverDetails::query()->select(
                        'transport_driver_details.id',
                        'providers.id as provider_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.last_name as last_name', 'providers.contact_number',
                        'providers.avatar',
                        'transport_driver_details.current_lat',
                        'transport_driver_details.current_long',
                        'transport_driver_details.availability_ride_status',
                        'service_category.id as service_cat_id',

                        'users.first_name as customer_first_name', 'users.last_name as customer_last_name',
                        'users.contact_number as customer_contact_number', 'users.id as customer_id',
                        'user_store_product_booking.delivery_address'
                    )
                        ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                        ->join('providers', 'providers.id', 'provider_services.provider_id')
                        ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                        ->join('user_store_product_booking', 'user_store_product_booking.delivery_people_id', '=', 'transport_driver_details.id')
                        ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
                        ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                        ->whereNull('providers.deleted_at')
                        ->whereNull('users.deleted_at');
                    if ($check_type != Null && $check_type == 1) {
                        $providers_tran_check->whereIn('service_category.id', $this->transport_service_id_array);
                    } else {
                        if ($service_category->id == $this->bike_rental) {
                            $providers_tran_check->whereIn('service_category.id', [$service_category->id, $this->bike_ride])
                                ->where('rental_service', 1);
                        } elseif ($service_category->id == $this->taxi_rental) {
                            $providers_tran_check->whereIn('service_category.id', [$service_category->id, $this->taxi_ride])
                                ->where('rental_service', 1);
                        } else {
                            $providers_tran_check->where('service_category.id', $service_category->id);
                        }
                    }
                    if ($search_provider_name != Null) {
                        //$providers_tran_check->where('providers.name', 'LIKE', "%$search_provider_name%");
                        $providers_tran_check->where(function($query) use ($search_provider_name){
                            $query->orWhere('providers.first_name', 'LIKE', "%$search_provider_name%")
                                ->orWhere('providers.last_name', 'LIKE', "%$search_provider_name%");
                        });
                    }
                    $providers_tran = $providers_tran_check->where('transport_driver_details.id', $driver->delivery_people_id)
//                        ->where('provider_services.current_status', 1)
                        ->where('provider_services.status', 1)
                        ->where('providers.status', 1)
                        ->whereNotNull('providers.access_token')
                        ->orderBy('user_store_product_booking.id','desc')
                        ->first();
                    if ($providers_tran != Null) {
                        $use_tran[] = array(
                            "id" => $providers_tran->id,
                            "name" => $providers_tran->name,
                            "last_name" => Null,
                            "image" => $providers_tran->avatar,
                            "latitude" => $providers_tran->current_lat,
                            "longitude" => $providers_tran->current_long,
                            "address" => "",
                            "ride_status" => 2,
                            "phone" => User::ContactNumber2Stars($providers_tran->contact_number),
                            "service_cat_id" => $providers_tran->service_cat_id,
                            "pickup_address" => "",
                            "destination_address" => $providers_tran->delivery_address,
                            "suser_id" => $providers_tran->customer_id,
                            "user_name" => $providers_tran->customer_first_name,
                            "user_last_name" => $providers_tran->customer_last_name,
                            "rider_phone" => User::ContactNumber2Stars($providers_tran->customer_contact_number),
                        );
                    }
                }
                if (in_array($driver->service_cat_id, $this->transport_service_id_array)) {
                    $providers_delivery_check = TransportDriverDetails::query()->select(
                        'transport_driver_details.id',
                        'providers.id as provider_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.last_name', 'providers.contact_number',
                        'providers.avatar',
                        'transport_driver_details.current_lat',
                        'transport_driver_details.current_long',
                        'transport_driver_details.availability_ride_status',
                        'service_category.id as service_cat_id',

                        'users.first_name as customer_first_name', 'users.last_name as customer_last_name',
                        'users.contact_number as customer_contact_number', 'users.id as customer_id',
                        'user_ride_booking.destination_address', 'user_ride_booking.pickup_address'
                    )
                        ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                        ->join('providers', 'providers.id', 'provider_services.provider_id')
                        ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                        ->join('user_ride_booking', 'user_ride_booking.driver_id', '=', 'transport_driver_details.id')
                        ->join('users', 'users.id', '=', 'user_ride_booking.user_id')
                        ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                        ->whereNull('providers.deleted_at')
                        ->whereNull('users.deleted_at');
                    if ($check_type != Null && $check_type == 1) {
                        $providers_delivery_check->whereIn('service_category.id', $this->transport_service_id_array);
                    } else {
                        if ($service_category->id == $this->bike_rental) {
                            $providers_delivery_check->whereIn('service_category.id', [$service_category->id, $this->bike_ride])
                                ->where('rental_service', 1);
                        } elseif ($service_category->id == $this->taxi_rental) {
                            $providers_delivery_check->whereIn('service_category.id', [$service_category->id, $this->taxi_ride])
                                ->where('rental_service', 1);
                        } else {
                            $providers_delivery_check->where('service_category.id', $service_category->id);
                        }
                    }
                    if ($search_provider_name != Null) {
                        //$providers_delivery_check->where('providers.name', 'LIKE', "%$search_provider_name%");
                        $providers_delivery_check->where(function($query) use ($search_provider_name){
                            $query->orWhere('providers.first_name', 'LIKE', "%$search_provider_name%")
                                ->orWhere('providers.last_name', 'LIKE', "%$search_provider_name%");
                        });
                    }
                    $providers_delivery = $providers_delivery_check->where('transport_driver_details.id', $driver->driver_id)
//                        ->where('provider_services.current_status', 1)
                        ->where('provider_services.status', 1)
                        ->where('providers.status', 1)
                        ->whereNotNull('providers.access_token')
                        ->orderBy('user_ride_booking.id','desc')
                        ->first();
                    if ($providers_delivery != Null) {
                        $use_delivery[] = array(
                            "id" => $providers_delivery->id,
                            "name" => $providers_delivery->name,
                            "last_name" => Null,
                            "image" => $providers_delivery->avatar,
                            "latitude" => $providers_delivery->current_lat,
                            "longitude" => $providers_delivery->current_long,
                            "address" => "",
                            "ride_status" => 2,
                            "phone" => User::ContactNumber2Stars($providers_delivery->contact_number),
                            "service_cat_id" => $providers_delivery->service_cat_id,

                            "pickup_address" => $providers_delivery->pickup_address,
                            "destination_address" => $providers_delivery->destination_address,
                            "suser_id" => $providers_delivery->customer_id,
                            "user_name" => $providers_delivery->customer_first_name,
                            "user_last_name" => $providers_delivery->customer_last_name,
                            "rider_phone" => User::ContactNumber2Stars($providers_delivery->customer_contact_number),
                        );
                    }
                }
            }
        }
        $providers = array_merge($use_tran, $use_delivery);
        return $providers;
    }

    public function RideReachedProviderLocation($service_category, $check_type = Null, $search_provider_name = Null)
    {
        $expire_date_time = date('Y-m-d h:i:s', strtotime("-120 minutes"));
        $running_service = ProviderUserRunningService::all();
        $transport_running_service = [];
        $delivery_running_service = [];
        $driver_array = [];
        foreach ($running_service as $key => $running) {
            if (in_array($running->service_cat_id, $this->transport_service_id_array)) {
                $transport_running_service[$running->provider_id] = TransportRideBook::where('id', $running->booking_id)
                    ->whereIn('status', $this->reached)
                    ->first();
            } elseif (in_array($running->service_cat_id, $this->delivery_service_id_array)) {
                $delivery_running_service[$running->provider_id] = UserProductBooking::where('id', $running->booking_id)
                    ->whereIn('order_delivery_status', $this->reached)
                    ->first();
            }
        }
        $driver_array = array_merge($driver_array, $transport_running_service);
        $driver_array = array_merge($driver_array, $delivery_running_service);
        //$driver_array =  array_merge($transport_running_service, $delivery_running_service);
        if (!count($driver_array) > 0) {
            return $providers = [];
        }
        $use_tran = [];
        $use_delivery = [];
        foreach ($driver_array as $key => $driver) {
            if ($driver != Null) {
                if (in_array($driver->service_cat_id, $this->delivery_service_id_array)) {
                    $providers_tran_check = TransportDriverDetails::query()->select(
                        'transport_driver_details.id',
                        'providers.id as provider_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.last_name', 'providers.contact_number',
                        'providers.avatar',
                        'transport_driver_details.current_lat',
                        'transport_driver_details.current_long',
                        'transport_driver_details.availability_ride_status',
                        'service_category.id as service_cat_id',

                        'users.first_name as customer_first_name', 'users.last_name as customer_last_name',
                        'users.contact_number as customer_contact_number', 'users.id as customer_id',
                        'user_store_product_booking.delivery_address'
                    )
                        ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                        ->join('providers', 'providers.id', 'provider_services.provider_id')
                        ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                        ->join('user_store_product_booking', 'user_store_product_booking.delivery_people_id', '=', 'transport_driver_details.id')
                        ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
                        ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                        ->whereNull('providers.deleted_at')
                        ->whereNull('users.deleted_at');
                    if ($check_type != Null & $check_type == 1) {
                        $providers_tran_check->whereIn('service_category.id', $this->transport_service_id_array);
                    } else {
                        if ($service_category->id == $this->bike_rental) {
                            $providers_tran_check->whereIn('service_category.id', [$service_category->id, $this->bike_ride])
                                ->where('rental_service', 1);
                        } elseif ($service_category->id == $this->taxi_rental) {
                            $providers_tran_check->whereIn('service_category.id', [$service_category->id, $this->taxi_ride])
                                ->where('rental_service', 1);
                        } else {
                            $providers_tran_check->where('service_category.id', $service_category->id);
                        }
                    }
                    if ($search_provider_name != Null) {
                        //$providers_tran_check->where('providers.name', 'LIKE', "%$search_provider_name%");
                        $providers_tran_check->where(function($query) use ($search_provider_name){
                            $query->orWhere('providers.first_name', 'LIKE', "%$search_provider_name%")
                                ->orWhere('providers.last_name', 'LIKE', "%$search_provider_name%");
                        });
                    }
                    $providers_tran = $providers_tran_check->where('transport_driver_details.id', $driver->delivery_people_id)
//                        ->where('provider_services.current_status', 1)
                        ->where('provider_services.status', 1)
                        ->where('providers.status', 1)
                        ->whereNotNull('providers.access_token')
                        ->first();
                    if ($providers_tran != Null) {
                        $use_tran[] = array(
                            "id" => $providers_tran->id,
                            "name" => $providers_tran->name,
                            "last_name" => Null,
                            "image" => $providers_tran->avatar,
                            "latitude" => $providers_tran->current_lat,
                            "longitude" => $providers_tran->current_long,
                            "address" => "",
                            "ride_status" => 1,
                            "phone" => User::ContactNumber2Stars($providers_tran->contact_number),
                            "service_cat_id" => $providers_tran->service_cat_id,

                            "pickup_address" => "",
                            "destination_address" => $providers_tran->delivery_address,
                            "suser_id" => $providers_tran->customer_id,
                            "user_name" => $providers_tran->customer_first_name,
                            "user_last_name" => $providers_tran->customer_last_name,
                            "rider_phone" => User::ContactNumber2Stars($providers_tran->customer_contact_number),
                        );
                    }
                } else {
                    $providers_delivery_check = TransportDriverDetails::query()->select(
                        'transport_driver_details.id',
                        'providers.id as provider_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.last_name', 'providers.contact_number',
                        'providers.avatar',
                        'transport_driver_details.current_lat',
                        'transport_driver_details.current_long',
                        'transport_driver_details.availability_ride_status',
                        'service_category.id as service_cat_id',

                        'users.first_name as customer_first_name', 'users.last_name as customer_last_name',
                        'users.contact_number as customer_contact_number', 'users.id as customer_id',
                        'user_ride_booking.destination_address', 'user_ride_booking.pickup_address'
                    )
                        ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                        ->join('providers', 'providers.id', 'provider_services.provider_id')
                        ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                        ->join('user_ride_booking', 'user_ride_booking.driver_id', '=', 'transport_driver_details.id')
                        ->join('users', 'users.id', '=', 'user_ride_booking.user_id')
                        ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                        ->whereNull('providers.deleted_at')
                        ->whereNull('users.deleted_at');
                    if ($check_type != Null && $check_type == 1) {
                        $providers_delivery_check->whereIn('service_category.id', $this->transport_service_id_array);
                    } else {
                        if ($service_category->id == $this->bike_rental) {
                            $providers_delivery_check->whereIn('service_category.id', [$service_category->id, $this->bike_ride])
                                ->where('rental_service', 1);
                        } elseif ($service_category->id == $this->taxi_rental) {
                            $providers_delivery_check->whereIn('service_category.id', [$service_category->id, $this->taxi_ride])
                                ->where('rental_service', 1);
                        } else {
                            $providers_delivery_check->where('service_category.id', $service_category->id);
                        }
                    }
                    if ($search_provider_name != Null) {
                        //$providers_delivery_check->where('providers.name', 'LIKE', "%$search_provider_name%");
                        $providers_delivery_check->where(function($query) use ($search_provider_name){
                            $query->orWhere('providers.first_name', 'LIKE', "%$search_provider_name%")
                                ->orWhere('providers.last_name', 'LIKE', "%$search_provider_name%");
                        });
                    }
                    $providers_delivery = $providers_delivery_check->where('transport_driver_details.id', $driver->driver_id)
//                        ->where('provider_services.current_status', 1)
                        ->where('provider_services.status', 1)
                        ->where('providers.status', 1)
                        ->whereNotNull('providers.access_token')
                        ->first();
                    if ($providers_delivery != Null) {
                        $use_delivery[] = array(
                            "id" => $providers_delivery->id,
                            "name" => $providers_delivery->name,
                            "last_name" => Null,
                            "image" => $providers_delivery->avatar,
                            "latitude" => $providers_delivery->current_lat,
                            "longitude" => $providers_delivery->current_long,
                            "address" => "",
                            "ride_status" => 1,
                            "phone" => User::ContactNumber2Stars($providers_delivery->contact_number),
                            "service_cat_id" => $providers_delivery->service_cat_id,

                            "pickup_address" => $providers_delivery->pickup_address,
                            "destination_address" => $providers_delivery->destination_address,
                            "suser_id" => $providers_delivery->customer_id,
                            "user_name" => $providers_delivery->customer_first_name,
                            "user_last_name" => $providers_delivery->customer_last_name,
                            "rider_phone" => User::ContactNumber2Stars($providers_delivery->customer_contact_number),
                        );
                    }
                }
            }
        }
        $providers = array_merge($use_tran, $use_delivery);
        return $providers;
    }

    public function RideEnrouteProviderLocation($service_category, $check_type = Null, $search_provider_name = Null)
    {
        $expire_date_time = date('Y-m-d h:i:s', strtotime("-120 minutes"));
        $running_service = ProviderUserRunningService::all();
        $transport_running_service = [];
        $delivery_running_service = [];
        $driver_array = [];
        foreach ($running_service as $key => $running) {
            if (in_array($running->service_cat_id, $this->transport_service_id_array)) {
                $transport_running_service[$running->provider_id] = TransportRideBook::where('id', $running->booking_id)
                    ->whereIn('status', $this->enroute)
                    ->first();
            } elseif (in_array($running->service_cat_id, $this->delivery_service_id_array)) {
                $delivery_running_service[$running->provider_id] = UserProductBooking::where('id', $running->booking_id)
                    ->whereIn('order_delivery_status', $this->enroute)
                    ->first();
            }
        }
        $driver_array = array_merge($driver_array, $transport_running_service);
        $driver_array = array_merge($driver_array, $delivery_running_service);
        if (!count($driver_array) > 0) {
            return $providers = [];
        }
        $use_tran = [];
        $use_delivery = [];
        foreach ($driver_array as $key => $driver) {
            if ($driver != Null) {
                if (in_array($driver->service_cat_id, $this->delivery_service_id_array)) {
                    $providers_tran_check = TransportDriverDetails::query()->select(
                        'transport_driver_details.id',
                        'providers.id as provider_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.last_name', 'providers.contact_number',
                        'providers.avatar',
                        'transport_driver_details.current_lat',
                        'transport_driver_details.current_long',
                        'transport_driver_details.availability_ride_status',
                        'service_category.id as service_cat_id',

                        'users.first_name as customer_first_name', 'users.last_name as customer_last_name',
                        'users.contact_number as customer_contact_number', 'users.id as customer_id',
                        'user_store_product_booking.delivery_address'
                    )
                        ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                        ->join('providers', 'providers.id', 'provider_services.provider_id')
                        ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                        ->join('user_store_product_booking', 'user_store_product_booking.delivery_people_id', '=', 'transport_driver_details.id')
                        ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
                        ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                        ->whereNull('providers.deleted_at')
                        ->whereNull('users.deleted_at');
                    if ($check_type != Null && $check_type == 1) {
                        $providers_tran_check->whereIn('service_category.id', $this->transport_service_id_array);
                    } else {
                        if ($service_category->id == $this->bike_rental) {
                            $providers_tran_check->whereIn('service_category.id', [$service_category->id, $this->bike_ride])
                                ->where('rental_service', 1);
                        } elseif ($service_category->id == $this->taxi_rental) {
                            $providers_tran_check->whereIn('service_category.id', [$service_category->id, $this->taxi_ride])
                                ->where('rental_service', 1);
                        } else {
                            $providers_tran_check->where('service_category.id', $service_category->id);
                        }
                    }
                    if ($search_provider_name != Null) {
                        //$providers_tran_check->where('providers.name', 'LIKE', "%$search_provider_name%");
                        $providers_tran_check->where(function($query) use ($search_provider_name){
                            $query->orWhere('providers.first_name', 'LIKE', "%$search_provider_name%")
                                ->orWhere('providers.last_name', 'LIKE', "%$search_provider_name%");
                        });
                    }
                    $providers_tran = $providers_tran_check->where('transport_driver_details.id', $driver->delivery_people_id)
//                        ->where('provider_services.current_status', 1)
                        ->where('provider_services.status', 1)
                        ->where('providers.status', 1)
                        ->whereNotNull('providers.access_token')
                        ->first();
                    if ($providers_tran != Null) {
                        $use_tran[] = array(
                            "id" => $providers_tran->id,
                            "name" => $providers_tran->name,
                            "last_name" => Null,
                            "image" => $providers_tran->avatar,
                            "latitude" => $providers_tran->current_lat,
                            "longitude" => $providers_tran->current_long,
                            "address" => "",
                            "ride_status" => 0,
                            "phone" => User::ContactNumber2Stars($providers_tran->contact_number),
                            "service_cat_id" => $providers_tran->service_cat_id,

                            "pickup_address" => "",
                            "destination_address" => $providers_tran->delivery_address,
                            "suser_id" => $providers_tran->customer_id,
                            "user_name" => $providers_tran->customer_first_name,
                            "user_last_name" => $providers_tran->customer_last_name,
                            "rider_phone" => User::ContactNumber2Stars($providers_tran->customer_contact_number),
                        );
                    }
                } else {
                    $providers_delivery_check = TransportDriverDetails::query()->select(
                        'transport_driver_details.id',
                        'providers.id as provider_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.last_name', 'providers.contact_number',
                        'providers.avatar',
                        'transport_driver_details.current_lat',
                        'transport_driver_details.current_long',
                        'transport_driver_details.availability_ride_status',
                        'service_category.id as service_cat_id',

                        'users.first_name as customer_first_name', 'users.last_name as customer_last_name',
                        'users.contact_number as customer_contact_number', 'users.id as customer_id',
                        'user_ride_booking.destination_address', 'user_ride_booking.pickup_address'
                    )
                        ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                        ->join('providers', 'providers.id', 'provider_services.provider_id')
                        ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                        ->join('user_ride_booking', 'user_ride_booking.driver_id', '=', 'transport_driver_details.id')
                        ->join('users', 'users.id', '=', 'user_ride_booking.user_id')
                        ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                        ->whereNull('users.deleted_at')
                        ->whereNull('providers.deleted_at');
                    if ($check_type != Null && $check_type == 1) {
                        $providers_delivery_check->whereIn('service_category.id', $this->transport_service_id_array);
                    } else {
                        if ($service_category->id == $this->bike_rental) {
                            $providers_delivery_check->whereIn('service_category.id', [$service_category->id, $this->bike_ride])
                                ->where('rental_service', 1);
                        } elseif ($service_category->id == $this->taxi_rental) {
                            $providers_delivery_check->whereIn('service_category.id', [$service_category->id, $this->taxi_ride])
                                ->where('rental_service', 1);
                        } else {
                            $providers_delivery_check->where('service_category.id', $service_category->id);
                        }
                    }
                    if ($search_provider_name != Null) {
                        //$providers_delivery_check->where('providers.name', 'LIKE', "%$search_provider_name%");
                        $providers_delivery_check->where(function($query) use ($search_provider_name){
                            $query->orWhere('providers.first_name', 'LIKE', "%$search_provider_name%")
                                ->orWhere('providers.last_name', 'LIKE', "%$search_provider_name%");
                        });
                    }
                    $providers_delivery = $providers_delivery_check->where('transport_driver_details.id', $driver->driver_id)
//                        ->where('provider_services.current_status', 1)
                        ->where('provider_services.status', 1)
                        ->where('providers.status', 1)
                        ->whereNotNull('providers.access_token')
                        ->first();
                    if ($providers_delivery != Null) {
                        $use_delivery[] = array(
                            "id" => $providers_delivery->id,
                            "name" => $providers_delivery->name,
                            "last_name" => Null,
                            "image" => $providers_delivery->avatar,
                            "latitude" => $providers_delivery->current_lat,
                            "longitude" => $providers_delivery->current_long,
                            "address" => "",
                            "ride_status" => 0,
                            "phone" => $providers_delivery->contact_number,
                            "service_cat_id" => $providers_delivery->service_cat_id,

                            "pickup_address" => $providers_delivery->pickup_address,
                            "destination_address" => $providers_delivery->destination_address,
                            "suser_id" => $providers_delivery->customer_id,
                            "user_name" => $providers_delivery->customer_first_name,
                            "user_last_name" => $providers_delivery->customer_last_name,
                            "rider_phone" => $providers_delivery->customer_contact_number,
                        );
                    }
                }
            }
        }
        $providers = array_merge($use_tran, $use_delivery);
        return $providers;
    }

    public function get_langugae_fields($user_lang="en")
    {
        if ($user_lang == "fil") {
            $lang_prefix = "fl_";
        } else if ($user_lang == "ceb") {
            $lang_prefix = "cb_";
        } else if ($user_lang == "zh-CN" || $user_lang == "zh-Hans" ) {
            $lang_prefix = "cs_";
        } else if ($user_lang == "zh" ||  $user_lang == "zh-Hant" ) {
//            $lang_prefix = "ct_";
            $lang_prefix = "zh_";
        } else if ($user_lang == "ja") {
//            $lang_prefix = "jp_";
            $lang_prefix = "ja_";
        } else if ($user_lang == "ko") {
            $lang_prefix = "ko_";
        } else if ($user_lang == "fr") {
            $lang_prefix = "fr_";
        } else if ($user_lang == "es") {
            $lang_prefix = "sp_";
        } else if ($user_lang == "de") {
            $lang_prefix = "gr_";
        } else if ($user_lang == "en") {
            $lang_prefix = "";
        } else if ($user_lang == "ar") {
            $lang_prefix = "ar_";
        } else {
//            $lang_prefix = "";
            $lang_prefix = ($user_lang == 'en') ? '' : $user_lang."_";
        }
        return $lang_prefix;
    }

    public function is_in_restricted_area($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y){
        $i = $j = $c = 0;
        for ($i = 0, $j = $points_polygon-1; $i < $points_polygon; $j = $i++) {
            if ((($vertices_y[$i] > $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
                ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i])))
                $c = !$c;
        }
        return $c;
    }

    public function checkPromoCodeValid($promo_id, $amount, $user_id, $service_cat_id)
    {
        $get_user_used_count = UsedPromocodeDetails::where('user_id', $user_id)->where('id', $promo_id)->where('service_cat_id', $service_cat_id)->count();
        $get_user_used = UsedPromocodeDetails::where('user_id', $user_id)->where('id', $promo_id)->where('service_cat_id', $service_cat_id)->first();
        $get_promocode = PromocodeDetails::where('id', $get_user_used->id)->where('service_cat_id', $service_cat_id)->where('status', 1)->first();
        if ($get_promocode != Null) {
            if ($get_promocode->min_order_amount != Null) {
                if ($get_promocode->min_order_amount <= $amount) {
                    if ($get_promocode->discount_type == 1) {
                        $new_price = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);
                    } elseif ($get_promocode->discount_type == 2) {
                        $new_price = round((($amount) - (($amount) * ($get_promocode->discount_amount)) / (100)), 2);
                    } else {
                        return 0;
                    }
                    if ($get_promocode->max_discount_amount != Null) {
                        if ($get_promocode->max_discount_amount < $new_price) {
                            return round($get_promocode->max_discount_amount, 2);
                        } else {
                            return round($new_price, 2);
                        }
                    } else {
                        return round($new_price, 2);
                    }
                } else {
                    return 1;
                }
            } else {
                if ($get_promocode->discount_type == 1) {
                    $new_price = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);
                } elseif ($get_promocode->discount_type == 2) {
                    $new_price = round((($amount) - (($amount) * ($get_promocode->discount_amount)) / (100)), 2);
                } else {
                    return 0;
                }
                if ($get_promocode->max_discount_amount != Null) {
                    if ($get_promocode->max_discount_amount < $new_price) {
                        return round($get_promocode->max_discount_amount, 2);
                    } else {
                        return round($new_price, 2);
                    }
                } else {
                    return round($new_price, 2);
                }
            }
        } else {
            return 0;
        }
    }

    public function emailTemplates($data,$id){
        $template = EmailTemplates::query()->where('id',$id)->where('status',1)->first();
        if($template != Null){
            if($template->id == 1){
                $html = str_replace(['#user_name#'],$data,$template->content);
            } else if($template->id == 2){
                $html = str_replace(['#user_name#','#service_name#'],$data,$template->content);
            } else if($template->id == 3){
                $html = str_replace(['#user_name#','#driver_name#'],$data,$template->content);
            } else if($template->id == 4){
                $html = str_replace(['#rider#','#service_name#','#ride_id#'],$data,$template->content);
            } else if($template->id == 5){
                $html = str_replace(['#user_name#','#store_name#'],$data,$template->content);
            } else if($template->id == 6){
                $html = str_replace(['#cancel_by#','#service_name#'],$data,$template->content);
            } else if($template->id == 7){
                $html = str_replace(['#service_name#','#order_id#','#address#','#driver_name#','#time#'],$data,$template->content);
            } else if($template->id == 8){
                $html = str_replace(['#user_name#','#provider_name#','#service_name#'],$data,$template->content);
            } else if($template->id == 9){
                $html = str_replace(['#user_name#','#provider_name#','#service_name#'],$data,$template->content);
            } else if($template->id == 10){
                $html = str_replace(['#user_name#','#provider_name#','#service_name#','#date_time#'],$data,$template->content);
            } else if($template->id == 11){
                $html = str_replace(['#user_name#'],$data,$template->content);
            } else if($template->id == 12){
                $html = str_replace(['#driver_name#'],$data,$template->content);
            } else if($template->id == 13){
                $html = str_replace(['#driver_name#'],$data,$template->content);
            } else if($template->id == 14){
                $html = str_replace(['#driver_name#','#service_name#','#user_name#','#pickup_location#','#destination_location#','#pickup_time#'],$data,$template->content);
            } else if($template->id == 15){
                $html = str_replace(['#driver_name#','#service_name#','#ride_id#','#date_time#'],$data,$template->content);
            } else if($template->id == 16){
                $html = str_replace(['#driver_name#','#service_name#','#order_id#'],$data,$template->content);
            } else if($template->id == 17){
                $html = str_replace(['#driver_name#'],$data,$template->content);
            } else if($template->id == 18){
                $html = str_replace(['#store_name#'],$data,$template->content);
            } else if($template->id == 19){
                $html = str_replace(['#store_name#'],$data,$template->content);
            } else if($template->id == 20){
                $html = str_replace(['#store_name#','#user_name#','#product_details#','#total#'],$data,$template->content);
            } else if($template->id == 21){
                $html = str_replace(['#store_name#'],$data,$template->content);
            } else if($template->id == 22){
                $html = str_replace(['#provider_name#'],$data,$template->content);
            } else if($template->id == 23){
                $html = str_replace(['#provider_name#'],$data,$template->content);
            } else if($template->id == 24){
                $html = str_replace(['#provider_name#'],$data,$template->content);
            } else if($template->id == 25){
                $html = str_replace(['#provider_name#','#service_name#','#user_name#'],$data,$template->content);
            } else if($template->id == 26){
                $html = str_replace(['#provider_name#','#service_name#','#time#'],$data,$template->content);
            } else if($template->id == 27){
                $html = str_replace(['#restaurant_name#'],$data,$template->content);
            } else if($template->id == 28){
                $html = str_replace(['#user_name#'],$data,$template->content);
            } else if($template->id == 29){
                $html = str_replace(['#driver_name#'],$data,$template->content);
            } else if($template->id == 30){
                $html = str_replace(['#provider_name#','#services_name#','#email#','#contact_no#'],$data,$template->content);
            } else if($template->id == 31){
                $html = str_replace(['#store_name#','#user_name#','#product_details#','#total#'],$data,$template->content);
            } else if($template->id == 32){
                $html = str_replace(['#provider_name#','#service_name#','#user_name#'],$data,$template->content);
            } else if($template->id == 32){
                $html = str_replace(['#driver_name#','#service_name#','#user_name#','#pickup_location#','#destination_location#','#pickup_time#'],$data,$template->content);
            } else{
                $html = Null;
            }

            $settings = GeneralSettings::query()->select('twitter_link','facebook_link')->first();
            $facebook = $settings->facebook_link != NUll ? $settings->facebook_link : 'https://www.facebook.com';
            $twitter = $settings->twitter_link != NUll ? $settings->twitter_link : 'https://www.twitter.com';

            $html = str_replace(['#facebook#','#twitter#'],[$facebook,$twitter],$html);
            return $html;
        } else{
            return Null;
        }
    }
}
