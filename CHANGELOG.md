# Changelog

本项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。分支策略：`main` 为稳定分支，功能在 `feat/*` 分支开发后合入，每次发布打 `vX.Y.Z` 标签。

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
