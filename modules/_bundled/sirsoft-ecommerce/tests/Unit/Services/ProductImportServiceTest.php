<?php

namespace Modules\Sirsoft\Ecommerce\Tests\Unit\Services;

use Modules\Sirsoft\Ecommerce\Services\ProductImportService;
use Modules\Sirsoft\Ecommerce\Tests\ModuleTestCase;

/**
 * 상품 일괄등록(CSV) 서비스 테스트
 */
class ProductImportServiceTest extends ModuleTestCase
{
    /**
     * 샘플 CSV 구조를 검증합니다.
     *
     * @return void
     */
    public function test_sample_csv_has_header_and_examples(): void
    {
        $service = app(ProductImportService::class);
        $parsed = $service->parse($service->sampleCsv());

        $this->assertSame([], $parsed['errors']);
        $this->assertGreaterThanOrEqual(2, count($parsed['rows']));
        $this->assertSame('FLWSAMPLE001', $parsed['rows'][0]['product_code']);
    }

    /**
     * 헤더 불일치 시 행 오류를 반환합니다.
     *
     * @return void
     */
    public function test_parse_rejects_bad_header(): void
    {
        $service = app(ProductImportService::class);
        $parsed = $service->parse("code,name\nA,B\n");

        $this->assertSame([], $parsed['rows']);
        $this->assertNotEmpty($parsed['errors']);
    }

    /**
     * 행 검증을 검증합니다 (가격 역전·중복 코드).
     *
     * @return void
     */
    public function test_validate_rows_catches_price_and_dup_code(): void
    {
        $service = app(ProductImportService::class);
        $errors = $service->validateRows([
            ['product_code' => 'T-1', 'name_ko' => '테스트', 'name_en' => '', 'list_price' => '10000', 'selling_price' => '12000', 'stock_quantity' => '5', 'sales_status' => 'on_sale', 'display_status' => 'visible', 'category_slug' => ''],
            ['product_code' => 'T-1', 'name_ko' => '테스트2', 'name_en' => '', 'list_price' => '10000', 'selling_price' => '9000', 'stock_quantity' => '5', 'sales_status' => 'on_sale', 'display_status' => 'visible', 'category_slug' => ''],
        ]);

        $fields = array_column($errors, 'field');
        $this->assertContains('selling_price', $fields);
        $this->assertContains('product_code', $fields);
    }
}
