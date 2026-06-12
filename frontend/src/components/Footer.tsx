import Link from 'next/link';
import { Sparkles } from 'lucide-react';
import type { Category } from '@/lib/types';
import { ADMIN_URL, SITE_NAME, SITE_SLOGAN } from '@/lib/site';

export default function Footer({ categories }: { categories: Category[] }) {
  return (
    <footer className="mt-16 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
      <div className="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-4">
        <div className="md:col-span-1">
          <div className="flex items-center gap-2">
            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white">
              <Sparkles size={15} />
            </span>
            <span className="font-bold text-gray-900 dark:text-gray-100">{SITE_NAME}</span>
          </div>
          <p className="mt-3 text-sm leading-6 text-gray-500">
            {SITE_SLOGAN}。精选全球优秀 AI 产品，让每个人都能找到合适的 AI 工具。
          </p>
        </div>

        <div>
          <h3 className="text-sm font-semibold text-gray-900 dark:text-gray-100">探索</h3>
          <ul className="mt-3 space-y-2 text-sm text-gray-500">
            <li><Link href="/tools" className="hover:text-brand-600 dark:hover:text-brand-400">全部工具</Link></li>
            <li><Link href="/ranking" className="hover:text-brand-600 dark:hover:text-brand-400">流量排行榜</Link></li>
            <li><Link href="/ranking?by=growth" className="hover:text-brand-600 dark:hover:text-brand-400">增长黑马榜</Link></li>
            <li><Link href="/compare" className="hover:text-brand-600 dark:hover:text-brand-400">工具 PK 对比</Link></li>
            <li><Link href="/favorites" className="hover:text-brand-600 dark:hover:text-brand-400">我的收藏</Link></li>
            <li><Link href="/flash" className="hover:text-brand-600 dark:hover:text-brand-400">AI 快讯</Link></li>
            <li><Link href="/news" className="hover:text-brand-600 dark:hover:text-brand-400">AI 资讯</Link></li>
          </ul>
        </div>

        <div>
          <h3 className="text-sm font-semibold text-gray-900 dark:text-gray-100">热门分类</h3>
          <ul className="mt-3 space-y-2 text-sm text-gray-500">
            {categories.slice(0, 6).map((c) => (
              <li key={c.slug}>
                <Link href={`/category/${c.slug}`} className="hover:text-brand-600 dark:hover:text-brand-400">{c.name}</Link>
              </li>
            ))}
          </ul>
        </div>

        <div>
          <h3 className="text-sm font-semibold text-gray-900 dark:text-gray-100">运营</h3>
          <ul className="mt-3 space-y-2 text-sm text-gray-500">
            <li><Link href="/submit" className="hover:text-brand-600 dark:hover:text-brand-400">提交工具</Link></li>
            <li>
              <a href={ADMIN_URL} target="_blank" rel="noopener noreferrer" className="hover:text-brand-600 dark:hover:text-brand-400">
                内容管理后台
              </a>
            </li>
          </ul>
        </div>
      </div>
      <div className="border-t border-gray-100 dark:border-gray-800 py-5 text-center text-xs text-gray-400">
        © {new Date().getFullYear()} {SITE_NAME} · 由 WordPress + Next.js 驱动 · 仅供学习交流
      </div>
    </footer>
  );
}
