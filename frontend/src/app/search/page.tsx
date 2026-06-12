import type { Metadata } from 'next';
import Link from 'next/link';
import { searchPosts } from '@/lib/api';
import { formatDate } from '@/lib/format';
import EmptyState from '@/components/EmptyState';
import SearchBox from '@/components/SearchBox';
import ToolGrid from '@/components/ToolGrid';

export const metadata: Metadata = {
  title: '搜索',
};

export default async function SearchPage({
  searchParams,
}: {
  searchParams: Promise<{ q?: string }>;
}) {
  const { q } = await searchParams;
  const keyword = (q ?? '').trim();
  const { tools, news, count } = keyword
    ? await searchPosts(keyword, 1, 60)
    : { tools: [], news: [], count: 0 };

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">搜索</h1>

      <div className="mt-6 max-w-2xl">
        <SearchBox variant="hero" defaultValue={keyword} />
      </div>

      {keyword && (
        <p className="mt-6 text-sm text-gray-500">
          「<span className="font-medium text-gray-900 dark:text-gray-100">{keyword}</span>」共找到 {count} 条结果
        </p>
      )}

      {keyword && tools.length === 0 && news.length === 0 && (
        <div className="mt-8">
          <EmptyState title="没有找到相关结果" hint="换个关键词试试，比如「写作」「视频」「编程」。" />
        </div>
      )}

      {tools.length > 0 && (
        <section className="mt-8">
          <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">工具（{tools.length}）</h2>
          <ToolGrid tools={tools} />
        </section>
      )}

      {news.length > 0 && (
        <section className="mt-10">
          <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">资讯（{news.length}）</h2>
          <div className="space-y-3">
            {news.map((item) => (
              <Link
                key={item.cid}
                href={`/news/${item.slug}`}
                className="block rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm transition hover:border-brand-300 hover:shadow-md"
              >
                <time className="text-xs text-gray-400">{formatDate(item.created)}</time>
                <h3 className="mt-1 font-semibold text-gray-900 dark:text-gray-100">{item.title}</h3>
                <p className="mt-1 line-clamp-2 text-sm text-gray-500">{item.digest}</p>
              </Link>
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
