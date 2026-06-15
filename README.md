# HahaTool 哈哈工具

<p>
  <img alt="license" src="https://img.shields.io/badge/license-MIT-blue.svg">
  <img alt="Next.js" src="https://img.shields.io/badge/Next.js-15-black?logo=next.js">
  <img alt="React" src="https://img.shields.io/badge/React-19-61dafb?logo=react&logoColor=white">
  <img alt="WordPress" src="https://img.shields.io/badge/WordPress-Headless-21759b?logo=wordpress&logoColor=white">
  <img alt="MySQL" src="https://img.shields.io/badge/MySQL-8.0-4479a1?logo=mysql&logoColor=white">
  <img alt="Docker" src="https://img.shields.io/badge/Docker-Compose-2496ed?logo=docker&logoColor=white">
  <img alt="AI Built" src="https://img.shields.io/badge/Built%20by-AI%20🤖-7c3aed">
</p>

**中文 AI 工具导航站** —— 对标 [toolify.ai](https://www.toolify.ai/zh/) 的开源实现，带有自己的差异化功能（工具 PK、能力雷达、快讯时间线、全站预置广告位）。

**两种渲染模式，同一套数据，按需切换：**

| 模式 | 前台 | 适合 |
| --- | --- | --- |
| 🚀 **无头模式**（默认） | Next.js 15（`:3000`），WordPress 仅作 REST 数据源 | 追求性能/现代前端栈，前后端分离部署 |
| 🎨 **WordPress 主题模式** | WordPress 主题直接渲染（`:8090`），**无需运行 Node** | 只想要一个 WordPress 站点、低运维、虚拟主机部署 |

两种模式共用同一套自定义字段与内容，可用 `bash scripts/switch-mode.sh [theme|headless]` 随时切换，互不影响。

> 🤖 **本项目几乎 100% 由 AI 构建**
>
> 从竞品调研、架构选型、全部代码（后端配置、前端页面、数据种子、部署脚本）、UI 设计、文档撰写到测试验收，均由 AI（Claude）完成。人类只负责极少量的输入——提出想法、说「继续」和偶尔吐槽「太普通了」。
>
> **有问题请直接提 [Issue](https://github.com/maobase/hahatool/issues)。** AI 会阅读每一个 Issue，自行判断是否继续迭代、如何修复，并直接提交改动。你描述清楚现象即可，不必客气，也不必给出解决方案。

---

## 📸 预览

| 首页 | 工具 PK ⭐ |
| --- | --- |
| ![首页](screenshots/home.png) | ![工具PK](screenshots/compare.png) |

| 工具详情（雷达 / 流量分析 / 评论） | 排行榜（领奖台 + 广告位） |
| --- | --- |
| ![详情](screenshots/tool-detail.png) | ![排行榜](screenshots/ranking.png) |

| 深色模式 🌙 | 多主题色（翡翠绿示例）🎨 |
| --- | --- |
| ![深色](screenshots/home-dark.png) | ![翡翠绿](screenshots/home-emerald.png) |

更多截图见 [`screenshots/`](screenshots/)。

## ✨ 功能特性

### 内容与导航
- 🗂 **工具库**：分类 + 定价筛选，最新 / 流量 / 收藏 / 增长 / 评分五维排序
- 🏷 **标签体系**：场景标签页、首页标签云
- 🔍 **即时搜索**：输入即出建议下拉（logo + 简介），支持全文搜索
- 📰 **AI 资讯**：头条特写卡、图文 / 视频富媒体正文（`<video>` / B 站 iframe 自动适配）
- ⚡ **AI 快讯**：短讯时间线频道（按天分组），首页跑马灯联动
- ✍️ **AI 提示词库**：高质量中文 Prompt，场景筛选 + **一键复制** + 详情页使用说明，搜索建议直达

### 差异化亮点
- ⚔️ **工具 PK**（`/compare`）：任选两款工具对打——双系列**能力雷达图**叠加、逐项数据对比并自动标注 A/B 胜，纯表单实现零 JS 依赖
- 📊 **能力雷达**：五维评分（易用性 / 功能强度 / 性价比 / 生态集成 / 中文友好），详情页与 PK 页共用
- 📈 **流量分析**：近 6 月访问量趋势柱状图 + 地区分布条形图（纯 SVG 服务端渲染，零图表库）
- 🏆 **四维排行榜**：流量 / 收藏 / 增长 / 新品榜 + 分类子榜 + 前三名领奖台

### 部署与运营
- 🎭 **双前台模式**：同一套数据，既可用 Next.js 无头前台，也可用 WordPress 主题直接渲染，一行命令切换
- 📡 **站内真实统计**：详情页浏览量与「官网直达」点击自动计数（IP 去重防刷），驱动排行榜**人气榜**
- 🔎 **SEO 完备**：sitemap.xml（全站 80+ URL）、robots.txt、OpenGraph/Twitter 卡片、SoftwareApplication 结构化数据
- 🎨 **多风格主题**：浅色 / 深色 / 跟随系统 × 4 套主题色（紫罗兰 / 海蓝 / 翡翠 / 玫红），全站换肤、首屏无闪烁、本机持久化
- 💬 **评论系统**：基于 WordPress 评论，免登录发表，后台可审核
- ❤️ **本机收藏夹**：localStorage 实现，无需注册登录
- 📣 **8 个运营位全站预置**：空位显示「虚位以待」占位，后台改一个字段即上刊（见下文）
- 🖼 **官网截图 / Logo 自动化**：截图自动生成、Logo 失败降级首字母头像

## 🏗 架构

```mermaid
graph LR
  A[浏览器] -->|3000 无头模式| B[Next.js 15 前台<br/>SSR + ISR 60s]
  A -->|8090 主题模式| C[WordPress + HahaTool 主题<br/>PHP 直接渲染]
  B -->|REST API| C
  C --> D[(MySQL 8)]
  E[wp-admin<br/>内容运营] --> C
  F[mu-plugin<br/>hahatool.php] -.->|注册 meta 到 REST<br/>track 统计 / 匿名评论| C
  C -.->|同一套 meta 字段| C
```

两个前台读取**同一套** WordPress 文章 + 自定义字段。无头前台经 REST API，主题模式由 `wordpress/themes/hahatool/` 的 PHP 模板直接渲染（雷达图/流量图均为 PHP 生成的 SVG，零前端依赖）。

- **工具 = WordPress 文章 + 自定义字段（meta）**：`url / logo / tagline / pricing / likes / monthly_visits / growth / rating / scores / visits_history / regions / faq / screenshot / cover / featured / banner / promo`
- 字段由内置 **mu-plugin** 注册进原生 REST API（`show_in_rest`），无需任何第三方插件
- 前台服务端直连 `wp-json`，浏览器端经 Next.js 白名单代理（规避 CORS）
- 后端不可用时前台优雅降级为空状态，不白屏

## 🚀 快速开始

```bash
git clone https://github.com/maobase/hahatool.git
cd hahatool
cp .env.example .env             # 按需修改密码/端口

docker compose up -d --build     # 启动 db + wordpress + frontend
bash scripts/setup-wp.sh         # 一键：安装 WordPress + 导入示例数据
```

完成后：

| 入口 | 地址 | 说明 |
| --- | --- | --- |
| 无头前台 | http://localhost:3000 | Next.js 渲染（默认前台） |
| WordPress 站点 | http://localhost:8090 | 装上主题后即第二个前台；未装主题时为默认 WP |
| 后台 | http://localhost:8090/wp-admin/ | 内容运营（账号见 `.env`，默认 admin / hahatool_admin） |

**启用 WordPress 主题模式**（让 `:8090` 也渲染整站，无需 Node）：

```bash
bash scripts/switch-mode.sh theme       # 激活 HahaTool 主题
bash scripts/switch-mode.sh headless    # 切回默认主题（仅作 REST 数据源）
```

示例数据：9 个工具分类、14 个标签、28 款主流 AI 工具（含完整数据与雷达评分）、4 篇资讯、8 条快讯。

> 前台有 60 秒 ISR 缓存，导入数据后最多等 1 分钟刷新。详细步骤与常见问题见 [安装手册](docs/INSTALL.md)。

## 📍 页面地图

| 路由 | 页面 |
| --- | --- |
| `/` | 首页（深色 Hero、快讯跑马灯、精选/增长/分类板块、标签云） |
| `/tools` | 工具库（筛选 + 排序 + 信息流广告位） |
| `/tool/[slug]` | 工具详情（截图、雷达、流量分析、FAQ、替代品、评论） |
| `/compare?a=&b=` | 工具 PK 对比 |
| `/ranking?by=&cat=` | 排行榜（流量/收藏/增长/**人气（真实统计）**/新品 五榜 + 分类子榜） |
| `/category/[slug]` / `/tag/[slug]` | 分类页 / 标签页 |
| `/flash` / `/news` / `/news/[slug]` | 快讯时间线 / 资讯列表 / 资讯详情 |
| `/prompts` / `/prompts/[slug]` | 提示词库（场景筛选 + 一键复制） / 提示词详情 |
| `/favorites` / `/search?q=` | 收藏夹 / 搜索 |
| `/submit` | 提交工具（在线表单 → 后台待审，含限流与校验） |

## 📣 广告运营位

全站 **8 个运营位提前预置**，空位渲染「广告位 AD · 虚位以待」占位（自带招商入口）。上刊 = 在 wp-admin 给任意工具加自定义字段：

| 字段 | 位置 | 数量 |
| --- | --- | --- |
| `banner=1` | 首页顶部 Banner 大卡 | 2 |
| `featured=1` | 首页「编辑精选」 | 8 |
| `promo=home-mid` | 首页中部宽幅横幅 | 1 |
| `promo=ranking-top` | 排行榜顶部横幅 | 1 |
| `promo=detail-side` | 全部工具详情页侧栏 | 2 |
| `promo=detail-bottom` | 全部工具详情页底部横幅 | 1 |
| `promo=tools-inline` | 工具库信息流（第 8 卡后） | 1 |
| `promo=news-inline` | 资讯列表信息流 | 1 |

所有推广位自动带「推广」标识。下刊 = 删除字段。详见 [内容运营手册](docs/CONTENT_GUIDE.md)。

## 📂 目录结构

```
hahatool/
├── docker-compose.yml            # db + wordpress + wpcli + frontend
├── .env.example                  # 环境变量模板
├── wordpress/
│   ├── mu-plugins/hahatool.php   # 核心 mu-plugin：meta 注册 / track 统计 / 匿名评论
│   └── themes/hahatool/          # WordPress 主题版（PHP 直接渲染整站）
├── frontend/                     # Next.js 15 前台（App Router + Tailwind）
│   └── src/{app,components,lib}
├── scripts/
│   ├── setup-wp.sh               # WordPress 一键初始化
│   ├── seed-wp.php               # 数据导入（wp eval-file）
│   └── seed-data.json            # 示例数据
├── docs/                         # 安装 / 开发 / 运营手册
└── screenshots/                  # 页面截图
```

## 📚 文档

- [安装手册](docs/INSTALL.md) —— 从零部署到验收、常见问题
- [**WordPress 结合使用教程**](docs/WORDPRESS_GUIDE.md) —— 两种模式架构、wp-admin 实操（收录工具/上刊广告/发快讯）、主题模式切换、REST 调试、接入已有 WP 站点、安全清单
- [主题说明](wordpress/themes/hahatool/README.md) —— 主题模板结构、模式切换、定制指引
- [开发手册](docs/DEVELOPMENT.md) —— 技术栈、目录结构、数据模型、REST 约定、本地热更新
- [内容运营手册](docs/CONTENT_GUIDE.md) —— 添加工具、运营位上刊、发布快讯与图文视频资讯
- [AGENTS.md](AGENTS.md) / [CLAUDE.md](CLAUDE.md) —— AI 协作者指南（本项目由 AI 维护，代理按此规范工作）

## 🗺 Roadmap

- [x] ~~暗色模式~~ → **多风格主题**（明暗 × 4 主题色，v1.1.0）
- [x] **工具提交在线表单**（前台表单 → WordPress 待审文章，运营审核后发布，v1.2.0）
- [x] **提示词频道**（提示词库 + 场景筛选 + 一键复制，v1.3.0）
- [x] **站内真实统计 + SEO**（浏览/点击计数、人气榜、sitemap/OG/结构化数据，v1.4.0）
- [ ] 多语言（i18n）
- [ ] 外部流量数据接入（SimilarWeb 类数据源，替代运营手填的月访问量）
- [ ] 邮件订阅快讯 / 周报

想要哪个先做？提 Issue 投票，AI 看得到。

## 🤝 贡献

- **提 Issue 是最有效的贡献方式**：bug、新功能、设计吐槽都行。AI 会阅读并自行判断是否迭代。
- 也欢迎 PR：请保持与现有代码风格一致（中文注释、Tailwind、服务端组件优先）。
- 提交前请确认 `cd frontend && npm run build` 通过。

## ⚠️ 免责声明

示例数据中的流量、收藏、增长率、评分等均为**演示用途的运营填充数据**，不代表真实统计；收录工具的商标与 Logo 归各自权利人所有。

## 📄 License

[MIT](LICENSE) © maobase

> WordPress 本身遵循 GPL-2.0（通过官方 Docker 镜像引用，本仓库不包含其源码）。
