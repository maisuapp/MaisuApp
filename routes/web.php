<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
//    return view('homepage');
    return view('homepage');
})->name('front');



//code for set locale middelware Start User Route App.
//Route::group(['middleware' => 'setLocaleLang'], function () {
//
//    //route for front
////    Route::get('/login', ['as' => 'get:user:login', 'uses' => 'Auth\AuthPagesController@getUserLogin']);
//
//    Route::group(['middleware' => ['web','revalidate']], function () {
//        Route::get('change/lang', ['as' => 'get:lang_change', 'uses' => 'User\LangController@lang_change']);
//        Route::post('/getLocation', ['as' => 'post:getLocation', 'uses' => 'User\StoreController@getLocation']);
//        Route::post('/setTimezone', ['as' => 'post:setTimezone', 'uses' => 'User\LangController@setTimezone']);
//
//        Route::get('/', ['as' => 'get:homepage', 'uses' => 'User\StoreController@getHomepage']);
////        Route::get('/test', ['as' => 'get:homepage', 'uses' => 'User\StoreController@getHomepage']);
//        Route::get('/stores/{slug}', ['as' => 'get:storehomepage', 'uses' => 'User\StoreController@getStoreHomepage']);
//        Route::get('/ride-booking', ['as' => 'get:ride-booking', 'uses' => 'User\TransportController@getRideBooking']);
//
//        Route::get('/service-booking/{service_name}', ['as' => 'get:service-booking', 'uses' => 'User\OtherServiceController@getServiceBooking']);
//        Route::get('/search', ['as' => 'get:search', 'uses' => 'User\StoreController@getSearch']);
//        Route::get('{service_category_name}/stores-list/{location}', ['as' => 'get:search_location_restaurants_list', 'uses' => 'User\StoreController@getSearchLocationRestaurantsList']);
//
////            Route::get('{location}/restaurants-list/{search}', ['as' => 'get:search_restaurants_list', 'uses' => 'User\StoreController@getSearchRestaurantsList']);
//        Route::get('{service_category_name}/store-list/{location}/{search?}', ['as' => 'get:search_restaurants_list', 'uses' => 'User\StoreController@getSearchRestaurantsList']);
//
//        Route::get('{location}/offer-wise-store-list/', ['as' => 'get:search_offer_restaurants_list', 'uses' => 'User\StoreController@getSearchOfferRestaurantsList']);
//
////    Route::get('/restaurant/{location}/{slug}', ['as' => 'get:restaurant_data', 'uses' => 'User\StoreController@getRestaurantData']);
//        Route::get('/store-detail/{slug}', ['as' => 'get:restaurant_data', 'uses' => 'User\StoreController@getRestaurantData']);
////    Route::get('filter-cuisine', ['as' => 'get:filter-cuisine', 'uses' => 'User\StoreController@getFilterCuisine']);
//        Route::post('/user-login', ['as' => 'get:user_login', 'uses' => 'Auth\LoginController@getUserLogin']);
//        Route::get('/user-signup', ['as' => 'get:user_signup', 'uses' => 'Auth\RegisterController@getUserSignup']);
//        Route::post('/register', ['as' => 'post:provider-web:register', 'uses' => 'Auth\RegisterController@postWebProvierRegister']);
//
//        Route::get('city-list', ['as' => 'get:city_list', 'uses' => 'User\StoreController@getCityList']);
//        Route::get('/redirect/{service}', 'Auth\RegisterController@redirectToFacebook');
//        Route::get('/callback/{service}', 'Auth\RegisterController@handleFacebookCallback');
//
//        Route::get('/about-us', ['as' => 'get:page_about_us', 'uses' => 'User\StoreController@getAboutUs']);
//        Route::get('/faq', ['as' => 'get:page_faq', 'uses' => 'User\StoreController@getFaq']);
//        Route::get('/terms-conditions', ['as' => 'get:page_terms_conditions', 'uses' => 'User\StoreController@getTermsCondition']);
//        Route::get('/privacy', ['as' => 'get:page_privacy', 'uses' => 'User\StoreController@getPrivacy']);
//        Route::get('/contact-us', ['as' => 'get:contact-us', 'uses' => 'User\StoreController@getContactus']);
//
//        // Start route ride and taxi
//        Route::get('/booking/{service}', ['as' => 'get:transport-service-booking', 'uses' => 'User\TransportController@getRideBooking']);
//        Route::post('/ride-preview/{service}', ['as' => 'post:transport-ride-preview', 'uses' => 'User\TransportController@postRidePreview']);
//
//        // Start route provider service
////        Route::post('/get-provider-lists', ['as' => 'post:get-provider-lists', 'uses' => 'User\OtherServiceController@postgetProviderLists']);
//
//        //start route of coupan code
//        Route::get('/coupon-deals/coupon-lists', ['as' => 'get:coupon-list', 'uses' => 'User\CouponServiceController@getCouponList']);
//        Route::get('/coupon-deals/coupon-details/{coupon_id}', ['as' => 'get:coupon-details', 'uses' => 'User\CouponServiceController@getCouponDetails']);
//        Route::post('/coupon-deals/singel-cat-coupons', ['as' => 'post:singel-cat-coupons', 'uses' => 'User\CouponServiceController@getSingelCatCoupons']);
//
//        // End route ride and taxi
//
//        Route::group(['middleware' => 'auth:user'], function () {
//            Route::get('/otp-verify', ['as' => 'get:otp-verify', 'uses' => 'User\StoreController@getUserOtpVerify']);
//            Route::post('/otp-verify', ['as' => 'post:otp-verify', 'uses' => 'User\StoreController@postUserOtpVerify']);
//            Route::post('/resend-otp', ['as' => 'post:resend-otp', 'uses' => 'User\StoreController@postResendOtp']);
//            Route::get('/logout/{user}', 'Auth\LoginController@logout')->name('user:logout');
//
//            Route::group(['middleware' => 'VerifyUser'], function () {
//
//                Route::get('check-email', ['as' => 'post:check_email', 'uses' => 'User\StoreController@checkEmail']);
//
//                Route::get('/user-logout', ['as' => 'get:user_logout', 'uses' => 'Auth\LoginController@getUserLogout']);
//
//
//                Route::post('/temp-cart-store', ['as' => 'post:temp_cart_store', 'uses' => 'User\StoreController@postTempCartStore']);
//                Route::post('/temp-cart-update', ['as' => 'post:temp_cart_update', 'uses' => 'User\StoreController@postTempCartUpdate']);
//                Route::post('/temp-cart-delete', ['as' => 'post:temp_cart_delete', 'uses' => 'User\StoreController@postTempCartDelete']);
//                Route::get('/check-out', ['as' => 'get:checkout', 'uses' => 'User\StoreController@getCheckout']);
//                Route::post('/add-new-address', ['as' => 'post:new_address', 'uses' => 'User\StoreController@postNewAddress']);
//                Route::get('/get-delivery-cost', ['as' => 'get:delivery_cost', 'uses' => 'User\StoreController@postDeliveryCost']);
//
//                Route::post('/add-fevourite-restaurant', ['as' => 'post:fevourite_restaurant', 'uses' => 'User\StoreController@postFevouriteRestaurant']);
//
//                Route::post('/edit-user-address', ['as' => 'post:edit_user_address', 'uses' => 'User\StoreController@postEditUserAddress']);
//                Route::post('/delete-user-address', ['as' => 'post:delete_user_address', 'uses' => 'User\StoreController@postDeleteUserAddress']);
//
//                Route::post('/delete-user-profile-image', ['as' => 'post:delete_user_profile_image', 'uses' => 'User\StoreController@postDeleteUserProfileImage']);
////        Route::post('/upload-user-profile-image', ['as' => 'post:upload_user_profile_image', 'uses' => 'User\StoreController@postUploadUserProfileImage']);
//
//                Route::post('/update-user-profile-details', ['as' => 'post:update_user_profile_details', 'uses' => 'User\StoreController@postUpdateUserProfileDetails']);
//
//                Route::any('/user-order', ['as' => 'post:user_order', 'uses' => 'User\StoreController@postPlaceOrder']);
//
//                Route::get('/profile', ['as' => 'get:user_profile', 'uses' => 'User\StoreController@getProfile']);
//                Route::get('/change-password', ['as' => 'get:user_change_password', 'uses' => 'User\StoreController@getUserChangePassword']);
//                Route::post('/change-password', ['as' => 'post:user_change_password', 'uses' => 'Auth\ResetPasswordController@postUserChangePassword']);
//                Route::get('/orders', ['as' => 'get:user_orders', 'uses' => 'User\StoreController@getOrders']);
//                Route::get('/orders/{service_type}/{filter_type}/{page?}', ['as' => 'get:user_orders_all', 'uses' => 'User\StoreController@getOrdersAll']);
//                Route::get('/offers', ['as' => 'get:user_offers', 'uses' => 'User\StoreController@getOffers']);
//                Route::get('/payments', ['as' => 'get:user_payments', 'uses' => 'User\StoreController@getPayments']);
//                Route::get('/my-coupons', ['as' => 'get:my_coupons', 'uses' => 'User\StoreController@getMyCoupons']);
//                Route::get('/my-coupon-details/{coupon_id}', ['as' => 'get:my_coupon_details', 'uses' => 'User\StoreController@getMyCouponDetails']);
//                Route::get('/favourites', ['as' => 'get:user_favourites', 'uses' => 'User\StoreController@getFavourites']);
//                Route::get('/address', ['as' => 'get:user_address', 'uses' => 'User\StoreController@getAddress']);
//
//                Route::post('/add-card', ['as' => 'post:add_user_card', 'uses' => 'User\StoreController@getAddUserCard']);
//                Route::get('/delete-card', ['as' => 'get:delete_user_card', 'uses' => 'User\StoreController@getDeleteUserCard']);
//
//                Route::get('/store-order-details/{order_id}', ['as' => 'get:store_order_details', 'uses' => 'User\StoreController@getStoreOrderDetails']);
//                //code for get topping data for display
//                Route::post('/get-product-toppings', ['as' => 'post:get_product_toppings', 'uses' => 'User\StoreController@postGetProductToppings']);
//
//                //code for wallet
//                Route::get('/wallet/{page?}', ['as' => 'get:user_wallet', 'uses' => 'User\StoreController@getWallet']);
//                Route::post('/wallet', ['as' => 'post:user_wallet', 'uses' => 'User\StoreController@postWallet']);
//
//                // code for tranport ride booking data
//                Route::post('/ride-book/{service}', ['as' => 'post:transport-ride-book', 'uses' => 'User\TransportController@postRideBook']);
//                Route::post('/ride-cancel', ['as' => 'post:transport-ride-cancel', 'uses' => 'User\TransportController@postRideCancel']);
//                Route::post('/check-ride-status', ['as' => 'post:transport-check-ride-status', 'uses' => 'User\TransportController@postCheckRideStatus']);
//                Route::get('/ride-details/{ride_id}', ['as' => 'get:ride_details', 'uses' => 'User\TransportController@getRideDetails']);
//
//                // code for Courier ride booking data
//                /*Route::post('/ride-book/{service}', ['as' => 'post:transport-ride-book', 'uses' => 'User\TransportController@postRideBook']);
//                Route::post('/ride-cancel', ['as' => 'post:transport-ride-cancel', 'uses' => 'User\TransportController@postRideCancel']);
//                Route::post('/check-ride-status', ['as' => 'post:transport-check-ride-status', 'uses' => 'User\TransportController@postCheckRideStatus']);
//                Route::get('/ride-details/{ride_id}', ['as' => 'get:ride_details', 'uses' => 'User\TransportController@getRideDetails']);*/
//
//
//                // Start route provider service
//                Route::post('/get-provider-lists', ['as' => 'post:get-provider-lists', 'uses' => 'User\OtherServiceController@postgetProviderLists']);
//                Route::post('/get-provider-with-package-lists', ['as' => 'post:get-provider-with-package-lists', 'uses' => 'User\OtherServiceController@getProviderWithPackageLists']);
//                Route::post('/get-add-update-provider-package', ['as' => 'post:get-add-update-provider-package', 'uses' => 'User\OtherServiceController@getAddUpdateProviderPackage']);
//                Route::post('/post-other-service-placeOrder', ['as' => 'post:post-OtherServicePlaceOrder', 'uses' => 'User\OtherServiceController@postOtherServicePlaceOrder']);
//                Route::get('/provider-order-details/{order_id}', ['as' => 'get:provider_order_details', 'uses' => 'User\OtherServiceController@getProviderOrderDetails']);
//
//                //cancel ride ,service
//                Route::get('/update-other-service-order-status', ['as' => 'get:update_other_service_order_status', 'uses' => 'User\OtherServiceController@getUpdateOtherServiceOrderStatus']);
//                Route::get('/update-ride-status', ['as' => 'get:transport_update_ride_status', 'uses' => 'User\TransportController@getTransportUpdateRideStatus']);
//                Route::get('store-order-status', ['as' => 'post:order_status', 'uses' => 'User\StoreController@postOrderStatus']);
//
//                Route::post('/coupon-deals/add-coupon-cart', ['as' => 'post:add-coupon-cart', 'uses' => 'User\CouponServiceController@getAddCouponCart']);
//                Route::post('/place-order-coupon', ['as' => 'post:place-order-coupon', 'uses' => 'User\CouponServiceController@getPlaceOrderCoupon']);
//
//                Route::get('payment-processing',['as' => 'get:payment-processing', 'uses' => 'User\PaymentController@getPaymentProcessing']);
//
//            });
//        });
//    });
//});
//End User Route App.

Route::get('/coupon-code-verify', ['as' => 'get:couponcode_verify', 'uses' => 'HomeController@getCuponcodeVerify']);
Route::post('/coupon-code-verify', ['as' => 'post:couponcode_verify', 'uses' => 'HomeController@postCuponcodeVerify']);


// stripe
Route::any('/stripe-success',[StripeController::class,'stripeSuccess'])->name('stripe.success');
Route::any('/stripe-fail/{id?}',[StripeController::class,'stripeFail'])->name('stripe.fail');

Route::get('/pay-success',[StripeController::class,'paySuccess'])->name('pay.success');
Route::get('/pay-fail',[StripeController::class,'payFail'])->name('pay.fail');

//paymongo routes

//web hook success
Route::get('mongo-pay-success/{user_id}/{order_id}', array('as' => 'mongo_pay.success','uses' => 'PaymentController@getMongoPaySuccess',));
//web hook failed
Route::get('mongo-pay-failed/{user_id}/{order_id}', array('as' => 'mongo_pay.failed','uses' => 'PaymentController@getMongoPayFailed',));

Route::get('paypmongo-success/', array('as' => 'paypmongo.success','uses' => 'PaymentController@getPaypmongoSuccess',));
Route::get('paypmongo-failed', array('as' => 'paypmongo.failed','uses' => 'PaymentController@getPaypmongoFailed',));
Route::get('mongo-pay-error', array('as' => 'mongo_pay.error','uses' => 'PaymentController@getMongoPayError',));
//end paymongo routes

//paypal route's
Route::get('paypal', array('as' => 'payment.status','uses' => 'PaymentController@getPaymentStatus',));

Route::get('paypal-success', array('as' => 'paypal.success','uses' => 'PaymentController@getPaypalSuccess',));
Route::get('paypal-failed', array('as' => 'paypal.failed','uses' => 'PaymentController@getPaypalFailed',));
//end paypal routes

//support pages routes
Route::get('/terms-and-conditions', ['as' => 'get:terms-and-conditions', 'uses' => 'HomeController@getTermsAndConditions']);
Route::get('/disclaimer', ['as' => 'get:disclaimer', 'uses' => 'HomeController@getDisclaimer']);
Route::get('/privacy-policy', ['as' => 'get:privacy-policy', 'uses' => 'HomeController@getPrivacyPolicy']);
Route::get('/faq', ['as' => 'get:faq', 'uses' => 'HomeController@getFaq']);
Route::get('/security', ['as' => 'get:security', 'uses' => 'HomeController@getSecurity']);
//Route::get('/contact-us', ['as' => 'get:contact-us', 'uses' => 'HomeController@getContactUs']);

Route::get('/publication', ['as' => 'get:publication', 'uses' => 'HomeController@getPublication']);
Route::get('/news', ['as' => 'get:news', 'uses' => 'HomeController@getNews']);

Route::get('/provider/terms-and-conditions', ['as' => 'get:provider:terms-and-conditions', 'uses' => 'HomeController@getProviderTermsAndConditions']);
Route::get('/driver/terms-and-conditions', ['as' => 'get:driver:terms-and-conditions', 'uses' => 'HomeController@getDriverTermsAndConditions']);
Route::get('/store/terms-and-conditions', ['as' => 'get:store:terms-and-conditions', 'uses' => 'HomeController@getStoreTermsAndConditions']);
Route::get('/shopper/terms-and-conditions', ['as' => 'get:shopper:terms-and-conditions', 'uses' => 'HomeController@getShopperTermsAndConditions']);

Route::get('/provider/privacy-policy', ['as' => 'get:provider:privacy-policy', 'uses' => 'HomeController@getProviderPrivacyPolicy']);
Route::get('/driver/privacy-policy', ['as' => 'get:driver:privacy-policy', 'uses' => 'HomeController@getDriverPrivacyPolicy']);
Route::get('/store/privacy-policy', ['as' => 'get:store:privacy-policy', 'uses' => 'HomeController@getStorePrivacyPolicy']);
Route::get('/shopper/privacy-policy', ['as' => 'get:shopper:privacy-policy', 'uses' => 'HomeController@getShopperPrivacyPolicy']);
//end support pages routes

/* -----------------------------------For Play-Store, App-Store Upload account_deletion Module------------------------------ */
Route::get('/account-deletion/login', ['as' => 'get:account:deletion:login', 'uses' => 'HomeController@getAccountDeletion']);
Route::post('/account-deletion/login', ['as' => 'post:account:deletion:login', 'uses' => 'HomeController@postAccountDeletion']);

//Route::get('/account-deletion/verification', ['as' => 'get:account:deletion:verification', 'uses' => 'HomeController@getAccountDeletionVerification']);
//Route::post('/account-deletion/verification', ['as' => 'post:account:deletion:verification', 'uses' => 'HomeController@postAccountDeletionVerification']);

//Route::get('/account-deletion/resend-verification-code', ['as' => 'get:account:deletion:resend-verification-code', 'uses' => 'HomeController@getAccountDeletionRensendVerificationCode']);

Route::get('/account-deletion/profile', ['as' => 'get:account:deletion:profile', 'uses' => 'HomeController@getAccountDeletionProfile']);

Route::get('/account-deletion/logout/{guard}', ['as' => 'get:account:deletion:logout', 'uses' => 'HomeController@getAccountDeletionLogout']);

// Delete Account
Route::post('/account-deletion/delete-account', ['as' => 'post:account:deletion:delete-account:logout', 'uses' => 'HomeController@postAccountDeletionDeleteAccount']);

/// Social Login
Route::get('{guards}/auth/{provider}', 'HomeController@redirectToProvider');
Route::get('auth/{provider}/callback', 'HomeController@handleCallback');

/* -----------------------------------End For Play-Store, App-Store Upload account_deletion Module-------------------------- */


Route::get('/return-url', ['as' => 'get:return-url', 'uses' => 'HomeController@getReturnURL']);
Route::get('/ipn-url', ['as' => 'get:ipn-url', 'uses' => 'HomeController@getIPNURL']);

Route::get('/deletion/{reference}', ['as' => 'get:deletion_status', 'uses' => 'HomeController@postDataDeletionStatus']);
Route::get('/provider-documents/{filename}', 'HomeController@getfile')->name('get.file');

//super admin
Route::group(['middleware' => 'revalidate'], function () {
    Route::get('/admin/login', ['as' => 'get:admin:login', 'uses' => 'Auth\AuthPagesController@getAdminLogin']);
    Route::post('/admin/login', ['as' => 'post:admin:update_super_admin_login', 'uses' => 'Auth\LoginController@postSuperAdminLogin']);
});
Route::prefix('admin')->group(function () {
    Route::group(['middleware' => 'auth:admin'], function () {
        Route::get('/logout/{admin}', 'Auth\LoginController@logout')->name('admin:logout');
        Route::group(['middleware' => 'revalidate'], function () {

            Route::get('/vehicle-type-list', ['as' => 'get:admin:vehicle_type_list', 'uses' => 'TransportController@getTransportVehicleTypeList']);

            Route::group(['middleware' => 'adminrole'], function () {
                //test-mail
                Route::get('/test-mail', ['as' => 'get:admin:test_mail', 'uses' => 'AdminController@getAdminTest_Mail']);

                Route::get('/change-password', ['as' => 'get:admin:change_password', 'uses' => 'Auth\ResetPasswordController@getAdminChangePassword']);
                Route::post('/change-password', ['as' => 'post:admin:change_password', 'uses' => 'Auth\ResetPasswordController@postAdminChangePassword']);
                Route::get('/dashboard', ['as' => 'get:admin:dashboard', 'uses' => 'AdminController@getAdminDashboard']);


                //world cities list
                Route::get('/world-country-list', ['as' => 'get:admin:world_country_list', 'uses' => 'AdminController@getAdminWorldCountryList']);
                Route::get('/add-country', ['as' => 'get:admin:add_country', 'uses' => 'AdminController@getAdminAddCountry']);
                //Route::get('/country-city-list/{slug}', ['as' => 'get:admin:country_city_list', 'uses' => 'AdminController@getAdminCountryCityList']);
                //Route::get('/add-city/{slug}', ['as' => 'get:admin:add_city', 'uses' => 'AdminController@getAdminAddCity']);
                //Route::post('/update-country-city', ['as' => 'post:admin:update_country_city', 'uses' => 'AdminController@postUpdateCountryCity']);
                Route::post('/update-country', ['as' => 'post:admin:update_country', 'uses' => 'AdminController@postUpdateCountry']);

                Route::get('/world-currency-list', ['as' => 'get:admin:world_currency_list', 'uses' => 'AdminController@getAdminWorldCurrencyList']);
                Route::post('/world-currency-list', ['as' => 'post:admin:world_currency_list', 'uses' => 'AdminController@postAdminWorldCurrencyList']);

                //service category
                Route::get('/service-category-list', ['as' => 'get:admin:service_category_list', 'uses' => 'AdminController@getServiceCategoryList']);
                Route::get('/service-category-change-status', ['as' => 'get:admin:service_category_change_status', 'uses' => 'AdminController@getServiceCategoryChangeStatus']);

                Route::get('/add-service-category', ['as' => 'get:admin:add_service_category', 'uses' => 'AdminController@getAddServiceCategory']);
                Route::get('/edit-service-category/{slug}', ['as' => 'get:admin:edit_service_category', 'uses' => 'AdminController@getEditServiceCategory']);
                Route::get('/delete-service-category', ['as' => 'get:admin:delete_service_category', 'uses' => 'AdminController@getDeleteServiceCategory']);
                Route::post('/update-service-category', ['as' => 'post:admin:update_service_category', 'uses' => 'AdminController@postUpdateServiceCategory']);

                //store and transport service category edited
                Route::get('/edit-all-service-category/{slug}', ['as' => 'get:admin:edit_all_service_category', 'uses' => 'AdminController@getEditServiceCategory']);
                Route::post('/update-all-service-category', ['as' => 'post:admin:update_all_service_category', 'uses' => 'AdminController@postUpdateServiceCategory']);

                //user
                Route::get('/customer-list', ['as' => 'get:admin:user_list', 'uses' => 'AdminController@getAdminUserList']);
                Route::get('/add-customer', ['as' => 'get:admin:add_user', 'uses' => 'AdminController@getAdminAddUser']);
                Route::get('/edit-customer/{slug}', ['as' => 'get:admin:edit_user', 'uses' => 'AdminController@getAdminEditUser']);
                Route::get('/delete-customer', ['as' => 'get:admin:delete_user', 'uses' => 'AdminController@getAdminDeleteUser']);
                Route::post('/update-customer', ['as' => 'post:admin:update_user', 'uses' => 'AdminController@postAdminUpdateUser']);
                Route::get('/customer-order-list/{id}', ['as' => 'post:admin:customer_order_list', 'uses' => 'AdminController@postAdminCustomerOrderList']);
                Route::get('/customer-wallet-transaction/{id}', ['as' => 'post:admin:customer_wallet_transaction', 'uses' => 'AdminController@postAdminCustomerWalletTransaction']);
                //ajax user status update
                Route::get('/update-user-status', ['as' => 'get:ajax:admin:update_user_status', 'uses' => 'AdminController@getAdminUpdateUserStatus']);
                Route::get('/user-list-new', ['as' => 'get:admin:user_list_new', 'uses' => 'AdminController@getAdminUserListNew']);

                //user review list
                Route::get('/customer-review-list/{user_id?}', ['as' => 'get:admin:user_review_list', 'uses' => 'AdminController@getAdminUserReviewList']);
                Route::get('/update-user-review-status', ['as' => 'get:ajax:admin:update_user_review_status', 'uses' => 'AdminController@getAdminUpdateUserReviewStatus']);
                Route::get('/delete-customer-review', ['as' => 'get:admin:delete_user_review', 'uses' => 'AdminController@getAdminDeleteUserReview']);

                //delivery preson
                Route::get('/delivery-person-list', ['as' => 'get:admin:delivery_person_list', 'uses' => 'AdminController@getAdminDeliveryPersonList']);
                Route::get('/add-delivery-person', ['as' => 'get:admin:add_delivery_person', 'uses' => 'AdminController@getAdminAddDeliveryPerson']);

                //ajax required- documents status update
                Route::get('/update-required-documents-status', ['as' => 'get:ajax:admin:update_required_document_status', 'uses' => 'AdminController@getAjaxUpdateAdminRequiredDocumentStatus']);
                //ajax provider approved reject document
                Route::get('/approved-reject-provider-document', ['as' => 'get:ajax:admin:update_approved_reject_provider_document', 'uses' => 'AdminController@getAjaxUpdateAdminApprovedRejectProviderDocument']);

                //get general settings
                Route::get('/site-setting', ['as' => 'get:admin:general_setting', 'uses' => 'AdminController@getAdminGeneralSetting']);
                Route::post('/site-setting', ['as' => 'post:admin:update_general_setting', 'uses' => 'AdminController@postAdminUpdateGeneralSetting']);


                //get general settings
                Route::get('/app-version-setting', ['as' => 'get:admin:app_version_setting', 'uses' => 'AdminController@getAdminAppVersionSetting']);
                Route::post('/app-version-setting', ['as' => 'post:admin:update_app_version_setting', 'uses' => 'AdminController@postAdminUpdateAppVersionSetting']);

                //get push notification
                Route::get('/push-notification', ['as' => 'get:admin:push_notification', 'uses' => 'AdminController@getAdminPushNotification']);
                Route::post('/push-notification', ['as' => 'post:admin:update_push_notification', 'uses' => 'AdminController@postAdminUpdatePushNotification']);



                //provider
                Route::get('/{slug}/provider-list', ['as' => 'get:admin:provider_list', 'uses' => 'AdminController@getAdminProviderList']);
                //Route::get('/{slug}/add-provider-services', ['as' => 'get:admin:add_provider_services', 'uses' => 'AdminController@getAdminAddProviderServices']);
                Route::get('/{slug}/add-provider-services/{provider_id}', ['as' => 'get:admin:add_provider_services', 'uses' => 'AdminController@getAdminAddProviderServices']);

                //un-approved (Pending) provider list
                Route::get('/pending-provider-list', ['as' => 'get:admin:pending_provider_list', 'uses' => 'AdminController@getAdminPendingProviderList']);
                Route::get('/pending-delivery-person-list', ['as' => 'get:admin:pending_delivery_person_list', 'uses' => 'AdminController@getAdminPendingDeliveryPersonList']);
                Route::get('/transport/pending-provider-list', ['as' => 'get:admin:pending_transport_provider_list', 'uses' => 'AdminController@getAdminPendingTransportProviderList']);
                Route::get('/store/pending-provider-list', ['as' => 'get:admin:pending_store_provider_list', 'uses' => 'AdminController@getAdminPendingStoreProviderList']);
                Route::get('/provider-services/pending-provider-list', ['as' => 'get:admin:pending_other_provider_list', 'uses' => 'AdminController@getAdminPendingProviderList']);

                //pages route
                Route::get('/support-page-list', ['as' => 'get:admin:support_pages', 'uses' => 'AdminController@getAdminSupportPages']);
                Route::get('/add-support-page', ['as' => 'get:admin:add_pages', 'uses' => 'AdminController@getAdminAddPages']);
                Route::get('/edit-support-page/{page_id}', ['as' => 'get:admin:edit_pages', 'uses' => 'AdminController@getAdminEditPages']);
                Route::post('/update-support-page', ['as' => 'post:admin:update_pages', 'uses' => 'AdminController@postAdminUpdateSupportPages']);
                Route::get('/delete-support-page', ['as' => 'get:admin:delete_support_page', 'uses' => 'AdminController@getAdminDeleteSupportPages']);

                Route::get('/about-us', ['as' => 'get:admin:about-us', 'uses' => 'AdminController@getAdminAboutPages']);
                Route::get('/contact-us', ['as' => 'get:admin:contact-us', 'uses' => 'AdminController@getAdminContactUsPages']);
                Route::get('/faq', ['as' => 'get:admin:faq', 'uses' => 'AdminController@getAdminFaqPages']);

                Route::post('/update', ['as' => 'post:admin:update_pages', 'uses' => 'AdminController@postAdminUpdateSupportPages']);

                Route::get('/un-register-provider-list', ['as' => 'get:admin:un_register_provider_list', 'uses' => 'AdminController@getAdminUnRegisterProviderList']);
                Route::get('/delete-un-register-provider', ['as' => 'get:admin:delete_un_register_provider', 'uses' => 'AdminController@getDeleteUnRegisterProvider']);
                //on-demand service list
                //Route::get('/on-demand/service-list', ['as' => 'get:admin:other_service_list', 'uses' => 'OtherServiceController@getOtherServiceList']);

                //coupon-deals
                Route::get('/{slug}/dashboard', ['as' => 'get:admin:coupon_deals_dashboard', 'uses' => 'CouponDealsController@getCouponDealsDashboard']);
                Route::get('coupon-deals/service-list', ['as' => 'get:admin:coupon_deals_service_list', 'uses' => 'CouponDealsController@getCouponDealsServiceList']);
                Route::get('/{slug}/category-list', ['as' => 'get:admin:coupon_deals_category_list', 'uses' => 'CouponDealsController@getCouponDealsCategoryList']);

                Route::get('/{slug}/add-category', ['as' => 'get:admin:coupon_deals_add_category', 'uses' => 'CouponDealsController@getCouponDealsAddCategory']);
                Route::get('/{slug}/edit-category/{id}', ['as' => 'get:admin:coupon_deals_edit_category', 'uses' => 'CouponDealsController@getCouponDealsEditCategory']);
                Route::post('/{slug}/update-category', ['as' => 'get:admin:coupon_deals_update_category', 'uses' => 'CouponDealsController@getCouponDealsUpdateCategory']);
                Route::get('coupon-deals/update-category-status', ['as' => 'get:admin:coupon_deals_category_change_status', 'uses' => 'CouponDealsController@getAdminUpdateCouponDealsCategoryStatus']);

                //coupon-deals-list
                Route::get('/{slug}/coupon-list', ['as' => 'get:admin:coupon_deals:coupon_list', 'uses' => 'CouponDealsController@getCouponList']);
                Route::get('/{slug}/add-coupon', ['as' => 'get:admin:coupon_deals:add_coupon', 'uses' => 'CouponDealsController@getCouponDealsAddCoupon']);
                Route::get('/{slug}/edit-coupon/{id}', ['as' => 'get:admin:coupon_deals:edit_coupon', 'uses' => 'CouponDealsController@getCouponDealsEditCoupon']);
                Route::post('/{slug}/update-coupon', ['as' => 'get:admin:coupon_deals:update_coupon', 'uses' => 'CouponDealsController@getCouponDealsUpdateCoupon']);
                Route::get('/update-coupon-status', ['as' => 'get:admin:coupon_deals:update_coupon_status', 'uses' => 'CouponDealsController@getCouponDealsUpdateCouponStatus']);

                Route::get('/{slug}/purchase-coupon-history', ['as' => 'get:admin:purchase_coupon_history', 'uses' => 'CouponDealsController@getCouponDealsPurchaseCouponHistory']);
                Route::get('/{slug}/{id}/purchase-coupon-history', ['as' => 'get:admin:user_purchase_coupon_history', 'uses' => 'CouponDealsController@getCouponDealsUserPurchaseCouponHistory']);

                //get promo code lists
                Route::get('/{slug}/{id}/coupon-code-list', ['as' => 'get:admin:coupon_code_list', 'uses' => 'CouponDealsController@getCouponDealsCouponCodeList']);
                Route::get('/{slug}/{id}/coupon-code-list-new', ['as' => 'get:admin:coupon_code_list_new', 'uses' => 'CouponDealsController@getCouponDealsCouponCodeListNew']);
                Route::get('/delete-coupon-code', ['as' => 'get:admin:delete_coupon_code', 'uses' => 'CouponDealsController@getCouponDealsDeleteCouponCode']);
                Route::get('/add-coupon-code', ['as' => 'get:admin:add_coupon_code', 'uses' => 'CouponDealsController@getCouponDealsAddCouponCode']);

                //sub admin route
                Route::get('/sub-admin-list', ['as' => 'get:admin:sub_admin_list', 'uses' => 'AdminController@getAdminSubAdminList']);
                Route::get('/add-sub-admin', ['as' => 'get:admin:add_sub_admin', 'uses' => 'AdminController@getAdminAddSubAdmin']);
                Route::get('/edit-sub-admin/{admin_id}', ['as' => 'get:admin:edit_sub_admin', 'uses' => 'AdminController@getAdminEditSubAdmin']);
                Route::post('/update-sub-admin', ['as' => 'post:admin:update_sub_admin', 'uses' => 'AdminController@postAdminUpdateSubAdmin']);
                Route::get('/delete-sub-admin', ['as' => 'get:admin:delete_sub_admin', 'uses' => 'AdminController@getAdminDeleteSubAdmin']);

                //Home page banner
                Route::get('/home-page-banner-list', ['as' => 'get:admin:home_page_banner_list', 'uses' => 'AdminController@getHomePageBannerList']);
                Route::get('/home-page-banner', ['as' => 'get:admin:home_page_banner', 'uses' => 'AdminController@getHomePageBanner']);
                Route::get('/edit-page-banner/{id}', ['as' => 'get:admin:edit_home_page_banner', 'uses' => 'AdminController@getHomePageBanner']);
                Route::post('/add-home-page-banner', ['as' => 'post:admin:add_home_page_banner', 'uses' => 'AdminController@AddEditHomePageBanner']);
                Route::get('/home-banner-change-status', ['as' => 'get:admin:change_home_banner_status', 'uses' => 'AdminController@getAdminHomeBannerChangeStatus']);
                Route::get('/delete-home-banner', ['as' => 'get:admin:delete_home_banner', 'uses' => 'AdminController@getAdminDeleteHomeBanner']);

                //Home page slider
                Route::get('/home-page-slider-list', ['as' => 'get:admin:home_page_slider_list', 'uses' => 'AdminController@getHomePageSliderList']);
                Route::get('/home-page-slider', ['as' => 'get:admin:home_page_slider', 'uses' => 'AdminController@getHomePageSlider']);
                Route::get('/edit-page-slider/{id}', ['as' => 'get:admin:edit_home_page_slider', 'uses' => 'AdminController@getHomePageSlider']);
                Route::post('/add-home-page-slider', ['as' => 'post:admin:add_home_page_slider', 'uses' => 'AdminController@AddEditHomePageSlider']);
                Route::get('/home-slider-change-status', ['as' => 'get:admin:change_home_slider_status', 'uses' => 'AdminController@getAdminHomeSliderChangeStatus']);
                Route::get('/delete-home-slider', ['as' => 'get:admin:delete_home_slider', 'uses' => 'AdminController@getAdminDeleteHomeSlider']);

                //Home page feature store
                Route::get('/home-page-feature-store-list', ['as' => 'get:admin:home_page_feature_store_list', 'uses' => 'AdminController@getHomePageFeatureStoreList']);
                Route::get('/home-page-feature-store', ['as' => 'get:admin:home_page_feature_store', 'uses' => 'AdminController@getHomePageFeatureStore']);
                Route::get('/edit-page-feature-store/{id}', ['as' => 'get:admin:edit_home_page_feature_store', 'uses' => 'AdminController@getHomePageFeatureStore']);
                Route::post('/add-home-page-feature-store', ['as' => 'post:admin:add_home_page_feature_store', 'uses' => 'AdminController@AddEditHomePageFeatureStore']);
                Route::get('/home-feature-store-change-status', ['as' => 'get:admin:change_home_feature_store_status', 'uses' => 'AdminController@getAdminHomeFeatureStoreChangeStatus']);
                Route::get('/delete-home-feature-store', ['as' => 'get:admin:delete_home_feature_store', 'uses' => 'AdminController@getAdminDeleteHomeFeatureStore']);
                Route::post('/ajax-load-store', ['as' => 'get:admin:ajax_load_store', 'uses' => 'AdminController@postAjaxLoadStore']);

                //set service category seq
                Route::get('/ordering-service-category-list', ['as' => 'get:admin:ordering_service_category_list', 'uses' => 'AdminController@getOrderServiceCategoryList']);
                Route::get('/ordering-service-category-status', ['as' => 'get:admin:ordering_service_category_list_status', 'uses' => 'AdminController@getStoreCategoryChangeStatus']);
                Route::post('/ordering-service-category-sorting', ['as' => 'post:admin:ordering_service_category_sorting', 'uses' => 'AdminController@postOrderingServiceCategorySorting']);


                //Home page Spot Light Section
                Route::get('/home-page-spot-light-list', ['as' => 'get:admin:home_page_spot_light_list', 'uses' => 'AdminController@getHomePageSpotLightList']);
                Route::get('/add-home-page-spot-light', ['as' => 'get:admin:add_home_page_spot_light', 'uses' => 'AdminController@getAddEditHomePageSpotLight']);
                Route::get('/edit-home-page-spot-light/{id}', ['as' => 'get:admin:edit_home_page_spot_light', 'uses' => 'AdminController@getAddEditHomePageSpotLight']);
                Route::post('/update-home-page-spot-light', ['as' => 'post:admin:update_home_page_spot_light', 'uses' => 'AdminController@postAdminUpdateHomePageSpotLight']);
                Route::get('/home-spot-light-change-status', ['as' => 'get:admin:change_home_spot_light_status', 'uses' => 'AdminController@getAdminHomeSpotLightChangeStatus']);
                Route::get('/delete-home-page-spot-light', ['as' => 'get:admin:delete_home_spot_light', 'uses' => 'AdminController@getAdminDeleteHomeSpotLight']);
                Route::post('/ajax-load-store-provider', ['as' => 'get:admin:ajax_load_store-provider', 'uses' => 'AdminController@postAjaxLoadStoreProvider']);


                //Geo Fencing Restricted areas
                Route::get('/restricted-area-list', ['as' => 'get:admin:restricted_area_list', 'uses' => 'AdminController@getAdminRestrictedAreaList']);
                Route::get('/add-restricted-area', ['as' => 'get:admin:add_restricted_area', 'uses' => 'AdminController@getAdminAddRestrictedArea']);
                Route::get('/edit-restricted-area/{id}', ['as' => 'get:admin:edit_restricted_area', 'uses' => 'AdminController@getAdminEditRestrictedArea']);
                Route::post('/update-restricted-area', ['as' => 'post:admin:update_restricted_area', 'uses' => 'AdminController@postAdminUpdateRestrictedArea']);
                Route::get('/update-restricted-area-status', ['as' => 'get:admin:update_restricted_area_status', 'uses' => 'AdminController@postAdminUpdateRestrictedAreaStatus']);
                Route::get('/delete-restricted-area', ['as' => 'get:admin:delete_restricted_area', 'uses' => 'AdminController@getAdminDeleteRestrictedArea']);

                //Email Templates
                Route::get('/email-templates', ['as' => 'get:admin:email_templates', 'uses' => 'AdminController@getEmailTemplatesList']);
                Route::get('/add-email-templates', ['as' => 'get:admin:add_email_templates', 'uses' => 'AdminController@getAdminAddEmailTemplates']);
                Route::get('/edit-email-templates/{id}', ['as' => 'get:admin:edit_email_templates', 'uses' => 'AdminController@getAdminEditEmailTemplates']);
                Route::post('/update-email-templates', ['as' => 'post:admin:update_email_templates', 'uses' => 'AdminController@postAdminUpdateEmailTemplates']);
                Route::get('/update-email-templates-status', ['as' => 'get:admin:update_email_templates_status', 'uses' => 'AdminController@postAdminUpdateEmailTemplatesStatus']);
                Route::get('/delete-email-templates', ['as' => 'get:admin:delete_email_templates', 'uses' => 'AdminController@getAdminDeleteEmailTemplates']);

                //get language lists
                Route::get('/language-lists', ['as' => 'get:admin:language_lists', 'uses' => 'AdminController@getAdminLanguageLists']);
                Route::post('/language-lists', ['as' => 'post:admin:update_language-lists', 'uses' => 'AdminController@postAdminUpdateLanguageLists']);
                Route::get('/language-lists-status', ['as' => 'get:ajax:admin:language_lists_status', 'uses' => 'AdminController@getAdminUpdateLanguageLists']);

                //get language constant
                Route::get('/language-constant', ['as' => 'get:admin:language_constant', 'uses' => 'AdminController@getAdminLanguageConstant']);
                Route::post('/language-constant', ['as' => 'post:admin:update_language_constant', 'uses' => 'AdminController@postAdminUpdateLanguageConstant']);
                Route::get('/edit-language-constant/{id}', ['as' => 'post:admin:edit_language_constant', 'uses' => 'AdminController@getAdminEditLanguageConstant']);

                Route::get('/user-change-password', ['as' => 'get:admin:user_change_password', 'uses' => 'AdminController@getUpdateUserChangePassword']);
                Route::get('/provider-change-password', ['as' => 'get:admin:provider_change_password', 'uses' => 'AdminController@getUpdateProviderChangePassword']);

                // user chat module
                Route::get('/admin-web-token', ['as' => 'get:admin:update_web_token', 'uses' => 'AdminController@postUpdateWebToken']);
                Route::get('/chat-{type_string}-list/{type}', ['as' => 'get:admin:chat_user_list', 'uses' => 'AdminController@getUserList']);
                Route::get('/chat/{string}/{type}/{id}', ['as' => 'get:admin:chat_user_message_list', 'uses' => 'AdminController@getUserChatMessageList']);
                Route::get('/chat/', ['as' => 'get:admin:chat_message', 'uses' => 'AdminController@getSendUserChatMessages']);

                //other services
                Route::prefix('provider-services')->group(function () {
                    Route::get('service-list', ['as' => 'get:admin:other_service_list', 'uses' => 'OtherServiceController@getOtherServiceList']);

                    //other-service dashboard
                    Route::get('/{slug}/dashboard', ['as' => 'get:admin:other_service_dashboard', 'uses' => 'OtherServiceController@getOtherServiceDashboard']);
                    //other services list
                    //Route::get('/other-service-list', ['as' => 'get:admin:other_service_list', 'uses' => 'OtherServiceController@getOtherServiceList']);

                    Route::get('/add-service-category', ['as' => 'get:admin:add_service_category', 'uses' => 'OtherServiceController@getAddServiceCategory']);
                    Route::get('/edit-service-category/{slug}', ['as' => 'get:admin:edit_service_category', 'uses' => 'OtherServiceController@getEditServiceCategory']);
                    //Route::get('/delete-service-category', ['as' => 'get:admin:delete_service_category', 'uses' => 'AdminController@getDeleteServiceCategory']);
                    Route::post('/update-service-category', ['as' => 'post:admin:update_service_category', 'uses' => 'OtherServiceController@postUpdateServiceCategory']);

                    Route::get('{slug}/provider-list/{status}', ['as' => 'get:admin:other_service_provider_list', 'uses' => 'OtherServiceController@getOtherServicesProviderList']);
                    Route::get('{slug}/add-provider', ['as' => 'get:admin:other_service_add_provider', 'uses' => 'OtherServiceController@getOtherServicesAddProvider']);
                    Route::get('{slug}/edit-provider/{id}', ['as' => 'get:admin:other_service_edit_provider', 'uses' => 'OtherServiceController@getOtherServicesEditProvider']);
                    Route::get('change-provider-status', ['as' => 'get:admin:other_service_change_provider_status', 'uses' => 'OtherServiceController@getOtherServicesChangeProviderStatus']);
                    Route::get('delete-provider-service', ['as' => 'get:admin:other_service_delete_provider', 'uses' => 'OtherServiceController@getOtherServicesChangeDeleteProvider']);
                    Route::post('/{slug}/update-provider', ['as' => 'post:admin:update_other_service_provider', 'uses' => 'OtherServiceController@postUpdateOtherServiceProvider']);
                    Route::get('/update-provider-status', ['as' => 'get:admin:update_other_service_provider_status', 'uses' => 'OtherServiceController@getUpdateOtherServiceProviderStatus']);
                    Route::get('change-provider-sponsor', ['as' => 'get:admin:other_service_change_provider_sponsor', 'uses' => 'OtherServiceController@getOtherServicesChangeProvidersponsor']);

                    Route::get('/{slug}/service-setting', ['as' => 'get:admin:other_service_setting', 'uses' => 'OtherServiceController@getOtherServiceSetting']);
                    Route::post('/update-service-setting', ['as' => 'post:admin:update_other_service_setting', 'uses' => 'OtherServiceController@postUpdateOtherServiceSetting']);

                    //other service sub category list
                    Route::get('/{slug}/sub-category-list', ['as' => 'get:admin:other_service_sub_category_list', 'uses' => 'OtherServiceController@getOtherServiceSubCategoryList']);
                    Route::get('/sub-category-change-status', ['as' => 'get:admin:other_service_sub_category_change_status', 'uses' => 'OtherServiceController@getOtherServiceSubCategoryChangeStatus']);
                    Route::post('/{slug}/update-category', ['as' => 'get:admin:update_other_service_sub_category', 'uses' => 'OtherServiceController@getUpdateOtherServiceSubCategory']);
                    Route::get('/{slug}/edit-sub-category/{id}', ['as' => 'get:admin:edit_other_service_sub_category', 'uses' => 'OtherServiceController@getEditOtherServiceSubCategory']);
                    Route::get('/{slug}/add-sub-category', ['as' => 'get:admin:add_other_service_sub_category', 'uses' => 'OtherServiceController@getAddOtherServiceSubCategory']);
                    Route::get('/delete-sub-category', ['as' => 'get:admin:delete_other_service_sub_category', 'uses' => 'OtherServiceController@getDeleteOtherServiceSubCategory']);

                    //other service order list
                    Route::get('/{slug}/order-list/{status}', ['as' => 'get:admin:other_service_order_list', 'uses' => 'OtherServiceController@getOtherServiceOrderList']);
//                        Route::get('/{slug}/order-details/', ['as' => 'get:admin:other_service_order_details', 'uses' => 'OtherServiceController@getOtherServiceOrderDetails']);
                    Route::get('/{slug}/order-details/{id}', ['as' => 'get:admin:other_service_order_details', 'uses' => 'OtherServiceController@getOtherServiceOrderDetails']);
                    Route::get('/{slug}/provider-order-list/{id}', ['as' => 'get:admin:other_service_provider_order_list', 'uses' => 'OtherServiceController@getOtherServiceProviderOrderList']);

                    Route::get('/other-service-user-refund-amount-settle', ['as' => 'get:admin:other_service_user_refund_amount_settle', 'uses' => 'OtherServiceController@getUserRefundAmountSettle']);

                    Route::get('/update-order-status', ['as' => 'get:admin:update_other_service_order_status', 'uses' => 'OtherServiceController@getUpdateOtherServiceOrderStatus']);

                    //store review list
                    Route::get('/review-list', ['as' => 'get:admin:review_list', 'uses' => 'OtherServiceController@getOtherServiceReviewList']);
                    Route::get('/{slug}/provider-review-list/{id}', ['as' => 'get:admin:provider_review_list', 'uses' => 'OtherServiceController@getOtherServiceProviderReviewList']);

                    Route::get('/provider-review-change-status', ['as' => 'get:admin:provider_review_change_status', 'uses' => 'OtherServiceController@getOtherServiceProviderReviewChangeStatus']);
                    Route::get('/delete-provider-review', ['as' => 'get:admin:delete_provider_review', 'uses' => 'OtherServiceController@getDeleteOtherServiceProviderReview']);

                    //other service product listb
                    Route::get('/{slug}/provider-package-list/{id}', ['as' => 'get:admin:provider_package_list', 'uses' => 'OtherServiceController@getOtherServiceProviderPackageList']);
                    Route::get('/{slug}/provider-add-package/{id}', ['as' => 'get:admin:provider_add_package', 'uses' => 'OtherServiceController@getAddOtherServiceProviderPackage']);
                    Route::post('/{slug}/provider-update-package', ['as' => 'post:admin:provider_update_package', 'uses' => 'OtherServiceController@postUpdateOtherServiceProviderPackage']);
                    Route::get('/{slug}/provider-edit-package/{id}', ['as' => 'get:admin:provider_edit_package', 'uses' => 'OtherServiceController@getEditOtherServiceProviderPackage']);
                    Route::get('/{slug}/provider-package/change-status', ['as' => 'get:admin:provider_package_change_status', 'uses' => 'OtherServiceController@getChangeStatusOtherServiceProviderPackage']);
                    Route::get('/provider-delete-package', ['as' => 'get:admin:provider_delete_package', 'uses' => 'OtherServiceController@getDeleteOtherServiceProviderPackage']);

                    Route::get('/{slug}/provider-document/{id}', ['as' => 'get:admin:provider_document', 'uses' => 'OtherServiceController@getOtherServiceProviderDocument']);

                    //other service required document list
                    Route::get('/{slug}/required-document-list/', ['as' => 'get:admin:other_service_required_document_list', 'uses' => 'AdminController@getRequiredDocumentList']);
                    Route::get('/{slug}/add-required-document/', ['as' => 'get:admin:other_service_add_required_document', 'uses' => 'AdminController@getAddRequiredDocument']);
                    Route::get('/{slug}/edit-required-document/{id}', ['as' => 'get:admin:other_service_edit_required_document', 'uses' => 'AdminController@getEditRequiredDocument']);
                    Route::post('/{slug}/update-required-document', ['as' => 'post:admin:other_service_update_required_document', 'uses' => 'AdminController@postUpdateRequiredDocument']);

                    Route::get('/{slug}/earning-report', ['as' => 'get:admin:other_service_earning_report', 'uses' => 'OtherServiceController@getOtherServiceEarningReport']);
                    Route::post('/{slug}/earning-report', ['as' => 'post:admin:other_service_earning_report', 'uses' => 'OtherServiceController@postOtherServiceEarningReport']);
                    Route::get('/{slug}/order-payment-settle', ['as' => 'post:admin:other_service_order_payment_settle', 'uses' => 'OtherServiceController@postOtherServiceOrderPaymentSettle']);

                    Route::get('/{slug}/promocode-list', ['as' => 'get:admin:other:promocode_list', 'uses' => 'AdminController@getAdminPromocodeList']);
                    Route::get('/{slug}/add-promocode', ['as' => 'get:admin:other:add_promocode', 'uses' => 'AdminController@getAdminAddPromocode']);
                    Route::get('/{slug}/edit-promocode/{id}', ['as' => 'get:admin:other:edit_promocode', 'uses' => 'AdminController@getAdminEditPromocode']);
                    Route::post('/{slug}/update-promocode', ['as' => 'post:admin:other:update_promocode', 'uses' => 'AdminController@postAdminUpdatePromocode']);
                    Route::get('/promocode-change-status', ['as' => 'get:admin:other:promocode_change_status', 'uses' => 'AdminController@getAdminPromocodeChangeStatus']);

                    //code for service slider for on demand service
                    Route::get('/{slug}/service-slider-list', ['as' => 'get:admin:on_demand_service_slider_list', 'uses' => 'OtherServiceController@getOnDemandServiceSliderList']);
                    Route::get('/{slug}/add-service-slider', ['as' => 'get:admin:add_on_demand_service_slider', 'uses' => 'OtherServiceController@getAddOnDemandServiceSlider']);
                    Route::get('/{slug}/edit-service-slider/{id}', ['as' => 'get:admin:edit_on_demand_service_slider', 'uses' => 'OtherServiceController@getEditOnDemandServiceSlider']);
                    Route::post('/{slug}/update-service-slider', ['as' => 'post:admin:update_on_demand_service_slider', 'uses' => 'OtherServiceController@postUpdateOnDemandServiceSlider']);
                    Route::get('/update-service-slider-status', ['as' => 'get:ajax:admin:update_on_demand_service_slider_status', 'uses' => 'OtherServiceController@postUpdateOnDemandServiceSliderStatus']);
                    Route::get('/delete-service-slider', ['as' => 'get:ajax:admin:delete_on_demand_service_slider', 'uses' => 'OtherServiceController@postDeleteOnDemandServiceSlider']);


                });

            });
        });
    });
});

//Route::prefix('dispatcher-admin')->group(function () {
//    Route::group(['middleware' => 'auth:admin'], function () {
//        Route::get('/logout/{admin}', 'Auth\LoginController@logout')->name('admin:logout');
//        Route::group(['middleware' => 'revalidate'], function () {
//            Route::group(['middleware' => 'adminrole'], function () {
//
//
//                Route::get('/change-password', ['as' => 'get:dispatcher:change_password', 'uses' => 'Auth\ResetPasswordController@getAdminChangePassword']);
//                Route::post('/change-password', ['as' => 'post:dispatcher:change_password', 'uses' => 'Auth\ResetPasswordController@postDispatcherAdminChangePassword']);
//                Route::get('/dashboard', ['as' => 'get:dispatcher:dashboard', 'uses' => 'DispatcherController@getAdminDashboard']);
//
//                Route::get('/profile', ['as' => 'get:dispatcher:profile', 'uses' => 'DispatcherController@getAdminProfile']);
//                Route::post('/update-profile', ['as' => 'post:dispatcher:profile', 'uses' => 'DispatcherController@postAdminProfile']);
//
//
//                //Dispatcher Admin
//                Route::get('/manual-booking', ['as' => 'get:dispatcher:manual_ride_booking', 'uses' => 'DispatcherController@getTransportManualRideBooking']);
//                Route::post('/update-manual-ride', ['as' => 'post:dispatcher:update_manual_ride', 'uses' => 'DispatcherController@postUpdateStoreManualRide']);
//                Route::get('/ride-list/scheduled', ['as' => 'get:dispatcher:ride_list', 'uses' => 'DispatcherController@getTransportServiceRideList']);
//                Route::get('/scheduled/ride-detail/{id}', ['as' => 'get:dispatcher:ride_details', 'uses' => 'DispatcherController@getTransportRideDetails']);
//                Route::get('/search-drivers-manual-ride-book', ['as' => 'get:ajax:dispatcher:transport_search_drivers_manual_book', 'uses' => 'DispatcherController@getAjaxTransportSearchDriversManualRideBook']);
//                Route::get('/get-drivers-ride-book', ['as' => 'get:ajax:dispatcher:drivers_manual_book_change_category', 'uses' => 'DispatcherController@getAjaxTransportDriversManualRideBookChangeCategory']);
//                Route::get('/get-service-setting', ['as' => 'get:ajax:dispatcher:service_setting', 'uses' => 'DispatcherController@getAjaxServiceSetting']);
//
//                Route::get('/update-ride-status', ['as' => 'get:dispatcher:transport_update_ride_status', 'uses' => 'DispatcherController@getTransportUpdateRideStatus']);
//
//
//                Route::get('/driver-on-map', ['as' => 'get:dispatcher:transport_provider_location', 'uses' => 'DispatcherController@getAdminTransportProviderLocation']);
//                Route::get('/all-map', ['as' => 'get:dispatcher:transport_all_provider_location', 'uses' => 'DispatcherController@getAdminTransportAllProviderLocation']);
//                Route::get('/available-on-map', ['as' => 'get:dispatcher:transport_available_provider_location', 'uses' => 'DispatcherController@getAdminTransportAvailableProviderLocation']);
//                Route::get('/ride-start-on-map', ['as' => 'get:dispatcher:transport_ride_start_provider_location', 'uses' => 'DispatcherController@getAdminTransportRideStartProviderLocation']);
//                Route::get('/ride-reached-on-map', ['as' => 'get:dispatcher:transport_ride_reached_provider_location', 'uses' => 'DispatcherController@getAdminTransportRideReachedProviderLocation']);
//                Route::get('/ride-enroute-on-map', ['as' => 'get:dispatcher:transport_ride_enroute_provider_location', 'uses' => 'DispatcherController@getAdminTransportRideEnrouteProviderLocation']);
//
//                Route::get('/location-on-map', ['as' => 'get:admin:transport_location_on_map', 'uses' => 'TransportController@getAdminTransportLocationOnMap']);
//
//                //store module
//                Route::get('/{slug}/dispatcher/{slug2}', ['as' => 'get:dispatcher:store_service_dispatcher', 'uses' => 'DispatcherController@getStoreServiceDispatcher']);
//                Route::get('/store-order-status-update', ['as' => 'get:dispatcher:store_order_status_update', 'uses' => 'DispatcherController@getStoreOrderStatusUpdate']);
//
//                Route::get('/{slug}/store-order-details/{id}', ['as' => 'get:dispatcher:store_order_details', 'uses' => 'DispatcherController@getStoreOrderDetails']);
//
//                //manual assign driver
//                Route::get('/{slug}/manual-assign-driver/{order_id}', ['as' => 'get:dispatcher:store_manual_assign_driver', 'uses' => 'DispatcherController@getStoreManualAssignDriver']);
//                Route::any('/{slug}/update-manual-assign-driver', ['as' => 'post:dispatcher:update_manual_assign_driver', 'uses' => 'DispatcherController@postUpdateStoreManualAssignDriver']);
//
//                Route::get('/{slug}/manual-assign-shopper/{order_id}', ['as' => 'get:dispatcher:store_manual_assign_shopper', 'uses' => 'DispatcherController@getStoreManualAssignShopper']);
//                Route::post('/{slug}/update-manual-assign-shopper', ['as' => 'post:dispatcher:update_manual_assign_shopper', 'uses' => 'DispatcherController@postUpdateStoreManualAssignShopper']);
//
//                // <-- Place new order routes -->*
//                Route::get('/{slug}/add-user/create-order', ['as' => 'get:dispatcher:add_users', 'uses' => 'DispatcherController@getAdminAddUser']);//add user form
//                Route::get('/search-customer-create-order', ['as' => 'get:dispatcher:store_delivery_search_customer_create_order', 'uses' => 'DispatcherController@getAdminStoreDeliverySearchCustomerCreateOrder']);//search user form
//                Route::post('/{slug}/store-user', ['as' => 'post:dispatcher:store_user', 'uses' => 'DispatcherController@postAdminStoreUser']);//add user store
//
//                Route::get('/{slug}/place-order/{user_id}/{address?}', ['as' => 'get:dispatcher:place_order', 'uses' => 'DispatcherController@getAdminPlaceOrder']);//create order form
//
//                Route::get('/edit-user-address', ['as' => 'get:dispatcher:edit_user_address', 'uses' => 'DispatcherController@getAdminEditUserAddress']); // get edit user address
//                Route::get('/delete-user-address', ['as' => 'get:dispatcher:delete_user_address', 'uses' => 'DispatcherController@getAdminDeleteUserAddress']); // get edit user address
//                Route::get('/add-user-address', ['as' => 'get:dispatcher:add_user_address', 'uses' => 'DispatcherController@getAdminAddUserAddress']);//store user address
//
//                Route::get('/get-store-list', ['as' => 'get:dispatcher:get_store_list', 'uses' => 'DispatcherController@getAdminCreateOrderStoreList']);//get order form store list
//                Route::get('/get-store-product-list', ['as' => 'get:dispatcher:get_store_product_list', 'uses' => 'DispatcherController@getAdminCreateOrderStoreProductList']);//get order form store product list
//                Route::get('/get-store-product-details', ['as' => 'get:dispatcher:get_store_product_details', 'uses' => 'DispatcherController@getAdminCreateOrderStoreProductDetails']);//get order form store product details
//                Route::get('/get-store-add-product-cart', ['as' => 'get:dispatcher:add_cart_store_product', 'uses' => 'DispatcherController@getAdminCreateOrderAddCartStoreProduct']);//post order form Add Cart Store Product
//                Route::get('/get-store-cart-details', ['as' => 'get:dispatcher:get_cart_details', 'uses' => 'DispatcherController@getAdminCreateOrderGetCartDetails']);//get order form get cart details
//                Route::get('/get-store-checkout-cart-details', ['as' => 'get:dispatcher:get_store_checkout_cart_details', 'uses' => 'DispatcherController@getAdminCreateOrderGetStoreCheckoutCartDetails']);//get order form get cart details
//
//                Route::any('/{slug}/checkout-order/{user_id}/{address?}', ['as' => 'get:dispatcher:checkout_order', 'uses' => 'DispatcherController@getAdminCheckoutOrder']);//create order checkout form
//                Route::any('/{slug}/create-place-order/', ['as' => 'any:dispatcher:create_place_order', 'uses' => 'DispatcherController@anyAdminCreatePlaceOrder']);//admin create place order
//                Route::get('/remove-cart-details/', ['as' => 'get:dispatcher:remove_cart_details', 'uses' => 'DispatcherController@getAdminRemoveCartDetails']);//admin create place order
//                // <-- End Place new order routes -->*
//            });
//        });
//    });
//});

Route::prefix('billing-admin')->group(function () {
    Route::group(['middleware' => 'auth:admin'], function () {
        Route::get('/logout/{admin}', 'Auth\LoginController@logout')->name('admin:logout');
        Route::group(['middleware' => 'revalidate'], function () {
            Route::group(['middleware' => 'adminrole'], function () {
                Route::get('/change-password', ['as' => 'get:account:change_password', 'uses' => 'Auth\ResetPasswordController@getAccountAdminChangePassword']);
                Route::post('/change-password', ['as' => 'post:account:change_password', 'uses' => 'Auth\ResetPasswordController@postAccountAdminChangePassword']);
                Route::get('/dashboard', ['as' => 'get:account:dashboard', 'uses' => 'AccountAdminController@getAdminDashboard']);

                Route::get('/profile', ['as' => 'get:account:profile', 'uses' => 'AccountAdminController@getAdminProfile']);
                Route::post('/update-profile', ['as' => 'post:account:profile', 'uses' => 'AccountAdminController@postAdminProfile']);

                //delivery list
                Route::get('/delivery-list', ['as' => 'get:account:delivery_list', 'uses' => 'AccountAdminController@getStoreOrderList']);
                Route::get('/order-details/{id}', ['as' => 'get:account:store_order_details', 'uses' => 'AccountAdminController@getStoreOrderDetails']);

                //ride list
                Route::get('/ride-list', ['as' => 'get:account:ride_list', 'uses' => 'AccountAdminController@getTransportServiceRideList']);
                Route::get('/ride-detail/{id}', ['as' => 'get:account:ride_details', 'uses' => 'AccountAdminController@getTransportRideDetails']);
                Route::get('/delivery-details/{id}', ['as' => 'get:account:delivery_details', 'uses' => 'AccountAdminController@getTransportStoreDeliveryDetails']);

                //order list
                Route::get('/order-list', ['as' => 'get:account:other_service_order_list', 'uses' => 'AccountAdminController@getOtherServiceOrderList']);
                Route::get('/service-order-details/{id}', ['as' => 'get:account:other_service_order_details', 'uses' => 'AccountAdminController@getOtherServiceOrderDetails']);

                //account biling admin  earings reports
                Route::get('/transport-earning-report', ['as' => 'get:account:transport_earning_report', 'uses' => 'AccountAdminController@getTransportEarningReport']);
                Route::post('/transport-earning-report', ['as' => 'post:account:transport_search_earing_report', 'uses' => 'AccountAdminController@PostTransportSearchEarningReport']);
                Route::post('/driver-payment-settled', ['as' => 'post:account:transport_driver_payment_settled', 'uses' => 'AccountAdminController@postTransportDriverPaymentSettled']);

                Route::get('/store-earning-report', ['as' => 'get:account:store_earning_report', 'uses' => 'AccountAdminController@getStoreEarningReport']);
                Route::post('/store-earning-report', ['as' => 'post:account:store_earning_report', 'uses' => 'AccountAdminController@postStoreEarningReport']);
                Route::post('/order-payment-settle', ['as' => 'post:account:store_order_payment_settle', 'uses' => 'AccountAdminController@postStoreOrderPaymentSettle']);

                Route::get('/services-earning-report', ['as' => 'get:account:other_service_earning_report', 'uses' => 'AccountAdminController@getOtherServiceEarningReport']);
                Route::post('/services-earning-report', ['as' => 'post:account:other_service_earning_report', 'uses' => 'AccountAdminController@postOtherServiceEarningReport']);
                Route::get('/services-order-payment-settle', ['as' => 'post:account:other_service_order_payment_settle', 'uses' => 'AccountAdminController@postOtherServiceOrderPaymentSettle']);
            });
        });
    });
});


//Route::group(['middleware' => 'revalidate'], function () {
//    Route::get('/store-admin/login', ['as' => 'get:store-admin:login', 'uses' => 'Auth\AuthPagesController@getStoreLogin']);
//    Route::get('/store-admin/register', ['as' => 'get:store-admin:register', 'uses' => 'Auth\AuthPagesController@getStoreRegister']);
//});
//Route::prefix('store-admin')->group(function () {
//    Route::post('/login', ['as' => 'post:store-admin:login', 'uses' => 'Auth\LoginController@postStoreAdminLogin']);
//    Route::post('/register', ['as' => 'post:store-admin:register', 'uses' => 'Auth\RegisterController@postStoreRegister']);
//
//    Route::get('/auth/{provider}', 'Auth\LoginController@redirectToProvider');
//    Route::get('/auth/{provider}/callback', 'Auth\LoginController@handleProviderCallback');
//
//    Route::group(['middleware' => 'auth:store'], function () {
//        Route::get('/logout/{store}', 'Auth\LoginController@logout')->name('store:logout');
//        Route::group(['middleware' => 'revalidate'], function () {
//            Route::get('/dashboard', ['as' => 'get:store-admin:dashboard', 'uses' => 'StoreAdminController@getDashboard']);
//            Route::get('/not-verified', ['as' => 'get:store-admin:not_verified', 'uses' => 'StoreAdminController@getStoreNotVerified']);
//
//            Route::get('/resend-verification-link', ['as' => 'get:store-admin:resend_verification_link', 'uses' => 'StoreAdminController@getStoreResendVerificationLink']);
//
//            Route::post('/verification-account', ['as' => 'post:store-admin:account_verification_approval', 'uses' => 'StoreAdminController@postStoreOtpVerification']);
//            Route::get('/account-approval-pending', ['as' => 'get:store-admin:account_approval_pending', 'uses' => 'StoreAdminController@getStoreAccountApprovalPending']);
//
//            Route::get('/account-rejected', ['as' => 'get:store-admin:account_rejected', 'uses' => 'StoreAdminController@getStoreAccountRejected']);
//            Route::get('/service-register', ['as' => 'get:store-admin:service-register', 'uses' => 'StoreAdminController@getStoreServiceRegister']);
//            Route::post('/service-register', ['as' => 'post:store-admin:service-register', 'uses' => 'StoreAdminController@postStoreServiceRegister']);
//            Route::get('/check-unique-email', ['as' => 'get:store-admin:check-unique-email', 'uses' => 'StoreAdminController@getStoreCheckUniqueEmail']);
//            Route::get('/check-unique-contact-number', ['as' => 'get:store-admin:check-unique-contact-number', 'uses' => 'StoreAdminController@getStoreCheckUniqueContactNumber']);
//
//            Route::get('/dispatcher/{slug}', ['as' => 'get:store-admin:store_service_dispatcher', 'uses' => 'StoreAdminController@getStoreServiceDispatcher']);
//            Route::get('/manual-driver-assign/{order_id}', ['as' => 'get:store-admin:store_manual_assign_driver', 'uses' => 'StoreAdminController@getStoreManualAssignDriver']);
//            Route::any('/update-manual-driver-assign', ['as' => 'post:store-admin:update_manual_assign_driver', 'uses' => 'StoreAdminController@postUpdateStoreManualAssignDriver']);
//
//            //store manual shopper assign
//            Route::get('/manual-shopper-assign/{order_id}', ['as' => 'get:store-admin:store_manual_assign_shopper', 'uses' => 'StoreAdminController@getStoreManualAssignShopper']);
//            Route::post('/update-manual-shopper-assign', ['as' => 'post:store-admin:update_manual_assign_shopper', 'uses' => 'StoreAdminController@postUpdateStoreManualAssignShopper']);
//
//            Route::get('/store-order-details/{id}', ['as' => 'get:store-admin:store_order_details', 'uses' => 'StoreAdminController@getStoreOrderDetails']);
//            Route::get('/edit-store-details', ['as' => 'get:store-admin:edit_store', 'uses' => 'StoreAdminController@getEditStore']);
//            Route::post('/edit-store-details', ['as' => 'get:store-admin:update_store', 'uses' => 'StoreAdminController@getUpdateStore']);
//            Route::get('/store-order-list/{status}', ['as' => 'get:store-admin:store_order_list', 'uses' => 'StoreAdminController@getStoreOrderList']);
//
//            Route::get('/change-password', ['as' => 'get:store-admin:change_password', 'uses' => 'Auth\ResetPasswordController@getStoreAdminChangePassword']);
//            Route::post('/change-password', ['as' => 'post:store-admin:change_password', 'uses' => 'Auth\ResetPasswordController@postStoreAdminChangePassword']);
//
//            Route::get('/store-order-status-update', ['as' => 'get:store-admin:store_order_status_update', 'uses' => 'StoreAdminController@getStoreOrderStatusUpdate']);
//
//            Route::get('/product-list', ['as' => 'get:store-admin:store_product_list', 'uses' => 'StoreAdminController@getStoreProductList']);
//            Route::get('/add-product', ['as' => 'get:store-admin:add_store_product', 'uses' => 'StoreAdminController@getAddStoreProduct']);
//            Route::get('/edit-product/{id}', ['as' => 'get:store-admin:edit_store_product', 'uses' => 'StoreAdminController@getEditStoreProduct']);
//            Route::post('/update-product', ['as' => 'get:store-admin:update_store_product', 'uses' => 'StoreAdminController@getUpdateStoreProduct']);
//            Route::get('/product-change-status', ['as' => 'get:store-admin:store_product_change_status', 'uses' => 'StoreAdminController@getStoreProductChangeStatus']);
//            Route::get('/product-change-recommended-status', ['as' => 'get:store-admin:store_product_change_recommended_status', 'uses' => 'StoreAdminController@getStoreProductChangeRecommendedStatus']);
//            Route::get('/delete-product', ['as' => 'get:store-admin:delete_store_product', 'uses' => 'StoreAdminController@getDeleteStoreProduct']);
//
//            Route::post('/ajax-load-product-subcategory', ['as' => 'get:store-admin:ajax:load_store_product_subcategory', 'uses' => 'StoreAdminController@getStoreProductSubCategoryList']);
//
//            Route::get('/store-provider-document', ['as' => 'get:store-admin:store_provider_document', 'uses' => 'StoreAdminController@getStoreProviderDocument']);
//            Route::post('/store-provider-document', ['as' => 'post:store-admin:store_provider_document', 'uses' => 'StoreAdminController@postStoreProviderDocument']);
//
//            Route::get('/earning-report', ['as' => 'get:store-admin:store_earning_report', 'uses' => 'StoreAdminController@getStoreEarningReport']);
//            Route::post('/earning-report', ['as' => 'post:store-admin:store_earning_report', 'uses' => 'StoreAdminController@postStoreEarningReport']);
//
//            Route::get('/add-product-excel/{id}', ['as' => 'get:store-admin:add_store_product_excel', 'uses' => 'StoreAdminController@getAddStoreProductExcel']);
//            Route::post('/update-product-excel', ['as' => 'post:store-admin:update_store_product_excel', 'uses' => 'StoreAdminController@getUpdateStoreProductExcel']);
//
//            //toppings data
//            Route::get('/remove-product-customize-category', ['as' => 'get:store-admin:remove_product_customize_category', 'uses' => 'StoreAdminController@postRemoveStoreProductCustomizeCategory']);
//            Route::get('/remove-product-customize-size', ['as' => 'get:store-admin:remove_product_customize_size', 'uses' => 'StoreAdminController@postRemoveStoreProductCustomizeSize']);
//            Route::get('/remove-product-customize-options', ['as' => 'get:store-admin:remove_product_customize_options', 'uses' => 'StoreAdminController@postRemoveStoreProductCustomizeOptions']);
//            Route::get('/remove-product-customize-toppings', ['as' => 'get:store-admin:remove_product_customize_toppings', 'uses' => 'StoreAdminController@postRemoveStoreProductCustomizeToppings']);
//
//        });
//    });
//});


Route::group(['middleware' => 'revalidate'], function () {
    Route::get('/provider-admin/login', ['as' => 'get:provider-admin:login', 'uses' => 'Auth\AuthPagesController@getProvideLogin']);
    Route::get('/provider-admin/register', ['as' => 'get:provider-admin:register', 'uses' => 'Auth\AuthPagesController@getProvideRegister']);
});

Route::prefix('provider-admin')->group(function () {
    Route::post('/login', ['as' => 'post:provider-admin:login', 'uses' => 'Auth\LoginController@postProviderAdminLogin']);
    Route::post('/register', ['as' => 'post:provider-admin:register', 'uses' => 'Auth\RegisterController@postProviderRegister']);

    // Social Login
    Route::get('/auth/{provider}', [\App\Http\Controllers\Auth\LoginController::class, 'redirectToProvider']);
    Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Auth\LoginController::class, 'handleProviderCallback']);

    Route::group(['middleware' => 'auth:on_demand'], function () {
        Route::get('/logout/{provider}', 'Auth\LoginController@logout')->name('provider:logout');
        Route::group(['middleware' => 'revalidate'], function () {
            Route::get('/dashboard', ['as' => 'get:provider-admin:dashboard', 'uses' => 'OtherServiceAdminController@getDashboard']);
            Route::get('/service-register', ['as' => 'get:provider-admin:service-register', 'uses' => 'OtherServiceAdminController@getProviderServiceRegister']);
            Route::post('/service-register', ['as' => 'post:provider-admin:service-register', 'uses' => 'OtherServiceAdminController@postProviderServiceRegister']);

            Route::get('/edit-profile', ['as' => 'get:provider-admin:edit-profile', 'uses' => 'OtherServiceAdminController@getProviderServiceEditProfile']);
            Route::post('/edit-profile', ['as' => 'post:provider-admin:edit-profile', 'uses' => 'OtherServiceAdminController@postProviderServiceEditProfile']);

            Route::get('/change-password', ['as' => 'get:provider-admin:change_password', 'uses' => 'Auth\ResetPasswordController@getProviderAdminChangePassword']);
            Route::post('/change-password', ['as' => 'post:provider-admin:change_password', 'uses' => 'Auth\ResetPasswordController@postProviderAdminChangePassword']);

            Route::get('/services', ['as' => 'get:provider-admin:services', 'uses' => 'OtherServiceAdminController@getProviderServices']);
            Route::get('/add-service', ['as' => 'get:provider-admin:add_services', 'uses' => 'OtherServiceAdminController@getProviderAddServices']);
            Route::post('/add-service', ['as' => 'post:provider-admin:add_services', 'uses' => 'OtherServiceAdminController@postProviderAddServices']);
            Route::get('/delete-service', ['as' => 'post:provider-admin:delete_service', 'uses' => 'OtherServiceAdminController@postProviderDeleteService']);
            Route::get('/update-service-current-status', ['as' => 'get:provider-admin:update_service_current_status', 'uses' => 'OtherServiceAdminController@getProviderUpdateServiceCurrentStatus']);

            Route::get('{slug}/dashboard', ['as' => 'get:provider-admin:service-dashboard', 'uses' => 'OtherServiceAdminController@getProviderServiceDashboard']);
            Route::get('{slug}/package-list', ['as' => 'get:provider-admin:service-package-list', 'uses' => 'OtherServiceAdminController@getProviderServicePackageList']);
            Route::get('{slug}/add-package', ['as' => 'get:provider-admin:add-package', 'uses' => 'OtherServiceAdminController@getProviderServiceAddPackage']);
            Route::get('{slug}/edit-package/{id}', ['as' => 'get:provider-admin:edit-package', 'uses' => 'OtherServiceAdminController@getProviderServiceEditPackage']);
            Route::post('{slug}/update-package', ['as' => 'get:provider-admin:update-package', 'uses' => 'OtherServiceAdminController@getProviderServiceUpdatePackage']);
            Route::get('/update-package-status', ['as' => 'get:provider-admin:update-package-status', 'uses' => 'OtherServiceAdminController@getProviderServiceUpdatePackageStatus']);
            Route::get('/delete-package', ['as' => 'get:provider-admin:delete-package', 'uses' => 'OtherServiceAdminController@getProviderServiceDeletePackage']);

            Route::get('/{slug}/order-list/{status}', ['as' => 'get:provider-admin:other_service_order_list', 'uses' => 'OtherServiceAdminController@getProviderOtherServiceOrderList']);
            Route::get('/{slug}/order-details/{id}', ['as' => 'get:provider-admin:other_service_order_details', 'uses' => 'OtherServiceAdminController@getProviderOtherServiceOrderDetails']);

            Route::get('/provider-document/{slug}', ['as' => 'get:provider-admin:other_service_provider_document', 'uses' => 'OtherServiceAdminController@getProviderOtherServiceDocument']);
            Route::post('/provider-document/{slug}', ['as' => 'post:provider-admin:other_service_provider_document', 'uses' => 'OtherServiceAdminController@postProviderOtherServiceDocument']);

            Route::get('/order-list/{status}', ['as' => 'get:provider-admin:other_service_all_order_list', 'uses' => 'OtherServiceAdminController@getProviderOtherServiceAllOrderList']);
            Route::get('/order-details/{id}', ['as' => 'get:provider-admin:other_service_order_details', 'uses' => 'OtherServiceAdminController@getProviderOtherServiceAllOrderDetails']);

            Route::get('/{slug}/earning-report', ['as' => 'get:provider-admin:other_service_earning_report', 'uses' => 'OtherServiceAdminController@getProviderOtherServiceEarningReport']);
            Route::post('/{slug}/earning-report', ['as' => 'post:provider-admin:other_service_earning_report', 'uses' => 'OtherServiceAdminController@postProviderOtherServiceEarningReport']);

            Route::get('/update-order-status', ['as' => 'get:provider:update_other_service_order_status', 'uses' => 'OtherServiceAdminController@getUpdateOtherServiceOrderStatus']);

        });
    });
});


