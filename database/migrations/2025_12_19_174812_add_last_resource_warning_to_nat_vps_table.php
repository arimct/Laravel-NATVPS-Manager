<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nat_vps', function (Blueprint $table) {
            $table->timestamp('last_resource_warning_at')->nullable()->after('specs_cached_at');
        });
    }

    public function down(): void
    {
        Schema::table('nat_vps', function (Blueprint $table) {
            $table->dropColumn('last_resource_warning_at');
        });
    }
};
