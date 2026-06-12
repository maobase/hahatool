import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { Hash } from 'lucide-react';
import { getTags, getToolsByTag } from '@/lib/api';
import EmptyState from '@/components/EmptyState';
import Pagination from '@/components/Pagination';
import ToolGrid from '@/components/ToolGrid';

export const revalidate = 60;

const PAGE_SIZE = 24;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const tag = (await getTags()).find((t) => t.slug === slug);
  return { title: tag ? `${tag.name} - 相关 AI 工具` : '标签' };
}

export default async function TagPage({
  params,
  searchParams,
}: {
  params: Promise<{ slug: string }>;
  searchParams: Promise<{ page?: string }>;
}) {
  const { slug } = await params;
  const { page: pageParam } = await searchParams;
  const page = Math.max(1, Number(pageParam) || 1);

  const tags = await getTags();
  const tag = tags.find((t) => t.slug === slug);
  if (!tag) notFound();

  const { tools, pages, count } = await getToolsByTag(slug, page, PAGE_SIZE);
  const otherTags = tags.filter((t) => t.slug !== slug).slice(0, 12);

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-gray-900 sm:text-3xl">
        <Hash size={26} className="text-brand-500" />
        {tag.name}
      </h1>
      <p className="mt-2 text-sm text-gray-500">共 {count} 款「{tag.name}」相关工具</p>

      {otherTags.length > 0 && (
        <div className="mt-5 flex flex-wrap gap-2">
          {otherTags.map((t) => (
            <Link
              key={t.slug}
              href={`/tag/${t.slug}`}
              className="rounded-full bg-white px-3 py-1 text-sm text-gray-600 ring-1 ring-gray-200 transition hover:text-brand-600 hover:ring-brand-300"
            >
              # {t.name}
              <span className="ml-1 text-xs text-gray-400">{t.count}</span>
            </Link>
          ))}
        </div>
      )}

      <div className="mt-8">
        {tools.length === 0 ? <EmptyState title="该标签下暂无工具" /> : <ToolGrid tools={tools} />}
      </div>

      <Pagination current={page} total={pages} basePath={`/tag/${slug}`} />
    </div>
  );
}
