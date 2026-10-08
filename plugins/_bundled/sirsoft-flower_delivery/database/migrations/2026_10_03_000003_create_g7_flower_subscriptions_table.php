<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('g7_flower_subscriptions', function (Blueprint $table) {
            $table->id()->comment('행 ID');
            $table->foreignId('user_id')->comment('구독자 ID')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->comment('정기구독 상품 ID')->index();
            $table->string('cycle', 20)->default('weekly')->comment('구독 주기 (weekly/monthly)');
            $table->date('next_delivery_date')->nullable()->comment('다음 배송일');
            $table->string('status', 20)->default('active')->comment('구독 상태 (active/paused/cancelled)');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('테넌트 ID (SaaS 대비, nullable)')->index();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('g7_flower_subscriptions', function (Blueprint $table) {
                $table->comment('꽃배달 정기구독');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g7_flower_subscriptions');
    }
};
