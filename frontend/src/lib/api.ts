import type { Category, NewsItem, PromptItem, Tag, Tool, WpPost } from './types';

/**
 * WordPress REST API 客户端（仅在服务端调用）。
 * 所有请求带 60s ISR 缓存；后端不可用时返回空数据，保证前台可降级渲染。
 */
const API_BASE = (process.env.WP_API_BASE ?? 'http://localhost:8090/wp-json').replace(/\/+$/, '');

export const NEWS_CATEGORY_SLUG = 'ai-news';
export const FLASH_CATEGORY_SLUG = 'ai-flash';
export const PROMPTS_CATEGORY_SLUG = 'ai-prompts';

/** 非工具的保留分类 */
const RESERVED_SLUGS = new Set([NEWS_CATEGORY_SLUG, FLASH_CATEGORY_SLUG, PROMPTS_CATEGORY_SLUG]);

/** 分类展示顺序（WP 分类无排序字段，前台固定） */
const CATEGORY_ORDER: Record<string, number> = {
  chatbot: 1, 'text-writing': 2, image: 3, video: 4, audio: 5,
  'code-it': 6, productivity: 7, marketing: 8, education: 9,
  'ai-prompts': 97, 'ai-flash': 98, 'ai-news': 99,
};

interface ApiResult<T> {
  data: T | null;
  total: number;
  totalPages: number;
}

async function apiGet<T>(path: string, params: Record<string, string | number | undefined> = {}): Promise<ApiResult<T>> {
  const qs = new URLSearchParams();
  for (const [k, v] of Object.entries(params)) {
    if (v !== undefined && v !== '') qs.set(k, String(v));
  }
  const url = `${API_BASE}/${path}${qs.size ? `?${qs.toString()}` : ''}`;
  try {
    const res = await fetch(url, { next: { revalidate: 60 } });
    if (!res.ok) return { data: null, total: 0, totalPages: 0 };
    return {
      data: (await res.json()) as T,
      total: Number(res.headers.get('x-wp-total')) || 0,
      totalPages: Number(res.headers.get('x-wp-totalpages')) || 0,
    };
  } catch {
    // 后端未就绪时静默降级
    return { data: null, total: 0, totalPages: 0 };
  }
}

// ---------------- 数据映射 ----------------

function decodeEntities(s: string): string {
  return s
    .replace(/<[^>]+>/g, '')
    .replace(/&hellip;|\[&hellip;\]/g, '…')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;|&#8220;|&#8221;/g, '"')
    .replace(/&#8217;|&#8216;|&#039;/g, "'")
    .replace(/&#(\d+);/g, (_, code) => String.fromCharCode(Number(code)))
    .trim();
}

function toTs(dateGmt: string): number {
  return Math.floor(Date.parse(`${dateGmt}Z`) / 1000) || 0;
}

function meta(p: WpPost, key: string): string {
  return p.meta?.[key] ?? '';
}

function terms(p: WpPost, taxonomy: 'category' | 'post_tag'): { name: string; slug: string }[] {
  const groups = p._embedded?.['wp:term'] ?? [];
  return groups
    .flat()
    .filter((t) => t.taxonomy === taxonomy)
    .map((t) => ({ name: t.name, slug: t.slug }));
}

function toTool(p: WpPost): Tool {
  return {
    cid: p.id,
    title: decodeEntities(p.title.rendered),
    slug: p.slug,
    created: toTs(p.date_gmt),
    url: meta(p, 'url'),
    logo: meta(p, 'logo'),
    tagline: meta(p, 'tagline'),
    pricing: meta(p, 'pricing') || '未知',
    likes: Number(meta(p, 'likes')) || 0,
    monthlyVisits: Number(meta(p, 'monthly_visits')) || 0,
    growth: Number(meta(p, 'growth')) || 0,
    screenshot: meta(p, 'screenshot'),
    rating: Number(meta(p, 'rating')) || 0,
    views: Number(meta(p, 'views')) || 0,
    clicks: Number(meta(p, 'clicks')) || 0,
    visitsHistory: meta(p, 'visits_history')
      .split(',')
      .map((v) => Number(v.trim()))
      .filter((v) => v > 0),
    regions: meta(p, 'regions')
      .split(',')
      .map((pair) => {
        const [name, pct] = pair.split(':');
        return { name: (name ?? '').trim(), pct: Number(pct) || 0 };
      })
      .filter((r) => r.name && r.pct > 0),
    faq: meta(p, 'faq')
      .split(/\r?\n/)
      .map((line) => {
        const [q, a] = line.split('|');
        return { q: (q ?? '').trim(), a: (a ?? '').trim() };
      })
      .filter((item) => item.q && item.a),
    scores: meta(p, 'scores')
      .split(',')
      .map((pair) => {
        const [label, value] = pair.split(':');
        return { label: (label ?? '').trim(), value: Number(value) || 0 };
      })
      .filter((s) => s.label && s.value > 0),
    featured: meta(p, 'featured') === '1',
    banner: meta(p, 'banner') === '1',
    promo: meta(p, 'promo'),
    categories: terms(p, 'category'),
    tags: terms(p, 'post_tag'),
    contentHtml: p.content?.rendered,
  };
}

function toNews(p: WpPost): NewsItem {
  return {
    cid: p.id,
    title: decodeEntities(p.title.rendered),
    slug: p.slug,
    created: toTs(p.date_gmt),
    digest: decodeEntities(p.excerpt?.rendered ?? '').replace(/\s+/g, ' '),
    cover: meta(p, 'cover'),
    contentHtml: p.content?.rendered,
  };
}

/** 是否为「工具」文章（有 url 字段且不属于保留分类） */
function isTool(p: WpPost): boolean {
  if (!meta(p, 'url')) return false;
  return !terms(p, 'category').some((c) => RESERVED_SLUGS.has(c.slug));
}

/** 是否为「提示词」文章 */
function isPrompt(p: WpPost): boolean {
  return Boolean(meta(p, 'prompt'));
}

function toPrompt(p: WpPost): PromptItem {
  return {
    cid: p.id,
    title: decodeEntities(p.title.rendered),
    slug: p.slug,
    created: toTs(p.date_gmt),
    prompt: meta(p, 'prompt'),
    model: meta(p, 'prompt_model') || '通用',
    scene: meta(p, 'prompt_scene') || '其他',
    likes: Number(meta(p, 'likes')) || 0,
    contentHtml: p.content?.rendered,
  };
}

// ---------------- 分类 ----------------

export async function getCategories(): Promise<Category[]> {
  const { data } = await apiGet<any[]>('wp/v2/categories', { per_page: 100, hide_empty: 'true' });
  if (!data) return [];
  return data
    .map((c) => ({
      mid: Number(c.id),
      name: decodeEntities(String(c.name)),
      slug: c.slug,
      description: c.description ?? '',
      count: Number(c.count) || 0,
      order: CATEGORY_ORDER[c.slug] ?? 50,
    }))
    .filter((c) => c.slug !== 'uncategorized')
    .sort((a, b) => a.order - b.order);
}

/** 工具分类（排除资讯/快讯/提示词等保留分类） */
export async function getToolCategories(): Promise<Category[]> {
  return (await getCategories()).filter((c) => !RESERVED_SLUGS.has(c.slug));
}

async function categoryIdBySlug(slug: string): Promise<number> {
  return (await getCategories()).find((c) => c.slug === slug)?.mid ?? 0;
}

// ---------------- 标签 ----------------

export async function getTags(): Promise<Tag[]> {
  const { data } = await apiGet<any[]>('wp/v2/tags', { per_page: 100, hide_empty: 'true' });
  if (!data) return [];
  return data
    .map((t) => ({ mid: Number(t.id), name: decodeEntities(String(t.name)), slug: t.slug, count: Number(t.count) || 0 }))
    .filter((t) => t.count > 0)
    .sort((a, b) => b.count - a.count);
}

export async function getToolsByTag(slug: string, page = 1, pageSize = 24): Promise<{ tools: Tool[]; pages: number; count: number }> {
  const tag = (await getTags()).find((t) => t.slug === slug);
  if (!tag) return { tools: [], pages: 0, count: 0 };
  const { data, total, totalPages } = await apiGet<WpPost[]>('wp/v2/posts', {
    tags: tag.mid,
    page,
    per_page: pageSize,
    _embed: 'wp:term',
  });
  if (!data) return { tools: [], pages: 0, count: 0 };
  return { tools: data.filter(isTool).map(toTool), pages: totalPages, count: total };
}

// ---------------- 工具 ----------------

/** 拉取全量工具（本站定位中小规模导航，单次拉取后在服务端派生各板块） */
export async function getAllTools(): Promise<Tool[]> {
  const { data } = await apiGet<WpPost[]>('wp/v2/posts', { per_page: 100, _embed: 'wp:term' });
  if (!data) return [];
  return data.filter(isTool).map(toTool);
}

export async function getToolsByCategory(slug: string, page = 1, pageSize = 24): Promise<{ tools: Tool[]; pages: number; count: number }> {
  const catId = await categoryIdBySlug(slug);
  if (!catId) return { tools: [], pages: 0, count: 0 };
  const { data, total, totalPages } = await apiGet<WpPost[]>('wp/v2/posts', {
    categories: catId,
    page,
    per_page: pageSize,
    _embed: 'wp:term',
  });
  if (!data) return { tools: [], pages: 0, count: 0 };
  return { tools: data.filter(isTool).map(toTool), pages: totalPages, count: total };
}

export async function searchPosts(keyword: string, page = 1, pageSize = 24): Promise<{ tools: Tool[]; prompts: PromptItem[]; news: NewsItem[]; pages: number; count: number }> {
  const { data, total, totalPages } = await apiGet<WpPost[]>('wp/v2/posts', {
    search: keyword,
    page,
    per_page: pageSize,
    _embed: 'wp:term',
  });
  if (!data) return { tools: [], prompts: [], news: [], pages: 0, count: 0 };
  return {
    tools: data.filter(isTool).map(toTool),
    prompts: data.filter(isPrompt).map(toPrompt),
    news: data.filter((p) => !isTool(p) && !isPrompt(p)).map(toNews),
    pages: totalPages,
    count: total,
  };
}

/** 工具详情（带正文 HTML） */
export async function getToolBySlug(slug: string): Promise<Tool | null> {
  const { data } = await apiGet<WpPost[]>('wp/v2/posts', { slug, _embed: 'wp:term' });
  const post = data?.[0];
  if (!post || !meta(post, 'url')) return null;
  return toTool(post);
}

// ---------------- 资讯 / 快讯 ----------------

async function getPostsByCategory(slug: string, page: number, pageSize: number): Promise<{ items: NewsItem[]; pages: number; count: number }> {
  const catId = await categoryIdBySlug(slug);
  if (!catId) return { items: [], pages: 0, count: 0 };
  const { data, total, totalPages } = await apiGet<WpPost[]>('wp/v2/posts', {
    categories: catId,
    page,
    per_page: pageSize,
    _embed: 'wp:term',
  });
  if (!data) return { items: [], pages: 0, count: 0 };
  return { items: data.map(toNews), pages: totalPages, count: total };
}

export async function getNews(page = 1, pageSize = 12) {
  return getPostsByCategory(NEWS_CATEGORY_SLUG, page, pageSize);
}

export async function getFlash(page = 1, pageSize = 20) {
  return getPostsByCategory(FLASH_CATEGORY_SLUG, page, pageSize);
}

export async function getNewsBySlug(slug: string): Promise<NewsItem | null> {
  const { data } = await apiGet<WpPost[]>('wp/v2/posts', { slug, _embed: 'wp:term' });
  const post = data?.[0];
  if (!post) return null;
  return toNews(post);
}

// ---------------- 提示词 ----------------

/** 提示词列表（量级不大，单次拉全量后前端筛选场景） */
export async function getPrompts(): Promise<PromptItem[]> {
  const catId = await categoryIdBySlug(PROMPTS_CATEGORY_SLUG);
  if (!catId) return [];
  const { data } = await apiGet<WpPost[]>('wp/v2/posts', { categories: catId, per_page: 100 });
  if (!data) return [];
  return data.map(toPrompt);
}

export async function getPromptBySlug(slug: string): Promise<PromptItem | null> {
  const { data } = await apiGet<WpPost[]>('wp/v2/posts', { slug });
  const post = data?.[0];
  if (!post || !meta(post, 'prompt')) return null;
  return toPrompt(post);
}

// ---------------- 运营位 ----------------

/** 取指定运营位的工具（promo 字段：home-mid / detail-side / ranking-top / tools-inline / news-inline / detail-bottom） */
export function pickPromo(tools: Tool[], slot: string, limit = 2, excludeCid?: number): Tool[] {
  return tools.filter((t) => t.promo === slot && t.cid !== excludeCid).slice(0, limit);
}
