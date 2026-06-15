import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { Bookmark, CalendarDays, Eye, Globe, TrendingUp } from 'lucide-react';
import { getAllTools, getToolBySlug, pickPromo } from '@/lib/api';
import AdSlot from '@/components/AdSlot';
import RadarChart from '@/components/RadarChart';
import { domainOf, formatCount, formatDate } from '@/lib/format';
import Comments from '@/components/Comments';
import FaqList from '@/components/FaqList';
import FavoriteButton from '@/components/FavoriteButton';
import GrowthBadge from '@/components/GrowthBadge';
import PricingBadge from '@/components/PricingBadge';
import RatingStars from '@/components/RatingStars';
import ToolLogo from '@/components/ToolLogo';
import ToolScreenshot from '@/components/ToolScreenshot';
import TrackView from '@/components/TrackView';
import TrafficPanel from '@/components/TrafficPanel';
import VisitButton from '@/components/VisitButton';

export const revalidate = 60;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const tool = await getToolBySlug(slug);
  if (!tool) return { title: '工具详情' };
  const title = `${tool.title} - ${tool.tagline}`.slice(0, 60);
  const image = tool.screenshot || `https://s0.wp.com/mshots/v1/${encodeURIComponent(tool.url)}?w=1200`;
  return {
    title,
    description: tool.tagline,
    openGraph: { title, description: tool.tagline, type: 'website', images: [{ url: image }] },
    twitter: { card: 'summary_large_image', title, description: tool.tagline, images: [image] },
  };
}

export default async function ToolDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const tool = await getToolBySlug(slug);
  if (!tool) notFound();

  const allTools = await getAllTools();
  const sidePromo = pickPromo(allTools, 'detail-side', 2, tool.cid);
  const mainCategory = tool.categories.find((c) => c.slug !== 'ai-news');
  // 替代品：同分类按月访问量排序
  const alternatives = allTools
    .filter((t) => t.cid !== tool.cid && mainCategory && t.categories.some((c) => c.slug === mainCategory.slug))
    .sort((a, b) => b.monthlyVisits - a.monthlyVisits)
    .slice(0, 5);

  // SEO：SoftwareApplication 结构化数据
  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'SoftwareApplication',
    name: tool.title,
    description: tool.tagline,
    url: tool.url,
    applicationCategory: mainCategory?.name,
    offers: { '@type': 'Offer', price: tool.pricing === '免费' ? '0' : undefined, description: tool.pricing },
    ...(tool.rating > 0 && {
      aggregateRating: { '@type': 'AggregateRating', ratingValue: tool.rating, bestRating: 5, ratingCount: Math.max(1, tool.likes) },
    }),
  };

  // SEO：面包屑结构化数据
  const siteUrl = (process.env.NEXT_PUBLIC_SITE_URL ?? 'http://localhost:3000').replace(/\/+$/, '');
  const breadcrumbLd = {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
      { '@type': 'ListItem', position: 1, name: '首页', item: `${siteUrl}/` },
      ...(mainCategory ? [{ '@type': 'ListItem', position: 2, name: mainCategory.name, item: `${siteUrl}/category/${mainCategory.slug}` }] : []),
      { '@type': 'ListItem', position: mainCategory ? 3 : 2, name: tool.title, item: `${siteUrl}/tool/${tool.slug}` },
    ],
  };

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      {/* 浏览量上报（每会话一次） */}
      <TrackView cid={tool.cid} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbLd) }} />
      {/* 面包屑 */}
      <nav className="text-sm text-gray-400" aria-label="面包屑">
        <Link href="/" className="hover:text-brand-600 dark:hover:text-brand-400">首页</Link>
        <span className="mx-1.5">/</span>
        {mainCategory && (
          <>
            <Link href={`/category/${mainCategory.slug}`} className="hover:text-brand-600 dark:hover:text-brand-400">{mainCategory.name}</Link>
            <span className="mx-1.5">/</span>
          </>
        )}
        <span className="text-gray-600 dark:text-gray-400">{tool.title}</span>
      </nav>

      <div className="mt-6 grid gap-8 lg:grid-cols-3">
        {/* 主栏 */}
        <div className="lg:col-span-2">
          <div className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm sm:p-8">
            <div className="flex flex-col gap-5 sm:flex-row sm:items-center">
              <ToolLogo src={tool.logo} name={tool.title} size={72} className="ring-1 ring-gray-100 dark:ring-gray-800" />
              <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-2">
                  <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">{tool.title}</h1>
                  <GrowthBadge growth={tool.growth} />
                </div>
                <div className="mt-1.5">
                  <RatingStars rating={tool.rating} size={15} />
                </div>
                <p className="mt-2 text-gray-600 dark:text-gray-400">{tool.tagline}</p>
                {tool.tags.length > 0 && (
                  <p className="mt-2 flex flex-wrap gap-1.5">
                    {tool.tags.map((t) => (
                      <Link
                        key={t.slug}
                        href={`/tag/${t.slug}`}
                        className="rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-xs text-gray-600 dark:text-gray-400 transition hover:bg-brand-100 dark:hover:bg-brand-900/40 hover:text-brand-700 dark:hover:text-brand-300"
                      >
                        # {t.name}
                      </Link>
                    ))}
                  </p>
                )}
              </div>
              <div className="flex shrink-0 items-center gap-2">
                <FavoriteButton cid={tool.cid} size="lg" />
                <VisitButton cid={tool.cid} url={tool.url} />
              </div>
            </div>

            <div className="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 border-t border-gray-100 dark:border-gray-800 pt-5 text-sm text-gray-500">
              <span className="flex items-center gap-1.5 tabular-nums" title="收藏数">
                <Bookmark size={15} className="text-brand-500" />
                {formatCount(tool.likes)} 收藏
              </span>
              <span className="flex items-center gap-1.5 tabular-nums" title="月访问量">
                <TrendingUp size={15} className="text-emerald-500" />
                月访问 {formatCount(tool.monthlyVisits)}
              </span>
              <span className="flex items-center gap-1.5" title="收录时间">
                <CalendarDays size={15} className="text-gray-400" />
                {formatDate(tool.created)} 收录
              </span>
              {tool.views > 0 && (
                <span className="flex items-center gap-1.5 tabular-nums" title="站内真实浏览量">
                  <Eye size={15} className="text-sky-500" />
                  {formatCount(tool.views)} 浏览
                </span>
              )}
              <PricingBadge pricing={tool.pricing} />
            </div>
          </div>

          {/* 官网截图 */}
          <div className="mt-8">
            <ToolScreenshot url={tool.url} screenshot={tool.screenshot} name={tool.title} logo={tool.logo} />
          </div>

          {/* 流量分析 */}
          <TrafficPanel visitsHistory={tool.visitsHistory} regions={tool.regions} name={tool.title} />

          {tool.contentHtml && (
            <article
              className="prose prose-gray dark:prose-invert mt-8 max-w-none rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm prose-headings:scroll-mt-20 prose-a:text-brand-600 sm:p-8"
              dangerouslySetInnerHTML={{ __html: tool.contentHtml }}
            />
          )}

          {/* 常见问题 */}
          <FaqList faq={tool.faq} name={tool.title} />

          {/* 评论区 */}
          <Comments postId={tool.cid} />

          {/* 底部广告位 */}
          <div className="mt-8">
            <AdSlot slot="detail-bottom" tools={pickPromo(allTools, 'detail-bottom', 1, tool.cid)} />
          </div>
        </div>

        {/* 侧栏 */}
        <aside className="space-y-6">
          <div className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
            <h2 className="font-semibold text-gray-900 dark:text-gray-100">工具信息</h2>
            <dl className="mt-4 space-y-3 text-sm">
              <div className="flex items-center justify-between gap-3">
                <dt className="flex items-center gap-1.5 text-gray-500">
                  <Globe size={14} />
                  官网
                </dt>
                <dd className="truncate">
                  <a
                    href={tool.url}
                    target="_blank"
                    rel="noopener noreferrer nofollow"
                    className="font-medium text-brand-600 dark:text-brand-400 hover:underline"
                  >
                    {domainOf(tool.url)}
                  </a>
                </dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-gray-500">评分</dt>
                <dd>
                  {tool.rating ? <RatingStars rating={tool.rating} /> : <span className="text-gray-400">—</span>}
                </dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-gray-500">定价模式</dt>
                <dd className="font-medium text-gray-800 dark:text-gray-200">{tool.pricing}</dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-gray-500">月访问量</dt>
                <dd className="font-medium tabular-nums text-gray-800 dark:text-gray-200">{formatCount(tool.monthlyVisits)}</dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-gray-500">月增长</dt>
                <dd>
                  {tool.growth ? <GrowthBadge growth={tool.growth} /> : <span className="text-gray-400">—</span>}
                </dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-gray-500">收藏数</dt>
                <dd className="font-medium tabular-nums text-gray-800 dark:text-gray-200">{formatCount(tool.likes)}</dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-gray-500" title="本站真实统计">站内浏览</dt>
                <dd className="font-medium tabular-nums text-gray-800 dark:text-gray-200">{formatCount(tool.views)}</dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-gray-500" title="本站真实统计">官网直达</dt>
                <dd className="font-medium tabular-nums text-gray-800 dark:text-gray-200">{formatCount(tool.clicks)}</dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-gray-500">分类</dt>
                <dd className="flex flex-wrap justify-end gap-1.5">
                  {tool.categories
                    .filter((c) => c.slug !== 'ai-news')
                    .map((c) => (
                      <Link
                        key={c.slug}
                        href={`/category/${c.slug}`}
                        className="rounded-full bg-brand-50 dark:bg-brand-900/30 px-2.5 py-0.5 text-xs text-brand-700 dark:text-brand-300 hover:bg-brand-100 dark:hover:bg-brand-900/40"
                      >
                        {c.name}
                      </Link>
                    ))}
                </dd>
              </div>
            </dl>
          </div>

          {/* 能力雷达 */}
          {tool.scores.length >= 3 && (
            <div className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
              <div className="flex items-center justify-between">
                <h2 className="font-semibold text-gray-900 dark:text-gray-100">能力雷达</h2>
                <Link href={`/compare?a=${tool.slug}`} className="text-xs text-brand-600 dark:text-brand-400 hover:underline">
                  发起 PK →
                </Link>
              </div>
              <RadarChart series={[{ name: tool.title, color: 'rgb(var(--brand-600))', scores: tool.scores }]} size={280} />
            </div>
          )}

          {/* 侧栏推广位 */}
          <AdSlot slot="detail-side" tools={sidePromo} variant="side" />

          {/* 替代品 */}
          {alternatives.length > 0 && (
            <div className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
              <h2 className="font-semibold text-gray-900 dark:text-gray-100">
                {tool.title} 的替代品
                <span className="ml-1 text-xs font-normal text-gray-400">按流量排序</span>
              </h2>
              <ol className="mt-4 space-y-1">
                {alternatives.map((t, i) => (
                  <li key={t.cid} className="group relative flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-brand-50 dark:hover:bg-brand-900/30">
                    <Link
                      href={`/tool/${t.slug}`}
                      className="absolute inset-0 rounded-xl"
                      aria-label={`查看 ${t.title} 详情`}
                    />
                    <span className="w-5 text-center font-display text-sm font-bold text-gray-300 group-hover:text-brand-400">
                      {i + 1}
                    </span>
                    <ToolLogo src={t.logo} name={t.title} size={36} />
                    <span className="min-w-0 flex-1">
                      <span className="block truncate text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-brand-700 dark:group-hover:text-brand-300">
                        {t.title}
                      </span>
                      <span className="block truncate text-xs text-gray-400">
                        月访问 {formatCount(t.monthlyVisits)}
                      </span>
                    </span>
                    <Link
                      href={`/compare?a=${tool.slug}&b=${t.slug}`}
                      className="relative z-10 shrink-0 rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-[11px] font-medium text-gray-500 transition hover:bg-brand-600 hover:text-white"
                      aria-label={`${tool.title} 与 ${t.title} 对比`}
                    >
                      PK
                    </Link>
                  </li>
                ))}
              </ol>
              {mainCategory && (
                <Link
                  href={`/category/${mainCategory.slug}`}
                  className="mt-3 block text-center text-sm text-brand-600 dark:text-brand-400 hover:underline"
                >
                  查看{mainCategory.name}全部工具 →
                </Link>
              )}
            </div>
          )}
        </aside>
      </div>
    </div>
  );
}
