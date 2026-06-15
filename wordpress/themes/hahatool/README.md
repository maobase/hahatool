# HahaTool WordPress 主题

HahaTool 导航站的 **WordPress 主题版本**。与 Next.js 无头前台共用同一套内容与自定义字段，但由 WordPress 的 PHP 模板直接渲染整站——**无需运行 Node，普通 PHP 虚拟主机即可部署**。

## 启用

主题随 `docker-compose.yml` 挂载到 `wp-content/themes/hahatool`。切换：

```bash
bash scripts/switch-mode.sh theme       # 激活本主题，WordPress 直接渲染整站
bash scripts/switch-mode.sh headless    # 切回默认主题，仅作 REST 数据源
```

或在 wp-admin → 外观 → 主题 中手动启用「HahaTool」，启用后**务必到 设置 → 固定链接 点一次保存**以刷新虚拟路由。

## 页面与模板

| 路径 | 模板 | 内容 |
| --- | --- | --- |
| `/` | `front-page.php` | 深色 Hero + 统计 + 分类条 + 精选/增长/分类/提示词/资讯板块 |
| `/tools/` | `template-tools.php` | 工具库（分类+定价筛选、五维排序）|
| `/ranking/` | `template-ranking.php` | 排行榜（流量/收藏/增长/人气/新品 + 领奖台）|
| `/compare/?a=&b=` | `template-compare.php` | 工具 PK（双系列能力雷达 + 数据对比）|
| `/submit/` | `template-submit.php` | 提交工具（写入待审文章）|
| `/favorites/` | `template-favorites.php` | 我的收藏（localStorage，前端筛选）|
| `/category/<slug>/` | `category.php` | 工具分类→网格；`ai-prompts`→提示词库；`ai-flash`→按天分组时间线；`ai-news`→头条+列表+侧栏 |
| `/tag/<slug>/` | `tag.php` | 标签页（含相关标签云）|
| 工具/提示词/资讯详情 | `single.php` → `template-parts/single-*.php` | 按 meta 自动分流 |
| `/?s=` | `search.php` | 搜索结果（工具/提示词/资讯分组）|

与无头原版**功能对等**的交互（纯前端 `assets/theme.js`）：收藏（♥ 按钮 + 顶栏计数 + 收藏页）、搜索即时建议下拉、首页快讯跑马灯、明暗 × 4 主题色、一键复制提示词、站内浏览/点击统计上报。首页含 Banner 大卡运营位、编辑精选、增长最快、分类板块、标签云、热门提示词、AI 资讯等板块。

`/tools` `/ranking` `/submit` `/compare` 为 `functions.php` 注册的**虚拟路由**（rewrite + `template_include`），无需在后台建页面。

## 设计与实现要点

- **零前端依赖**：能力雷达图、流量趋势柱状图全部由 PHP 生成 SVG（`inc/helpers.php`）；明暗模式 + 4 套主题色用 CSS 变量 + 一段轻量 `assets/theme.js`（localStorage 持久化、首屏防闪烁）。
- **数据契约一致**：`hahatool_is_tool()`、字段读取与无头前台 `lib/api.ts` 完全对应；保留分类 `ai-news/ai-flash/ai-prompts`、字段 `url/featured/banner/promo/...` 含义相同。
- **复用 mu-plugin**：评论、站内统计端点 `/hahatool/v1/track`、字段注册都由 `wordpress/mu-plugins/hahatool.php` 提供，主题不重复实现。

## 定制

- 改色/间距：编辑 `style.css` 顶部的 CSS 变量；
- 改导航：`header.php` 的 `.nav-links`；
- 加板块：在 `front-page.php` 用 `hahatool_tools()` / `hahatool_channel()` 查询后渲染；
- 新字段展示：在对应 `template-parts/single-*.php` 中用 `hh_meta($id, 'your_key')` 读取（字段需先在 mu-plugin 注册）。

详见仓库根 [docs/WORDPRESS_GUIDE.md](../../../docs/WORDPRESS_GUIDE.md)。
