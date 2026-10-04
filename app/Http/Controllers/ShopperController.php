<?php

namespace App\Http\Controllers;

use App\Classes\AdminClass;
use App\Http\Requests\ShopperProviderStoreRequest;
use App\Http\Requests\ShopperRequiredDocumentsRequest;
use App\model\Provider;
use App\model\ProviderBankDetails;
use App\model\ProviderServices;
use App\model\RequiredDocuments;
use App\model\ServiceCategory;
use App\model\ShopperProviderDetails;
use App\model\ShopperProviderDocuments;
use App\model\ShopperRating;
use App\model\ShopperRequiredDocuments;
use App\model\UserProductBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ShopperController extends Controller
{
    private $adminClass;
    private $store_service_id_array;

    public function __construct(AdminClass $adminClass)
    {
        $this->middleware('auth');
        $this->adminClass = $adminClass;
        $this->store_service_id_array = [5, 6, 7, 8, 9, 10];
    }

    public function getShopperServiceDashboard(Request $request)
    {
        return redirect()->route("get:admin:shopper_service_list","approved");
        //$approved_store = ProviderServices::query()->whereIn('service_cat_id', $this->store_service_id_array)->where('status', 1)->count();
        //$live_store = ProviderServices::query()->whereIn('service_cat_id', $this->store_service_id_array)->where('status', 1)->where('current_status', 1)->count();
        //$pending_store = ProviderServices::query()->whereIn('service_cat_id', $this->store_service_id_array)->where('status', 0)->count();
        //$total_orders = UserProductBooking::query()->whereIn('service_cat_id', $this->store_service_id_array)->where('shopper_type', 1)->count();
        //$completed_orders = UserProductBooking::query()->whereIn('service_cat_id', $this->store_service_id_array)->where('shopper_type', 1)->where('status', 7)->count();
        //$running_orders = UserProductBooking::query()->whereIn('service_cat_id', $this->store_service_id_array)->where('shopper_type', 1)->whereIN('status', [4, 5, 6, 7])->count();
        //$cancelled_orders = UserProductBooking::query()->whereIn('service_cat_id', $this->store_service_id_array)->where('shopper_type', 1)->count();
        $approved_store = 0;
        $live_store = 0;
        $pending_store = 0;
        $total_orders = 0;
        $completed_orders = 0;
        $running_orders = 0;
        $cancelled_orders = 0;

        $order_total = UserProductBooking::select(DB::raw('sum(total_pay) as total_pay'))
            ->groupBy('service_cat_id')
            ->whereIn('service_cat_id', $this->store_service_id_array)
            ->first();
        $store_total = UserProductBooking::select(DB::raw('sum(store_amount) as store_amount'))
            ->groupBy('service_cat_id')
            ->whereIn('service_cat_id', $this->store_service_id_array)
            ->first();

        if ($order_total != Null && $store_total != Null) {
            $admin_earning = round(($order_total->total_pay) - ($store_total->store_amount), 2);
        } else {
            $admin_earning = 0.00;
        }
        $view = view('admin.pages.super_admin.service_dashboard.shopper_dashboard', compact('approved_store', 'live_store', 'pending_store', 'total_orders', 'completed_orders', 'cancelled_orders', 'running_orders', 'admin_earning'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //shopper list
    public function getShopperServiceList(Request $request, $status)
    {
        if ($status == "approved") {
            $status = 1;
            $provider_status = 1;
        } elseif ($status == "un-approved") {
            $status = 0;
            $provider_status = 0;
        } elseif ($status == "blocked") {
            $status = 2;
            $provider_status = 2;
        } elseif ($status == "rejected") {
            $status = 3;
            $provider_status = 3;
        } else {
            return redirect()->back();
        }
        $shopper_list_get = Provider::query()->select('providers.id', 'providers.id as shopper_id',
            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"),
            'providers.email',
            'providers.contact_number',
            'providers.created_at',
            'provider_services.status')
            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
            ->join('shopper_provider_details', 'shopper_provider_details.provider_id', '=', 'providers.id')
//            ->where('provider_services.status', $status)
            ->groupBy('provider_services.provider_id')
            ->get();
        $shopper_id_list = $shopper_list_get->pluck('shopper_id')->toArray();

        $shopper_services = [];
        $shopper_list = [];
        foreach ($shopper_id_list as $key => $shopper_id) {
            $provider_services = ProviderServices::query()
                ->select('service_category.name as category_name', 'provider_services.status', 'provider_services.id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->where('provider_services.provider_id', $shopper_id)
                ->get();
            $shopper_services[$shopper_id] = $provider_services->pluck('category_name')->toArray();
            $services = ProviderServices::query()
                ->select('providers.id', 'providers.id as shopper_id',
                    DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"),
                    'providers.email',
                    'providers.contact_number',
                    'providers.created_at',
                    'provider_services.status')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('provider_services.provider_id', $shopper_id)
                ->where('provider_services.status', $status)
                ->first();
            if ($status == 1) {
                $provider = ProviderServices::query()
                    ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                    ->where('provider_services.provider_id', $shopper_id)
                    ->whereIn('provider_services.status', [0, 2, 3])
                    ->count();
                if ($services != Null && $provider == 0) {
                    $shopper_list[] = [
                        'id' => $services->id,
                        'shopper_id' => $services->shopper_id,
                        'name' => $services->name,
                        'email' => $services->email,
                        'contact_number' => $services->contact_number,
                        'created_at' => $services->created_at,
                        'status' => $services->status,
                    ];
                }
            } elseif ($status == 0) {
                $provider = ProviderServices::query()
                    ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                    ->where('provider_services.provider_id', $shopper_id)
                    ->whereIn('provider_services.status', [0])
                    ->count();
                if ($services != Null && $provider >= 0) {
                    $shopper_list[] = [
                        'id' => $services->id,
                        'shopper_id' => $services->shopper_id,
                        'name' => $services->name,
                        'email' => $services->email,
                        'contact_number' => $services->contact_number,
                        'created_at' => $services->created_at,
                        'status' => $status,
                    ];
                }
            } else {
                $provider = ProviderServices::query()
                    ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                    ->where('provider_services.provider_id', $shopper_id)
                    ->whereIn('provider_services.status', [0])
                    ->count();
                if ($services != Null && $provider == 0) {
                    $shopper_list[] = [
                        'id' => $services->id,
                        'shopper_id' => $services->shopper_id,
                        'name' => $services->name,
                        'email' => $services->email,
                        'contact_number' => $services->contact_number,
                        'created_at' => $services->created_at,
                        'status' => $status,
                    ];
                }
            }

        }
        $view = view('admin.pages.shopper_services.shopper_provider_list', compact('status', 'shopper_list', 'shopper_services'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get add store
    public function getAddShopper(Request $request)
    {
        $service_category_list = ServiceCategory::select('id', 'name')->whereIN('category_type', [2])->where('status', 1)->get();
//        dd($service_category_list);
        $view = view('admin.pages.shopper_services.shopper.form', compact('service_category_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getEditshopper(Request $request, $id)
    {
        if ($id == Null) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }
        $service_category_list = ServiceCategory::select('id', 'name')->whereIN('category_type', [2])->where('status', 1)->get();
        $provider = Provider::query()->where('id', $id)->first();
        if ($provider != Null) {
            $provider_other_details = ShopperProviderDetails::query()->where('provider_id', $provider->id)->first();
            $bank_details = ProviderBankDetails::query()->where('provider_id', $provider->id)->first();

            $provider_services = ProviderServices::query()->where('provider_id', $provider->id)->get()->pluck('service_cat_id', 'service_cat_id')->toArray();

            $view = view('admin.pages.shopper_services.shopper.form', compact('service_category_list', 'provider', 'provider_other_details', 'bank_details', 'provider_services'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            } else {
                return $view;
            }
        } else {
            $view = view('admin.pages.shopper_services.shopper.form', compact('service_category_list'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            } else {
                return $view;
            }
        }
    }

    public function getUpdateShopper(ShopperProviderStoreRequest $request)
    {
        Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
        return redirect()->back();
        $provider = $this->adminClass->AddServiceProvider($request);
        if ($provider == Null) {
            Session::flash('error', 'Something want to wrong!');
            return redirect()->back();
        }

        $id = $request->get('id');
        $provider_details = ShopperProviderDetails::query()->where('id', $provider->id)->first();
        if ($provider_details == Null) {
            $provider_details = new ShopperProviderDetails();
        }
        $provider_details->provider_id = $provider->id;
        $provider_details->address = $request->get('address');
        $provider_details->lat = $request->get('lat');
        $provider_details->long = $request->get('long');
        $provider_details->save();

        $provider_services = $request->get('provider_services');
//        dd($provider_services);
        if ($provider_services != Null) {
            $service_category_list = ServiceCategory::select('id', 'name')->whereIN('category_type', [2])->where('status', 1)->get();
            foreach ($service_category_list as $key => $services) {
                if (array_key_exists($services->id, $provider_services) && $provider_services[$services->id] == $services->id) {
                    $provider_service = ProviderServices::query()->where('provider_id', $provider->id)->where('service_cat_id', $services->id)->first();
                    if ($provider_service == Null) {
                        $provider_service = new ProviderServices();
                        $provider_service->provider_id = $provider->id;
                        $provider_service->service_cat_id = $services->id;
                        $provider_service->current_status = 1;
                        $provider_service->status = 1;
                        $provider_service->save();
                    }
                } else {
                    ProviderServices::query()->where('provider_id', $provider->id)->where('service_cat_id', $services->id)->delete();
                }
            }
        }
        if ($request->get('account_number') != Null && $request->get('holder_name') != Null && $request->get('bank_name') != Null && $request->get('bank_location') != Null && $request->get('payment_email') != Null && $request->get('bic_swift_code') != Null) {
            $provider_bank_details = ProviderBankDetails::where('provider_id', $provider->id)->first();
            if ($provider_bank_details == Null) {
                $provider_bank_details = new ProviderBankDetails();
            }
            $provider_bank_details->provider_id = $provider->id;
            $provider_bank_details->account_number = $request->get('account_number');
            $provider_bank_details->holder_name = $request->get('holder_name');
            $provider_bank_details->bank_name = $request->get('bank_name');
            $provider_bank_details->bank_location = $request->get('bank_location');
            $provider_bank_details->payment_email = $request->get('payment_email');
            $provider_bank_details->bic_swift_code = $request->get('bic_swift_code');
            $provider_bank_details->save();
        }

        if ($id == Null) {
            $status = "approved";
            Session::flash('success', 'Shopper add successfully!');
        } else {
            $status = "approved";
            Session::flash('success', 'Shopper updated successfully!');
        }

        return redirect()->route('get:admin:shopper_service_list', [$status]);
    }

    public function getUpdateShopperStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "id" => "required",
                "request_for" => "required",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first()
            ]);
        }
        $id = $request->get('id');
        $request_for = $request->get('request_for');
        //1 = approved
        //2 = blocked
        //3 = rejected
        if ($id == Null || $request_for == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $provider = Provider::where('id', $id)->first();
        if ($provider == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $shopper_details = ShopperProviderDetails::where('provider_id', $provider->id)->first();
        if ($shopper_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $provider_service_list = ProviderServices::where('provider_id', $provider->id)->get();
        if ($provider_service_list == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        foreach ($provider_service_list as $key => $service) {
            $provider_service = ProviderServices::where('id', $service->id)->first();
            if ($provider_service == Null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ]);
            }
            if ($request_for == 1) {
                $provider_service->status = 1;
                $provider_service->save();
                $provider->status = 1;
                $provider->save();
            } elseif ($request_for == 2) {
                $provider_service->status = 2;
                $provider_service->save();
            } elseif ($request_for == 3) {
                $validator = Validator::make($request->all(), [
                        "reject_reason" => "required"
                    ]
                );
                if ($validator->fails()) {
                    return response()->json([
                        "status" => false,
                        "message" => $validator->errors()->first()
                    ]);
                }
                $provider_service->status = 3;
                $provider_service->rejected_reason = $request->get('reject_reason');
                $provider_service->save();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ]);
            }
        }
        return response()->json([
            'success' => true
        ]);
    }

    public function getRequiredDocumentList(Request $request)
    {
        $required_documents_list = ShopperRequiredDocuments::select('shopper_required_documents.id', 'shopper_required_documents.name as document_name', 'shopper_required_documents.status'
//, 'service_category.name'
        )
//            ->join('service_category', 'service_category.id', '=', 'required_documents.service_cat_id')
//            ->where('service_category.id', '=', $service_cat_id)
            ->get();
//        if (in_array($service_category->id, $this->transport_service_id_array)) {
//            $segment = "transport";
//        } elseif (in_array($service_category->id, $this->store_service_id_array)) {
//            $segment = "store";
//        } else {
//            $segment = "provider-services";
//        }
        $view = view('admin.pages.shopper_services.required-documents.manage', compact('required_documents_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function getAddRequiredDocument(Request $request)
    {
        $view = view('admin.pages.shopper_services.required-documents.form');
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //edit required documents
    public function getEditRequiredDocument($id, Request $request)
    {
        $required_document = ShopperRequiredDocuments::where('id', $id)->first();
        if ($required_document != Null) {
            $view = view('admin.pages.shopper_services.required-documents.form', compact('required_document'));
            if ($request->ajax()) {
                $view = $view->renderSections();
                return $this->adminClass->renderingResponce($view);
            }
            return $view;
        } else {
            Session::flash('error', 'Required Document Not Found!');
            return redirect()->back();
        }
    }

    //save or update required documents
    public function postUpdateRequiredDocument(ShopperRequiredDocumentsRequest $request)
    {
        $id = $request->get('id');
        if ($id != Null) {
            $required_document = ShopperRequiredDocuments::where('id', $request->get('id'))->first();
        } else {
            $required_document = new ShopperRequiredDocuments();
        }
        $required_document->name = $request->get('name');
        $required_document->service_cat_id = 0;
        $required_document->status = $request->get('status');
        $required_document->save();

        if ($id != Null) {
            Session::flash('success', 'Required Document Updated successfully!');
        } else {
            Session::flash('success', 'Required Document Added successfully!');
        }
        return redirect()->route('get:admin:shopper_required_document_list');
    }

    //get ajax admin required document status change
    public function getAjaxUpdateAdminRequiredDocumentStatus(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $required_document_details = ShopperRequiredDocuments::select('id', 'status')->where('id', '=', $id)->first();
        if ($required_document_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($required_document_details->status == 1) {
            $status = $required_document_details->status = 0;
        } else {
            $status = $required_document_details->status = 1;
        }
        $required_document_details->save();
        return response()->json([
            'success' => true,
            'status' => $status,
        ]);
    }

    //get Shoppe order review List
    public function getTransportProviderRideReviewList(Request $request)
    {
        $provider_reviews = ShopperRating::query()->select(
            'shopper_rating.id', 'shopper_rating.rating',
            'shopper_rating.comment', 'shopper_rating.status',
            'providers.name as shopper_name', 'store_details.name as store_name',
            'user_store_product_booking.order_date_time', 'user_store_product_booking.order_no'
        )
            ->join('user_store_product_booking', 'user_store_product_booking.id', '=', 'shopper_rating.booking_id')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->join('providers', 'providers.id', '=', 'shopper_rating.shopper_id')
            ->orderBy('shopper_rating.id', 'desc')
            ->get();
        $view = view('admin.pages.shopper_services.shopper_review_list', compact('provider_reviews'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get Shopper Order Review Status Change
    public function getUpdateShopperOrderReviewStatus(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $shopper_review = ShopperRating::query()->select('id', 'status')->where('id', '=', $id)->first();
        if ($shopper_review == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        if ($shopper_review->status == 1) {
            $status = $shopper_review->status = 0;
        } else {
            $status = $shopper_review->status = 1;
        }
        $shopper_review->save();
        return response()->json([
            'success' => true,
            'status' => $status,
        ]);
    }

    //get Delete Shopper Order Review
    public function getDeleteShopperOrderReview(Request $request)
    {
        $id = $request->get('id');
        if ($id == Null) {
            return response()->json([
                'success' => false
            ]);
        }
        $shopper_review = ShopperRating::query()->where('id', '=', $id)->first();
        if ($shopper_review == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $shopper_review->delete();
        return response()->json([
            'success' => true
        ]);
    }

    //shopper provider document list
    public function getShopperProviderDocument(Request $request, $shopper_id)
    {
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service category not found!');
//            return redirect()->back();
//        }
        $shopper_details = Provider::where('id', $shopper_id)->first();
        if ($shopper_details == Null) {
            return redirect()->back();
        }
        $provider_documents = [];
        $required_documents = ShopperRequiredDocuments::where('status', 1)->get();
        foreach ($required_documents as $key => $required_document) {
            $document_details = ShopperProviderDocuments::query()->where('provider_id', '=', $shopper_details->id)->where('req_document_id', $required_document->id)->first();
//            if ($required_document->service_cat_id == $service_category->id) {
            $provider_documents[$key] = $document_details;
//            } else {
//                $provider_documents[$key] = Null;
//            }
        }
        $view = view('admin.pages.shopper_services.document.manage', compact( 'upload_document', 'required_documents', 'provider_documents'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    //get ajax admin required document status change
    public function getAjaxUpdateAdminApprovedRejectShopperDocument(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');
        if ($id == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        $provider_document_details = ShopperProviderDocuments::query()->where('id',$id)->first();
        if ($provider_document_details == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
//        if ($provider_document_details->status == 1) {
//            $status = $provider_document_details->status = 0;
//        } else {
        $provider_document_details->status = $status;
//        }
        $provider_document_details->save();
        return response()->json([
            'success' => true,
            'status' => $provider_document_details,
        ]);
    }


    //store earning reports
    public function getShopperEarningReport(Request $request)
    {
        $user_list = UserProductBooking::select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->groupBy('user_store_product_booking.user_id')
            ->get();
        $shopper_list = UserProductBooking::select('providers.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.shopper_id as shopper_id')
            ->join('provider_services', 'provider_services.provider_id', '=', 'user_store_product_booking.shopper_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->groupBy('user_store_product_booking.shopper_id')
            ->get();

        $store_list = UserProductBooking::select('store_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.store_id')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->groupBy('user_store_product_booking.store_id')
            ->get();
        $view = view('admin.pages.shopper_services.earning_report.admin_earning_report', compact('user_list', 'shopper_list', 'store_list'));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function postShopperEarningReport(Request $request)
    {

        $user_list = UserProductBooking::select('users.id', 'users.first_name', 'users.last_name', 'users.contact_number')
            ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->groupBy('user_store_product_booking.user_id')
            ->get();
        $shopper_list = UserProductBooking::select('providers.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.shopper_id as shopper_id')
            ->join('provider_services', 'provider_services.provider_id', '=', 'user_store_product_booking.shopper_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->groupBy('user_store_product_booking.shopper_id')
            ->get();

        $store_list = UserProductBooking::select('store_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'providers.contact_number', 'user_store_product_booking.store_id')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('user_store_product_booking.status', '=', 9)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array)
            ->groupBy('user_store_product_booking.store_id')
            ->get();

        $from_date = ($request['from_date'] != Null && $request['to_date'] != Null) ? $request['from_date'] : Null;
        $to_date = ($request['from_date'] != Null && $request['to_date'] != Null) ? $request['to_date'] : Null;
        $shopper = $request['shopper'] != Null ? $request['shopper'] : Null;
        $store = $request['store'] != Null ? $request['store'] : Null;
        $user = $request['user'] != Null ? $request['user'] : Null;
        $payment_type = $request['payment_type'] != Null ? $request['payment_type'] : Null;
        $shopper_pay_type = $request['shopper_pay_type'] != Null ? $request['shopper_pay_type'] : Null;
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
            'used_promocode_details.discount_amount as promo_code_amount',
            'user_store_product_booking.store_name',
            'user_store_product_booking.user_name',
            'user_store_product_booking.shopper_name',
            'user_store_product_booking.shopper_pay_settle_status',
            'user_store_product_booking.store_pay_settle_status',
            'service_category.name as service_category_name'
            )
            ->join('service_category', 'service_category.id', 'user_store_product_booking.service_cat_id')
            ->leftJoin('used_promocode_details', 'used_promocode_details.id', 'user_store_product_booking.promo_code')
            ->where('user_store_product_booking.status', 9)
            ->where('user_store_product_booking.shopper_type', 1)
            //->where('user_store_product_booking.service_cat_id', $service_category->id)
            ->whereIn('user_store_product_booking.service_cat_id', $this->store_service_id_array);
        if ($from_date != Null && $to_date != Null) {
            $from = Date('Y-m-d', strtotime($from_date)) . " 00:00:00";
            $to = Date('Y-m-d', strtotime($to_date)) . " 23:59:59";
            $store_order_list->whereDate('user_store_product_booking.created_at', '>=', $from);
            $store_order_list->whereDate('user_store_product_booking.created_at', '<=', $to);
        }
        if ($shopper != Null) {
            $store_order_list->where('user_store_product_booking.shopper_id', $shopper);
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
        if ($shopper_pay_type != Null) {
            $store_order_list->where('user_store_product_booking.shopper_pay_settle_status', $shopper_pay_type);
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

        $shopper_earning = $store_order_list->sum('shopper_earring');

//        dd($store_order_list);


        $view = view('admin.pages.shopper_services.earning_report.admin_earning_report', compact('slug', 'service_category', 'store_order_list', 'user_list', 'shopper_list', 'store_list',
            'from_date', 'to_date', 'shopper', 'store', 'user', 'payment_type', 'shopper_pay_type', 'store_pay_type',
            'total_amount', 'site_commission', 'driver_earning', 'collect_from_driver', 'total_pay_to_driver',
            'total_pay_to_store', 'store_earning', 'collect_from_store', 'shopper_earning'
        ));
        if ($request->ajax()) {
            $view = $view->renderSections();
            return $this->adminClass->renderingResponce($view);
        }
        return $view;
    }

    public function postShopperOrderPaymentSettle(Request $request)
    {
//        $service_category = ServiceCategory::where('slug', $slug)->first();
//        if ($service_category == Null) {
//            Session::flash('error', 'Service Category Not Found!');
//            return redirect()->back();
//        }
        Session::flash('error', 'Add / Edit / Delete Property has been disabled in the Demo Admin Panel. We will provide the enabled features in the main clone script.');
        return redirect()->back();
        if ($request->get('order_id') != Null) {
            $order_id_settle = $request->get('order_id');
            foreach ($order_id_settle as $key => $order_id) {
                $order_details = UserProductBooking::where('id', $key)->first();
                if ($order_details != Null) {
                    $order_details->shopper_pay_settle_status = 1;
//                    $order_details->store_pay_settle_status = 1;
                    $order_details->save();
                }
            }
        }
        return redirect()->back();
    }
}
