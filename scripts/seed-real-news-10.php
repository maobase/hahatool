<?php
/**
 * 真实 AI 资讯种子（第十批：OpenAI Codex 录制回放）—— 幂等。
 * 经爱范儿原文 + techtimes 等多源交叉核实（发布 6/18，Codex app v26.616），如实标注来源。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['openai-codex-record-replay', 'OpenAI Codex 上线「录制回放」：演示一遍，AI 自动打包成可复用技能', '2026-06-18 10:00:00',
        '<p>据爱范儿等报道，OpenAI 于 6 月 18 日为 AI 编程助手 Codex 上线「录制回放」（Record &amp; Replay）功能：你只需在电脑上把一套操作正常做一遍，Codex 全程观察学习，随后自动打包成一个可复用的「技能（skill）」，之后在新任务里直接调用。</p>'
        . '<p>与传统 RPA 录制不同，它生成的不是写死坐标的脚本，而是一份自然语言的 SKILL.md 说明——记录「什么时候用、需要哪些输入、按什么步骤走、做完怎么验证」。回放时由模型结合当前屏幕状态灵活解读执行，因此界面变动时更不易失效，但在复杂多变的流程上仍不能保证 100% 成功。执行中可调用 Computer Use、浏览器操作与已安装插件。</p>'
        . '<p>该功能随 Codex 应用 v26.616 推送，面向 ChatGPT 付费用户（Plus、Pro、Business、Enterprise、Edu）开放；发布时暂未在欧洲经济区、英国与瑞士上线，OpenAI 未说明原因与时间表。</p>',
        '爱范儿', 'https://www.ifanr.com/1669204'],
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
WP_CLI::success("第十批真实资讯完成，新增 $n 篇");
