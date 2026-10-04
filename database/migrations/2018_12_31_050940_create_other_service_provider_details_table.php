<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtherServiceProviderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_service_provider_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_id');
            $table->foreign('provider_id')
                ->references('id')->on('providers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->float('rating')->default(0);
            $table->integer('total_completed_order')->default(0);
            $table->string('address');
            $table->string('flat_no',100);
            $table->string('landmark',100);
            $table->string('lat');
            $table->string('long');
            $table->integer('mail_status')->default(0);
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
        Schema::dropIfExists('other_service_provider_details');
    }
}
