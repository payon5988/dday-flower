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
        Schema::create('g7_flower_faqs', function (Blueprint $table) {
            $table->id()->comment('행 ID');
            $table->string('category', 30)->default('general')->comment('분류 (order/delivery/subscription/general)');
            $table->json('question')->comment('질문 (로케일별)');
            $table->json('answer')->comment('답변 (로케일별)');
            $table->integer('sort_order')->default(0)->comment('정렬 순서');
            $table->boolean('is_active')->default(true)->comment('활성 여부');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('테넌트 ID (SaaS 대비, nullable)')->index();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('g7_flower_faqs', function (Blueprint $table) {
                $table->comment('꽃배달 자주 묻는 질문 (관리자 관리)');
            });
        }

        // 기본 3문항 시드 (신규 설치 시 빈 화면 방지)
        DB::table('g7_flower_faqs')->insert([
            [
                'category' => 'order',
                'question' => json_encode(['ko' => '당일배송 마감은 몇 시인가요?', 'en' => 'What is the same-day cutoff?', 'ja' => '当日配送の締切は何時ですか？'], JSON_UNESCAPED_UNICODE),
                'answer' => json_encode(['ko' => '14시 이전 주문까지 당일 출발합니다. 이후 주문은 다음날 가장 빠른 시간으로 자동 안내됩니다.', 'en' => 'Orders by 2 PM leave the same day. Later orders move to the earliest slot tomorrow.', 'ja' => '14時までのご注文は当日発送します。以降は翌日の最も早い時間帯を自動案内します。'], JSON_UNESCAPED_UNICODE),
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'delivery',
                'question' => json_encode(['ko' => '메시지 카드는 누가 볼 수 있나요?', 'en' => 'Who can read my message card?', 'ja' => 'メッセージカードは誰が読めますか？'], JSON_UNESCAPED_UNICODE),
                'answer' => json_encode(['ko' => '암호화 저장되어 배송 기사 외에는 누구도 열람할 수 없습니다. 주문 완료 화면에서도 내용은 표시되지 않습니다.', 'en' => 'It is encrypted — no one but the delivery rider can read it. The content never shows on screen.', 'ja' => '暗号化保存され、配達員以外は閲覧できません。内容が画面に表示されることはありません。'], JSON_UNESCAPED_UNICODE),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'subscription',
                'question' => json_encode(['ko' => '정기구독을 잠시 쉴 수 있나요?', 'en' => 'Can I pause my subscription?', 'ja' => '定期便を一時休止できますか？'], JSON_UNESCAPED_UNICODE),
                'answer' => json_encode(['ko' => '프리미엄관 구독 섹션에서 일시정지·재개할 수 있습니다. 요금은 정지 기간만큼 청구되지 않습니다.', 'en' => 'Pause and resume anytime in the premium hall subscription section. Paused weeks are not billed.', 'ja' => 'プレミアム館の定期便セクションで一時停止・再開できます。停止期間の料金は請求されません。'], JSON_UNESCAPED_UNICODE),
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g7_flower_faqs');
    }
};
