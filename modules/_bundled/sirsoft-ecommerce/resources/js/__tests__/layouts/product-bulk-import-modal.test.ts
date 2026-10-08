/**
 * @file product-bulk-import-modal.test.ts
 * @description 상품 일괄등록 모달 배선 회귀 테스트
 *
 * - 목록 도구모음에 일괄등록 버튼 → modal_bulk_import 오픈
 * - 모달에 샘플 다운로드·CSV 선택·검증전용·업로드 요소 존재
 * - 노드 직접 액션은 type/event 필수 (charAt 회귀)
 */
import { describe, it, expect } from 'vitest';
import listJson from '../../../layouts/admin/admin_ecommerce_product_list.json';
import modalJson from '../../../layouts/admin/partials/admin_ecommerce_product_list/_modal_bulk_import.json';

function findAll(node: any, predicate: (n: any) => boolean, out: any[] = []): any[] {
  if (node == null || typeof node !== 'object') return out;
  if (predicate(node)) out.push(node);
  for (const value of Object.values(node)) {
    if (Array.isArray(value)) {
      for (const item of value) findAll(item, predicate, out);
    } else if (value && typeof value === 'object') {
      findAll(value, predicate, out);
    }
  }
  return out;
}

describe('상품 일괄등록 모달 배선', () => {
  it('목록에 일괄등록 버튼과 모달 파셜이 있다', () => {
    const serialized = JSON.stringify(listJson);
    expect(serialized).toContain('modal_bulk_import');
    expect(serialized).toContain('_modal_bulk_import.json');
  });

  it('모달에 샘플·파일·검증전용·업로드 요소가 있다', () => {
    const serialized = JSON.stringify(modalJson);
    expect(serialized).toContain('/import/sample');
    expect(serialized).toContain('/import');
    expect(serialized).toContain('multipart/form-data');
    expect(serialized).toContain('dry_run');
  });

  it('모달의 노드 직접 액션은 type 또는 event 를 갖는다', () => {
    const bad = findAll(
      modalJson,
      (n) =>
        Array.isArray(n.actions) &&
        n.actions.some((a: any) => a && typeof a === 'object' && 'handler' in a && !('type' in a) && !('event' in a))
    );
    expect(bad, 'type 없는 노드 액션').toEqual([]);
  });
});

describe('일괄등록 인증 전달', () => {
  it('샘플·업로드 apiCall 은 auth_required 다', async () => {
    const mod = (await import('../../../layouts/admin/partials/admin_ecommerce_product_list/_modal_bulk_import.json')) as any;
    const calls: any[] = [];
    const walk = (n: any): void => {
      if (n == null || typeof n !== 'object') return;
      if (Array.isArray(n)) {
        n.forEach(walk);
        return;
      }
      if (n.handler === 'apiCall' && typeof n.target === 'string' && n.target.includes('/import')) {
        calls.push(n);
      }
      Object.values(n).forEach(walk);
    };
    walk(mod.default ?? mod);
    expect(calls.length).toBe(1);
    for (const c of calls) {
      expect(c.auth_required, c.target).toBe(true);
    }
    expect(JSON.stringify(mod.default ?? mod)).toContain('downloadAttachment');
  });
});
