<?php

namespace App\Http\Controllers;

use App\model\CouponCodeDetail;
use App\model\GeneralSettings;
use App\model\PageSettings;
use App\model\Provider;
use App\model\ProviderVerification;
use App\model\TempUserBooking;
use App\model\User;
use App\model\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class HomeController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth');
    }

    public function index()
    {
        return view('home');
    }

    public function getTermsAndConditions(Request $request)
    {
        //1=user;
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%terms%")->where('type', 1)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data'));
        }
        return view('terms-and-condition-new');
    }

    public function getCuponcodeVerify(Request $request){

        return view('admin.pages.coupon_deals.coupon_code_verify');
    }

    public function postCuponcodeVerify(Request $request){

        $validator = Validator::make($request->all(),[
            "coupon_code" => "required",
            "contact_number" => "required",

        ]);
        if($validator->fails()){
            return redirect()->route('get:couponcode_verify')->with("error", $validator->errors()->first());
        }
        $contact_number = $request->get('full_number');
        $coupon_code = strtoupper($request->get('coupon_code'));
        $get_user_details = User::query()->where('status','=','1')
            ->where('contact_number','=',$contact_number)->first();
        if($get_user_details != Null){
            $get_counpon_code_details = CouponCodeDetail::query()->where('coupon_code','=',($coupon_code))
                ->where('user_id','=',$get_user_details->id)
                ->first();
            if($get_counpon_code_details != Null){
                if($get_counpon_code_details->status == "1"){
                    //already used
                    return redirect()->route('get:couponcode_verify')->with("error", "Coupon code already used");
                }else{
                    $curr_date = date("Y-m-d H:i:s");
                    if (strtotime($curr_date) > strtotime($get_counpon_code_details->code_expire_date)) {
                        //already expired
                        return redirect()->route('get:couponcode_verify')->with("error", "Coupon code has beed expired");
                    }else{
                        //already update to used
                        $get_counpon_code_details->status = 1;
                        $get_counpon_code_details->code_used_date = date("Y-m-d H:i:s");
                        $get_counpon_code_details->save();
                        return redirect()->route('get:couponcode_verify')->with("success", "Successfully Verify Coupon Code.");
                    }
                }
            }else{
                return redirect()->route('get:couponcode_verify')->with("error", "Coupon code Not Found");
            }
        }else{
            return redirect()->route('get:couponcode_verify')->with("error", "User Not Found");
        }
    }

    public function getProviderTermsAndConditions(Request $request)
    {
        //2=provider
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%terms%")->where('type', 2)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data'));
        }
        return view('terms-and-condition-new');
    }

    public function getDriverTermsAndConditions(Request $request)
    {
        //3=driver
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%terms%")->where('type', 3)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data'));
        }
        return view('terms-and-condition-new');
    }

    public function getStoreTermsAndConditions(Request $request)
    {
        //4=store
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%terms%")->where('type', 4)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data'));
        }
        return view('terms-and-condition-new');
    }

    public function getShopperTermsAndConditions(Request $request)
    {
        //5=shopper
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%terms%")->where('type', 5)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data'));
        }
        return view('terms-and-condition-new');
    }

    public function getPrivacyPolicy(Request $request)
    {
        //1=user;
        $title = "privacy policy";
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%privacy%")->where('type', 1)->first();
        if ($get_page_data != Null) {

            return view('terms-and-condition-new', compact('get_page_data', 'title'));
        }
        return view('terms-and-condition-new', compact('title'));
    }

    public function getDisclaimer(Request $request)
    {
        //1=user;
        $title = "disclaimer";
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%disclaimer%")->where('type', 1)->first();
        if ($get_page_data != Null) {

            return view('terms-and-condition-new', compact('get_page_data', 'title'));
        }
        return view('terms-and-condition-new', compact('title'));
    }

    public function getFaq(Request $request)
    {
        //1=user;
        $title = "faq";
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%faq%")->where('type', 1)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data', 'title'));
        }
        return view('terms-and-condition-new', compact('title'));
    }

    public function getProviderPrivacyPolicy(Request $request)
    {
        //2=provider
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%privacy%")->where('type', 2)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data'));
        }
        return view('terms-and-condition-new');
    }

    public function getDriverPrivacyPolicy(Request $request)
    {
        //3=driver
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%privacy%")->where('type', 3)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data'));
        }
        return view('terms-and-condition-new');
    }

    public function getStorePrivacyPolicy(Request $request)
    {
        //4=store
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%privacy%")->where('type', 4)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data'));
        }
        return view('terms-and-condition-new');
    }

    public function getShopperPrivacyPolicy(Request $request)
    {
        //5=shopper
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%privacy%")->where('type', 5)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data'));
        }
        return view('terms-and-condition-new');
    }

    public function getFile($filename)
    {
        $icon = GeneralSettings::query()->first();
        if ($icon != Null) {
            $icon = asset('assets/images/website-logo-icon/' . $icon->website_favicon);
        } else {
            $icon = '';
        }
        return "<head><link rel='icon' href='' type='image/x-icon'></head><body style='margin: 0px; background: #0e0e0e; text-align: center;'><img style='-webkit-user-select: none; margin-left: auto;margin-right: auto; position: relative; top: 50%; transform: translateY(-50%);' src='" . asset('/assets/images/provider-documents/' . $filename) . "'></body>";
    }

    public function getReturnURL(Request $request)
    {
        return "payment successfully done";
    }

    public function getIPNURL(Request $request)
    {
    }

    public function getSecurity(Request $request)
    {
        //1=user;
        $title = "Security";
        $get_page_data = PageSettings::query()->where('name', 'LIKE', "%security%")->where('type', 1)->first();
        if ($get_page_data != Null) {
            return view('terms-and-condition-new', compact('get_page_data', 'title'));
        }
        return view('terms-and-condition-new', compact('title'));
    }

    public function postDataDeletionStatus($reference){
        $user = User::query()->where('login_id',$reference)->first();
        if($user == Null){
            $provider = Provider::query()->where('login_id',$reference)->first();
            if($provider != NUll){
                return view('success_deletion');
            }
            return view('failed_deletion');
        } else{
            return view('success_deletion');
        }
    }

    /* -----------------------------------For Play-Store, App-Store Upload account_deletion-------------------------- */
    // provider_type = 1:store,2:driver,3:provider

    // getAccountDeletion login
    public function getAccountDeletion()
    {
        $guard = "";
        if (Auth::guard('user')->check()) {
            $guard = 'user';
            $user_details = User::query()->where('id', Auth::guard($guard)->id())->whereNull('deleted_at')->first();
        } elseif (Auth::guard('on_demand')->check()) {
            $guard = 'on_demand';
            $user_details = Provider::query()->where('id', Auth::guard($guard)->id())
                ->where('provider_type', '=', 3)
                ->whereNull('deleted_at')->first();
        } else {
            $user_details = null;
        }
        if ($user_details != null) {
            return redirect()->route('get:account:deletion:profile');
        }

        // account_deletion
        return view('account_deletion.login');
    }

    // store AccountDeletion login
//    public function postAccountDeletion(Request $request)
//    {
//
//        \Log::info("\n postAccountDeletion \n");
//        $validator = Validator::make($request->all(), [
//            'contact_number' => 'required',
//            'country_code' => 'required',
//            'password' => 'required',
//            'full_number' => 'nullable',
//            'roles' => 'required|numeric',
//        ]);
//
//        if ($validator->fails()) {
//            return redirect()->back()->with('error', $validator->errors()->first());
//        }
//        $settings = request()->get('general_settings');
//
//        $roles = $request->get('roles');
//        $country_code = $request->get('country_code');
//        $contact_number = $request->get('contact_number');
//        $password = Hash::make($request->get('password'));
//        $user_type = ($roles > 0) ? ($roles == 3) ? "on_demand" : "user" : "user";
//        if($user_type='user')
//        {
//            $super_admin = User::where('contact_number', $contact_number)->first();
//        }
//        else{
//            $super_admin = Provider::where('contact_number', $contact_number)->first();
//        }
//            if ($super_admin != null && Hash::check($request->get('password'), $super_admin->password)) {
//                if ($roles > 0) {
//                    $provider = Provider::query()->where('contact_number', '=', $contact_number)
//                        ->where('country_code', '=', $country_code)
//                        ->where('provider_type', '=', $roles)
//                        ->whereNull('deleted_at')->first();
//                    if ($provider == null) {
//                        //$provider = new Provider();
//                        return redirect()->route('get:account:deletion:login')->with('error', 'Your account is not registered with us please try again later.');
//                    }
//                    $provider->contact_number = $contact_number;
//                    $provider->country_code = $country_code;
//                    $provider->password = $password;
//                    $provider->provider_type = $roles;
//                    $provider->status = 1;
//                    $provider->save();
//                    Auth::guard($user_type)->login($provider);
//                }
//
//            else {
//                $user = User::query()->where('contact_number', '=', $contact_number)
//                    ->where('country_code','=',$country_code)
//                    ->whereNull('deleted_at')->first();
//                if ($user == null) {
//                 //  $user = new User();
//                    return redirect()->route('get:account:deletion:login')->with('error','Your account is not registered with us please try again later.');
//                }
//                $user->contact_number = $contact_number;
//                $user->country_code = $country_code;
//                $user->password = $password;
//                $user->status = 1;
//                $user->save();
//                Auth::guard($user_type)->login($user);
//            }
//
//            return redirect()->route('get:account:deletion:profile')->with('success', 'Login SuccessFully!');
//        }
//    }

    public function postAccountDeletion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_number' => 'required',
            'country_code' => 'required',
            'password' => 'required',
            'full_number' => 'nullable',
            'roles' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $settings = request()->get('general_settings');

        $roles = $request->get('roles');
        $country_code = $request->get('country_code');
        $contact_number = $request->get('contact_number');
        $password = Hash::make($request->get('password'));

        if ($roles > 0) {
            $provider = Provider::where('contact_number', $contact_number)
                ->where('country_code', $country_code)
                ->where('provider_type', $roles)
                ->whereNull('deleted_at')->first();

            if ($provider != null && Hash::check($request->get('password'), $provider->password)) {
                $provider->contact_number = $contact_number;
                $provider->country_code = $country_code;
                $provider->provider_type = $roles;
                $provider->status = 1;
                $provider->save();
                Auth::guard('on_demand')->login($provider);
                return redirect()->route('get:account:deletion:profile')->with('success', 'Login Successfully!');
            } else {
                return redirect()->route('get:account:deletion:login')->with('error', 'Your account is not registered with us, please try again later.');
            }

        }else{
            $user = User::where('contact_number', $contact_number)
                ->where('country_code', $country_code)
                ->whereNull('deleted_at')->first();

            if ($user != null && Hash::check($request->get('password'), $user->password)) {
                $user->contact_number = $contact_number;
                $user->country_code = $country_code;
                $user->status = 1;
                $user->save();
                Auth::guard('user')->login($user);
                return redirect()->route('get:account:deletion:profile')->with('success', 'Login Successfully!');
            }else {
                return redirect()->route('get:account:deletion:login')->with('error', 'Your account is not registered with us, please try again later.');
            }
        }


    }
    // get AccountDeletionVerification
    public function getAccountDeletionVerification(Request $request) {
        $guard = "";
        if (Auth::guard('user')->check()){
            $guard = 'user';
        }
        elseif (Auth::guard('on_demand')->check()) {
            $guard = 'on_demand';
        }
//        elseif (Auth::guard('store')->check()) {
//            $guard = 'store';
//        }
        else {
            Auth::logout();
            return redirect()->route('get:account:deletion:login')->with('error','Something went wrong');
        }
        return view('account_deletion.verification',compact('guard'));
    }

    // store AccountDeletionVerification
    public function postAccountDeletionVerification(Request $request) {
        $guard = "";
        if (Auth::guard('user')->check()){
            $guard = 'user';
            $user_details = User::query()->where('id',Auth::guard($guard)->id())->whereNull('deleted_at')->first();

            $get_token = UserVerification::query()->where('user_id', "=", $user_details->id)->first();
        }
        elseif (Auth::guard('on_demand')->check()) {
            $guard = 'on_demand';
            $user_details = Provider::query()->where('id',Auth::guard($guard)->id())
                ->where('provider_type','=',3)
                ->whereNull('deleted_at')->first();

            $get_token = ProviderVerification::query()->where('provider_id', "=", $user_details->id)->first();
        }
//
        else {
            Auth::logout();
            return redirect()->route('get:account:deletion:login')->with('error','Something went wrong');
        }

        $otp = $request->get('otp_1').$request->get('otp_2').$request->get('otp_3').$request->get('otp_4');
        $settings = request()->get('general_settings');
        if ($settings->is_otp_verification == 0){
            if ($otp == "1234") {
                $user_details->verified_at = date('Y-m-d H:i:s');
                $user_details->save();

                return redirect()->route('get:account:deletion:profile');
            } else {
                return redirect()->back()->with('error','Invalid OTP');
            }
        }
        try {
            if ($get_token != Null) {
                if($guard == "user"){
                    if ($otp == trim($get_token->token)) {
                        $user_details->verified_at = date('Y-m-d H:i:s');
                        $user_details->save();
                        UserVerification::where('user_id', $user_details->id)->delete();
                        return redirect()->route('get:account:deletion:profile');
                    } else {
                        return redirect()->back()->with('error','Invalid OTP');
                    }
                } else {
                    if ($otp == trim($get_token->token)) {
                        $user_details->verified_at = date('Y-m-d H:i:s');
                        $user_details->save();
                        ProviderVerification::where('provider_id', $user_details->id)->delete();
                        return redirect()->route('get:account:deletion:profile');
                    } else {
                        return redirect()->back()->with('error','Invalid OTP');
                    }

                }
            } else {
                return redirect()->back()->with('error','Invalid OTP');
            }
        } catch (\Exception $e) {
            Log::info($e);
            Session::flash('error', 'Something went wrong');
            return view('account_deletion.login');
//            return redirect()->route('get:account:deletion:login')->with('error','Something went wrong');
        }
    }

    // get AccountDeletion Resend VerificationCode
    public function getAccountDeletionRensendVerificationCode(Request $request) {
        $settings = request()->get('general_settings');
        if ($settings->is_otp_verification != 0) {
            $guard = ""; $id = 0;
            if (Auth::guard('user')->check()) {
                $guard = 'user';
                $id = Auth::guard($guard)->id();

                if ($id > 0) {
                    (new TokenClassApi())->sendUserSmsVerification($id);
                }

                return redirect()->back()->with('success', 'Otp sent successfully!');
            }
            elseif (Auth::guard('on_demand')->check()) {
                $guard = 'on_demand';
                $id = Auth::guard($guard)->id();

                if ($id > 0) {
                    (new TokenClassApi())->sendProviderSmsVerification($id);
                }

                return redirect()->back()->with('success', 'Otp sent successfully!');
            }
            elseif (Auth::guard('store')->check()) {
                $guard = 'store';
                $id = Auth::guard($guard)->id();

                if ($id > 0) {
                    (new TokenClassApi())->sendProviderSmsVerification($id);
                }

                return redirect()->back()->with('success', 'Otp sent successfully!');
            } else {
                Auth::logout();
                return redirect()->route('get:account:deletion:login')->with('error', 'Something went wrong');
            }
        }

        return redirect()->back()->with('success', 'Otp sent successfully!');
    }

    // get Account Deletion Logout
    public function getAccountDeletionLogout($guard) {
        Auth::guard($guard)->logout();
        return redirect()->route('get:account:deletion:login');
    }

    // get Account Deletion Profile
    public function getAccountDeletionProfile() {
        $guard = "";
        if (Auth::guard('user')->check()){
            $guard = 'user';
            $user_details = User::query()->where('id',Auth::guard($guard)->id())->whereNull('deleted_at')->first();
        }
        elseif (Auth::guard('on_demand')->check()) {
            $guard = 'on_demand';
            $user_details = Provider::query()->where('id',Auth::guard($guard)->id())
                ->where('provider_type','=',3)
                ->whereNull('deleted_at')->first();
        }

        else {
            Auth::logout();
            return redirect()->route('get:account:deletion:login')->with('error','Something went wrong');
        }

        return view('account_deletion.profile',compact('guard','user_details'));
    }

    // store Account Deletion Profile
    public function postAccountDeletionDeleteAccount(Request $request) {
        $guard=$request->get('guard');
        $id=$request->get('id');

        if ($guard == "user") {
            User::query()->whereKey($id)->delete();
        } else {
            Provider::query()->whereKey($id)->delete();
        }
        Auth::logout();
        return redirect()->route('get:account:deletion:login')->with('success','Account Delete Successfully!');
    }

    //social Account Deletion Login
    public function redirectToProvider($guards,$provider)
    {
        try {
            if($guards == "user" || $guards == "on_demand") {
                $previous_url = url()->previous();
                session()->put('login_previous_url', $previous_url);
            }
            return Socialite::driver($provider)->with(['state' => $guards])->redirect();
        } catch (\Exception $e) {
            Auth::logout();
            request()->session()->forget('login_previous_url');
            return redirect()->back();
        }
    }

    //social Account Deletion Login callback function
    public function handleCallback(Request $request, $provider) {
        try {
            $user = Socialite::driver($provider)->user();
        } catch (InvalidStateException $e) {
            $user = Socialite::driver($provider)->stateless()->user();
        }

        $guards = $request->get("state");

        if($guards == "on_demand") {
            $userstore = Provider::query()->where('login_id', $user->id)->first();
            if ($userstore == null) {
                // $userstore = new Provider(); //for new provider
                return redirect()->route('get:account:deletion:login')->with('error','Your account is not registered with us please try again later.');
            }
            $userstore->provider_type = 3;
        }

        elseif($guards == "user") {
            $userstore = User::query()->where('login_id',$user->id)->first();
            if ($userstore == null) {
                //$userstore = new User(); //for new provider new user

                return redirect()->route('get:account:deletion:login')->with('error','Your account is not registered with us please try again later.');
            }
        }
        else {
            return redirect()->route("get:account:deletion:login");
        }
        //for new user
        $userstore->first_name = $user->name;
        $userstore->email = $user->email;
        $userstore->login_id = $user->id;
        $userstore->login_type = $provider;
        $userstore->status = 0;
        $userstore->save();
        //for new user

        Auth::guard($guards)->login($userstore);

        return redirect()->route('get:account:deletion:profile');
    }

    /* -----------------------------------End For Play-Store, App-Store Upload account_deletion---------------------- */
}
