import { Star } from 'lucide-react';

/** 星级评分（双层填充，支持小数） */
export default function RatingStars({
  rating,
  size = 14,
  showValue = true,
}: {
  rating: number;
  size?: number;
  showValue?: boolean;
}) {
  if (!rating) return null;
  const pct = Math.min(100, (rating / 5) * 100);

  return (
    <span className="inline-flex items-center gap-1" title={`评分 ${rating} / 5`}>
      <span className="relative inline-flex" aria-hidden>
        <span className="flex text-gray-200">
          {Array.from({ length: 5 }).map((_, i) => (
            <Star key={i} size={size} className="fill-current" strokeWidth={0} />
          ))}
        </span>
        <span className="absolute inset-0 flex overflow-hidden text-amber-400" style={{ width: `${pct}%` }}>
          {Array.from({ length: 5 }).map((_, i) => (
            <Star key={i} size={size} className="shrink-0 fill-current" strokeWidth={0} />
          ))}
        </span>
      </span>
      {showValue && <span className="text-xs font-semibold tabular-nums text-amber-500">{rating.toFixed(1)}</span>}
    </span>
  );
}
