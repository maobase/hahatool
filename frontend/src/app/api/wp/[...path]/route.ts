import { NextRequest, NextResponse } from 'next/server';

/**
 * WordPress REST API 代理（供浏览器端组件调用，规避 CORS）。
 * 端点白名单：只放行前台需要的只读接口与评论提交。
 */
const API_BASE = (process.env.WP_API_BASE ?? 'http://localhost:8090/wp-json').replace(/\/+$/, '');

const ALLOWED_GET = new Set(['wp/v2/posts', 'wp/v2/comments', 'wp/v2/categories', 'wp/v2/tags']);
const ALLOWED_POST = new Set(['wp/v2/comments']);

export async function GET(req: NextRequest, { params }: { params: Promise<{ path: string[] }> }) {
  const { path } = await params;
  const endpoint = path.join('/');
  if (!ALLOWED_GET.has(endpoint)) {
    return NextResponse.json({ code: 'forbidden', message: 'endpoint not allowed' }, { status: 403 });
  }
  try {
    const res = await fetch(`${API_BASE}/${endpoint}?${req.nextUrl.searchParams.toString()}`, { cache: 'no-store' });
    return NextResponse.json(await res.json(), { status: res.status });
  } catch {
    return NextResponse.json({ code: 'unavailable', message: 'backend unavailable' }, { status: 502 });
  }
}

export async function POST(req: NextRequest, { params }: { params: Promise<{ path: string[] }> }) {
  const { path } = await params;
  const endpoint = path.join('/');
  if (!ALLOWED_POST.has(endpoint)) {
    return NextResponse.json({ code: 'forbidden', message: 'endpoint not allowed' }, { status: 403 });
  }
  try {
    const body = await req.text();
    const res = await fetch(`${API_BASE}/${endpoint}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body,
      cache: 'no-store',
    });
    return NextResponse.json(await res.json(), { status: res.status });
  } catch {
    return NextResponse.json({ code: 'unavailable', message: 'backend unavailable' }, { status: 502 });
  }
}
