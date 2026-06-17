import Link from 'next/link';
import { ArrowRight, Clock, Flame, Layers, Megaphone, Sparkles } from 'lucide-react';
import { getAllTools, getFlash, getNews, getPrompts, getTags, getToolCategories, getTopics, pickPromo } from '@/lib/api';
import AdSlot from '@/components/AdSlot';
import NewsTicker from '@/components/NewsTicker';
import PromptCard from '@/components/PromptCard';
import { formatDate } from '@/lib/format';
import EmptyState from '@/components/EmptyState';
import ToolCard from '@/components/ToolCard';
import SectionHeader from '@/components/SectionHeader';
import SearchBox from '@/components/SearchBox';
import ToolGrid from '@/components/ToolGrid';
import ToolLogo from '@/components/ToolLogo';

export const revalidate = 60;

const HOT_KEYWORDS = ['AI写作', 'AI绘画', '视频生成', 'AI编程', '数字人', 'AI音乐'];

/** 本周新增：7 天内收录的工具数 */
function weeklyNew(created: number[]): number {
  const weekAgo = Date.now() / 1000 - 7 * 86400;
  return created.filter((t) => t > weekAgo).length;
}

export default async function HomePage() {
  const [tools, categories, news, tags, flash, prompts, topics] = await Promise.all([
    getAllTools(),
    getToolCategories(),
    getNews(1, 5),
    getTags(),
    getFlash(1, 6),
    getPrompts(),
    getTopics(),
  ]);
  const midPromo = pickPromo(tools, 'home-mid', 1);
  const hotPrompts = [...prompts].sort((a, b) => b.likes - a.likes).slice(0, 3);
  const homeTopics = topics.slice(0, 3);

  const banners = tools.filter((t) => t.banner).slice(0, 2);
  const featured = tools.filter((t) => t.featured).slice(0, 8);
  const latest = [...tools].sort((a, b) => b.created - a.created).slice(0, 8);
  const trending = [...tools].sort((a, b) => b.growth - a.growth).slice(0, 4);

  const stats = [
    { value: tools.length, label: '收录工具' },
    { value: categories.length, label: '工具分类' },
    { value: weeklyNew(tools.map((t) => t.created)), label: '本周新增' },
  ];

  return (
    <>
      {/* ===== 深色 Hero ===== */}
      <section className="relative overflow-hidden bg-[#0d0a1f] text-white">
        {/* 网格纹理 + 光晕 */}
        <div
          aria-hidden
          className="absolute inset-0 opacity-[0.07]"
          style={{
            backgroundImage:
              'linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px)',
            backgroundSize: '56px 56px',
          }}
        />
        <div
          aria-hidden
          className="absolute left-1/2 top-0 h-[460px] w-[900px] -translate-x-1/2 -translate-y-1/3 rounded-full bg-brand-600/40 blur-[140px]"
        />
        <div className="relative mx-auto max-w-7xl px-4 pb-14 pt-16 sm:px-6 sm:pb-20 sm:pt-24">
          <div className="mx-auto max-w-3xl text-center">
            <p className="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs text-brand-200">
              <Sparkles size={12} />
              全球 AI 工具中文导航 · 每日更新
            </p>
            <h1 className="mt-6 text-4xl font-extrabold leading-tight tracking-tight sm:text-6xl">
              发现最好用的
              <span className="relative mx-2 inline-block text-brand-300">
                AI 工具
                <svg
                  aria-hidden
                  viewBox="0 0 120 8"
                  className="absolute -bottom-1.5 left-0 w-full text-brand-500"
                  preserveAspectRatio="none"
                >
                  <path d="M2 6 Q 30 1 60 4 T 118 3" stroke="currentColor" strokeWidth="3" fill="none" strokeLinecap="round" />
                </svg>
              </span>
            </h1>
            <p className="mt-5 text-sm leading-7 text-gray-400 sm:text-base">
              收录全球优秀 AI 产品，附流量数据、定价与真实点评，帮你少踩坑、快上手。
            </p>
            <div className="mx-auto mt-8 max-w-2xl">
              <SearchBox variant="hero" placeholder="搜索 AI 工具，例如：视频生成、写作助手…" />
            </div>
            <div className="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs text-gray-400">
              <span>热门：</span>
              {HOT_KEYWORDS.map((kw) => (
                <Link
                  key={kw}
                  href={`/search?q=${encodeURIComponent(kw)}`}
                  className="rounded-full border border-white/10 bg-white/5 px-3 py-1 transition hover:border-brand-400/50 hover:text-brand-200"
                >
                  {kw}
                </Link>
              ))}
            </div>
          </div>
          {/* 统计行 */}
          <dl className="mx-auto mt-12 grid max-w-2xl grid-cols-3 divide-x divide-white/10 rounded-2xl border border-white/10 bg-white/[0.04] py-5 backdrop-blur">
            {stats.map((s) => (
              <div key={s.label} className="px-4 text-center">
                <dd className="font-display text-3xl font-bold tabular-nums text-white sm:text-4xl">
                  {s.value}
                  {s.value > 0 && <span className="text-brand-400">+</span>}
                </dd>
                <dt className="mt-1 text-xs text-gray-400">{s.label}</dt>
              </div>
            ))}
          </dl>
        </div>
      </section>

      {/* ===== 快讯跑马灯 ===== */}
      <NewsTicker items={flash.items} />

      {/* ===== sticky 分类导航条 ===== */}
      <nav
        aria-label="分类快捷导航"
        className="sticky top-16 z-40 border-b border-gray-200 dark:border-gray-800 bg-white/95 dark:bg-gray-950/95 backdrop-blur"
      >
        <div className="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-2.5 sm:px-6 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
          <a
            href="#featured"
            className="shrink-0 rounded-full bg-gray-900 dark:bg-brand-600 px-4 py-1.5 text-sm font-medium text-white"
          >
            精选
          </a>
          <a
            href="#trending"
            className="inline-flex shrink-0 items-center gap-1 rounded-full px-4 py-1.5 text-sm text-gray-600 dark:text-gray-400 transition hover:bg-gray-100 dark:hover:bg-gray-800"
          >
            <Flame size={14} className="text-orange-500" />
            增长最快
          </a>
          {categories.map((c) => (
            <a
              key={c.slug}
              href={`#cat-${c.slug}`}
              className="shrink-0 rounded-full px-4 py-1.5 text-sm text-gray-600 dark:text-gray-400 transition hover:bg-gray-100 dark:hover:bg-gray-800"
            >
              {c.name}
            </a>
          ))}
        </div>
      </nav>

      <div className="mx-auto max-w-7xl space-y-14 px-4 py-10 sm:px-6">
        {tools.length === 0 ? (
          <EmptyState title="还没有任何工具数据" />
        ) : (
          <>
            {/* Banner 推广位 */}
            {banners.length > 0 && (
              <section aria-label="推广位">
                <div className="grid gap-4 md:grid-cols-2">
                  {banners.map((tool) => (
                    <div
                      key={tool.cid}
                      className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-brand-700 to-brand-900 p-6 text-white shadow-md sm:p-8"
                    >
                      <span className="absolute right-4 top-4 flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-1 text-[11px]">
                        <Megaphone size={11} />
                        推广
                      </span>
                      <div className="flex items-center gap-4">
                        <ToolLogo src={tool.logo} name={tool.title} size={56} className="ring-2 ring-white/30" />
                        <div>
                          <h3 className="text-xl font-bold">{tool.title}</h3>
                          <p className="mt-1 line-clamp-2 max-w-md text-sm text-white/80">{tool.tagline}</p>
                        </div>
                      </div>
                      <div className="mt-5 flex items-center gap-3">
                        <a
                          href={tool.url}
                          target="_blank"
                          rel="noopener noreferrer nofollow"
                          className="inline-flex items-center gap-1 rounded-xl bg-white dark:bg-gray-900 px-4 py-2 text-sm font-semibold text-brand-700 dark:text-brand-300 transition hover:bg-brand-50 dark:hover:bg-brand-900/30"
                        >
                          立即体验
                          <ArrowRight size={15} />
                        </a>
                        <Link
                          href={`/tool/${tool.slug}`}
                          className="rounded-xl px-4 py-2 text-sm text-white/90 ring-1 ring-white/40 transition hover:bg-white/10"
                        >
                          查看详情
                        </Link>
                      </div>
                    </div>
                  ))}
                </div>
              </section>
            )}

            {/* 编辑精选 */}
            {featured.length > 0 && (
              <section id="featured" className="scroll-mt-32">
                <SectionHeader title="编辑精选" subtitle="运营团队为你挑选的优质 AI 工具" moreHref="/tools" />
                <ToolGrid tools={featured} />
              </section>
            )}

            {/* 增长最快 */}
            {trending.length > 0 && (
              <section id="trending" className="scroll-mt-32">
                <SectionHeader
                  title="增长最快"
                  subtitle="本月访问量增速最高的黑马工具"
                  moreHref="/ranking?by=growth"
                  moreText="查看增长榜"
                />
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                  {trending.map((tool, i) => (
                    <div key={tool.cid} className="relative">
                      <span className="absolute -top-2 left-4 z-10 inline-flex items-center gap-1 rounded-full bg-orange-500 px-2 py-0.5 text-[11px] font-bold text-white shadow">
                        <Flame size={11} />
                        NO.{i + 1}
                      </span>
                      <ToolCard tool={tool} />
                    </div>
                  ))}
                </div>
              </section>
            )}

            {/* 精选专题 */}
            {homeTopics.length > 0 && (
              <section>
                <SectionHeader title="精选专题" subtitle="按场景策划的 AI 工具合集" moreHref="/topics" moreText="全部专题" />
                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                  {homeTopics.map((t) => (
                    <Link
                      key={t.slug}
                      href={`/topic/${t.slug}`}
                      className="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm transition hover:-translate-y-1 hover:border-brand-300 hover:shadow-md"
                    >
                      <div
                        className="h-32 bg-gradient-to-br from-brand-500 to-brand-800 bg-cover bg-center"
                        style={t.cover ? { backgroundImage: `url(${t.cover})` } : undefined}
                      />
                      <div className="flex flex-1 flex-col gap-1.5 p-5">
                        <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100 group-hover:text-brand-700 dark:group-hover:text-brand-300">{t.name}</h3>
                        <p className="line-clamp-2 text-sm leading-6 text-gray-500">{t.description}</p>
                        <span className="mt-auto inline-flex items-center gap-1 pt-2 text-xs text-gray-400">
                          <Layers size={12} />
                          {t.count} 个工具
                        </span>
                      </div>
                    </Link>
                  ))}
                </div>
              </section>
            )}

            {/* 最新收录 */}
            <section className="scroll-mt-32">
              <SectionHeader title="最新收录" subtitle="刚刚加入 HahaTool 的新工具" moreHref="/tools" />
              <ToolGrid tools={latest} />
            </section>

            {/* 分类板块（中部插入运营位横幅） */}
            {categories.map((cat, idx) => {
              const catTools = tools.filter((t) => t.categories.some((c) => c.slug === cat.slug)).slice(0, 4);
              if (catTools.length === 0) return null;
              return (
                <div key={cat.slug} className="space-y-14">
                  {idx === Math.floor(categories.length / 2) && (
                    <AdSlot slot="home-mid" tools={midPromo} />
                  )}
                  <section id={`cat-${cat.slug}`} className="scroll-mt-32">
                    <SectionHeader
                      title={cat.name}
                      subtitle={cat.description}
                      moreHref={`/category/${cat.slug}`}
                      moreText={`全部 ${cat.count} 款`}
                    />
                    <ToolGrid tools={catTools} />
                  </section>
                </div>
              );
            })}

            {/* 热门提示词 */}
            {hotPrompts.length > 0 && (
              <section>
                <SectionHeader
                  title="热门提示词"
                  subtitle="复制即用的高质量中文 Prompt"
                  moreHref="/prompts"
                  moreText="进入提示词库"
                />
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                  {hotPrompts.map((p) => (
                    <PromptCard key={p.cid} prompt={p} />
                  ))}
                </div>
              </section>
            )}

            {/* 热门标签 */}
            {tags.length > 0 && (
              <section>
                <SectionHeader title="按标签找工具" subtitle="从使用场景出发，快速定位同类工具" />
                <div className="flex flex-wrap gap-2.5">
                  {tags.slice(0, 16).map((t) => (
                    <Link
                      key={t.slug}
                      href={`/tag/${t.slug}`}
                      className="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 hover:text-brand-700 dark:hover:text-brand-300 hover:shadow-md"
                    >
                      # {t.name}
                      <span className="ml-1.5 font-display text-xs text-gray-400">{t.count}</span>
                    </Link>
                  ))}
                </div>
              </section>
            )}

            {/* AI 资讯（头条大图 + 紧凑列表，杂志式布局） */}
            {news.items.length > 0 && (
              <section>
                <SectionHeader title="AI 资讯" subtitle="行业新闻与趋势解读" moreHref="/news" />
                <div className="grid gap-5 lg:grid-cols-2">
                  {/* 头条 */}
                  <Link
                    href={`/news/${news.items[0].slug}`}
                    className="group overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-md"
                  >
                    {news.items[0].cover && (
                      // eslint-disable-next-line @next/next/no-img-element
                      <img src={news.items[0].cover} alt={news.items[0].title} loading="lazy" className="aspect-[16/9] w-full bg-gray-100 dark:bg-gray-800 object-cover" />
                    )}
                    <div className="p-5">
                      <div className="flex items-center gap-2 text-xs text-gray-400">
                        <time>{formatDate(news.items[0].created)}</time>
                        {news.items[0].readTime > 0 && (
                          <>
                            <span>·</span>
                            <span className="inline-flex items-center gap-1"><Clock size={12} />{news.items[0].readTime} 分钟阅读</span>
                          </>
                        )}
                      </div>
                      <h3 className="mt-2 line-clamp-2 text-lg font-bold leading-7 text-gray-900 dark:text-gray-100 group-hover:text-brand-700 dark:group-hover:text-brand-300">{news.items[0].title}</h3>
                      <p className="mt-2 line-clamp-2 text-sm leading-6 text-gray-500">{news.items[0].digest}</p>
                    </div>
                  </Link>
                  {/* 列表 */}
                  <div className="divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
                    {news.items.slice(1, 5).map((item) => (
                      <Link
                        key={item.cid}
                        href={`/news/${item.slug}`}
                        className="group flex gap-4 p-4 transition hover:bg-brand-50/50 dark:hover:bg-brand-900/20"
                      >
                        {item.cover && (
                          // eslint-disable-next-line @next/next/no-img-element
                          <img src={item.cover} alt={item.title} loading="lazy" className="h-16 w-24 shrink-0 rounded-lg bg-gray-100 dark:bg-gray-800 object-cover" />
                        )}
                        <div className="min-w-0 flex-1">
                          <h4 className="line-clamp-2 text-sm font-semibold leading-6 text-gray-900 dark:text-gray-100 group-hover:text-brand-700 dark:group-hover:text-brand-300">{item.title}</h4>
                          <div className="mt-1 flex items-center gap-2 text-xs text-gray-400">
                            <time>{formatDate(item.created)}</time>
                            {item.readTime > 0 && (
                              <>
                                <span>·</span>
                                <span className="inline-flex items-center gap-1"><Clock size={12} />{item.readTime} 分钟阅读</span>
                              </>
                            )}
                          </div>
                        </div>
                      </Link>
                    ))}
                  </div>
                </div>
              </section>
            )}
          </>
        )}
      </div>
    </>
  );
}
