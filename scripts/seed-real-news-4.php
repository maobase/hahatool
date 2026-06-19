<?php
/**
 * 真实 AI 资讯种子（第四批：国产 AI 算力芯片）—— 幂等。
 * 经量子位、证券时报核实（WebFetch 原文），如实标注来源。封面走对象存储品牌图。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['suanmiao-tokenpu-tapeout', '算苗 3D TokenPU 芯片正式流片：3D 堆叠架构，16TB/s 访存带宽', '2026-06-17 11:00:00',
        '<p>据量子位报道，专注 3D 架构 AI 云端大算力芯片的算苗科技宣布，其 3D TokenPU 芯片 A4E 已于 6 月 15 日正式流片。该芯片采用 3D 混合堆叠架构，将 8 层存储晶圆垂直堆叠在计算逻辑晶圆之上，通过硅通孔与凸点技术实现微米级互联，提供高达 16TB/s 的访存带宽。</p>'
        . '<p>A4E 面向大模型推理场景，主打突破「内存墙、算力墙、通信墙」三大瓶颈，并强调基于国产供应链的自主可控，目标是为国内大模型产业提供高性能、高性价比的算力支撑。</p>',
        '量子位', 'https://www.qbitai.com/2026/06/436213.html'],
    ['enflame-ipo-approved', '燧原科技 IPO 过会：自研云端 AI 芯片，拟募资 60 亿元', '2026-06-16 10:00:00',
        '<p>据证券时报报道，上海燧原科技股份有限公司首发申请于 6 月 15 日获上交所上市委会议通过。燧原科技围绕自研云端 AI 芯片，构建了覆盖 AI 加速卡及模组、智算系统及集群的全栈算力产品体系，并通过自研软件平台「驭算 TopsRider」实现软硬协同优化。</p>'
        . '<p>据招股材料，公司拟发行约 6835 万股、募集资金 60 亿元，主要投向五代及六代 AI 芯片系列产品研发及产业化、人工智能软硬件协同创新等项目。2023 至 2025 年公司营收分别为 3.01 亿、7.22 亿、9.90 亿元，净利润仍为亏损但幅度逐年收窄。</p>',
        '证券时报', 'https://www.stcn.com/article/detail/3963299.html'],
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
WP_CLI::success("第四批真实资讯完成，新增 $n 篇");
