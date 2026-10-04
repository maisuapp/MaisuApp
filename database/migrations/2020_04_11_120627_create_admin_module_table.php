<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_module', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('module_name');
            $table->string('module_action');
            $table->string('seq');
            $table->string('status')->default(1)->comment('0:inactive , 1:active');
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
        Schema::dropIfExists('admin_module');
    }
}
