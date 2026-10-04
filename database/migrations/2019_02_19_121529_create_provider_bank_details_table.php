<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderBankDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_bank_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_id');
            $table->foreign('provider_id')
                ->references('id')->on('providers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('account_number');
            $table->string('holder_name');
            $table->string('bank_name');
            $table->string('bank_location');
            $table->string('payment_email');
            $table->string('bic_swift_code');
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
        Schema::dropIfExists('provider_bank_details');
    }
}
