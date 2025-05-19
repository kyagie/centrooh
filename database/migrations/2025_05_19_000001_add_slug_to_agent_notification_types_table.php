<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agent_notification_types', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
        });

        // Backfill slugs for existing rows
        $types = DB::table('agent_notification_types')->get();
        foreach ($types as $type) {
            $slug = Str::slug($type->name);
            // Ensure uniqueness
            $originalSlug = $slug;
            $i = 1;
            while (DB::table('agent_notification_types')->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $i;
                $i++;
            }
            DB::table('agent_notification_types')->where('id', $type->id)->update(['slug' => $slug]);
        }

        // Make slug not nullable
        Schema::table('agent_notification_types', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_notification_types', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
