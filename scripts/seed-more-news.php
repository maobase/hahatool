<?php
/**
 * 补充资讯/快讯种子内容（幂等，按 slug 去重），让杂志式板块更饱满。
 * 运行：docker compose run --rm wpcli wp eval-file /scripts/seed-more-news.php
 */
function hh_seed_cat($slug) {
    $t = get_category_by_slug($slug);
    return $t ? (int) $t->term_id : 0;
}
$news_cat = hh_seed_cat('ai-news');
$flash_cat = hh_seed_cat('ai-flash');

function hh_seed_post($slug, $title, $date, $cat, $content, $cover = '') {
    if (get_page_by_path($slug, OBJECT, 'post')) { WP_CLI::log("跳过已存在：$slug"); return; }
    $pid = wp_insert_post([
        'post_title' => $title,
        'post_name' => $slug,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_content' => $content,
        'post_date' => $date,
        'post_date_gmt' => get_gmt_from_date($date),
        'post_category' => [$cat],
    ], true);
    if (is_wp_error($pid)) { WP_CLI::warning("$slug: " . $pid->get_error_message()); return; }
    if ($cover) update_post_meta($pid, 'cover', $cover);
    WP_CLI::log("新增：$title");
}

$P = fn($s) => '<p>' . $s . '</p>';
$H = fn($s) => '<h2>' . $s . '</h2>';

// ---- 资讯（带封面、近一周日期）----
$news = [
    ['ai-agent-enterprise-2026', '企业级 AI Agent 元年：从「能聊」到「能干活」', '2026-06-16 09:20:00', 'topicagent',
        $P('2026 年被普遍视为企业级 AI Agent 落地的转折点。越来越多团队不再满足于对话式助手，而是把 Agent 接入工单、CRM、数据库，让它真正完成多步骤任务。') . $H('从单点到工作流') . $P('头部厂商相继开放工具调用与长任务编排能力，Agent 可在一次会话中跨多个系统取数、决策、回写，人类只在关键节点确认。') . $P('实测中，规范的权限边界与可观测的执行轨迹，是企业敢于把 Agent 放进生产流程的前提。')],
    ['multimodal-models-leap', '多模态大模型再进化：图文音视频统一理解成新战场', '2026-06-15 14:05:00', 'topicmm',
        $P('最新一批多模态模型在「跨模态对齐」上取得明显进步，能同时理解一段视频里的画面、语音与字幕，并据此回答复杂问题。') . $H('应用想象空间') . $P('从视频内容审核、会议纪要到无障碍辅助，统一的多模态理解正在打开一批此前难以自动化的场景。')],
    ['open-source-llm-catchup', '开源大模型加速追赶：性价比成中小团队首选', '2026-06-14 11:30:00', 'topicoss',
        $P('随着多个高质量开源权重发布，中小团队以更低成本自建推理服务的可行性大幅提升。') . $P('在「够用 + 可控 + 数据不出域」三重诉求下，开源模型在企业内网部署中的占比持续上升。')],
    ['ai-coding-benchmark', 'AI 编程助手横评：补全之外，谁更懂「改 bug」', '2026-06-13 16:40:00', 'topiccodebench',
        $P('我们用一组真实仓库的缺陷修复任务横评主流 AI 编程助手，发现「读懂上下文 + 跨文件定位」才是拉开差距的关键。') . $H('结论速览') . $P('补全体验趋同，差异主要体现在长上下文检索、测试驱动修复与对项目约定的遵循程度上。')],
    ['ai-video-cost-down', 'AI 视频生成成本继续下探，创作者迎来红利期', '2026-06-12 10:15:00', 'topicvidcost',
        $P('随着推理优化与竞争加剧，主流 AI 视频生成的单位成本较半年前明显下降，短视频创作者的试错门槛随之降低。') . $P('画质与一致性持续改善的同时，「提示词友好度」成为中文创作者选型的重要考量。')],
    ['ai-regulation-update', 'AI 合规新动向：可解释与数据来源披露受关注', '2026-06-12 08:00:00', 'topicreg',
        $P('围绕生成内容标识、训练数据来源披露与可解释性的讨论持续升温，合规正从「加分项」变为「入场券」。') . $P('对工具方而言，提前建设审计日志与内容溯源能力，有助于在 To B 市场建立信任。')],
];
foreach ($news as $n) {
    hh_seed_post($n[0], $n[1], $n[2], $news_cat, $n[4], "https://picsum.photos/seed/{$n[3]}/1200/630");
}

// ---- 快讯（短讯，近两天时间线）----
$flash = [
    ['flash-agent-protocol', '主流厂商就 Agent 工具调用协议达成初步互通共识', '2026-06-16 18:30:00'],
    ['flash-edge-inference', '端侧推理新进展：手机本地跑中等规模模型延迟再降', '2026-06-16 15:10:00'],
    ['flash-rag-eval', '检索增强（RAG）评测套件更新，更看重引用准确率', '2026-06-16 11:00:00'],
    ['flash-voice-clone-guard', '语音克隆滥用引关注，多家平台上线声纹水印', '2026-06-15 20:45:00'],
    ['flash-image-c2pa', '图像生成内容溯源标准（C2PA）采用率上升', '2026-06-15 13:20:00'],
    ['flash-gpu-supply', 'AI 算力供给改善，中小团队租用成本环比下降', '2026-06-15 09:05:00'],
    ['flash-cn-model-overseas', '又一国产模型登陆海外开发者平台', '2026-06-14 17:50:00'],
    ['flash-prompt-market', '提示词交易与共享社区活跃度创新高', '2026-06-14 10:30:00'],
];
foreach ($flash as $f) {
    hh_seed_post($f[0], $f[1], $f[2], $flash_cat, '<p>' . esc_html($f[1]) . '</p>');
}

WP_CLI::success('资讯/快讯补充种子完成');
