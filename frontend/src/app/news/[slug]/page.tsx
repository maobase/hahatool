import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { getNews, getNewsBySlug } from '@/lib/api';
import { formatDate } from '@/lib/format';

export const revalidate = 60;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const item = await getNewsBySlug(slug);
  return { title: item?.title ?? '资讯详情' };
}

export default async function NewsDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const [item, list] = await Promise.all([getNewsBySlug(slug), getNews(1, 50)]);
  if (!item || !item.contentHtml) notFound();

  // 上一篇/下一篇（列表按时间倒序）
  const idx = list.items.findIndex((n) => n.slug === slug);
  const newer = idx > 0 ? list.items[idx - 1] : null;
  const older = idx >= 0 && idx < list.items.length - 1 ? list.items[idx + 1] : null;
  const related = list.items.filter((n) => n.slug !== slug).slice(0, 3);

  return (
    <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6">
      <Link href="/news" className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-600 dark:hover:text-brand-400">
        <ChevronLeft size={16} />
        返回资讯列表
      </Link>
      <article className="mt-6 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        {item.cover && (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={item.cover} alt={`${item.title} 封面`} className="aspect-[2/1] w-full object-cover" />
        )}
        <div className="p-6 sm:p-10">
          <time className="text-sm text-gray-400">{formatDate(item.created)}</time>
          <h1 className="mt-3 text-2xl font-bold leading-9 text-gray-900 dark:text-gray-100 sm:text-3xl">{item.title}</h1>
          <div
            className="prose prose-gray dark:prose-invert mt-6 max-w-none prose-a:text-brand-600"
            dangerouslySetInnerHTML={{ __html: item.contentHtml }}
          />
        </div>
      </article>

      {/* 上一篇 / 下一篇 */}
      {(newer || older) && (
        <nav className="mt-6 grid gap-3 sm:grid-cols-2" aria-label="上下篇导航">
          {newer ? (
            <Link
              href={`/news/${newer.slug}`}
              className="group rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 transition hover:border-brand-300"
            >
              <span className="flex items-center gap-1 text-xs text-gray-400">
                <ChevronLeft size={13} />
                上一篇
              </span>
              <span className="mt-1 line-clamp-1 text-sm font-medium text-gray-800 dark:text-gray-200 group-hover:text-brand-700 dark:group-hover:text-brand-300">
                {newer.title}
              </span>
            </Link>
          ) : (
            <span aria-hidden />
          )}
          {older && (
            <Link
              href={`/news/${older.slug}`}
              className="group rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 text-right transition hover:border-brand-300"
            >
              <span className="flex items-center justify-end gap-1 text-xs text-gray-400">
                下一篇
                <ChevronRight size={13} />
              </span>
              <span className="mt-1 line-clamp-1 text-sm font-medium text-gray-800 dark:text-gray-200 group-hover:text-brand-700 dark:group-hover:text-brand-300">
                {older.title}
              </span>
            </Link>
          )}
        </nav>
      )}

      {/* 相关资讯 */}
      {related.length > 0 && (
        <section className="mt-10">
          <h2 className="text-lg font-bold text-gray-900 dark:text-gray-100">相关资讯</h2>
          <div className="mt-4 space-y-3">
            {related.map((n) => (
              <Link
                key={n.cid}
                href={`/news/${n.slug}`}
                className="block rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 transition hover:border-brand-300 hover:shadow-sm"
              >
                <time className="text-xs text-gray-400">{formatDate(n.created)}</time>
                <p className="mt-1 font-medium text-gray-900 dark:text-gray-100">{n.title}</p>
              </Link>
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
