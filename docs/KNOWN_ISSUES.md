# 已知问题（团队迭代记录）

本文件记录团队迭代中发现、但暂未修复或评估为低优先级的真实问题，供后续迭代认领。

## 低优先级

### 1. 无头版未知分类/标签软 404
- **现象**：Next.js 前台访问不存在的 `/category/<未知>` 或 `/tag/<未知>` 时，渲染了「页面不存在」内容，但 HTTP 状态码是 **200** 而非 404（软 404）。
- **影响**：低。这类 URL 不在 sitemap、无站内外链；**用户可见行为与 WP 主题一致**（都展示「页面不存在」页）。仅 HTTP 状态码这一非用户可见的技术细节不同。
- **对比**：WordPress 主题版对相同情况返回 404。完全不匹配的路由（如 `/zzz`）两版本都正确 404；差异仅出现在 `notFound()` 触发的动态路由。
- **已验证的修复尝试（均无效或代价过高）**：
  1. `export const dynamic = 'force-dynamic'`（去 ISR 缓存）→ 重建后仍返回 200，无效。
  2. `generateStaticParams` + `dynamicParams = false` → 可返回真 404，但会使分类/标签页变为纯静态，**新增工具/分类需重建才生效**，违背无头 CMS 的内容实时性，代价过高，未采用。
  3. 保持 ISR（`revalidate=60`）→ 内容实时，软 404 保留。**当前采用此方案**。
- **结论**：这是 Next.js 15 standalone 下 `notFound()` 与 ISR 的已知框架限制，无法在不牺牲内容实时性的前提下修复。用户可见功能两版本已一致；保留为已记录的框架限制。
- **发现**：团队迭代轮 2；深入排查于「双版本一致性」目标（2026-06-15）。

## 双版本功能一致性矩阵（持续更新，最近核验 2026-06-17）

> v1.6.20–v1.6.28 以 JustNews（demo.wpcom.cn/justnews）杂志风为参考做了 9 轮板块打磨，并经桌面浅/深双色 + 移动 375px 截图 QA：移动端零横向溢出、新布局正确堆叠、专题/资讯杂志块渲染良好。

| 维度 | 无头 (Next.js) | 主题 (WordPress) | 一致 |
| --- | --- | --- | --- |
| 首页全部板块（精选/增长/最新/分类/标签云/提示词/资讯/Banner） | ✓ | ✓ | ✓ |
| 首页「精选专题」板块（封面卡） | ✓ | ✓ | ✓ |
| 首页「AI 资讯」头条大图 + 紧凑列表杂志布局 | ✓ | ✓ | ✓ |
| 专题系统（topic 分类法 / 列表 /topics / 归档 /topic/<slug> / 导航） | ✓ | ✓ | ✓ |
| 工具详情全部模块（截图/雷达/流量/FAQ/评论/替代品/收藏/统计） | ✓ | ✓ | ✓ |
| 列表分页（chevron 上下页 + 当前±2 窗口 + 首末省略号，24/页） | ✓ Pagination 组件 | ✓ hahatool_pagination 助手 | ✓ |
| 排行榜 5 榜 + 领奖台 | ✓ | ✓ | ✓ |
| 工具 PK 双雷达 | ✓ | ✓ | ✓ |
| 提示词库 + 一键复制 | ✓ | ✓ | ✓ |
| 收藏（localStorage）+ 导航计数 | ✓ | ✓ | ✓ |
| 搜索即时建议 | ✓ | ✓ | ✓ |
| 快讯跑马灯 / 按天分组 / 时间线连接线+节点 | ✓ | ✓ | ✓ |
| 资讯列表项 + 阅读时长 meta（read_time REST 字段同口径） | ✓ | ✓ | ✓ |
| 资讯详情（封面/上下篇/相关资讯/两栏 + 快讯·热门工具侧栏） | ✓ | ✓ | ✓ |
| 频道/落地页标题图标 chip（prompts/flash/news/topics/compare/favorites） | ✓ | ✓ | ✓ |
| 明暗 × 4 主题色 | ✓ | ✓ | ✓ |
| 提交工具（待审）/ 评论 端到端 | ✓ | ✓ | ✓ |
| 导航分类下拉 | ✓ | ✓ | ✓ |
| 图标系统（lucide 风格 SVG，无 emoji） | ✓ | ✓ | ✓ |
| 字体自托管（无第三方/中国可用） | ✓ next/font | ✓ @font-face | ✓ |
| 统计防刷（IP 30 分钟去重） | ✓ 代理层 | ✓ 端点 transient | ✓ |
| SEO：页面标题 / meta 描述 / OG / Twitter 卡片 | ✓ | ✓ | ✓ |
| SEO：结构化数据（SoftwareApplication/Offer/AggregateRating/BreadcrumbList） | ✓ | ✓ | ✓ |
| SEO：sitemap 全量 | ✓ force-dynamic | ✓ wp-sitemap | ✓ |
| 频道 URL（/prompts /flash /news 清爽路径） | ✓ | ✓（重写别名+canonical） | ✓ |
| 未知分类/标签 HTTP 状态 | 200（软404，框架限制） | 404 | ⚠ 见上 |
| 工具/资讯/提示词详情 URL | `/tool/`、`/news/`、`/prompts/` 分型路径 | `/<slug>/`（WP 统一永久链接） | 平台路径差异，功能相同 |

用户可见功能与体验已完全一致；剩余两项为框架/平台层面的技术差异（非用户可见功能），见上方说明。经 v1.6.0–v1.6.28 的团队打磨 + JustNews 杂志风参考迭代，功能、视觉、无障碍、SEO、性能、安全、响应式各维度均已对齐并通过桌面/移动截图 QA。

## 全量回归认证（v1.6.40，2026-06-18）

经 21 轮 JustNews 参考迭代后的完整回归，两版均健康：
- **路由**：两版各 14–15 条核心路由（首页/工具库/排行/PK/资讯/快讯/提示词/专题/收藏/提交/各类详情/搜索/sitemap）全部 HTTP 200。
- **交互端点**：站内浏览上报 `/hahatool/v1/track` 正常自增（资讯 views 实测 +1）、无头 `/api/track` 代理 200、搜索命中正常。
- **SEO/社交**：结构化数据全覆盖（工具 SoftwareApplication / 资讯 NewsArticle / 专题 CollectionPage+ItemList，均配 BreadcrumbList）；资讯/专题 OG 大图卡；`robots.txt` 两版均含 Sitemap 指令。
- **无障碍**：alt / 图标 aria-label / `:focus-visible` 焦点环 / `prefers-reduced-motion` / skip-link 跳主内容 均具备。
- **多维 QA**：桌面浅/深、移动 375px、4 套主题色截图核验均通过，零横向溢出。

## 已评估为「非问题」

- **无头 RSC 预取报 requestfailed**：导航时中断的 React Server Component 预取，直接请求全部 200，属 Next.js 正常行为。
- **mshots 对 Cloudflare 站点返回验证页**：第三方截图服务局限，运营可在后台手填 `screenshot` 字段覆盖（详情页图注已设定预期）。
