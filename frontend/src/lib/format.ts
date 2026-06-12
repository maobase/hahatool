/** 1234567 -> "123.5万"，460000000 -> "4.6亿" */
export function formatCount(n: number): string {
  if (!n || n <= 0) return '—';
  if (n >= 1e8) return `${(n / 1e8).toFixed(1).replace(/\.0$/, '')}亿`;
  if (n >= 1e4) return `${(n / 1e4).toFixed(1).replace(/\.0$/, '')}万`;
  return String(n);
}

export function formatDate(ts: number): string {
  const d = new Date(ts * 1000);
  const pad = (x: number) => String(x).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

/** 提取 URL 的域名用于展示 */
export function domainOf(url: string): string {
  try {
    return new URL(url).hostname.replace(/^www\./, '');
  } catch {
    return url;
  }
}
