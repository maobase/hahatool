/** 能力雷达图（纯 SVG 服务端渲染），支持 1-2 个系列叠加对比 */

export interface RadarSeries {
  name: string;
  color: string; // tailwind 无关的原始色值，写入 SVG
  scores: { label: string; value: number }[];
}

const MAX = 10;

function polygonPoints(values: number[], cx: number, cy: number, r: number): string {
  const n = values.length;
  return values
    .map((v, i) => {
      const angle = (Math.PI * 2 * i) / n - Math.PI / 2;
      const radius = (Math.min(v, MAX) / MAX) * r;
      return `${(cx + radius * Math.cos(angle)).toFixed(1)},${(cy + radius * Math.sin(angle)).toFixed(1)}`;
    })
    .join(' ');
}

export default function RadarChart({ series, size = 300 }: { series: RadarSeries[]; size?: number }) {
  const base = series[0]?.scores ?? [];
  if (base.length < 3) return null;
  const labels = base.map((s) => s.label);
  const n = labels.length;
  const cx = size / 2;
  const cy = size / 2 + 6;
  const r = size / 2 - 44;

  const axisPoint = (i: number, radius: number) => {
    const angle = (Math.PI * 2 * i) / n - Math.PI / 2;
    return [cx + radius * Math.cos(angle), cy + radius * Math.sin(angle)] as const;
  };

  const desc = series
    .map((s) => `${s.name}：${s.scores.map((x) => `${x.label}${x.value}`).join('、')}`)
    .join('；');

  return (
    <svg viewBox={`0 0 ${size} ${size + 10}`} className="w-full" role="img" aria-label={`能力雷达图。${desc}`}>
      {/* 网格环 */}
      {[0.25, 0.5, 0.75, 1].map((f) => (
        <polygon
          key={f}
          points={polygonPoints(Array(n).fill(MAX * f), cx, cy, r)}
          fill={f === 1 ? 'rgba(124,58,237,0.04)' : 'none'}
          stroke="#e5e7eb"
          strokeWidth="1"
        />
      ))}
      {/* 轴线与标签 */}
      {labels.map((label, i) => {
        const [x2, y2] = axisPoint(i, r);
        const [lx, ly] = axisPoint(i, r + 22);
        return (
          <g key={label}>
            <line x1={cx} y1={cy} x2={x2} y2={y2} stroke="#e5e7eb" strokeWidth="1" />
            <text x={lx} y={ly} textAnchor="middle" dominantBaseline="middle" fontSize="12" fill="#6b7280">
              {label}
            </text>
          </g>
        );
      })}
      {/* 数据多边形 */}
      {series.map((s) => (
        <g key={s.name}>
          <polygon
            points={polygonPoints(labels.map((l) => s.scores.find((x) => x.label === l)?.value ?? 0), cx, cy, r)}
            fill={s.color}
            fillOpacity="0.18"
            stroke={s.color}
            strokeWidth="2"
            strokeLinejoin="round"
          />
          {labels.map((l, i) => {
            const v = s.scores.find((x) => x.label === l)?.value ?? 0;
            const [px, py] = axisPoint(i, (Math.min(v, MAX) / MAX) * r);
            return <circle key={l} cx={px} cy={py} r="3" fill={s.color} />;
          })}
        </g>
      ))}
    </svg>
  );
}
