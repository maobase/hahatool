import type { MetadataRoute } from 'next';

// robots 为构建期静态生成：站点地址经 docker build arg NEXT_PUBLIC_SITE_URL 注入
const SITE_URL = (process.env.NEXT_PUBLIC_SITE_URL ?? 'http://localhost:3000').replace(/\/+$/, '');

export default function robots(): MetadataRoute.Robots {
  return {
    rules: { userAgent: '*', allow: '/', disallow: ['/api/'] },
    sitemap: `${SITE_URL}/sitemap.xml`,
  };
}
