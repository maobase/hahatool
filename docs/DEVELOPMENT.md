# HahaTool 开发手册

## 技术栈

| 层 | 技术 |
| --- | --- |
| 前台 | Next.js 15（App Router、Server Components、ISR）、React 19、TypeScript、Tailwind CSS 3、lucide-react、Space Grotesk（display 字体） |
| 内容后台 | WordPress（官方镜像）+ 内置 mu-plugin（`wordpress/mu-plugins/hahatool.php`） |
| 数据库 | MySQL 8（utf8mb4） |
| 部署 | Docker Compose（含 wpcli 工具容器） |

## 本地开发（前台热更新）

```bash
docker compose up -d db wordpress    # 后端跑容器
make dev                             # 前台 dev server，注入 WP_API_BASE=http://localhost:8090/wp-json
```

## 目录结构（前台）

```
frontend/src/
├── app/
│   ├── page.tsx                # 首页
│   ├── compare/                # 工具 PK（亮点功能）
│   ├── tools/ tool/[slug]/     # 工具库 / 详情
│   ├── ranking/ flash/ news/   # 榜单 / 快讯 / 资讯
│   ├── category/ tag/          # 分类页 / 标签页
│   ├── favorites/ search/ submit/
│   └── api/wp/[...path]/       # WordPress REST 代理（浏览器端走这里）
├── components/                 # ToolCard / RadarChart / AdSlot / TrafficPanel / Comments ...
└── lib/
    ├── api.ts                  # WP REST 客户端（服务端）
    ├── client.ts               # 浏览器端：建议 / 评论 / 收藏
    ├── types.ts  format.ts  site.ts
```

## 数据模型约定

工具 = WordPress **文章** + **自定义字段（meta）**。所有字段由 mu-plugin 注册到 REST（`show_in_rest`）：

| 字段 | 含义 | 示例 |
| --- | --- | --- |
| `url` | 官网链接（**有它才被识别为工具**） | `https://chatgpt.com` |
| `logo` | Logo URL（失败自动降级首字母头像） | `https://favicon.im/chatgpt.com?larger=true` |
| `tagline` | 一句话简介 | `全能 AI 对话助手` |
| `pricing` | `免费` / `免费增值` / `付费` | `免费增值` |
| `likes` / `monthly_visits` / `growth` | 收藏数 / 月访问量 / 月增长率% | `9800` / `4600000000` / `5.2` |
| `rating` | 评分 0-5 | `4.8` |
| `scores` | 能力雷达五维（0-10，逗号分隔） | `易用性:9.5,功能强度:9.6,性价比:8.5,生态集成:9.8,中文友好:8.0` |
| `visits_history` | 近 N 月访问量（逗号分隔） | `41e8,42e8,...`（纯数字） |
| `regions` | 地区分布 | `美国:19.1,印度:8.8,其他:62.8` |
| `faq` | 常见问题（每行 `问|答`） | 多行文本 |
| `screenshot` / `cover` | 截图 / 资讯封面 URL | 可选 |
| `featured` / `banner` | `1` = 编辑精选 / 首页顶部 Banner | `1` |
| `prompt` / `prompt_scene` / `prompt_model` | 提示词频道：全文 / 场景 / 适用模型（分类须为 `ai-prompts`） | 多行文本 / `编程` / `通用` |
| `views` / `clicks` | **站内真实统计**（详情页浏览 / 官网直达点击），由 `POST /wp-json/hahatool/v1/track` 自动累加，请勿手填 | 自动 |
| `promo` | 广告位：`home-mid` `ranking-top` `detail-side` `detail-bottom` `tools-inline` `news-inline` | 单值 |

- 分类 = WP 分类；`ai-news`（资讯）与 `ai-flash`（快讯）为保留 slug；
- 标签 = WP 标签；分类展示顺序在 `lib/api.ts` 的 `CATEGORY_ORDER` 中维护。

## REST API 约定

服务端（`lib/api.ts`）直连 `WP_API_BASE`（默认 `http://wordpress/wp-json`），60s ISR，失败降级空数据：

| 端点 | 用途 | 关键参数 |
| --- | --- | --- |
| `GET wp/v2/posts` | 列表 / 搜索 / 详情(slug) | `per_page` `page` `categories` `tags` `search` `slug` `include` `_embed=wp:term` |
| `GET wp/v2/categories` / `wp/v2/tags` | 分类 / 标签 | `per_page=100&hide_empty=true` |
| `GET/POST wp/v2/comments` | 评论列表 / 匿名发表 | `post` `per_page`；POST：`post` `author_name` `author_email` `content` |
| `POST hahatool/v1/track` | 站内统计计数器自增（mu-plugin 自定义端点，不产生修订） | `cid` `type=views\|clicks`；前台经 `/api/track` 代理（IP+条目 30 分钟去重） |

分页总数从响应头 `X-WP-Total` / `X-WP-TotalPages` 读取；meta 在 `post.meta`；分类标签在 `_embedded['wp:term']`。

浏览器端组件统一走 Next 代理 `/api/wp/[...path]`（白名单：posts/comments/categories/tags 只读 + comments POST），规避 CORS。

匿名评论由 mu-plugin `rest_allow_anonymous_comments` 放开；演示环境关闭了评论审核（`setup-wp.sh`），生产建议打开。

## mu-plugin 说明

`wordpress/mu-plugins/hahatool.php` 通过 compose 卷挂载到 `wp-content/mu-plugins`（mu-plugin 免激活、不可在后台停用）。改动它无需重建镜像，刷新即生效。新增字段：在 `HAHATOOL_META_KEYS` 数组加 key → `lib/types.ts` 加属性 → `lib/api.ts` 的 `toTool()` 映射。

## 修改前台后重新部署

```bash
docker compose build frontend && docker compose up -d frontend
```

## 已知限制

- 流量/收藏/评分/雷达为运营手工数据，非真实统计；
- 全量工具单次拉取（per_page=100），工具量超百后应改为分页/按需查询；
- WP 原生搜索为全文 LIKE，中文分词有限。
