<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserServicePackageBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_service_package_booking', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('provider_id');
            $table->foreign('provider_id')
                ->references('id')->on('providers')
                ->onUpdate('cascade');
//                ->onDelete('cascade');
//            $table->unsignedInteger('address_id');
//            $table->foreign('address_id')
//                ->references('id')->on('user_address')
//                ->onUpdate('cascade');
//                ->onDelete('cascade');
            $table->unsignedInteger('service_cat_id');
            $table->foreign('service_cat_id')
                ->references('id')->on('service_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->bigInteger('order_no')->nullable();
            $table->boolean('order_type')->default(0)->comment('0=book-now,1=schedule');
            $table->dateTime('service_date_time');
            $table->float('total_item_cost')->default(0);
            $table->float('tax')->default(0);
            $table->float('total_pay')->default(0);
            $table->float('provider_amount')->default(0);
            $table->tinyInteger('status')->comment('1=pending, 2=confirmed, 3=scheduled-confirm, 4=rejected, 5=cancelled, 6=ongoing, 7=arrived, 8=processing, 9=completed, 10=failed')->default(1);
            $table->string('provider_name', 100)->nullable();
            $table->string('user_name',100)->nullable();
            $table->text('delivery_address');
            $table->string('lat_long');
            $table->string('flat_no');
            $table->string('landmark');
            $table->string('payment_type', 10);
            $table->boolean('payment_status')->comment('0=pending, 1=completed')->default(0);
            $table->boolean('promo_code')->comment('0=notused, 1=used')->default(0);
            $table->text('remark')->nullable();
            $table->string('cancel_by', 10)->nullable();
            $table->string('cancel_reason', 50)->nullable();
            $table->text('order_package_list')->nullable();
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
        Schema::dropIfExists('user_service_package_booking');
    }
}
