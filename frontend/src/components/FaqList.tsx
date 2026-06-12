import { HelpCircle } from 'lucide-react';

/** 常见问题（原生 details/summary 折叠，无 JS 依赖） */
export default function FaqList({ faq, name }: { faq: { q: string; a: string }[]; name: string }) {
  if (faq.length === 0) return null;
  return (
    <section className="mt-8 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm sm:p-8" aria-label="常见问题">
      <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-gray-100">
        <HelpCircle size={18} className="text-brand-500" />
        关于 {name} 的常见问题
      </h2>
      <div className="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
        {faq.map((item) => (
          <details key={item.q} className="group py-3">
            <summary className="flex cursor-pointer list-none items-center justify-between gap-3 font-medium text-gray-800 dark:text-gray-200 transition hover:text-brand-700 dark:hover:text-brand-300 [&::-webkit-details-marker]:hidden">
              {item.q}
              <span className="shrink-0 text-gray-300 transition group-open:rotate-45" aria-hidden>
                ＋
              </span>
            </summary>
            <p className="mt-2 text-sm leading-7 text-gray-600 dark:text-gray-400">{item.a}</p>
          </details>
        ))}
      </div>
    </section>
  );
}
