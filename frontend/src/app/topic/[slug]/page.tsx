import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ChevronLeft, Layers } from 'lucide-react';
import { getTopicBySlug } from '@/lib/api';
import EmptyState from '@/components/EmptyState';
import ToolGrid from '@/components/ToolGrid';

export const revalidate = 60;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const data = await getTopicBySlug(slug);
  if (!data) return { title: '专题' };
  const title = `${data.topic.name} - 专题`;
  const image = data.topic.cover || undefined;
  return {
    title,
    description: data.topic.description,
    openGraph: {
      title,
      description: data.topic.description,
      ...(image && { images: [{ url: image }] }),
    },
    ...(image && { twitter: { card: 'summary_large_image', title, images: [image] } }),
  };
}

export default async function TopicDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const data = await getTopicBySlug(slug);
  if (!data) notFound();
  const { topic, tools } = data;

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <Link href="/topics" className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-600 dark:hover:text-brand-400">
        <ChevronLeft size={16} />
        全部专题
      </Link>

      <header
        className="relative mt-6 overflow-hidden rounded-2xl p-8 text-white shadow-md sm:p-10"
        style={
          topic.cover
            ? { backgroundImage: `linear-gradient(120deg, rgba(3,7,18,.62), rgba(3,7,18,.86)), url(${topic.cover})`, backgroundSize: 'cover', backgroundPosition: 'center' }
            : undefined
        }
      >
        {!topic.cover && <div aria-hidden className="absolute inset-0 bg-gradient-to-br from-brand-700 to-brand-900" />}
        <div className="relative">
          <span className="rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-medium">专题</span>
          <h1 className="mt-3 text-2xl font-bold sm:text-3xl">{topic.name}</h1>
          {topic.description && <p className="mt-2 max-w-2xl text-sm leading-6 text-white/80">{topic.description}</p>}
          <span className="mt-4 inline-flex items-center gap-1.5 text-xs text-white/70">
            <Layers size={14} />
            {topic.count} 个工具
          </span>
        </div>
      </header>

      <div className="mt-8">
        {tools.length === 0 ? <EmptyState title="该专题暂无内容" /> : <ToolGrid tools={tools} />}
      </div>
    </div>
  );
}
