import '../css/flower.css';

export * from './FlowerDeliveryCalendar';

export const FLOWER_THEME = {
  forest: '#2D4A3E',
  cream: '#F9F5F0',
  blush: '#E8C4C4',
  gold: '#D4AF37',
} as const;

export function formatMaskedPhone(phone: string): string {
  // 주문 완료 페이지 PIPA 마스킹: 010-****-1234
  const digits = phone.replace(/\D/g, '');
  if (digits.length < 8) return phone;
  const tail = digits.slice(-4);
  const head = digits.slice(0, 3);
  return `${head}-****-${tail}`;
}
