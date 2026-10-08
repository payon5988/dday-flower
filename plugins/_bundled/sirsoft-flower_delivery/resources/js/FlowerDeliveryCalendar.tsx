import { useState } from 'react';
import { FLOWER_THEME } from './index';

export interface DeliverySlot {
  time: string;
  available: boolean;
  remaining: number;
}

export interface FlowerDeliveryCalendarProps {
  productId: number;
  slots: DeliverySlot[];
  onSelect?: (date: string, time: string) => void;
}

/**
 * 스마트 배송 캘린더 오버레이 (감성 UI)
 *
 * 기존 상점 페이지를 덮어쓰지 않고 /flower-premium 또는 체크아웃 블록에 주입하는 조각.
 * 마감일은 회색·클릭 비활성화, 마감 임박(잔여<=2)은 웜 오렌지 경고.
 */
export function FlowerDeliveryCalendar({ productId, slots, onSelect }: FlowerDeliveryCalendarProps) {
  const [selected, setSelected] = useState<string | null>(null);

  return (
    <div
      className="flower-calendar"
      style={{ backgroundColor: FLOWER_THEME.forest }}
      data-testid={`flower-calendar-${productId}`}
    >
      <div className="text-sm opacity-80">배송 시간 선택</div>
      <div className="mt-2 grid grid-cols-2 gap-2">
        {slots.map((slot) => {
          const urgent = slot.available && slot.remaining <= 2;
          return (
            <button
              key={slot.time}
              type="button"
              disabled={!slot.available}
              onClick={() => {
                setSelected(slot.time);
                onSelect?.(new Date().toISOString().slice(0, 10), slot.time);
              }}
              className={slot.available ? '' : 'closed'}
              style={
                urgent
                  ? { border: '1px solid #E8955A', borderRadius: 8, padding: 8 }
                  : { border: '1px solid rgba(255,255,255,0.25)', borderRadius: 8, padding: 8 }
              }
              data-testid={`slot-${slot.time}`}
            >
              <div>{slot.time}</div>
              <div className="text-xs opacity-80">
                {slot.available ? `잔여 ${slot.remaining}` : '마감'}
                {urgent ? ' · 마감임박' : ''}
              </div>
              {selected === slot.time ? <div className="text-xs">선택됨</div> : null}
            </button>
          );
        })}
      </div>
    </div>
  );
}
