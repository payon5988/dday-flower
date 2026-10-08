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
        Schema::create('g7_flower_options', function (Blueprint $table) {
            $table->id()->comment('행 ID');
            $table->unsignedBigInteger('product_id')->comment('상품 ID (sirsoft-ecommerce 상품 참조, FK 미부착: 모듈 독립 배포)')->index();
            $table->string('option_group', 30)->comment('옵션 그룹 (ribbon/wrap/preservative/addon)');
            $table->string('option_key', 50)->comment('옵션 키 (예: ribbon_dusty_pink)');
            $table->string('label', 100)->comment('옵션 표시명');
            $table->integer('price_delta')->default(0)->comment('추가 금액 (원)');
            $table->string('color_code', 20)->nullable()->comment('컬러칩 표시용 색상 코드 (리본 그룹)');
            $table->integer('sort_order')->default(0)->comment('정렬 순서');
            $table->boolean('is_active')->default(true)->comment('활성 여부');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('테넌트 ID (SaaS 대비, nullable)')->index();
            $table->timestamps();

            $table->unique(['product_id', 'option_group', 'option_key', 'tenant_id'], 'flower_options_product_group_key_tenant_unique');
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('g7_flower_options', function (Blueprint $table) {
                $table->comment('꽃배달 상품 맞춤 옵션 (리본·포장·보존제·추가상품)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g7_flower_options');
    }
};
