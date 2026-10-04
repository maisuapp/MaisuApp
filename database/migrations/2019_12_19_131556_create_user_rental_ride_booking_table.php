<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRentalRideBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_rental_ride_booking', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('driver_id')->nullable();
            $table->foreign('driver_id')
                ->references('id')->on('transport_driver_details')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('service_cat_id');
            $table->foreign('service_cat_id')
                ->references('id')->on('service_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('vehicle_type_id');
            $table->string('pickup_address');
            $table->string('pickup_lat_long');
            $table->string('drop_address');
            $table->string('drop_lat_long');
            $table->bigInteger('ride_no');
            $table->dateTime('pickup_date_time');
            $table->dateTime('drop_date_time');
            $table->float('total_km');
            $table->float('ride_fare');
            $table->float('additional_fare');
            $table->float('additional_km');
            $table->float('discount');
            $table->tinyInteger('payment_type')->comment('1=cash , 2=card, 3=wallet');
            $table->tinyInteger('payment_status')->comment("0=pending, 1=completed");
            $table->integer('promocode')->default(0);
            $table->float('tax');
            $table->float('total_pay');
            $table->float('driver_amount');
            $table->float('admin_commission');
            $table->string('user_name');
            $table->string('driver_name')->nullable();
            $table->string('vehicle_type_name');
            $table->float('km_limit_per_hour');
            $table->float('rental_amount_per_hour');
            $table->float('extra_km_amount_per_hour');
            $table->string('cancel_by');
            $table->string('cancel_reason');
            $table->tinyInteger('status');
            $table->tinyInteger('driver_pay_settle_status');
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
        Schema::dropIfExists('user_rental_ride_booking');
    }
}
