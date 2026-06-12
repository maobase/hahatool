'use client';

import { useState } from 'react';
import { Check, Copy } from 'lucide-react';

/** 一键复制按钮（提示词等） */
export default function CopyButton({ text, label = '复制' }: { text: string; label?: string }) {
  const [copied, setCopied] = useState(false);

  const onCopy = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    try {
      await navigator.clipboard.writeText(text);
    } catch {
      // 非 https 或旧浏览器降级
      const ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      ta.remove();
    }
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <button
      type="button"
      onClick={onCopy}
      aria-label={copied ? '已复制' : `${label}提示词`}
      className={`relative z-10 inline-flex min-h-9 items-center gap-1.5 rounded-lg px-3 text-xs font-semibold transition ${
        copied
          ? 'bg-emerald-500 text-white'
          : 'bg-brand-600 text-white hover:bg-brand-700'
      }`}
    >
      {copied ? <Check size={13} /> : <Copy size={13} />}
      {copied ? '已复制' : label}
    </button>
  );
}
