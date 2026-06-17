import type { Metadata } from 'next';
import Link from 'next/link';
import { Clock, Newspaper, Zap } from 'lucide-react';
import { getAllTools, getFlash, getHotNews, getNews, pickPromo } from '@/lib/api';
import AdSlot from '@/components/AdSlot';
import { formatCount, formatDate } from '@/lib/format';
import EmptyState from '@/components/EmptyState';
import FlashTimeline from '@/components/FlashTimeline';
import HotNewsWidget from '@/components/HotNewsWidget';
import Pagination from '@/components/Pagination';
import ToolLogo from '@/components/ToolLogo';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'AI 资讯',
  description: 'AI 行业新闻、趋势解读与工具动态。',
};

export default async function NewsPage({
  searchParams,
}: {
  searchParams: Promise<{ page?: string }>;
}) {
  const { page: pageParam } = await searchParams;
  const page = Math.max(1, Number(pageParam) || 1);
  const [{ items, pages }, flash, tools, hotNews] = await Promise.all([
    getNews(page, 10),
    getFlash(1, 6),
    getAllTools(),
    getHotNews(5),
  ]);
  const [headline, ...rest] = items;
  const hotTools = [...tools].sort((a, b) => b.monthlyVisits - a.monthlyVisits).slice(0, 5);

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">
        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-white">
          <Newspaper size={18} />
        </span>
        AI 资讯
      </h1>
      <p className="mt-2 text-sm text-gray-500">行业新闻、趋势解读与工具动态</p>

      <div className="mt-8 grid gap-8 lg:grid-cols-3">
        {/* 主栏：资讯列表 */}
        <div className="lg:col-span-2">
          {items.length === 0 ? (
            <EmptyState title="暂无资讯" />
          ) : (
            <div className="space-y-4">
              {/* 头条特写（仅第一页，封面图打底） */}
              {page === 1 && headline && (
                <Link
                  href={`/news/${headline.slug}`}
                  className="relative block overflow-hidden rounded-2xl bg-gray-900 text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                  {headline.cover ? (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img
                      src={headline.cover}
                      alt=""
                      aria-hidden
                      className="absolute inset-0 h-full w-full object-cover opacity-50"
                    />
                  ) : (
                    <div
                      aria-hidden
                      className="absolute -right-16 -top-24 h-72 w-72 rounded-full bg-brand-600/40 blur-[90px]"
                    />
                  )}
                  <div
                    aria-hidden
                    className="absolute inset-0 bg-gradient-to-t from-gray-950/90 via-gray-950/40 to-transparent"
                  />
                  <div className="relative p-7 pt-24 sm:p-9 sm:pt-32">
                    <span className="rounded-full bg-brand-500/30 px-2.5 py-1 text-[11px] font-medium text-brand-100 ring-1 ring-brand-400/40">
                      头条
                    </span>
                    <h2 className="mt-4 text-xl font-bold leading-8 sm:text-2xl">{headline.title}</h2>
                    <p className="mt-3 line-clamp-2 text-sm leading-6 text-white/75">{headline.digest}</p>
                    <div className="mt-4 flex items-center gap-2 text-xs text-white/50">
                      <time>{formatDate(headline.created)}</time>
                      {headline.readTime > 0 && (
                        <>
                          <span>·</span>
                          <span className="inline-flex items-center gap-1"><Clock size={12} />{headline.readTime} 分钟阅读</span>
                        </>
                      )}
                    </div>
                  </div>
                </Link>
              )}
              {/* 资讯信息流广告位 */}
              <AdSlot slot="news-inline" tools={pickPromo(tools, 'news-inline', 1)} />
              {(page === 1 ? rest : items).map((item) => (
                <Link
                  key={item.cid}
                  href={`/news/${item.slug}`}
                  className="flex gap-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-md sm:p-6"
                >
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2 text-xs text-gray-400">
                      <time>{formatDate(item.created)}</time>
                      {item.readTime > 0 && (
                        <>
                          <span>·</span>
                          <span className="inline-flex items-center gap-1"><Clock size={12} />{item.readTime} 分钟阅读</span>
                        </>
                      )}
                    </div>
                    <h2 className="mt-2 text-lg font-semibold leading-7 text-gray-900 dark:text-gray-100">{item.title}</h2>
                    <p className="mt-2 line-clamp-2 text-sm leading-6 text-gray-500">{item.digest}</p>
                  </div>
                  {item.cover && (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img
                      src={item.cover}
                      alt={item.title}
                      loading="lazy"
                      className="hidden h-24 w-40 shrink-0 rounded-xl bg-gray-100 dark:bg-gray-800 object-cover sm:block"
                    />
                  )}
                </Link>
              ))}
            </div>
          )}
          <Pagination current={page} total={pages} basePath="/news" />
        </div>

        {/* 侧栏：快讯时间线 + 热门工具 */}
        <aside className="space-y-6">
          <div className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div className="flex items-center justify-between">
              <h2 className="flex items-center gap-1.5 font-semibold text-gray-900 dark:text-gray-100">
                <Zap size={15} className="fill-brand-500 text-brand-500" />
                AI 快讯
              </h2>
              <Link href="/flash" className="text-xs text-brand-600 dark:text-brand-400 hover:underline">
                全部 →
              </Link>
            </div>
            <div className="mt-4">
              <FlashTimeline items={flash.items} compact />
            </div>
          </div>

          <HotNewsWidget items={hotNews} />

          {hotTools.length > 0 && (
            <div className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
              <h2 className="font-semibold text-gray-900 dark:text-gray-100">本周热门工具</h2>
              <ol className="mt-3 space-y-1">
                {hotTools.map((t, i) => (
                  <li key={t.cid}>
                    <Link
                      href={`/tool/${t.slug}`}
                      className="group flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-brand-50 dark:hover:bg-brand-900/30"
                    >
                      <span className="w-4 text-center font-display text-sm font-bold text-gray-300">{i + 1}</span>
                      <ToolLogo src={t.logo} name={t.title} size={32} />
                      <span className="min-w-0 flex-1 truncate text-sm font-medium text-gray-800 dark:text-gray-200 group-hover:text-brand-700 dark:group-hover:text-brand-300">
                        {t.title}
                      </span>
                      <span className="shrink-0 font-display text-xs tabular-nums text-gray-400">
                        {formatCount(t.monthlyVisits)}
                      </span>
                    </Link>
                  </li>
                ))}
              </ol>
            </div>
          )}
        </aside>
      </div>
    </div>
  );
}
