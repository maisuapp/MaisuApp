<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserProductBookingStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_product_booking_status', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->foreign('order_id')
                ->references('id')->on('user_store_product_booking')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->tinyInteger('status')->comment('1=pending, 2=approved-by-store, 3=rejected, 4=cancelled, 5=processing, 6=approved-by-driver, 7=arrived-driver, 8=ongoing, 9=completed, 10=failed');
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
        Schema::dropIfExists('user_product_booking_status');
    }
}
