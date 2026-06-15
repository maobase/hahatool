# AGENTS.md — AI 协作者指南

本项目由 AI 构建并维护。任何 AI 编码代理（Claude Code、Codex、Cursor 等）在本仓库工作时，请遵循本文档。

## 项目一句话

中文 AI 工具导航站：WordPress（内容）+ **两种前台**（Next.js 15 无头前台 / WordPress 主题），Docker Compose 部署。「工具 = WordPress 文章 + 自定义字段（meta）」。两个前台共用同一套数据，`scripts/switch-mode.sh` 切换。

## 常用命令

```bash
docker compose up -d --build        # 启动全套（db + wordpress + frontend）
bash scripts/setup-wp.sh            # 首次：安装 WP + 导入示例数据（幂等可重跑）
cd frontend && npm run dev          # 前台热更新开发（需 WP_API_BASE=http://localhost:8090/wp-json）
cd frontend && npm run build        # 必须通过才能提交
docker compose build frontend && docker compose up -d frontend   # 前台改动上线
```

## 架构与关键文件

| 位置 | 职责 |
| --- | --- |
| `wordpress/mu-plugins/hahatool.php` | 把自定义字段注册进 REST（`HAHATOOL_META_KEYS`）、站内统计端点、放开匿名评论。卷挂载，改完即生效 |
| `wordpress/themes/hahatool/` | WordPress 主题版前台（PHP 渲染）。`inc/helpers.php` 的数据逻辑须与 `lib/api.ts` 对齐；新增 meta 展示要两边同步 |
| `frontend/src/lib/api.ts` | WP REST 客户端（**仅服务端**），60s ISR，失败返回空数据降级。字段映射在 `toTool()`/`toNews()` |
| `frontend/src/lib/client.ts` | 浏览器端：搜索建议 / 评论 / localStorage 收藏 |
| `frontend/src/app/api/wp/[...path]/route.ts` | 浏览器端 REST 代理（端点白名单，勿随意放宽） |
| `frontend/src/lib/types.ts` | `Tool` / `NewsItem` / `Category` 等核心类型 |
| `scripts/seed-data.json` + `seed-wp.php` | 示例数据与导入脚本（按 slug 幂等） |
| `docs/` | 安装 / 开发 / 运营 / WordPress 教程，改了行为要同步更新 |

## 代码约定（必须遵守）

1. **中文注释、中文 UI 文案**；注释只写代码看不出来的约束，不写流水账；
2. **服务端组件优先**：只有需要状态/事件/localStorage 的组件才加 `'use client'`；
3. **样式**：Tailwind 原子类。**暗色模式必须配套**——新增 UI 时给背景/边框/文字补 `dark:` 变体（项目惯例：`bg-white→dark:bg-gray-900`、`border-gray-200→dark:border-gray-800`、`text-gray-900→dark:text-gray-100`）；
4. **品牌色只用 `brand-*`**（CSS 变量驱动，支持 4 套主题色切换），不要硬编码紫色 hex；图表用 `var(--chart-grid)` / `var(--chart-label)` / `rgb(var(--brand-600))`；
5. **图表零依赖**：雷达图/柱状图均为手写 SVG，不引入图表库；
6. **可访问性**：图标按钮必须有 `aria-label`，触控目标 ≥44px，数字列加 `tabular-nums`，禁止用 emoji 当图标；
7. **新增 meta 字段的完整链路**：mu-plugin `HAHATOOL_META_KEYS` → `types.ts` → `api.ts` 映射 → 组件 → `docs/DEVELOPMENT.md` 字段表。

## 保留约定（破坏会导致前台异常）

- 分类 slug `ai-news`（资讯）、`ai-flash`（快讯）是保留值；
- 字段 `url` 是「工具」的身份标识；运营位字段：`featured` / `banner` / `promo`（值见 README 广告位表）；
- 分类展示顺序在 `api.ts` 的 `CATEGORY_ORDER`。

## 验收标准

提交前自查：

1. `cd frontend && npm run build` 零报错；
2. 核心路由 200：`/` `/tools` `/tool/chatgpt` `/compare` `/ranking` `/flash` `/news`；
3. 涉及 UI 的改动：浅色 + 深色都看一眼（可用 playwright-core + 本机 Chrome 截图，参考 git 历史中的 mshot 脚本模式）；
4. 行为变化同步到 `docs/` 与 README。

## Issue 处理流程（本项目特色）

用户被告知「提 Issue 后 AI 自行判断是否迭代」。处理 Issue 时：

1. 复现/确认问题（起本地环境验证）；
2. 自行决定方案并实现，不必等待人类确认细节；
3. 修复需附验证证据（构建通过 + 路由冒烟 + 必要截图）；
4. 提交信息说明动机与改动，关联 Issue 编号。

## 已知约束

- 流量/评分等为运营演示数据，非真实统计；
- 全量工具单次拉取 `per_page=100`，超过 100 个工具需改造为分页查询；
- WordPress 站点 URL 写死在安装时，改端口要同步 `wp option update siteurl/home`。
