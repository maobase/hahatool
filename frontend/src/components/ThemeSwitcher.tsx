'use client';

import { useEffect, useRef, useState } from 'react';
import { Check, Monitor, Moon, Palette, Sun } from 'lucide-react';

type Mode = 'light' | 'dark' | 'system';
type Accent = 'violet' | 'sky' | 'emerald' | 'rose';

const STORE_KEY = 'hahatool:theme';

const MODES: { key: Mode; label: string; icon: typeof Sun }[] = [
  { key: 'light', label: '浅色', icon: Sun },
  { key: 'dark', label: '深色', icon: Moon },
  { key: 'system', label: '系统', icon: Monitor },
];

const ACCENTS: { key: Accent; label: string; swatch: string }[] = [
  { key: 'violet', label: '紫罗兰', swatch: '#7c3aed' },
  { key: 'sky', label: '海蓝', swatch: '#0284c7' },
  { key: 'emerald', label: '翡翠', swatch: '#059669' },
  { key: 'rose', label: '玫红', swatch: '#e11d48' },
];

function applyTheme(mode: Mode, accent: Accent) {
  const dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
  document.documentElement.classList.toggle('dark', dark);
  document.documentElement.dataset.accent = accent;
}

/** 外观设置：明暗模式 + 主题色（localStorage 持久化，layout 内联脚本负责首屏防闪烁） */
export default function ThemeSwitcher() {
  const [open, setOpen] = useState(false);
  const [mode, setMode] = useState<Mode>('system');
  const [accent, setAccent] = useState<Accent>('violet');
  const boxRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    try {
      const saved = JSON.parse(localStorage.getItem(STORE_KEY) ?? '{}');
      if (saved.mode) setMode(saved.mode);
      if (saved.accent) setAccent(saved.accent);
    } catch {
      /* 忽略损坏的存储 */
    }
  }, []);

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (!boxRef.current?.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener('mousedown', onClick);
    return () => document.removeEventListener('mousedown', onClick);
  }, []);

  // 跟随系统模式下监听系统切换
  useEffect(() => {
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    const sync = () => applyTheme(mode, accent);
    mq.addEventListener('change', sync);
    return () => mq.removeEventListener('change', sync);
  }, [mode, accent]);

  const update = (nextMode: Mode, nextAccent: Accent) => {
    setMode(nextMode);
    setAccent(nextAccent);
    localStorage.setItem(STORE_KEY, JSON.stringify({ mode: nextMode, accent: nextAccent }));
    applyTheme(nextMode, nextAccent);
  };

  return (
    <div ref={boxRef} className="relative">
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        aria-label="外观设置"
        aria-expanded={open}
        className="flex min-h-10 min-w-10 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:bg-gray-800"
      >
        <Palette size={19} />
      </button>

      {open && (
        <div className="absolute right-0 top-full z-50 mt-2 w-56 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-xl dark:border-gray-800 dark:bg-gray-900">
          <p className="text-xs font-medium text-gray-400">外观模式</p>
          <div className="mt-2 grid grid-cols-3 gap-1.5" role="radiogroup" aria-label="外观模式">
            {MODES.map(({ key, label, icon: Icon }) => (
              <button
                key={key}
                type="button"
                role="radio"
                aria-checked={mode === key}
                onClick={() => update(key, accent)}
                className={`flex flex-col items-center gap-1 rounded-xl border py-2 text-xs transition ${
                  mode === key
                    ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 dark:bg-brand-900/30 dark:text-brand-300'
                    : 'border-gray-200 dark:border-gray-800 text-gray-500 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'
                }`}
              >
                <Icon size={15} />
                {label}
              </button>
            ))}
          </div>

          <p className="mt-4 text-xs font-medium text-gray-400">主题色</p>
          <div className="mt-2 flex gap-2.5" role="radiogroup" aria-label="主题色">
            {ACCENTS.map((a) => (
              <button
                key={a.key}
                type="button"
                role="radio"
                aria-checked={accent === a.key}
                title={a.label}
                onClick={() => update(mode, a.key)}
                className="flex h-9 w-9 items-center justify-center rounded-full ring-2 ring-offset-2 ring-offset-white transition dark:ring-offset-gray-900"
                style={{ background: a.swatch, ['--tw-ring-color' as any]: accent === a.key ? a.swatch : 'transparent' }}
              >
                {accent === a.key && <Check size={15} className="text-white" />}
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
