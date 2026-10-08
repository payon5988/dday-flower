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
        Schema::create('g7_flower_reservations', function (Blueprint $table) {
            $table->id()->comment('행 ID');
            $table->string('reservation_code', 30)->comment('예약 코드 (예: RSV-20261004-0001)')->unique();
            $table->foreignId('user_id')->comment('예약자 ID')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->comment('상품 ID (sirsoft-ecommerce 상품 참조, FK 미부착: 모듈 독립 배포)')->index();
            $table->date('delivery_date')->comment('배송일');
            $table->string('time_slot', 30)->comment('시간대 (예: 14:00-16:00)');
            $table->text('encrypted_message')->nullable()->comment('메시지 본문 (encrypted 캐스트, 없으면 null)');
            $table->string('sender_name', 50)->nullable()->comment('보내는 사람');
            $table->string('recipient_name', 50)->nullable()->comment('받는 사람');
            $table->string('status', 20)->default('pending')->comment('상태 (pending/consumed/expired)');
            $table->unsignedBigInteger('consumed_order_id')->nullable()->comment('소진 주문 ID')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('테넌트 ID (SaaS 대비, nullable)')->index();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('g7_flower_reservations', function (Blueprint $table) {
                $table->comment('꽃배달 슬롯 예약 (주문 연결용)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g7_flower_reservations');
    }
};
