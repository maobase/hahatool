<?php
/**
 * 真实 AI/科技资讯种子（第二批：硬件/机器人/具身智能/融资）—— 幂等。
 * 封面走对象存储 /media/hahatool-media/news/<slug>.jpg（需先把图片 mc cp 到 MinIO）。
 * 运行：docker compose run --rm wpcli wp eval-file /scripts/seed-real-news-2.php
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['nvidia-cosmos-3', '英伟达发布 Cosmos 3：全球首款全开放全模态物理 AI 模型', '2026-06-04 09:00:00',
        '<p>英伟达正式发布 Cosmos 3，基于「混合 Transformer」架构，将视觉推理、世界生成与动作预测整合到单一系统，可原生理解并生成文本、图像、视频、环境声音与动作，号称全球首款完全开放的全模态物理 AI 模型，并将物理 AI 的训练与评估周期从数月缩短到数天。</p>',
        '新浪财经', 'https://finance.sina.com.cn/roll/2026-06-04/doc-iniafvzs9331352.shtml'],
    ['nvidia-unitree-humanoid', '英伟达携手宇树科技推出科研人形机器人系统', '2026-06-03 14:00:00',
        '<p>英伟达与中国初创企业宇树科技合作，推出面向科研领域的人形机器人系统，搭载英伟达 Blackwell 架构芯片，进一步推动具身智能研究与落地。</p>',
        '新浪科技', 'https://k.sina.cn/article_7857201856_1d45362c00190693wi.html'],
    ['aizrobotics-b-round', '智平方完成 B 轮融资超 10 亿元，估值突破百亿', '2026-06-02 10:30:00',
        '<p>具身智能创业公司智平方（AI²Robotics）宣布完成 B 轮系列融资，规模超 10 亿元人民币，估值正式突破百亿。业内认为，具身智能赛道中数据与场景理解的价值，正变得不亚于模型参数规模。</p>',
        '量子位', 'https://www.qbitai.com/2026/02/382004.html'],
    ['openai-robotics-team', 'OpenAI 宣布进军机器人研发，招募全栈硬件团队', '2026-06-05 08:30:00',
        '<p>OpenAI CEO Sam Altman 透露，OpenAI 将进军机器人研发，正在招聘优秀的全栈硬件、运营、系统及机器学习工程师，AI 巨头加速布局具身智能。</p>',
        '钛媒体', 'https://www.tmtpost.com/8009284.html'],
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
    update_post_meta($pid, 'cover', "https://tool.hahaha.chat/media/hahatool-media/news/{$it[0]}.jpg?v=1");
    $n++;
    WP_CLI::log("新增：{$it[1]}");
}
WP_CLI::success("第二批真实资讯完成，新增 $n 篇");
