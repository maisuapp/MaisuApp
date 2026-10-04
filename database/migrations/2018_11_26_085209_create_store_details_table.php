<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_service_id');
            $table->foreign('provider_service_id')
                ->references('id')->on('provider_services')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('name',100);
            $table->string('image',15)->nullable();
            $table->float('order_min_amount')->default(0);
            $table->float('offer_min_amount')->default(0);
            $table->float('offer_amount')->default(0);
            $table->tinyInteger('offer_type')->comment('1=amount 2=percentage')->default(0);
            $table->text('address');
            $table->float('address_lat');
            $table->float('address_long');
            $table->integer('eta_delivery_time')->nullable();
            $table->float('packaging_charges')->default(0);
            $table->float('rating')->default(0);
            $table->string('slug',100);
            $table->text('description')->nullable();
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
        Schema::dropIfExists('store_details');
    }
}
