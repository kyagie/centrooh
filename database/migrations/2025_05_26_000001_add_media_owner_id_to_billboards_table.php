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
            $table->unsignedBigInteger('media_owner_id')->nullable()->after('agent_id');
            $table->foreign('media_owner_id')->references('id')->on('media_owners')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billboards', function (Blueprint $table) {
            $table->dropForeign(['media_owner_id']);
            $table->dropColumn('media_owner_id');
        });
    }
};
