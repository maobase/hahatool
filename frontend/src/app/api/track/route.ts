import { NextRequest, NextResponse } from 'next/server';

/**
 * 站内统计上报代理：浏览量（views）/ 官网直达点击（clicks）。
 * 同一访客（IP）对同一条目同一类型 30 分钟内只计一次，转发给 WP 自增计数器。
 * 永远返回 ok——统计失败不应影响用户体验。
 */
const API_BASE = (process.env.WP_API_BASE ?? 'http://localhost:8090/wp-json').replace(/\/+$/, '');

const seen = new Map<string, number>();
const TTL = 30 * 60 * 1000;

function dedup(key: string): boolean {
  const now = Date.now();
  // 顺手清理过期项，防止 Map 无限增长
  if (seen.size > 5000) {
    for (const [k, t] of seen) if (now - t > TTL) seen.delete(k);
  }
  const last = seen.get(key);
  if (last && now - last < TTL) return true;
  seen.set(key, now);
  return false;
}

export async function POST(req: NextRequest) {
  try {
    const { cid, type } = await req.json();
    const id = Number(cid);
    if (!id || (type !== 'views' && type !== 'clicks')) {
      return NextResponse.json({ ok: false }, { status: 400 });
    }
    const ip = req.headers.get('x-forwarded-for')?.split(',')[0]?.trim() || 'local';
    if (!dedup(`${ip}:${id}:${type}`)) {
      // 不等待结果，失败也不影响前台
      fetch(`${API_BASE}/hahatool/v1/track`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cid: id, type }),
        cache: 'no-store',
      }).catch(() => {});
    }
  } catch {
    /* 静默 */
  }
  return NextResponse.json({ ok: true });
}
