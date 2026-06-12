import Link from 'next/link';
import { ChevronRight } from 'lucide-react';

export default function SectionHeader({
  title,
  subtitle,
  moreHref,
  moreText = '查看全部',
}: {
  title: string;
  subtitle?: string;
  moreHref?: string;
  moreText?: string;
}) {
  return (
    <div className="mb-5 flex items-end justify-between">
      <div>
        <h2 className="text-xl font-bold text-gray-900 sm:text-2xl">{title}</h2>
        {subtitle && <p className="mt-1 text-sm text-gray-500">{subtitle}</p>}
      </div>
      {moreHref && (
        <Link
          href={moreHref}
          className="flex shrink-0 items-center gap-0.5 text-sm font-medium text-brand-600 hover:text-brand-700"
        >
          {moreText}
          <ChevronRight size={16} />
        </Link>
      )}
    </div>
  );
}
