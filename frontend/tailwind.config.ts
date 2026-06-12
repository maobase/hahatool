import type { Config } from 'tailwindcss';
import typography from '@tailwindcss/typography';

/** 品牌色走 CSS 变量（globals.css 定义 4 套主题色），实现运行时换肤 */
const brand = Object.fromEntries(
  [50, 100, 200, 300, 400, 500, 600, 700, 800, 900].map((n) => [n, `rgb(var(--brand-${n}) / <alpha-value>)`]),
);

const config: Config = {
  content: ['./src/**/*.{ts,tsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        display: ['var(--font-display)', 'PingFang SC', 'Microsoft YaHei', 'sans-serif'],
      },
      colors: {
        brand,
      },
    },
  },
  plugins: [typography],
};

export default config;
