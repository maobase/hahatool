import Link from 'next/link';
import { Zap } from 'lucide-react';
import type { NewsItem } from '@/lib/types';

/** 快讯跑马灯横条（纯 CSS 动画，hover 暂停，reduced-motion 下静态展示） */
export default function NewsTicker({ items }: { items: NewsItem[] }) {
  if (items.length === 0) return null;
  // 内容重复两遍实现无缝循环
  const loop = [...items, ...items];

  return (
    <div className="border-b border-brand-100 dark:border-gray-800 bg-brand-50/70 dark:bg-gray-900/70">
      <div className="mx-auto flex max-w-7xl items-center gap-3 px-4 py-2 sm:px-6">
        <Link
          href="/flash"
          className="flex shrink-0 items-center gap-1 rounded-full bg-brand-600 px-2.5 py-0.5 text-[11px] font-bold text-white"
        >
          <Zap size={11} className="fill-current" />
          快讯
        </Link>
        <div className="ticker-mask relative flex-1 overflow-hidden">
          <ul className="ticker-track flex w-max items-center gap-10 motion-reduce:w-auto motion-reduce:animate-none">
            {loop.map((item, i) => (
              <li key={`${item.cid}-${i}`} className={i >= items.length ? 'motion-reduce:hidden' : ''} aria-hidden={i >= items.length}>
                <Link
                  href={`/news/${item.slug}`}
                  className="whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 transition hover:text-brand-700 dark:hover:text-brand-300"
                >
                  {item.title}
                </Link>
              </li>
            ))}
          </ul>
        </div>
        <Link href="/flash" className="hidden shrink-0 text-xs text-brand-600 dark:text-brand-400 hover:underline sm:block">
          全部快讯 →
        </Link>
      </div>
    </div>
  );
}
