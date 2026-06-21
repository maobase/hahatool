#!/usr/bin/env python3
# 生成 HahaTool 资讯品牌封面 SVG（1200×600，2:1）——渐变 + 关键词 + 科技网点 + 品牌字标。
# 替代无关的图库照片，所有封面统一为「有设计感、与内容相关」的编辑图，存入对象存储。
import os, html

OUT = "/tmp/hh-covers"
os.makedirs(OUT, exist_ok=True)

# slug -> (大标签, 副标题, 渐变色1, 渐变色2, 强调点色)
ITEMS = {
    "nvidia-cosmos-3":                ("物理 AI", "Cosmos 3 · 全模态世界模型", "#0f2027", "#2c5364", "#38bdf8"),
    "nvidia-unitree-humanoid":        ("人形机器人", "英伟达 × 宇树 · 科研系统", "#1a1a2e", "#16213e", "#7c3aed"),
    "aizrobotics-b-round":            ("具身智能", "智平方 B 轮 · 估值破百亿", "#0b3d2e", "#134e4a", "#34d399"),
    "openai-robotics-team":           ("OpenAI 机器人", "组建全栈硬件团队", "#2b1055", "#7597de", "#a78bfa"),
    "ai-agent-day-level":             ("AI Agent", "迈入「天级」自主任务", "#1e3a5f", "#0ea5e9", "#38bdf8"),
    "anthropic-revenue-47b":          ("Anthropic", "年化营收 47 亿美元", "#3a1c1c", "#7c2d12", "#fb923c"),
    "chatgpt-code-interpreter-upgrade":("ChatGPT", "代码解释器重大升级", "#0d3b2e", "#10b981", "#6ee7b7"),
    "claude-fable-5-release":         ("Claude Fable 5", "新一代模型发布", "#2d1b4e", "#6d28d9", "#a78bfa"),
    "deepseek-v4-opensource":         ("DeepSeek V4", "开源 · 性能跃升", "#1e1b4b", "#4338ca", "#818cf8"),
    "gemini-3-pro-multimodal":        ("Gemini 3 Pro", "原生多模态", "#1a2980", "#26d0ce", "#5eead4"),
    "openai-gpt-5-5":                 ("GPT-5.5", "OpenAI 旗舰更新", "#10162f", "#3b82f6", "#93c5fd"),
    # 第二批：原 picsum 外链 → 统一品牌封面（消除第三方依赖）
    "ai-agent-enterprise-2026":       ("企业级 Agent", "从「能聊」到「能干活」", "#1e293b", "#475569", "#818cf8"),
    "multimodal-models-leap":         ("多模态大模型", "图文音视频统一理解", "#312e81", "#4f46e5", "#a5b4fc"),
    "open-source-llm-catchup":        ("开源大模型", "性价比成中小团队首选", "#064e3b", "#047857", "#6ee7b7"),
    "ai-coding-benchmark":            ("AI 编程横评", "谁更懂「改 bug」", "#0c2d48", "#145374", "#5eead4"),
    "ai-video-cost-down":             ("AI 视频", "生成成本持续下探", "#3b0764", "#7e22ce", "#d8b4fe"),
    "ai-regulation-update":           ("AI 合规", "可解释与数据来源披露", "#422006", "#a16207", "#fcd34d"),
    "ai-video-review":                ("AI 视频实测", "主流工具横向对比", "#1e1b4b", "#5b21b6", "#c4b5fd"),
    "ai-traffic-2026-05":             ("AI 流量榜", "2026 年 5 月全球榜", "#082f49", "#0369a1", "#7dd3fc"),
    "china-ai-going-global":          ("国产大模型出海", "多款应用登顶海外榜", "#7f1d1d", "#b91c1c", "#fca5a5"),
    "ai-video-minute-era":            ("AI 视频", "迈入「分钟级」时代", "#172554", "#1d4ed8", "#93c5fd"),
    # 第三批：核实后的真实资讯（量子位/证券时报/21经济报道）
    "deepseek-mega-funding":          ("DeepSeek 融资", "首轮超 500 亿 · 估值近 4000 亿", "#1e1b4b", "#4338ca", "#818cf8"),
    "star-market-ai-llm":             ("科创板 × AI", "第五套标准拟扩容大模型", "#422006", "#a16207", "#fcd34d"),
    # 第四批：国产 AI 算力芯片（量子位 / 证券时报核实）
    "suanmiao-tokenpu-tapeout":       ("国产算力芯片", "算苗 3D TokenPU 流片", "#0c2d48", "#0e7490", "#22d3ee"),
    "enflame-ipo-approved":           ("燧原科技 IPO", "云端 AI 芯片 · 科创板过会", "#064e3b", "#059669", "#6ee7b7"),
    # 第五批：行业大会 + 模型发布（爱范儿 / 证券时报核实）
    "waic-2026-countdown":            ("WAIC 2026", "7 月上海 · 智能伙伴共创未来", "#0c2d48", "#0369a1", "#38bdf8"),
    "minimax-m3-release":             ("MiniMax M3", "MSA 稀疏注意力 · 推理提效", "#4a044e", "#a21caf", "#f0abfc"),
    # 第六批：资本市场 + AI 编程工具（36氪核实）
    "anthropic-ipo-s1":               ("Anthropic IPO", "S-1 交表 · 估值 9650 亿", "#451a03", "#b45309", "#fcd34d"),
    "google-antigravity-2":           ("Antigravity 2.0", "谷歌 · Agent 编程工作台", "#0c2d48", "#1d4ed8", "#60a5fa"),
    # 第七批：政策（证券时报核实）
    "ai-consumption-policy":          ("AI + 消费", "八部门 17 项举措落地", "#451a03", "#c2410c", "#fdba74"),
    # 第八批：世界模型 / 具身大脑融资（量子位核实）
    "daxiao-world-model-funding":     ("世界模型", "大晓机器人 · 数亿美元融资", "#134e4a", "#0d9488", "#5eead4"),
}

TPL = '''<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="600" viewBox="0 0 1200 600" font-family="-apple-system,'PingFang SC','Microsoft YaHei',sans-serif">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{c1}"/><stop offset="1" stop-color="{c2}"/>
    </linearGradient>
    <radialGradient id="glow" cx="0.82" cy="0.2" r="0.6">
      <stop offset="0" stop-color="{accent}" stop-opacity="0.55"/>
      <stop offset="1" stop-color="{accent}" stop-opacity="0"/>
    </radialGradient>
    <pattern id="dots" width="32" height="32" patternUnits="userSpaceOnUse">
      <circle cx="2" cy="2" r="1.4" fill="#ffffff" fill-opacity="0.10"/>
    </pattern>
  </defs>
  <rect width="1200" height="600" fill="url(#bg)"/>
  <rect width="1200" height="600" fill="url(#dots)"/>
  <rect width="1200" height="600" fill="url(#glow)"/>
  <!-- 科技弧线 -->
  <g fill="none" stroke="{accent}" stroke-opacity="0.35" stroke-width="2">
    <circle cx="1010" cy="120" r="60"/><circle cx="1010" cy="120" r="100" stroke-opacity="0.18"/>
    <circle cx="1010" cy="120" r="150" stroke-opacity="0.10"/>
  </g>
  <circle cx="1010" cy="120" r="10" fill="{accent}"/>
  <!-- 分类胶囊 -->
  <rect x="72" y="232" width="{chipw}" height="44" rx="22" fill="#ffffff" fill-opacity="0.14"/>
  <text x="96" y="261" font-size="22" fill="#ffffff" fill-opacity="0.92" font-weight="600">{chip}</text>
  <!-- 关键词 -->
  <text x="70" y="372" font-size="74" font-weight="800" fill="#ffffff">{label}</text>
  <!-- 副标题 -->
  <text x="72" y="424" font-size="28" fill="#ffffff" fill-opacity="0.78">{sub}</text>
  <!-- 品牌字标 -->
  <g transform="translate(72,520)">
    <rect width="34" height="34" rx="9" fill="{accent}"/>
    <text x="9" y="25" font-size="20" font-weight="800" fill="#0b1020">H</text>
    <text x="48" y="25" font-size="22" font-weight="700" fill="#ffffff">HahaTool</text>
    <text x="180" y="25" font-size="18" fill="#ffffff" fill-opacity="0.6">· AI 工具与资讯导航</text>
  </g>
</svg>'''

# 专题封面（chip 用「专题」）—— 替换无关图库照片
TOPICS = {
    "ai-writing":      ("AI 写作助手", "写作 · 润色 · 翻译 · 笔记", "#2d1b4e", "#6d28d9", "#c4b5fd"),
    "ai-coding":       ("AI 编程提效", "补全 · 审查 · 重构 · 改 bug", "#0c2d48", "#145374", "#5eead4"),
    "ai-video-create": ("AI 视频创作", "文生视频 · 配乐 · 一站式", "#3b0764", "#7e22ce", "#d8b4fe"),
    # 扩充专题，把 /topics 填满（场景化精选，覆盖现有工具）
    "ai-office":       ("AI 办公提效", "文档 · 表格 · 演示 · 待办", "#1e293b", "#475569", "#94a3b8"),
    "ai-avatar":       ("AI 数字人", "口播 · 短视频 · 虚拟形象", "#3b0764", "#9333ea", "#d8b4fe"),
    "ai-audio":        ("AI 音乐 · 配音", "作曲 · 人声 · 旁白", "#7c2d12", "#ea580c", "#fdba74"),
    "ai-marketing":    ("AI 营销文案", "种草 · 广告 · 品牌出海", "#831843", "#db2777", "#f9a8d4"),
    "ai-learn":        ("AI 学习 · 翻译", "语言 · 答疑 · 笔记", "#14532d", "#16a34a", "#86efac"),
    "ai-search":       ("AI 搜索 · 研究", "联网 · 引用 · 调研", "#0c4a6e", "#0284c7", "#7dd3fc"),
}

def emit(slug, label, sub, c1, c2, accent, chip):
    chipw = 48 + len(chip) * 12
    svg = TPL.format(c1=c1, c2=c2, accent=accent, label=html.escape(label),
                     sub=html.escape(sub), chipw=chipw, chip=html.escape(chip))
    with open(os.path.join(OUT, slug + ".svg"), "w", encoding="utf-8") as f:
        f.write(svg)

n = 0
for slug, (label, sub, c1, c2, accent) in ITEMS.items():
    emit(slug, label, sub, c1, c2, accent, "AI 资讯"); n += 1
for slug, (label, sub, c1, c2, accent) in TOPICS.items():
    emit("topic-" + slug, label, sub, c1, c2, accent, "专题"); n += 1
print(f"generated {n} covers -> {OUT}")
