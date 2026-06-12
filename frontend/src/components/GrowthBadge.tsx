import { TrendingDown, TrendingUp } from 'lucide-react';

/** 月增长率徽章：正绿负红，0 或缺省不显示 */
export default function GrowthBadge({ growth, className = '' }: { growth: number; className?: string }) {
  if (!growth) return null;
  const up = growth > 0;
  return (
    <span
      title="月访问量环比增长"
      className={`inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[11px] font-semibold tabular-nums ${
        up
          ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300'
          : 'bg-red-50 text-red-500 dark:bg-red-900/40 dark:text-red-300'
      } ${className}`}
    >
      {up ? <TrendingUp size={11} /> : <TrendingDown size={11} />}
      {up ? '+' : ''}
      {growth}%
    </span>
  );
}
