<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCourierServiceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_courier_service_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ride_id');
            $table->foreign('ride_id')
                ->references('id')->on('user_ride_booking')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('recipient_name', 60);
            $table->string('house_name');
            $table->string('recipient_contact_number', 20);
            $table->integer('goods_height');
            $table->integer('goods_width');
            $table->string('goods_weight');
            $table->Text('description');
            $table->string('delivery_instruction')->nullable();
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
        Schema::dropIfExists('user_courier_service_details');
    }
}
