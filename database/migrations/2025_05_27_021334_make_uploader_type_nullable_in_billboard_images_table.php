<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('billboard_images', function (Blueprint $table) {
            $table->string('uploader_type')->nullable()->comment('The type of the uploader, e.g., "user" or "agent"')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billboard_images', function (Blueprint $table) {
            $table->string('uploader_type')->nullable(false)->comment('The type of the uploader, e.g., "user" or "agent"')->change();
        });
    }
};
