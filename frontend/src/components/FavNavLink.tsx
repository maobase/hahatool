'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { Heart } from 'lucide-react';
import { FAV_EVENT, getFavorites } from '@/lib/client';

/** 导航栏收藏入口（带数量角标） */
export default function FavNavLink() {
  const [count, setCount] = useState(0);

  useEffect(() => {
    const sync = () => setCount(getFavorites().length);
    sync();
    window.addEventListener(FAV_EVENT, sync);
    window.addEventListener('storage', sync);
    return () => {
      window.removeEventListener(FAV_EVENT, sync);
      window.removeEventListener('storage', sync);
    };
  }, []);

  return (
    <Link
      href="/favorites"
      aria-label={`我的收藏（${count} 个）`}
      className="relative flex min-h-10 min-w-10 items-center justify-center rounded-lg text-gray-500 transition hover:bg-rose-50 hover:text-rose-500"
    >
      <Heart size={19} />
      {count > 0 && (
        <span className="absolute right-0.5 top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 font-display text-[10px] font-bold text-white">
          {count > 99 ? '99+' : count}
        </span>
      )}
    </Link>
  );
}
