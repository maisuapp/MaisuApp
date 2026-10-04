<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_permission', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')
                ->references('id')->on('super_admin')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('module_id');
            $table->foreign('module_id')
                ->references('id')->on('admin_module')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('permission');
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
        Schema::dropIfExists('admin_permission');
    }
}
