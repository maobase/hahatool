'use client';

import { ArrowUpRight } from 'lucide-react';

/** 「访问官网」按钮：跳转的同时上报点击统计（fire-and-forget，不阻塞跳转） */
export default function VisitButton({ cid, url }: { cid: number; url: string }) {
  const onClick = () => {
    fetch('/api/track', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cid, type: 'clicks' }),
      keepalive: true,
    }).catch(() => {});
  };

  return (
    <a
      href={url}
      target="_blank"
      rel="noopener noreferrer nofollow"
      onClick={onClick}
      className="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-brand-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
    >
      访问官网
      <ArrowUpRight size={16} />
    </a>
  );
}
