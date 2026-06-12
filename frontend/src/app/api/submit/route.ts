import { NextRequest, NextResponse } from 'next/server';

/**
 * 「提交工具」接口：在 WordPress 创建一篇「待审（pending）」文章，
 * 运营在 wp-admin 审核补全后发布。鉴权使用 Application Password（仅服务端持有）。
 */
const API_BASE = (process.env.WP_API_BASE ?? 'http://localhost:8090/wp-json').replace(/\/+$/, '');
const APP_USER = process.env.WP_APP_USER ?? '';
const APP_PASS = process.env.WP_APP_PASS ?? '';

const PRICING = new Set(['免费', '免费增值', '付费']);

/** 朴素限流：每 IP 每小时最多 3 次（进程内存级，重启清零，演示环境够用） */
const hits = new Map<string, number[]>();
function rateLimited(ip: string): boolean {
  const now = Date.now();
  const list = (hits.get(ip) ?? []).filter((t) => now - t < 3600_000);
  if (list.length >= 3) return true;
  list.push(now);
  hits.set(ip, list);
  return false;
}

function bad(message: string, status = 400) {
  return NextResponse.json({ ok: false, message }, { status });
}

export async function POST(req: NextRequest) {
  if (!APP_USER || !APP_PASS) {
    return bad('提交功能未配置（缺少 WP_APP_USER / WP_APP_PASS），请联系站长', 503);
  }

  const ip = req.headers.get('x-forwarded-for')?.split(',')[0]?.trim() || 'unknown';
  if (rateLimited(ip)) {
    return bad('提交太频繁了，请一小时后再试', 429);
  }

  let body: Record<string, string>;
  try {
    body = await req.json();
  } catch {
    return bad('请求格式错误');
  }

  const name = (body.name ?? '').trim();
  const url = (body.url ?? '').trim();
  const tagline = (body.tagline ?? '').trim();
  const pricing = (body.pricing ?? '').trim();
  const mail = (body.mail ?? '').trim();
  const note = (body.note ?? '').trim();
  const categoryId = Number(body.categoryId) || 0;

  if (!name || name.length > 50) return bad('请填写工具名称（50 字内）');
  if (!/^https?:\/\/.+\..+/.test(url)) return bad('请填写有效的官网链接（http/https 开头）');
  if (!tagline || tagline.length > 60) return bad('请填写一句话简介（60 字内）');
  if (!PRICING.has(pricing)) return bad('请选择定价模式');
  if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(mail)) return bad('请填写有效的联系邮箱');
  if (note.length > 500) return bad('补充说明过长（500 字内）');

  const content = [
    '<p><em>—— 以下为用户提交，待运营审核 ——</em></p>',
    `<p>联系邮箱：${mail}</p>`,
    note ? `<p>补充说明：${note.replace(/</g, '&lt;')}</p>` : '',
  ].join('\n');

  try {
    const res = await fetch(`${API_BASE}/wp/v2/posts`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Basic ${Buffer.from(`${APP_USER}:${APP_PASS}`).toString('base64')}`,
      },
      body: JSON.stringify({
        title: name,
        status: 'pending',
        content,
        categories: categoryId ? [categoryId] : [],
        meta: { url, tagline, pricing },
      }),
      cache: 'no-store',
    });
    const json = await res.json();
    if (!res.ok) {
      return bad(`提交失败：${json?.message ?? res.status}`, 502);
    }
    return NextResponse.json({ ok: true, id: json.id });
  } catch {
    return bad('后端暂时不可用，请稍后再试', 502);
  }
}
