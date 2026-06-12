import Link from 'next/link';
import { Megaphone } from 'lucide-react';
import type { Tool } from '@/lib/types';
import { PromoBanner, PromoSideCard } from './PromoSlot';

const SLOT_LABELS: Record<string, string> = {
  'home-mid': '首页中部横幅',
  'ranking-top': '榜单顶部横幅',
  'detail-side': '详情页侧栏',
  'tools-inline': '工具库信息流',
  'news-inline': '资讯信息流',
  'detail-bottom': '详情页底部横幅',
};

/**
 * 广告运营位。已分配工具 → 推广卡；未分配 → 「虚位以待」占位（广告位提前可见，方便招商）。
 * 后台给任意工具加自定义字段 promo=<slot> 即上刊。
 */
export default function AdSlot({
  slot,
  tools,
  variant = 'banner',
}: {
  slot: string;
  tools: Tool[];
  variant?: 'banner' | 'side';
}) {
  if (tools.length > 0) {
    return variant === 'side' ? <PromoSideCard tools={tools} /> : <PromoBanner tool={tools[0]} />;
  }

  const label = SLOT_LABELS[slot] ?? slot;

  if (variant === 'side') {
    return (
      <Link
        href="/submit"
        className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50/60 px-5 py-8 text-center transition hover:border-brand-300 hover:bg-brand-50/40"
        aria-label={`广告位招商：${label}`}
      >
        <Megaphone size={20} className="text-gray-300" />
        <p className="mt-2 text-sm font-medium text-gray-400">
          广告位 <span className="font-display">AD</span>
        </p>
        <p className="mt-1 text-xs text-gray-400">{label} · 虚位以待</p>
        <span className="mt-3 text-xs text-brand-500">联系投放 →</span>
      </Link>
    );
  }

  return (
    <Link
      href="/submit"
      className="flex items-center justify-center gap-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50/60 px-6 py-7 transition hover:border-brand-300 hover:bg-brand-50/40"
      aria-label={`广告位招商：${label}`}
    >
      <Megaphone size={18} className="shrink-0 text-gray-300" />
      <span className="text-sm text-gray-400">
        广告位 <span className="font-display font-semibold">AD</span> · {label} · 虚位以待
      </span>
      <span className="shrink-0 text-xs text-brand-500">联系投放 →</span>
    </Link>
  );
}
