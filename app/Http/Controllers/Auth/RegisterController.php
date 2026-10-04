<?php

namespace App\Http\Controllers\Auth;

use App\Classes\NotificationClass;
use App\Http\Requests\ProviderRegisterRequest;
use App\model\OtherServiceProviderTimings;
use App\model\Provider;
use App\model\User;
use App\Http\Controllers\Controller;
use App\Rules\EmailRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

//    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
    //     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'first_name' => $data['first_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function getUserSignup(Request $request)
    {
        $check_email = User::query()->select('id', 'email')->where('email', $request->get('email'))->whereNull('users.deleted_at')->first();
        if ($check_email != Null) {
            return response()->json([
                'success' => false,
                'error' => 'Email Already Exist'
            ]);
        }
        $contact = User::query()->select('id', 'contact_number')
            ->where('contact_number',$request->get('contact'))
            ->where('country_code',$request->get('scountry_code'))
            ->whereNull('users.deleted_at')
            ->first();
        if ($contact != Null) {
            return response()->json([
                'success' => false,
                'error' => 'Contact Number Already Exist',
            ]);
        }

        $user = new User();
        $user->first_name = ucwords(strtolower(trim($request->get('name'))));
        $user->last_name = ucwords(strtolower(trim($request->get('last_name'))));
        $user->email = $request->get('email');
        $user->contact_number = $request->get('contact');
        $user->login_type = "email";
        $user->password = Hash::make($request->get('password'));
        $user->country_code = $request->get('scountry_code');
        //$user->currency = $request->get('select_currency');
        //$user->language = $request->get('select_language');
        //$user->login_device = $request->get('login_device');
        //$user->verified_at = date('Y-m-d H:i:s');
        $user->currency = "$";
        $user->language = "en";
        $user->status = 1;
        $user->save();
        try {
            $user->save();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

//        $this->tokenClassApi->sendUserSmsVerification($user->id);
        Auth::guard('user')->login($user, true);
        return response()->json([
            'success' => true,
            'verify' => 0,
            'message' => 'Register successfully!'
        ]);
    }

    public function getStoreRegister()
    {
        return view('admin.auth.service_provider.register');
    }

    public function postStoreRegister(ProviderRegisterRequest $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('store')->logout();
        Auth::guard('on_demand')->logout();
//dd($request);
        $provider = new Provider();
        $provider->first_name = ucwords($request->get('name'));
        $provider->email = $request->get('email');
//        $provider->contact_number = $request->get('full_number');
        $provider->provider_type = 1;
        $provider->country_code = $request->get('country_code');
        $provider->contact_number = $request->get('contact_number');
        $provider->password = Hash::make($request->get('password'));
        $provider->login_type = "email";
        $provider->gender = $request->get('gender');
        $provider->last_active = date('Y-m-d H:i:s');
        $provider->status = 3;
        $provider->save();

        Auth::guard('store')->login($provider, true);
        if (Auth::guard('store')->user()->status == 3) {
            return redirect()->route('get:store-admin:service-register')->with("success", "Store Admin Register Successfully.");
        }
        return redirect()->route('get:store-admin:dashboard')->with("success", "Store Admin Login Successfully.");
    }

    public function postWebProvierRegister(Request $request)
    {
        $registerType = $request->get('registerType');

        $check_email = Provider::query()->select('id', 'email')->where('email', $request->get('email'))->whereNull('providers.deleted_at')->first();
        if ($check_email != Null) {
            return response()->json([
                'success' => false,
                'error' => 'Email Already Exist'
            ]);
        }
        $contact = Provider::query()->select('id', 'contact_number')
            ->where('contact_number', $request->get('store_contact_numbers'))
            ->where('country_code', $request->get('store_country_code'))
            ->whereNull('providers.deleted_at')
            ->first();
        if ($contact != Null) {
            return response()->json([
                'success' => false,
                'error' => 'Contact Number Already Exist',
            ]);
        }

        $provider = new Provider();
        $provider->first_name = ucwords($request->get('name'));
        $provider->email = $request->get('email');
        $provider->contact_number = $request->get('store_contact_numbers');
        $provider->country_code = $request->get('store_country_code');
        $provider->password = Hash::make($request->get('password'));
        $provider->login_type = "email";
        $provider->provider_type = 1;
        $provider->gender = $request->get('gender');
        $provider->last_active = date('Y-m-d H:i:s');
        $provider->status = 3;
        $provider->save();

        if($registerType == 0){
            $redirect =   route('get:store-admin:login');
            Session::flash('success', 'Store Register successfully!');
        }else{
            $redirect =   route('get:provider-admin:login');
            Session::flash('success', 'Provider Register successfully!');
        }
        return response()->json([
            'success' => true,
            'verify' => 0,
            'message' => 'Store Register successfully!',
            'redirect' => $redirect
        ]);
    }

    public function postProviderRegister(ProviderRegisterRequest $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('store')->logout();
        Auth::guard('on_demand')->logout();

        $provider = new Provider();
//      $provider->name = $request->get('name');
        $provider->first_name = $request->get('name');
        $provider->email = $request->get('email');
        $provider->country_code = $request->get('country_code');
        $provider->provider_type = 3;
//        $provider->contact_number = $request->get('full_number');
        $provider->contact_number = $request->get('contact_number');
        $provider->password = Hash::make($request->get('password'));
        $provider->login_type = "email";
        $provider->gender = $request->get('gender');
        $provider->last_active = date('Y-m-d H:i:s');
        $provider->status = 3;
        $provider->verified_at = date('Y-m-d H:i:s');
        $provider->save();

        $setting = \request()->get('general_settings');
        $all_day_open_time = ($setting->default_start_time == null) ? "00:00:00" : $setting->default_start_time;
        $all_day_close_time = ($setting->default_end_time == null) ? "23:59:59" : $setting->default_end_time;
        $get_times = (new NotificationClass())->defaultProviderOpenCloseTime($all_day_open_time,$all_day_close_time);
        collect(["SUN","MON","TUE","WED","THU","FRI","SAT"])->each(function ($value)use ($provider,$get_times){
            $get_provider_timings = OtherServiceProviderTimings::query()->where('provider_id',$provider->id)->where('day',$value)->first();
            if ($get_provider_timings == null){
                collect($get_times['default_provider_slot'])->each(function ($t)use ($provider,$value){
                    $add_times = new OtherServiceProviderTimings();
                    $add_times->provider_id = $provider->id;
                    $add_times->day = $value;
                    $add_times->status= 1;
                    $add_times->provider_open_time = $t['start_time'];
                    $add_times->provider_close_time = $t['end_time'];
                    $add_times->save();
                });
            }
        });

        Auth::guard('on_demand')->login($provider, true);

//        if (Auth::guard('on_demand')->user()->status == 3) {
//            return redirect()->route('get:provider-admin:service-register')->with("success", "Store Admin Register Successfully.");
//        }
        return redirect()->route('get:provider-admin:dashboard')->with("success", "Provider Admin Login Successfully.");
    }
}
