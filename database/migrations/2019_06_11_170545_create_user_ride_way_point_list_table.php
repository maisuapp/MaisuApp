<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRideWayPointListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_ride_way_point_list', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ride_id')->nullable();
            $table->foreign('ride_id')
                ->references('id')->on('user_ride_booking')
                ->onUpdate('cascade');
            $table->string('way_point_1');
            $table->string('lat_long_1');
            $table->string('way_point_2')->nullable();
            $table->string('lat_long_2')->nullable();
            $table->string('way_point_3')->nullable();
            $table->string('lat_long_3')->nullable();
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
        Schema::dropIfExists('user_ride_way_point_list');
    }
}
