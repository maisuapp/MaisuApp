<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserStoreProductBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_store_product_booking', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('store_id');
            $table->foreign('store_id')
                ->references('id')->on('store_details')
                ->onUpdate('cascade');
//                ->onDelete('cascade');
            $table->unsignedInteger('service_cat_id');
            $table->foreign('service_cat_id')
                ->references('id')->on('service_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');
//            $table->unsignedInteger('address_id');
//            $table->foreign('address_id')
//                ->references('id')->on('user_address')
//                ->onUpdate('cascade');
//                ->onDelete('cascade');
            $table->unsignedInteger('delivery_people_id')->nullable();
            $table->foreign('delivery_people_id')
                ->references('id')->on('providers')
                ->onUpdate('cascade');
//                ->onDelete('cascade');
            $table->bigInteger('order_no')->nullable();
            $table->tinyInteger('order_type')->comment('0=book-now,1=schedule')->default(0);
            $table->dateTime('order_date_time')->nullable();
            $table->float('total_item_cost')->default(0);
            $table->float('discount_cost')->default(0);
            $table->float('tax_cost')->default(0);
            $table->float('delivery_cost')->default(0);
            $table->float('packaging_cost')->default(0);
            $table->float('total_pay')->default(0);
            $table->float('store_amount')->default(0);
            $table->float('admin_commission')->default(0);
            $table->tinyInteger('status')->comment('1=pending, 2=approved-by-store, 3=rejected, 4=cancelled, 5=processing, 6=approved-by-driver, 7=arrived-driver, 8=ongoing, 9=completed, 10=failed')->default(1);
            $table->tinyInteger('order_delivery_status')->comment('0=pending, 1=accepted, 2=schedule-accepted, 3=arrived, 4=cancelled, 5=running, 6=drop, 7=payment, 8=rating, 9=completed, 10=failed')->default(0);
            $table->string('store_name',100)->nullable();
            $table->string('user_name',100)->nullable();
            $table->text('delivery_address');
            $table->string('lat_long');
            $table->string('flat_no');
            $table->string('landmark');
            $table->string('delivery_people_name',60);
            $table->tinyInteger('payment_type')->comment('1=cash, 2=card, 3=wallet');
            $table->tinyInteger('payment_status')->comment('0=pending, 1=completed');
            $table->tinyInteger('promo_code')->comment('0= notused, 1=used')->default(0);
            $table->string('cancel_by',10)->nullable();
            $table->string('cancel_reason',50)->nullable();
            $table->text('order_product_list')->nullable();
            $table->tinyInteger('user_rating_status')->default(0);
            $table->tinyInteger('driver_pay_settle_status')->comment('0=pending, 1=done')->default(0);
            $table->tinyInteger('store_pay_settle_status')->comment('0=pending, 1=done')->default(0);
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
        Schema::dropIfExists('user_store_product_booking');
    }
}
