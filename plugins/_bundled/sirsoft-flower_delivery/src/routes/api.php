<?php

use Illuminate\Support\Facades\Route;
use Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\DeliverySlotAdminController;
use Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Public\DeliverySlotController;
use Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Public\ShowcaseController;
use Plugins\Sirsoft\FlowerDelivery\Http\Controllers\User\SubscriptionController;

/*
 * 꽃배달 플러그인 API 라우트
 *
 * URL prefix 자동 적용: /api/plugins/sirsoft-flower_delivery/
 * Name prefix 자동 적용: api.plugins.sirsoft-flower_delivery.
 */

/*
|--------------------------------------------------------------------------
| 공개 API (인증 불필요) — 배송 슬롯 조회 · 쇼케이스
|--------------------------------------------------------------------------
*/
Route::get('/products/{id}/delivery-slots', [DeliverySlotController::class, 'index'])
    ->where('id', '[0-9]+')
    ->name('products.delivery-slots.index');

Route::get('/products/by-code/{code}/delivery-slots', [DeliverySlotController::class, 'indexByCode'])
    ->where('code', '[A-Za-z0-9\-_]+')
    ->name('products.delivery-slots.index-by-code');

Route::get('/products/{id}/options', [ShowcaseController::class, 'options'])
    ->where('id', '[0-9]+')
    ->name('products.options.index');

Route::get('/products/{id}/share', [ShowcaseController::class, 'share'])
    ->where('id', '[0-9]+')
    ->name('products.share.show');

Route::get('/message-card/templates', [ShowcaseController::class, 'cardTemplates'])
    ->name('message-card.templates.index');

Route::get('/faqs', [\Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Public\FaqController::class, 'index'])
    ->name('faqs.index');

Route::get('/delivery-day-status', [ShowcaseController::class, 'dayStatus'])
    ->name('delivery-day-status.show');

/*
|--------------------------------------------------------------------------
| 사용자 API (sanctum 인증 필수)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/delivery-slots/reserve', [DeliverySlotController::class, 'reserve'])
        ->name('delivery-slots.reserve');

    Route::get('/subscriptions/mine', [SubscriptionController::class, 'mine'])
        ->name('subscriptions.mine');

    Route::post('/subscriptions', [SubscriptionController::class, 'store'])
        ->name('subscriptions.store');

    Route::patch('/subscriptions/{id}/pause', [SubscriptionController::class, 'pause'])
        ->where('id', '[0-9]+')
        ->name('subscriptions.pause');

    Route::patch('/subscriptions/{id}/resume', [SubscriptionController::class, 'resume'])
        ->where('id', '[0-9]+')
        ->name('subscriptions.resume');

    Route::get('/message-cards/mine', [\Plugins\Sirsoft\FlowerDelivery\Http\Controllers\User\MessageCardController::class, 'mine'])
        ->name('message-cards.mine');
});

/*
|--------------------------------------------------------------------------
| 관리자 API (sanctum + permission)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('auth:sanctum')->group(function () {
    Route::get('/delivery-slots/board', [DeliverySlotAdminController::class, 'board'])
        ->middleware('permission:admin,sirsoft-flower_delivery.slots.view')
        ->name('delivery-slots.board');

    Route::patch('/delivery-slots/{id}/toggle', [DeliverySlotAdminController::class, 'toggle'])
        ->where('id', '[0-9]+')
        ->middleware('permission:admin,sirsoft-flower_delivery.slots.update')
        ->name('delivery-slots.toggle');

    Route::get('/faqs', [\Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\FlowerFaqAdminController::class, 'index'])
        ->middleware('permission:admin,sirsoft-flower_delivery.slots.view')
        ->name('faqs.index');

    Route::post('/faqs', [\Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\FlowerFaqAdminController::class, 'store'])
        ->middleware('permission:admin,sirsoft-flower_delivery.slots.update')
        ->name('faqs.store');

    Route::put('/faqs/{id}', [\Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\FlowerFaqAdminController::class, 'update'])
        ->where('id', '[0-9]+')
        ->middleware('permission:admin,sirsoft-flower_delivery.slots.update')
        ->name('faqs.update');

    Route::delete('/faqs/{id}', [\Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\FlowerFaqAdminController::class, 'destroy'])
        ->where('id', '[0-9]+')
        ->middleware('permission:admin,sirsoft-flower_delivery.slots.update')
        ->name('faqs.destroy');

    Route::patch('/faqs/{id}/toggle', [\Plugins\Sirsoft\FlowerDelivery\Http\Controllers\Admin\FlowerFaqAdminController::class, 'toggle'])
        ->where('id', '[0-9]+')
        ->middleware('permission:admin,sirsoft-flower_delivery.slots.update')
        ->name('faqs.toggle');
});
