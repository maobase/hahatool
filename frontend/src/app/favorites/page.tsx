'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { Heart } from 'lucide-react';
import { FAV_EVENT, getFavorites } from '@/lib/client';
import type { Tool } from '@/lib/types';
import ToolGrid from '@/components/ToolGrid';

/** 我的收藏（localStorage，本机保存，无需登录） */
export default function FavoritesPage() {
  const [tools, setTools] = useState<Tool[] | null>(null);

  useEffect(() => {
    let cancelled = false;

    const load = async () => {
      const favs = getFavorites();
      if (favs.length === 0) {
        if (!cancelled) setTools([]);
        return;
      }
      try {
        const res = await fetch(`/api/wp/wp/v2/posts?include=${favs.join(',')}&per_page=100&_embed=wp:term`);
        const posts = (await res.json()) as any[];
        const list: Tool[] = posts
          .filter((p) => p.meta?.url)
          .map((p) => ({
            cid: p.id,
            title: (p.title?.rendered ?? '').replace(/<[^>]+>/g, ''),
            slug: p.slug,
            created: Math.floor(Date.parse(`${p.date_gmt}Z`) / 1000) || 0,
            url: p.meta?.url ?? '',
            logo: p.meta?.logo ?? '',
            tagline: p.meta?.tagline ?? '',
            pricing: p.meta?.pricing ?? '未知',
            likes: Number(p.meta?.likes) || 0,
            monthlyVisits: Number(p.meta?.monthly_visits) || 0,
            growth: Number(p.meta?.growth) || 0,
            screenshot: p.meta?.screenshot ?? '',
            rating: Number(p.meta?.rating) || 0,
            views: 0,
            clicks: 0,
            visitsHistory: [],
            regions: [],
            faq: [],
            scores: [],
            featured: false,
            banner: false,
            promo: '',
            categories: (p._embedded?.['wp:term'] ?? [])
              .flat()
              .filter((t: any) => t.taxonomy === 'category')
              .map((t: any) => ({ name: t.name, slug: t.slug })),
            tags: [],
          }))
          // 按收藏先后排序
          .sort((a, b) => favs.indexOf(a.cid) - favs.indexOf(b.cid));
        if (!cancelled) setTools(list);
      } catch {
        if (!cancelled) setTools([]);
      }
    };

    load();
    window.addEventListener(FAV_EVENT, load);
    return () => {
      cancelled = true;
      window.removeEventListener(FAV_EVENT, load);
    };
  }, []);

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">
        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-600 text-white">
          <Heart size={18} className="fill-current" />
        </span>
        我的收藏
      </h1>
      <p className="mt-2 text-sm text-gray-500">收藏保存在本机浏览器中，无需登录</p>

      <div className="mt-8">
        {tools === null ? (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" aria-hidden>
            {Array.from({ length: 4 }).map((_, i) => (
              <div key={i} className="h-44 animate-pulse rounded-2xl bg-gray-100 dark:bg-gray-800" />
            ))}
          </div>
        ) : tools.length === 0 ? (
          <div className="flex flex-col items-center rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-6 py-16 text-center">
            <Heart size={40} className="text-gray-200" />
            <p className="mt-4 font-medium text-gray-700 dark:text-gray-300">还没有收藏任何工具</p>
            <p className="mt-2 text-sm text-gray-500">在工具卡片或详情页点击心形按钮即可收藏</p>
            <Link
              href="/tools"
              className="mt-6 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-700"
            >
              去逛逛工具库
            </Link>
          </div>
        ) : (
          <ToolGrid tools={tools} />
        )}
      </div>
    </div>
  );
}
