import type { Metadata } from 'next';
import Link from 'next/link';
import { ArrowUpRight, Swords } from 'lucide-react';
import { getAllTools } from '@/lib/api';
import { formatCount, formatDate } from '@/lib/format';
import type { Tool } from '@/lib/types';
import GrowthBadge from '@/components/GrowthBadge';
import RadarChart from '@/components/RadarChart';
import RatingStars from '@/components/RatingStars';
import ToolLogo from '@/components/ToolLogo';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'AI 工具 PK 对比',
  description: '任选两款 AI 工具，能力雷达、流量、评分、定价一屏对比。',
};

const COLOR_A = '#7c3aed';
const COLOR_B = '#f59e0b';

function Winner({ a, b, higherWins = true }: { a: number; b: number; higherWins?: boolean }) {
  if (!a || !b || a === b) return null;
  const aWins = higherWins ? a > b : a < b;
  return (
    <span
      className={`ml-1.5 rounded-full px-1.5 py-0.5 text-[10px] font-bold text-white ${aWins ? 'bg-brand-500' : 'bg-amber-500'}`}
    >
      {aWins ? 'A 胜' : 'B 胜'}
    </span>
  );
}

export default async function ComparePage({
  searchParams,
}: {
  searchParams: Promise<{ a?: string; b?: string }>;
}) {
  const { a, b } = await searchParams;
  const tools = await getAllTools();
  const byVisits = [...tools].sort((x, y) => y.monthlyVisits - x.monthlyVisits);

  const toolA = tools.find((t) => t.slug === a) ?? byVisits[0];
  const toolB = tools.find((t) => t.slug === b && t.slug !== toolA?.slug) ?? byVisits.find((t) => t.cid !== toolA?.cid);

  if (!toolA || !toolB) {
    return (
      <div className="mx-auto max-w-4xl px-4 py-16 text-center text-gray-500 sm:px-6">
        暂无足够的工具数据可供对比。
      </div>
    );
  }

  const rows: { label: string; va: string; vb: string; na?: number; nb?: number; higherWins?: boolean }[] = [
    { label: '月访问量', va: formatCount(toolA.monthlyVisits), vb: formatCount(toolB.monthlyVisits), na: toolA.monthlyVisits, nb: toolB.monthlyVisits },
    { label: '月增长率', va: `${toolA.growth > 0 ? '+' : ''}${toolA.growth}%`, vb: `${toolB.growth > 0 ? '+' : ''}${toolB.growth}%`, na: toolA.growth, nb: toolB.growth },
    { label: '收藏数', va: formatCount(toolA.likes), vb: formatCount(toolB.likes), na: toolA.likes, nb: toolB.likes },
    { label: '定价模式', va: toolA.pricing, vb: toolB.pricing },
    { label: '分类', va: toolA.categories.map((c) => c.name).join(' / ') || '—', vb: toolB.categories.map((c) => c.name).join(' / ') || '—' },
    { label: '标签', va: toolA.tags.map((t) => `#${t.name}`).join(' ') || '—', vb: toolB.tags.map((t) => `#${t.name}`).join(' ') || '—' },
    { label: '收录时间', va: formatDate(toolA.created), vb: formatDate(toolB.created) },
  ];

  const pickerOptions = [...tools].sort((x, y) => x.title.localeCompare(y.title, 'zh'));

  return (
    <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-gray-900 sm:text-3xl">
        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-900 text-white">
          <Swords size={18} />
        </span>
        工具 PK
      </h1>
      <p className="mt-2 text-sm text-gray-500">任选两款工具，能力雷达、流量与定价一屏对比</p>

      {/* 选择器（纯表单，无 JS 依赖） */}
      <form action="/compare" className="mt-6 flex flex-wrap items-end gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div className="min-w-40 flex-1">
          <label htmlFor="pick-a" className="mb-1 block text-xs font-medium text-gray-500">
            <span className="mr-1 inline-block h-2.5 w-2.5 rounded-full align-middle" style={{ background: COLOR_A }} />
            选手 A
          </label>
          <select
            id="pick-a"
            name="a"
            defaultValue={toolA.slug}
            className="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-brand-400"
          >
            {pickerOptions.map((t) => (
              <option key={t.cid} value={t.slug}>{t.title}</option>
            ))}
          </select>
        </div>
        <span className="pb-2.5 font-display text-sm font-bold text-gray-400">VS</span>
        <div className="min-w-40 flex-1">
          <label htmlFor="pick-b" className="mb-1 block text-xs font-medium text-gray-500">
            <span className="mr-1 inline-block h-2.5 w-2.5 rounded-full align-middle" style={{ background: COLOR_B }} />
            选手 B
          </label>
          <select
            id="pick-b"
            name="b"
            defaultValue={toolB.slug}
            className="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-brand-400"
          >
            {pickerOptions.map((t) => (
              <option key={t.cid} value={t.slug}>{t.title}</option>
            ))}
          </select>
        </div>
        <button
          type="submit"
          className="min-h-11 rounded-xl bg-brand-600 px-6 text-sm font-semibold text-white transition hover:bg-brand-700"
        >
          开始对比
        </button>
      </form>

      {/* 对阵卡 */}
      <div className="mt-8 grid items-stretch gap-4 sm:grid-cols-2">
        {[
          { tool: toolA, color: COLOR_A, side: 'A' },
          { tool: toolB, color: COLOR_B, side: 'B' },
        ].map(({ tool, color, side }) => (
          <div key={tool.cid} className="rounded-2xl border-2 bg-white p-6 shadow-sm" style={{ borderColor: color }}>
            <div className="flex items-center gap-3">
              <ToolLogo src={tool.logo} name={tool.title} size={52} />
              <div className="min-w-0 flex-1">
                <p className="flex items-center gap-2">
                  <span className="rounded px-1.5 font-display text-xs font-bold text-white" style={{ background: color }}>
                    {side}
                  </span>
                  <Link href={`/tool/${tool.slug}`} className="truncate font-bold text-gray-900 hover:underline">
                    {tool.title}
                  </Link>
                </p>
                <RatingStars rating={tool.rating} size={13} />
              </div>
              <GrowthBadge growth={tool.growth} />
            </div>
            <p className="mt-3 line-clamp-2 min-h-10 text-sm text-gray-600">{tool.tagline}</p>
            <a
              href={tool.url}
              target="_blank"
              rel="noopener noreferrer nofollow"
              className="mt-3 inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:underline"
            >
              访问官网
              <ArrowUpRight size={14} />
            </a>
          </div>
        ))}
      </div>

      <div className="mt-8 grid gap-8 lg:grid-cols-2">
        {/* 能力雷达 */}
        {(toolA.scores.length >= 3 || toolB.scores.length >= 3) && (
          <section className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 className="font-bold text-gray-900">能力雷达对比</h2>
            <p className="mt-1 text-xs text-gray-400">五维评分（0-10），由运营团队评估</p>
            <RadarChart
              series={[
                { name: toolA.title, color: COLOR_A, scores: toolA.scores },
                { name: toolB.title, color: COLOR_B, scores: toolB.scores },
              ].filter((s) => s.scores.length >= 3)}
            />
            <div className="flex justify-center gap-6 text-xs text-gray-500">
              <span className="flex items-center gap-1.5">
                <span className="h-2.5 w-2.5 rounded-full" style={{ background: COLOR_A }} />
                {toolA.title}
              </span>
              <span className="flex items-center gap-1.5">
                <span className="h-2.5 w-2.5 rounded-full" style={{ background: COLOR_B }} />
                {toolB.title}
              </span>
            </div>
          </section>
        )}

        {/* 数据对比表 */}
        <section className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
          <h2 className="font-bold text-gray-900">数据对比</h2>
          <table className="mt-4 w-full text-sm">
            <thead>
              <tr className="text-left text-xs text-gray-400">
                <th className="w-20 py-2 font-medium">指标</th>
                <th className="py-2 font-medium" style={{ color: COLOR_A }}>{toolA.title}</th>
                <th className="py-2 font-medium" style={{ color: COLOR_B }}>{toolB.title}</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              <tr>
                <td className="py-2.5 text-gray-500">评分</td>
                <td className="py-2.5"><RatingStars rating={toolA.rating} size={12} /><Winner a={toolA.rating} b={toolB.rating} /></td>
                <td className="py-2.5"><RatingStars rating={toolB.rating} size={12} /></td>
              </tr>
              {rows.map((row) => (
                <tr key={row.label}>
                  <td className="py-2.5 align-top text-gray-500">{row.label}</td>
                  <td className="py-2.5 align-top font-medium tabular-nums text-gray-800">
                    {row.va}
                    {row.na !== undefined && row.nb !== undefined && (
                      <Winner a={row.na} b={row.nb} higherWins={row.higherWins ?? true} />
                    )}
                  </td>
                  <td className="py-2.5 align-top font-medium tabular-nums text-gray-800">{row.vb}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </section>
      </div>

      <p className="mt-6 text-center text-xs text-gray-400">
        数据由运营团队整理，仅供参考 · 想对比其他工具？在上方选择器中任意切换
      </p>
    </div>
  );
}
