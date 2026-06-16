import Link from 'next/link';

export default function NotFound() {
  return (
    <div className="flex flex-col items-center justify-center px-4 py-32 text-center">
      <p className="text-6xl font-extrabold text-brand-200">404</p>
      <h1 className="mt-4 text-xl font-bold text-gray-900 dark:text-gray-100">页面不存在</h1>
      <p className="mt-2 text-sm text-gray-500">你访问的内容可能已被删除或尚未收录。</p>
      <div className="mt-6 flex flex-wrap justify-center gap-3">
        <Link
          href="/"
          className="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-700"
        >
          返回首页
        </Link>
        <Link
          href="/tools"
          className="rounded-xl border border-gray-200 dark:border-gray-700 px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 transition hover:border-brand-300 hover:text-brand-700 dark:hover:text-brand-300"
        >
          浏览工具库
        </Link>
      </div>
    </div>
  );
}
