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
        Schema::create('g7_flower_message_cards', function (Blueprint $table) {
            $table->id()->comment('행 ID');
            $table->unsignedBigInteger('order_id')->comment('주문 ID (sirsoft-ecommerce 주문 참조, FK 미부착: 모듈 독립 배포)')->unique();
            $table->text('encrypted_message')->comment('암호화 메시지 본문 (encrypted 캐스트)');
            $table->string('sender_name', 50)->comment('보내는 사람');
            $table->string('recipient_name', 50)->comment('받는 사람');
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('g7_flower_message_cards', function (Blueprint $table) {
                $table->comment('꽃배달 암호화 메시지 카드');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g7_flower_message_cards');
    }
};
