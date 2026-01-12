<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contact_merge_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_contact_id');
            $table->unsignedBigInteger('secondary_contact_id');
            $table->json('merged_emails')->nullable();
            $table->json('merged_phones')->nullable();
            $table->json('merged_custom_fields')->nullable();
            $table->json('secondary_contact_data')->nullable(); // Removed ->after()
            $table->timestamps();

            $table->foreign('master_contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('secondary_contact_id')->references('id')->on('contacts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contact_merge_logs'); // Drop the whole table
    }
};
