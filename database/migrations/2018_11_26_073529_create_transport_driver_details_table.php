<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportDriverDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_driver_details', function (Blueprint $table) {
            $table->increments('id');
//            $table->unsignedInteger('provider_id');
//            $table->foreign('provider_id')
//                ->references('id')->on('providers')
//                ->onUpdate('cascade')
//                ->onDelete('cascade');
            $table->unsignedInteger('provider_service_id');
            $table->foreign('provider_service_id')
                ->references('id')->on('provider_services')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('vehicle_type_id');
            $table->foreign('vehicle_type_id')
                ->references('id')->on('transport_vehicle_type')
                ->onUpdate('cascade');
//                ->onDelete('cascade');
            $table->integer('total_request')->default(0);
            $table->integer('total_completed')->default(0);
            $table->integer('total_cancelled')->default(0);
            $table->float('rating')->default(0);
            $table->string('current_lat',30)->nullable();
            $table->string('current_long',30)->nullable();
            $table->dateTime('last_online_date_time')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('vehicle_company',20);
            $table->string('plat_no',20);
            $table->mediumInteger('model_year');
            $table->string('model_name',20);
//            $table->tinyInteger('availability_ride_status')->default(1)->comment('1 = available , 2 = accept, 3 enroute, 4= arrived(pickup) , 5 =start');
            $table->tinyInteger('driver_service')->default(1)->comment('1= ride driver  , 0= not ride driver (only delivery or courier for bike ride service)');
            $table->tinyInteger('delivery_boy')->default(0)->comment('1= as delivery boy , 0= not as delivery boy');
            $table->tinyInteger('courier_boy')->default(0)->comment('1= as courier boy , 0= not as courier boy');
//            $table->tinyInteger('taxi_driver')->comment('1= as taxi driver, 0= not as taxi driver');
            $table->string('vehicle_color',20)->nullable();
            $table->integer('no_of_seat')->default(0)->comment('0 = not required');
            $table->boolean('handicap')->default(0)->comment('0 = no , 1 = handicap accessibility');
            $table->boolean('child_seat')->default(0)->comment('0 = no , 1 = child seat accessibility');
            $table->integer('support_weight')->default(0)->comment('0 = not required');
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
        Schema::dropIfExists('driver_details');
    }
}
