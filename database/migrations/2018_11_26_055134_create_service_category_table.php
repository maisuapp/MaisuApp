<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_category', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',30)->unique();
            $table->string('slug',30)->unique();
            $table->string('icon_name',35);
            $table->integer('display_order')->default(0);
            $table->tinyInteger('category_type')->comment('1=transport, 2=store, 3=others-part-1, 4=others-part-2, 5= transport-part-2')->default(3);
            $table->tinyInteger('status')->comment('1=on, 0=off')->default(1);
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
        Schema::dropIfExists('service_category');
    }
}
