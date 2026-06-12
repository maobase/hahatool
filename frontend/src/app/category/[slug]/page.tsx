import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { getToolCategories, getToolsByCategory } from '@/lib/api';
import EmptyState from '@/components/EmptyState';
import Pagination from '@/components/Pagination';
import ToolGrid from '@/components/ToolGrid';

export const revalidate = 60;

const PAGE_SIZE = 24;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const category = (await getToolCategories()).find((c) => c.slug === slug);
  return {
    title: category ? `${category.name} AI 工具` : '分类',
    description: category?.description,
  };
}

export default async function CategoryPage({
  params,
  searchParams,
}: {
  params: Promise<{ slug: string }>;
  searchParams: Promise<{ page?: string }>;
}) {
  const { slug } = await params;
  const { page: pageParam } = await searchParams;
  const page = Math.max(1, Number(pageParam) || 1);

  const categories = await getToolCategories();
  const category = categories.find((c) => c.slug === slug);
  if (!category) notFound();

  const { tools, pages, count } = await getToolsByCategory(slug, page, PAGE_SIZE);

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <h1 className="text-2xl font-bold text-gray-900 sm:text-3xl">{category.name}</h1>
      <p className="mt-2 text-sm text-gray-500">
        {category.description} · 共 {count} 款工具
      </p>

      <div className="mt-8">
        {tools.length === 0 ? <EmptyState title="该分类下暂无工具" /> : <ToolGrid tools={tools} />}
      </div>

      <Pagination current={page} total={pages} basePath={`/category/${slug}`} />
    </div>
  );
}
