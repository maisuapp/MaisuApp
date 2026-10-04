<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 30);
            $table->string('last_name', 20)->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('contact_number', 15)->unique()->nullable();
            $table->string('login_type', 10);
            $table->string('login_id')->nullable();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->tinyInteger('gender')->comment('1=male, 0=female')->default(0);
            $table->string('invite_code')->nullable();
            $table->bigInteger('access_token')->nullable();
            $table->string('device_token')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('language', 10)->nullable();
            $table->string('emergency_contact')->nullable();
            $table->tinyInteger('status')->comment('1=active, 0=block')->default(1);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
