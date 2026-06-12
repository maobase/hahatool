import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { Bookmark, ChevronLeft } from 'lucide-react';
import { getPromptBySlug, getPrompts } from '@/lib/api';
import { formatCount, formatDate } from '@/lib/format';
import CopyButton from '@/components/CopyButton';
import PromptCard from '@/components/PromptCard';

export const revalidate = 60;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const item = await getPromptBySlug(slug);
  return { title: item ? `${item.title} - AI 提示词` : '提示词' };
}

export default async function PromptDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const [item, all] = await Promise.all([getPromptBySlug(slug), getPrompts()]);
  if (!item) notFound();

  const related = all.filter((p) => p.cid !== item.cid && p.scene === item.scene).slice(0, 3);

  return (
    <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6">
      <Link href="/prompts" className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-600 dark:hover:text-brand-400">
        <ChevronLeft size={16} />
        返回提示词库
      </Link>

      <article className="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-8">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">{item.title}</h1>
          <CopyButton text={item.prompt} label="复制提示词" />
        </div>
        <div className="mt-2 flex flex-wrap items-center gap-2 text-xs">
          <span className="rounded-full bg-brand-50 px-2.5 py-0.5 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
            {item.scene}
          </span>
          <span className="rounded-full bg-gray-100 px-2.5 py-0.5 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
            适用：{item.model}
          </span>
          <span className="flex items-center gap-1 tabular-nums text-gray-400">
            <Bookmark size={12} className="text-brand-400" />
            {formatCount(item.likes)} 热度
          </span>
          <time className="text-gray-400">{formatDate(item.created)} 收录</time>
        </div>

        <pre className="mt-5 whitespace-pre-wrap rounded-xl border border-gray-100 bg-gray-50 p-5 font-mono text-sm leading-7 text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300">
          {item.prompt}
        </pre>

        {item.contentHtml && (
          <div
            className="prose prose-gray dark:prose-invert mt-6 max-w-none prose-a:text-brand-600"
            dangerouslySetInnerHTML={{ __html: item.contentHtml }}
          />
        )}
      </article>

      {related.length > 0 && (
        <section className="mt-10">
          <h2 className="text-lg font-bold text-gray-900 dark:text-gray-100">同场景提示词</h2>
          <div className="mt-4 grid gap-4 sm:grid-cols-2">
            {related.map((p) => (
              <PromptCard key={p.cid} prompt={p} />
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
