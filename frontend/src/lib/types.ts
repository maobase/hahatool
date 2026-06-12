/** 分类（WordPress category） */
export interface Category {
  mid: number;
  name: string;
  slug: string;
  description: string;
  count: number;
  order: number;
}

/** 标签（WordPress tag） */
export interface Tag {
  mid: number;
  name: string;
  slug: string;
  count: number;
}

/** WordPress REST 文章原始结构（仅声明用到的字段） */
export interface WpPost {
  id: number;
  slug: string;
  date_gmt: string;
  title: { rendered: string };
  content?: { rendered: string };
  excerpt?: { rendered: string };
  meta?: Record<string, string>;
  _embedded?: {
    'wp:term'?: { taxonomy: string; name: string; slug: string }[][];
  };
}

/** 站内的「工具」实体（由文章 + 自定义字段映射而来） */
export interface Tool {
  cid: number;
  title: string;
  slug: string;
  created: number;
  url: string;
  logo: string;
  tagline: string;
  pricing: string;
  likes: number;
  monthlyVisits: number;
  /** 月环比增长率（%），可为负 */
  growth: number;
  /** 官网截图 URL（可选，缺省时前台自动生成） */
  screenshot: string;
  /** 评分 0-5（运营维护） */
  rating: number;
  /** 近 N 月访问量（流量趋势图） */
  visitsHistory: number[];
  /** 地区流量分布 */
  regions: { name: string; pct: number }[];
  /** 常见问题 */
  faq: { q: string; a: string }[];
  /** 能力雷达五维评分（0-10） */
  scores: { label: string; value: number }[];
  featured: boolean;
  banner: boolean;
  /** 运营位标识：home-mid / detail-side / ranking-top 等（空 = 无） */
  promo: string;
  categories: { name: string; slug: string }[];
  tags: { name: string; slug: string }[];
  /** 详情页才有：HTML 正文 */
  contentHtml?: string;
}

/** 提示词条目 */
export interface PromptItem {
  cid: number;
  title: string;
  slug: string;
  created: number;
  /** 提示词全文 */
  prompt: string;
  /** 适用模型（如：通用 / Midjourney） */
  model: string;
  /** 使用场景（如：写作 / 编程 / 营销） */
  scene: string;
  likes: number;
  /** 使用说明 HTML（详情页） */
  contentHtml?: string;
}

/** 资讯条目 */
export interface NewsItem {
  cid: number;
  title: string;
  slug: string;
  created: number;
  digest: string;
  /** 封面图 URL（cover 字段，可选） */
  cover: string;
  contentHtml?: string;
}
