# Changelog

本项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。分支策略：`main` 为稳定分支，功能在 `feat/*` 分支开发后合入，每次发布打 `vX.Y.Z` 标签。

## [v1.5.1] - 2026-06-15

### 修复（WordPress 主题响应式与易用性）
- 修复工具 PK 页移动端横向溢出（对手卡片两列改为响应式 `.vs-grid`；详情网格加 `min-width:0` 防 min-content 撑开；数据表改为可横向滚动）
- 修复顶栏「提交工具」按钮在窄屏换行（`.btn` 加 `white-space:nowrap`；移动端隐藏顶栏按钮，移入汉堡菜单）
- 移动端汉堡菜单新增搜索框（此前非首页移动端无搜索入口）+ 工具PK/提交工具 入口
- 移动端隐藏品牌副标题、缩放 Hero 统计字号，避免拥挤与文字换行
- 工具详情页移动端头部改为纵向堆叠、操作按钮占满宽度
- 汉堡菜单同步 `aria-expanded`（无障碍）
- 全站在 320 / 375 / 414 px 三档实测零横向溢出；暗色×4 主题色、复制、评论、提交表单端到端验证通过

## [v1.5.0] - 2026-06-15

### 新增
- **WordPress 主题版本**（`wordpress/themes/hahatool/`）—— 在原有无头模式之外新增第二种前台：
  WordPress 主题直接用 PHP 渲染整站，**无需运行 Node**，普通 PHP 虚拟主机即可部署
  - 完整页面：首页（Hero/统计/分类条/精选/增长/分类/提示词/资讯板块）、工具详情
    （能力雷达/流量分析/FAQ/评论/替代品/侧栏推广）、工具库、排行榜（含人气榜）、
    工具 PK（双系列雷达）、提示词库、AI 资讯/快讯、搜索、提交工具
  - 能力雷达图、流量趋势/地区分布图均由 PHP 生成 SVG，零前端依赖
  - 明暗模式 + 4 套主题色（CSS 变量 + 轻量 theme.js，localStorage 持久化、首屏防闪烁）
  - 与无头前台共用 mu-plugin、自定义字段、保留分类与数据契约（`hahatool_is_tool()` 等对齐 `lib/api.ts`）
  - `/tools` `/ranking` `/compare` `/submit` 为虚拟路由（rewrite + template_include），无需建页面
- `scripts/switch-mode.sh [theme|headless]`：一行命令在两种模式间切换
- 主题随 docker-compose 挂载；新增 `wordpress/themes/hahatool/README.md` 主题说明

## [v1.4.0] - 2026-06-12

### 新增
- **站内真实统计**：详情页浏览量（views）与「官网直达」点击（clicks）自动计数
  - mu-plugin 自定义端点 `POST /hahatool/v1/track`（纯计数器自增，不产生文章修订）
  - Next `/api/track` 代理：IP+条目+类型 30 分钟去重防刷；浏览每会话仅上报一次
  - 详情页展示「站内浏览 / 官网直达」真实数据；排行榜新增**人气榜**（按真实浏览量）
- **SEO 完善**：动态 sitemap.xml（80+ URL，每小时再生）、robots.txt、
  工具详情 OpenGraph/Twitter 卡片（截图作 og:image）、SoftwareApplication JSON-LD 结构化数据
- 全局路由加载骨架屏（loading.tsx）

### 修复
- 站点对外地址改由 `SITE_URL` 环境变量驱动（compose build args 注入构建期产物）

## [v1.3.0] - 2026-06-12

### 新增
- **AI 提示词库**（`/prompts`）：12 条高质量中文 Prompt 示例数据
  - 场景筛选（写作/编程/营销/办公/学习/绘画）、按热度排序、**一键复制**（含降级方案）
  - 提示词详情页：全文、使用说明、同场景推荐
  - 首页「热门提示词」板块；搜索建议与搜索结果页支持提示词分组直达
  - 新增 meta 字段：`prompt` / `prompt_scene` / `prompt_model`；保留分类 `ai-prompts`

## [v1.2.0] - 2026-06-12

### 新增
- **工具提交在线表单**（`/submit`）：前台表单直接写入 WordPress 待审（pending）文章，运营在 wp-admin 审核补全后发布
  - 服务端接口 `/api/submit`：字段校验、每 IP 每小时 3 次限流、Application Password 鉴权（凭据仅服务端持有）
  - `scripts/setup-wp.sh` 自动创建 Application Password 并写入 `.env`
  - mu-plugin 放开本地 http 环境的 Application Passwords（生产请用 HTTPS）
- `CHANGELOG.md` 与版本标签管理

### 文档
- 内容运营手册新增「审核用户提交的工具」流程

## [v1.1.0] - 2026-06-12

### 新增
- **多风格主题系统**：浅色 / 深色 / 跟随系统 × 4 套主题色（紫罗兰 / 海蓝 / 翡翠 / 玫红）
  - 品牌色 CSS 变量化（运行时换肤），全站 `dark:` 暗色适配，首屏内联脚本防闪烁，localStorage 持久化
- `docs/WORDPRESS_GUIDE.md`：WordPress 结合使用教程（Headless 架构、wp-admin 实操、REST 调试、接入已有站点、安全清单）
- `AGENTS.md` / `CLAUDE.md`：AI 协作者规范

## [v1.0.0] - 2026-06-12

首个开源版本。

- Headless WordPress + Next.js 15 + MySQL 8，Docker Compose 一键部署
- 工具库（筛选/排序）、详情页（截图/评分/能力雷达/流量分析/FAQ/替代品/评论/收藏）
- 差异化功能：工具 PK 对比页（双系列雷达图 + A/B 胜负标注）
- 四维排行榜（领奖台）、AI 快讯时间线 + 首页跑马灯、富媒体资讯
- 即时搜索建议、本机收藏夹、8 个全站预置广告运营位
- 示例数据（28 工具 / 4 资讯 / 8 快讯）与一键初始化脚本、完整中文文档
