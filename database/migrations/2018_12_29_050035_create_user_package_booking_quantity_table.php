<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPackageBookingQuantityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_package_booking_quantity', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->foreign('order_id')
                ->references('id')->on('user_service_package_booking')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('package_id');
            $table->foreign('package_id')
                ->references('id')->on('other_service_provider_packages')
                ->onUpdate('cascade');
//                ->onDelete('cascade');
            $table->string('package_name');
            $table->string('sub_category_name');
            $table->integer('num_of_items');
            $table->float('price_for_one');
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
        Schema::dropIfExists('user_package_booking_quantity');
    }
}
