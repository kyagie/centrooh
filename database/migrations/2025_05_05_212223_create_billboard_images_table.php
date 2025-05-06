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
        Schema::create('billboard_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billboard_id')->constrained('billboards')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('image_type')->nullable()->comment('For example: "front", "side", "damage", etc.');
            $table->string('status')->default('active');
            $table->boolean('is_primary')->default(false);
            $table->string('uploader_type')->comment('The type of the uploader, e.g., "user" or "agent"');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billboard_images');
    }
};
