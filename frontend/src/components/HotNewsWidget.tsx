import Link from 'next/link';
import { Eye, Flame } from 'lucide-react';
import { formatCount } from '@/lib/format';
import type { NewsItem } from '@/lib/types';

/** 热门资讯榜（按站内浏览量排序）—— 资讯列表页/详情页侧栏复用 */
export default function HotNewsWidget({ items }: { items: NewsItem[] }) {
  if (items.length === 0) return null;
  return (
    <div className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
      <h2 className="flex items-center gap-1.5 font-semibold text-gray-900 dark:text-gray-100">
        <Flame size={15} className="text-orange-500" />
        热门资讯
      </h2>
      <ol className="mt-3 space-y-1">
        {items.map((n, i) => (
          <li key={n.cid}>
            <Link
              href={`/news/${n.slug}`}
              className="group flex items-start gap-2.5 rounded-xl px-2 py-2 transition hover:bg-brand-50 dark:hover:bg-brand-900/30"
            >
              <span className="mt-0.5 w-4 shrink-0 text-center font-display text-sm font-bold text-gray-300">{i + 1}</span>
              <span className="min-w-0 flex-1 line-clamp-2 text-sm font-medium text-gray-800 dark:text-gray-200 group-hover:text-brand-700 dark:group-hover:text-brand-300">
                {n.title}
              </span>
              <span className="mt-0.5 inline-flex shrink-0 items-center gap-1 font-display text-xs tabular-nums text-gray-400">
                <Eye size={12} />
                {formatCount(n.views)}
              </span>
            </Link>
          </li>
        ))}
      </ol>
    </div>
  );
}
