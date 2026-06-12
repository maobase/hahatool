import Link from 'next/link';
import { Bookmark } from 'lucide-react';
import type { PromptItem } from '@/lib/types';
import { formatCount } from '@/lib/format';
import CopyButton from './CopyButton';

/** 提示词卡片：预览 + 一键复制 */
export default function PromptCard({ prompt }: { prompt: PromptItem }) {
  return (
    <div className="group relative flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lg hover:shadow-brand-100/60 dark:border-gray-800 dark:bg-gray-900 dark:hover:shadow-none">
      <Link
        href={`/prompts/${prompt.slug}`}
        className="absolute inset-0 rounded-2xl"
        aria-label={`查看提示词「${prompt.title}」`}
      />
      <div className="flex items-start justify-between gap-2">
        <h3 className="font-semibold text-gray-900 group-hover:text-brand-700 dark:text-gray-100 dark:group-hover:text-brand-300">
          {prompt.title}
        </h3>
        <CopyButton text={prompt.prompt} />
      </div>
      <div className="mt-1.5 flex items-center gap-2 text-xs">
        <span className="rounded-full bg-brand-50 px-2 py-0.5 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
          {prompt.scene}
        </span>
        <span className="rounded-full bg-gray-100 px-2 py-0.5 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
          {prompt.model}
        </span>
        <span className="ml-auto flex items-center gap-1 tabular-nums text-gray-400" title="热度">
          <Bookmark size={12} className="text-brand-400" />
          {formatCount(prompt.likes)}
        </span>
      </div>
      <pre className="mt-3 line-clamp-5 flex-1 whitespace-pre-wrap rounded-xl bg-gray-50 p-3 font-mono text-xs leading-5 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
        {prompt.prompt}
      </pre>
    </div>
  );
}
