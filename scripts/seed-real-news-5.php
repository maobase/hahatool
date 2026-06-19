<?php
/**
 * 真实 AI 资讯种子（第五批：行业大会 + 模型发布）—— 幂等。
 * 经爱范儿、证券时报核实（WebFetch 原文），如实标注来源。封面走对象存储品牌图。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['waic-2026-countdown', '2026 世界人工智能大会定档：7 月 17–20 日上海举行，主题「智能伙伴，共创未来」', '2026-06-17 16:00:00',
        '<p>据爱范儿报道，2026 世界人工智能大会（WAIC）暨人工智能全球治理高级别会议倒计时 30 天发布会于 6 月 17 日举行。大会将于 7 月 17 日至 20 日在上海举办，分设世博、张江、西岸三大片区，主题为「智能伙伴，共创未来」。</p>'
        . '<p>发布会公布了大会首个 AI 原生平台「Hi WAIC」App，集成智能活动、供需撮合、垂直内容、圈层社交等功能，并已开启观众注册；同时推出创投、链接、智识、青创、出海五大生态矩阵。据介绍，本届大会计划设 140 余场主题论坛、1400 余位国际嘉宾、超 10 万平方米展览面积、1100 余家企业参展、3000 余项展品，并发布最高奖项 SAIL 奖 TOP30 榜单。</p>',
        '爱范儿', 'https://www.ifanr.com/digest/1669250'],
    ['minimax-m3-release', 'MiniMax 发布新一代通用模型 M3：自研稀疏注意力架构，推理效率大幅提升', '2026-06-01 12:00:00',
        '<p>据证券时报报道，MiniMax 于 6 月 1 日正式发布新一代通用模型 MiniMax M3。该模型采用全新自研的稀疏注意力架构 MiniMax Sparse Attention（MSA），在编程、智能体能力、超长上下文与原生多模态等方向实现突破。</p>'
        . '<p>据介绍，在 100 万 token 上下文规模下，M3 的单 token 计算量仅为上一代模型的约二十分之一，显著降低长上下文场景的推理成本。</p>',
        '证券时报', 'https://www.stcn.com/article/detail/3936246.html'],
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
WP_CLI::success("第五批真实资讯完成，新增 $n 篇");
