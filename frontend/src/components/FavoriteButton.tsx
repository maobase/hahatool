'use client';

import { useEffect, useState } from 'react';
import { Heart } from 'lucide-react';
import { FAV_EVENT, isFavorite, toggleFavorite } from '@/lib/client';

/** 收藏按钮（localStorage 本地收藏，无需登录） */
export default function FavoriteButton({
  cid,
  size = 'sm',
}: {
  cid: number;
  size?: 'sm' | 'lg';
}) {
  const [fav, setFav] = useState(false);

  useEffect(() => {
    const sync = () => setFav(isFavorite(cid));
    sync();
    window.addEventListener(FAV_EVENT, sync);
    return () => window.removeEventListener(FAV_EVENT, sync);
  }, [cid]);

  const onClick = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setFav(toggleFavorite(cid));
  };

  if (size === 'lg') {
    return (
      <button
        type="button"
        onClick={onClick}
        aria-pressed={fav}
        aria-label={fav ? '取消收藏' : '收藏'}
        className={`inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl border px-4 text-sm font-medium transition ${
          fav
            ? 'border-rose-200 bg-rose-50 text-rose-600'
            : 'border-gray-200 bg-white text-gray-600 hover:border-rose-200 hover:text-rose-500'
        }`}
      >
        <Heart size={16} className={fav ? 'fill-rose-500 text-rose-500' : ''} />
        {fav ? '已收藏' : '收藏'}
      </button>
    );
  }

  return (
    <button
      type="button"
      onClick={onClick}
      aria-pressed={fav}
      aria-label={fav ? '取消收藏' : '收藏'}
      className="relative z-10 flex min-h-9 min-w-9 items-center justify-center rounded-lg text-gray-300 transition hover:bg-rose-50 hover:text-rose-500"
    >
      <Heart size={16} className={fav ? 'fill-rose-500 text-rose-500' : ''} />
    </button>
  );
}
