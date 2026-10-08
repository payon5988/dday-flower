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
        Schema::create('g7_flower_delivery_slots', function (Blueprint $table) {
            $table->id()->comment('행 ID');
            $table->unsignedBigInteger('product_id')->comment('상품 ID (sirsoft-ecommerce 상품 참조, FK 미부착: 모듈 독립 배포)')->index();
            $table->date('delivery_date')->comment('배송일')->index();
            $table->string('time_slot', 30)->comment('시간대 (예: 14:00-16:00)');
            $table->integer('max_capacity')->default(10)->comment('시간대별 최대 배송 건수');
            $table->integer('current_bookings')->default(0)->comment('현재 예약 건수');
            $table->boolean('is_active')->default(true)->comment('슬롯 활성 여부');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('테넌트 ID (SaaS 대비, nullable)')->index();
            $table->timestamps();

            // 동시성 제어 및 조회 성능을 위한 복합 유니크
            $table->unique(['product_id', 'delivery_date', 'time_slot', 'tenant_id'], 'flower_slots_product_date_slot_tenant_unique');
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('g7_flower_delivery_slots', function (Blueprint $table) {
                $table->comment('꽃배달 배송 슬롯 관리');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g7_flower_delivery_slots');
    }
};
