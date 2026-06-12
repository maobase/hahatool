'use client';

import { useState } from 'react';

const GRADIENTS = [
  'from-violet-500 to-indigo-500',
  'from-pink-500 to-rose-500',
  'from-emerald-500 to-teal-500',
  'from-amber-500 to-orange-500',
  'from-sky-500 to-blue-500',
  'from-fuchsia-500 to-purple-500',
];

function hashOf(s: string): number {
  let h = 0;
  for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) | 0;
  return Math.abs(h);
}

/** 工具 Logo，加载失败时降级为首字母渐变头像 */
export default function ToolLogo({
  src,
  name,
  size = 48,
  className = '',
}: {
  src?: string;
  name: string;
  size?: number;
  className?: string;
}) {
  const [failed, setFailed] = useState(false);
  const gradient = GRADIENTS[hashOf(name) % GRADIENTS.length];

  if (!src || failed) {
    return (
      <div
        aria-hidden
        className={`flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-white font-bold ${gradient} ${className}`}
        style={{ width: size, height: size, fontSize: size * 0.42 }}
      >
        {name.trim().charAt(0).toUpperCase()}
      </div>
    );
  }

  return (
    // eslint-disable-next-line @next/next/no-img-element
    <img
      src={src}
      alt={`${name} Logo`}
      width={size}
      height={size}
      loading="lazy"
      onError={() => setFailed(true)}
      className={`shrink-0 rounded-xl bg-gray-100 dark:bg-gray-800 object-cover ${className}`}
      style={{ width: size, height: size }}
    />
  );
}
