<?php

namespace Modules\Sirsoft\Ecommerce\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Sirsoft\Ecommerce\Enums\ProductDisplayStatus;
use Modules\Sirsoft\Ecommerce\Enums\ProductSalesStatus;
use Modules\Sirsoft\Ecommerce\Models\Category;
use Modules\Sirsoft\Ecommerce\Models\Product;
use Modules\Sirsoft\Ecommerce\Models\ProductOption;

/**
 * 상품 일괄등록(CSV) 서비스
 *
 * 엑셀에 익숙한 운영자가 CSV로 상품을 대량 등록·갱신합니다.
 * 전수 검증 후 하나라도 오류가 있으면 아무것도 만들지 않습니다 (all-or-nothing).
 * 생성·수정은 ProductService 단일 경로를 타므로 훅·SEO·통화 규칙이 동일하게 적용됩니다.
 */
class ProductImportService
{
    /**
     * CSV 헤더 (영문 정본 — 한글 별칭도 허용).
     *
     * @var array<int, string>
     */
    public const HEADERS = [
        'product_code',
        'name_ko',
        'name_en',
        'list_price',
        'selling_price',
        'stock_quantity',
        'sales_status',
        'display_status',
        'category_slug',
        'description_ko',
    ];

    /**
     * 한글 헤더 별칭 → 정본 키.
     *
     * @var array<string, string>
     */
    public const HEADER_ALIASES = [
        '상품코드' => 'product_code',
        '상품명(국문)' => 'name_ko',
        '상품명(영문)' => 'name_en',
        '정가' => 'list_price',
        '판매가' => 'selling_price',
        '재고' => 'stock_quantity',
        '판매상태' => 'sales_status',
        '전시상태' => 'display_status',
        '카테고리' => 'category_slug',
        '상세설명' => 'description_ko',
    ];

    /**
     * 한 번에 처리할 최대 행 수.
     */
    public const MAX_ROWS = 500;

    /**
     * @param ProductService $productService 상품 서비스 (생성·수정 단일 경로)
     */
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    /**
     * 샘플 CSV 본문을 생성합니다 (한글 헤더 + 예시 2행, UTF-8 BOM 포함).
     *
     * @return string CSV 본문
     */
    public function sampleCsv(): string
    {
        $rows = [
            ['상품코드', '상품명(국문)', '상품명(영문)', '정가', '판매가', '재고', '판매상태', '전시상태', '카테고리', '상세설명'],
            ['FLWSAMPLE001', '샘플 장미 꽃다발', 'Sample Rose Bouquet', '59000', '49000', '50', 'on_sale', 'visible', 'bouquet', '신선한 장미 11송이 꽃다발입니다.'],
            ['FLWSAMPLE002', '샘플 튤립 믹스', 'Sample Tulip Mix', '45000', '39000', '30', 'on_sale', 'visible', 'bouquet', '봄 튤립 믹스 10송이입니다.'],
        ];

        $out = "\xEF\xBB\xBF";
        foreach ($rows as $row) {
            $out .= $this->encodeRow($row)."\n";
        }

        return $out;
    }

    /**
     * 업로드 파일을 파싱합니다 (인코딩 정규화 포함).
     *
     * @param UploadedFile|string $file 업로드 파일 또는 내용
     * @return array{rows: array<int, array<string, string>>, errors: array<int, array{row: int|string, field: string, message: string}>}
     */
    public function parse(UploadedFile|string $file): array
    {
        $content = $file instanceof UploadedFile ? (string) file_get_contents($file->getRealPath()) : $file;

        // UTF-8 BOM 제거 · EUC-KR 추정 시 변환 (엑셀 한글 CSV 대응)
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'EUC-KR');
        }

        $lines = preg_split('/\r\n|\r|\n/', $content);
        $lines = array_values(array_filter($lines, fn ($l) => trim($l) !== ''));

        if ($lines === []) {
            return ['rows' => [], 'errors' => [['row' => '-', 'field' => 'file', 'message' => __('sirsoft-ecommerce::messages.products.import.empty_file')]]];
        }

        $header = str_getcsv(array_shift($lines));
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        // 한글 별칭 → 정본 키 (영문 헤더도 그대로 허용, 대소문자 무시)
        $header = array_map(fn ($h) => self::HEADER_ALIASES[$h] ?? $h, $header);

        if ($header !== self::HEADERS) {
            return ['rows' => [], 'errors' => [['row' => '-', 'field' => 'header', 'message' => __('sirsoft-ecommerce::messages.products.import.bad_header')]]];
        }

        if (count($lines) > self::MAX_ROWS) {
            return ['rows' => [], 'errors' => [['row' => '-', 'field' => 'file', 'message' => __('sirsoft-ecommerce::messages.products.import.too_many_rows', ['max' => self::MAX_ROWS])]]];
        }

        $rows = [];
        foreach ($lines as $i => $line) {
            $cells = str_getcsv($line);
            $row = [];
            foreach (self::HEADERS as $j => $key) {
                $row[$key] = trim((string) ($cells[$j] ?? ''));
            }
            $rows[] = $row;
        }

        return ['rows' => $rows, 'errors' => []];
    }

    /**
     * 파싱된 행들을 검증합니다.
     *
     * @param array<int, array<string, string>> $rows 파싱된 행
     * @return array<int, array{row: int, field: string, message: string}> 행 오류 목록 (2부터 시작: 1은 헤더)
     */
    public function validateRows(array $rows): array
    {
        $errors = [];
        $seenCodes = [];
        $salesValues = array_map(fn ($c) => $c->value, ProductSalesStatus::cases());
        $displayValues = array_map(fn ($c) => $c->value, ProductDisplayStatus::cases());

        foreach ($rows as $i => $row) {
            $line = $i + 2;
            $fail = function (string $field) use (&$errors, $line) {
                return function (string $message) use (&$errors, $line, $field) {
                    $errors[] = ['row' => $line, 'field' => $field, 'message' => $message];
                };
            };

            $code = $row['product_code'];
            if ($code === '' || mb_strlen($code) > 50) {
                $fail('product_code')(__('sirsoft-ecommerce::messages.products.import.invalid_code'));
            } elseif (isset($seenCodes[$code])) {
                $fail('product_code')(__('sirsoft-ecommerce::messages.products.import.dup_code', ['line' => $seenCodes[$code]]));
            } else {
                $seenCodes[$code] = $line;
            }

            if ($row['name_ko'] === '' || mb_strlen($row['name_ko']) > 200) {
                $fail('name_ko')(__('sirsoft-ecommerce::messages.products.import.invalid_name'));
            }

            foreach (['list_price', 'selling_price'] as $priceKey) {
                if (! is_numeric($row[$priceKey]) || (float) $row[$priceKey] < 0.01) {
                    $fail($priceKey)(__('sirsoft-ecommerce::messages.products.import.invalid_price'));
                }
            }

            if (is_numeric($row['list_price']) && is_numeric($row['selling_price'])
                && (float) $row['selling_price'] > (float) $row['list_price']) {
                $fail('selling_price')(__('sirsoft-ecommerce::messages.products.import.price_exceeds_list'));
            }

            if (! ctype_digit($row['stock_quantity'])) {
                $fail('stock_quantity')(__('sirsoft-ecommerce::messages.products.import.invalid_stock'));
            }

            if (! in_array($row['sales_status'], $salesValues, true)) {
                $fail('sales_status')(__('sirsoft-ecommerce::messages.products.import.invalid_sales_status', ['values' => implode(',', $salesValues)]));
            }

            if (! in_array($row['display_status'], $displayValues, true)) {
                $fail('display_status')(__('sirsoft-ecommerce::messages.products.import.invalid_display_status', ['values' => implode(',', $displayValues)]));
            }

            if ($row['category_slug'] !== '' && ! Category::where('slug', $row['category_slug'])->exists()) {
                $fail('category_slug')(__('sirsoft-ecommerce::messages.products.import.unknown_category', ['slug' => $row['category_slug']]));
            }

            if (mb_strlen($row['description_ko'] ?? '') > 65535) {
                $fail('description_ko')(__('sirsoft-ecommerce::messages.products.import.invalid_description'));
            }
        }

        return $errors;
    }

    /**
     * 검증된 행들을 등록·갱신합니다 (UPSERT: 상품코드 기준).
     *
     * @param array<int, array<string, string>> $rows 검증된 행
     * @return array{created: int, updated: int}
     */
    public function importRows(array $rows): array
    {
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($rows, &$created, &$updated) {
            foreach ($rows as $row) {
                // 소프트삭제 행까지 조회 — 삭제된 코드 재등록은 복원+수정으로 처리
                $existing = Product::withTrashed()->where('product_code', $row['product_code'])->first();

                if ($existing && $existing->trashed()) {
                    $existing->restore();
                }

                $payload = [
                    'name' => array_filter(['ko' => $row['name_ko'], 'en' => $row['name_en'] ?: null]),
                    'list_price' => (float) $row['list_price'],
                    'selling_price' => (float) $row['selling_price'],
                    'stock_quantity' => (int) $row['stock_quantity'],
                    'sales_status' => $row['sales_status'],
                    'display_status' => $row['display_status'],
                ];

                if (trim($row['description_ko'] ?? '') !== '') {
                    $payload['description'] = ['ko' => trim($row['description_ko'])];
                }

                if ($row['category_slug'] !== '') {
                    $category = Category::where('slug', $row['category_slug'])->first();
                    if ($category) {
                        $payload['category_ids'] = [$category->id];
                    }
                }

                if ($existing) {
                    $this->productService->update($existing, $payload);
                    $updated++;
                } else {
                    $payload['product_code'] = $row['product_code'];
                    $product = $this->productService->create($payload);
                    $this->ensureDefaultOption($product);
                    $created++;
                }
            }
        });

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * 기본 단일 옵션을 보장합니다 (장바구니 담기·관리자 수정 가능 조건).
     *
     * 수정 폼 검증이 옵션명·가격·재고를 요구하므로 모두 채웁니다.
     *
     * @param Product $product 상품 모델
     * @return void
     */
    private function ensureDefaultOption(Product $product): void
    {
        if (ProductOption::where('product_id', $product->id)->exists()) {
            return;
        }

        ProductOption::create([
            'product_id' => $product->id,
            'option_code' => 'DEFAULT',
            'option_name' => ['ko' => '기본 옵션', 'en' => 'Default option'],
            'option_values' => [['key' => ['ko' => '옵션', 'en' => 'Option'], 'value' => ['ko' => '기본', 'en' => 'Default']]],
            'list_price' => $product->list_price ?? 0,
            'selling_price' => $product->selling_price ?? 0,
            'is_active' => true,
            'sort_order' => 0,
            'stock_quantity' => $product->stock_quantity ?? 0,
        ]);
    }

    /**
     * CSV 행을 인코딩합니다 (따옴표 이스케이프).
     *
     * @param array<int, string> $row 행 셀
     * @return string CSV 행
     */
    private function encodeRow(array $row): string
    {
        return implode(',', array_map(
            fn ($c) => '"'.str_replace('"', '""', (string) $c).'"',
            $row
        ));
    }
}
