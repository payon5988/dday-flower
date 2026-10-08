/**
 * @file shop-flower-premium.test.tsx
 * @description 프리미엄 꽃배달관(shop/flower_premium) 레이아웃 정합 검증
 *
 * 회귀 차단:
 * 1. `extends` 누락 시 toast/modal 호스트가 없어 핸들러가 조용히 실패한다.
 * 2. data_source ID 는 routes/editor-spec 과 일치해야 한다 (flowerSlots 라벨 키 존재).
 * 3. 마감 슬롯 버튼은 미렌더(if) — disabled 렌더는 "URL 을 넣으면 살아나겠지" 식의 거짓 어포던스다.
 * 4. 목록 클러스터가 아닌 독립 페이지이므로 navigate 에는 query 의도를 명시한다.
 */

import { describe, it, expect } from 'vitest';
import flowerPremiumJson from '../../layouts/shop/flower_premium.json';
import routesJson from '../../routes.json';
import homeJson from '../../layouts/home.json';
import shopIndexJson from '../../layouts/shop/index.json';
import shopShowJson from '../../layouts/shop/show.json';
import userBaseJson from '../../layouts/_user_base.json';
import koShop from '../../lang/partial/ko/shop.json';
import enShop from '../../lang/partial/en/shop.json';

/** 객체 트리에서 조건을 만족하는 모든 노드를 수집 */
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

const serialized = JSON.stringify(flowerPremiumJson);

describe('프리미엄 꽃배달관 기본 계약', () => {
  it('_user_base 를 상속한다 (toast/modal 호스트)', () => {
    expect((flowerPremiumJson as any).extends).toBe('_user_base');
  });

  it('/flower-premium 라우트가 shop/flower_premium 레이아웃을 지목한다', () => {
    const route = (routesJson as any).routes.find(
      (r: any) => r.path === '/flower-premium' && r.layout === 'shop/flower_premium'
    );
    expect(route, '/flower-premium 라우트가 존재해야 함').toBeDefined();
    expect(route.auth_required).toBe(false);
  });

  it('data_source 는 products + flowerSlots 이다', () => {
    const ids = (flowerPremiumJson as any).data_sources.map((s: any) => s.id);
    expect(ids).toContain('products');
    expect(ids).toContain('flowerSlots');
    expect(ids).toContain('dayStatus');
    expect(ids).toContain('flowerOptions');
    expect(ids).toContain('cardTemplates');
  });

  it('개인정보 동의 모달이 있고 자세히 보기 버튼의 target 과 일치한다', () => {
    const modals = (flowerPremiumJson as any).modals ?? [];
    expect(modals.map((m: any) => m.id)).toContain('flowerPrivacyModal');
    const openers = findAll(
      flowerPremiumJson,
      (n) => n.handler === 'openModal' && (n as any).target === 'flowerPrivacyModal'
    );
    expect(openers.length).toBeGreaterThan(0);
  });
});

describe('프리미엄 꽃배달관 바인딩 정합', () => {
  it('반복 렌더링은 item_var/index_var 를 쓴다 ("item"/"index" 금지)', () => {
    expect(serialized).not.toContain('"item"');
    expect(serialized).not.toMatch(/"index"(?!_var)/);
  });

  it('슬롯 반복의 원천은 옵셔널 체이닝 + 빈 배열 폴백이다', () => {
    expect(serialized).toContain('flowerSlots?.data?.slots ?? []');
  });

  it('마감 슬롯 예약 버튼은 조건부 미렌더(if)이다', () => {
    const reserveButtons = findAll(
      flowerPremiumJson,
      (n) => n.name === 'Button' && n.text === '$t:shop.flower.reserve_btn'
    );
    expect(reserveButtons.length).toBe(1);
    expect(reserveButtons[0].if).toContain('slot?.available === true');
  });

  it('예약 apiCall 은 target 최상위 + 성공 시 슬롯 refetch 를 동반한다', () => {
    const apiCalls = findAll(
      flowerPremiumJson,
      (n) => n.handler === 'apiCall' && typeof n.target === 'string' && n.target.includes('delivery-slots/reserve')
    );
    expect(apiCalls.length).toBe(1);
    expect(JSON.stringify(apiCalls[0].params?.body ?? {})).toContain('message_card');
    const onSuccess = JSON.stringify(apiCalls[0].onSuccess ?? []);
    expect(onSuccess).toContain('refetchDataSource');
    expect(onSuccess).toContain('flowerSlots');
    expect(onSuccess).toContain('flowerDraft');
  });

  it('폼 제출 버튼은 type="button" 으로 submit 을 방지한다', () => {
    const buttons = findAll(
      flowerPremiumJson,
      (n) => n.name === 'Button' && n.props?.type === 'button'
    );
    expect(buttons.length).toBeGreaterThan(0);
  });

  it('4:5 커스텀 카드는 상세 이동 경로에 product_code 폴백을 둔다', () => {
    expect(serialized).toContain('aspect-[4/5]');
    expect(serialized).toContain('product?.product_code ?? product?.id');
  });

  it('구독 일시정지/재개는 상태별 상호배타 if 로 분기한다', () => {
    const pauseBtn = findAll(
      flowerPremiumJson,
      (n) => n.name === 'Button' && n.text === '$t:shop.flower.pause_btn'
    );
    const resumeBtn = findAll(
      flowerPremiumJson,
      (n) => n.name === 'Button' && n.text === '$t:shop.flower.resume_btn'
    );
    expect(pauseBtn.length).toBe(1);
    expect(resumeBtn.length).toBe(1);
    expect(pauseBtn[0].if).toContain("sub?.status !== 'paused'");
    expect(resumeBtn[0].if).toContain("sub?.status === 'paused'");
  });

  it('사용자 대상 텍스트는 하드코딩하지 않고 $t: 키를 쓴다', () => {
    const koreanLiteral = findAll(
      flowerPremiumJson,
      (n) =>
        typeof n.text === 'string' &&
        !n.text.startsWith('{{') &&
        !n.text.startsWith('$t:') &&
        /[가-힣]/.test(n.text)
    );
    expect(koreanLiteral, '하드코딩된 한글 텍스트가 없어야 함').toEqual([]);
    const koreanMessage = findAll(
      flowerPremiumJson,
      (n) =>
        n.params &&
        typeof n.params.message === 'string' &&
        !n.params.message.startsWith('{{') &&
        !n.params.message.startsWith('$t:') &&
        /[가-힣]/.test(n.params.message)
    );
    expect(koreanMessage, '하드코딩된 한글 토스트가 없어야 함').toEqual([]);
  });

  it('레이아웃이 참조하는 $t:shop.flower.* 키는 ko/en 에 모두 존재한다', () => {    const refs = new Set<string>();
    const collect = (node: any): void => {
      if (node == null || typeof node !== 'object') return;
      for (const [k, v] of Object.entries(node)) {
        if (typeof v === 'string') {
          for (const m of v.matchAll(/\$t:shop\.flower\.([A-Za-z0-9_]+)/g)) refs.add(m[1]);
        } else if (v && typeof v === 'object') {
          collect(v);
        }
      }
    };
    collect(flowerPremiumJson);
    expect(refs.size).toBeGreaterThan(20);
    const missingKo = [...refs].filter((k) => !(koShop as any).flower?.[k]);
    const missingEn = [...refs].filter((k) => !(enShop as any).flower?.[k]);
    expect(missingKo, 'ko 누락 키').toEqual([]);
    expect(missingEn, 'en 누락 키').toEqual([]);
  });
});

describe('꽃쇼핑몰 전역 연결 (홈·목록·상세)', () => {
  it('홈·목록에 꽃 공지 파셜이 연결되고 dayStatus 소스를 갖는다', () => {
    for (const [name, layout] of [
      ['home', homeJson],
      ['shop/index', shopIndexJson],
    ] as const) {
      const partials = JSON.stringify((layout as any).slots);
      expect(partials, `${name} 공지 파셜`).toContain('partials/shop/flower/_notice.json');
      const ids = ((layout as any).data_sources as any[]).map((s) => s.id);
      expect(ids, `${name} dayStatus`).toContain('dayStatus');
    }
  });

  it('홈에 히어로·픽·구독 파셜과 products 소스가 있다', () => {
    const partials = JSON.stringify((homeJson as any).slots);
    for (const p of ['_hero.json', '_pick.json', '_sub_cta.json']) {
      expect(partials, p).toContain(p);
    }
    const ids = ((homeJson as any).data_sources as any[]).map((s: any) => s.id);
    expect(ids).toContain('products');
  });

  it('상품 상세에 코드 기준 슬롯 소스 + 슬롯 파셜이 있다', () => {
    const show = shopShowJson as any;
    const byCode = (show.data_sources as any[]).find((s) => s.id === 'flowerDetailSlots');
    expect(byCode, 'flowerDetailSlots 소스').toBeDefined();
    expect(byCode.endpoint).toContain('by-code/{{route.product_code}}');
    expect(JSON.stringify(show.slots)).toContain('partials/shop/detail/_flower_slots.json');
    expect(show.meta.seo.data_sources).toContain('flowerDetailSlots');
  });
});

describe('꽃쇼핑몰 홈·전역 장식', () => {
  it('홈에 구 게시판 섹션이 남지 않고 꽃 파셜 8종이다', () => {
    const serialized = JSON.stringify((homeJson as any).slots);
    for (const old of ['_welcome_card', '_stat_card_', '_recent_posts', '_popular_boards']) {
      expect(serialized, old).not.toContain(old);
    }
    for (const p of ['_notice.json', '_hero.json', '_pick.json', '_occasion.json', '_guide.json', '_sub_cta.json', '_story.json', '_faq.json']) {
      expect(serialized, p).toContain(p);
    }
    const ids = ((homeJson as any).data_sources as any[]).map((s: any) => s.id);
    expect(ids).toEqual(expect.arrayContaining(['products', 'dayStatus']));
    expect(ids).not.toContain('stats');
  });

  it('푸터에 꽃 링크 그룹 + flower-footer 클래스가 있다', () => {
    const serialized = JSON.stringify((userBaseJson as any).components ?? (userBaseJson as any));
    expect(serialized).toContain('flower-footer');
    expect(serialized).toContain('/flower-premium');
  });
});

describe('결제 준비중·메뉴 연결', () => {
  it('체크아웃에 카드·간편결제 준비중 블록이 있다', async () => {
    const mod = await import('../../layouts/partials/shop/_checkout_payment.json');
    const serialized = JSON.stringify(mod.default ?? mod);
    for (const k of ['pay_coming_title', 'pay_coming_desc', 'pay_coming_badge', 'pay_coming_toast', 'pay_card', 'pay_kakao', 'pay_naver', 'pay_toss']) {
      expect(serialized, k).toContain(`$t:shop.flower.${k}`);
    }
  });

  it('모바일 서랍에 꽃배달 섹션이 있다', async () => {
    const base = await import('../../layouts/_user_base.json');
    const serialized = JSON.stringify((base as any).default ?? base);
    expect(serialized).toContain('/flower-premium');
    expect(serialized).toContain('$t:shop.flower.menu_subscription');
    expect(serialized).toContain('$t:shop.flower.menu_dictionary');
    expect(serialized).toContain('$t:shop.flower.menu_b2b');
  });
});

describe('꽃 카테고리 메뉴 연결', () => {
  it('모바일 서랍에 꽃 카테고리 4종 바로가기가 있다', async () => {
    const base = await import('../../layouts/_user_base.json');
    const serialized = JSON.stringify((base as any).default ?? base);
    for (const slug of ['bouquet', 'basket', 'plant', 'wreath']) {
      expect(serialized, slug).toContain(`/category/${slug}`);
    }
  });
});

describe('경조사 문구 연결', () => {
  it('프리미엄관에 리본 안내 파셜이 있다', async () => {
    const layout = (await import('../../layouts/shop/flower_premium.json')) as any;
    const mod = layout.default ?? layout;
    expect(JSON.stringify(mod.slots)).toContain('partials/shop/flower/_ribbon.json');
  });
});

describe('꽃 파이프 연결 (옵션·공유·구독·아카이브)', () => {
  it('예약 본문에 message_card 와 selected_options 가 실린다', async () => {
    const layout = (await import('../../layouts/shop/flower_premium.json')) as any;
    const mod = layout.default ?? layout;
    const serializedSlots = JSON.stringify(mod.slots);
    expect(serializedSlots).toContain('message_card');
    expect(serializedSlots).toContain('selected_options');
    expect(serializedSlots).toContain('flowerShareModal');
    expect(serializedSlots).toContain('/subscriptions');
    const endpoints = ((mod as any).data_sources as any[]).map((s: any) => s.endpoint ?? '');
    expect(endpoints.some((e: string) => e.includes('/message-cards/mine'))).toBe(true);
    expect(serializedSlots).toContain('$t:shop.flower.archive_title');
  });

  it('홈에 꽃말·기업·관리팁 파셜이 있다', async () => {
    const layout = (await import('../../layouts/home.json')) as any;
    const mod = layout.default ?? layout;
    const serialized = JSON.stringify(mod.slots);
    for (const p of ['_dict.json', '_b2b.json', '_tips.json']) {
      expect(serialized, p).toContain(p);
    }
  });
});

describe('상세 페이지 메시지 작성기', () => {
  it('상세 슬롯 패널에 작성기 3종과 예약 본문 message_card 가 있다', async () => {
    const layout = (await import('../../layouts/partials/shop/detail/_flower_slots.json')) as any;
    const mod = layout.default ?? layout;
    const serialized = JSON.stringify(mod);
    expect(serialized).toContain('detailCard.sender');
    expect(serialized).toContain('detailCard.recipient');
    expect(serialized).toContain('detailCard.content');
    expect(serialized).toContain('message_card');
  });
});

describe('노드 액션 이벤트 타입 (charAt 회귀 차단)', () => {
  it('꽃 파일의 노드 직접 액션은 type 또는 event 를 갖는다', async () => {
    const files = [
      '../../layouts/shop/flower_premium.json',
      '../../layouts/partials/shop/flower/_faq.json',
      '../../layouts/partials/shop/flower/_notice.json',
      '../../layouts/partials/shop/flower/_hero.json',
      '../../layouts/partials/shop/flower/_pick.json',
      '../../layouts/partials/shop/flower/_sub_cta.json',
      '../../layouts/partials/shop/flower/_occasion.json',
      '../../layouts/partials/shop/flower/_guide.json',
      '../../layouts/partials/shop/flower/_story.json',
      '../../layouts/partials/shop/flower/_dict.json',
      '../../layouts/partials/shop/flower/_b2b.json',
      '../../layouts/partials/shop/flower/_tips.json',
      '../../layouts/partials/shop/flower/_ribbon.json',
      '../../layouts/partials/shop/detail/_flower_slots.json',
    ];
    const bad: string[] = [];
    const walk = (n: any, file: string): void => {
      if (n == null || typeof n !== 'object') return;
      if (Array.isArray(n)) {
        n.forEach((v) => walk(v, file));
        return;
      }
      if (Array.isArray((n as any).actions)) {
        for (const a of (n as any).actions) {
          if (a && typeof a === 'object' && 'handler' in a && !('type' in a) && !('event' in a)) {
            bad.push(`${file} <${(n as any).name}> <${(a as any).handler}>`);
          }
        }
      }
      for (const [k, v] of Object.entries(n)) {
        if (k !== 'actions') walk(v, file);
      }
    };
    for (const f of files) {
      const mod = (await import(f)) as any;
      walk(mod.default ?? mod, f);
    }
    expect(bad, 'type 없는 노드 액션').toEqual([]);
  });
});
