'use client';

import { useEffect } from 'react';

/** 详情页浏览量上报：每个浏览器会话对同一条目只上报一次 */
export default function TrackView({ cid }: { cid: number }) {
  useEffect(() => {
    const key = `hahatool:viewed:${cid}`;
    if (sessionStorage.getItem(key)) return;
    sessionStorage.setItem(key, '1');
    fetch('/api/track', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cid, type: 'views' }),
    }).catch(() => {});
  }, [cid]);

  return null;
}
