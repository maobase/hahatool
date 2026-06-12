/** 全局路由加载骨架 */
export default function Loading() {
  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6" aria-busy="true" aria-label="加载中">
      <div className="h-8 w-48 animate-pulse rounded-lg bg-gray-200 dark:bg-gray-800" />
      <div className="mt-3 h-4 w-72 animate-pulse rounded bg-gray-100 dark:bg-gray-800/60" />
      <div className="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {Array.from({ length: 8 }).map((_, i) => (
          <div key={i} className="h-44 animate-pulse rounded-2xl bg-gray-100 dark:bg-gray-800/60" />
        ))}
      </div>
    </div>
  );
}
