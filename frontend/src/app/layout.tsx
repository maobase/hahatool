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
    <html lang="zh-CN" className={displayFont.variable}>
      <body className="flex min-h-screen flex-col">
        <Header categories={categories} />
        <main className="flex-1">{children}</main>
        <Footer categories={categories} />
      </body>
    </html>
  );
}
