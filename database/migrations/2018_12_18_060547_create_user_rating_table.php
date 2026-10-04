<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRatingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_rating', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('provider_id')->nullable();
            $table->foreign('provider_id')
                ->references('id')->on('providers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('ride_book_id')->nullable();
            $table->foreign('ride_book_id')
                ->references('id')->on('user_ride_booking')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('product_book_id')->nullable();
            $table->foreign('product_book_id')
                ->references('id')->on('user_store_product_booking')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('package_book_id')->nullable();
            $table->foreign('package_book_id')
                ->references('id')->on('user_service_package_booking')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->float('rating');
            $table->string('comment')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('user_rating');
    }
}
