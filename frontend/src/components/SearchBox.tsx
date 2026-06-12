'use client';

import { useEffect, useRef, useState } from 'react';
import { useRouter } from 'next/navigation';
import { Loader2, Search } from 'lucide-react';
import { fetchSuggestions, type SuggestItem } from '@/lib/client';
import ToolLogo from './ToolLogo';

/** 带即时建议下拉的搜索框。variant 控制尺寸样式。 */
export default function SearchBox({
  variant = 'header',
  defaultValue = '',
  placeholder = '搜索 AI 工具…',
}: {
  variant?: 'hero' | 'header';
  defaultValue?: string;
  placeholder?: string;
}) {
  const router = useRouter();
  const [q, setQ] = useState(defaultValue);
  const [items, setItems] = useState<SuggestItem[]>([]);
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const boxRef = useRef<HTMLDivElement>(null);
  const timer = useRef<ReturnType<typeof setTimeout>>(undefined);

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (!boxRef.current?.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener('mousedown', onClick);
    return () => document.removeEventListener('mousedown', onClick);
  }, []);

  const onChange = (value: string) => {
    setQ(value);
    clearTimeout(timer.current);
    const keyword = value.trim();
    if (keyword.length < 1) {
      setItems([]);
      setOpen(false);
      return;
    }
    timer.current = setTimeout(async () => {
      setLoading(true);
      try {
        const result = await fetchSuggestions(keyword);
        setItems(result);
        setOpen(true);
      } catch {
        setItems([]);
      } finally {
        setLoading(false);
      }
    }, 250);
  };

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!q.trim()) return;
    setOpen(false);
    router.push(`/search?q=${encodeURIComponent(q.trim())}`);
  };

  const isHero = variant === 'hero';

  return (
    <div ref={boxRef} className="relative w-full">
      <form onSubmit={submit} role="search">
        <div className="relative">
          <Search
            size={isHero ? 20 : 16}
            className={`pointer-events-none absolute top-1/2 -translate-y-1/2 text-gray-400 ${isHero ? 'left-4' : 'left-3'}`}
          />
          <input
            type="search"
            value={q}
            onChange={(e) => onChange(e.target.value)}
            onFocus={() => items.length > 0 && setOpen(true)}
            placeholder={placeholder}
            aria-label="搜索 AI 工具"
            autoComplete="off"
            className={
              isHero
                ? 'w-full rounded-2xl border border-white/10 bg-white py-4 pl-12 pr-28 text-base text-gray-900 shadow-xl shadow-brand-950/30 outline-none transition focus:ring-4 focus:ring-brand-400/40'
                : 'w-full rounded-xl border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-base outline-none transition focus:border-brand-400 focus:bg-white lg:text-sm'
            }
          />
          {isHero && (
            <button
              type="submit"
              className="absolute right-2 top-1/2 -translate-y-1/2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-500"
            >
              {loading ? <Loader2 size={16} className="animate-spin" /> : '搜索'}
            </button>
          )}
        </div>
      </form>

      {open && items.length > 0 && (
        <ul
          role="listbox"
          aria-label="搜索建议"
          className="absolute left-0 right-0 top-full z-50 mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-white py-2 text-left shadow-xl"
        >
          {items.map((item) => (
            <li key={item.cid}>
              <button
                type="button"
                onClick={() => {
                  setOpen(false);
                  router.push(item.isTool ? `/tool/${item.slug}` : `/news/${item.slug}`);
                }}
                className="flex w-full items-center gap-3 px-4 py-2.5 text-left transition hover:bg-brand-50"
              >
                <ToolLogo src={item.logo} name={item.title} size={32} />
                <span className="min-w-0">
                  <span className="block truncate text-sm font-medium text-gray-900">{item.title}</span>
                  <span className="block truncate text-xs text-gray-500">
                    {item.isTool ? item.tagline || '查看详情' : 'AI 资讯'}
                  </span>
                </span>
              </button>
            </li>
          ))}
          <li className="border-t border-gray-100 pt-1">
            <button
              type="button"
              onClick={submit as any}
              className="w-full px-4 py-2 text-left text-sm text-brand-600 transition hover:bg-brand-50"
            >
              查看「{q.trim()}」的全部结果 →
            </button>
          </li>
        </ul>
      )}
    </div>
  );
}
