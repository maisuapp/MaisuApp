<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorldCurrencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('world_currency', function (Blueprint $table) {
            $table->increments('id');
            $table->string('currency_name',50);
            $table->float('ratio');
            $table->string('symbol',10);
            $table->boolean('status')->default(1);
            $table->boolean('default_currency');
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
        Schema::dropIfExists('world_currency');
    }
}
