<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_product_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id');
            $table->foreign('store_id')
                ->references('id')->on('store_details')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('product_cate_id');
            $table->foreign('product_cate_id')
                ->references('id')->on('store_product_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('name', 30);
            $table->string('image', 40)->nullable();
            $table->float('amount')->default(0);
            $table->float('discount_amount')->default(0);
            $table->tinyInteger('discount_type')->comment('0=no, 1=amount, 2=percentage')->default(0);
            $table->tinyInteger('food_type')->comment('0=not required, 1=veg, 2=non-veg')->default(0);
            $table->string('slug',40);
            $table->text('description')->nullable();
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
        Schema::dropIfExists('store_product_details');
    }
}
