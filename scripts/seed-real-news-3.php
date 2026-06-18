<?php
/**
 * 真实 AI 资讯种子（第三批：资本市场 —— DeepSeek 融资、科创板扩容）—— 幂等。
 * 内容均经多家权威源（量子位 / 证券时报 / 21 世纪经济报道）核实，并用对冲措辞如实标注「据报道」。
 * 封面走对象存储品牌图（scripts/gen-news-covers.py 生成，按 slug）。
 * 运行：docker exec hahatool-wordpress php /var/www/html/wp-content/themes/.../ 或 wp eval-file。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['deepseek-mega-funding', 'DeepSeek 完成首轮外部融资：募资超 500 亿元，估值逼近 4000 亿', '2026-06-16 18:00:00',
        '<p>据 The Information、量子位等媒体报道，国产大模型公司深度求索（DeepSeek）已完成首轮外部融资，募资总额超 500 亿元人民币，刷新中国 AI 公司单轮融资纪录；融资后估值升至 3500 亿至 4000 亿元区间。</p>'
        . '<p>据报道，创始人梁文锋以自有资金先行投入约 200 亿元，约占本轮融资的四成，腾讯、宁德时代等机构亦在参投之列。与融资同步，DeepSeek 新一代模型 V4.1 计划于 6 月推出。</p>'
        . '<p>截至发稿，DeepSeek 未就上述融资细节作出官方置评，相关数字以媒体报道为准。</p>',
        '量子位', 'https://www.qbitai.com/2026/05/414432.html'],
    ['star-market-ai-llm', '科创板第五套标准拟扩容至 AI 大模型，多家头部企业排队 IPO', '2026-06-17 15:00:00',
        '<p>6 月 17 日，证监会主席吴清在 2026 陆家嘴论坛开幕式上表示，科创板第五套上市标准的适用范围将扩大至人工智能大模型行业。该标准原为未盈利的生物医药企业设计，扩容后将更好适配 AI 大模型「前期投入大、回报后置」的行业特点。</p>'
        . '<p>据 21 世纪经济报道，目前已有多家头部大模型企业推进上市进程：DeepSeek 完成融资、估值或突破 4000 亿元，智谱已完成上市辅导，MiniMax 已签署辅导协议。政策松绑被视为国产大模型登陆资本市场的重要一步。</p>',
        '21 世纪经济报道', 'https://www.21jingji.com/article/20260617/herald/d42f717e8b45d22e0ea7d485b7e18936.html'],
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
WP_CLI::success("第三批真实资讯完成，新增 $n 篇");
