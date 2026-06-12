import Link from 'next/link';

export default function NotFound() {
  return (
    <div className="flex flex-col items-center justify-center px-4 py-32 text-center">
      <p className="text-6xl font-extrabold text-brand-200">404</p>
      <h1 className="mt-4 text-xl font-bold text-gray-900">页面不存在</h1>
      <p className="mt-2 text-sm text-gray-500">你访问的内容可能已被删除或尚未收录。</p>
      <Link
        href="/"
        className="mt-6 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-700"
      >
        返回首页
      </Link>
    </div>
  );
}
