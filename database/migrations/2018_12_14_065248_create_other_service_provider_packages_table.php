<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtherServiceProviderPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_service_provider_packages', function (Blueprint $table) {
            $table->increments('id');
//            $table->unsignedInteger('provider_id');
//            $table->foreign('provider_id')
//                ->references('id')->on('providers')
//                ->onUpdate('cascade')
//                ->onDelete('cascade');
            $table->unsignedInteger('provider_service_id');
            $table->foreign('provider_service_id')
                ->references('id')->on('provider_services')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('sub_cat_id');
            $table->foreign('sub_cat_id')
                ->references('id')->on('other_service_sub_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->float('price')->default(0);
            $table->integer('max_book_quantity')->default(1);
            $table->tinyInteger('status')->comment('0=deactivate, 1=activate')->default(0);
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
        Schema::dropIfExists('other_service_provider_packages');
    }
}
