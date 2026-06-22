<?php
/**
 * 真实 AI 资讯种子（第十二批：演语科技 / LiblibAI ARR）—— 幂等。
 * 经爱范儿原文核实（发布 6/18），如实标注来源与数字。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['liblibai-evoken-arr', '国产 AI 创意公司演语科技 ARR 近 3 亿美元：LiblibAI、LibTV 领跑，估值超 20 亿', '2026-06-18 10:02:00',
        '<p>据爱范儿报道，国产 AI 创意内容公司演语科技（Evoken）年化经常性收入（ARR）已接近 3 亿美元。2026 年 5 月，集团整体收入同比增速超过 3000%（约 30 倍），公司预计 2026 年底突破 6 亿美元。</p>'
        . '<p>支撑增长的是三款产品：AI 绘画社区与素材平台 LiblibAI、AI 视频创作平台 LibTV（2024 年 3 月上线，到 5 月月收入已是上线首月的 13 倍以上），以及 AI 设计 Agent「星流」（累计服务用户超千万）。LibTV 已服务近千个短剧团队、影视制作机构、广告公司与品牌客户。</p>'
        . '<p>资本层面，演语科技完成近 3 亿美元 B+ 轮融资，投后估值超过 20 亿美元——是国内少数已实现规模化营收、且仍高速增长的 AIGC 公司。</p>',
        '爱范儿', 'https://www.ifanr.com/1669210'],
];

$n = 0;
foreach ($items as $it) {
    if (get_page_by_path($it[0], OBJECT, 'post')) { WP_CLI::log("跳过：{$it[0]}"); continue; }
    $pid = wp_insert_post([
        'post_title' => $it[1], 'post_name' => $it[0], 'post_status' => 'publish', 'post_type' => 'post',
        'post_content' => $it[3] . $src($it[4], $it[5]), 'post_date' => $it[2], 'post_date_gmt' => get_gmt_from_date($it[2]),
        'post_category' => [$cid],
    ], true);
    if (is_wp_error($pid)) { WP_CLI::warning($it[0] . ': ' . $pid->get_error_message()); continue; }
    update_post_meta($pid, 'cover', "https://tool.hahaha.chat/media/hahatool-media/news/covers/{$it[0]}.svg");
    $n++;
    WP_CLI::log("新增：{$it[1]}");
}
WP_CLI::success("第十二批真实资讯完成，新增 $n 篇");
