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
        Schema::table('billboards', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['district_id']);
            
            // Change the column to be nullable
            $table->foreignId('district_id')->nullable()->change();
            
            // Add the foreign key constraint back, but with nullOnDelete
            $table->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billboards', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['district_id']);
            
            // Make the column required again
            $table->foreignId('district_id')->nullable(false)->change();
            
            // Add the original foreign key constraint back
            $table->foreign('district_id')->references('id')->on('districts')->cascadeOnDelete();
        });
    }
};
