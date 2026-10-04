<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportRideRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_ride_route', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ride_id');
            $table->foreign('ride_id')
                ->references('id')->on('user_ride_booking')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->longText('ride_route');
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
        Schema::dropIfExists('transport_ride_route');
    }
}
