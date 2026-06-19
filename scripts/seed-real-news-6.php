<?php
/**
 * 真实 AI 资讯种子（第六批：Anthropic IPO + 谷歌 Antigravity）—— 幂等。
 * 经 36 氪核实（WebFetch 原文），如实标注来源。封面走对象存储品牌图（SVG 显示 + PNG 供 og）。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;
$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['anthropic-ipo-s1', 'Anthropic 保密提交 IPO 招股书：估值 9650 亿美元，最快今秋上市', '2026-06-01 20:00:00',
        '<p>据 36 氪报道，Anthropic 于 6 月 1 日向美国证券交易委员会（SEC）保密提交了 S-1 注册声明草案，正式启动 IPO 进程，最快有望于今年秋季挂牌。此前 5 月 28 日，公司刚宣布完成 650 亿美元 H 轮融资，投后估值达 9650 亿美元。</p>'
        . '<p>据介绍，Anthropic 年化营收（ARR）已达约 470 亿美元；本轮投资方包括 Altimeter、红杉资本、Greenoaks，以及亚马逊（50 亿美元战略增资）、三星、SK 海力士、美光等。与此同时，公司发布的 Claude Opus 4.8 据称运行速度提升约 2.5 倍、成本下降约 3 倍。</p>',
        '36氪', 'https://36kr.com/p/3835519857653129'],
    ['google-antigravity-2', '谷歌 I/O 发布 Antigravity 2.0：从 AI IDE 转向「任务中心」Agent 工作台', '2026-05-20 22:00:00',
        '<p>据 36 氪报道，谷歌在 I/O 2026 大会上正式推出 AI 编程工具 Antigravity 2.0，产品定位从「AI IDE」转变为「任务中心型 Agent 工作台」：界面重新设计为左侧项目列表、右侧对话区，不再是传统 IDE 布局。</p>'
        . '<p>新版开放支持谷歌 Gemini 系列、Claude 系列与 GPT-OSS 等第三方模型，并新增 /goal、/grill-me、/schedule、/browser 等斜杠命令，强调持续执行任务的能力。其直接竞争对手为 OpenAI 的 Codex 与 Anthropic 的 Claude Code——三者均采用对话驱动的 agent 工作流而非传统 IDE 编辑模式。</p>',
        '36氪', 'https://www.36kr.com/p/3817576331747331'],
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
WP_CLI::success("第六批真实资讯完成，新增 $n 篇");
