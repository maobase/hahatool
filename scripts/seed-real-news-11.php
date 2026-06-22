<?php
/**
 * 真实 AI 资讯种子（第十一批：AI 版支付宝 / 阿宝）—— 幂等。
 * 经爱范儿原文实测核实（发布 6/18，测试 6/17），如实标注来源；含能力边界，不夸大。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 实测报道整理。</p>';

$items = [
    ['alipay-ai-agent', '支付宝上线「AI 版」：智能助手「阿宝」能点外卖、打车、记账，超级 App 卷向 Agent OS', '2026-06-18 12:05:00',
        '<p>据爱范儿实测，支付宝推出「AI 版支付宝」，内置智能助手「阿宝」，可用一句话调度支付宝内的各类服务。实测中，它能「打开麦当劳小程序帮我下单可乐」、自动收取蚂蚁森林能量、调用高德打车 API 叫车，还支持一句话记账、上传小票或截图自动记账，并能分析消费习惯与恩格尔系数。</p>'
        . '<p>能力也有边界：在订火车票、值机等场景，「阿宝」能帮你打开 12306，但还不能自动完成全流程。整体上更像把支付宝里高频的「跑腿」动作交给智能体代劳。</p>'
        . '<p>爱范儿认为，当越来越多 Skill 被统一管理、调度与执行，超级 App 正朝着「Agent OS」的方向演化——这与近期 OpenAI Codex 录制回放、各家争相把 Agent 塞进入口的趋势相互呼应。</p>',
        '爱范儿', 'https://www.ifanr.com/1669294'],
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
WP_CLI::success("第十一批真实资讯完成，新增 $n 篇");
