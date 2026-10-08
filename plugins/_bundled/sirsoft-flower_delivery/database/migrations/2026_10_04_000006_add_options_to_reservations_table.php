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
        Schema::table('g7_flower_reservations', function (Blueprint $table) {
            $table->json('selected_options')->nullable()->comment('선택 맞춤 옵션 스냅샷 [{group,key,label,price_delta}]')->after('recipient_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('g7_flower_reservations', function (Blueprint $table) {
            $table->dropColumn('selected_options');
        });
    }
};
