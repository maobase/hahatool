const STYLES: Record<string, string> = {
  免费: 'bg-emerald-50 text-emerald-700',
  免费增值: 'bg-sky-50 text-sky-700',
  付费: 'bg-amber-50 text-amber-700',
};

export default function PricingBadge({ pricing }: { pricing: string }) {
  if (!pricing || pricing === '未知') return null;
  return (
    <span className={`rounded-full px-2 py-0.5 ${STYLES[pricing] ?? 'bg-gray-100 text-gray-600'}`}>
      {pricing}
    </span>
  );
}
