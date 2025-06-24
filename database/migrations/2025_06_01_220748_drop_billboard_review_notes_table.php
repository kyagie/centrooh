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
        Schema::dropIfExists('billboard_review_notes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('billboard_review_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billboard_id')->constrained('billboards')->cascadeOnDelete();
            $table->longText('note');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
