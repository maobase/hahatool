import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ChevronLeft, ChevronRight, Clock, Zap } from 'lucide-react';
import { getAllTools, getFlash, getHotNews, getNews, getNewsBySlug } from '@/lib/api';
import { formatCount, formatDate } from '@/lib/format';
import FlashTimeline from '@/components/FlashTimeline';
import HotNewsWidget from '@/components/HotNewsWidget';
import ToolLogo from '@/components/ToolLogo';
import TrackView from '@/components/TrackView';

export const revalidate = 60;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const item = await getNewsBySlug(slug);
  if (!item) return { title: '资讯详情' };
  const image = item.cover || undefined;
  return {
    title: item.title,
    description: item.digest,
    openGraph: {
      title: item.title,
      description: item.digest,
      type: 'article',
      ...(image && { images: [{ url: image }] }),
    },
    twitter: {
      card: image ? 'summary_large_image' : 'summary',
      title: item.title,
      description: item.digest,
      ...(image && { images: [image] }),
    },
  };
}

export default async function NewsDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const [item, list, flash, tools, hotNewsRaw] = await Promise.all([
    getNewsBySlug(slug),
    getNews(1, 50),
    getFlash(1, 6),
    getAllTools(),
    getHotNews(6),
  ]);
  if (!item || !item.contentHtml) notFound();
  const hotNews = hotNewsRaw.filter((n) => n.cid !== item.cid).slice(0, 5);

  // 结构化数据：NewsArticle + 面包屑（资讯 SEO，对齐工具详情做法）
  const siteUrl = (process.env.NEXT_PUBLIC_SITE_URL ?? 'http://localhost:3000').replace(/\/+$/, '');
  const isoDate = new Date(item.created * 1000).toISOString();
  const newsLd = {
    '@context': 'https://schema.org',
    '@type': 'NewsArticle',
    headline: item.title,
    datePublished: isoDate,
    dateModified: isoDate,
    ...(item.cover && { image: [item.cover] }),
    description: item.digest,
    mainEntityOfPage: `${siteUrl}/news/${item.slug}`,
    author: { '@type': 'Organization', name: 'HahaTool' },
    publisher: { '@type': 'Organization', name: 'HahaTool' },
  };
  const breadcrumbLd = {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
      { '@type': 'ListItem', position: 1, name: '首页', item: `${siteUrl}/` },
      { '@type': 'ListItem', position: 2, name: 'AI 资讯', item: `${siteUrl}/news` },
      { '@type': 'ListItem', position: 3, name: item.title, item: `${siteUrl}/news/${item.slug}` },
    ],
  };

  // 上一篇/下一篇（列表按时间倒序）
  const idx = list.items.findIndex((n) => n.slug === slug);
  const newer = idx > 0 ? list.items[idx - 1] : null;
  const older = idx >= 0 && idx < list.items.length - 1 ? list.items[idx + 1] : null;
  const related = list.items.filter((n) => n.slug !== slug).slice(0, 3);
  const hotTools = [...tools].sort((a, b) => b.monthlyVisits - a.monthlyVisits).slice(0, 5);

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <TrackView cid={item.cid} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(newsLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbLd) }} />
      <Link href="/news" className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-600 dark:hover:text-brand-400">
        <ChevronLeft size={16} />
        返回资讯列表
      </Link>

      <div className="mt-6 grid gap-8 lg:grid-cols-3">
        {/* 主栏：文章 */}
        <div className="lg:col-span-2">
          <article className="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            {item.cover && (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={item.cover} alt={`${item.title} 封面`} className="aspect-[2/1] w-full bg-gray-100 dark:bg-gray-800 object-cover" />
            )}
            <div className="p-6 sm:p-10">
              <div className="flex items-center gap-2 text-sm text-gray-400">
                <time>{formatDate(item.created)}</time>
                {item.readTime > 0 && (
                  <>
                    <span>·</span>
                    <span className="inline-flex items-center gap-1"><Clock size={13} />{item.readTime} 分钟阅读</span>
                  </>
                )}
              </div>
              <h1 className="mt-3 text-2xl font-bold leading-9 text-gray-900 dark:text-gray-100 sm:text-3xl">{item.title}</h1>
              <div
                className="prose prose-gray dark:prose-invert mt-6 max-w-none prose-a:text-brand-600"
                dangerouslySetInnerHTML={{ __html: item.contentHtml }}
              />
            </div>
          </article>

          {/* 上一篇 / 下一篇 */}
          {(newer || older) && (
            <nav className="mt-6 grid gap-3 sm:grid-cols-2" aria-label="上下篇导航">
              {newer ? (
                <Link
                  href={`/news/${newer.slug}`}
                  className="group rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 transition hover:border-brand-300"
                >
                  <span className="flex items-center gap-1 text-xs text-gray-400">
                    <ChevronLeft size={13} />
                    上一篇
                  </span>
                  <span className="mt-1 line-clamp-1 text-sm font-medium text-gray-800 dark:text-gray-200 group-hover:text-brand-700 dark:group-hover:text-brand-300">
                    {newer.title}
                  </span>
                </Link>
              ) : (
                <span aria-hidden />
              )}
              {older && (
                <Link
                  href={`/news/${older.slug}`}
                  className="group rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 text-right transition hover:border-brand-300"
                >
                  <span className="flex items-center justify-end gap-1 text-xs text-gray-400">
                    下一篇
                    <ChevronRight size={13} />
                  </span>
                  <span className="mt-1 line-clamp-1 text-sm font-medium text-gray-800 dark:text-gray-200 group-hover:text-brand-700 dark:group-hover:text-brand-300">
                    {older.title}
                  </span>
                </Link>
              )}
            </nav>
          )}

          {/* 相关资讯 */}
          {related.length > 0 && (
            <section className="mt-10">
              <h2 className="text-lg font-bold text-gray-900 dark:text-gray-100">相关资讯</h2>
              <div className="mt-4 space-y-3">
                {related.map((n) => (
                  <Link
                    key={n.cid}
                    href={`/news/${n.slug}`}
                    className="block rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 transition hover:border-brand-300 hover:shadow-sm"
                  >
                    <time className="text-xs text-gray-400">{formatDate(n.created)}</time>
                    <p className="mt-1 font-medium text-gray-900 dark:text-gray-100">{n.title}</p>
                  </Link>
                ))}
              </div>
            </section>
          )}
        </div>

        {/* 侧栏：快讯 + 热门工具（对齐资讯列表页侧栏） */}
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
