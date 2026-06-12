import Link from 'next/link';
import { ArrowUpRight, Bookmark, TrendingUp } from 'lucide-react';
import type { Tool } from '@/lib/types';
import { formatCount } from '@/lib/format';
import ToolLogo from './ToolLogo';
import PricingBadge from './PricingBadge';
import GrowthBadge from './GrowthBadge';
import FavoriteButton from './FavoriteButton';
import RatingStars from './RatingStars';

export default function ToolCard({ tool, rank }: { tool: Tool; rank?: number }) {
  return (
    <div className="group relative flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lg hover:shadow-brand-100/60">
      <Link
        href={`/tool/${tool.slug}`}
        className="absolute inset-0 rounded-2xl"
        aria-label={`查看 ${tool.title} 详情`}
      />
      {rank !== undefined && (
        <span className="absolute -left-2 -top-2 flex h-7 w-7 items-center justify-center rounded-lg bg-gray-900 font-display text-xs font-bold text-white shadow">
          {rank}
        </span>
      )}
      <div className="flex items-start gap-3">
        <ToolLogo src={tool.logo} name={tool.title} size={48} className="ring-1 ring-gray-100" />
        <div className="min-w-0 flex-1">
          <div className="flex items-center gap-2">
            <h3 className="truncate font-semibold text-gray-900 group-hover:text-brand-700">{tool.title}</h3>
            <GrowthBadge growth={tool.growth} />
          </div>
          <div className="mt-1 flex items-center gap-2 text-xs text-gray-500">
            {tool.categories
              .filter((c) => c.slug !== 'ai-news')
              .slice(0, 1)
              .map((c) => (
                <Link
                  key={c.slug}
                  href={`/category/${c.slug}`}
                  className="relative z-10 rounded-full bg-gray-100 px-2 py-0.5 hover:bg-brand-100 hover:text-brand-700"
                >
                  {c.name}
                </Link>
              ))}
            <PricingBadge pricing={tool.pricing} />
          </div>
        </div>
      </div>
      <p className="mt-3 line-clamp-2 min-h-10 text-sm leading-5 text-gray-600">{tool.tagline || '——'}</p>
      <div className="mt-4 flex items-center gap-3 border-t border-gray-100 pt-2.5 text-xs text-gray-500">
        {tool.rating > 0 && <RatingStars rating={tool.rating} size={12} />}
        <span className="flex items-center gap-1 tabular-nums" title="收藏数">
          <Bookmark size={14} className="text-brand-400" />
          {formatCount(tool.likes)}
        </span>
        <span className="flex items-center gap-1 tabular-nums" title="月访问量">
          <TrendingUp size={14} className="text-emerald-500" />
          {formatCount(tool.monthlyVisits)}
        </span>
        <span className="ml-auto flex items-center">
          <FavoriteButton cid={tool.cid} />
          <a
            href={tool.url}
            target="_blank"
            rel="noopener noreferrer nofollow"
            aria-label={`访问 ${tool.title} 官网`}
            title="访问官网"
            className="relative z-10 flex min-h-9 min-w-9 items-center justify-center rounded-lg text-gray-300 transition hover:bg-brand-50 hover:text-brand-600"
          >
            <ArrowUpRight size={16} />
          </a>
        </span>
      </div>
    </div>
  );
}
