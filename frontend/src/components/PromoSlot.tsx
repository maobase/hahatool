import Link from 'next/link';
import { ArrowRight, Megaphone } from 'lucide-react';
import type { Tool } from '@/lib/types';
import ToolLogo from './ToolLogo';
import RatingStars from './RatingStars';

/** 宽幅运营位横幅（首页中部 / 榜单顶部） */
export function PromoBanner({ tool }: { tool: Tool }) {
  return (
    <div className="relative overflow-hidden rounded-2xl bg-gray-900 p-6 text-white shadow-md sm:p-7">
      <div
        aria-hidden
        className="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-brand-600/50 blur-[80px]"
      />
      <span className="absolute right-4 top-4 flex items-center gap-1 rounded-full bg-white/10 px-2.5 py-1 text-[11px] text-white/80">
        <Megaphone size={11} />
        推广
      </span>
      <div className="relative flex flex-col gap-4 sm:flex-row sm:items-center">
        <ToolLogo src={tool.logo} name={tool.title} size={56} className="ring-2 ring-white/20" />
        <div className="min-w-0 flex-1">
          <div className="flex items-center gap-2">
            <h3 className="text-lg font-bold">{tool.title}</h3>
            <RatingStars rating={tool.rating} size={12} showValue={false} />
          </div>
          <p className="mt-1 line-clamp-1 text-sm text-white/70">{tool.tagline}</p>
        </div>
        <div className="flex shrink-0 items-center gap-2.5">
          <a
            href={tool.url}
            target="_blank"
            rel="noopener noreferrer nofollow"
            className="inline-flex items-center gap-1 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-400"
          >
            立即体验
            <ArrowRight size={14} />
          </a>
          <Link
            href={`/tool/${tool.slug}`}
            className="rounded-xl px-4 py-2 text-sm text-white/80 ring-1 ring-white/25 transition hover:bg-white/10"
          >
            详情
          </Link>
        </div>
      </div>
    </div>
  );
}

/** 侧栏推广卡（详情页等） */
export function PromoSideCard({ tools }: { tools: Tool[] }) {
  if (tools.length === 0) return null;
  return (
    <div className="rounded-2xl border border-brand-100 dark:border-gray-800 bg-gradient-to-b from-brand-50/80 to-white dark:from-brand-900/20 dark:to-gray-900 p-5 shadow-sm">
      <p className="flex items-center gap-1 text-xs font-medium text-brand-400">
        <Megaphone size={12} />
        推广
      </p>
      <ul className="mt-3 space-y-3">
        {tools.map((t) => (
          <li key={t.cid}>
            <Link
              href={`/tool/${t.slug}`}
              className="group flex items-center gap-3 rounded-xl bg-white dark:bg-gray-900 p-3 ring-1 ring-gray-100 dark:ring-gray-800 transition hover:ring-brand-300"
            >
              <ToolLogo src={t.logo} name={t.title} size={40} />
              <span className="min-w-0 flex-1">
                <span className="block truncate text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-brand-700 dark:group-hover:text-brand-300">
                  {t.title}
                </span>
                <span className="mt-0.5 block truncate text-xs text-gray-500">{t.tagline}</span>
              </span>
              <ArrowRight size={14} className="shrink-0 text-gray-300 transition group-hover:text-brand-500" />
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
}
