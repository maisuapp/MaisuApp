<?php

namespace App\Http\Controllers;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Classes\TokenClassApi;
use App\Http\Requests\StoreProductExcel;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\StoreRequest;
use App\Imports\StoreProductDetailsImport;
use App\model\GeneralSettings;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderBankDetails;
use App\model\ProviderDocuments;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ProviderVerification;
use App\model\RequiredDocuments;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\ShopperProviderDetails;
use App\model\StoreDetails;
use App\model\StoreProductCategory;
use App\model\StoreProductCustomizeCategory;
use App\model\StoreProductDetails;
use App\model\StoreProductOptions;
use App\model\StoreProductSize;
use App\model\StoreProductToppings;
use App\model\StoreTimings;
use App\model\TransportDriverDetails;
use App\model\TransportRideBook;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserProductBooking;
use App\model\UserProductBookingOptions;
use App\model\UserProductBookingQuantity;
use App\model\UserProductBookingSize;
use App\model\UserProductBookingStatus;
use App\model\UserProductBookingToppings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Twilio\Rest\Client;

class StoreAdminController extends Controller
{
    private $adminClass;
    private $tokenClassApi;
    private $store_order_status;
    private $notificationClass;
    private $store_service_id_array;
    private $food_delivery;

    public function __construct(TokenClassApi $tokenClassApi, AdminClass $adminClass, NotificationClass $notificationClass)
    {
        $this->middleware('auth');
        $this->store_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->adminClass = $adminClass;
        $this->notificationClass = $notificationClass;
        $this->tokenClassApi = $tokenClassApi;
        $this->store_order_status = ['', 'pending', 'approved', 'rejected', 'cancelled', 'processing', 'approved-by-driver', 'arrived-driver', 'ongoing', 'completed', 'failed'];
        $this->food_delivery = 5;
    }

    public function getDashboard(Request $request)
    {
        $store_service = $this->adminClass->checkStoreProviderStatus();
        if ($store_service != Null) {
            return $store_service;
        }
        $total_user = User::count();
        $total_sales = 0;
        $total_rev = 0;

        $view = view('admin.pages.store_services.store_dashboard', compact('total_user', 'total_sales', 'total_rev'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getStoreNotVerified(Request $request)
    {
        $store_service = Auth::guard('store')->user()->checkStoreProviderId();
        $provider_id = Auth::guard('store')->user()->id;
        $provider_status = Auth::guard('store')->user()->status;
        $verified = Auth::guard('store')->user()->verified_at;
        if ($verified == Null) {
            $view = view('admin.pages.store_services.store_not_verify');
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        }
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
        return redirect()->route('get:store-admin:dashboard');
    }

    public function getStoreResendVerificationLink(Request $request)
    {
        $provider_id = Auth::guard('store')->user()->id;
        ProviderVerification::where('provider_id', $provider_id)->delete();
        $this->tokenClassApi->sendProviderSmsVerification($provider_id);

        Session::flash('success', 'New Verification Link Send to your Register Contact Number.');
        $store_service = $this->adminClass->checkStoreProviderStatus();
        if ($store_service != Null) {
            return $store_service;
        }
        return redirect()->route('');
    }

    public function getStoreAccountApprovalPending(Request $request)
    {
        $store_service = Auth::guard('store')->user()->checkStoreProviderId();
        $provider_id = Auth::guard('store')->user()->id;
        $provider_status = Auth::guard('store')->user()->status;
        $verified = Auth::guard('store')->user()->verified_at;
        if ($verified == Null) {
            return redirect()->route('get:store-admin:not_verified');
        }

        $status = $store_service['status'];
        $is_check = 0;
        if (isset($status) && $status > 0){
            $is_check = 1;
        }
        if ($is_check == 0) {
            if ($provider_status != 1) {
                if ($provider_status == 0) {
                    $view = view('admin.pages.store_services.store_pending_service_status');
                    if ($request->ajax()) {
                        $view = $view->renderSections();
                        return $this->adminClass->renderingResponce($view);
                    }
                    return $view;
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
        } else {
            $store_id = $store_service['id'];
            if ($store_id == 0) {
                return redirect()->route('get:store-admin:service-register');
            } else {
                $provider_service = ProviderServices::whereIN('service_cat_id', $this->store_service_id_array)->where('provider_id', $provider_id)->first();
                if ($provider_service != Null) {
                    if ($provider_service->status != 1) {
                        if ($provider_service->status == 0) {
                            return view('admin.pages.store_services.store_pending_service_status');
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

        return redirect()->route('get:store-admin:dashboard');
    }

    public function postStoreOtpVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "store_id" => "required",
                "otp_1" => "required|numeric",
                "otp_2" => "required|numeric",
                "otp_3" => "required|numeric",
                "otp_4" => "required|numeric",
            ]
        );
        if ($validator->fails()) {
            Session::flash('error', 'OTP Fields requird!');
            return redirect()->back();
        }
        $store_details = Provider::where('id', $request->get('store_id'))->first();
        if ($store_details != Null) {
            $otp = $request->get('otp_1') . $request->get('otp_2') . $request->get('otp_3') . $request->get('otp_4');
            if ($otp == "1234") {
                ProviderVerification::where('provider_id', $store_details->id)->delete();

                $store_details->verified_at = date('Y-m-d H:i:s');
                $store_details->save();
                $store_service = $this->adminClass->checkStoreProviderStatus();
                if ($store_service != Null) {
                    return $store_service;
                }
                return redirect()->back();
            } else {
                Session::flash('error', 'Entered OTP is Incorrect!');
                return redirect()->back();
            }

            /*$get_token = ProviderVerification::where('provider_id', $store_details->id)->first();
            if ($get_token == Null) {
                Session::flash('error', 'Something went to wrong!');
                return redirect()->back();
            }
            $settings = GeneralSettings::first();
            if ($settings == Null || $settings->twilio_service_key == Null || $settings->twilio_auth_token == Null || $settings->twilio_verify_service_key == Null) {
                Session::flash('error', 'Something went to wrong!');
                return redirect()->back();
            }
            try {
                $twilio = new Client($settings->twilio_service_key, $settings->twilio_auth_token);
                $option = [
                    'To' => $store_details->contact_number,
                    'VerificationSid' => $get_token->token,
                ];
                $verification_check = $twilio->verify->v2->services($settings->twilio_verify_service_key)
                    ->verificationChecks
                    ->create($otp, $option);
                if ($verification_check->status == "approved") {
                    $verification_sid = $verification_check->sid;
                    if ($verification_sid != $get_token->token) {
                        Session::flash('error', 'Something went to wrong!');
                        return redirect()->back();
                    }
                    ProviderVerification::where('provider_id', $store_details->id)->delete();

                    $store_details->verified_at = date('Y-m-d H:i:s');
                    $store_details->save();
                    $store_service = $this->adminClass->checkStoreProviderStatus();
                    if ($store_service != Null) {
                        return $store_service;
                    }
                    return redirect()->back();

                } else {
                    Session::flash('error', 'Entered OTP is Incorrect!');
                    return redirect()->back();
                }
            } catch (\Exception $e) {
                Session::flash('error', 'something went to wrong!');
                return redirect()->back();
            }*/
        } else {
            Session::flash('error', 'Store not found!.');
            return redirect()->back();
        }
    }

    public function getStoreAccountRejected(Request $request)
    {
        $store_service = Auth::guard('store')->user()->checkStoreProviderId();
        $provider_id = Auth::guard('store')->user()->id;
        $provider_status = Auth::guard('store')->user()->status;
        $verified = Auth::guard('store')->user()->verified_at;
//        if ($verified == Null) {
//            return redirect()->route('get:store-admin:not_verified');
//        }
//        $status = $store_service['status'];

        $status = $store_service['status'];
        $is_check = 0;
        if (isset($status) && $status > 0) {
            $is_check = 1;
        }
        if ($is_check == 0) {
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
        } else {
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
                            $view = view('admin.pages.store_services.store_rejected_service_status');
                            if ($request->ajax()) {
                                $view = $view->renderSections();
                                return $this->adminClass->renderingResponce($view);
                            }
                            return $view;
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

        return redirect()->route('get:store-admin:dashboard');
    }

    public function getStoreServiceRegister(Request $request)
    {
        $store_service = Auth::guard('store')->user()->checkStoreProviderId();
        $provider_id = Auth::guard('store')->user()->id;
        $provider_status = Auth::guard('store')->user()->status;
        $verified = Auth::guard('store')->user()->verified_at;
        if ($verified == Null) {
            return redirect()->route('get:store-admin:not_verified');
        }
//        $status = $store_service['status'];
        if ($provider_status != 1) {
            if ($provider_status == 0) {
                return redirect()->route('get:store-admin:account_approval_pending');
            } elseif ($provider_status == 3) {
                $service_category = ServiceCategory::query()->select('id', 'name')->where('category_type','=', 2)->where('status','=', 1)->get();
                $required_document = [];
                foreach ($service_category as $category) {
                    $required_document[$category->id] = RequiredDocuments::query()->where('service_cat_id','=', $category->id)->where('status','=', 1)->get();
                }
                $view = view('admin.pages.store_services.store.service_register', compact('service_category', 'required_document'));
                return $view;
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
            $service_category = ServiceCategory::query()->select('id', 'name')->where('category_type','=', 2)->where('status','=', 1)->get();
            $required_document = [];
            foreach ($service_category as $category) {
                $required_document[$category->id] = RequiredDocuments::query()->where('service_cat_id', '=',$category->id)->where('status','=', 1)->get();
            }
            $view = view('admin.pages.store_services.store.service_register', compact('service_category', 'required_document'));
            return $view;
        } else {
            $provider_service = ProviderServices::query()->whereIN('service_cat_id', $this->store_service_id_array)->where('provider_id', $provider_id)->first();
            if ($provider_service != Null) {
                if ($provider_service->status != 1) {
                    if ($provider_service->status == 0) {
                        return redirect()->route('get:store-admin:account_approval_pending');
                    } elseif ($provider_service->status == 3) {
                        $view = view('admin.pages.store_services.store_rejected_service_status');
                        if ($request->ajax()) {
                            $view = $view->renderSections();
                            return $this->adminClass->renderingResponce($view);
                        }
                        return $view;
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

    public function postStoreServiceRegister(Request $request)
    {

        $id = $request->get('provider_id');
        $provider_details = Provider::query()->where('id', $id)->where('status', 3)->first();
        $service_category = ServiceCategory::query()->where('id', $request->get('service_category'))->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->route('get:store-admin:service-register');
        }
        if ($provider_details == Null) {
            Session::flash('error', 'something went to wrong!');
            return redirect()->route('get:store-admin:service-register');
        }
        if ($provider_details->email == Null) {
            $provider_details->email = $request->get('email');
        }
        if ($provider_details->contact_number == Null) {
            $provider_details->contact_number = $request->get('contact_number');
        }
        $provider_details->service_radius = $request->get('delivery_radius');
        $provider_details->gender = $request->get('gender');
        $provider_details->status = 0;
        $provider_details->save();

        $store_open_times = $request->get('open_time');
        $store_close_times = $request->get('close_time');

        $provider_service = new ProviderServices();
        $provider_service->provider_id = $provider_details->id;
        $provider_service->service_cat_id = $service_category->id;
        $provider_service->current_status = 1;
        $provider_service->status = 0;
        $provider_service->save();
        try {
            $store_details = new StoreDetails();
            $store_details->provider_service_id = $provider_service->id;
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
            $store_details->order_min_amount = ($request->get('order_min_amount') != Null || $request->get('order_min_amount') != 0) ? $request->get('order_min_amount') : 0;
            $store_details->packaging_charges = ($request->get('packaging_charges') != Null) ? $request->get('packaging_charges') : 0;
            $store_details->address = $request->get('store_address');
            $store_details->address_lat = $request->get('address_lat');
            $store_details->address_long = $request->get('address_long');
            $store_details->eta_delivery_time = $request->get('eta_delivery_time');
            $store_details->description = ($request->get('description') != Null) ? $request->get('description') : Null;
            $store_details->slug = "";
            $store_details->save();
            try {
                $days = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
                if ($store_open_times[0] != Null && $store_close_times[0] != Null) {
                    for ($i = 0; $i < 7; $i++) {
                        $update_store_timing = new StoreTimings();
                        $update_store_timing->store_id = $store_details->id;
                        $update_store_timing->day = strtoupper(trim($days[$i]));
                        $update_store_timing->store_open_time = Date('H:i:s', strtotime($store_open_times[0]));
                        $update_store_timing->store_close_time = Date('H:i:s', strtotime($store_close_times[0]));
                        $update_store_timing->save();
                    }
                } else {
                    for ($i = 0; $i < 7; $i++) {
                        if ($store_open_times[$i + 1] != Null && $store_close_times[$i + 1] != Null) {
                            $update_store_timing = new StoreTimings();
                            $update_store_timing->store_id = $store_details->id;
                            $update_store_timing->day = strtoupper(trim($days[$i]));
                            $update_store_timing->store_open_time = Date('H:i:s', strtotime($store_open_times[$i + 1]));
                            $update_store_timing->store_close_time = Date('H:i:s', strtotime($store_close_times[$i + 1]));
                            $update_store_timing->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                $provider_details->status = 3;
                $provider_details->save();
                StoreTimings::where('store_id', $store_details->id)->delete();
                StoreDetails::where('provider_service_id', $provider_service->id)->delete();
                ProviderServices::where('provider_id', $provider_details->id)->delete();
                Session::flash('error', 'something went to wrong!');
                return redirect()->back();
            }
            try {
                if ($request->hasfile('documents')) {
                    foreach ($request->file('documents') as $key => $image) {
                        $required_document = RequiredDocuments::where('id', $key)->where('service_cat_id', $provider_service->service_cat_id)->first();
                        if ($required_document != Null) {
                            $destinationPath = public_path('/assets/images/provider-documents/');
                            $img = Image::make($image->getRealPath());
                            $img->orientate();
                            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $image->getClientOriginalExtension();
                            $img->resize(500, 500, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($destinationPath . $file_new);
                            $documents = new ProviderDocuments();
                            $documents->provider_service_id = $provider_service->id;
                            $documents->req_document_id = $required_document->id;
                            $documents->document_file = $file_new;
                            $documents->status = 0;
                            $documents->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                $provider_details->status = 3;
                $provider_details->save();
                ProviderDocuments::where('provider_service_id', $provider_service->id)->delete();
                StoreTimings::where('store_id', $store_details->id)->delete();
                StoreDetails::where('provider_service_id', $provider_service->id)->delete();
                ProviderServices::where('provider_id', $provider_details->id)->delete();
                Session::flash('error', 'something went to wrong!');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            $provider_details->status = 3;
            $provider_details->save();
            StoreDetails::query()->where('provider_service_id', $provider_service->id)->delete();
            ProviderServices::query()->where('provider_id', $provider_details->id)->delete();
            return redirect()->back();
        }

        $provider_details->status = 1;
        $provider_details->save();
        $provider_service->status = 1;
        $provider_service->save();

        $general_settings = request()->get("general_settings");
        if ($general_settings !=  Null) {
            if ($general_settings->send_mail == 1 && $general_settings->send_receive_email != Null) {
                try{
                    $store_name =  ($store_details->name != "")? $store_details->name : "";
                    $mail_type = "admin_new_store_signup";
                    $to_mail = $general_settings->send_receive_email;
                    $subject = "New Store Registered";
                    $disp_data = array("##store_name##"=>$store_name );
                    $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                }
                catch (\Exception $e){}
            }
        }

        Session::flash('success', 'store details add successfully!');
        return redirect()->route('get:store-admin:dashboard');
    }

    public function getStoreCheckUniqueEmail(Request $request)
    {
        $check_email = Provider::where('email', $request->input('email'))->where('id', '!=', $request->get('id'))->first();
        if (count($check_email) >= 1) {
            return "taken";
        } else {
            return "not_taken";
        }
    }

    public function getStoreCheckUniqueContactNumber(Request $request)
    {
        $check_contact_number = Provider::where('contact_number', $request->input('contact_number'))->where('id', '!=', $request->get('id'))->first();
        if (count($check_contact_number) >= 1) {
            return "taken";
        } else {
            return "not_taken";
        }
    }

    public function getStoreServiceDispatcher(Request $request, $slug)
    {
        $service_status = $this->adminClass->checkStoreProviderStatus();
        if ($service_status != Null) {
            return $service_status;
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        if ($slug == "new") {
            $status = [1];
        } elseif ($slug == "approved") {
            $status = [2];
        } elseif ($slug == "processing") {
            $status = [5, 7];
        } elseif ($slug == "ongoing") {
            $status = [8];
        } elseif ($slug == "completed") {
            $status = [9];
        } elseif ($slug == "cancelled") {
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
            'user_store_product_booking.user_refund_status',
            'store_details.image as store_banner',
            'store_details.name as store_name',
            'store_details.address as pickup_address')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->where('user_store_product_booking.status', '!=', 11)
            ->where('user_store_product_booking.store_id', $store_id)
            ->whereIN('user_store_product_booking.status', $status)
            ->where('user_store_product_booking.created_at', '>=', $date)
            ->orderBy('user_store_product_booking.id', "desc")
            ->get();
        $service_category = ServiceCategory::where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $pending_orders = UserProductBooking::where('store_id', $store_id)->whereIN('user_store_product_booking.status', $new)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $approved_orders = UserProductBooking::where('store_id', $store_id)->whereIN('user_store_product_booking.status', $approved)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $processing_orders = UserProductBooking::where('store_id', $store_id)->whereIN('user_store_product_booking.status', $processing)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $ongoing_orders = UserProductBooking::where('store_id', $store_id)->whereIN('user_store_product_booking.status', $ongoing)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $completed_orders = UserProductBooking::where('store_id', $store_id)->whereIN('user_store_product_booking.status', $completed)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $cancelled_orders = UserProductBooking::where('store_id', $store_id)->whereIN('user_store_product_booking.status', $cancelled)->where('created_at', '>=', $date)->where('status', '!=', 11)->count();
        $order_slug = $slug;

        $view = view('admin.pages.store_services.store_dispatcher', compact('slug', 'service_category', 'store_order_list', 'pending_orders', 'approved_orders', 'processing_orders', 'ongoing_orders', 'completed_orders', 'cancelled_orders', 'order_slug'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;

    }

    public function getStoreOrderDetails(Request $request, $order_id)
    {
        $service_status = $this->adminClass->checkStoreProviderStatus();
        if ($service_status != Null) {
            return $service_status;
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $slug = $service_category->slug;
        $order_details = UserProductBooking::query()->select("user_store_product_booking.*","used_promocode_details.promocode_name","used_promocode_details.discount_amount")
            ->leftJoin('used_promocode_details','used_promocode_details.id','user_store_product_booking.promo_code')
            ->where('user_store_product_booking.id', '=',$order_id)->first();
        if ($order_details == Null) {
            return redirect()->back();
        }
        $store_details = StoreDetails::query()->select('address')->where('id','=', $order_details->store_id)->first();
        $user_details = User::query()->select('contact_number')->where('id','=', $order_details->user_id)->first();
        $get_order_status = UserProductBookingStatus::query()->select('status', 'created_at')->where('order_id', '=',$order_details->id)->get();
        $order_status = $this->store_order_status;
        $product_details = UserProductBookingQuantity::query()->select('id','product_name', 'product_cat_name', 'num_of_items', 'price_for_one')->where('order_id', '=',$order_details->id)->get()->groupBy('product_cat_name');

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

        $view = view('admin.pages.store_services.store_order_details', compact('slug', 'product_option_list','shopper_details', 'order_details',
            'get_order_status', 'order_status', 'product_details', 'store_details', 'user_details', 'driver_details',
            'service_category','orders_status'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getEditStore(Request $request)
    {
        $service_status = $this->adminClass->checkStoreProviderStatus();
        if ($service_status != Null) {
            return $service_status;
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }

        $store_details = StoreDetails::select('store_details.id as store_id',
            'provider_services.id as provider_service_id',
            'providers.id as provider_id',
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
            'store_details.is_private_driver_notification as is_private_driver_notification',
            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as contact_person_name"),
            'providers.first_name',
            'providers.last_name',
            'providers.email',
            'providers.first_name',
            'providers.last_name',
            'providers.avatar as profile_image',
            'providers.contact_number',
            'providers.country_code',
            'providers.gender',
            'provider_services.current_status',
            'provider_services.status'
        )
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('provider_services.service_cat_id', $service_category->id)
            ->where('store_details.id', $store_id)->first();
        $bank_details = ProviderBankDetails::where('provider_id', $store_details->provider_id)->first();
        $store_timings = StoreTimings::select('day', 'store_open_time', 'store_close_time')->where('store_id', $store_details->store_id)->get();
        $store_open_timings = $store_timings->pluck('store_open_time', 'day')->toArray();
        $store_close_timings = $store_timings->pluck('store_close_time', 'day')->toArray();
        if ($request->ajax()) {
            $view = view('admin.pages.store_services.store.form', compact( 'service_category', 'store_details', 'bank_details', 'store_open_timings', 'store_close_timings'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.store_services.store.form', compact( 'service_category', 'store_details', 'bank_details', 'store_open_timings', 'store_close_timings'));
    }

    public function getUpdateStore(StoreRequest $request)
    {
        $service_status = $this->adminClass->checkStoreProviderStatus();
        if ($service_status != Null) {
            return $service_status;
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_id = $request->get('id');
        $store_open_times = $request->get('open_time');
        $store_close_times = $request->get('close_time');
//        $provider_id = $request->get('provider_id');
        $bank_name = $request->get('bank_name');
//        if ($store_id != Null) {
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
        $provider_details->provider_type = 1;
//        $provider_details->last_name = ucwords(trim(strtolower($request->get('last_name'))));

//        $provider_details->name = ucwords(trim(strtolower($request->get('contact_person_name'))));

        $provider_details->service_radius = $request->get('delivery_radius');
        $provider_details->gender = $request->get('gender');
        $provider_details->save();
//        } else {
//            $provider_details = new Provider();
//            $provider_details->name = ucwords(trim(strtolower($request->get('contact_person_name'))));
//            $provider_details->email = $request->get('email');
//            $provider_details->contact_number = $request->get('contact_number');
//            $provider_details->verified_at = Date('Y-m-d h:i:s');
//            $provider_details->last_active = Date('Y-m-d h:i:s');
//            $provider_details->password = Hash::make($request->get('password'));
//            $provider_details->login_type = "email";
//            $provider_details->status = 1;
//            $provider_details->service_radius = $request->get('delivery_radius');
//            $provider_details->gender = $request->get('gender');
//            $provider_details->save();
//
//            $provider_service = new ProviderServices();
//            $provider_service->provider_id = $provider_details->id;
//            $provider_service->service_cat_id = $service_category->id;
//            $provider_service->current_status = $request->get('current_status');
//            $provider_service->status = 1;
//            $provider_service->save();
//
//            $store_details = new StoreDetails();
//            $store_details->provider_service_id = $provider_service->id;
//        }
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
        $view = redirect()->route('get:store-admin:edit_store');
        if ($store_id == Null) {
            Session::flash('success', 'store details add successfully!');
        } else {
            Session::flash('success', 'store details updated successfully!');
        }
        return $view;
    }

    public function getStoreOrderList(Request $request, $status)
    {
        $service_status = $this->adminClass->checkStoreProviderStatus();
        if ($service_status != Null) {
            return $service_status;
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
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
            $status = [4, 10];
        } else {
            $status = [];
        }
        if (count($status) > 0) {
            $order_list = UserProductBooking::select('id', 'user_name', 'store_name', 'total_pay', 'status')->where('service_cat_id', $service_category->id)->where('status', '!=', 11)->whereIN('status', $status)->get();
        } else {
            $order_list = UserProductBooking::select('id', 'user_name', 'store_name', 'total_pay', 'status')->where('service_cat_id', $service_category->id)->where('status', '!=', 11)->get();
        }
        $order_status = $this->store_order_status;
        $view = view('admin.pages.store_services.store_order_list', compact('order_list', 'service_category', 'order_status'));
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
                'message' => 'Data not found'
            ]);
        }
        $user_details = User::query()->where('id', $store_order->user_id)->first();
        if ($store_order->status == 3 || $store_order->status == 4) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
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
                    $store_order->cancel_by = "store";
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
                if ($store_order->status == 2) {
                    $order_status = $store_order->status;
                    $store_order->otp = null;
                    $store_order->status = $update_status;
                    $store_order->cancel_by = "store";
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
                    if ($store_order->shopper_type == 1) {
                        $store_order->order_shopper_status = 8;
                        $store_order->save();
                        $shopper_provider_details = Provider::query()->select('providers.device_token', 'providers.login_device')
                            ->join('shopper_provider_details', 'shopper_provider_details.provider_id', '=', 'providers.id')
                            ->where('shopper_provider_details.id', $store_order->shopper_id)->first();
                        if ($shopper_provider_details != Null) {
                            $this->notificationClass->shopperCancelNotification($store_order->id, $shopper_provider_details->device_token, $store_order->service_cat_id, $shopper_provider_details->login_device);
                        }

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
//                        return $current_date_time."--".date("Y-m-d H:i:s",$beforeOneHour);
                        if($current_date_time < $beforeOneHour ){
                            return response()->json([
                                'success' => false,
                                'message' => 'Sorry, its Schedule Order. You can start before 1 hours of schedule time.'
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
                    if ($store_order->taken_away_type == 2) {
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

    public function getStoreProductList(Request $request)
    {
//        $service_status = $this->adminClass->checkStoreProviderStatus();
//        if ($service_status != Null) {
//            return $service_status;
//        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::select('store_details.id', 'store_details.name')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->where('store_details.id', $store_id)->where('provider_services.service_cat_id', $service_category->id)->first();
        if ($store_details == Null) {
            return redirect()->back();
        }
        $store_products = StoreProductDetails::select('store_product_details.id', 'store_product_details.name', 'store_product_details.status', 'store_product_category.name as category_name')
            ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
            ->where('store_product_details.store_id', $store_details->id)->orderBy('store_product_details.id','desc')->get();
        if ($request->ajax()) {
            $view = view('admin.pages.store_services.products.manage', compact( 'service_category', 'store_details', 'store_products'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.store_services.products.manage', compact( 'service_category', 'store_details', 'store_products'));
    }

    public function getAddStoreProduct(Request $request)
    {
//        $service_status = $this->adminClass->checkStoreProviderStatus();
//        if ($service_status != Null) {
//            return $service_status;
//        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::query()->where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service category not found!');
            return redirect()->back();
        }
        $is_sub_cat_flow = $service_category->is_sub_cat_flow- 0;
        $product_categories = StoreProductCategory::query()->select('id', 'name', 'status')->where('service_cat_id','=', $service_category->id)->where('parent_id','=', 0)->get();
        $view = view('admin.pages.store_services.products.form', compact('service_category', 'store_id', 'is_sub_cat_flow', 'product_categories'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //edit store product
    public function getEditStoreProduct(Request $request, $id)
    {
        $this->adminClass->checkStoreProviderStatus();
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::query()->where('id', $store_cat_id)->first();
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


        //$product_categories = StoreProductCategory::select('id', 'name', 'status')->where('service_cat_id', $service_category->id)->get();


        $store_id = $product_details->store_id;
        if ($service_category->id == $this->food_delivery) {
            $customize_category = StoreProductCustomizeCategory::query()->where('product_id', $product_details->id)->get();
            $customize_size = StoreProductSize::query()->where('product_id', $product_details->id)->get();
            $customize_options = StoreProductOptions::query()->where('product_id', $product_details->id)->get();
            $customize_toppings = StoreProductToppings::query()->where('product_id', $product_details->id)->get();

            $view = view('admin.pages.store_services.products.form', compact('service_category', 'store_id', 'is_sub_cat_flow', 'product_categories', 'product_details', 'customize_category', 'customize_size', 'customize_options', 'customize_toppings'));
        } else {
            $view = view('admin.pages.store_services.products.form', compact('service_category', 'store_id', 'is_sub_cat_flow', 'product_categories', 'product_details'));
        }
        return $view;
    }

    public function getUpdateStoreProduct(StoreProductRequest $request)
    {
//        $service_status = $this->adminClass->checkStoreProviderStatus();
//        if ($service_status != Null) {
//            return $service_status;
//        }
        $product_id = $request->get('id');
        $store_details = StoreDetails::where('id', $request->get('store_id'))->first();
        $service_category = ServiceCategory::where('id', $request->get('service_cat_id'))->first();
        if ($store_details == Null || $service_category == Null) {
            Session::flash('error', 'store id not exists!');
            return redirect()->back();
        }
        $store_id = $store_details->id;
        if ($product_id != Null) {
            Session::flash('success', 'product details update successfully!');
            $product_details = StoreProductDetails::where('id', $product_id)->first();
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

        // insert toppings data
        if ($service_category->id == $this->food_delivery){
            $product_id = $product_details->id;
            $customize_category = $request->get('customize_category');
            $customize_size = $request->get('size');
            $customize_options = $request->get('options');
            $customize_toppings = $request->get('toppings');
            if ($customize_category != Null) {
                $fetch_customize_category = (array_column($customize_category, 'type', 'name'));
                $fetch_customize_category_id = (array_column($customize_category, 'id', 'name'));
//                $fetch_customize_category_max_selection = (array_column($customize_category, 'max_selection', 'name'));
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
//                        $max_selection = $fetch_customize_category_max_selection[$category];
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
                        $category_details->max_selection =  0;
                        $category_details->name = $category;
                        $category_details->selection_type = $category_type;
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
//                        $max_selection = $fetch_customize_category_max_selection[$category];
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
                        $category_details->name = $category;
                        $category_details->selection_type = $category_type;
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

        return redirect()->route('get:store-admin:store_product_list');
    }

    public function getStoreProductChangeStatus(Request $request)
    {
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
        $id = $request->get('id');
        if ($id == Null) {
            Session::flash('error', 'product Not Found!');
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $product_details = StoreProductDetails::query()->where('id','=', $id)->first();
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
//            Session::flash('success', $service_sub_category->name . ' category disabled successfully!');
        } else {
            $product_details->is_recommended = 1;
            $product_details->save();
//            Session::flash('success', $service_sub_category->name . ' category enable successfully!');
        }
        return response()->json([
            'success' => true,
            'status' => $product_details->is_recommended
        ]);
    }

    public function getStoreProductSubCategoryList(Request $request) {
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

    public function getDeleteStoreProduct(Request $request)
    {
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

    //topping data
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


    public function getStoreProviderDocument(Request $request)
    {
        $store_service = Auth::guard('store')->user()->checkStoreProviderId();
        $provider_id = Auth::guard('store')->user()->id;
        $provider_status = Auth::guard('store')->user()->status;
        $verified = Auth::guard('store')->user()->verified_at;
//        if ($verified == Null) {
//            return redirect()->route('get:store-admin:not_verified');
//        }
        if ($provider_status != 1) {
            if ($provider_status == 3) {
                return redirect()->route('get:store-admin:service-register');
            }
            if ($provider_status == 2) {
                Auth::logout();
                Session::flash('error', 'Your account is currently blocked, so not authorised to allow any activity!!');
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
                    if ($provider_service->status == 2) {
                        Auth::logout();
                        Session::flash('error', 'Your account is currently blocked, so not authorised to allow any activity!!');
                        return redirect()->route('post:store-admin:login');
                    }
                }
            }
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::where('id', $store_cat_id)->first();
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
        if ($request->ajax()) {
            $view = view('admin.pages.store_services.document.form', compact( 'required_documents', 'provider_documents'))->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return view('admin.pages.store_services.document.form', compact( 'required_documents', 'provider_documents'));
    }

    public function postStoreProviderDocument(Request $request)
    {
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $slug = $request->get('slug');

        $store_details = StoreDetails::where('id', $store_id)->first();
        if ($store_details == Null) {
            return redirect()->back();
        }
        $get_document = RequiredDocuments::where('id', $slug)->where('service_cat_id', $store_cat_id)->first();
        if ($get_document != Null) {
            $find_document = ProviderDocuments::where('provider_service_id', $store_details->provider_service_id)->where('req_document_id', $get_document->id)->first();
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
                }
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
                    $documents->provider_service_id = $store_details->provider_service_id;
                    $documents->req_document_id = $get_document->id;
                    $documents->document_file = $file_new;
                    $documents->status = 0;
                    $documents->save();
                    Session::flash('success', $get_document->name . ' Upload Successfully!');
                }
            }
        } else {
            Session::flash('error', 'Document Name Not Found!');
        }
        return redirect()->route('get:store-admin:store_provider_document');
    }

    public function getStoreEarningReport(Request $request)
    {
        $service_status = $this->adminClass->checkStoreProviderStatus();
        if ($service_status != Null) {
            return $service_status;
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service Category Not Found!');
            return redirect()->back();
        }
        $month = date('m');
        $store_order_list = UserProductBooking::query()->select('id', 'payment_status', 'payment_type',
            'created_at',
            'order_no',
            'total_item_cost',
            'discount_cost',
            'packaging_cost',
            'delivery_cost',
            'tax_cost',
            'total_pay',
            'store_amount',
            'admin_commission',
            'delivery_people_id',
            'store_id',
            'user_id',
            'payment_type',
            'promo_code',
            'store_name', 'user_name', 'delivery_people_name',
            'driver_pay_settle_status',
            'store_pay_settle_status')
            ->where('status', 9)
            ->where('service_cat_id', $service_category->id)
            ->whereMonth('created_at', $month)->where('store_id', $store_id)->get();

        $total_amount = $store_order_list->sum('total_pay');
        $site_commission = $store_order_list->sum('admin_commission');
//        $discount_cost = $store_order_list->sum('discount_cost');
        $total_pay_to_store = $store_order_list->sum('store_amount');

        $view = view('admin.pages.store_services.earning_report.store_earning_report', compact('service_category', 'store_order_list', 'total_amount', 'site_commission', 'total_pay_to_store'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }

        return $view;
    }

    public function postStoreEarningReport(Request $request)
    {
        $service_status = $this->adminClass->checkStoreProviderStatus();
        if ($service_status != Null) {
            return $service_status;
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service Category Not Found!');
            return redirect()->back();
        }

        $from_date = ($request['from_date'] != Null && $request['to_date'] != Null) ? $request['from_date'] : Null;
        $to_date = ($request['from_date'] != Null && $request['to_date'] != Null) ? $request['to_date'] : Null;
        $payment_type = $request['payment_type'] != Null ? $request['payment_type'] : Null;
        $store_pay_type = $request['store_pay_type'] != Null ? $request['store_pay_type'] : Null;
        $store_order_list = UserProductBooking::query()->select('id', 'payment_status', 'payment_type',
            'created_at',
            'order_no',
            'order_type',
            'total_item_cost',
            'discount_cost',
            'packaging_cost',
            'delivery_cost',
            'tax_cost',
            'total_pay',
            'store_amount',
            'admin_commission',
            'delivery_people_id',
            'store_id',
            'user_id',
            'promo_code',
            'store_name', 'user_name', 'delivery_people_name',
            'driver_pay_settle_status',
            'store_pay_settle_status')
            ->where('status', 9)
            ->where('service_cat_id', $service_category->id)->where('store_id', $store_id);
        if ($from_date != Null && $to_date != Null) {
            $from = Date('Y-m-d', strtotime($from_date)) . " 00:00:00";
            $to = Date('Y-m-d', strtotime($to_date)) . " 23:59:59";
            $store_order_list->whereDate('created_at', '>=', $from);
            $store_order_list->whereDate('created_at', '<=', $to);
        }
        if ($payment_type != Null) {
            //cash & card
            $store_order_list->where('payment_type', $payment_type);
        }
        if ($store_pay_type != Null) {
            $store_order_list->where('store_pay_settle_status', $store_pay_type);
        }
        $store_order_list = $store_order_list->orderBy('id', "desc")->get();
        $total_amount = $store_order_list->sum('total_pay');
        $site_commission = $store_order_list->sum('admin_commission');
//        $discount_cost = $store_order_list->sum('discount_cost');
        $total_pay_to_store = $store_order_list->sum('store_amount');
        $total_reimburse =  round($total_amount - $total_pay_to_store,2);


        $view = view('admin.pages.store_services.earning_report.store_earning_report', compact('service_category',
            'store_order_list', 'from_date', 'to_date', 'total_amount', 'site_commission', 'total_pay_to_store', 'total_reimburse'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Admin Store Manual Assign Driver
    public function getStoreManualAssignDriver(Request $request, $order_id)
    {
        $service_status = $this->adminClass->checkStoreProviderStatus();
        if ($service_status != Null) {
            return $service_status;
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::query()->where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service Category Not Found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::query()->where('id', $store_id)->first();
        if ($store_details == Null) {
            Session::flash('error', 'Store Details Not Found!');
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

        $view = view('admin.pages.store_services.manual_assign_driver.form', compact('service_category', 'order_id', 'driver_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;

    }

    //post Admin Store Manual Assign Driver
    public function postUpdateStoreManualAssignDriver(Request $request)
    {
        try {
//        $order_slug = "approved";
            $service_status = $this->adminClass->checkStoreProviderStatus();
            if ($service_status != Null) {
                return $service_status;
            }
            $store_id = Auth::guard('store')->user()->checkStoreId();
            $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
            $service_category = ServiceCategory::query()->where('id', $store_cat_id)->first();
            if ($service_category == Null) {
                Session::flash('error', 'Service Category Not Found!');
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
                    $driver_details = TransportDriverDetails::query()->select('providers.device_token',
                        'providers.id as driver_id', 'transport_driver_details.id','transport_driver_details.delivery_boy',
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
                            return redirect()->route('get:store-admin:store_service_dispatcher', [$order_slug]);

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
    public function getStoreManualAssignShopper(Request $request, $order_id)
    {
        $service_status = $this->adminClass->checkStoreProviderStatus();
        if ($service_status != Null) {
            return $service_status;
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::query()->where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service Category Not Found!');
            return redirect()->back();
        }
        $store_details = StoreDetails::query()->where('id', $store_id)->first();
        if ($store_details == Null) {
            Session::flash('error', 'Store Details Not Found!');
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
        $shopper_list = Provider::select('providers.id as shopper_id', 'providers.name as shopper_name', 'providers.contact_number',
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
        $view = view('admin.pages.store_services.manual_assign_shopper.form', compact('service_category', 'order_id', 'shopper_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //post Admin Store Manual Assign Driver
    public function postUpdateStoreManualAssignShopper(Request $request)
    {
        $service_status = $this->adminClass->checkStoreProviderStatus();
        if ($service_status != Null) {
            return $service_status;
        }
        $store_id = Auth::guard('store')->user()->checkStoreId();
        $store_cat_id = Auth::guard('store')->user()->checkStoreCatId();
        $service_category = ServiceCategory::query()->where('id', $store_cat_id)->first();
        if ($service_category == Null) {
            Session::flash('error', 'Service Category Not Found!');
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
                $driver_details = ShopperProviderDetails::query()->select('providers.name as shopper_name', 'providers.id as shopper_id', 'provider_services.id as shopper_service_id', 'providers.device_token', 'providers.login_device')
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
                        return redirect()->route('get:store-admin:store_service_dispatcher', [$order_slug]);
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

    //code for excelsheet product upload in store
    public function getAddStoreProductExcel(Request $request, $store_id)
    {
        $slug = "";
        $view = view('admin.pages.store_services.products.excel', compact('slug', 'store_id'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getUpdateStoreProductExcel(StoreProductExcel $request)
    {
        $store_id = $request->get('store_id');
        $store_details = StoreDetails::query()->where('id', $store_id)->first();
        if ($store_details == Null) {
            Session::flash('error', 'store not exists!');
            return redirect()->back();
        }
        try {
            Excel::import(new StoreProductDetailsImport($store_id),$request->file('product_excel_file'));
            Session::flash('success', 'Product Import successfully!');
        } catch (\Exception $e) {
            Session::flash('error', 'Check and correct data in spreadsheet!');
        }
        return redirect()->route('get:store-admin:store_product_list');
    }
}
