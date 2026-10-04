<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportDriverRatingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_driver_rating', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->foreign('driver_id')
                ->references('id')->on('transport_driver_details')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('ride_id')->nullable();
            $table->foreign('ride_id')
                ->references('id')->on('user_ride_booking')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('delivery_id')->nullable();
            $table->foreign('delivery_id')
                ->references('id')->on('user_store_product_booking')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('rental_id')->nullable();
            $table->foreign('rental_id')
                ->references('id')->on('user_rental_ride_booking')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->float('rating');
            $table->string('comment')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('driver_rating');
    }
}
