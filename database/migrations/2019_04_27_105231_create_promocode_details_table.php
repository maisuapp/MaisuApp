<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromocodeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promocode_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_cat_id');
            $table->foreign('service_cat_id')
                ->references('id')->on('service_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('promo_code');
            $table->float('discount_amount');
            $table->tinyInteger('discount_type')->comment('1=amount 2=percentage');
            $table->float('min_order_amount')->nullable();
            $table->float('max_discount_amount')->nullable();
            $table->integer('coupon_limit')->default('0')->comment('0 = no any limit');
            $table->integer('usage_limit')->default('1')->comment('per user use limit');
            $table->integer('total_usage')->default('0')->comment('no of user used code');
            $table->dateTime('expiry_date_time');
            $table->text('description')->nullable();
            $table->boolean('status')->comment('1=on, 0=off');
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
        Schema::dropIfExists('promocode_details');
    }
}
