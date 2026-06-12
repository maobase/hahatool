# CLAUDE.md

> Claude Code 在本仓库工作时的指引。完整协作规范见 [AGENTS.md](AGENTS.md)，本文件只放高频要点。

## 项目速览

中文 AI 工具导航站。Headless WordPress（wp-admin 管内容，REST API 出数据）+ Next.js 15 前台（App Router + Tailwind + ISR 60s）+ MySQL 8，Docker Compose 部署。核心心智模型：**工具 = WP 文章 + meta 字段**（`url` 字段是工具的身份标识）。

## 命令

```bash
docker compose up -d --build && bash scripts/setup-wp.sh   # 从零起全套（setup 幂等）
cd frontend && npm run build                               # 提交前必须通过
docker compose build frontend && docker compose up -d frontend  # 前台上线
```

本地地址：前台 :3000、后台 :8090/wp-admin（端口可被 .env 覆盖）。

## 改代码时的硬约束

- 中文注释与 UI 文案；服务端组件优先，`'use client'` 仅限有状态/事件的组件；
- 新 UI **必须补 `dark:` 暗色变体**（惯例映射见 AGENTS.md §代码约定）；品牌色只用 `brand-*`（CSS 变量，4 套主题色切换依赖它），禁止硬编码紫色 hex；
- 图表手写 SVG，不引图表库；图标用 lucide-react，禁止 emoji 图标；
- 新增 meta 字段链路：`wordpress/mu-plugins/hahatool.php` 的 `HAHATOOL_META_KEYS` → `lib/types.ts` → `lib/api.ts` 的 `toTool()` → 组件 → `docs/DEVELOPMENT.md` 字段表；
- 浏览器端取数一律走 `/api/wp/*` 代理（白名单在 `app/api/wp/[...path]/route.ts`，勿随意放宽）；
- 保留值勿动：分类 slug `ai-news` / `ai-flash`，字段 `featured` / `banner` / `promo`。

## 验收

build 零报错 + 核心路由冒烟（`/` `/tools` `/tool/chatgpt` `/compare` `/ranking`）+ UI 改动浅/深两色各看一眼 + 文档同步。

## Issue 即工单

用户提的 Issue 由 AI 自主判断与实现：先本地复现，再决策方案，修复附验证证据，提交信息关联 Issue。
