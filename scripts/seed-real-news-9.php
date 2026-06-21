<?php
/**
 * 真实 AI 资讯种子（第九批：智能眼镜 / XR）—— 幂等。
 * 经爱范儿核实（WebFetch 原文），如实标注来源。封面走对象存储品牌图（SVG 显示 + PNG 供 og）。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['smart-glasses-2026', '2026 最强智能眼镜扎堆：Xreal Aura、Snap SPECS 发布，但「iPhone 时刻」未到', '2026-06-18 13:00:00',
        '<p>据爱范儿报道，2026 年智能眼镜赛道密集发布：谷歌与 Xreal 联合打造的 XR 眼镜 Xreal Aura 已开放预订，搭载高通骁龙 Reality Elite 芯片与 Android XR 系统，95 克重、70 度视场角、双芯片架构，计划秋季在美、英、日、加、韩等地推出，预订价 99 美元（或 299 美元定金）。</p>'
        . '<p>Snap 也发布了一体化 AR 眼镜 SPECS，132–136 克、51 度视场角、约 4 小时续航，搭载自研 Snap OS，售价 2195 美元。</p>'
        . '<p>不过爱范儿认为，Android XR + 骁龙 Reality Elite 只是「范式打底」，智能眼镜仍处于野蛮生长期，能「一锤定音」塑造未来智能交互形态的「iPhone 时刻」尚未到来。</p>',
        '爱范儿', 'https://www.ifanr.com/1669287'],
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
WP_CLI::success("第九批真实资讯完成，新增 $n 篇");
