<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPurchaseCouponDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_purchase_coupon_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('coupon_id');
            $table->foreign('coupon_id')
                ->references('id')->on('coupon_details')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('coupon_name');
            $table->string('coupon_brand_name');
            $table->float('coupon_amount');
            $table->string('coupon_code');
            $table->dateTime('purchase_date');
            $table->dateTime('expiry_date');
            $table->tinyInteger('status')->default(1)->comment('1=on, 0=off');
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
        Schema::dropIfExists('user_purchase_coupon_details');
    }
}
