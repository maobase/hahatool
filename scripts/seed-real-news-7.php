<?php
/**
 * 真实 AI 资讯种子（第七批：AI+消费政策）—— 幂等。
 * 经证券时报核实（WebFetch 原文），如实标注来源。封面走对象存储品牌图（SVG 显示 + PNG 供 og）。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['ai-consumption-policy', '八部门发布「人工智能+消费」实施意见：17 项举措推动 AI 走进千家万户', '2026-06-18 19:00:00',
        '<p>据证券时报报道，6 月 18 日，商务部等八部门发布《关于加快"人工智能+消费"发展的实施意见》，提出 17 项具体举措，从供需两端推动 AI 在消费场景的落地。</p>'
        . '<p>商品消费方面，意见提出扩大智能终端供给、推动消费电子从「功能型」向「智能型」转变，培育人形机器人消费新赛道（从工业场景向消费场景渗透），打造「人、车、家」全场景联动生态，并推动 AI 与脑机接口、增强现实等前沿技术融合。</p>'
        . '<p>服务消费方面聚焦居家、养老、文旅、住宿餐饮、教育五大场景：研究将智能家居纳入建设指南；引导养老机构配备智能护理与康复机器人、布局智能安防；提升境外人员酒店入住便利性等。</p>',
        '证券时报', 'https://www.stcn.com/article/detail/3968828.html'],
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
WP_CLI::success("第七批真实资讯完成，新增 $n 篇");
