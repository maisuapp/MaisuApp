<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactLoginRequest;
use App\Http\Requests\EmailLoginRequest;
use App\model\Admin;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ServiceCategory;
use App\model\User;
use http\Exception;
//use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
//    /*
//    |--------------------------------------------------------------------------
//    | Login Controller
//    |--------------------------------------------------------------------------
//    |
//    | This controller handles authenticating users for the application and
//    | redirecting them to your home screen. The controller uses a trait
//    | to conveniently provide its functionality to your applications.
//    |
//    */
//
//    use AuthenticatesUsers;

//
//    /**
//     * Where to redirect users after login.
//     *
//     * @var string
//     */
//    protected $redirectTo = '/home';
//
//    /**
//     * Create a new controller instance.
//     *
//     * @return void
//     */

    private $store_service_id_array;

    public function __construct()
    {
        $this->store_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:admin')->except('logout');
        $this->middleware('guest:store')->except('logout');
    }

    //get user register
    public function getRegister()
    {
        return view('auth.register');
    }

    //get user login
    public function getUserLogin(Request $request)
    {
        $email = $request->get('contact');
        $country_code = $request->get('country_code');

        if (is_numeric($email)) {

            $user_details = User::query()
                ->where('contact_number', $email)
                ->where('country_code', $country_code)
                ->whereNull('users.deleted_at')
                ->first();
        } else {
            $user_details = User::query()->where('email','=', $email)->first();
        }

        if ($user_details != Null) {
            if (Hash::check($request->get('password'), $user_details->password)) {
                Auth::logout();

                if (is_numeric($email)) {
                    if (Auth::guard('user')->attempt(['contact_number' => $email,'country_code' => $country_code, 'password' => $request->get('password')], $request->get('remember'))) {
                        if (Auth::guard('user')->user()->status == 1) {
                            if(Auth::guard('user')->user()->verified_at == Null){
                                Session::flash('success', 'User Login Successfully.');
                                return redirect()->route('get:otp-verify')->with("success", "User Login Successfully.");
                            }else{
                                Session::flash('success', 'User Login Successfully.');
                                return redirect()->back();
                            }
                        } else {
                            Auth::guard('user')->logout();
                            return redirect()->back()->with("error", "Your account is currently blocked.");
                        }
                    } else {
                        Auth::logout();
                        return redirect()->back()->with("error", "Your credential was wrong. Please enter right credential.");
                    }
                } else {
                    if (Auth::guard('user')->attempt(['email' => $email, 'password' => $request->get('password')], $request->get('remember'))) {
                        if (Auth::guard('user')->user()->status == 1) {
                            if(Auth::guard('user')->user()->verified_at == Null){
                                Session::flash('success', 'User Login Successfully.');

                                return redirect()->route('get:otp-verify')->with("success", "User Login Successfully.");
                            }else{
                                Session::flash('success', 'User Login Successfully.');
                                return redirect()->back();
                            }
                        } else {
                            Auth::guard('user')->logout();
                            return redirect()->back()->with("error", "Your account is currently blocked.");
                        }
                    } else {
                        Auth::logout();
                        return redirect()->back()->with("error", "Your credential was wrong. Please enter right credential.");
                    }
                }
            } else {
                Auth::logout();
                return redirect()->back()->with("error", "Your credential was wrong. Please enter right credential.");
            }
        } else {

            Session::flash('error', 'User not Found!');
            return redirect()->back();
        }
    }

    //get admin login form
    public function getAdminLogin()
    {
//        Auth::logout();
        if (Auth::guard("admin")->check()) {
            return redirect('/admin/dashboard');
        } else {
            Auth::logout();
        }
        return view('admin.auth.super_admin.login');
    }

    //post admin login
    public function postSuperAdminLogin(EmailLoginRequest $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('store')->logout();
        Auth::guard('on_demand')->logout();
        $roles = $request->get('roles');
        $super_admin = Admin::where('email', $request->get('email'))->first();

        if ($super_admin != Null) {
            if ($super_admin->roles == $roles || $super_admin->roles == "4") {
                if (Hash::check($request->get('password'), $super_admin->password)) {
                    if ($super_admin->roles == 1 || $super_admin->roles == 2 || $super_admin->roles == 3 || $super_admin->roles == 4 ) {
                        if ($super_admin->roles == 1 || $super_admin->roles == 4 ) {
                            Auth::logout();

                            if (Auth::guard('admin')->attempt(['email' => $request->get('email'), 'password' => $request->get('password')], $request->get('remember'))) {
                                return redirect()->intended(route('get:admin:dashboard'))->with("success", "Admin Login Successfully.");
//                                dd( redirect()->intended('defaultpage')->getTargetUrl());
//                                return redirect()->route('get:admin:dashboard')->with("success", "Super Admin Login Successfully.");
                            } else {
                                Auth::logout();
                                return redirect()->back()->with("error", "Your email and password was wrong. Please enter right credential.");
                            }
                        } elseif ($super_admin->roles == 2) {
                            Auth::logout();
                            if (Auth::guard('admin')->attempt(['email' => $request->get('email'), 'password' => $request->get('password')], $request->get('remember'))) {
                                return redirect()->intended(route('get:dispatcher:manual_ride_booking'))->with("success", "Dispatcher Admin Login Successfully.");
//                                return redirect()->route('get:dispatcher:manual_ride_booking')->with("success", "Dispatcher Admin Login Successfully.");
                            } else {
                                Auth::logout();
                                return redirect()->back()->with("error", "Your email and password was wrong. Please enter right credential.");
                            }
                        } elseif ($super_admin->roles == 3) {
                            Auth::logout();
                            if (Auth::guard('admin')->attempt(['email' => $request->get('email'), 'password' => $request->get('password')], $request->get('remember'))) {
//                                return redirect()->route('get:account:dashboard')->with("success", "Billing Account Admin Login Successfully.");
                                return redirect()->intended(route('get:account:dashboard'))->with("success", "Billing Account Admin Login Successfully.");
                            } else {
                                Auth::logout();
                                return redirect()->back()->with("error", "Your email and password was wrong. Please enter right credential.");
                            }
                        } else {
                            return redirect()->back()->with("error", "Your email and password was wrong. Please enter right credential.");
                        }
                    } else {
                        return redirect()->back()->with("error", "Your email and password was wrong. Please enter right credential.");
                    }
                } else {
                    Auth::logout();
                    return redirect()->back()->with("error", "Your email and password was wrong. Please enter right credential.");
                }
            } else {
                return redirect()->back()->with("error", "Only Administration are Allowed.");
            }
        } else {
            return redirect()->back()->with("error", "Your email and password was wrong. Please enter right credential.");
        }
    }

    //get store login form
    public function getStoreLogin()
    {
        if (Auth::guard("store")->check()) {
            return redirect('/store-admin/dashboard');
        } else {
            Auth::logout();
        }
        return view('admin.auth.service_provider.store-login');
    }

    //post store login
    public function postStoreAdminLogin(Request $request)
    {
        $contact_number = $request->get('contact_number');
        $country_code = $request->get('country_code');
        Auth::guard('admin')->logout();
        Auth::guard('store')->logout();
        Auth::guard('on_demand')->logout();
//        $store_admin = Provider::where('email', $request->get('email'))->first();
        $store_admin = Provider::query()->where('contact_number', $contact_number)
            ->where('country_code', $country_code)
            ->whereNull('providers.deleted_at')
            ->where('provider_type','=',1)
            ->first();
        if ($store_admin != Null) {
            if (Hash::check($request->get('password'), $store_admin->password)) {
                Auth::logout();
                if (Auth::guard('store')->attempt(['contact_number' => $contact_number, 'country_code' => $country_code, 'password' => $request->get('password')], $request->get('remember'))) {
                    if (Auth::guard('store')->user()->status == 3) {
                        return redirect()->route('get:store-admin:service-register')->with("success", "Store Admin Login Successfully.");
                    }
                    $provider_id = Auth::guard('store')->user()->id;
                    $provider_service = ProviderServices::where('provider_id', $provider_id)->first();
                    if ($provider_service != Null) {
                        if (!in_array($provider_service->service_cat_id, $this->store_service_id_array)) {
                            Auth::logout();
                            return redirect()->back()->with("error", "You are not register as store provider.");
                        }
                    }
                    return redirect()->route('get:store-admin:dashboard')->with("success", "Store Admin Login Successfully.");
                } else {
                    Auth::logout();
                    return redirect()->back()->with("error", "Your contact number password was wrong. Please enter right credential.");
                }
            } else {
                Auth::logout();
                return redirect()->back()->with("error", "Your contact and password was wrong. Please enter right credential.");
            }
        } else {
            return redirect()->back()->with("error", "Your contact and password was wrong. Please enter right credential.");
        }
    }

    public function postProviderAdminLogin(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'contact_number' => "required|numeric"
        ]);
        if ($validator->fails()){
            return redirect()->back()->with('error',$validator->errors()->first());
        }
        $contact_number = $request->get('contact_number');
        $country_code = $request->get('country_code');
        Auth::guard('admin')->logout();
        Auth::guard('store')->logout();
        Auth::guard('on_demand')->logout();
//        $provider_admin = Provider::where('email', $request->get('email'))->first();
        $provider_admin = Provider::query()->where('contact_number', $contact_number)
            ->where('country_code', $country_code)
            ->where('provider_type', '=',3)
            ->whereNull('providers.deleted_at')
            ->first();
        if ($provider_admin != Null) {
            if (Hash::check($request->get('password'), $provider_admin->password)) {
                Auth::logout();
                if (Auth::guard('on_demand')->attempt(['contact_number' => $contact_number, 'country_code' => $country_code, 'password' => $request->get('password')], $request->get('remember'))) {
                    if (Auth::guard('on_demand')->user()->status == 3) {
                        return redirect()->route('get:provider-admin:service-register')->with("success", "Provider Admin Login Successfully.");
                    }
                    $provider_id = Auth::guard('on_demand')->user()->id;
                    $provider_service = ProviderServices::where('provider_id', $provider_id)->first();
                    if ($provider_service != Null) {
                        $service_category = ServiceCategory::where('id', $provider_service->service_cat_id)->first();
                        if ($service_category == Null) {
                            Auth::logout();
                            return redirect()->back()->with("error", "something went to wrong.");
                        }
                        if (!in_array($service_category->category_type, [3, 4])) {
                            Auth::logout();
                            return redirect()->back()->with("error", "You are not register as on-demand provider.");
                        }
                    }
                    return redirect()->route('get:provider-admin:dashboard')->with("success", "Provider Admin Login Successfully.");
                } else {
                    Auth::logout();
                    return redirect()->back()->with("error", "Your contact and password was wrong. Please enter right credential.");
                }
            } else {
                Auth::logout();
                return redirect()->back()->with("error", "Your contact and password was wrong. Please enter right credential.");
            }
        } else {
            return redirect()->back()->with("error", "Your contact and password was wrong. Please enter right credential.");
        }
    }

    //post logout
    public function logout(Request $request, $guard)
    {
        if ($guard == 'admin') {
            Auth::guard('admin')->logout();
            return redirect()->route('get:admin:login')->with("success", "Admin Logout Successfully.");
        } elseif ($guard == 'store') {
            Auth::guard('store')->logout();
            return redirect()->route('get:store-admin:login')->with("success", "Store Logout Successfully.");
        } elseif ($guard == 'on_demand') {
            Auth::guard('on_demand')->logout();
            return redirect()->route('get:provider-admin:login')->with("success", "Provider Logout Successfully.");
        }elseif($guard == "user"){
            Auth::guard('user')->logout();
            return redirect()->route('get:homepage')->with('success',"User Logout Successfully.");
        }else {
            Auth::logout();
            return redirect()->route('get:homepage')->with("success", "User Logout Successfully.");
        }
    }

    //get Admin change password form
    public function getAdminChangePassword()
    {
        return view('admin.auth.change_password');
    }

    //social Login
    public function redirectToProvider($provider)
    {
//        dd($provider);
        return Socialite::driver($provider)->redirect();
    }

    //social loggid user existing check function
    public function findOrCreateUser($user, $provider)
    {
        $authUser = Provider::where('login_id', $user->id)->whereNull('providers.deleted_at')->first();
        if ($authUser) {
            return $authUser;
        }
        $userstore = Provider::where('email', $user->email)->whereNull('providers.deleted_at')->first();
        if ($userstore) {
            return response()->json([
                "status" => 0,
            ]);
        }
        $userstore = new Provider();
        $userstore->name = $user->name;
        $userstore->email = $user->email;
        $userstore->login_id = $user->id;
        $userstore->login_type = $provider;
        $userstore->status = 0;
        $userstore->save();
        return $userstore;
    }

    //social Login callback function
    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->stateless()->user();
            $authUser = $this->findOrCreateUser($user, $provider);
            if ($decoded['status'] = json_decode($authUser) == false) {
                return redirect()->route('get:provider-admin:login')->with("error", "already login with this Id. Please login with different Id.");
            }
            Auth::logout();
            Auth::guard('on_demand')->login($authUser, true);
            if (Auth::guard('on_demand')->user()->status == 3) {
                return redirect()->route('get:provider-admin:service-register')->with("success", "Provider Admin Login Successfully.");
            }
            $store_service = Auth::guard('on_demand')->user()->checkStoreProviderId("on_demand");
            return redirect()->route('get:provider-admin:dashboard')->with("success", "Provider Admin Login Successfully.");

        } catch (Exception $e) {
            return redirect('auth/' . $provider);
        }
    }
}
