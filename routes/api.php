<?php

use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['middleware' => 'setLocaleLang'], function () {
    Route::middleware('api')->group(function () {

        Route::post('/twilio-access-token', ['as' => 'get:twilio-access-token', 'uses' => 'Api\VoiceCallController@getTestTwilioAccessToken']);

        Route::post('/test-email-templates', ['as' => 'post:customer:test_email_templates', 'uses' => 'Api\CustomerApiController@postTestEmailTemplates']);

        Route::post('/google-map', ['as' => 'post:customer:firebase_security_rules', 'uses' => 'Api\CustomerApiController@postFirebaseSecurityRules']);

        Route::prefix('customer')->group(function () {
            Route::post('/remove-account',['as'=>'post:customer:remove_account','uses'=>'Api\CustomerApiController@postUserRemoveAccount']);
            Route::post('/app-version-check', ['as' => 'post:app-version-check', 'uses' => 'Api\CustomerApiController@postAppVersionCheck']);
            Route::post('/country-and-currency-list', ['as' => 'post:customer:country-and-currency-list', 'uses' => 'Api\CustomerApiController@postCountryAndCurrencyList']);
            Route::post('/facebook-delete-customer',['as'=>'post:user:facebook_data_deletion','uses'=>'Api\CustomerApiController@postFacebookUserDataDeletion']);
            Route::post('/login', ['as' => 'post:customer:login', 'uses' => 'Api\Auth\LoginController@postCustomerLogin']);
            Route::post('/register', ['as' => 'post:customer:register', 'uses' => 'Api\Auth\RegisterController@postCustomerRegister']);
//            Route::post('/register1', ['as' => 'post:customer:register', 'uses' => 'Api\Auth\RegisterController@postCheckCustomerRegister']);
            Route::post('/social-required-field', ['as' => 'post:customer:social_required_field', 'uses' => 'Api\Auth\UpdateRegisterController@postCustomerSocialRequiredField']);
            Route::post('/edit-profile', ['as' => 'post:customer:update_details', 'uses' => 'Api\Auth\UpdateRegisterController@postUpdateCustomerDetails']);
            Route::post('/change-password', ['as' => 'post:customer:change_password', 'uses' => 'Api\Auth\ResetPasswordController@postCustomerChangePassword']);
            Route::post('/update-country-and-currency', ['as' => 'post:customer:update-country-and-currency', 'uses' => 'Api\Auth\UpdateRegisterController@postCustomerUpdateCountryAndCurrency']);
            Route::post('/logout', ['as' => 'post:customer:logout', 'uses' => 'Api\Auth\LogoutController@postCustomerLogout']);

            //make call (vo-ip call)
            Route::post('/accessToken', ['as' => 'get:demo', 'uses' => 'Api\VoiceCallController@getAccessTokenNew']);
            Route::post('/makeCall', ['as' => 'get:demo', 'uses' => 'Api\VoiceCallController@getMakeCallNew']);
            Route::post('/incoming', ['as' => 'post:call_incoming', 'uses' => 'Api\VoiceCallController@getIncomingNew']);
            Route::post('/place-call', ['as' => 'post:call_incoming', 'uses' => 'Api\VoiceCallController@getPlaceCall']);

            Route::post('/contact-verification', ['as' => 'post:customer:contact_verification', 'uses' => 'Api\Auth\UpdateRegisterController@postCustomerContactVerification']);
            Route::post('/resend-otp-verification', ['as' => 'post:customer:resend_otp_verification', 'uses' => 'Api\Auth\UpdateRegisterController@postCustomerResendOtpVerification']);

            Route::post('/forgot-password-request', ['as' => 'post:customer:forgot_password_request', 'uses' => 'Api\Auth\ResetPasswordController@postCustomerForgotPasswordRequest']);
            Route::post('/forgot-change-password', ['as' => 'post:customer:forgot_change_password', 'uses' => 'Api\Auth\ResetPasswordController@postCustomerForgotChangePassword']);

            Route::post('/resend-verification-link', ['as' => 'post:customer:resend_verification_link', 'uses' => 'Api\Auth\UpdateRegisterController@postCustomerResendVerificationLink']);

            Route::post('/home', ['as' => 'post:customer:homepage', 'uses' => 'Api\CustomerApiController@postHomepage']);

            Route::post('/add-card', ['as' => 'post:customer:add_card', 'uses' => 'Api\CustomerApiController@postCustomerAddCard']);
            Route::post('/delete-card', ['as' => 'post:customer:remove_card', 'uses' => 'Api\CustomerApiController@postCustomerRemoveCard']);
            Route::post('/card-list', ['as' => 'post:customer:card_list', 'uses' => 'Api\CustomerApiController@postCustomerCardList']);

            Route::post('/add-address', ['as' => 'post:customer:add_address', 'uses' => 'Api\CustomerApiController@postCustomerAddAddress']);
            Route::post('/edit-address', ['as' => 'post:customer:edit_address', 'uses' => 'Api\CustomerApiController@postCustomerEditAddress']);
            Route::post('/delete-address', ['as' => 'post:customer:delete_address', 'uses' => 'Api\CustomerApiController@postCustomerDeleteAddress']);
            Route::post('/address-list', ['as' => 'post:customer:address_list', 'uses' => 'Api\CustomerApiController@postCustomerAddressList']);

            Route::post('/add-wallet-balance', ['as' => 'post:customer:add_wallet_balance', 'uses' => 'Api\CustomerApiController@postCustomerAddWalletBalance']);
            Route::post('/wallet-transaction', ['as' => 'post:customer:wallet_transaction', 'uses' => 'Api\CustomerApiController@postCustomerWalletTransaction']);
            Route::post('/get-wallet-balance', ['as' => 'post:customer:get_wallet_balance', 'uses' => 'Api\CustomerApiController@postCustomerGetWalletBalance']);

            Route::post('/search-wallet-transfer-user-list', ['as' => 'post:customer:search_wallet_transfer_user_list', 'uses' => 'Api\CustomerApiController@postCustomerSearchWalletTransferUserList']);
            Route::post('/wallet-transfer', ['as' => 'post:customer:wallet_to_wallet_transfer', 'uses' => 'Api\CustomerApiController@postCustomerWalletToWalletTransfer']);

            Route::post('/get-running-service', ['as' => 'post:customer:get_running_service', 'uses' => 'Api\CustomerApiController@postCustomerGetRunningService']);

            Route::post('/get-promocode-list', ['as' => 'post:customer:get_promocode_list', 'uses' => 'Api\CustomerApiController@postStorePromocodeList']);
            Route::post('/apply-promocode-check', ['as' => 'post:customer:apply_promocode_check', 'uses' => 'Api\CustomerApiController@postApplyPromocodeCheck']);

            Route::post('/coupon-list', ['as' => 'post:customer:coupon_list', 'uses' => 'Api\CustomerApiController@postCustomerCouponList']);
            Route::post('/coupon-details', ['as' => 'post:customer:coupon_details', 'uses' => 'Api\CustomerApiController@postCustomerCouponDetails']);
            Route::post('/purchase-coupon', ['as' => 'post:customer:purchase_coupon', 'uses' => 'Api\CustomerApiController@postCustomerPurchaseCoupon']);
            Route::post('/purchase-coupon-list', ['as' => 'post:customer:purchase_coupon_list', 'uses' => 'Api\CustomerApiController@postCustomerPurchaseCouponList']);

            //testing payment route for vnpay
            Route::post('/temp-booking', ['as' => 'post:customer:temp_transport_ride_booking', 'uses' => 'Api\CustomerApiController@postTempTransportRideBooking']);
            Route::post('/temp-booking-status', ['as' => 'post:customer:temp_transport_ride_booking_status', 'uses' => 'Api\CustomerApiController@postTempTransportRideBookingStatus']);

            Route::post('/change-contact-number', ['as' => 'post:customer:change_contact_number', 'uses' => 'Api\Auth\UpdateRegisterController@postCustomerChangeContactNumber']);

            Route::post('/home-page-banners', ['as' => 'post:home_page_banner', 'uses' => 'Api\CustomerApiController@getHomePageBanner']);

            Route::post('/home-page-feature-store', ['as' => 'post:home_page_feature_store', 'uses' => 'Api\CustomerApiController@getHomePageFeatureStore']);
            Route::post('/view-all-feature-store', ['as' => 'post:view_all_feature_store', 'uses' => 'Api\CustomerApiController@getViewAllFeatureStore']);

            Route::post('/home-page-spot-light-list', ['as' => 'post:home_page_spot_light_list', 'uses' => 'Api\CustomerApiController@postHomePageSpotLightList']);

            Route::post('/store-review-list', ['as' => 'post:customer:review_list', 'uses' => 'Api\CustomerApiController@postCustomerReviewList']);

//            Route::prefix('transport')->group(function () {
//                Route::post('/get-nearest-driver-position', ['as' => 'post:customer:get_nearest_driver_position', 'uses' => 'Api\Transport\UserController@postGetNearestDriverPosition']);
//                Route::post('/service-type', ['as' => 'post:customer:transport_service_type', 'uses' => 'Api\Transport\UserController@postTransportServiceType']);
//
//                //testing payment route for vnpay
//                //Route::post('/temp-ride-booking-status',['as'=>'post:customer:temp_transport_ride_booking_status','uses'=>'Api\Transport\UserController@postTempTransportRideBookingStatus']);
//
//                Route::post('/ride-booking', ['as' => 'post:customer:transport_ride_booking', 'uses' => 'Api\Transport\UserController@postTransportRideBooking']);
//                Route::post('/add-ride-tip', ['as' => 'post:customer:transport_add_ride_tip', 'uses' => 'Api\Transport\UserController@postTransportAddRideTip']);
//                Route::post('/cancel-ride', ['as' => 'post:customer:transport_cancel_ride', 'uses' => 'Api\Transport\UserController@postTransportCancelRide']);
//                //Route::post('/assign-driver-details', ['as' => 'post:customer:transport_assign_driver_details', 'uses' => 'Api\Transport\UserController@postTransportAssignDriverDetails']);
//                Route::post('/ride-history', ['as' => 'post:customer:transport_ride_history', 'uses' => 'Api\Transport\UserController@postTransportRideHistory']);
//                Route::post('/ride-completed-invoice-details', ['as' => 'post:customer:transport_ride_completed_invoice_details', 'uses' => 'Api\Transport\UserController@postTransportRideCompletedInvoiceDetails']);
//                Route::post('/ride-receipt-details', ['as' => 'post:customer:transport_ride_receipt_details', 'uses' => 'Api\Transport\UserController@postTransportRideReceiptDetails']);
//                Route::post('/ride-user-rating', ['as' => 'post:customer:transport_ride_user_rating', 'uses' => 'Api\Transport\UserController@postTransportRideUserRating']);
//                Route::post('/ride-payment', ['as' => 'post:customer:ride_payment', 'uses' => 'Api\Transport\UserController@postTransportOrderPayment']);
//
//                Route::post('/service-home-page', ['as' => 'post:customer:transport_service_home', 'uses' => 'Api\Transport\UserController@postTransportServiceHome']);
//                Route::post('/ride-preview', ['as' => 'post:customer:transport_ride_preview', 'uses' => 'Api\Transport\UserController@postTransportRidePreview']);
//
//                Route::post('/surcharge-charge', ['as' => 'post:customer:transport_surcharge_charge', 'uses' => 'Api\Transport\UserController@postTransportSurchargeCharge']);
//
//                Route::prefix('rental')->group(function () {
//                    Route::post('/get-nearest-driver-position', ['as' => 'post:customer:get_nearest_driver_position', 'uses' => 'Api\Transport\Rental\UserController@postGetNearestDriverPosition']);
//                    Route::post('/service-type', ['as' => 'post:customer:transport_service_type', 'uses' => 'Api\Transport\Rental\UserController@postTransportServiceType']);
//                    Route::post('/ride-preview', ['as' => 'post:customer:transport_ride_preview', 'uses' => 'Api\Transport\Rental\UserController@postTransportRentalRidePreview']);
//                    Route::post('/ride-booking', ['as' => 'post:customer:transport_ride_booking', 'uses' => 'Api\Transport\Rental\UserController@postTransportRentalRideBooking']);
//                    Route::post('/ride-details', ['as' => 'post:customer:transport_ride_details', 'uses' => 'Api\Transport\Rental\UserController@postTransportRentalRideDetails']);
//                    Route::post('/update-ride-status', ['as' => 'post:customer:transport_ride_booking', 'uses' => 'Api\Transport\Rental\UserController@postTransportRentalRideUpdateStatus']);
//                });
//            });

            Route::prefix('on-demand')->group(function () {
                Route::post('/home', ['as' => 'post:other_service:home', 'uses' => 'Api\OtherService\UserController@postOtherServiceHome']);
                Route::post('/category-list', ['as' => 'post:other_service:category_list', 'uses' => 'Api\OtherService\UserController@postOtherServiceCategoryList']);
                Route::post('/provider-package-list', ['as' => 'post:other_service:provider_package_list', 'uses' => 'Api\OtherService\UserController@postOtherServiceProviderPackageList']);
                Route::post('/provider-details', ['as' => 'post:other_service:provider_details', 'uses' => 'Api\OtherService\UserController@postOtherServiceProviderDetails']);
                Route::post('/provider-time-list', ['as' => 'post:other_service:provider_time_list', 'uses' => 'Api\OtherService\UserController@postOtherServiceProviderTimeList']);
                Route::post('/provider-list', ['as' => 'post:other_service:provider_list', 'uses' => 'Api\OtherService\UserController@postOtherServiceProviderList']);
                Route::post('/place-order', ['as' => 'post:other_service:place_order', 'uses' => 'Api\OtherService\UserController@postOtherServicePlaceOrder']);
                Route::post('/order-preview', ['as' => 'post:other_service:order_preview', 'uses' => 'Api\OtherService\UserController@postOtherServiceOrderPreview']);
                //Route::post('/add-order-remark', ['as' => 'post:other_service:add_order_remark', 'uses' => 'Api\OtherService\UserController@postOtherServiceAddOrderRemark']);
                Route::post('/order-history', ['as' => 'post:other_service:order_history', 'uses' => 'Api\OtherService\UserController@postOtherServiceOrderHistory']);
                Route::post('/order-details', ['as' => 'post:other_service:order_details', 'uses' => 'Api\OtherService\UserController@postOtherServiceOrderDetails']);
                Route::post('/order-payment', ['as' => 'post:other_service:order_payment', 'uses' => 'Api\OtherService\UserController@postOtherServiceOrderPayment']);

                Route::post('/order-cancelled', ['as' => 'post:other_service:order_cancelled', 'uses' => 'Api\OtherService\UserController@postOtherServiceOrderCancelled']);

                Route::post('/add-tip', ['as' => 'post:other_service:order_add_tip', 'uses' => 'Api\OtherService\UserController@postOtherServiceOrderAddTip']);
                Route::post('/order-rating', ['as' => 'post:other_service:order_rating', 'uses' => 'Api\OtherService\UserController@postOtherServiceOrderRating']);
                Route::post('/provider-review', ['as' => 'post:other_service:provider_review', 'uses' => 'Api\OtherService\UserController@postOtherServiceProviderReview']);
                Route::post('/provider-gallery', ['as' => 'post:other_service:provider_gallery', 'uses' => 'Api\OtherService\UserController@postOtherServiceProviderGallery']);
            });

//            Route::prefix('delivery')->group(function () {
//                Route::post('/store-list', ['as' => 'post:delivery:store_list', 'uses' => 'Api\Store\UserController@postStoreList']);
//                Route::post('/search-store', ['as' => 'post:delivery:search_store', 'uses' => 'Api\Store\UserController@postSearchStore']);
//                Route::post('/search-product', ['as' => 'post:delivery:search_all_product', 'uses' => 'Api\Store\UserController@postSearchAllProduct']);
//                Route::post('/store-details', ['as' => 'post:delivery:store_details', 'uses' => 'Api\Store\UserController@postStoreDetails']);
//                Route::post('/store-product-category-list', ['as' => 'post:delivery:product_category_list', 'uses' => 'Api\Store\UserController@postStoreProductCategoryList']);
//                Route::post('/store-place-order', ['as' => 'post:delivery:store_place_order', 'uses' => 'Api\Store\UserController@postStorePlaceOrder']);
//                Route::post('/track-order', ['as' => 'post:delivery:track_order', 'uses' => 'Api\Store\UserController@postStoreTrackOrder']);
//                Route::post('/order-details', ['as' => 'post:delivery:order_details', 'uses' => 'Api\Store\UserController@postStoreOrderDetails']);
//                Route::post('/order-history', ['as' => 'post:delivery:order_history', 'uses' => 'Api\Store\UserController@postStoreOrderHistory']);
//                Route::post('/order-cancelled', ['as' => 'post:delivery:order_cancelled', 'uses' => 'Api\Store\UserController@postStoreOrderCancelled']);
//                Route::post('/order-rating', ['as' => 'post:delivery:order_rating', 'uses' => 'Api\Store\UserController@postStoreOrderRating']);
//                Route::post('/order-preview', ['as' => 'post:delivery:order_preview', 'uses' => 'Api\Store\UserController@postOrderPreview']);
//                Route::post('/store-product-list', ['as' => 'post:delivery:store_product_details', 'uses' => 'Api\Store\UserController@postStoreProductList']);
//
//                //user favourite store
//                Route::post('/add-favourite-store', ['as' => 'post:delivery:add_favourite_store', 'uses' => 'Api\Store\UserController@postAddFavouriteStore']);
//                Route::post('/favourite-store-lists', ['as' => 'post:delivery:favourite_store_lists', 'uses' => 'Api\Store\UserController@postFavouriteStoreLists']);
//
//                //user reorder
//                Route::post('/re-order', ['as' => 'post:delivery:re_order', 'uses' => 'Api\Store\UserController@postDeliveryReorder']);
//            });

            Route::post('/support-pages', ['as' => 'post:support_pages', 'uses' => 'Api\CustomerApiController@postMyCheckoutSupportPages']);
        });

//        Route::prefix('driver')->group(function () {
//            Route::post('/app-version-check', ['as' => 'post:app-version-check', 'uses' => 'Api\CustomerApiController@postAppVersionCheck']);
//            Route::post('/country-and-currency-list', ['as' => 'post:driver:country-and-currency-list', 'uses' => 'Api\CustomerApiController@postCountryAndCurrencyList']);
//            Route::post('/facebook-delete-driver',['as'=>'post:driver:facebook_data_deletion','uses'=>'Api\CustomerApiController@postFacebookDriverDataDeletion']);
//            Route::post('/login', ['as' => 'post:driver:login', 'uses' => 'Api\Auth\LoginController@postTransportDriverLogin']);
//            Route::post('/register', ['as' => 'post:driver:register', 'uses' => 'Api\Auth\RegisterController@postTransportDriverRegister']);
//            Route::post('/social-required-field', ['as' => 'post:driver:social_required_field', 'uses' => 'Api\Auth\UpdateRegisterController@postDriverSocialRequiredField']);
//            Route::post('/edit-profile', ['as' => 'post:driver:edit_profile', 'uses' => 'Api\Auth\UpdateRegisterController@postUpdateDriverDetails']);
//            Route::post('/change-password', ['as' => 'post:driver:change_password', 'uses' => 'Api\Auth\ResetPasswordController@postDriverChangePassword']);
//            Route::post('/update-country-and-currency', ['as' => 'post:driver:update-country-and-currency', 'uses' => 'Api\Auth\UpdateRegisterController@postDriverUpdateCountryAndCurrency']);
//
//            Route::post('/contact-verification', ['as' => 'post:driver:contact_verification', 'uses' => 'Api\Auth\UpdateRegisterController@postDriverContactVerification']);
//            Route::post('/resend-otp-verification', ['as' => 'post:driver:resend_otp_verification', 'uses' => 'Api\Auth\UpdateRegisterController@postDriverResendOtpVerification']);
//
//            Route::post('/resend-verification-link', ['as' => 'post:driver:resend_verification_link', 'uses' => 'Api\Auth\UpdateRegisterController@postDriverResendVerificationLink']);
//
//            Route::post('/forgot-password-request', ['as' => 'post:driver:forgot_password_request', 'uses' => 'Api\Auth\ResetPasswordController@postTransportDriverForgotPasswordRequest']);
//            Route::post('/forgot-change-password', ['as' => 'post:driver:forgot_change_password', 'uses' => 'Api\Auth\ResetPasswordController@postTransportDriverForgotChangePassword']);
//
//            Route::post('/logout', ['as' => 'post:driver:logout', 'uses' => 'Api\Auth\LogoutController@postDriverLogout']);
//
//            Route::post('/check-service-status', ['as' => 'post:driver:check_service_status', 'uses' => 'Api\Transport\DriverController@postDriverCheckServiceStatus']);
//
//            Route::post('/get-service-type-document-list', ['as' => 'post:driver:service_document', 'uses' => 'Api\Transport\DriverController@postGetServiceTypeDocumentList']);
//
//            Route::post('/service-register', ['as' => 'post:driver:service_register', 'uses' => 'Api\Transport\DriverController@postTransportDriverServiceRegister']);
//
//            Route::post('/update-current-status', ['as' => 'post:driver:update_current_status', 'uses' => 'Api\Transport\DriverController@postDriverUpdateCurrentStatus']);
//
//            Route::post('/home', ['as' => 'post:driver:home', 'uses' => 'Api\Transport\DriverController@postDriverHome']);
//
//            Route::post('/update-current-lat-long', ['as' => 'post:driver:update_current_lat_long', 'uses' => 'Api\Transport\DriverController@postDriverUpdateCurrentLatLong']);
//
//
//            Route::post('/ride-details', ['as' => 'post:driver:ride_details', 'uses' => 'Api\Transport\DriverController@postDriverRideDetails']);
//
//            Route::post('/ride-history', ['as' => 'post:driver:ride_history', 'uses' => 'Api\Transport\DriverController@postDriverRideHistory']);
//
//            Route::post('/update-bank-details', ['as' => 'post:driver:update_bank_history', 'uses' => 'Api\Transport\DriverController@postUpdateDriverBankDetails']);
//            Route::post('/get-bank-details', ['as' => 'post:driver:get_bank_history', 'uses' => 'Api\Transport\DriverController@postGetDriverBankDetails']);
//
//            Route::post('/ride-feedback', ['as' => 'post:driver:get_driver_feedback', 'uses' => 'Api\Transport\DriverController@postGetDriverFeedback']);
//
//
//            //testing pending
//            Route::post('/accept-request', ['as' => 'post:driver:accept_request', 'uses' => 'Api\Transport\DriverController@postDriverAcceptRequest']);
//            Route::post('/get-new-notification-request', ['as' => 'post:driver:new_notification_request', 'uses' => 'Api\Transport\DriverController@postGetDriverNewNotificationRequest']);
//            Route::post('/driver-reject-notification', ['as' => 'post:driver:driver_reject_notification', 'uses' => 'Api\Transport\DriverController@postDriverRejectNotification']);//only use (reject-request)API
//
//            //status updating
//            Route::post('/update-ride-status', ['as' => 'post:driver:update_ride_status', 'uses' => 'Api\Transport\DriverController@postDriverUpdateRideStatus']);
//            //Route::post('/update-delivery-status', ['as' => 'post:driver:update_delivery_status', 'uses' => 'Api\Transport\DriverController@postDriverUpdateDeliveryStatus']);
//
//            Route::post('/manage-document-list', ['as' => 'post:driver:manage_document_list', 'uses' => 'Api\Transport\DriverController@postDriverManageDocumentList']);
//            Route::post('/upload-single-document', ['as' => 'post:driver:upload_single_document', 'uses' => 'Api\Transport\DriverController@postDriverUploadSingleDocument']);
//            Route::post('/get-running-service', ['as' => 'post:driver:get_running_service', 'uses' => 'Api\Transport\DriverController@postDriverGetRunningService']);
//            Route::post('/get-vehicle-details', ['as' => 'post:driver:get_vehicle_details', 'uses' => 'Api\Transport\DriverController@postDriverGetVehicleDetails']);
//            Route::post('/update-vehicle-details', ['as' => 'post:driver:update_vehicle_details', 'uses' => 'Api\Transport\DriverController@postDriverUpdateVehicleDetails']);
//
//            Route::post('/transport-ride-invoice', ['as' => 'post:driver:transport_ride_invoice', 'uses' => 'Api\Transport\DriverController@postDriverTransportRideInvoice']);
//            Route::post('/delivery-ride-invoice', ['as' => 'post:driver:delivery_ride_invoice', 'uses' => 'Api\Transport\DriverController@postDriverDeliveryRideInvoice']);
//
//            Route::post('/delivery-order-details', ['as' => 'post:driver:delivery_order_details', 'uses' => 'Api\Transport\DriverController@postDriverDeliveryOrderDetails']);
//
//            Route::post('/support-pages', ['as' => 'post:support_pages', 'uses' => 'Api\CustomerApiController@postMyDriverSupportPages']);
//
//            Route::post('/change-contact-number', ['as' => 'post:provider:change_contact_number', 'uses' => 'Api\Auth\UpdateRegisterController@postProviderChangeContactNumber']);
//
//            Route::post('/reject-request', ['as' => 'post:driver:reject_request', 'uses' => 'Api\Transport\DriverController@postDriverRejectRequest']);
//
//            Route::post('/start-request', ['as' => 'post:driver:start_request', 'uses' => 'Api\Transport\DriverController@postDriverStartRequest']);
//
//            Route::post('/add-card', ['as' => 'post:driver:add_card', 'uses' => 'Api\ProviderApiController@postDriverAddCard']);
//            Route::post('/delete-card', ['as' => 'post:driver:remove_card', 'uses' => 'Api\ProviderApiController@postDriverRemoveCard']);
//            Route::post('/card-list', ['as' => 'post:driver:card_list', 'uses' => 'Api\ProviderApiController@postDriverCardList']);
//
//            Route::post('/add-wallet-balance', ['as' => 'post:driver:add_wallet_balance', 'uses' => 'Api\ProviderApiController@postDriverAddWalletBalance']);
//            Route::post('/wallet-transaction', ['as' => 'post:driver:wallet_transaction', 'uses' => 'Api\ProviderApiController@postDriverWalletTransaction']);
//            Route::post('/get-wallet-balance', ['as' => 'post:driver:get_wallet_balance', 'uses' => 'Api\ProviderApiController@postDriverGetWalletBalance']);
//
//            Route::post('/search-wallet-transfer-user-list', ['as' => 'post:driver:search_wallet_transfer_user_list', 'uses' => 'Api\ProviderApiController@postDriverSearchWalletTransferUserList']);
//            Route::post('/wallet-transfer', ['as' => 'post:driver:wallet_to_wallet_transfer', 'uses' => 'Api\ProviderApiController@postDriverWalletToWalletTransfer']);
//
//            Route::prefix('rental')->group(function () {
//                Route::post('/ride-details', ['as' => 'post:driver:transport_ride_details', 'uses' => 'Api\Transport\Rental\DriverController@postTransportRentalRideDetails']);
//                Route::post('/update-ride-status', ['as' => 'post:driver:transport_ride_status', 'uses' => 'Api\Transport\Rental\DriverController@postTransportRentalRideUpdateStatus']);
//            });
//        });

//        Route::prefix('store')->group(function () {
//            Route::post('/app-version-check', ['as' => 'post:app-version-check', 'uses' => 'Api\CustomerApiController@postAppVersionCheck']);
//            Route::post('/country-and-currency-list', ['as' => 'post:store:country-and-currency-list', 'uses' => 'Api\CustomerApiController@postCountryAndCurrencyList']);
//
//            Route::post('/login', ['as' => 'post:store:login', 'uses' => 'Api\Auth\LoginController@postStoreLogin']);
//            Route::post('/register', ['as' => 'post:store:register', 'uses' => 'Api\Auth\RegisterController@postStoreRegister']);
//            Route::post('/social-required-field', ['as' => 'post:store:social_required_field', 'uses' => 'Api\Auth\UpdateRegisterController@postStoreSocialRequiredField']);
//            Route::post('/edit-profile', ['as' => 'post:store:edit_profile', 'uses' => 'Api\Auth\UpdateRegisterController@postUpdateStoreDetails']);
//            Route::post('/change-password', ['as' => 'post:store:change_password', 'uses' => 'Api\Auth\ResetPasswordController@postStoreChangePassword']);
//            Route::post('/update-country-and-currency', ['as' => 'post:store:update-country-and-currency', 'uses' => 'Api\Auth\UpdateRegisterController@postStoreUpdateCountryAndCurrency']);
//            Route::post('/logout', ['as' => 'post:store:logout', 'uses' => 'Api\Auth\LogoutController@postStoreLogout']);
//
//            Route::post('/contact-verification', ['as' => 'post:store:contact_verification', 'uses' => 'Api\Auth\UpdateRegisterController@postStoreContactVerification']);
//            Route::post('/resend-otp-verification', ['as' => 'post:store:resend_otp_verification', 'uses' => 'Api\Auth\UpdateRegisterController@postStoreResendOtpVerification']);
//
//            Route::post('/resend-verification-link', ['as' => 'post:store:resend_verification_link', 'uses' => 'Api\Auth\UpdateRegisterController@postStoreResendVerificationLink']);
//
//            Route::post('/forgot-password-request', ['as' => 'post:store:forgot_password_request', 'uses' => 'Api\Auth\ResetPasswordController@postStoreForgotPasswordRequest']);
//            Route::post('/forgot-change-password', ['as' => 'post:store:forgot_change_password', 'uses' => 'Api\Auth\ResetPasswordController@postStoreForgotChangePassword']);
//
//            Route::post('/check-service-status', ['as' => 'post:store:check_service_status', 'uses' => 'Api\Store\StoreController@postStoreCheckServiceStatus']);
////    Route::post('/service-details', ['as' => 'post:store:service_details', 'uses' => 'Api\Store\StoreController@postStoreServiceDetails']);
//
//            Route::post('/store-details', ['as' => 'post:store:store_details', 'uses' => 'Api\Store\StoreController@postStoreDetails']);
//
//            Route::post('/get-and-update-offer', ['as' => 'post:store:get_and_update_offer', 'uses' => 'Api\Store\StoreController@postStoreGetAndUpdateOffer']);
//
//            Route::post('/update-current-status', ['as' => 'post:store:update_current_status', 'uses' => 'Api\Store\StoreController@postStoreUpdateCurrentStatus']);
//
//            Route::post('/get-and-update-settings', ['as' => 'post:store:get_and_update_settings', 'uses' => 'Api\Store\StoreController@postStoreGetAndUpdateSettings']);
//
//            Route::post('/product-list', ['as' => 'post:store:product_list', 'uses' => 'Api\Store\StoreController@postStoreProductList']);
//            Route::post('/update-product-status', ['as' => 'post:store:update_product_status', 'uses' => 'Api\Store\StoreController@postStoreUpdateProductStatus']);
//
//            Route::post('/order-details', ['as' => 'post:store:order_details', 'uses' => 'Api\Store\StoreController@postStoreOrderDetails']);
//
//            Route::post('/order-history', ['as' => 'post:store:order_history', 'uses' => 'Api\Store\StoreController@postStoreOrderHistory']);
//
//            Route::post('/home', ['as' => 'post:store:home', 'uses' => 'Api\Store\StoreController@postStoreHome']);
//
//            Route::post('/update-order-status', ['as' => 'post:store:update_order_status', 'uses' => 'Api\Store\StoreController@postStoreUpdateOrderStatus']);
//            Route::post('/shopper-order-rating', ['as' => 'post:store:shopper_order_rating', 'uses' => 'Api\Store\StoreController@postStoreShopperOrderRating']);
//
//            Route::post('/support-pages', ['as' => 'post:support_pages', 'uses' => 'Api\CustomerApiController@postMyStoreSupportPages']);
//
//            Route::post('/change-contact-number', ['as' => 'post:provider:change_contact_number', 'uses' => 'Api\Auth\UpdateRegisterController@postProviderChangeContactNumber']);
//
//            Route::post('/add-card', ['as' => 'post:store:add_card', 'uses' => 'Api\ProviderApiController@postStoreAddCard']);
//            Route::post('/delete-card', ['as' => 'post:store:remove_card', 'uses' => 'Api\ProviderApiController@postStoreRemoveCard']);
//            Route::post('/card-list', ['as' => 'post:store:card_list', 'uses' => 'Api\ProviderApiController@postStoreCardList']);
//
//            Route::post('/add-wallet-balance', ['as' => 'post:store:add_wallet_balance', 'uses' => 'Api\ProviderApiController@postStoreAddWalletBalance']);
//            Route::post('/wallet-transaction', ['as' => 'post:store:wallet_transaction', 'uses' => 'Api\ProviderApiController@postStoreWalletTransaction']);
//            Route::post('/get-wallet-balance', ['as' => 'post:store:get_wallet_balance', 'uses' => 'Api\ProviderApiController@postStoreGetWalletBalance']);
//
//            Route::post('/search-wallet-transfer-user-list', ['as' => 'post:store:search_wallet_transfer_user_list', 'uses' => 'Api\ProviderApiController@postStoreSearchWalletTransferUserList']);
//            Route::post('/wallet-transfer', ['as' => 'post:store:wallet_to_wallet_transfer', 'uses' => 'Api\ProviderApiController@postStoreWalletToWalletTransfer']);
//        });

        Route::prefix('on-demand')->group(function () {
            Route::post('/remove-account',['as'=>'post:on_demand:remove_account','uses'=>'Api\CustomerApiController@postProviderRemoveAccount']);
            Route::post('/app-version-check', ['as' => 'post:app-version-check', 'uses' => 'Api\CustomerApiController@postAppVersionCheck']);
            Route::post('/country-and-currency-list', ['as' => 'post:on_demand:country-and-currency-list', 'uses' => 'Api\CustomerApiController@postCountryAndCurrencyList']);
            Route::post('/facebook-delete-provider',['as'=>'post:driver:facebook_data_deletion','uses'=>'Api\CustomerApiController@postFacebookProviderDataDeletion']);
            Route::post('/login', ['as' => 'post:on_demand:login', 'uses' => 'Api\Auth\LoginController@postOnDemandLogin']);
            Route::post('/register', ['as' => 'post:on_demand:register', 'uses' => 'Api\Auth\RegisterController@postOnDemandRegister']);
            Route::post('/social-required-field', ['as' => 'post:on_demand:social_required_field', 'uses' => 'Api\Auth\UpdateRegisterController@postOnDemandSocialRequiredField']);
            Route::post('/edit-profile', ['as' => 'post:on_demand:edit_profile', 'uses' => 'Api\Auth\UpdateRegisterController@postUpdateOnDemandProviderDetails']);
            Route::post('/change-password', ['as' => 'post:on_demand:change_password', 'uses' => 'Api\Auth\ResetPasswordController@postProviderChangePassword']);
            Route::post('/update-country-and-currency', ['as' => 'post:on_demand:update-country-and-currency', 'uses' => 'Api\Auth\UpdateRegisterController@postProviderUpdateCountryAndCurrency']);
            Route::post('/logout', ['as' => 'post:on_demand:logout', 'uses' => 'Api\Auth\LogoutController@postProviderLogout']);

            Route::post('/contact-verification', ['as' => 'post:on_demand:contact_verification', 'uses' => 'Api\Auth\UpdateRegisterController@postOnDemandContactVerification']);
            Route::post('/resend-otp-verification', ['as' => 'post:on_demand:resend_otp_verification', 'uses' => 'Api\Auth\UpdateRegisterController@postOnDemandResendOtpVerification']);

            Route::post('/forgot-password-request', ['as' => 'post:on_demand:forgot_password_request', 'uses' => 'Api\Auth\ResetPasswordController@postOnDemandForgotPasswordRequest']);
            Route::post('/forgot-change-password', ['as' => 'post:on_demand:forgot_change_password', 'uses' => 'Api\Auth\ResetPasswordController@postOnDemandForgotChangePassword']);

            Route::post('/provider-service-register-step', ['as' => 'post:on_demand:provider_service_register_step', 'uses' => 'Api\OtherService\ProviderController@postOnDemandProviderServiceRegisterStep']);

            Route::post('/get-service-list', ['as' => 'post:on_demand:service_list', 'uses' => 'Api\OtherService\ProviderController@postOnDemandServiceList']);
            Route::post('/provider-service-list', ['as' => 'post:on_demand:provider_service_list', 'uses' => 'Api\OtherService\ProviderController@postOnDemandProviderServiceList']);
            Route::post('/add-services', ['as' => 'post:on_demand:add_services', 'uses' => 'Api\OtherService\ProviderController@postOnDemandAddServices']);
            Route::post('/change-service-current-status', ['as' => 'post:on_demand:change_service_current_status', 'uses' => 'Api\OtherService\ProviderController@postOnDemandChangeServiceCurrentStatus']);
            Route::post('/remove-service', ['as' => 'post:on_demand:remove_service', 'uses' => 'Api\OtherService\ProviderController@postOnDemandRemoveService']);
            Route::post('/package-list', ['as' => 'post:on_demand:package_list', 'uses' => 'Api\OtherService\ProviderController@postOnDemandPackageList']);
            Route::post('/get-category-list', ['as' => 'post:on_demand:get_category_list', 'uses' => 'Api\OtherService\ProviderController@postOnDemandGetCategoryList']);
            Route::post('/add-update-package', ['as' => 'post:on_demand:add_update_package', 'uses' => 'Api\OtherService\ProviderController@postOnDemandAddUpdatePackage']);
            Route::post('/change-package-status', ['as' => 'post:on_demand:change_package_status', 'uses' => 'Api\OtherService\ProviderController@postOnDemandChangePackageStatus']);
            Route::post('/delete-package', ['as' => 'post:on_demand:delete_package', 'uses' => 'Api\OtherService\ProviderController@postOnDemandDeletePackage']);
            Route::post('/document-list', ['as' => 'post:on_demand:document_list', 'uses' => 'Api\OtherService\ProviderController@postOnDemandDocumentList']);

            Route::post('/order-feedback', ['as' => 'post:on_demand:get_order_feedback', 'uses' => 'Api\OtherService\ProviderController@postGetOnDemandFeedback']);

            Route::post('/upload-single-document', ['as' => 'post:on_demand:upload_single_document', 'uses' => 'Api\OtherService\ProviderController@postOnDemandUploadSingleDocument']);

            Route::post('/home', ['as' => 'post:on_demand:home', 'uses' => 'Api\OtherService\ProviderController@postOnDemandHome']);
            Route::post('/update-order-status', ['as' => 'post:on_demand:update_order_status', 'uses' => 'Api\OtherService\ProviderController@postOnDemandUpdateOrderStatus']);
            Route::post('/order-collect-payment', ['as' => 'post:on_demand:order_collect_payment', 'uses' => 'Api\OtherService\ProviderController@postOnDemandOrderCollectPayment']);

            Route::post('/order-details', ['as' => 'post:on_demand:order_details', 'uses' => 'Api\OtherService\ProviderController@postOnDemandOrderDetails']);

            Route::post('/order-rating', ['as' => 'post:on_demand:order_rating', 'uses' => 'Api\OtherService\ProviderController@postOnDemandOrderRating']);

            Route::post('/order-history', ['as' => 'post:on_demand:order_history', 'uses' => 'Api\OtherService\ProviderController@postOnDemandOrderHistory']);
            Route::post('/change-current-status', ['as' => 'post:on_demand:change_current_status', 'uses' => 'Api\OtherService\ProviderController@postOnDemandChangeCurrentStatus']);

            Route::post('/update-bank-details', ['as' => 'post:on_demand:update_bank_history', 'uses' => 'Api\OtherService\ProviderController@postUpdateProviderBankDetails']);
            Route::post('/get-bank-details', ['as' => 'post:on_demand:get_bank_history', 'uses' => 'Api\OtherService\ProviderController@postGetProviderBankDetails']);

            Route::post('/support-pages', ['as' => 'post:support_pages', 'uses' => 'Api\CustomerApiController@postMyServiceSupportPages']);

            Route::post('/change-contact-number', ['as' => 'post:provider:change_contact_number', 'uses' => 'Api\Auth\UpdateRegisterController@postProviderChangeContactNumber']);

            Route::post('/upload-portfolio-image', ['as' => 'post:on_demand:upload_portfolio_image', 'uses' => 'Api\OtherService\ProviderController@postOnDemandUploadPortfolioImage']);
            Route::post('/delete-portfolio-image', ['as' => 'post:on_demand:delete_portfolio_image', 'uses' => 'Api\OtherService\ProviderController@postOnDemandDeletePortfolioImage']);

            Route::post('/add-card', ['as' => 'post:on_demand:add_card', 'uses' => 'Api\ProviderApiController@postOnDemandAddCard']);
            Route::post('/delete-card', ['as' => 'post:on_demand:remove_card', 'uses' => 'Api\ProviderApiController@postOnDemandRemoveCard']);
            Route::post('/card-list', ['as' => 'post:on_demand:card_list', 'uses' => 'Api\ProviderApiController@postOnDemandCardList']);

            Route::post('/add-wallet-balance', ['as' => 'post:on_demand:add_wallet_balance', 'uses' => 'Api\ProviderApiController@postOnDemandAddWalletBalance']);
            Route::post('/wallet-transaction', ['as' => 'post:on_demand:wallet_transaction', 'uses' => 'Api\ProviderApiController@postOnDemandWalletTransaction']);
            Route::post('/get-wallet-balance', ['as' => 'post:on_demand:get_wallet_balance', 'uses' => 'Api\ProviderApiController@postOnDemandGetWalletBalance']);

            Route::post('/search-wallet-transfer-user-list', ['as' => 'post:on_demand:search_wallet_transfer_user_list', 'uses' => 'Api\ProviderApiController@postOnDemandSearchWalletTransferUserList']);
            Route::post('/wallet-transfer', ['as' => 'post:on_demand:wallet_to_wallet_transfer', 'uses' => 'Api\ProviderApiController@postOnDemandWalletToWalletTransfer']);

            Route::post('/update-open-time', ['as' => 'post:on_demand:update_open_time', 'uses' => 'Api\ProviderApiController@postOnDemandUpdateOpenTime']);
            Route::post('/update-work-schedule', ['as' => 'post:on_demand:update_work_schedule', 'uses' => 'Api\ProviderApiController@postOnDemandUpdateWorkSchedule']);
            Route::post('/update-work-status', ['as' => 'post:on_demand:update_work_status', 'uses' => 'Api\ProviderApiController@postOnDemandUpdateWorkStatus']);
            Route::post('/open-time-list', ['as' => 'post:on_demand:open_time_list', 'uses' => 'Api\ProviderApiController@postOnDemandOpenTimeList']);
        });

    });
});

