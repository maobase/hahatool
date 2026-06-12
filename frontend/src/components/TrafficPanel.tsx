import { BarChart3, Globe2 } from 'lucide-react';
import { formatCount } from '@/lib/format';

/** 近 N 月的月份标签（最后一项为上月） */
function monthLabels(n: number): string[] {
  const now = new Date();
  return Array.from({ length: n }, (_, i) => {
    const d = new Date(now.getFullYear(), now.getMonth() - (n - i), 1);
    return `${d.getMonth() + 1}月`;
  });
}

/** 流量分析面板：访问量趋势柱状图（纯 SVG）+ 地区分布条形图 */
export default function TrafficPanel({
  visitsHistory,
  regions,
  name,
}: {
  visitsHistory: number[];
  regions: { name: string; pct: number }[];
  name: string;
}) {
  if (visitsHistory.length === 0 && regions.length === 0) return null;

  const max = Math.max(...visitsHistory, 1);
  const labels = monthLabels(visitsHistory.length);
  const W = 600;
  const H = 180;
  const PAD = 8;
  const barGap = 14;
  const barW = visitsHistory.length > 0 ? (W - PAD * 2 - barGap * (visitsHistory.length - 1)) / visitsHistory.length : 0;

  return (
    <section className="mt-8 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm sm:p-8" aria-label="流量分析">
      <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-gray-100">
        <BarChart3 size={18} className="text-brand-500" />
        {name} 流量分析
        <span className="text-xs font-normal text-gray-400">数据由运营整理，仅供参考</span>
      </h2>

      <div className="mt-6 grid gap-8 md:grid-cols-2">
        {/* 访问量趋势 */}
        {visitsHistory.length > 1 && (
          <div>
            <h3 className="text-sm font-medium text-gray-500">近 {visitsHistory.length} 月访问量趋势</h3>
            <svg
              viewBox={`0 0 ${W} ${H + 26}`}
              className="mt-3 w-full"
              role="img"
              aria-label={`近${visitsHistory.length}月访问量：${visitsHistory.map((v, i) => `${labels[i]} ${formatCount(v)}`).join('，')}`}
            >
              {visitsHistory.map((v, i) => {
                const h = Math.max(6, (v / max) * (H - 28));
                const x = PAD + i * (barW + barGap);
                const isLast = i === visitsHistory.length - 1;
                return (
                  <g key={i}>
                    <rect
                      x={x}
                      y={H - h}
                      width={barW}
                      height={h}
                      rx={6}
                      className={isLast ? 'fill-brand-500' : 'fill-brand-200'}
                    />
                    <text
                      x={x + barW / 2}
                      y={H - h - 8}
                      textAnchor="middle"
                      className="fill-gray-500 font-display"
                      fontSize="13"
                    >
                      {formatCount(v)}
                    </text>
                    <text x={x + barW / 2} y={H + 18} textAnchor="middle" className="fill-gray-400" fontSize="13">
                      {labels[i]}
                    </text>
                  </g>
                );
              })}
            </svg>
          </div>
        )}

        {/* 地区分布 */}
        {regions.length > 0 && (
          <div>
            <h3 className="flex items-center gap-1.5 text-sm font-medium text-gray-500">
              <Globe2 size={14} />
              主要地区分布
            </h3>
            <ul className="mt-3 space-y-2.5">
              {regions.map((r) => (
                <li key={r.name} className="flex items-center gap-3 text-sm">
                  <span className="w-12 shrink-0 text-gray-600 dark:text-gray-400">{r.name}</span>
                  <span className="h-2.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                    <span
                      className="block h-full rounded-full bg-gradient-to-r from-brand-400 to-brand-600"
                      style={{ width: `${Math.min(100, r.pct)}%` }}
                    />
                  </span>
                  <span className="w-12 shrink-0 text-right font-display text-xs font-semibold tabular-nums text-gray-700 dark:text-gray-300">
                    {r.pct}%
                  </span>
                </li>
              ))}
            </ul>
          </div>
        )}
      </div>
    </section>
  );
}
