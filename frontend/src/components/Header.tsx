'use client';

import { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { ChevronDown, Menu, Sparkles, X } from 'lucide-react';
import type { Category } from '@/lib/types';
import { SITE_NAME } from '@/lib/site';
import SearchBox from './SearchBox';
import FavNavLink from './FavNavLink';
import ThemeSwitcher from './ThemeSwitcher';

const NAV = [
  { href: '/', label: '首页' },
  { href: '/tools', label: '工具库' },
  { href: '/ranking', label: '排行榜' },
  { href: '/compare', label: '工具PK' },
  { href: '/prompts', label: '提示词' },
  { href: '/flash', label: 'AI快讯' },
  { href: '/news', label: 'AI资讯' },
  { href: '/topics', label: '专题' },
];

export default function Header({ categories }: { categories: Category[] }) {
  const [mobileOpen, setMobileOpen] = useState(false);
  const pathname = usePathname();

  const linkCls = (href: string) => {
    const active = href === '/' ? pathname === '/' : pathname.startsWith(href);
    return `rounded-lg px-3 py-2 text-sm font-medium transition ${
      active ? 'text-brand-700 dark:text-brand-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100'
    }`;
  };

  return (
    <header className="sticky top-0 z-50 border-b border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-950/90 backdrop-blur">
      <div className="mx-auto flex h-16 max-w-7xl items-center gap-2 px-4 sm:px-6">
        {/* Logo */}
        <Link href="/" className="flex shrink-0 items-center gap-2" aria-label={`${SITE_NAME} 首页`}>
          <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white">
            <Sparkles size={18} />
          </span>
          <span className="text-lg font-bold tracking-tight text-gray-900 dark:text-gray-100">
            {SITE_NAME}
            <span className="ml-1 hidden text-xs font-normal text-gray-400 sm:inline">哈哈工具</span>
          </span>
        </Link>

        {/* 桌面导航 */}
        <nav className="ml-4 hidden items-center md:flex" aria-label="主导航">
          {NAV.map((item) => (
            <Link key={item.href} href={item.href} className={linkCls(item.href)}>
              {item.label}
            </Link>
          ))}
          {/* 分类下拉 */}
          <div className="group relative">
            <button
              type="button"
              className="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 transition hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100"
            >
              分类
              <ChevronDown size={14} className="transition group-hover:rotate-180" />
            </button>
            <div className="invisible absolute left-0 top-full z-50 w-64 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-2 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100">
              <div className="grid grid-cols-1 gap-0.5">
                {categories.map((c) => (
                  <Link
                    key={c.slug}
                    href={`/category/${c.slug}`}
                    className="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/30 hover:text-brand-700 dark:hover:text-brand-300"
                  >
                    {c.name}
                    <span className="text-xs text-gray-400">{c.count}</span>
                  </Link>
                ))}
              </div>
            </div>
          </div>
        </nav>

        {/* 搜索（桌面） */}
        <div className="ml-auto hidden w-full max-w-xs lg:block">
          <SearchBox variant="header" />
        </div>

        <div className="ml-auto hidden items-center gap-1 md:flex lg:ml-3">
          <ThemeSwitcher />
          <FavNavLink />
          <Link
            href="/submit"
            className="shrink-0 rounded-xl bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-brand-700"
          >
            提交工具
          </Link>
        </div>

        {/* 移动端按钮 */}
        <span className="ml-auto flex items-center md:hidden">
          <ThemeSwitcher />
          <FavNavLink />
        </span>
        <button
          type="button"
          onClick={() => setMobileOpen((v) => !v)}
          aria-label={mobileOpen ? '关闭菜单' : '打开菜单'}
          aria-expanded={mobileOpen}
          className="rounded-lg p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 md:hidden"
        >
          {mobileOpen ? <X size={22} /> : <Menu size={22} />}
        </button>
      </div>

      {/* 移动端菜单 */}
      {mobileOpen && (
        <div className="border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 pb-4 md:hidden">
          <div className="mt-3">
            <SearchBox variant="header" />
          </div>
          <nav className="mt-3 flex flex-col" aria-label="移动端导航">
            {NAV.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                onClick={() => setMobileOpen(false)}
                className="rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
              >
                {item.label}
              </Link>
            ))}
            <Link
              href="/submit"
              onClick={() => setMobileOpen(false)}
              className="mt-2 rounded-xl bg-brand-600 px-4 py-2.5 text-center text-sm font-medium text-white"
            >
              提交工具
            </Link>
          </nav>
          <div className="mt-4 border-t border-gray-100 dark:border-gray-800 pt-3">
            <p className="px-3 pb-1 text-xs font-medium text-gray-400">分类</p>
            <div className="grid grid-cols-2 gap-1">
              {categories.map((c) => (
                <Link
                  key={c.slug}
                  href={`/category/${c.slug}`}
                  onClick={() => setMobileOpen(false)}
                  className="rounded-lg px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 hover:text-brand-700 dark:hover:text-brand-300"
                >
                  {c.name}
                </Link>
              ))}
            </div>
          </div>
        </div>
      )}
    </header>
  );
}
