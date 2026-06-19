<?php
/**
 * 为热门工具补充 FAQ（faq meta，每行「问题|答案」）—— 幂等，仅在为空时写入，不覆盖已有。
 * 内容为通用、可核实的事实性问答（定价/访问/适用场景），配合 single-tool.php 的 FAQPage 结构化数据。
 * 运行：docker cp 后 docker exec hahatool-wordpress php（含 WP_CLI shim）。
 */
$faqs = [
    'claude' => [
        'Claude 免费吗？|提供免费版可体验，付费 Claude Pro 约 $20/月解锁更高用量与更强模型；企业可通过 API 按量计费接入。',
        '国内如何使用 Claude？|官方网页版需要海外网络环境与账号；开发者可通过 Anthropic API 或亚马逊云科技 Bedrock 等渠道调用。',
        'Claude 和 ChatGPT 怎么选？|Claude 在长文档处理、写作与代码生成上口碑突出；ChatGPT 生态插件与多模态更全面，可按场景选用。',
    ],
    'perplexity' => [
        'Perplexity 是什么？|一款「对话式 AI 搜索引擎」，能实时联网检索并给出带来源引用的答案，适合查资料与做调研。',
        'Perplexity 免费吗？|基础搜索免费；Pro 订阅约 $20/月，提供更强模型、更多次 Pro 搜索与文件分析。',
        'Perplexity 和普通搜索引擎有何不同？|它直接生成结构化答案并标注信息来源，省去逐条点开链接的过程，但仍建议核对引用。',
    ],
    'github-copilot' => [
        'GitHub Copilot 免费吗？|面向学生、教师与热门开源项目维护者免费；个人版约 $10/月，企业版价格更高。',
        'GitHub Copilot 支持哪些编辑器？|支持 VS Code、Visual Studio、JetBrains 系列、Neovim 等主流 IDE。',
        'Copilot 能做什么？|提供代码补全、整段生成、注释转代码与对话式 Copilot Chat，辅助编写与解释代码。',
    ],
    'kimi' => [
        'Kimi 免费吗？|网页与 App 端可免费使用，主打超长上下文，适合长文档与资料阅读。',
        'Kimi 擅长什么？|以超长文本处理见长，可一次读取长篇文档、网页与文件并总结问答。',
        'Kimi 支持联网吗？|支持联网检索，可基于实时网页信息作答并给出参考来源。',
    ],
    'suno' => [
        'Suno 是什么？|一款 AI 音乐生成工具，输入歌词或描述即可生成带人声与配乐的完整歌曲。',
        'Suno 免费吗？|提供每日免费额度，付费订阅可获得更多生成次数与商用授权。',
        'Suno 生成的歌曲能商用吗？|付费订阅通常包含商用授权，免费额度作品的商用范围受限，使用前请阅读其条款。',
    ],
    'runway' => [
        'Runway 是什么？|面向创作者的 AI 视频生成与编辑平台，支持文生视频、图生视频与多种 AI 特效。',
        'Runway 免费吗？|提供有限免费额度；付费订阅按月计费，解锁更高分辨率、更长时长与更多积分。',
        'Runway 适合谁用？|适合短视频、广告与影视创作者快速生成与编辑素材。',
    ],
    'notion-ai' => [
        'Notion AI 是什么？|集成在 Notion 里的 AI 助手，可在文档中写作、总结、翻译与自动整理信息。',
        'Notion AI 怎么收费？|作为 Notion 的付费增值功能，按用户/月订阅，可在工作区内开通。',
        'Notion AI 能做什么？|起草与润色文本、总结长文、生成表格与待办、跨页面问答等。',
    ],
    'elevenlabs' => [
        'ElevenLabs 是什么？|领先的 AI 语音合成平台，可生成自然逼真的多语种配音与语音克隆。',
        'ElevenLabs 免费吗？|提供免费额度（每月一定字符数）；付费订阅解锁更多字符、商用授权与更高音质。',
        'ElevenLabs 支持中文吗？|支持包括中文在内的多种语言，适合配音、有声书与视频旁白。',
    ],
];

$n = 0;
foreach ($faqs as $slug => $lines) {
    $p = get_page_by_path($slug, OBJECT, 'post');
    if (!$p) { WP_CLI::log("跳过（无此工具）：$slug"); continue; }
    if (trim((string) get_post_meta($p->ID, 'faq', true))) { WP_CLI::log("已有 FAQ，跳过：$slug"); continue; }
    update_post_meta($p->ID, 'faq', implode("\n", $lines));
    $n++;
    WP_CLI::log("写入 FAQ：{$p->post_title}（" . count($lines) . " 条）");
}
WP_CLI::success("FAQ 补充完成，新增 $n 个工具");
