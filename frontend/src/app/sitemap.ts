import type { MetadataRoute } from 'next';
import { getAllTools, getFlash, getNews, getPrompts, getTags, getToolCategories } from '@/lib/api';

const SITE_URL = (process.env.NEXT_PUBLIC_SITE_URL ?? 'http://localhost:3000').replace(/\/+$/, '');

// 构建期后端不可达时动态条目为空，运行时每小时重新生成
export const revalidate = 3600;

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const [tools, categories, tags, news, flash, prompts] = await Promise.all([
    getAllTools(),
    getToolCategories(),
    getTags(),
    getNews(1, 100),
    getFlash(1, 100),
    getPrompts(),
  ]);

  const staticPages: MetadataRoute.Sitemap = ['', '/tools', '/ranking', '/compare', '/prompts', '/flash', '/news', '/submit'].map(
    (path) => ({ url: `${SITE_URL}${path}`, changeFrequency: 'daily', priority: path === '' ? 1 : 0.8 }),
  );

  return [
    ...staticPages,
    ...tools.map((t) => ({
      url: `${SITE_URL}/tool/${t.slug}`,
      lastModified: new Date(t.created * 1000),
      changeFrequency: 'weekly' as const,
      priority: 0.9,
    })),
    ...categories.map((c) => ({ url: `${SITE_URL}/category/${c.slug}`, changeFrequency: 'daily' as const, priority: 0.7 })),
    ...tags.map((t) => ({ url: `${SITE_URL}/tag/${t.slug}`, changeFrequency: 'weekly' as const, priority: 0.5 })),
    ...prompts.map((p) => ({ url: `${SITE_URL}/prompts/${p.slug}`, changeFrequency: 'monthly' as const, priority: 0.6 })),
    ...[...news.items, ...flash.items].map((n) => ({
      url: `${SITE_URL}/news/${n.slug}`,
      lastModified: new Date(n.created * 1000),
      changeFrequency: 'monthly' as const,
      priority: 0.6,
    })),
  ];
}
