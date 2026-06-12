import Link from 'next/link';
import type { NewsItem } from '@/lib/types';

function dayLabel(ts: number): string {
  const d = new Date(ts * 1000);
  return `${d.getMonth() + 1}月${d.getDate()}日`;
}

function timeLabel(ts: number): string {
  const d = new Date(ts * 1000);
  const pad = (x: number) => String(x).padStart(2, '0');
  return `${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

/** 快讯时间线（按天分组） */
export default function FlashTimeline({ items, compact = false }: { items: NewsItem[]; compact?: boolean }) {
  if (items.length === 0) return null;

  const groups: { day: string; items: NewsItem[] }[] = [];
  for (const item of items) {
    const day = dayLabel(item.created);
    const last = groups[groups.length - 1];
    if (last && last.day === day) last.items.push(item);
    else groups.push({ day, items: [item] });
  }

  return (
    <div className="space-y-6">
      {groups.map((group) => (
        <section key={group.day} aria-label={group.day}>
          <h3 className="font-display text-sm font-bold text-gray-900 dark:text-gray-100">
            <span className="rounded-lg bg-gray-900 dark:bg-brand-600 px-2.5 py-1 text-white">{group.day}</span>
          </h3>
          <ol className="mt-3 space-y-0 border-l-2 border-brand-100 dark:border-gray-800 pl-5">
            {group.items.map((item) => (
              <li key={item.cid} className="relative pb-5 last:pb-0">
                <span
                  aria-hidden
                  className="absolute -left-[25px] top-1.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-brand-400 ring-1 ring-brand-200"
                />
                <time className="font-display text-xs font-semibold tabular-nums text-brand-500">
                  {timeLabel(item.created)}
                </time>
                <Link href={`/news/${item.slug}`} className="group mt-0.5 block">
                  <p className="text-sm font-medium leading-6 text-gray-800 dark:text-gray-200 transition group-hover:text-brand-700 dark:group-hover:text-brand-300">
                    {item.title}
                  </p>
                  {!compact && item.digest && (
                    <p className="mt-1 line-clamp-2 text-xs leading-5 text-gray-500">{item.digest}</p>
                  )}
                </Link>
              </li>
            ))}
          </ol>
        </section>
      ))}
    </div>
  );
}
