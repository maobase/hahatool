# WordPress 结合使用教程

本文讲清楚一件事：**HahaTool 是怎么把 WordPress 当「无头 CMS」用的，以及你如何在 wp-admin 里完成全部内容工作**。零 WordPress 经验也能照着做。

## 1. 两种模式：无头 vs 主题

HahaTool 把内容（WordPress）和渲染（前台）解耦，于是有**两种前台模式，共用同一套内容**：

```
                       ┌──► REST API ──► Next.js 前台 (:3000)   ← 无头模式（默认）
你在 wp-admin 写内容 ──┤
   存进 MySQL          └──► HahaTool 主题 PHP 渲染 (:8090)       ← 主题模式（无需 Node）
```

- **无头模式**：WordPress 只当数据源，前台用 Next.js（性能好、现代栈、前后端分离）。
- **主题模式**：激活 `wordpress/themes/hahatool` 主题，WordPress 自己把 `:8090` 渲染成完整导航站，普通 PHP 虚拟主机就能跑，不用 Node。

切换（数据不变，随时来回切）：

```bash
bash scripts/switch-mode.sh theme       # 启用主题模式
bash scripts/switch-mode.sh headless    # 切回无头模式
```

两种模式共用：
- **mu-plugin（`wordpress/mu-plugins/hahatool.php`）**：把自定义字段（url、logo、评分等）注册进 REST、提供站内统计端点、放开匿名评论。放在 `mu-plugins` 目录所以**免安装、免激活、不可误停用**。
- **主题（`wordpress/themes/hahatool/`）**：PHP 模板版前台，雷达图/流量图用 PHP 生成 SVG，零前端依赖。详见 [主题说明](../wordpress/themes/hahatool/README.md)。

记住核心心智模型：**「工具」= 一篇 WordPress 文章 + 一组自定义字段**。两个前台读的是同一篇文章，所以你在 wp-admin 的任何改动对两边同时生效。分类、标签、评论全部用 WordPress 原生功能。

## 2. 第一次进后台必做的一件事

自定义字段面板默认是隐藏的：

1. 登录 `http://localhost:8090/wp-admin/`（账号见 `.env`，默认 `admin / hahatool_admin`）；
2. 打开任意文章编辑页 → 右上角 **⋮** → **首选项** → **面板** → 打开 **「自定义字段」**；
3. 回到编辑页，滚动到底部就能看到「自定义字段」表单区。只需设置一次。

## 3. 实操：收录一个新工具（约 2 分钟）

以收录「Gemini」为例：

1. **文章 → 写文章**；
2. 标题填 `Gemini`；右侧 **链接 → 别名** 填 `gemini`（决定前台地址 `/tool/gemini`）;
3. 正文用块编辑器写三段（前台详情页直接渲染）：
   - `## Gemini 是什么？` + 一段介绍
   - `## 核心功能` + 列表
   - `## 定价` + 一段
4. 右侧 **分类** 勾选「聊天机器人」；**标签** 填 `对话助手`；
5. 底部 **自定义字段**，逐个「输入新内容」添加：

   | 名称 | 值 |
   | --- | --- |
   | `url` | `https://gemini.google.com` |
   | `logo` | `https://favicon.im/gemini.google.com?larger=true` |
   | `tagline` | `Google 出品的多模态 AI 助手` |
   | `pricing` | `免费增值` |
   | `rating` | `4.6` |
   | `likes` | `3200` |
   | `monthly_visits` | `320000000` |
   | `growth` | `12.5` |
   | `scores` | `易用性:9,功能强度:9,性价比:9,生态集成:9.5,中文友好:7` |

6. **发布**。最多 60 秒后，前台工具库、对应分类页、排行榜里都会出现 Gemini，详情页自动有雷达图和数据卡。

> ⚠️ 字段名全部**小写**；数字字段填**纯数字**（别带「万」「亿」「%」）；`url` 是工具的身份标识——没有它这篇文章不会被前台当成工具。

可选进阶字段：`visits_history`（近 6 月访问量，逗号分隔 → 流量趋势图）、`regions`（`美国:20,中国:15,其他:65` → 地区分布）、`faq`（每行 `问题|答案` → 折叠 FAQ）、`screenshot`（官网截图 URL，不填会自动生成）。

## 4. 实操：广告位上刊 / 下刊

全站 8 个运营位（位置清单见 README）。上刊 = 给某个工具加一个字段：

1. 打开该工具的文章编辑页；
2. 自定义字段添加：名称 `promo`，值如 `home-mid`；
3. 更新。首页中部横幅立刻换成它（带「推广」标识）。

下刊 = 删除这个字段（字段行末的「删除」按钮）。`banner=1`（首页顶部大卡）和 `featured=1`（编辑精选）同理。

## 5. 实操：发快讯和图文/视频资讯

**快讯**：写文章 → 分类勾「AI快讯」→ 标题即快讯内容（30 字内）→ 正文一两句 → 发布。出现在首页跑马灯、`/flash` 时间线。

**图文/视频资讯**：写文章 → 分类勾「AI资讯」：

- 封面：自定义字段 `cover` = 图片 URL；
- 插图：直接用「图片」块（可上传到 媒体库 或贴外链）；
- 视频：加一个「自定义 HTML」块，粘贴：
  ```html
  <video src="https://example.com/demo.mp4" controls preload="metadata"></video>
  <!-- 或 B 站嵌入 -->
  <iframe src="//player.bilibili.com/player.html?bvid=BV1xxxx" allowfullscreen></iframe>
  ```
  前台自动渲染为 16:9 圆角播放器。

## 6. 评论管理

前台评论免登录（mu-plugin 放开了匿名评论），演示环境**免审核直接显示**（`setup-wp.sh` 设置）。管理入口：后台 → **评论**（批准/回复/移到垃圾）。

**生产环境建议**：设置 → 讨论 → 勾选「评论必须经人工批准」，并考虑装 Akismet 反垃圾。

## 7. 调试：直接看 REST API 返回什么

前台显示不对时，先确认数据源：

```bash
# 文章列表（看 meta 字段是否带出来了）
curl 'http://localhost:8090/wp-json/wp/v2/posts?slug=gemini' | python3 -m json.tool

# 分类 / 标签
curl 'http://localhost:8090/wp-json/wp/v2/categories?per_page=100'
curl 'http://localhost:8090/wp-json/wp/v2/tags?per_page=100'

# 某篇文章的评论
curl 'http://localhost:8090/wp-json/wp/v2/comments?post=4'
```

检查点：`meta` 对象里有没有你填的字段 → 没有就检查字段名拼写，或该字段是否已在 mu-plugin 注册；`_embedded['wp:term']` 里有没有分类标签（前台请求带 `_embed=wp:term`）。

前台拿到数据但页面没变？等 60 秒（ISR 缓存）或重启前台容器：`docker compose restart frontend`。

## 8. 进阶：新增一个自定义字段（端到端）

假设要加「开源协议 `license`」字段并展示在详情页：

1. **注册到 REST**：`wordpress/mu-plugins/hahatool.php` 的 `HAHATOOL_META_KEYS` 数组里加 `'license'`（文件是卷挂载，保存即生效，无需重启）；
2. **类型**：`frontend/src/lib/types.ts` 的 `Tool` 接口加 `license: string;`；
3. **映射**：`frontend/src/lib/api.ts` 的 `toTool()` 加 `license: meta(p, 'license'),`；
4. **展示**：在 `app/tool/[slug]/page.tsx` 的「工具信息」卡里加一行；
5. 后台给文章填 `license` 字段 → `docker compose build frontend && docker compose up -d frontend`。

## 9. 接入你已有的 WordPress 站点

HahaTool 前台可以对接任何 WordPress（不限于本仓库的容器）：

1. 把 `wordpress/mu-plugins/hahatool.php` 复制到你站点的 `wp-content/mu-plugins/`（目录不存在就创建）；
2. 前台环境变量 `WP_API_BASE` 指向你的站点：`https://你的域名/wp-json`；
3. 确认 REST API 未被安全插件禁用（访问 `/wp-json/wp/v2/posts` 能出 JSON）；
4. 按本文第 3 节的字段约定补内容；分类需包含保留 slug：`ai-news`（资讯）、`ai-flash`（快讯）；
5. 浏览器端功能（搜索建议/评论/收藏页）走 Next.js 代理，无需在 WordPress 侧配 CORS。

## 10. 安全清单（生产部署前过一遍）

- [ ] 修改 `.env` 全部默认密码（数据库、WP 管理员）；
- [ ] 开启评论人工审核（见第 6 节）；
- [ ] WordPress 站点 URL 改为真实域名：`wp option update siteurl/home`；
- [ ] 8090 端口不要暴露公网——wp-admin 建议放内网或加访问控制，前台只需要能从 Next.js 服务端访问 `wp-json`；
- [ ] 保持 WordPress 自动更新开启（官方镜像重建即升级）。
