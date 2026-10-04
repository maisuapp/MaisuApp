<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->foreign('category_id')
                ->references('id')->on('coupon_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('coupon_name');
            $table->string('coupon_image');
            $table->string('coupon_brand_name');
            $table->float('coupon_amount');
            $table->string('coupon_code');
            $table->string('link');
            $table->longText('terms_condition');
            $table->longText('how_to_redeem');
            $table->dateTime('active_date');
            $table->dateTime('expiry_date');
            $table->integer('coupon_limit');
            $table->integer('coupon_usage');
            $table->tinyInteger('status')->default(1)->comment('0=off, 1=on');
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
        Schema::dropIfExists('coupon_details');
    }
}
