<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_cat_id');
            $table->foreign('service_cat_id')
                ->references('id')->on('service_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->tinyInteger('driver_accept_timeout')->nullable();
            $table->tinyInteger('driver_search_radius')->nullable();
            $table->float('tax')->nullable();
            $table->float('admin_commission')->nullable();
            $table->float('cancel_charge')->nullable();
            $table->float('delivery_charge')->nullable();
            $table->tinyInteger('status')->nullable();
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
        Schema::dropIfExists('service_settings');
    }
}
