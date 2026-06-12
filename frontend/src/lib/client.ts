'use client';

/** 浏览器端工具集：搜索建议、评论、收藏（localStorage）。数据走 /api/wp 代理。 */

export interface SuggestItem {
  cid: number;
  title: string;
  slug: string;
  logo: string;
  tagline: string;
  kind: 'tool' | 'prompt' | 'news';
}

export interface CommentItem {
  coid: number;
  author: string;
  text: string;
  created: number;
  children?: CommentItem[];
}

function stripTags(s: string): string {
  return s.replace(/<[^>]+>/g, '').replace(/&hellip;/g, '…').trim();
}

// ---------------- 搜索建议 ----------------

export async function fetchSuggestions(keyword: string): Promise<SuggestItem[]> {
  const qs = new URLSearchParams({ search: keyword, per_page: '6' });
  const res = await fetch(`/api/wp/wp/v2/posts?${qs}`);
  if (!res.ok) throw new Error('搜索失败');
  const posts = (await res.json()) as any[];
  return posts.map((p) => ({
    cid: p.id,
    title: stripTags(p.title?.rendered ?? ''),
    slug: p.slug,
    logo: p.meta?.logo ?? '',
    tagline: p.meta?.tagline ?? (p.meta?.prompt ? String(p.meta.prompt).slice(0, 40) : ''),
    kind: p.meta?.url ? 'tool' : p.meta?.prompt ? 'prompt' : 'news',
  }));
}

// ---------------- 评论 ----------------

export async function fetchComments(postId: number): Promise<{ count: number; items: CommentItem[] }> {
  const qs = new URLSearchParams({ post: String(postId), per_page: '50', order: 'desc' });
  const res = await fetch(`/api/wp/wp/v2/comments?${qs}`);
  if (!res.ok) throw new Error('评论加载失败');
  const total = Number(res.headers.get('x-wp-total')) || 0;
  const raw = (await res.json()) as any[];

  const map = new Map<number, CommentItem>();
  const roots: CommentItem[] = [];
  for (const c of raw) {
    map.set(c.id, {
      coid: c.id,
      author: c.author_name || '匿名用户',
      text: c.content?.rendered ?? '',
      created: Math.floor(Date.parse(`${c.date_gmt}Z`) / 1000) || 0,
      children: [],
    });
  }
  for (const c of raw) {
    const node = map.get(c.id)!;
    if (c.parent && map.has(c.parent)) map.get(c.parent)!.children!.push(node);
    else roots.push(node);
  }
  return { count: total || raw.length, items: roots };
}

export async function postComment(postId: number, author: string, mail: string, text: string): Promise<void> {
  const res = await fetch('/api/wp/wp/v2/comments', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ post: postId, author_name: author, author_email: mail, content: text }),
  });
  const json = await res.json();
  if (!res.ok) {
    throw new Error(stripTags(json?.message || '评论提交失败'));
  }
  if (json?.status === 'hold') {
    throw new Error('评论已提交，待管理员审核后显示');
  }
}

// ---------------- 收藏（localStorage） ----------------

const FAV_KEY = 'hahatool:favs';
export const FAV_EVENT = 'hahatool:favs-changed';

export function getFavorites(): number[] {
  if (typeof window === 'undefined') return [];
  try {
    const raw = JSON.parse(localStorage.getItem(FAV_KEY) ?? '[]');
    return Array.isArray(raw) ? raw.map(Number).filter(Boolean) : [];
  } catch {
    return [];
  }
}

export function isFavorite(cid: number): boolean {
  return getFavorites().includes(cid);
}

export function toggleFavorite(cid: number): boolean {
  const favs = getFavorites();
  const idx = favs.indexOf(cid);
  if (idx >= 0) favs.splice(idx, 1);
  else favs.unshift(cid);
  localStorage.setItem(FAV_KEY, JSON.stringify(favs));
  window.dispatchEvent(new Event(FAV_EVENT));
  return idx < 0;
}
