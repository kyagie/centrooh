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
        Schema::table('regions', function (Blueprint $table) {
            // Make created_by nullable if it's not already
            $table->foreignId('created_by')->nullable()->change();
            
            // Drop the description column
            $table->dropColumn('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            // Add the description column back
            $table->text('description')->nullable();
            
            // Make created_by non-nullable again
            $table->foreignId('created_by')->nullable(false)->change();
        });
    }
};
