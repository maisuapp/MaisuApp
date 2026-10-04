<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportVehicleTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_vehicle_type', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_cat_id');
            $table->foreign('service_cat_id')
                ->references('id')->on('service_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('name',20)->unique();
            $table->string('icon_name',25)->unique();
            $table->float('cost_for_km');
            $table->string('weight_limit',20)->nullable();
            $table->string('dimension_limit',20)->nullable();
            $table->float('base_fare',20)->default(0.00);
            $table->float('time_fare',20)->default(0.00);
            $table->float('min_fare_amount',20)->default(0.00);
            $table->tinyInteger('status')->comment('1=activate, 0=deactivate')->default(1);
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
        Schema::dropIfExists('driver_vehicle_type');
    }
}
