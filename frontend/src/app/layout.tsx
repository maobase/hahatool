import type { Metadata } from 'next';
import { Space_Grotesk } from 'next/font/google';
import './globals.css';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { getToolCategories } from '@/lib/api';
import { SITE_DESCRIPTION, SITE_NAME, SITE_SLOGAN } from '@/lib/site';

// 数字与拉丁字符的 display 字体；中文回退系统字体
const displayFont = Space_Grotesk({ subsets: ['latin'], variable: '--font-display', display: 'swap' });

export const metadata: Metadata = {
  title: {
    default: `${SITE_NAME} - ${SITE_SLOGAN}`,
    template: `%s - ${SITE_NAME}`,
  },
  description: SITE_DESCRIPTION,
  icons: { icon: '/favicon.svg' },
};

export default async function RootLayout({ children }: { children: React.ReactNode }) {
  const categories = await getToolCategories();

  return (
    <html lang="zh-CN" className={displayFont.variable} suppressHydrationWarning>
      <head>
        {/* 首屏防闪烁：在水合前应用已保存的明暗模式与主题色 */}
        <script
          dangerouslySetInnerHTML={{
            __html: `(function(){try{var t=JSON.parse(localStorage.getItem('hahatool:theme')||'{}');var m=t.mode||'system';var d=m==='dark'||(m==='system'&&matchMedia('(prefers-color-scheme: dark)').matches);document.documentElement.classList.toggle('dark',d);if(t.accent)document.documentElement.dataset.accent=t.accent;}catch(e){}})()`,
          }}
        />
      </head>
      <body className="flex min-h-screen flex-col">
        <Header categories={categories} />
        <main className="flex-1">{children}</main>
        <Footer categories={categories} />
      </body>
    </html>
  );
}
