import type { Metadata } from 'next';
import Link from 'next/link';
import { getAllTools, getToolCategories, pickPromo } from '@/lib/api';
import AdSlot from '@/components/AdSlot';
import EmptyState from '@/components/EmptyState';
import Pagination from '@/components/Pagination';
import ToolGrid from '@/components/ToolGrid';

export const revalidate = 60;

export const metadata: Metadata = {
  title: '全部工具',
  description: '浏览 HahaTool 收录的全部 AI 工具，支持按分类、定价筛选与多维排序。',
};

const PAGE_SIZE = 24;

const PRICING_OPTIONS = ['免费', '免费增值', '付费'] as const;
const SORT_OPTIONS = [
  { key: 'latest', label: '最新收录' },
  { key: 'visits', label: '流量最高' },
  { key: 'likes', label: '收藏最多' },
  { key: 'growth', label: '增长最快' },
  { key: 'rating', label: '评分最高' },
] as const;

export default async function ToolsPage({
  searchParams,
}: {
  searchParams: Promise<{ page?: string; cat?: string; pricing?: string; sort?: string }>;
}) {
  const { page: pageParam, cat, pricing, sort } = await searchParams;
  const page = Math.max(1, Number(pageParam) || 1);
  const sortKey = SORT_OPTIONS.find((s) => s.key === sort)?.key ?? 'latest';

  const [allTools, categories] = await Promise.all([getAllTools(), getToolCategories()]);

  let filtered = cat ? allTools.filter((t) => t.categories.some((c) => c.slug === cat)) : allTools;
  if (pricing) filtered = filtered.filter((t) => t.pricing === pricing);
  filtered = [...filtered].sort((a, b) => {
    switch (sortKey) {
      case 'visits':
        return b.monthlyVisits - a.monthlyVisits;
      case 'likes':
        return b.likes - a.likes;
      case 'growth':
        return b.growth - a.growth;
      case 'rating':
        return b.rating - a.rating;
      default:
        return b.created - a.created;
    }
  });

  const pages = Math.ceil(filtered.length / PAGE_SIZE);
  const tools = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);

  /** 构造保留其他筛选条件的链接 */
  const buildHref = (patch: Record<string, string | undefined>) => {
    const merged: Record<string, string | undefined> = { cat, pricing, sort: sortKey === 'latest' ? undefined : sortKey, ...patch };
    const qs = new URLSearchParams();
    for (const [k, v] of Object.entries(merged)) if (v) qs.set(k, v);
    const s = qs.toString();
    return s ? `/tools?${s}` : '/tools';
  };

  const chip = (active: boolean) =>
    `rounded-full px-3.5 py-1.5 text-sm transition ${
      active
        ? 'bg-brand-600 font-medium text-white'
        : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 ring-1 ring-gray-200 dark:ring-gray-700 hover:text-brand-600 dark:hover:text-brand-400 hover:ring-brand-300'
    }`;

  const params: Record<string, string> = {};
  if (cat) params.cat = cat;
  if (pricing) params.pricing = pricing;
  if (sortKey !== 'latest') params.sort = sortKey;

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">全部工具</h1>
      <p className="mt-2 text-sm text-gray-500">共 {filtered.length} 款工具</p>

      {/* 分类 */}
      <div className="mt-6 flex flex-wrap items-center gap-2">
        <span className="w-10 text-xs text-gray-400">分类</span>
        <Link href={buildHref({ cat: undefined, page: undefined })} className={chip(!cat)}>全部</Link>
        {categories.map((c) => (
          <Link key={c.slug} href={buildHref({ cat: c.slug, page: undefined })} className={chip(cat === c.slug)}>
            {c.name}
          </Link>
        ))}
      </div>

      {/* 定价 */}
      <div className="mt-3 flex flex-wrap items-center gap-2">
        <span className="w-10 text-xs text-gray-400">定价</span>
        <Link href={buildHref({ pricing: undefined, page: undefined })} className={chip(!pricing)}>全部</Link>
        {PRICING_OPTIONS.map((p) => (
          <Link key={p} href={buildHref({ pricing: p, page: undefined })} className={chip(pricing === p)}>
            {p}
          </Link>
        ))}
      </div>

      {/* 排序 */}
      <div className="mt-3 flex flex-wrap items-center gap-2">
        <span className="w-10 text-xs text-gray-400">排序</span>
        {SORT_OPTIONS.map((s) => (
          <Link
            key={s.key}
            href={buildHref({ sort: s.key === 'latest' ? undefined : s.key, page: undefined })}
            className={chip(sortKey === s.key)}
          >
            {s.label}
          </Link>
        ))}
      </div>

      <div className="mt-8 space-y-6">
        {tools.length === 0 ? (
          <EmptyState title="没有符合条件的工具" hint="换个筛选条件试试。" />
        ) : (
          <>
            <ToolGrid tools={tools.slice(0, 8)} />
            {/* 信息流广告位 */}
            <AdSlot slot="tools-inline" tools={pickPromo(allTools, 'tools-inline', 1)} />
            {tools.length > 8 && <ToolGrid tools={tools.slice(8)} />}
          </>
        )}
      </div>

      <Pagination current={page} total={pages} basePath="/tools" params={params} />
    </div>
  );
}
