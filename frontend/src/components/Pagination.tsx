import Link from 'next/link';
import { ChevronLeft, ChevronRight } from 'lucide-react';

/** 基于查询参数的分页（服务端渲染，无 JS 依赖） */
export default function Pagination({
  current,
  total,
  basePath,
  params = {},
}: {
  current: number;
  total: number;
  basePath: string;
  params?: Record<string, string>;
}) {
  if (total <= 1) return null;

  const href = (page: number) => {
    const qs = new URLSearchParams({ ...params, page: String(page) });
    return `${basePath}?${qs.toString()}`;
  };

  const pages: number[] = [];
  for (let p = Math.max(1, current - 2); p <= Math.min(total, current + 2); p++) pages.push(p);

  const itemCls = 'flex h-9 min-w-9 items-center justify-center rounded-lg px-2 text-sm transition';

  return (
    <nav aria-label="分页" className="mt-8 flex items-center justify-center gap-1.5">
      {current > 1 && (
        <Link href={href(current - 1)} aria-label="上一页" className={`${itemCls} text-gray-600 hover:bg-gray-100`}>
          <ChevronLeft size={16} />
        </Link>
      )}
      {pages[0] > 1 && (
        <>
          <Link href={href(1)} className={`${itemCls} text-gray-600 hover:bg-gray-100`}>1</Link>
          {pages[0] > 2 && <span className="px-1 text-gray-400">…</span>}
        </>
      )}
      {pages.map((p) =>
        p === current ? (
          <span key={p} aria-current="page" className={`${itemCls} bg-brand-600 font-medium text-white`}>
            {p}
          </span>
        ) : (
          <Link key={p} href={href(p)} className={`${itemCls} text-gray-600 hover:bg-gray-100`}>
            {p}
          </Link>
        ),
      )}
      {pages[pages.length - 1] < total && (
        <>
          {pages[pages.length - 1] < total - 1 && <span className="px-1 text-gray-400">…</span>}
          <Link href={href(total)} className={`${itemCls} text-gray-600 hover:bg-gray-100`}>{total}</Link>
        </>
      )}
      {current < total && (
        <Link href={href(current + 1)} aria-label="下一页" className={`${itemCls} text-gray-600 hover:bg-gray-100`}>
          <ChevronRight size={16} />
        </Link>
      )}
    </nav>
  );
}
