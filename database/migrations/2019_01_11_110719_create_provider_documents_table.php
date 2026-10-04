<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_service_id');
            $table->foreign('provider_service_id')
                ->references('id')->on('provider_services')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedInteger('req_document_id');
            $table->foreign('req_document_id')
                ->references('id')->on('required_documents')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('document_file',40);
            $table->tinyInteger('status')->comment('0=pending,1=approved,2=rejected');
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
        Schema::dropIfExists('provider_documents');
    }
}
