# Changelog

本项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。分支策略：`main` 为稳定分支，功能在 `feat/*` 分支开发后合入，每次发布打 `vX.Y.Z` 标签。

## [v1.6.112] - 2026-06-22

### SEO / 资讯（迭代 69：RSS Feed 修正与净化 —— 目标 #2 #4）
审计 RSS 发现两处问题并修复（JustNews 式资讯站应有干净可订阅的 Feed）：
- **Feed 语言纠正**：`<language>` 原为 `en-US`（站点 locale 默认值，与中文内容不符，影响阅读器分类与 SEO）→ 经 `bloginfo_rss` 过滤改为 `zh-CN`。
- **主 Feed 净化**：全站 `/feed/` 原本混入提示词（实用模板而非文章，作订阅条目突兀）。经 `pre_get_posts` 在主 Feed 排除 `ai-prompts`，保留资讯/快讯/新工具；**分类/标签 Feed 不受影响**（`/news/feed/` 仍专供资讯）。
- 既有：Feed 自动发现 `<link rel=alternate>` 已在 `<head>`（`automatic-feed-links`）。
- 主题 1.6.104 → 1.6.105。实测：`/feed/` `<language>zh-CN</language>`、10 条且无提示词条目；`/news/feed/` 正常。

## [v1.6.111] - 2026-06-22

### 打磨（迭代 68：移动端 QA 巡检 + 工具详情流量分析标题修正 —— 目标 #1）
按「每页在不同设备尺寸下截图检查」，对首页 / 工具详情 / 排行榜 / 工具 PK / 资讯等在 390px 手机宽度逐一截图巡检：整体布局、卡片、雷达图、领奖台、筛选条均良好；自托管 Logo 在移动端渲染清晰。
- 发现并修正一处细节：工具详情「XX 流量分析」标题把「数据由运营整理，仅供参考」内联在 `h2`（`display:flex`）里，窄屏下与标题挤在一行、折行难看。改为标题下方独立的 `<p class="muted">` 小字副标题，浅深与各尺寸均整洁。
- 主题 1.6.103 → 1.6.104。实测：`/chatgpt/` 流量分析标题与免责声明分两行；移动端各页截图确认无溢出/错位。

## [v1.6.110] - 2026-06-22

### 内容（迭代 67：填充「AI 版支付宝 / 阿宝」资讯 —— 目标 #2 #3）
继续用科技热榜当线索源，核实后入库一条新鲜、对口（AI 智能体 / 超级 App）的资讯：
- **支付宝上线「AI 版」：智能助手「阿宝」能点外卖、打车、记账，超级 App 卷向 Agent OS**（6/18，爱范儿实测）—— 一句话调度支付宝内服务（麦当劳下单、收蚂蚁森林能量、调高德打车、一句话记账、小票自动记账、分析消费习惯）；**如实写出能力边界**（12306 能打开但不能自动完成）；定位「Agent OS」。
- 蓝色（支付宝）品牌封面 SVG+PNG 入对象存储；新增 `scripts/seed-real-news-11.php`；`wp cache flush`。
- ai-news 33 → 34；实测：文章 200、og:image=PNG、列表头部即新文。

### 质量（顺带：工具 Logo 自托管效果复核）
对上轮迁移的 28 个自托管 Logo 抽查最小的几个（tongyi-lingma/github-copilot/copy-ai/runway），渲染图确认均为有效品牌标（非空白占位），小体积只是单色简单图标——迁移结果可靠，无需修补。

## [v1.6.109] - 2026-06-22

### 性能 / 对象存储（迭代 66：全部 28 个工具 Logo 自托管，彻底去除 favicon.im 第三方依赖 —— 目标 #3）
此前每个列表页/详情页都要向第三方 `favicon.im` 拉 N 个工具图标（外部请求、可被墙、不可控）。本轮把全部工具 Logo 迁入自有对象存储：
- 取图源用 Google favicons（服务端可达；favicon.im 对数据中心 IP 返回 403）。25 个直接拿到 128px PNG；canva/gamma 为 JPEG、elevenlabs 的 Google 图是空白占位 → 回退 DuckDuckGo ico；按真实格式定扩展名上传 MinIO `tools/logos/`，并重写各工具 `logo` meta。
- 结果：favicon.im 0 个、自托管 28 个；首页 favicon.im 请求数 0；Logo 文件 `max-age=31536000, immutable`，content-type 正确（png/jpeg/x-icon）。
- 配套：og:image 逻辑改为「资讯封面 / 上传截图」二选一，彻底不纳入小尺寸 Logo（自托管后也不会误把 128px 图标当社交大图），无大图回退品牌卡 og-default.png。取代上轮的 favicon 字符串判断。
- 迁移脚本 `scripts/migrate-tool-logos.sh`（幂等可重跑）。主题 1.6.102 → 1.6.103。实测：工具网格 Logo 全部自托管正常渲染；`/chatgpt/` og:image 仍为品牌卡。

## [v1.6.108] - 2026-06-22

### SEO / 社媒（迭代 65：工具页社交卡改用自有品牌卡，去除第三方 favicon 依赖 —— 目标 #3 #4）
审计发现全部 28 个工具详情页的 `og:image` / `twitter:image` 都指向第三方 `favicon.im` 小图——做 `summary_large_image` 时被裁切糊化，且是外部请求。
- 在 wp_head OG 逻辑中：若候选图为 favicon 链接则丢弃，回退到自有对象存储的品牌社交卡 `og-default.png`（1200×600）。资讯封面 / 上传截图不受影响（实测资讯页仍用各自 PNG 封面）。
- 工具页分享预览从「拉伸的小图标」变为专业品牌卡，且零第三方图片请求。
- 顺手修正 `/hot` 的 meta description（迭代 60 已过滤掉知乎，但描述文案仍写着「知乎」，现同步为真实科技源）。
- 主题 1.6.101 → 1.6.102。实测：`/chatgpt/` og:image=og-default.png（summary_large_image）；资讯页 og:image 仍为自身封面 PNG；缓存与静态资源审计确认 CSS/JS/字体/封面均为 `max-age=31536000, immutable`（目标 #3 已达标）。

## [v1.6.107] - 2026-06-22

### 内容（迭代 64：扩充提示词库 +4 条原创精品 —— 目标 #2）
提示词库此前仅 12 条、部分场景偏薄（绘画仅 1）。本轮新增 4 条原创、即用型中文提示词（提示词是实用模板而非事实陈述，无核实问题）：
- **Midjourney 摄影级提示词生成器**（绘画）：主体+环境+光线+镜头+风格+画质 结构化产出 + 参数。
- **电商详情页卖点提炼**（营销）：核心卖点 + FABE 清单 + 主文案 + 促单短句。
- **会议纪要智能整理器**（办公）：结论/待办表格/分歧项，强约束「只基于原文不臆测」。
- **公众号深度长文框架**（写作）：备选标题 + 开头钩子 + 小标题论点 + 金句 + CTA。
- 另 2 条（代码审查、费曼学习法）因同名 slug 已存在被幂等跳过（库中本已覆盖）。
- 提示词总数 12 → 16；`scripts/seed-prompts-1.php` 幂等可重跑。实测：`/prompts` 200 且 4 条均在；详情页多行提示词格式完好、复制模块/场景侧栏/热门榜均正确收纳新条目。

## [v1.6.106] - 2026-06-22

### 体验 + SEO（迭代 63：标签归档页补结构化数据 + 打磨 —— 目标 #1 #4）
标签页是站内最后一个缺结构化数据、且页头最朴素的列表页。对齐 /tools、排行榜、专题等做法：
- 新增 `CollectionPage` + `ItemList`（本页工具）+ `BreadcrumbList` 结构化数据。
- 页头加面包屑（首页 / 全部工具 / # 标签）+ 图标标题，与其他频道页风格一致。
- 「相关标签」加小标题、保留计数；空态从一行字升级为图标 + 文案 + 「浏览全部工具」按钮；底部加「提交工具 / 浏览全部」CTA。
- 主题 1.6.100 → 1.6.101。实测线上：`/tag/made-in-china/` 200，含 BreadcrumbList + ItemList；浅/深两色截图均正常（国产工具 10 款正确聚合）。

## [v1.6.105] - 2026-06-22

### 内容（迭代 62：填充「OpenAI Codex 录制回放」资讯 —— 目标 #2 #3）
本轮从已过滤的科技热榜里找到 AI 工具线索，并多源核实后入库一条新鲜、对口（AI 编程工具）的资讯：
- **OpenAI Codex 上线「录制回放」：演示一遍，AI 自动打包成可复用技能**（6/18）—— 演示一遍操作 → Codex 观察并生成自然语言 SKILL.md 技能；回放时由模型结合当前屏幕灵活执行（语义自适应，区别于写死坐标的传统 RPA）；随 Codex app v26.616 推送，面向 ChatGPT 付费档，发布时未在 EEA/英国/瑞士上线。
- 核实链路：爱范儿原文（ifanr.com/1669204）+ techtimes(20260620) 等英文源交叉确认发布日期 6/18 与版本号 v26.616，非编造。
- 绿色品牌封面 SVG+PNG 入对象存储；新增 `scripts/seed-real-news-10.php`；`wp cache flush`。
- ai-news 32 → 33；实测线上：文章 200、og:image=PNG、列表头部即新文、品牌封面正常。

## [v1.6.104] - 2026-06-22

### 体验（迭代 61：提交页升级为双栏「转化页」—— 目标 #1，页面更丰富）
`/submit` 此前是单列窄表单（标准 + 表单），略显单薄、也没说清「为什么要提交」。本轮做成更有说服力的双栏页：
- 主栏保留收录标准 + 在线提交表单；右侧新增 **侧栏两件套**：①「收录后能获得什么」4 条权益（免费永久收录 / 完整工具详情页 / 首页与榜单曝光 / 真实用户互动，带图标）；②「常见问题」4 条可折叠 FAQ（收费、审核时长、信息修改、推广位）。
- 副标题补 **社会证明**「已收录 N+ 款工具」（取站内工具总数）。
- 成功态补「先逛逛工具库」CTA。复用既有 `detail-grid` / `.panel.faq`，移动端自动单列；主题 1.6.99 → 1.6.100。
- 实测线上：`/submit` 200，权益 + FAQ + 社会证明齐全；浅/深两色截图均正常。

## [v1.6.103] - 2026-06-22

### 内容质量（迭代 60：热榜只保留科技源，名实相符 —— 目标 #5）
之前 `/hot`「AI · 科技热榜」把 momoyu 全部 13 个源原样拉入，导致页面充斥微博热搜、豆瓣热话、虎扑、值得买等泛娱乐/生活内容，与「AI 与科技热点」标题严重不符。
- 在 `hahatool_fetch_hot()` 加**科技源白名单**（按 source_key）：保留 IT之家 / 中关村在线 / 爱范儿 / CSDN / 虎嗅 / 掘金，并预留 36氪/少数派/机器之心/量子位/极客公园等常见科技源 key 以兼容 momoyu 未来调整；过滤掉微博/豆瓣/今日头条/虎扑/B站/值得买/知乎等泛源。
- **安全兜底**：万一白名单全数未命中（momoyu 改 key），回退全部源，绝不出空页。
- 同步修正 `/hot` 页与首页热榜 teaser 的来源描述文案（去掉「知乎」，列真实科技源）。
- 部署后删除旧 `hahatool_hot` / `hahatool_hot_stale` transient，过滤即时生效。
- 实测线上：REST 与页面均为 6 个科技源 / 72 条；首屏标题已是小米/苹果/蔚来/AI/显示器等科技内容。主题 1.6.98 → 1.6.99。

## [v1.6.102] - 2026-06-22

### 体验 + SEO（迭代 59：热榜页加数据条/面包屑/结构化数据 + 站点地图按枢纽精确 lastmod）
两项实质改进：

1. **热榜页（/hot，目标 #5 + #1）更丰富**：此前只有标题 + 网格。新增 ①面包屑（首页 / 热榜）；②`.dir-stats` 数据条（站点源数 / 实时热点条数 / 15min 缓存刷新 / 最近更新时间）；③`CollectionPage` + `BreadcrumbList` 结构化数据。复用工具库页同款数据条样式，视觉一致。
2. **站点地图 hubs provider 按枢纽精确 lastmod（目标 #4）**：旧实现 8 个枢纽共用「全站最新修改时间」，加一条资讯会误标提示词页/工具页也更新。改为各枢纽取自身相关内容的最新修改时间——`/news /flash /prompts` 取各自分类、`/tools /ranking /compare /topics` 取工具（非保留分类文章）、`/hot` 为外部聚合不输出 lastmod（不虚报）。利于搜索引擎按需重抓。

- 主题 1.6.97 → 1.6.98。实测：`/hot` 200，数据条 13/156/15min/04:12，浅深两色正常；hubs 站点地图各枢纽 lastmod 各异（/tools 6-10、/news 6-18、/flash 6-16、/prompts 6-11、/hot 无）。

### 内容（本轮再次坚持核实优先，暂不入库）
比对 Anthropic 官方发布说明，确认到「6/9 发布 Claude Fable 5 → 6/12 暂停访问」的一手事实；但该模型当前状态可能已变化、单一来源无法确认是否已恢复，为免误导本轮不入库。

## [v1.6.101] - 2026-06-22

### 体验（迭代 58：快讯页升级为「双栏 + 侧栏」—— 目标 #2，加强快报模块）
`/flash` 快讯页此前是单列时间线、右侧大片留白，相比 `/news` 的双栏布局偏单薄。本轮对齐资讯频道，把它做成功能更丰富的双栏页：
- 主栏保留按天分组的时间线；右侧新增 **侧栏三件套**：①「想看完整报道？」快讯→资讯的引流 CTA（快讯只给一句话，深读去 AI 资讯）；②「热门资讯」榜单；③「本周热门工具」。
- 标题补「当前 N 条」计数；空态从死胡同改为引导去 AI 资讯。
- 复用既有 `detail-grid`（移动端自动塌成单列）、`hahatool_hot_news_panel`、`hahatool_hot_tools`，无新增样式。主题 1.6.96 → 1.6.97。
- 实测线上：`/flash` 200，侧栏三块齐全；桌面浅/深两色 + 移动端单列三态截图均正常。

### 内容（本轮放弃一条不可靠资讯，坚持「逐条核实不编造」）
搜到「智谱 GLM-5.2 开源 / 1M 上下文 / MIT 协议」的二手报道，但比对智谱官方文档（docs.bigmodel.cn）为 **GLM-5、200K 上下文**，版本号/上下文/协议三处与一手来源冲突，且无法确认确切发布日期，故本轮不入库该条，待有一手可核实信息再补。

## [v1.6.100] - 2026-06-22

### 体验（迭代 57：工具库页加「数据条」+ 充实空态 —— 目标 #1，页面更丰富）
站点最高频的 `/tools` 目录页此前页头只有「全部工具 + 一行计数」，偏单薄。本轮让它更像一个有数据感、权威的工具目录：
- 页头下新增 **4 格数据条**：收录工具（全量）、工具分类数、免费/免费增值数、本月更新数（本月为 0 时回退「平均评分」，避免出现 0）。统计基于筛选前全量。
- 副标题补「附流量数据、定价与真实点评」价值主张。
- 充实**空筛选态**：图标 + 「没有符合当前筛选条件的工具」+「清除全部筛选」按钮 +「大家都在用」8 张热门工具卡，原来的死胡同变成可继续浏览的发现位。
- 新增浅底 `.dir-stats` 样式（区别于深色 hero-stats），4 列桌面 / 2 列移动，浅深两色都适配；主题 1.6.95 → 1.6.96。
- 实测线上：`/tools` 200，数据条 28+/9/25/19；空态出现清除筛选 + 热门工具；浅/深两色与移动端均正常。

## [v1.6.99] - 2026-06-22

### 体验（迭代 56 #1：把 404 死胡同改造成「发现页」—— 目标 #1，页面更丰富）
按「每个页面布局细节都要打磨、功能内容越丰富越好」，把仅有搜索框 + 几个按钮的 404 升级为完整发现页：
- 新增「按场景逛专题」专题胶囊（取热门 topic 6 个，链到专题页）。
- 新增「最新 AI 资讯」清单（最近 4 条，带日期 + 跳转，末尾「查看全部资讯」）。
- 保留原有：大号 404、搜索框、返回首页/工具库/资讯入口、「大家都在用的工具」热门胶囊。
- 补 `.clamp1` 单行截断工具类；主题版本 1.6.94 → 1.6.95。
- 实测线上：HTTP 状态码仍为 404（不污染 SEO）；浅/深两色均正常；新加的智能眼镜资讯即出现在「最新资讯」中。

## [v1.6.98] - 2026-06-22

### 内容（迭代 56：填充「智能眼镜/XR」资讯 —— 目标 #2 #3）
拓宽到「科技硬件」方向，搜罗到一条新鲜（6/18）且角度全新（智能眼镜/XR，此前未覆盖）的资讯，WebFetch 核实后入库：
- **2026 最强智能眼镜扎堆：Xreal Aura、Snap SPECS 发布，但「iPhone 时刻」未到**（6/18，爱范儿核实）—— 谷歌×Xreal 的 Xreal Aura（骁龙 Reality Elite + Android XR，95g/70°FOV/双芯片，秋季多国上市，预订 99 美元）+ Snap SPECS（一体化 AR，132–136g/51°FOV/4h，2195 美元，Snap OS）；作者认为行业仍在野蛮生长期。
- SVG + PNG 双格式品牌封面（青色 XR 配色）入对象存储；新增 `scripts/seed-real-news-9.php`；入库后 `wp cache flush`。
- ai-news 31 → 32；内容新增「智能眼镜 / XR / 科技硬件」维度。
- 实测线上：文章 200、og:image=PNG、列表头部即新文、品牌封面正常。

## [v1.6.97] - 2026-06-22

### 界面（迭代 55：收藏空态加「先收藏这些热门工具」—— 目标 #1）
延续「内容越丰富越好」：我的收藏空态原本只有图标 + 提示 + 一个 CTA，是个死胡同。在空态内补 **「先从这些热门工具收藏起」+ 8 张按流量排序的热门工具卡**（带心形按钮，可当场收藏）。
- 卡片就在 `#favEmpty` 内，靠既有 JS（无收藏时显示空态）天然只在「空收藏」时出现，无需改 JS；卡片心形按钮复用全局收藏逻辑，收藏后即并入收藏网格、空态隐藏。
- 把空收藏页从「死胡同」变成「可立即上手收藏」的发现页。
- 实测线上：空态含「先从这些热门工具收藏起」+ 8 卡；`php -l` 通过；截图确认满屏可用。

## [v1.6.96] - 2026-06-22

### 界面（迭代 54：搜索页发现区，空结果不再死胡同 —— 目标 #1）
延续「内容越丰富越好」：搜索结果页原本无结果时只有一行提示 + 大片空白。新增**发现区**：
- **热门搜索胶囊**（常驻底部）：AI 写作 / 绘画 / 视频生成 / 编程 / 数字人 / 音乐 / 提示词 / 智能体，点击直接换词搜。
- **「大家都在用」热门工具网格**（结果 < 4 条时出现）：8 张按流量排序的工具卡 + 「全部工具」入口，把「0 结果」变成有用的发现页。
- 实测线上：空搜索含热门搜索 + 大家都在用（8 卡）；有结果时保留热门搜索、稀少时补热门工具；`php -l` 通过；截图确认满屏可用。

## [v1.6.95] - 2026-06-22

### 界面（迭代 53：工具分类归档页加料 —— 目标 #1）
延续「内容越丰富越好」：工具分类归档（/category/<slug>/）原为「裸标题 + 工具网格」，小分类时偏空。补三处：
- **图标徽标标题**（与资讯/快讯/提示词频道页一致的视觉规格）。
- **跨分类导航胶囊条**（`.filters`）：全部工具 + 各工具分类，当前高亮——可在分类间直接跳转，不必再开「分类」下拉。
- **底部 CTA 条**：「本分类 还能这样逛 · 看排行（/ranking?cat=）/ 提交工具」，既填满小分类的空白又给出后续动作（实测 `/ranking/?cat=` 200，分类筛选有效）。
- 本轮另探 6/20–22 资讯，无可核实新条目，未入库。
- 实测线上：分类页含 filters 胶囊条 + 看排行/提交工具 CTA；`php -l` 通过；截图确认满屏。

## [v1.6.94] - 2026-06-22

### 界面（迭代 52：工具PK 页补「热门对比」组合 —— 目标 #1 #2）
延续「内容越丰富越好」：/compare 数据表之后是大片空白。底部新增 **「热门对比」** 区——8 组精选对比组合卡（ChatGPT×Claude、Midjourney×Stable Diffusion、Cursor×GitHub Copilot、Kimi×豆包、Runway×可灵、Perplexity×ChatGPT、Notion AI×WPS AI、Suno×ElevenLabs），点一下直接跳到对应对比。
- 仅渲染两端工具都存在的组合；卡片含双 Logo + 名称 + VS，新增 `.pk-pair` 样式（明暗双色 + hover 上浮）。
- 既填满页面又增加真实导航/发现价值（探索更多对比）。
- 实测线上：8 张组合卡渲染，资源刷新 `?ver=1.6.94`；`php -l` 通过；截图确认底部不再空。

## [v1.6.93] - 2026-06-22

### 界面（迭代 51：提示词详情页从单栏稀疏 → 两栏丰富 —— 目标 #1 #2）
延续「内容越丰富越好」：提示词详情原为单栏（提示词块 + 1 条同场景 + 大片空白）。重构为**两栏丰富页**（对齐资讯/工具详情）：
- **主列新增「复制后，用这些 AI 试试」模块**：列出聊天助手（ChatGPT/Claude/Kimi/文心一言等，logo+简介+直达官网），让提示词「可立即使用」——真功能而非装饰。
- **相关提示词补足到 4 条**（同场景优先，不足用其他热门补齐），不再只有 1 条。
- **新增侧栏**：按场景浏览（场景胶囊，当前场景高亮）+ 热门提示词榜（按热度，带名次）+ 「投稿提示词」CTA。
- 面包屑升级为完整「首页 / AI 提示词库 / 标题」。
- 复用 `.detail-grid/.panel/.rank-item/.tagcloud-grid`（明暗双色已验证），无需改 CSS。
- 实测线上：页面含 detail-grid + 四个新模块；`php -l` 通过；截图确认满屏丰富。

## [v1.6.92] - 2026-06-19

### 内容 + 界面（迭代 50：专题扩充，/topics 从 3 张填到 9 张 —— 目标 #1 #2 #3）
按「每页布局/细节精修、功能与内容越丰富越好」的要求，/topics 此前仅 3 张卡片、大片空白，显得稀疏。扩充为 **9 个场景化专题**（3×3 满屏）：
- 新增 6 个专题（均关联现有工具，非空壳）：**AI 办公提效**（5）·**AI 数字人**（3）·**AI 音乐·配音**（2）·**AI 营销文案**（4）·**AI 学习·翻译**（3）·**AI 搜索·研究**（3）。
- 每个专题生成**品牌 SVG + PNG 封面**（各异配色）入对象存储 `topics/`；新增幂等脚本 `scripts/seed-topics-2.php`。
- 每个新专题页 `/topic/<slug>/` 即为一个真实的「精选工具合集」落地页（含 CollectionPage 结构化数据 + 关联工具网格），SEO/UX 双赢。
- 入库后 `wp cache flush`。实测线上：/topics 9 张卡片满屏；新专题页与封面均 200。

## [v1.6.91] - 2026-06-19

### 界面 + 无障碍（迭代 49：导航当前栏目高亮 + aria-current —— 目标 #1）
此前导航只有「首页」会高亮，访问 /tools /news 等时**当前栏目无任何高亮**，用户不知身处何处。
- 新增 `hahatool_nav_active()` / `hahatool_nav_attr()`：按 hh_page 虚拟路由 / 分类归档 / 单篇（工具/资讯/快讯/提示词）/ topic 智能判定当前栏目，输出 `class="active"` + **`aria-current="page"`**（读屏可播报当前页）。
- **排坑**：虚拟枢纽页 `is_front_page()` 误判为 true（主查询无文章），导致 /tools 等「首页」与本栏目双高亮——`home` 项加 `&& !$vp` 排除（与 canonical 同源坑）。
- 复用既有 `.nav-links a.active` 样式（明暗双色），无需改 CSS。
- 实测线上：/ 工具库 排行榜 工具PK 提示词 AI快讯 AI资讯 专题 热榜 九类页面各只高亮自身；单篇工具→工具库、单篇资讯→AI资讯、工具分类→工具库 均正确。
- 顺带核验：快讯 ticker 已有 pause-on-hover + reduced-motion，无需改。

## [v1.6.90] - 2026-06-19

### SEO（迭代 48：频道归档补 CollectionPage/ItemList —— 目标 #4）
落实上轮审计发现：`/news` `/flash` `/prompts` 频道归档缺结构化数据。在 `category.php` 为这三个保留分类输出 `CollectionPage` + `ItemList`（复用 `hahatool_itemlist_ld()`，仅首页不分页时输出，描述取频道 meta）。
- 实测线上：/news（24）、/flash（16）、/prompts（12）均含 CollectionPage + ItemList + ListItem。
- 至此结构化数据**全列表页覆盖**：工具库 · 排行榜 · 资讯 · 快讯 · 提示词 · 专题 + 首页/工具/资讯/专题详情 + 全页面包屑 + FAQ。

## [v1.6.89] - 2026-06-19

### SEO（迭代 47：补齐 self-canonical —— 目标 #4）
curl 审计各页面类型，发现**首页、虚拟枢纽页（/tools /ranking /hot /compare /topics）、专题归档此前无 canonical 标签**（WP `rel_canonical` 只覆盖单篇）。补 self-canonical：
- 首页 → `home_url('/')`；hh_page 虚拟路由 → `/<page>/`；topic 归档 → 词条链接；分页页不输出（避免与 paged 冲突）。
- **排坑**：虚拟枢纽页主查询无文章，`is_front_page()` 会**误判为 true**——必须先判 `hh_page` 再判首页，否则 /tools /ranking 等会错误 canonical 到首页（已发现并修正）。
- 单篇文章仍由 WP 输出唯一 canonical，未重复。
- 实测线上：/ /tools /ranking /hot /compare /topics /topic/* 七类页面 canonical 均指向各自 URL、唯一。

## [v1.6.88] - 2026-06-19

### 内容（迭代 46：填充「世界模型/具身大脑融资」资讯 —— 目标 #2 #3）
搜罗到一条新鲜（6/17）且角度前沿的资讯，WebFetch 核实后入库：
- **大晓机器人再获数亿美元融资：世界模型路线，开源 Kairos 3.0**（6/17，量子位核实）—— 天使+轮、累计数亿美元、15+ 机构（达晨/深创投/上海科创/沐曦/复星锐正等）；世界模型「理解—生成—预测」一体化；开源 Kairos 3.0（首个开源商用世界模型）+ 4B 端侧版 + 30 万套户型 Kairos-Homeworld 训练场。
- SVG + PNG 双格式品牌封面入对象存储；新增 `scripts/seed-real-news-8.php`；入库后 `wp cache flush`。
- ai-news 30 → 31；内容新增「世界模型 / 具身大脑」前沿维度。
- 实测线上：文章 200、og:image=PNG、列表头部即新文。

## [v1.6.87] - 2026-06-19

### 安全（迭代 45：安全响应头 HSTS 等 —— 目标 #4）
承接 http→https，给 hahatool 加标准安全响应头（`deploy/Caddyfile.proxy` 顶部 `header{}`，仅作用本站、不动同 zone 其他应用）：
- `Strict-Transport-Security: max-age=31536000`（配合跳转，回访浏览器免 301 往返直走 https）、`X-Content-Type-Options: nosniff`、`Referrer-Policy: strict-origin-when-cross-origin`、`X-Frame-Options: SAMEORIGIN`、`-Server`（去源站 Apache Server 头）。
- 排坑：改 Caddyfile 后 `caddy reload` 对 bind-mount 变更有时不生效，须 `docker compose restart frontend`（reload 后头未出、restart 后才出）。
- 同时核验：WP 时区 `Asia/Shanghai`（日期/`datePublished` 正确）；track 埋点 POST 不被 https 跳转误伤（REST/POST 已豁免）。
- 实测线上：经 Cloudflare 四个安全头齐全，全路由 200。

## [v1.6.86] - 2026-06-19

### SEO/安全（迭代 44：http→https 跳转，hahatool 作用域内解决 —— 目标 #4）
上轮发现 `http://tool.hahaha.chat` 返回 200 未跳转。因 zone 级 `always_use_https` 会影响同 zone 其他应用（待确认），改在 **WP 层** scoped 解决：
- 新增 mu-plugin `force-https-redirect.php`：凭 Cloudflare `CF-Visitor` 头判定原始请求 scheme，http 则 301 跳 https。
- 排坑：WP `wp_magic_quotes()` 会给 `$_SERVER` 加反斜杠，须 `wp_unslash` 才能匹配 `"scheme":"http"`；`X-Forwarded-Proto` 被 Caddy 写死 `https` 不可用，故只信 CF-Visitor。
- **无环、无副作用**：仅作用于 hahatool；检测不到 CF-Visitor 则无害空操作；非 GET/HEAD、WP-CLI/CRON/REST/admin 均跳过。
- 实测线上：`http://tool.hahaha.chat/` 与 `/news/` 均 **301 → https（保留路径）**，https 仍 200，跟随跳转终态 200（无环）。

## [v1.6.85] - 2026-06-19

### 性能/部署（迭代 43：Cloudflare 边缘提速 + 设置审计 —— 目标 #3 #4）
用所给 Cloudflare 凭证审计 hahaha.chat zone 边缘设置（API 从服务器执行，本机解析不了 api.cloudflare.com）：
- **开启 `0rtt` + `early_hints`**（此前 off）—— 纯增益、零行为风险（更快 TLS 恢复 + 103 资源预提示）。`brotli`/`http3` 本就已开。
- **审计发现（建议但未自动改，因影响全 zone 其他应用）**：`always_use_https=off`（`http://tool.hahaha.chat` 返回 200 未跳转 https，canonical 已 https 故影响有限）；`min_tls_version=1.0`（建议升 1.2）。已在 runbook 记录命令与「仅限 hahatool 的 Redirect Rule」备选方案，留待确认。
- 文档：部署 runbook 新增「Cloudflare 边缘设置」节。

## [v1.6.84] - 2026-06-19

### 内容（迭代 42：填充「AI+消费」政策资讯 —— 目标 #2 #3）
搜罗到一条**昨日（6/18）新鲜**且角度不同（国家政策）的资讯，WebFetch 核实后入库：
- **八部门发布「人工智能+消费」实施意见：17 项举措推动 AI 走进千家万户**（6/18，证券时报核实）—— 商品消费（智能终端、人形机器人从工业到消费、「人车家」生态、脑机/AR）+ 服务消费五大场景（居家/养老/文旅/住宿餐饮/教育，含智能护理机器人、智能家居纳入建设指南等）。
- SVG + PNG 双格式品牌封面入对象存储；新增 `scripts/seed-real-news-7.php`。
- 入库后 `wp cache flush` 让 Redis 对象缓存的列表查询即时刷新。
- ai-news 29 → 30；内容新增「政策」维度（此前为模型/融资/资本市场/芯片/大会/编程 Agent）。
- 实测线上：文章 200、og:image=PNG、品牌封面正常、列表头部即新文。

## [v1.6.83] - 2026-06-19

### 部署/性能（迭代 41：Redis 缓存硬化 —— 共享 Redis 良民 + 重启存活 —— 目标 #3）
承接上轮 Redis 对象缓存，做生产硬化：
- **发现**：共享 `manyan-redis-1` 为 `maxmemory 0` + `noeviction`——键永不自动淘汰，对象缓存会无限增长、影响同机其他应用。
- **设 `WP_REDIS_MAXTTL=604800`(7天)** 并 `wp cache flush` 重建：实测旧键 TTL 由 `-1` → ≈604788（≤7天），DB7 键 623→462 重建中。hahatool 缓存键现会自动过期，做共享 Redis 良民。
- **重启存活验证**：`docker compose up -d wordpress` 重建后，站点 200、`wp redis status` 仍 Connected、drop-in Valid。
- 核验首页无 `wp-embed` 等冗余脚本（上轮头部精简已生效）。
- 更新部署 runbook（MAXTTL/noeviction/flush/`-T` 要点）。

## [v1.6.82] - 2026-06-19

### 部署/性能（迭代 40：接入 Redis 对象缓存，复用现有 Redis —— 目标 #3）
按新指令「数据库/Redis 尽量复用、新建库表、尽量 Docker」，为 WordPress 接入对象缓存：
- **复用现有 `manyan-redis-1`**（不新建 Redis 容器），专用 **DB 索引 7** + 键前缀 `hahatool:` 实现隔离（实测 DB7 约 563 键，其他 DB 不受影响）。
- **Docker 原生**：`docker-compose.override.yml` 给 `wordpress`/`wpcli` 加 `manyan_default` 网络以解析 Redis；`wp-config.php` 写入 `WP_REDIS_*` 常量；官方 `redis-cache` 插件 drop-in，客户端 **Predis（纯 PHP，无需重建镜像/装扩展）**。
- 实测线上：`wp redis status` = **Connected**（Predis 2.4.0，Redis 7.4.9），drop-in Valid；首页/资讯/工具/热榜均 200；秒级回滚预案 `wp redis disable`。
- 文档：更新 `deploy/docker-compose.override.prod.yml` 与部署 runbook（含 `-T` 必加等运维要点）。
- 注：新建 30 分钟迭代循环（cron `106bb902`，发布凭证已纳入循环上下文）。

## [v1.6.81] - 2026-06-19

### 移动端（迭代 39：Web App Manifest，完成可安装 PWA-lite —— 目标 #1 #3）
承接前两轮的 icons / theme-color / apple-touch-icon，补齐 **Web App Manifest**：
- **`/site.webmanifest`**：经 `init` 钩子直接拦截输出（无需重写规则/flush），含 name/short_name/description/`lang:zh-CN`/`start_url`/`display:standalone`/`theme_color`/192+512 图标（512 标 `maskable`）；`Cache-Control` 1 天。
- header 增加 `<link rel="manifest">`。安卓 Chrome 现可「添加到主屏」（正确名称/图标/主题色），iOS 已由 apple-touch-icon 覆盖。
- 实测线上：`/site.webmanifest` 返回 `application/manifest+json` 200 + 正确 JSON；head 含 manifest 链接。

## [v1.6.80] - 2026-06-19

### 品牌一致性 + 功能核验（迭代 38：favicon 统一为品牌 sparkle —— 目标 #1）
- **favicon 统一**：原 favicon 为「哈」字 data URI，与页头 Logo / App 图标 / OG 卡的 **sparkle** 品牌标记不一致。改为指向对象存储的品牌 sparkle 图标（`brand/icon.svg` + `icon-192.png` 位图兜底），浏览器标签页与全站品牌标记统一。
- **功能核验**：实测埋点接口 `POST /hahatool/v1/track` —— 资讯 views 由 1→2，确认浏览统计（驱动「热门资讯」榜与工具浏览量）正常工作。
- 本轮亦再探最新资讯，无可核实新条目（仅 WAIC 等已收录项），未入库。
- 实测线上：favicon 指向 `brand/icon.svg`（image/svg+xml 200）+ PNG 兜底；`php -l` 通过。

## [v1.6.79] - 2026-06-19

### 性能/清理（迭代 37：头部精简 + 性能审计 —— 目标 #3 #1）
- **性能审计（CDP 实测）**：首页 **21 请求 / 147KB**，已属精简、无瓶颈（tool.hahaha.chat 69KB/9req，logo 62KB，分析/CF 微量）。确认无需结构性优化。
- **头部精简**：移除 WP 默认 emoji 检测脚本与内联样式（本项目禁用 emoji 图标，纯冗余）、`<meta generator>` 版本号暴露、RSD / WLW 遗留链接，以及对 `s.w.org` 的额外 dns-prefetch 请求。
- 实测线上：首页头部 generator/wlwmanifest/rsd/s.w.org/wp-emoji **全部清零**；首页/资讯/工具页均 200，功能正常。

## [v1.6.78] - 2026-06-19

### 移动端打磨（迭代 36：theme-color + apple-touch-icon —— 目标 #1 #3）
补齐两处移动端完整性缺口：
- **浏览器主题色**：新增 `<meta name="theme-color">`（浅 `#ffffff` / 深 `#030712`，随 `prefers-color-scheme`），手机浏览器地址栏/状态栏随站点明暗主题着色，不再是默认白。
- **iOS 主屏图标**：生成 512/192 品牌 App 图标（紫色圆角 + sparkle，iOS 风格圆角），入对象存储 `brand/icon-*.png`，新增 `<link rel="apple-touch-icon">`；源 `scripts/brand-icon.svg` 入库。
- 本轮亦探查最新（6/18–19）资讯，无新增可核实条目，遵循不回填旧闻未入库。
- 实测线上：首页输出 theme-color 浅/深两条 + apple-touch-icon；图标公网 `image/png` 200。

## [v1.6.77] - 2026-06-19

### SEO/社媒（迭代 35：默认社交卡 og:image 兜底 —— 目标 #4 #3）
发现真实缺口：首页与所有列表页（/tools /ranking /hot /topics /搜索 等）此前**无 og:image**，分享到微信/微博/Twitter 时无预览图。
- **默认品牌社交卡**：制作 1200×600 品牌卡（渐变 + Logo + 「发现最好用的 AI 工具」+ 「AI 工具·资讯·提示词·热榜」+ 域名），渲染 PNG 入对象存储 `brand/og-default.png`；源 `scripts/brand-og-default.svg` 入库。
- **站点级兜底**：`functions.php` 在无具体封面时回退到该卡，确保**每个页面都有 og:image**（含尺寸标注）；文章/专题仍优先用各自封面。
- 实测线上：首页/工具库/热榜均输出默认卡（1200×600）；资讯/专题仍用自身封面（PNG）。

## [v1.6.76] - 2026-06-19

### SEO（迭代 34：列表页 CollectionPage + ItemList 结构化数据 —— 目标 #4）
此前仅专题有 CollectionPage，工具库/排行榜两大列表页无结构化数据。新增 `hahatool_itemlist_ld()` 助手并接入：
- **排行榜 `/ranking`**：输出 `CollectionPage` + 有序 `ItemList`（position 即名次，最多 50），随 by 参数标注榜单名。
- **工具库 `/tools`**：输出当前页工具的 `CollectionPage` + `ItemList`。
- 同时核验排行榜筛选（流量/收藏/增长/人气/新品 × 分类）均按 `by` 正确 `usort`，功能正常。
- 实测线上：`/ranking` 含 CollectionPage+ItemList+28 ListItem；`/tools` 含 +24 ListItem。
- 结构化数据现覆盖：首页 · 工具详情 · 资讯 · 专题 · **工具库 · 排行榜** · 全页面包屑 · FAQ。

## [v1.6.75] - 2026-06-19

### 界面 + SEO（迭代 33：资讯面包屑频道化 + 平板 QA —— 目标 #1 #4）
- **平板 768px QA**：首页/资讯列表/资讯详情/工具详情在 768px（2 列↔1 列过渡断点）实测——均正确收敛为单列、无溢出、缩略图按 >640 显示，平板体验过关。
- **资讯面包屑升级为完整 + 频道感知**：资讯详情原仅「返回资讯列表」回链，改为与工具页一致的完整面包屑「首页 / 频道 / 标题」；并区分**资讯（AI 资讯→/news/）与快讯（AI 快讯→/flash/）**，可见面包屑与 `BreadcrumbList` 结构化数据同步正确。
- 实测线上：资讯文章面包屑「首页 / AI 资讯 / …」，快讯文章「首页 / AI 快讯 / …」。

## [v1.6.74] - 2026-06-19

### 基建 + 界面（迭代 32：打通 GitHub 推送 + 首页头条封面比例修正）
- **打通 GitHub 推送（积压清零）**：本机解析器（`100.100.100.100`）对所有域名超时，导致 `git push` 长期失败、积压 30 个提交。诊断后用「SSH 经 IP（140.82.116.4）+ `HostKeyAlias=github.com`」非破坏性绕过（仅向 `known_hosts` 追加 GitHub 官方稳定主机密钥），认证成功并**一次性推送 v1.6.43–v1.6.73 全部提交与标签，ahead 归零**。
- **首页头条封面比例修正（#1）**：头条资讯封面被内联 `aspect-ratio:16/9` 强制，而品牌封面实为 2:1，导致右侧雷达纹被裁切。移除内联覆盖，回归 CSS 的 `2/1`，完整展示封面。
- 实测线上：首页头条 `img.news-cover` 不再带 16/9，按 2:1 渲染；`php -l` 通过。

## [v1.6.73] - 2026-06-19

### 界面（迭代 31：返回顶部按钮 —— 目标 #1）
首页很长、文章也可能较长，缺少快速回顶手段。新增「返回顶部」浮动按钮：
- 滚动超 600px 淡入（`rAF` 节流），点击平滑回顶且**尊重 `prefers-reduced-motion`**；右下角圆形按钮，44px 触控、明暗双色、`brand-*` hover。
- 复用设计令牌（`--surface/--border/--brand-600`）+ 暗色 hover 变体；移动端边距收紧。
- 实测线上：按钮入 DOM，滚动后 class 变为 `to-top show`（opacity 1），视口右下角渲染正常；资源刷新到 `?ver=1.6.73`；清理了一处 rsync 误投的散落 `theme.js`。

## [v1.6.72] - 2026-06-19

### 内链（迭代 30：工具页「相关资讯」反向链接 —— 目标 #2 #4）
承接上轮 news→tool，补齐反向 tool→news，形成内容↔工具**双向内链**：
- **工具页「相关资讯」模块**：在侧栏「替代品」下方，按工具名在 ai-news 中检索提到该工具的资讯（最多 3 条、按日期倒序），展示日期 + 标题链接，复用 `.rank-item`（明暗 hover），无匹配则不渲染。
- 与 news 页「文中相关工具」对称：读者读工具可看相关动态、读资讯可跳到工具，内链闭环。
- 实测线上：Claude 工具页「相关资讯」精确匹配 3 篇（Claude Fable 5 / Anthropic IPO / AI Agent 天级），侧栏渲染正常。
- 注：本轮 cron 多次触发，已合并为一次迭代执行。

## [v1.6.71] - 2026-06-19

### 资讯 + 内链（迭代 29：文章「文中相关工具」交叉链接 —— 目标 #2 #4）
模仿 JustNews 的「相关产品」联动，并强化内容↔工具内链（利于 SEO 与读者发现）：
- **文中相关工具模块**：资讯详情扫描正文，匹配站内已收录的工具标题（精确匹配、长度≥2、最多 4 个），在正文与上一/下一篇之间展示匹配工具卡（Logo + 名称 + 一句话简介 + 跳转），复用 `.rank-item`（含明暗 hover），无匹配则不渲染。
- 链接指向工具规范永久链接（`/<slug>/`，实测 200）。
- 顺带核验：主 RSS `/feed/` 已以资讯/快讯为主（最新内容靠前），无需改动。
- 实测线上：「ChatGPT 代码能力升级」一文精确匹配并展示 ChatGPT 工具卡（无误配），浅色渲染正常。

## [v1.6.70] - 2026-06-19

### 内容（迭代 28：填充 Anthropic IPO + 谷歌 Antigravity 资讯 2 篇 —— 目标 #2 #3）
多源核实后填充，覆盖国际厂商与 AI 编程工具：
- **Anthropic 保密提交 IPO 招股书：估值 9650 亿美元，最快今秋上市**（6/1，36 氪核实）—— SEC 保密交 S-1，前序 650 亿 H 轮，ARR 约 470 亿，亚马逊 50 亿战略增资，Claude Opus 4.8 提速降本。
- **谷歌 I/O 发布 Antigravity 2.0：从 AI IDE 转向「任务中心」Agent 工作台**（5/20，36 氪核实）—— 开放支持 Gemini/Claude/GPT-OSS，新增 /goal、/schedule 等斜杠命令，对标 Codex 与 Claude Code（首次覆盖 AI 编程 Agent 工具）。
- 跳过「GPT-6 / Gemini 3.5 Pro 即将发布」类「将发布」消息（未实际落地，遵循不臆测）。
- SVG + PNG 双格式封面入对象存储；新增 `scripts/seed-real-news-6.php`。
- ai-news 27 → 29；实测两文 200，og:image=PNG，品牌封面正常。

## [v1.6.69] - 2026-06-19

### 安全/文案（迭代 27：评论反垃圾 + 空状态文案审查 —— 目标 #1）
- **评论反垃圾**：公开评论原为 WP 默认、无任何防护，公网必遭机器人灌水。新增**蜜罐字段 + 最短填写时间（3 秒）**双重拦截——无第三方依赖、无验证码摩擦、对真人零打扰；管理员豁免。实测：蜜罐被填充的提交返回 **403** 拦截。
- **空状态/微文案审查**：复核收藏空状态（图标 + 提示 + 操作说明 + 「去逛逛工具库」CTA）、搜索无结果（「换个关键词试试，比如…」）——均已规范、含引导，无需改动。
- 缓存友好：HTML 为 DYNAMIC，蜜罐时间戳每次新鲜，时间陷阱有效。
- 实测线上：评论表单含 `hh_hp` 蜜罐 + `hh_ts` 时间戳；机器人式提交被 403 拦截；`php -l` 通过。

## [v1.6.68] - 2026-06-19

### SEO（迭代 26：文章结构化数据补位图 image + publisher/Organization logo —— 目标 #4 #3）
完善文章富结果信号（Google Article 要求 image 为可抓取位图，并推荐 publisher.logo）：
- **NewsArticle.image 改位图**：原指向 SVG 封面，改用同名 PNG（新增 `hahatool_raster()` 助手，与 og:image 复用同一逻辑）。
- **新增品牌 Logo 资源**：生成 600×140 品牌 Logo（紫色 mark + HahaTool 字标 + 中文副标）渲染为 PNG 入对象存储 `brand/logo.png`；源文件 `scripts/brand-logo.svg` 入库可复现。
- **publisher.logo + Organization.logo**：NewsArticle 的 publisher 补 `logo`（ImageObject 600×140）、author 补 url；首页 Organization 补 `logo`，强化实体信号。
- 顺手：CLS 核验——所有封面 `<img>` 均有固定尺寸或 `aspect-ratio` 占位，无布局抖动；清理了一处 rsync 误投到主题根目录的散落文件。
- 实测线上：NewsArticle image=PNG、publisher.logo 正确；首页 Organization.logo 正确；logo 公网 `image/png` 200。

## [v1.6.67] - 2026-06-19

### 内容（迭代 25：填充行业大会 + 模型发布资讯 2 篇 —— 目标 #2 #3）
继续多源核实后填充，方向扩展到「行业大会」与新厂商：
- **2026 世界人工智能大会定档：7 月 17–20 日上海，主题「智能伙伴，共创未来」**（6/17，爱范儿核实）—— Hi WAIC App、五大生态矩阵、140+ 论坛/1400+ 嘉宾/3000+ 展品、SAIL 奖 TOP30。
- **MiniMax 发布新一代通用模型 M3：自研稀疏注意力架构**（6/1，证券时报核实）—— MSA 架构，100 万上下文单 token 算力约为上代 1/20（首次覆盖 MiniMax）。
- 两篇生成 **SVG + PNG 双格式封面**入对象存储（PNG 供 og:image），新增 `scripts/seed-real-news-5.php`。
- **坚持honesty**：本轮另一候选「豆包 6 月付费」为 36 氪独家爆料、官方未确认具体时间与定价，遵循不编造原则未入库。
- ai-news 25 → 27；实测两文 200，og:image 为 PNG，详情页品牌封面正常。

## [v1.6.66] - 2026-06-19

### SEO/社媒（迭代 24：og:image 改用 PNG 位图 —— 目标 #4 #3）
发现真实问题：资讯/专题的 `og:image` 指向 **SVG 封面**，而 FB / Twitter / 微信 / 微博等社媒**不渲染 SVG og:image**——分享文章时预览图会空白。
- **og:image 改 PNG**：把全部品牌封面 SVG 渲染为 1200×600 PNG（无头 Chrome）上传对象存储（25 资讯 + 3 专题）；`og:image`/`twitter:image` 自动把站内 `.svg` 换成同名 `.png`，并补 `og:image:width/height`=1200×600。
- **页面显示不变**：on-page `<img>` 仍用 SVG（清晰、体积小），仅社媒预览改 PNG——实测同一篇文章页同时含 `.svg`（显示）与 `.png`（og）。
- 新增 `scripts/render-covers-png.sh`（幂等：生成 SVG→渲染 PNG→上传），保证可复现。
- 实测线上：文章/专题 og:image 为 `.png`（image/png 200）+ 尺寸标注；页面 `<img>` 仍 SVG。

## [v1.6.65] - 2026-06-19

### 无障碍（迭代 23：A11y 审查与修复 —— 目标 #1 #4）
首次专项无障碍审查（ui-ux 准则中 a11y 为最高优先级），修复三处真实问题：
- **文档语言纠错**：`<html lang="en-US">` → `zh-CN`（站点为中文，原值误导读屏发音与搜索引擎语言定向）。经 `language_attributes` 过滤器全站纠正。
- **标题层级跳跃**：首页 hero（h1）后推广位卡片直接用 h3，造成 h1→h3 跳级。推广卡名称改为非标题元素（保持视觉权重），层级恢复 h1→h2→h3。
- **表单 label 关联**：提交页 7 个表单项的 `<label>` 原与控件无关联（无 for/id）。补齐 `for`/`id`，读屏可正确朗读字段名。
- 顺带审查提示词详情 / 提交页（390px 与桌面），结构、必填标记、可见标签均规范，无其他问题。
- 实测线上：首页与文章 `lang="zh-CN"`，首页层级 h1→h2→h3，提交页 7 个 `label for=` 关联。

## [v1.6.64] - 2026-06-19

### 界面（迭代 22：404 页升级为可恢复页 + 性能核验 —— 目标 #1 #3）
- **404 恢复页**：原 404 仅两个按钮，是个死胡同。升级为「搜索框 + 快捷入口（返回首页/工具库/AI 资讯）+ 热门工具胶囊」，把误入用户引导回站内。`.tagcloud-grid` 复用、设计令牌着色，浅/深双色与 46px 触控输入均已验证。
- **性能核验（#3）**：检查 `<head>` 资源——自托管字体 `font-display: swap`（无 FOIT）、正文用系统字体、analytics `defer`、`theme.js` 置于页脚、CSS/JS/SVG `immutable` 缓存命中，无渲染阻塞问题，性能已达良好，本轮无需改动。
- 实测线上：404 浅/深双色截图复核，搜索/入口/热门工具齐全。

## [v1.6.63] - 2026-06-19

### SEO 修复 + 移动 QA（迭代 21：站点地图枢纽页修复 —— 目标 #4 #1）
**移动审查**：compare / ranking / 工具详情 在 390px 实测——卡片堆叠、雷达/表格自适应、FAQ 与评论表单均正常，无横向滚动，移动端体验过关（无需改动）。
**站点地图真实 bug 修复**（#4）：
- `wp-sitemap-hahatool-hubs-1.xml` 此前返回首页 HTML 而非 XML——根因是 WP 的 sitemap rewrite 仅匹配 `[a-z]+`，**provider 名带连字符**「hahatool-hubs」被错解析成 `sitemap=hahatool` 而回退首页。枢纽页因此长期**不在任何有效 sitemap 中**。改用纯字母名 `hubs` 修复。
- 枢纽 provider 补齐**清爽频道 URL**（/news /flash /prompts，与 canonical 一致）+ 枢纽页（/tools /ranking /compare /topics /hot），并加 **lastmod**（取最近内容修改时间）。
- 从分类 sitemap **排除保留分类**（ai-news/ai-flash/ai-prompts）——其 canonical 指向清爽 URL，避免收录非规范 `/category/ai-*`。
- 实测线上：索引列出 `wp-sitemap-hubs-1.xml`，含 8 个 loc + lastmod；分类 sitemap 已不含 ai-* 保留分类。

## [v1.6.62] - 2026-06-19

### 内容（迭代 20：填充国产 AI 算力芯片资讯 2 篇 —— 目标 #2 #3）
此前资讯偏模型发布/融资，本轮锁定 AI 硬件/半导体方向，逐条 WebFetch 核实后入库：
- **算苗 3D TokenPU 芯片正式流片：3D 堆叠架构，16TB/s 访存带宽**（6/17，量子位核实）—— A4E 于 6/15 流片，8 层存储晶圆垂直堆叠，面向大模型推理、国产供应链自主可控。
- **燧原科技 IPO 过会：自研云端 AI 芯片，拟募资 60 亿元**（6/16，证券时报核实）—— 6/15 上交所上市委过会，全栈算力产品体系，营收 3.01→9.90 亿。
- 两篇均生成品牌 SVG 封面入对象存储（青/翠双色，区别于既有封面），新增 `scripts/seed-real-news-4.php`。
- ai-news 23 → 25，内容覆盖从「模型/融资」扩展到「AI 算力芯片」；首页 hero「AI 资讯」统计随之升至 25+。
- 实测线上：两文 200，资讯列表头部即新文且品牌封面各异。

## [v1.6.61] - 2026-06-19

### 首页结构（迭代 19：分类橱窗收敛，首页瘦身 —— 目标 #1 #2）
上轮发现首页过长（>5600px），主因是 11 个分类各占一整屏橱窗。优化信息密度：
- **热门 5 类做完整橱窗**（按工具数排序：聊天机器人/代码开发/图像设计/效率办公/视频生成各 4 款），其余长尾分类压缩为 **「更多分类」胶囊网格**（名称 + 数量，复用 `.tagcloud-grid`），一键直达，无任何分类丢失。
- 首页由 11 个分类整屏缩减为 5 屏 + 1 行胶囊，长度显著下降，首屏到资讯/热榜的路径更短。
- 实测线上：完整橱窗 5 个（cat-chatbot/code-it/image/productivity/video）+「更多分类」胶囊；浅/深双色渲染正常（CDP 滚动定位截图复核）。

## [v1.6.60] - 2026-06-19

### 首页 + 热榜（迭代 18：首页接入「AI·科技热榜」模块 —— 目标 #2 #5）
热榜此前仅在导航/独立页 `/hot`，首页未surface。新增首页热榜 teaser：
- **首页热榜模块**：资讯区下方新增「AI · 科技热榜」，取前 3 个热源（知乎/豆瓣/微博等）各 6 条，复用 `.hot-*` 组件（浅/深双色、移动端单列均已验证），带「更新于 HH:MM」与「完整热榜 →」入口。
- **缓存无冲突**：首页用默认 `$per` 调 `hahatool_fetch_hot()`，与 `/hot` 共享 15 分钟服务端缓存（仅在模板内 `array_slice` 到 6 条），实测 `/hot` 仍为 13 源 / 174 条完整数据。
- **优雅降级**：momoyu 拉取失败时模块整体不渲染，不影响首页。
- 顺带核验缓存策略（#3）：CSS/JS/SVG 封面均 `cf-cache-status: HIT` + `immutable` 1 年，HTML 正确为 `DYNAMIC`。
- 实测线上：首页渲染 3 张热榜卡（更新于 08:43），`php -l` 通过。

## [v1.6.59] - 2026-06-19

### SEO + 内容（迭代 17：工具页 FAQPage 结构化数据 + 补全热门工具 FAQ —— 目标 #4 #2）
工具页此前已渲染 FAQ 手风琴，但缺 `FAQPage` 结构化数据，且仅 3/28 个工具有 FAQ。
- **FAQPage 结构化数据**：`single-tool.php` 在有 faq 数据时输出 `FAQPage`（Question/Answer），符合 Google FAQ 富结果规范。
- **补全 8 个热门工具 FAQ**（Claude / Perplexity / GitHub Copilot / Kimi / Suno / Runway / Notion AI / ElevenLabs），均为通用可核实的事实性问答（定价/访问/适用场景），新增幂等脚本 `scripts/seed-tool-faqs.php`（仅填空、不覆盖）。有 FAQ 的工具 3 → 11 个。
- 实测线上：`/tool/claude/` 结构化数据含 SoftwareApplication + AggregateRating + Offer + BreadcrumbList + **FAQPage（3 问答）**；FAQ 手风琴浅/深双色渲染正常。
- 主题资源未变，`HAHATOOL_VERSION` 维持 1.6.58。

## [v1.6.58] - 2026-06-19

### 界面 + 工具（迭代 16：暗色模式跟随系统 + 暗色 QA 工具 —— 目标 #1）
首次系统性做「浅/深双色」QA（此前仅截浅色，暗色一直是盲区）：
- **新增 `scripts/shot.js`**：基于 CDP（Node 22 全局 WebSocket，无需 puppeteer）的截图工具，支持 `dark`（写 localStorage 强制暗色）与 `sysdark`（仅模拟暗色 OS，验证「跟随系统」）两档，浅/深双色 QA 从此可常态化。
- **暗色全量巡检**：首页/资讯详情/工具详情/热榜/资讯列表暗色实测，近期新增组件（分享行、品牌预览卡、阅读进度条、页头字标）在暗色下均正确适配，无瑕疵。
- **默认主题跟随系统**：旧版首访硬编码浅色，暗色 OS 用户也被强制浅色。改为默认 `system`（`prefers-color-scheme`）——暗色 OS 首访即暗色、浅色 OS 即浅色，手动切换仍持久化覆盖。`header.php` 内联防闪脚本与 `theme.js` 系统监听同步改默认。
- 实测线上：内联脚本默认为 `system`；暗色 OS 无 localStorage → 自动暗色；浅色 OS → 浅色；显式暗色 → 暗色。

## [v1.6.57] - 2026-06-19

### 内容（迭代 15：核实后填充真实 AI 资讯 2 篇 —— 目标 #2 #3）
按上轮计划，锁定权威源、逐条 WebFetch 核实后入库（不编造）：
- **DeepSeek 完成首轮外部融资：募资超 500 亿元，估值逼近 4000 亿**（2026-06-16）—— 经量子位、证券时报、36 氪、21 经济报道多源交叉核实（梁文锋约 200 亿、估值 3500–4000 亿、V4.1 定档 6 月），采用「据报道/未官方置评」对冲措辞。
- **科创板第五套标准拟扩容至 AI 大模型，多家头部企业排队 IPO**（2026-06-17）—— WebFetch 核实 21 世纪经济报道原文（吴清在陆家嘴论坛表态；DeepSeek/智谱/MiniMax 推进 IPO）。
- 两篇均生成品牌 SVG 封面入对象存储（`news/covers/`），新增 `scripts/seed-real-news-3.php`（幂等）。
- ai-news 总数 21 → 23；首页 hero「AI 资讯」统计随之升至 23+。
- 实测线上：两文 200，资讯列表头条/首条即新文且显示品牌封面，详情页封面/正文/来源齐全。

## [v1.6.56] - 2026-06-19

### 界面打磨（迭代 14：页头品牌去重 + 首页统计去零 —— 目标 #1）
全站桌面端多设备截图巡检（compare/ranking/search/404/home，真实 UA），修复两处真实瑕疵：
- **页头品牌重复**：站名已是「HahaTool 哈哈工具」，页头却又写死 `<small>哈哈工具</small>`，桌面端渲染成「HahaTool 哈哈工具**哈哈工具**」。改为按首个空格把站名拆成英文字标 + 中文副标，保留两级排版且不再重复（页脚本就正常）。
- **首页 hero 出现「0」**：第三项统计为「本周新增」，因工具均为早前录入，近 7 天为 0，hero 显示刺眼的「0」。增加回退：本周新增为 0 时改显「AI 资讯」总数（21+），hero 永不出现 0。
- 另：本轮尝试用 WebSearch 搜罗真实 AI 资讯（#2），但结果多为推测/聚合稿，难以高标准核实，遵循「不编造」原则本轮未入库（compare/ranking/search/404 经审查均为高完成度，无需改动）。
- 实测线上：页头显示「HahaTool · 哈哈工具」无重复；hero 显示「28+ / 9+ / 21+ AI 资讯」。

## [v1.6.55] - 2026-06-19

### 界面 + SEO（迭代 13：工具官网预览改品牌卡 + OG 补全 —— 目标 #1 #2 #3 #4）
多设备截图审查工具详情/对比/提示词页。两点发现与修复：
- **官网预览不可靠** → 旧版用 `s0.wp.com/mshots` 自动截图，对 Cloudflare 等防护站点会截到「人机验证」页（ChatGPT 等旗舰工具尤甚），既难看又是第三方请求。改为**品牌预览卡**（品牌渐变 + Logo + 名称 + 一句话简介 + 域名胶囊，整卡可点击直达官网），有真实上传截图时仍优先用真图。同时移除 OG 图的 mshots 兜底（改用工具 Logo）。**全站 mshots 依赖清零**。
- **OG 标签补全（#4）** → 新增 `og:site_name`、`og:locale=zh_CN`（此前缺失），利于社媒卡片与多语识别。
- 测试经验：无头 Chrome 默认 UA 含「HeadlessChrome」会被 Cloudflare 挑战导致样式丢失；截图统一加真实 `--user-agent`。
- 实测线上：工具页桌面/移动均显示品牌预览卡；`/`、`/tool/chatgpt/`、`/tool/claude/` 的 mshots 引用数 = 0；首页 og:site_name/og:locale 正确。

## [v1.6.54] - 2026-06-19

### 界面 + 对象存储（迭代 12：专题封面品牌化 + 详情头去重 —— 目标 #1 #2 #3）
无头 Chrome 多设备截图巡检（hot/topics/tools/flash/专题详情，移动+桌面），发现两处真实问题并修复：
- **专题封面为无关图库照片**（「AI 编程提效」竟配一张狼的照片，且 3 张均外链 picsum）→ 生成品牌 SVG 封面（chip「专题」+ 关键词 + 副标题），上传对象存储 `hahatool-media/topics/`，`topic_cover` 全部重指向；列表卡与社媒 OG 同步更新。
- **专题详情头文字重复** → 封面已是「带文字」编辑图，旧详情头把它当背景又叠加了一遍标题/副标题/chip，视觉重叠。改为品牌渐变 + 真实文案（利于 SEO/可读性），封面仅用于列表缩略图与 OG。
- **种子脚本去 picsum**（耐久化 #3）：`seed-real-news.php` / `seed-more-news.php` / `seed-topics.php` / `seed-data.json` 的 cover 字段全部改为对象存储 URL（按 slug），全新部署不再回归 picsum。
- 实测线上：3 个专题封面 200、专题列表/详情/移动端均显示品牌封面且无重复文字；首页/资讯/专题三页 picsum 引用数 = 0。
- 仅余 1 篇「AI 视频实测」正文内的示意图/示例视频为第三方（示例内容，后续处理）。

## [v1.6.53] - 2026-06-19

### 内容 + 对象存储（迭代 11：资讯封面统一为品牌编辑图 —— 目标 #1 #2 #3）
用本地无头 Chrome 在移动/桌面尺寸截图复核（首次落实「多设备截图检查」），发现资讯封面为无关图库照片（如 NVIDIA 报道配海岸悬崖），且 10 篇仍外链 `picsum.photos`。
- **品牌编辑封面**：新增 `scripts/gen-news-covers.py`，按文章生成 1200×600 SVG 封面（品牌渐变 + 关键词 + 副标题 + 科技网点/弧线 + HahaTool 字标 + AI 资讯胶囊），21 篇各异、与内容相关。
- **全部入对象存储**：21 个 SVG 上传至 MinIO `hahatool-media/news/covers/`，`image/svg+xml`、`/media` 公网直读、Caddy 命中 1 年 immutable 缓存。
- **消除第三方依赖**：21 篇 `cover` meta 全部指向对象存储（含原 10 个 `picsum.photos` 外链），前台再无第三方图片请求。
- 实测线上：21 个封面全部 200，资讯列表/头条/详情均显示品牌封面（截图复核浅色桌面+移动）。
- 主题代码未改动，`HAHATOOL_VERSION` 保持 1.6.52（不无谓刷新 CSS/JS 缓存）。

## [v1.6.52] - 2026-06-19

### 体验 + SEO（迭代 10：资讯阅读体验升级 —— 目标 #1 #2 #4）
模仿 JustNews 强化资讯文章页的阅读体验，并补齐文章级 OpenGraph。
- **阅读进度条**：文章顶部细进度条，滚动比例 → `scaleX`（rAF 节流、`transform` 动画、尊重 `prefers-reduced-motion`），品牌渐变。
- **文章分享行**：微博分享、复制链接（剪贴板 + execCommand 兜底、「已复制」反馈）、系统分享（`navigator.share`，移动端自动显隐）。触控目标 40px、明暗双色、`brand-*` hover。
- **文章级 OG 元信息**：`article:published_time` / `modified_time` / `section` / `publisher` / `tag`（所有 `is_singular('post')` 文章）。
- 新增 `link` / `share` 两枚 lucide 图标到 `hh_icon()`。
- 实测线上：文章页输出进度条/分享行/article:* 元信息，资源刷新到 `?ver=1.6.52`，share-btn 40px 已生效，文章 200。

## [v1.6.50] - 2026-06-19

### SEO（迭代 9：首页站点级结构化数据 —— 目标 #4）
首页此前无任何结构化数据。新增 `WebSite` + `Organization` JSON-LD：
- `WebSite` 含 `SearchAction`（`target = /?s={search_term_string}`）—— 启用 Google 站内链接搜索框（Sitelinks Searchbox）。
- `Organization` 确立站点实体（name/url/description）。
- 仅首页输出（`is_front_page`），`inLanguage: zh-CN`。
- 实测线上：首页输出 WebSite/Organization/SearchAction/EntryPoint，SearchAction target 正确，首页 200。

## [v1.6.49] - 2026-06-19

### 内容（迭代 8：增量真实资讯第二批 + 多设备 QA）
- **多设备 QA（#1）**：截图核验线上 `/news`（桌面 1280 + 移动 390）—— 头条特写 + 列表（MinIO 封面 + 阅读时长）+ 三侧栏组件渲染良好，无破版。
- **真实资讯第二批（#2）**：WebSearch 搜罗硬件/机器人/具身智能动态，新增 4 篇（`scripts/seed-real-news-2.php`）：NVIDIA Cosmos 3 全模态物理 AI、英伟达×宇树人形机器人、智平方 B 轮超 10 亿融资、OpenAI 进军机器人。封面**直接走对象存储**（先 `mc cp` 到 MinIO，cover 指向 `/media/...`）。
- 线上实测：4 新标题上线、封面经 MinIO 全 200、ai-news 总数 21 篇。

## [v1.6.48] - 2026-06-19

### 部署（迭代 7：图片接入对象存储 MinIO —— 目标 #3 下半）
- **对象存储**：复用服务器 MinIO 建公共读桶 `hahatool-media`；`deploy/Caddyfile.proxy` 加 `handle_path /media/* → manyan-minio-1:9000`，对象经 `https://tool.hahaha.chat/media/...` 公网直读，**仅 2xx 加 1 年 immutable 缓存**（修正 404 被误缓存的问题，用 `handle_response @ok`）。
- **资讯封面迁移**：7 篇真实资讯封面从第三方 picsum 热链迁移到对象存储（`mc cp` 至 `news/<slug>.jpg`，cover 改为 `/media/...?v=1`）—— 自托管、可缓存、去除外链依赖。
- 实测线上：`/media/hahatool-media/...` 200 + `image/jpeg` + immutable 缓存；7 张封面全 200；`/news` 7 张封面指向对象存储；站点全路由 200。
- runbook 增对象存储与上传/迁移说明。

## [v1.6.47] - 2026-06-19

### 部署/性能（迭代 6：静态资源缓存策略 —— 目标 #3 上半）
线上代理此前用 `caddy reverse-proxy` 快捷命令，无缓存头（主题 CSS 仅 `max-age=14400`、未压缩、CF MISS）。改为正式 Caddyfile：
- 新增 `deploy/Caddyfile.proxy`：静态资源（css/js/字体/图片）`Cache-Control: public, max-age=31536000, immutable` + `encode zstd gzip`；HTML/wp-json 不强缓存。
- 服务器 override 改为挂载该 Caddyfile（`deploy/docker-compose.override.prod.yml` 入库存档），重建 frontend 代理容器。
- 实测线上：theme CSS 现 `max-age=31536000, immutable` + `content-encoding: gzip` + `vary`；HTML 未被长缓存；首页/热榜/资讯/工具/专题全 200。WP 资源带 `?ver=` 版本号，immutable 安全。
- 下半（#3 对象存储）：MinIO 桶 + 公网路由 + WP 媒体卸载 + 真实封面图，下轮推进。

## [v1.6.46] - 2026-06-19

### 内容（迭代 5：填充真实 AI/科技资讯 —— 目标 #2）
用 WebSearch 搜罗 2026 年 6 月真实 AI 动态，聚合为 7 篇资讯（真实标题 + 事实摘要 + **来源链接**，幂等脚本 `scripts/seed-real-news.php`）：
- Claude Fable 5 发布 · GPT-5.5 推出 · ChatGPT 代码能力升级 · Gemini 3 Pro 多模态 · DeepSeek V4 开源(SWE-bench 80.6%) · AI 智能体「天级执行」· Anthropic 年化收入破 470 亿。
- 每篇含 `.news-source` 署名行（虚线分隔、链到原站，`rel=nofollow`）；封面暂用占位图（真实图片随 #3 对象存储接入替换）。
- 已上线 tool.hahaha.chat：`/news` 列出真实标题、详情页带来源链接、ai-news 总数 17、`/openai-gpt-5-5/` 200。

## [v1.6.45] - 2026-06-19

### 优化（迭代 4：多设备 QA + 虚拟枢纽页进 sitemap）
- **多设备 QA（#1）**：对线上 `tool.hahaha.chat` 的 `/hot`（桌面 1280 + 移动 390）与首页（移动 390）截图核验 —— 热榜 3/1 列正确堆叠、Top3 橙色、热度对齐、首页 hero/Banner/卡片均良好，无横向溢出与破版。
- **SEO（#4）**：`wp-sitemap` 默认不含虚拟路由，新增自定义 provider `hahatool-hubs`，把 `/tools /ranking /compare /topics /hot` 纳入 `wp-sitemap-hahatool-hubs-1.xml`。
- 顶栏已有「热榜」，补页脚「探索」列的「AI · 科技热榜」入口（一致性）。
- 实测线上：sitemap 索引含 hubs 子图且列出 5 枢纽 URL、页脚热榜链接、`/hot` 200。

## [v1.6.44] - 2026-06-19

### 新增（迭代 3：热榜页 + 热榜接口，数据源 momoyu —— 目标 #5）
- **热榜接口**：mu-plugin 新增 `hahatool_fetch_hot()` + REST `GET /wp-json/hahatool/v1/hot`，服务端代理 momoyu 公开聚合端点 `/api/hot/list`（需带 Referer/Origin 头），**transient 缓存 15 分钟 + 一天兜底**（上游故障返回旧数据），规范化为 `{updated, sources:[{name,key,color,items:[{title,extra,link}]}]}`。
- **热榜页 `/hot`**：13 个来源（知乎/豆瓣/微博/头条/虎扑/B站/IT之家/中关村/爱范儿/CSDN/虎嗅/什么值得买/掘金）卡片网格，每源 Top12（排名+标题+热度+外链），响应式 3/2/1 列；标题 chip + SEO 标题/描述；顶栏「热榜」导航。
- 实测**线上 tool.hahaha.chat**：REST 返回 13 源×12 条真实数据、`/hot` 页 200 渲染 13 卡、导航生效（本地 OrbStack 容器外网受限故空，生产服务器正常）。

## [v1.6.43] - 2026-06-19

### 部署（迭代 2：tool.hahaha.chat 切换为 WordPress 版并上线）
发现 `tool.hahaha.chat` 早已经 Cloudflare Tunnel 上线，但指向**旧的 Next.js 无头前端**（主题 1.6.23、默认 twentytwentyfive、未启用 hahatool 主题）。按 #6「仅维护 WordPress」完成切换并上线：
- **服务器 WP 初始化**：`rsync` 同步最新主题/插件（1.6.23 → 1.6.40）；激活 `hahatool` 主题；永久链接 `/%postname%/`；种子补齐（3 专题 + 6 资讯 + 8 快讯 + 资讯浏览量）。
- **切换链路（仅服务器侧，无需改 CF）**：把隧道目标容器 `hahatool-frontend` 从 Next.js 换成 `caddy:2-alpine` 反代到 `hahatool-wordpress:80`（保持容器名/端口/网络，隧道 ingress 不变）。
- **生产 URL**：WP `home/siteurl=https://tool.hahaha.chat`；新增 mu-plugin `https-behind-proxy.php`（隧道后强制 HTTPS 识别、避免重定向回环；本地 http 开发不受影响）。
- **实测线上**：`https://tool.hahaha.chat` 现为 WordPress 版（wp 标记 53、零 Next.js 残留）；首页/工具库/专题/资讯/快讯/排行/工具详情/专题详情全部 200；canonical=`https://tool.hahaha.chat/...`、sitemap 200、零 localhost 泄漏、零重定向回环。
- `deploy/DEPLOY-tool.hahaha.chat.md` 增「实际生产现状」拓扑与回滚说明。

## [v1.6.42] - 2026-06-18

### 部署（新方向迭代 1：生产部署脚手架 → tool.hahaha.chat）
新目标：仅维护 WordPress 版，部署到 `tool.hahaha.chat`（服务器 23.82.99.201，复用其 Caddy 自动 HTTPS + MinIO 对象存储）。
- 调研参考 `hahaha.chat`（Cloudflare Worker）与服务器 `manyan` 栈（Docker + Caddy + MinIO）的部署方案。
- 新增 `deploy/`：`docker-compose.prod.yml`（**去掉 frontend，仅 db + wordpress + wpcli**，wordpress 接入外部 `edge` 网供 Caddy 反代，注入 `WP_HOME/SITEURL=https://tool.hahaha.chat` + 反代 HTTPS 识别）、`.env.prod.example`、`Caddyfile.hahatool.snippet`（含静态资源长缓存）、`DEPLOY-tool.hahaha.chat.md`（DNS/起栈/Caddy/WP 初始化/对象存储/缓存/回滚全流程）。
- `compose config` 校验通过；`.env.prod` 已 gitignore。
- **待人工**：服务器 SSH 口令未入库，实际 `ssh + 部署` 需口令（见 runbook §0）。

> 说明：自本版起按用户要求**仅维护 WordPress 主题版**，无头 Next.js 版停止同步。

## [v1.6.41] - 2026-06-18

### 文档（迭代 22：全量功能回归认证）
21 轮迭代后做完整回归并记录认证（`docs/KNOWN_ISSUES.md`）：
- 两版各 14–15 条核心路由全部 200；站内浏览上报端点实测自增（资讯 views +1）、无头 `/api/track` 代理 200、搜索命中正常。
- SEO 结构化数据全覆盖 + 资讯/专题 OG 卡片 + 两版 robots 含 Sitemap；无障碍（alt/aria/焦点环/reduced-motion/skip-link）齐备；桌面/移动/暗色/多主题色 QA 通过。
- 结论：整站健康、两版严格同步，处于可上线的「非常好」状态；无新发现的真实缺陷。

## [v1.6.40] - 2026-06-18

### 新增（迭代 21：无障碍审计 + 跳到主要内容链接）
系统性 a11y 审计：图片 `alt`、图标按钮 `aria-label`、`:focus-visible` 焦点环、`prefers-reduced-motion`（快讯跑马灯）经核查**均已具备**。唯一标准缺口——**跳到主要内容（skip link）**缺失，两版补齐：
- 主题版 `header.php` body 首元素加 `.skip-link`（默认屏外、聚焦时显示品牌色按钮）+ 头部后置 `#main-content` 目标。
- 无头版 `layout.tsx` 加 `sr-only focus:not-sr-only` 跳转链接 + `<main id="main">`。
- 键盘/读屏用户首次 Tab 即可一键跳过导航直达正文。
- 实测：WP skip-link + `#main-content`、无头 skip link + `<main id="main">` 均渲染，构建通过。

## [v1.6.39] - 2026-06-18

### 新增（迭代 20：专题详情结构化数据 CollectionPage —— 结构化数据收口）
专题页此前有 meta + OG 但无结构化数据。两版补 **CollectionPage + ItemList**（专题=工具合集，列出成员工具）+ **BreadcrumbList**（首页/专题/名称）。
- 主题版 `taxonomy-topic.php` 用 `$wp_query->posts` 构造 ItemList；无头版 `topic/[slug]` 用 `tools` 数组构造。
- 至此结构化数据全覆盖：工具 `SoftwareApplication`、资讯 `NewsArticle`、专题 `CollectionPage+ItemList`，均配 `BreadcrumbList`。
- 实测：WP 与无头版专题详情均输出 CollectionPage(含 5 工具 ItemList)+BreadcrumbList、JSON 合法、200、构建通过。

## [v1.6.38] - 2026-06-18

### 修复（迭代 19：资讯/专题详情 OG 社交卡片补全）
社交分享卡片完整性核查，补齐缺失的 `og:image` 与文章级描述：
- **无头版资讯详情**：`generateMetadata` 此前仅设 title → 描述回退站点默认、无 `og:image`。补 description（资讯摘要）+ `openGraph`（type=article + 封面图）+ Twitter 卡片。
- **无头版专题详情**：`generateMetadata` 补 `openGraph` 封面图（topic cover）+ Twitter 卡片。
- **主题版专题归档**：`wp_head` OG 钩子补 `is_tax('topic')` 分支，`og:image` 用专题封面（此前仅文章有 OG 图）。
- 实测：无头资讯详情 `og:image`=文章封面 + `og:type=article`；无头/主题专题详情 `og:image`=专题封面；200、构建通过。

## [v1.6.37] - 2026-06-18

### 修复（迭代 18：主题版专题归档页 SEO 标题/描述）
主题版 `/topic/<slug>` 归档页的 meta 描述此前**回退到站点默认**（"发现最好用的 AI 网站和工具"），未用专题自身描述；文档标题也无「专题」标识。`hahatool_meta_description()`/`document_title_parts` 未处理 `is_tax('topic')`。
- 补 `is_tax('topic')` 分支：meta 描述用专题 term 描述（回退「…精选 AI 工具专题合集」），标题为「{专题名} - 专题」。
- 与无头版 `generateMetadata`（`topic.description`）口径一致。
- 实测：WP `/topic/ai-video-create/` 标题「AI 视频创作 – 专题 – HahaTool」、描述为专题自身文案、lint 通过。

## [v1.6.36] - 2026-06-18

### 修复（迭代 17：sitemap 补全专题 URL —— SEO）
专题系统（v1.6.24）的 URL 此前未进无头版 sitemap（专题内容不可被搜索引擎发现）：
- 无头版 `sitemap.ts` 补 `/topics` 索引 + 各 `/topic/<slug>` 归档（`getTopics()` 并入）。
- 主题版经核对 `wp-sitemap.xml` 已自动包含 `wp-sitemap-taxonomies-topic-1.xml`（公开分类法自动收录），无需改动。
- 实测：无头 `/sitemap.xml` 含 `/topics` + 3 个 `/topic/<slug>`、200、构建通过。

## [v1.6.35] - 2026-06-18

### 新增（迭代 16：资讯详情结构化数据 NewsArticle —— SEO 深化）
此前工具详情有 SoftwareApplication + BreadcrumbList JSON-LD，但**资讯文章无任何结构化数据**（新闻类内容的 SEO 短板）。两版补齐：
- 资讯详情输出 **NewsArticle**（headline / datePublished / dateModified / image / description / mainEntityOfPage / author / publisher）+ **BreadcrumbList**（首页 / AI 资讯 / 标题）。
- 主题版用 `wp_json_encode`（`get_the_date('c')` ISO 时间）；无头版用 `siteUrl` + `new Date(created*1000).toISOString()`，对齐工具详情写法。
- 实测：WP 与无头版资讯详情均输出 NewsArticle + BreadcrumbList、JSON 合法、200、构建通过。

## [v1.6.34] - 2026-06-18

### 优化（迭代 15：多主题色 QA 验证 + 首页统计「0+」修正）
- **多主题色 QA**：以 emerald 主题色截图核验上轮（v1.6.33）的 PK 配色修复 —— 选手 A 雷达/圆点/边框/表头确认随主题色变为翠绿、选手 B 保持琥珀；首页 hero/Banner/导航/卡片全部正确切换为翠绿，无残留紫色。多主题色体系健康。
- **「0+」修正**：首页 hero「本周新增」当数值为 0 时显示「0+」略显怪异；改为数值 >0 才追加「+」（28+ / 9+ / 0），两版同步。
- 实测：WP hero「28+ / 9+ / 0」、lint 通过；无头版同逻辑、构建通过。

## [v1.6.33] - 2026-06-18

### 修复（迭代 14：PK 页选手 A 颜色不随主题色自适应 + 品牌色硬编码审计）
全站审计「品牌色硬编码紫罗兰 hex」（项目规范：品牌色只用 `brand-*` 以支持 4 套主题色）：
- **发现真实 Bug**：无头版 `compare/page.tsx` 的 `COLOR_A = '#7c3aed'` 硬编码紫罗兰，导致切到 海蓝/翡翠/玫红 主题色时，PK 页「选手 A」的雷达/圆点/边框/表头**仍是紫色**，与全站不一致；且与主题版（用自适应 `var(--brand-600)`）不一致。
- **修复**：改为 `rgb(var(--brand-600))`（无头版 `--brand-*` 为 RGB 通道，已用于工具详情雷达），选手 A 现随 4 套主题色自适应；选手 B 保持固定琥珀色作对比（同主题版）。
- 审计确认其余命中均为合理用途（主题色 swatch、ToolLogo 多彩渐变占位调色板）。
- 实测：PK 页选手 A 24 处使用 `rgb(var(--brand-600))`、零 `#7c3aed` 残留、200、构建通过。

## [v1.6.32] - 2026-06-18

### 修复（迭代 13：暗色 QA + 热门资讯榜排除当前文章）
以「产品设计师 + 测试工程师」视角做新页面暗色截图 QA（专题详情、资讯详情两栏）：
- **QA 结论**：专题 hero 暗色渐变 + 白字对比良好、工具卡暗色正常；资讯详情两栏 + 三侧栏组件（快讯/热门资讯/热门工具）暗色均清晰，快讯日期徽章暗色可见（v1.6.23 修复确认生效）。
- **QA 发现并修复**：资讯详情页的「热门资讯榜」会把**当前正在阅读的文章**也列进去（自引用）。两版均加排除当前文章 —— 主题版 `hahatool_hot_news_panel($limit, $exclude)`、无头版 `getHotNews(limit, excludeCid)`（详情页超取 1 条再过滤，保证仍满 5 条）。
- 实测：WP 与无头版资讯详情的热门资讯榜均不再含当前文章、5 条满；详情 200、构建通过。

## [v1.6.31] - 2026-06-18

### 新增（迭代 12：热门资讯榜侧栏组件 + 资讯浏览量 —— JustNews 排行模块）
内容变丰富后补齐 JustNews 招牌的侧栏排行组件，资讯有了真实的「热度」信号：
- **资讯浏览量**：扩展站内浏览上报到资讯文章（WP `wp_footer` 钩子对 ai-news 也输出 `__HAHATOOL_TRACK__`，无头版资讯详情加 `<TrackView>`），`views` meta 累计；并 seed 10 篇资讯初始浏览量使排行即时有意义。
- **热门资讯榜**（按 `views` 排序，Top 5）：主题版新增 `hahatool_hot_news_panel()` 助手，无头版新增 `HotNewsWidget` 组件 + `getHotNews()` API（`NewsItem.views` 入 `toNews`）。
- 资讯列表页 `/news` 与资讯详情侧栏均加入该榜（快讯 → **热门资讯** → 本周热门工具）。
- 实测：WP `/news`+详情 热门资讯榜 5 条带浏览量眼睛图标、200 零 warning；无头 `/news`+`/news/<slug>` 榜渲染（flame 标题 + eye 浏览量）、200，构建通过。

## [v1.6.30] - 2026-06-17

### 内容（迭代 11：补充资讯/快讯种子，让杂志式板块更饱满）
QA 发现各杂志式板块已建好但内容偏稀疏（仅 4 资讯、右栏无缩略图）。补充高质量中文种子内容，让模块真正「显出来」：
- 新增 `scripts/seed-more-news.php`（幂等，按 slug 去重）：6 篇近一周 AI 资讯（含封面 + 正文，覆盖 Agent/多模态/开源/编程/视频/合规）+ 8 条近两天快讯。
- 效果：资讯 10 篇、快讯 16 条；首页资讯杂志块右栏现满 4 项且**带缩略图**；`/news`、`/flash` 内容密实，接近 JustNews 观感。
- 纯内容（无代码改动）：两版经 REST 自动读取，无头版动态页即时生效、ISR 页回填后更新。
- 实测：WP 首页 4 缩略图列表项 / `/news` 10 / `/flash` 16，均 200；无头 `/news`·`/flash` 即时变满，200。

## [v1.6.29] - 2026-06-17

### 文档（迭代 10：移动端 QA 认证 + 一致性矩阵同步）
- **移动端 QA（375px 截图 + DOM 测量）**：首页与资讯详情零横向溢出（scrollWidth=clientWidth=375）；新布局（资讯杂志两栏、详情两栏、专题网格、Banner）在移动端均正确堆叠为单列；快讯 marquee track 虽宽但被 `.ticker-mask` 正确裁切；唯一数据型小瑕「本周新增 0+」源于种子工具日期早于本周，非缺陷。
- **一致性矩阵同步**（`docs/KNOWN_ISSUES.md`，此前停在 ~v1.6.13）：补录 v1.6.20–v1.6.28 新增模块的两版一致性 —— 专题系统、首页精选专题板块、首页资讯杂志布局、资讯阅读时长 meta、资讯详情两栏+侧栏、统一分页（chevron+窗口化）、频道页标题 chip。
- 桌面浅/深双色 + 移动 375px 截图 QA 全部通过，整站达成参考 JustNews 的「非常好」状态。

## [v1.6.28] - 2026-06-17

### 优化（迭代 9：视觉 QA 通过 + 封面图优雅降级）
本轮以「产品设计师 + 测试工程师」视角做真实视觉 QA（Playwright 截图首页浅/深双色 + 专题/资讯杂志块近景）：
- **QA 结论**：首页两色、专题卡（封面/标题/描述/数量）、资讯头条+列表杂志块均渲染良好、布局密实专业，无破版/对比度问题。
- **QA 驱动的改进**：截图发现封面/缩略图在外链图片加载期间会闪现纯白。为所有资讯封面与缩略图加中性占位底色（加载/失败时优雅降级，不再刺眼空白）：主题版 `.news-cover/.news-list-thumb/.news-item .thumb` 加 `var(--surface-2)`；无头版资讯封面/缩略图加 `bg-gray-100 dark:bg-gray-800`。
- 实测：无头版资讯详情 200、占位底色生效；前端构建通过。

## [v1.6.27] - 2026-06-17

### 优化（迭代 8：资讯详情页升级为两栏杂志布局 + 侧栏 —— 参考 JustNews 文章页）
JustNews 文章页带侧栏排行/推荐组件，而本站资讯详情此前是窄单栏。两版同步升级为杂志式两栏：
- 资讯详情改为「正文主栏（≈2/3）+ 侧栏」：主栏含封面/正文/上下篇/相关资讯；侧栏含 **AI 快讯时间线** + **本周热门工具榜**（复用列表页侧栏组件，无新增数据）。
- 主题版复用 `.detail-grid`/`hahatool_flash_timeline`/`.rank-list`；无头版复用 `FlashTimeline`/`ToolLogo`，`getFlash`+`getAllTools` 并入详情页 `Promise.all`。
- 实测：WP `/ai-video-review/` 两栏 + 侧栏（快讯 + 5 热门工具）、200 零 warning；无头版 `/news/<slug>` `lg:col-span-2` 两栏 + 侧栏（快讯 + 5 热门工具链接）、200，构建通过。

## [v1.6.26] - 2026-06-17

### 优化（迭代 7：频道页标题图标 chip 一致性 —— 两版同步）
统一所有频道/落地页标题的图标处理：此前 `/prompts`·`/flash`·`/topics`·`/compare` 已是圆角方块 chip，但 `/news`（无头版无图标、主题版裸图标）与 `/favorites`（两版裸图标）不一致。
- `/news`：newspaper 图标入 `brand-600` 圆角方块 chip（两版）。
- `/favorites`：heart 图标入 `rose` 圆角方块 chip（两版，填充心形）。
- 至此全部频道/落地页标题图标风格统一。
- 实测：WP `/news` newspaper chip、`/favorites` rose chip，均 200 零 warning；无头版 `/news`（lucide-newspaper chip）、`/favorites`（bg-rose-600 chip）均 200，构建通过。

## [v1.6.25] - 2026-06-17

### 新增（迭代 6：首页「精选专题」板块 —— JustNews 专题介绍模块，两版同步）
承接 v1.6.24 的专题系统，把专题引流到首页（JustNews 首页招牌「专题介绍」模块）：
- 首页在「增长最快」之后新增**「精选专题」板块**：取热度前 3 的专题，封面卡网格（封面 + 名称 + 描述 + 工具数），「全部专题 →」入口指向 `/topics`。
- 两版同步：主题版 `front-page.php` 复用 `.topic-grid/.topic-card` 样式；无头版 `page.tsx` 复用专题卡片标记（`getTopics()` 并入首页 `Promise.all`）。
- 实测：WP 首页「精选专题」3 卡 + 3 个 `/topic/` 链接、HTTP 200、零 PHP warning；无头版前端构建通过，ISR 回填后首页渲染 3 专题卡。

## [v1.6.24] - 2026-06-16

### 新增（迭代 5：专题 Special Topics —— JustNews 招牌模块，两版同步）
用户点名的「专题列表」此前两版皆缺。本轮落地完整可导航的专题系统：
- **数据模型**：mu-plugin 注册 `topic` 自定义分类法（public、show_in_rest、归档 `/topic/<slug>/`）+ 封面 term meta `topic_cover`。
- **种子**：`scripts/seed-topics.php`（幂等，wp-cli `eval-file`）创建 3 个专题（AI 视频创作 / AI 编程提效 / AI 写作助手）并关联现有工具（5/4/5）。
- **WordPress 主题**：`/topics` 专题列表（封面卡网格）+ `taxonomy-topic.php` 专题归档（封面大图头 + 工具卡网格 + 统一分页）+ 顶栏/页脚导航「专题」+ 标题/描述 SEO + 新增 `layers` 图标与 `.topic-hero/.topic-grid/.topic-card` 样式。
- **无头前台**：`Topic` 类型 + `getTopics()/getTopicBySlug()` API（读 `topic_cover` term meta）+ `/topics` 列表页 + `/topic/[slug]` 详情页（封面头 + ToolGrid）+ 顶栏/页脚导航「专题」。
- 实测：WP `/topics`（3 卡）、`/topic/ai-video-create`（hero + 5 工具卡）、导航均 200 渲染、全 lint 通过；无头 `/topic/ai-video-create`（动态，200 + 5 工具卡 + 导航）即时可用；`/topics`（静态 ISR）回填后渲染 3 专题卡。
- 后续：首页「精选专题」卡片板块（JustNews 专题介绍模块）作下轮。

## [v1.6.23] - 2026-06-16

### 修复（快讯模块打磨：暗色徽章 + 标题图标 —— 参考 JustNews）
- **快讯日期徽章暗色不可见 Bug**：`hahatool_flash_timeline` 的日期徽章硬编码 `#111827`，而暗色模式 `--surface` 恰为 `#111827`，徽章与卡面同色导致**暗色下完全不可见**。改为 `.flash-day` 类，暗色下用 `var(--brand-600)`（对齐无头版 `dark:bg-brand-600`）。影响资讯侧栏与 `/flash` 全部快讯时间线。
- **`/flash` 标题图标对齐**：裸 `brand-500` zap 图标 → `brand-600` 圆角方块白色图标，对齐无头版 `/flash`（与 `/prompts` 风格统一）。
- 时间线连接线 + 节点圆点经核对两版已一致（`.flash` `border-left` + `.it::before` 圆点 ≈ 无头 `border-l-2` + 绝对定位圆点）。
- 实测：`.flash-day` 类生效、零 `#111827` 硬编码徽章残留、`/flash` 标题 zap 入 chip、`/flash` 与 `/news` 均 200 零 warning。
- 队列（下轮）：`/news`、`/favorites` 标题图标 chip 化（含无头版 `/news` 当前无 chip），需前端重建，作专项「频道页标题一致性」迭代；以及 JustNews 招牌 **专题（Special Topics）** 系统。

## [v1.6.22] - 2026-06-16

### 优化（资讯杂志化迭代 3：首页资讯板块「头条+列表」布局 —— 参考 JustNews）
- 首页「AI 资讯」由平铺 3 卡网格升级为 JustNews 式**两栏杂志布局**：左侧头条大图卡（16:9 封面 + 大标题 + 摘要 + 日期·阅读时长），右侧紧凑列表（小缩略图 + 2 行标题 + meta），移动端自动堆叠。
- 两版同步：无头版 `lg:grid-cols-2` + `divide-y` 列表；主题版新增 `.news-feature-grid / .news-list / .news-list-item / .news-list-thumb` 样式。首页资讯抓取量 3 → 5。
- 已验证：WP 首页头条+3 列表项、各带「N 分钟阅读」、零 PHP warning、HTTP 200；无头版前端构建通过。
- 附记：上版（v1.6.21）记录的「无头版首页资讯为空」经确认为**重建后 ISR 短暂未回填**，非缺陷——已自愈（首页 15 条资讯链接 + 阅读时长正常渲染）。

## [v1.6.21] - 2026-06-16

### 新增（资讯杂志化迭代 2：阅读时长 meta —— 参考 JustNews）
JustNews 列表项以「日期 · 浏览 · 评论」等富 meta 著称。本轮为资讯加入「预计阅读时长」并形成统一 meta 行：
- **mu-plugin** 新增共享函数 `hahatool_read_time($content)`（中文 ~400 字/分钟，至少 1 分钟）+ `read_time` REST 字段，WP 主题与无头版共用同一口径。
- **数据链路**：`types.ts` `NewsItem.readTime` / `WpPost.read_time` → `api.ts` `toNews` 映射。
- **两版资讯 meta 行**统一为「日期 · 🕐 N 分钟阅读」（新增 lucide `clock` 图标 + `.news-meta` 样式）：资讯列表项、资讯头条、资讯详情、首页资讯卡。
- 已验证：WP 资讯列表/头条/详情/首页卡全部渲染「1 分钟阅读」；无头版 `/news` 列表+头条、`/news/<slug>` 详情均渲染；4 条 ai-news 文章 `read_time` 经 REST 正确返回；零 PHP warning、列表路由全 200；前端构建通过。
- **待办（下轮）**：无头版首页「AI 资讯」板块当前渲染为空（`getNews(1,3)` 在静态首页返回空，疑似构建期/ISR 数据问题，与本次改动无关——首页工具数据正常 28 款），首页资讯卡的阅读时长待该问题修复后显现；下一迭代专项排查。

## [v1.6.20] - 2026-06-16

### 修复（列表页分页统一 —— 对齐无头版单一分页组件）
迭代起点：开始以 JustNews（https://demo.wpcom.cn/justnews）杂志风格为参考，逐板块打磨资讯/专题/快讯/列表页。本轮先收口「列表页」的分页不一致：
- 主题版此前有**三套**分页：`/tools` 自定义平铺页码（无上下页、无窗口化）、`the_posts_pagination`（快讯/资讯）、`paginate_links`（分类/标签/首页索引）；无头版统一用一个 `Pagination` 组件（chevron 上下页 + 当前页 ±2 窗口 + 首末页省略号）。
- 新增 `hahatool_pagination($current, $total, $href_fn)` 统一助手，视觉对齐无头版（chevron SVG、`.pg` 36×36 圆角、当前页 brand 底、`…` 省略号）。
- 全部 6 处列表分页改用该助手：`/tools`（查询参数 `paged`）、分类快讯/资讯、工具分类、标签、首页索引（后五者用 `get_pagenum_link` 适配 WP 路径式分页）。
- 实测：`/tools` 第 1/2 页上下页 chevron + 窗口页码正确；6 条列表路由零 PHP warning、全 200；`/news` 仅 1 页时正确不渲染分页。

## [v1.6.19] - 2026-06-16

### 修复（全局组件 Header/Footer/404 两版本一致性）
团队迭代轮：核对全局组件，修复一批真实差异：
- **导航「分类 ▾」字符 → `ChevronDown` SVG**：此前用 `▾` 字符（上一轮字符横扫的正则漏网项），无头版用 lucide `ChevronDown`。改为 SVG 并加悬停旋转 180°（对齐无头版 `group-hover:rotate-180`）。新增 `chevron-down` 图标。
- **页脚结构对齐无头版**：主题版此前为「探索(4) / 资讯(2) / 运营(2)」，无头版为「探索(8) / 热门分类 / 运营」。重构主题版页脚：探索列补至 8 项（全部工具/流量排行榜/增长黑马榜/工具 PK 对比/AI 提示词库/我的收藏/AI 快讯/AI 资讯，标签同无头版），资讯列改为「热门分类」（动态取 6 个工具分类），运营列不变。
- **404 页对齐**：主题版 404 有「返回首页 + 浏览工具库」双按钮，无头版此前仅「返回首页」。给无头版补「浏览工具库」按钮，两版 404 行动一致。
- 实测：分类下拉 chevron SVG（`▾` 残留 0）；主题版页脚 8 项探索 + 热门分类列；两版 404 均双按钮；前端构建通过；核心路由全 200。

## [v1.6.18] - 2026-06-16

### 修复（全主题字符型图标横扫 —— 全部改 SVG，对齐无头版与设计标准）
团队迭代轮：对全主题做字符型图标（`★ ‹ › ● ✓` 等）横扫，逐一对照无头版 lucide 用法改为 SVG：
- **星级评分 `★★★★★` → lucide `Star` SVG**：`hahatool_stars` 此前用 ★ 字符双层填充，无头版用 `Star` SVG。改为内联五角星 SVG（保留双层 `.bg/.fg` 小数填充，`flex-shrink:0` 配合 `overflow:hidden` 精确裁切）。该函数在工具卡/详情/排行榜/PK/侧栏全站复用 —— 一处修复全站对齐。
- **首页板块「查看全部 ›」`›` 字符 → `ChevronRight` SVG**：6 处 `.more` 链接对齐无头版 `SectionHeader`。
- **Banner/运营位「立即体验 →」`→` 字符 → `ArrowRight` SVG**：对齐无头版 `PromoBanner`。新增 `arrow-right` 图标。
- **复制按钮「✓ 已复制」`✓` 字符 → lucide `Check` SVG**（theme.js，对齐无头版 `CopyButton`）。
- **PK 页「选手 A/B」`●` 字符 → CSS 圆点**（对齐无头版 `rounded-full` 圆点）。
- 横扫确认：全主题 `★‹›●✓✕×○♥` 等字符型图标残留**清零**；剩余 6 处 `→` 均为「更多/招商」文本箭头，与无头版（`全部快讯 →`/`联系投放 →`/`发起 PK →` 等）一致，保留。
- 实测：星级 SVG 在首页/工具库/排行榜/PK/详情渲染、小数填充 `width:96%` 生效；首页 `›` 残留 0、14 个 `.more` chevron；banner/复制/圆点均 SVG；7 条路由全 200。

## [v1.6.17] - 2026-06-16

### 修复（提示词库/提示词详情/资讯详情两版本一致性）
团队迭代轮：对比提示词与资讯相关页，主题版多处落后无头版，全栈补齐：
- **提示词库补底部投稿引导**：无头版底部有「有好用的提示词想分享？通过提交页投稿给我们」，主题版此前无。已补（链接 `/submit/`）。
- **提示词库标题图标对齐**：裸 `brand-500` 着色图标 → `brand-600` 圆角方块白色图标（同无头版，亦与上轮 PK 页标题风格统一）；副标题「点」→「点击」。
- **返回/上下篇箭头改 SVG**：提示词详情、资讯详情的「‹ 返回」「‹ 上一篇」「下一篇 ›」用了 `‹ ›` 字符，无头版用 lucide `Chevron` SVG。新增 `chevron-left/right` 图标并替换，符合「图标用 SVG」标准；两页 `‹ ›` 残留清零。
- **资讯详情返回文案对齐**：「返回资讯」→「返回资讯列表」。
- **提示词详情相关网格对齐**：同场景提示词由 3 列改 2 列（新增 `.grid-2`，对齐无头版 `sm:grid-cols-2`）。
- 实测：提示词库投稿引导/方块图标/「点击」、详情页 chevron SVG、grid-2、资讯返回文案与链接均渲染；6 条路由（含提示词/资讯详情）全 200。

## [v1.6.16] - 2026-06-16

### 修复（首页两版本一致性 + 无头版 emoji 合规）
团队迭代轮：对比首页两版（板块最多的页面），双向修复真实差异：
- **无头版去最后一个 emoji 图标**：首页分类导航条「增长最快」用了 `🔥` emoji，违反项目「图标用 SVG 不用 emoji」标准，且与本页增长板块自身的 lucide `Flame` 角标不一致。改为 `<Flame>` SVG（橙色）。全前端 emoji 扫描确认仅此一处，现已清零。
- **主题版「增长最快」NO. 角标对齐**：此前是深色方块数字，无头版是橙色 `Flame` 胶囊。改为橙色 flame 胶囊角标（`hahatool_tool_card` 的 `$rank` 仅首页增长板块使用）。
- **主题版资讯卡补封面图**：无头版首页「AI 资讯」卡顶部有 2:1 封面图，主题版此前无图。新增 `.news-card` 结构（封面 `aspect-ratio:2/1` + 内容区）。
- **主题版「查看增长榜」补 `?by=growth`**：此前指向默认流量榜，无头版指向增长榜。
- 实测：前端构建通过、重启后首页 `🔥` 残留 0、catbar 渲染 `lucide-flame`；主题版增长榜链接带 `?by=growth`、橙色 flame 角标、3 张资讯封面卡均渲染；核心路由全 200。

## [v1.6.15] - 2026-06-16

### 修复（工具详情页两版本一致性）
团队迭代轮：对比工具详情页两版，主题版侧栏缺多处交互跨链，全栈补齐：
- **能力雷达面板补「发起 PK →」链接**：无头版雷达标题右侧有，指向 `/compare?a=<slug>`，主题版此前缺。
- **替代品每行补「PK」按钮**：无头版每个替代品右侧有 PK 跳转（`/compare?a=…&b=…`），主题版缺。改用绝对填充链接避免嵌套 `<a>`，新增 `.alt-pk` 悬停态。
- **替代品列表补底部「查看{分类}全部工具 →」链接**（对齐无头版）。
- **头部统计条顺序对齐**：收录时间移到「浏览」之前（收藏→月访问→收录→浏览）；统计图标按无头版着色（收藏 brand、月访问 emerald、收录 灰、浏览 sky）。
- 实测：雷达 PK 链接、4 个替代品 PK 按钮、分类全部工具链接均渲染；PK 跳转 `?a=chatgpt&b=claude` 正确选中双方；7 条核心路由全 200。

## [v1.6.14] - 2026-06-16

### 修复（工具 PK 对比页两版本一致性）
团队迭代轮：对比 `/compare` 两版实现，主题版多处落后无头版，全栈补齐：
- **数据表补 3 行**：无头版有「分类 / 标签 / 收录时间」，主题版此前缺。补齐三行（分类排除保留分类、标签加 `#` 前缀、收录时间 `Y-m-d`），与无头版同序同内容。
- **对阵卡补「访问官网」外链**：无头版每张对阵卡有官网链接，主题版缺。补 `data-track-click` 外链 + arrow-up-right 图标。
- **评分行改星级**：无头版评分行用 `RatingStars` 星级 + 胜出徽章，主题版此前只显示数字。改用 `hahatool_stars()`。
- **月增长率正值补 `+` 号**（`+5.2%`，对齐无头版）；**标题 PK 图标补深色圆角方块底**。
- 实测：`?a=chatgpt&b=midjourney` 三新行真实数据渲染、正增长带 `+`、零 PHP warning/notice、7 条核心路由全 200（`/tool/<slug>` 按 WP 永久链接 301→`/<slug>/` 属既有 URL 结构差异）。

## [v1.6.13] - 2026-06-16

### 修复（排行榜两版本一致性 + 增长徽章图标标准）
团队迭代轮：架构师/产品对比排行榜页，发现两处真实差异，全栈修复：
- **排行榜列表追平无头版**：主题版第 4 名起的列表此前只有单个「随榜切换」的数值列、无列头。改为与无头版一致 —— 桌面显示列头（排名/工具/增长/月访问/收藏）+ 每行 3 个固定数据列（增长徽章 + 月访问 + 收藏），移动端自动收起仅留排名/工具/简介；新品榜行补「收录日期 收录」标签；简介统一单行截断。新增 `.rk-head/.rk-list/.rk-num` 响应式样式。
- **增长徽章改 SVG**：`hahatool_growth_badge()` 此前用 `▲/▼` 字符，无头版 `GrowthBadge` 用 lucide `TrendingUp/Down` SVG。改用 `hh_icon('trending'|'trending-down')` SVG，符合「图标用 SVG 不用字符」设计标准；该徽章在工具卡、领奖台、排行榜列表全站生效，一处修复全站对齐。新增 `trending-down` 图标。
- 实测：6 条核心路由 HTTP 200、全站零 `▲/▼` 残留、涨/跌徽章与首页卡片均渲染 SVG。

## [v1.6.12] - 2026-06-16

### 修复（两版本搜索结果一致性）
团队迭代轮：测试工程师发现主题版搜索页与无头版存在两处可见差异，全栈修复：
- **资讯结果补摘要**：主题版搜索的资讯结果此前只显示标题+日期，无头版有 2 行摘要。补齐 `.news-item` 的摘要段（CSS 既有 2 行截断样式），与无头版及 `category.php` 资讯流一致。
- **搜索抓取量对齐**：主题版搜索此前每页 24 条，无头版一次抓取 60 条。将 `pre_get_posts` 搜索分支提至 60，分类/标签归档保持 24/页不变。
- 同步刷新 `docs/KNOWN_ISSUES.md` 一致性矩阵，补录近 11 轮已对齐的 SEO/结构化数据/图标/字体/防刷/频道 URL 等维度。

## [v1.6.0] - 2026-06-15

### 新增（WordPress 主题追平无头原版）
对照 Next.js 原版补齐主题模式此前缺失的功能，使两种前台功能对等：
- **收藏系统**：卡片/详情页 ♥ 收藏按钮、顶栏心形计数徽章、`/favorites` 收藏页（localStorage，前端筛选，无需登录）
- **首页快讯跑马灯**：CSS 滚动 + 悬停暂停 + reduced-motion 降级
- **搜索即时建议**：顶栏/Hero 搜索框输入即出下拉（REST 查询，区分工具/提示词/资讯）
- **标签体系**：专用 `tag.php`（含相关标签云）+ 首页「按标签找工具」板块
- **资讯页改版**：头条大图卡 + 列表 + 侧栏（快讯时间线 + 本周热门工具）
- **快讯按天分组**时间线
- **信息流广告位**（工具库 `tools-inline`）+ **404 页** + 详情页 **OG/Twitter 卡片**
- 顶栏防换行优化：导航 nowrap、提交/收藏移入移动菜单、搜索框自适应收窄

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
