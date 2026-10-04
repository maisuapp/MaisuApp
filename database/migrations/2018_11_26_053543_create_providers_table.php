<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30);
            $table->string('email')->unique()->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('contact_number', 15)->unique()->nullable();
            $table->string('avatar')->nullable();
            $table->string('password')->nullable();
            $table->string('login_id')->nullable();
            $table->string('login_type', 10);
            $table->float('service_radius')->nullable();
            $table->tinyInteger('gender')->nullable()->comment('0=male,1=female,2=others');
            $table->string('access_token')->nullable();
            $table->string('device_token')->nullable();
            $table->tinyInteger('status')->comment('0=pending, 1=activate, 2=blocked, 3=not-apply')->default(3);
            $table->dateTime('last_active');
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
        Schema::dropIfExists('providers');
    }
}
