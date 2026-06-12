const STYLES: Record<string, string> = {
  免费: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
  免费增值: 'bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
  付费: 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
};

export default function PricingBadge({ pricing }: { pricing: string }) {
  if (!pricing || pricing === '未知') return null;
  return (
    <span className={`rounded-full px-2 py-0.5 ${STYLES[pricing] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'}`}>
      {pricing}
    </span>
  );
}
