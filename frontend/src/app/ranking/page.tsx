import type { Metadata } from 'next';
import Link from 'next/link';
import { ArrowUpRight } from 'lucide-react';
import { getAllTools, getToolCategories, pickPromo } from '@/lib/api';
import { formatCount, formatDate } from '@/lib/format';
import EmptyState from '@/components/EmptyState';
import GrowthBadge from '@/components/GrowthBadge';
import PricingBadge from '@/components/PricingBadge';
import AdSlot from '@/components/AdSlot';
import RatingStars from '@/components/RatingStars';
import ToolLogo from '@/components/ToolLogo';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'AI 工具排行榜',
  description: '按月访问量、收藏数、增长速度排序的 AI 工具排行榜。',
};

const TABS = [
  { key: 'visits', label: '流量榜', desc: '按月访问量排序' },
  { key: 'likes', label: '收藏榜', desc: '按用户收藏数排序' },
  { key: 'growth', label: '增长榜', desc: '按月环比增速排序' },
  { key: 'new', label: '新品榜', desc: '按收录时间排序' },
] as const;

/** 前三名金/银/铜徽章，其余为普通序号（不使用 emoji 图标） */
const RANK_BADGE = [
  'bg-amber-400 text-amber-950',
  'bg-gray-300 text-gray-700 dark:text-gray-300',
  'bg-orange-300 text-orange-950',
];

export default async function RankingPage({
  searchParams,
}: {
  searchParams: Promise<{ by?: string; cat?: string }>;
}) {
  const { by, cat } = await searchParams;
  const sortBy = (TABS.find((t) => t.key === by)?.key ?? 'visits') as (typeof TABS)[number]['key'];

  const [allTools, categories] = await Promise.all([getAllTools(), getToolCategories()]);
  const promo = pickPromo(allTools, 'ranking-top', 1);

  const tools = (cat ? allTools.filter((t) => t.categories.some((c) => c.slug === cat)) : allTools).sort((a, b) => {
    switch (sortBy) {
      case 'likes':
        return b.likes - a.likes;
      case 'growth':
        return b.growth - a.growth;
      case 'new':
        return b.created - a.created;
      default:
        return b.monthlyVisits - a.monthlyVisits;
    }
  });

  const active = TABS.find((t) => t.key === sortBy)!;
  const podium = tools.slice(0, 3);
  const rest = tools.slice(3);

  const href = (patch: { by?: string; cat?: string }) => {
    const qs = new URLSearchParams();
    const merged = { by: sortBy === 'visits' ? undefined : sortBy, cat, ...patch };
    if (merged.by) qs.set('by', merged.by);
    if (merged.cat) qs.set('cat', merged.cat);
    const s = qs.toString();
    return s ? `/ranking?${s}` : '/ranking';
  };

  const PODIUM_RING = ['ring-2 ring-amber-300', 'ring-1 ring-gray-300', 'ring-1 ring-orange-300'];

  return (
    <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6">
      <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">AI 工具排行榜</h1>
      <p className="mt-2 text-sm text-gray-500">{active.desc} · 数据由运营团队维护，仅供参考</p>

      <div className="mt-6 flex flex-wrap gap-2" role="tablist" aria-label="榜单切换">
        {TABS.map((tab) => (
          <Link
            key={tab.key}
            href={href({ by: tab.key === 'visits' ? undefined : tab.key })}
            role="tab"
            aria-selected={sortBy === tab.key}
            className={`rounded-full px-4 py-1.5 text-sm transition ${
              sortBy === tab.key
                ? 'bg-brand-600 font-medium text-white'
                : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 ring-1 ring-gray-200 dark:ring-gray-700 hover:text-brand-600 dark:hover:text-brand-400 hover:ring-brand-300'
            }`}
          >
            {tab.label}
          </Link>
        ))}
      </div>

      {/* 分类筛选 */}
      <div className="mt-3 flex flex-wrap gap-2">
        <Link
          href={href({ cat: undefined })}
          className={`rounded-full px-3 py-1 text-xs transition ${!cat ? 'bg-gray-900 dark:bg-brand-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-500 ring-1 ring-gray-200 dark:ring-gray-700 hover:text-brand-600 dark:hover:text-brand-400'}`}
        >
          全部分类
        </Link>
        {categories.map((c) => (
          <Link
            key={c.slug}
            href={href({ cat: c.slug })}
            className={`rounded-full px-3 py-1 text-xs transition ${cat === c.slug ? 'bg-gray-900 dark:bg-brand-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-500 ring-1 ring-gray-200 dark:ring-gray-700 hover:text-brand-600 dark:hover:text-brand-400'}`}
          >
            {c.name}
          </Link>
        ))}
      </div>

      {/* 榜单顶部运营位 */}
      <div className="mt-6">
        <AdSlot slot="ranking-top" tools={promo} />
      </div>

      {tools.length === 0 ? (
        <div className="mt-8"><EmptyState title="该分类下暂无工具" /></div>
      ) : (
        <>
          {/* 前三名领奖台 */}
          <div className="mt-8 grid gap-4 sm:grid-cols-3">
            {podium.map((tool, i) => (
              <div
                key={tool.cid}
                className={`relative rounded-2xl bg-white dark:bg-gray-900 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md ${PODIUM_RING[i]}`}
              >
                <Link href={`/tool/${tool.slug}`} className="absolute inset-0 rounded-2xl" aria-label={`查看 ${tool.title}`} />
                <span
                  className={`absolute -top-3 left-4 rounded-full px-2.5 py-1 font-display text-xs font-bold ${RANK_BADGE[i]}`}
                >
                  NO.{i + 1}
                </span>
                <div className="mt-2 flex items-center gap-3">
                  <ToolLogo src={tool.logo} name={tool.title} size={52} />
                  <div className="min-w-0">
                    <p className="truncate font-semibold text-gray-900 dark:text-gray-100">{tool.title}</p>
                    <RatingStars rating={tool.rating} size={12} />
                  </div>
                </div>
                <p className="mt-3 line-clamp-2 min-h-10 text-sm leading-5 text-gray-500">{tool.tagline}</p>
                <div className="mt-3 flex items-center justify-between border-t border-gray-100 dark:border-gray-800 pt-3">
                  <span className="font-display text-lg font-bold tabular-nums text-gray-900 dark:text-gray-100">
                    {formatCount(sortBy === 'likes' ? tool.likes : tool.monthlyVisits)}
                    <span className="ml-1 text-xs font-normal text-gray-400">
                      {sortBy === 'likes' ? '收藏' : '月访问'}
                    </span>
                  </span>
                  <GrowthBadge growth={tool.growth} />
                </div>
              </div>
            ))}
          </div>
          {/* 列头（桌面） */}
          <div className="mt-8 hidden items-center gap-4 px-4 pb-2 text-xs font-medium text-gray-400 sm:flex">
            <span className="w-9 text-center">排名</span>
            <span className="w-11" />
            <span className="flex-1">工具</span>
            <span className="w-16 text-right">增长</span>
            <span className="w-20 text-right">月访问</span>
            <span className="w-16 text-right">收藏</span>
            <span className="w-11" />
          </div>
          <ol className="space-y-2 max-sm:mt-8">
            {rest.map((tool, i) => (
              <li
                key={tool.cid}
                className="relative flex items-center gap-4 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm transition hover:border-brand-300 hover:shadow-md"
              >
                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-display text-sm font-bold tabular-nums text-gray-400">
                  {i + 4}
                </span>
                <ToolLogo src={tool.logo} name={tool.title} size={44} />
                <div className="min-w-0 flex-1">
                  <span className="flex items-center gap-2">
                    <Link href={`/tool/${tool.slug}`} className="font-semibold text-gray-900 dark:text-gray-100 hover:text-brand-700 dark:hover:text-brand-300">
                      <span className="absolute inset-0" aria-hidden />
                      {tool.title}
                    </Link>
                    <PricingBadge pricing={tool.pricing} />
                    {sortBy === 'new' && (
                      <span className="text-xs text-gray-400">{formatDate(tool.created)} 收录</span>
                    )}
                  </span>
                  <p className="mt-0.5 line-clamp-1 text-sm text-gray-500">{tool.tagline}</p>
                </div>
                <div className="hidden w-16 justify-end sm:flex">
                  <GrowthBadge growth={tool.growth} />
                </div>
                <div className="hidden w-20 text-right tabular-nums sm:block">
                  <p className="font-display font-semibold text-gray-900 dark:text-gray-100">{formatCount(tool.monthlyVisits)}</p>
                </div>
                <div className="hidden w-16 text-right tabular-nums sm:block">
                  <p className="font-display font-semibold text-gray-900 dark:text-gray-100">{formatCount(tool.likes)}</p>
                </div>
                <a
                  href={tool.url}
                  target="_blank"
                  rel="noopener noreferrer nofollow"
                  aria-label={`访问 ${tool.title} 官网`}
                  className="relative z-10 flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-brand-50 dark:hover:bg-brand-900/30 hover:text-brand-600 dark:hover:text-brand-400"
                >
                  <ArrowUpRight size={18} />
                </a>
              </li>
            ))}
          </ol>
        </>
      )}
    </div>
  );
}
