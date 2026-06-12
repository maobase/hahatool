import type { Metadata } from 'next';
import Link from 'next/link';
import { Wand2 } from 'lucide-react';
import { getPrompts } from '@/lib/api';
import EmptyState from '@/components/EmptyState';
import PromptCard from '@/components/PromptCard';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'AI 提示词库',
  description: '高质量中文 AI 提示词：写作、编程、营销、办公、学习，一键复制即用。',
};

export default async function PromptsPage({
  searchParams,
}: {
  searchParams: Promise<{ scene?: string }>;
}) {
  const { scene } = await searchParams;
  const prompts = await getPrompts();

  // 场景列表由数据派生
  const scenes = [...new Set(prompts.map((p) => p.scene))];
  const filtered = scene ? prompts.filter((p) => p.scene === scene) : prompts;
  const sorted = [...filtered].sort((a, b) => b.likes - a.likes);

  const chip = (active: boolean) =>
    `rounded-full px-4 py-1.5 text-sm transition ${
      active
        ? 'bg-brand-600 font-medium text-white'
        : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:text-brand-600 dark:bg-gray-900 dark:text-gray-400 dark:ring-gray-700 dark:hover:text-brand-400'
    }`;

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">
        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-white">
          <Wand2 size={18} />
        </span>
        AI 提示词库
      </h1>
      <p className="mt-2 text-sm text-gray-500">
        高质量中文提示词，按热度排序，点击「复制」直接粘贴给任何 AI 使用
      </p>

      <div className="mt-6 flex flex-wrap gap-2">
        <Link href="/prompts" className={chip(!scene)}>全部</Link>
        {scenes.map((s) => (
          <Link key={s} href={`/prompts?scene=${encodeURIComponent(s)}`} className={chip(scene === s)}>
            {s}
          </Link>
        ))}
      </div>

      <div className="mt-8">
        {sorted.length === 0 ? (
          <EmptyState title="暂无提示词" />
        ) : (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {sorted.map((p) => (
              <PromptCard key={p.cid} prompt={p} />
            ))}
          </div>
        )}
      </div>

      <p className="mt-10 text-center text-xs text-gray-400">
        有好用的提示词想分享？通过 <Link href="/submit" className="text-brand-600 hover:underline dark:text-brand-400">提交页</Link> 投稿给我们
      </p>
    </div>
  );
}
