<?php

namespace Modules\Sirsoft\Ecommerce\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Sirsoft\Ecommerce\Http\Requests\Admin\ImportProductsRequest;
use Modules\Sirsoft\Ecommerce\Services\ProductImportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 상품 일괄등록(CSV) 관리자 컨트롤러
 */
class ProductImportController extends Controller
{
    /**
     * @param ProductImportService $importService 가져오기 서비스
     */
    public function __construct(
        private readonly ProductImportService $importService,
    ) {}

    /**
     * 샘플 CSV를 다운로드합니다 (엑셀 작성용 양식).
     *
     * @return StreamedResponse CSV 파일 응답
     */
    public function sample(): StreamedResponse
    {
        $csv = $this->importService->sampleCsv();

        return response()->streamDownload(
            fn () => print($csv),
            'products_sample.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * CSV를 검증·등록합니다 (dry_run=1이면 검증만).
     *
     * @param ImportProductsRequest $request 검증된 요청
     * @return JsonResponse 처리 결과 응답
     */
    public function import(ImportProductsRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $parsed = $this->importService->parse($request->file('file'));

            $errors = array_merge(
                $parsed['errors'],
                $this->importService->validateRows($parsed['rows']),
            );

            if ($errors !== []) {
                return ResponseHelper::moduleError(
                    'sirsoft-ecommerce',
                    'messages.products.import.invalid_rows',
                    422,
                    ['errors' => $errors, 'row_count' => count($parsed['rows'])]
                );
            }

            if ($request->boolean('dry_run')) {
                $rowCount = count($parsed['rows']);

                return ResponseHelper::moduleSuccess(
                    'sirsoft-ecommerce',
                    'messages.products.import.dry_run_ok',
                    ['row_count' => $rowCount],
                    200,
                    ['row_count' => $rowCount]
                );
            }

            $result = $this->importService->importRows($parsed['rows']);

            return ResponseHelper::moduleSuccess(
                'sirsoft-ecommerce',
                'messages.products.import.imported',
                array_merge($result, ['row_count' => count($parsed['rows'])]),
                201,
                [
                    'row_count' => count($parsed['rows']),
                    'created' => $result['created'],
                    'updated' => $result['updated'],
                ]
            );
        } catch (\Exception $e) {
            Log::error('상품 일괄등록 실패', ['error' => $e->getMessage()]);

            return ResponseHelper::moduleError(
                'sirsoft-ecommerce',
                'messages.products.import.failed',
                500
            );
        }
    }
}
