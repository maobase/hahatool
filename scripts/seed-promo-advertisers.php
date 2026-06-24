<?php
/**
 * 推广位示例广告主（套壳 AI / API 中转商档位）—— 幂等。
 * AI 导航站真实的付费广告主多为「API 中转聚合」「套壳镜像站」，故推广 banner 用这类档位演示，
 * 比拿 ChatGPT/Midjourney 占位更真实。同时取消 ChatGPT/Midjourney 的 banner 标记。
 * 链接指向各自站内详情页（避免死链）；详情正文标注「示例推广位」保持诚实。
 */
$cat = get_term_by('slug', 'chat-assistant', 'category');
if (!$cat) { $cs = get_categories(['hide_empty' => false, 'exclude' => implode(',', hahatool_reserved_ids()), 'number' => 1]); $cat = $cs ? $cs[0] : null; }
$cid = $cat ? (int) $cat->term_id : 0;
$base = 'https://tool.hahaha.chat/media/hahatool-media/tools/logos/';

$ads = [
    ['wuting-api', '雾町 API', 'OpenAI·Claude·Gemini 全系模型 API 中转聚合，官方稳定直连，低至 1.5 折，高并发不限速。', '付费',
        128000, 4.6, 860, 18.5, 'wuting-api.svg',
        '<p>雾町 API 是面向开发者的大模型 API 中转聚合服务，一个 Key 调用 OpenAI、Anthropic Claude、Google Gemini 等全系模型，官方线路直连、按量计费、价格低至官方 1.5 折，支持高并发与流式输出。</p>'],
    ['zhihe-ai', '智核 AI 助手', '国内免梯直连 ChatGPT / Claude / Midjourney 绘画，注册即送体验额度，支付宝微信充值。', '免费增值',
        216000, 4.4, 1340, 24.0, 'zhihe-ai.svg',
        '<p>智核 AI 助手是聚合 ChatGPT、Claude、Midjourney 绘画等能力的一站式镜像站，国内网络免梯直连，注册即送体验额度，支持支付宝/微信充值，适合个人与团队日常使用。</p>'],
];

$n = 0;
foreach ($ads as $a) {
    [$slug, $name, $tagline, $pricing, $visits, $rating, $likes, $growth, $logo, $body] = $a;
    if (!get_page_by_path($slug, OBJECT, 'post')) {
        $pid = wp_insert_post([
            'post_title' => $name, 'post_name' => $slug, 'post_status' => 'publish', 'post_type' => 'post',
            'post_content' => $body . '<p class="muted" style="font-size:13px">（本页为站内「推广位」示例展示，非真实商业合作。）</p>',
            'post_category' => $cid ? [$cid] : [],
        ], true);
        if (is_wp_error($pid)) { WP_CLI::warning($slug . ': ' . $pid->get_error_message()); continue; }
    } else {
        $pid = get_page_by_path($slug, OBJECT, 'post')->ID;
    }
    update_post_meta($pid, 'url', 'https://tool.hahaha.chat/' . $slug . '/');
    update_post_meta($pid, 'tagline', $tagline);
    update_post_meta($pid, 'pricing', $pricing);
    update_post_meta($pid, 'banner', '1');
    update_post_meta($pid, 'logo', $base . $logo);
    update_post_meta($pid, 'monthly_visits', $visits);
    update_post_meta($pid, 'rating', $rating);
    update_post_meta($pid, 'likes', $likes);
    update_post_meta($pid, 'growth', $growth);
    $n++;
    WP_CLI::log("广告主：$name");
}

// 取消 ChatGPT / Midjourney 的 banner 标记（保留为普通工具）
foreach (['chatgpt', 'midjourney'] as $s) {
    $p = get_page_by_path($s, OBJECT, 'post');
    if ($p) { update_post_meta($p->ID, 'banner', '0'); WP_CLI::log("取消 banner：$s"); }
}
WP_CLI::success("推广位示例完成，新增/更新 $n 个广告主");
