'use client';

import { useState } from 'react';
import ToolLogo from './ToolLogo';

/**
 * 工具官网截图。优先使用后台 screenshot 字段；
 * 缺省时用 WordPress mshots 免费截图服务；失败降级为品牌占位图。
 */
export default function ToolScreenshot({
  url,
  screenshot,
  name,
  logo,
}: {
  url: string;
  screenshot?: string;
  name: string;
  logo?: string;
}) {
  const [failed, setFailed] = useState(false);
  const src = screenshot || `https://s0.wp.com/mshots/v1/${encodeURIComponent(url)}?w=1280`;

  return (
    <figure className="overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-brand-50 to-indigo-50 shadow-sm">
      <div className="relative aspect-[16/9]">
        {!failed ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={src}
            alt={`${name} 官网截图`}
            loading="lazy"
            onError={() => setFailed(true)}
            className="absolute inset-0 h-full w-full object-cover object-top"
          />
        ) : (
          <div className="absolute inset-0 flex flex-col items-center justify-center gap-3">
            <ToolLogo src={logo} name={name} size={64} />
            <p className="text-sm text-gray-500">截图生成中，稍后再来看看</p>
          </div>
        )}
      </div>
      <figcaption className="border-t border-gray-100 bg-white px-4 py-2 text-xs text-gray-400">
        {name} 官网预览 · 截图自动生成，以官网实际为准
      </figcaption>
    </figure>
  );
}
