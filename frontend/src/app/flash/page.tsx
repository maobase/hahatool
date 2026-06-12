import type { Metadata } from 'next';
import { Zap } from 'lucide-react';
import { getFlash } from '@/lib/api';
import EmptyState from '@/components/EmptyState';
import FlashTimeline from '@/components/FlashTimeline';
import Pagination from '@/components/Pagination';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'AI 快讯',
  description: 'AI 行业即时短讯，按时间线滚动更新。',
};

export default async function FlashPage({
  searchParams,
}: {
  searchParams: Promise<{ page?: string }>;
}) {
  const { page: pageParam } = await searchParams;
  const page = Math.max(1, Number(pageParam) || 1);
  const { items, pages } = await getFlash(page, 20);

  return (
    <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-gray-900 sm:text-3xl">
        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-white">
          <Zap size={18} className="fill-current" />
        </span>
        AI 快讯
      </h1>
      <p className="mt-2 text-sm text-gray-500">行业即时短讯 · 按时间线更新</p>

      <div className="mt-8">
        {items.length === 0 ? <EmptyState title="暂无快讯" /> : <FlashTimeline items={items} />}
      </div>

      <Pagination current={page} total={pages} basePath="/flash" />
    </div>
  );
}
