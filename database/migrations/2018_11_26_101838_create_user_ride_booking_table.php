<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRideBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_ride_booking', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('service_cat_id');
            $table->foreign('service_cat_id')
                ->references('id')->on('service_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('driver_id')->nullable();
            $table->foreign('driver_id')
                ->references('id')->on('transport_driver_details')
                ->onUpdate('cascade');
//                ->onDelete('cascade');
            $table->unsignedInteger('vehicle_type_id')->nullable();
            $table->foreign('vehicle_type_id')
                ->references('id')->on('transport_vehicle_type')
                ->onUpdate('cascade');
//                ->onDelete('cascade');
            $table->bigInteger('ride_no')->nullable();
            $table->dateTime('pickup_datetime');
            $table->dateTime('destination_datetime')->nullable();
            $table->tinyInteger('hail_ride')->comment('1=hail , 0=ride')->default(0);
            $table->tinyInteger('ride_type')->comment('0=ride_now, 1=schedule_ride')->default(0);
            $table->text('pickup_address');
            $table->string('pickup_latlong');
            $table->text('destination_address');
            $table->string('destination_latlong');
            $table->string('driver_name', 100)->nullable();
            $table->string('user_name', 100)->nullable();
            $table->string('vehicle_type_name', 30)->nullable();
            $table->float('vehicle_cost_for_km')->default(0);
            $table->string('total_distance', 10);
            $table->string('eta', 20);
            $table->float('base_fare')->default(0);
            $table->float('discount_amount')->default(0);
            $table->float('tax')->default(0);
            $table->float('tip')->default(0);
            $table->float('total_pay')->default(0);
            $table->float('driver_amount')->default(0);
            $table->string('payment_type', 10);
            $table->tinyInteger('payment_status')->comment('0=pending, 1=completed');
            $table->tinyInteger('driver_pay_settle_status')->comment('0=un-settled, 1=settled');
            $table->tinyInteger('promo_code')->comment('0=notused, 1=used')->default(0);
            $table->tinyInteger('handicap')->comment('1=handicap user')->default(0);
            $table->tinyInteger('child_seat')->comment('1=child_seat')->default(0);
            $table->string('cancel_by', 10)->nullable();
            $table->string('cancel_reason', 50)->nullable();
            $table->boolean('is_way_point')->default(0)->comment('1= multi stop');
            $table->tinyInteger('way_point_status')->default(0)->comment('1=first waypoint complete,2=second,3=third');
            $table->tinyInteger('status')->comment('0=pending, 1=approved, 2=running, 3=completed, 4=cancelled')->default(0);
//            $table->string('status', 15);
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
        Schema::dropIfExists('user_ride_booking');
    }
}
