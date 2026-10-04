<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('website_name',50)->nullable();
            $table->string('website_logo',50)->nullable();
            $table->string('website_favicon',50)->nullable();

            $table->text('address')->nullable();
            $table->string('contact_no',15)->nullable();
            $table->string('email',40)->nullable();
            $table->string('copy_right')->nullable();

            $table->string('facebook_link')->nullable();
            $table->string('google_link')->nullable();
            $table->string('twitter_link')->nullable();

            $table->string('map_key')->nullable();
            $table->string('fcm_server_key',200)->nullable();
            $table->string('fcm_user_topic_name')->nullable();
            $table->string('fcm_driver_topic_name')->nullable();
            $table->string('stripe_key')->nullable();

            $table->string('user_playstore_link')->nullable();
            $table->string('user_appstore_link')->nullable();
            $table->string('driver_delivery_playstore_link')->nullable();
            $table->string('driver_delivery_appstore_link')->nullable();
            $table->string('provider_playstore_link')->nullable();
            $table->string('provider_appstore_link')->nullable();
            $table->string('store_playstore_link')->nullable();
            $table->string('store_appstore_link')->nullable();


//            $table->float('tax');
//            $table->integer('provider_search_radius');
//            $table->string('fcm_server_key',200);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_settings');
    }
}
