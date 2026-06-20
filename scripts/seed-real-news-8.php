<?php
/**
 * 真实 AI 资讯种子（第八批：世界模型 / 具身大脑融资）—— 幂等。
 * 经量子位核实（WebFetch 原文），如实标注来源。封面走对象存储品牌图（SVG 显示 + PNG 供 og）。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['daxiao-world-model-funding', '大晓机器人再获数亿美元融资：世界模型路线，开源 Kairos 3.0', '2026-06-17 14:00:00',
        '<p>据量子位报道，具身大脑公司大晓机器人近期完成天使+轮融资，累计融资金额达数亿美元，达晨财智、深创投、上海科创基金、沐曦股份、复星锐正等超 15 家机构参与。</p>'
        . '<p>大晓走「世界模型」路线——一种能预测「接下来会发生什么」的 AI 大模型，相当于让机器人拥有能「脑补」未来的大脑，采用「理解—生成—预测」一体化架构。</p>'
        . '<p>半年内，大晓开源了 Kairos 3.0（号称业内首个开源商用世界模型），发布 4B 轻量版实现端侧部署，并推出搭载 30 万套中国真实住宅户型的 Kairos-Homeworld 数字训练场。</p>',
        '量子位', 'https://www.qbitai.com/2026/06/436148.html'],
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
WP_CLI::success("第八批真实资讯完成，新增 $n 篇");
