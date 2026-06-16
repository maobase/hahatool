import type { Metadata } from 'next';
import Link from 'next/link';
import { Layers } from 'lucide-react';
import { getTopics } from '@/lib/api';
import EmptyState from '@/components/EmptyState';

export const revalidate = 60;

export const metadata: Metadata = {
  title: '专题合集',
  description: '精心策划的 AI 工具专题合集，按场景与主题归类，快速找到同类好工具。',
};

export default async function TopicsPage() {
  const topics = await getTopics();

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">
        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-white">
          <Layers size={18} />
        </span>
        专题合集
      </h1>
      <p className="mt-2 text-sm text-gray-500">精心策划的 AI 工具合集，按场景与主题归类</p>

      {topics.length === 0 ? (
        <div className="mt-8"><EmptyState title="暂无专题" /></div>
      ) : (
        <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {topics.map((t) => (
            <Link
              key={t.slug}
              href={`/topic/${t.slug}`}
              className="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm transition hover:-translate-y-1 hover:border-brand-300 hover:shadow-md"
            >
              <div
                className="h-36 bg-gradient-to-br from-brand-500 to-brand-800 bg-cover bg-center"
                style={t.cover ? { backgroundImage: `url(${t.cover})` } : undefined}
              />
              <div className="flex flex-1 flex-col gap-1.5 p-5">
                <h2 className="text-lg font-bold text-gray-900 dark:text-gray-100 group-hover:text-brand-700 dark:group-hover:text-brand-300">{t.name}</h2>
                <p className="line-clamp-2 text-sm leading-6 text-gray-500">{t.description}</p>
                <span className="mt-auto inline-flex items-center gap-1 pt-2 text-xs text-gray-400">
                  <Layers size={12} />
                  {t.count} 个工具
                </span>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
