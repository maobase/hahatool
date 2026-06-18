<?php
/**
 * 真实 AI/科技资讯种子（聚合自公开报道，含来源链接）—— 幂等，按 slug 去重。
 * 运行：docker compose run --rm wpcli wp eval-file /scripts/seed-real-news.php
 * 注：仅作标题+事实摘要+来源链接的聚合，版权归原站；封面暂用占位图（对象存储接入后替换）。
 */
$cat = get_category_by_slug('ai-news');
if (!$cat) { WP_CLI::error('缺少 ai-news 分类'); }
$cid = (int) $cat->term_id;

$src = fn($name, $url) => '<p class="news-source">据 <a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow">' . esc_html($name) . '</a> 报道整理。</p>';

$items = [
    ['claude-fable-5-release', 'Anthropic 发布新一代大模型 Claude Fable 5', '2026-06-09 10:00:00', 'newsfable',
        '<p>Anthropic 于 6 月 9 日发布新一代通用大模型 Claude Fable 5，并同步推出 Claude Mythos 5、更新轻量级 Claude Haiku 4.5，进一步加快与同行的模型迭代节奏。</p>',
        '证券时报', 'https://www.stcn.com/article/detail/3952926.html'],
    ['openai-gpt-5-5', 'OpenAI 推出 GPT-5.5：主打编程、研究与数据分析', '2026-06-12 09:30:00', 'newsgpt55',
        '<p>OpenAI 发布最新旗舰模型 GPT-5.5，主打更快的速度与更强的复杂任务处理能力，面向编程、研究、数据分析等场景，已向 Plus、Pro、Business 与 Enterprise 用户开放。</p>',
        'OpenAI', 'https://openai.com/index/introducing-gpt-5-5/'],
    ['chatgpt-code-interpreter-upgrade', 'ChatGPT 代码能力大升级：可读取 / 修改 / 运行完整项目', '2026-06-10 14:00:00', 'newscodeint',
        '<p>OpenAI 大幅强化 ChatGPT 的 Code Interpreter：从过去的数据分析与图表生成，升级为可直接读取、修改并运行多语言完整项目文件，开发者工作流进一步自动化。</p>',
        'CSDN', 'https://deepseek.csdn.net/6a18f48410ee7a33f2764165.html'],
    ['gemini-3-pro-multimodal', 'Gemini 3 Pro：超长多模态 + 直连 Google 生产力套件', '2026-06-11 11:20:00', 'newsgemini',
        '<p>Google 的 Gemini 3 Pro 可一次性处理极长的视频、音频与文档，并直接调用 Google Docs、Gmail、Drive 中的资料，显著强化办公场景下的多模态能力。</p>',
        '知乎', 'https://zhuanlan.zhihu.com/p/670574382'],
    ['deepseek-v4-opensource', 'DeepSeek V4 开源：SWE-bench Verified 得分 80.6%', '2026-06-13 16:40:00', 'newsdeepseek',
        '<p>国产开源模型 DeepSeek V4 在代码与数学能力上表现突出，SWE-bench Verified 得分达 80.6%，位列开放权重模型顶尖水平，且 API 价格极低，受到中小团队青睐。</p>',
        '博客园', 'https://www.cnblogs.com/vipstone/p/19496540'],
    ['ai-agent-day-level', 'AI 智能体迈入「天级执行」：单任务可跑数十小时', '2026-06-16 08:30:00', 'newsagent',
        '<p>据报道，AI 智能体正从分钟级演示走向天级自主执行：Cursor Agent 单任务运行时长可达 36 小时，Claude Code 的单日提交量已占全球 GitHub 公开代码相当比例，预示软件开发范式的转变。</p>',
        'CSDN', 'https://deepseek.csdn.net/6a18f48410ee7a33f2764165.html'],
    ['anthropic-revenue-47b', 'Anthropic 年化收入突破 470 亿美元', '2026-06-14 09:00:00', 'newsrevenue',
        '<p>据报道，Anthropic 年化运营收入已突破 470 亿美元，较此前 G 轮融资时大幅增长，AI 商业化进程显著提速，三大模型厂商竞争持续升温。</p>',
        'OFweek', 'https://www.ofweek.com/ai/2026-06/ART-201721-8130-30690496.html'],
];

$n = 0;
foreach ($items as $it) {
    if (get_page_by_path($it[0], OBJECT, 'post')) { WP_CLI::log("跳过：{$it[0]}"); continue; }
    $content = $it[4] . $src($it[5], $it[6]);
    $pid = wp_insert_post([
        'post_title' => $it[1], 'post_name' => $it[0], 'post_status' => 'publish', 'post_type' => 'post',
        'post_content' => $content, 'post_date' => $it[2], 'post_date_gmt' => get_gmt_from_date($it[2]),
        'post_category' => [$cid],
    ], true);
    if (is_wp_error($pid)) { WP_CLI::warning($it[0] . ': ' . $pid->get_error_message()); continue; }
    update_post_meta($pid, 'cover', "https://picsum.photos/seed/{$it[3]}/1200/630");
    $n++;
    WP_CLI::log("新增：{$it[1]}");
}
WP_CLI::success("真实资讯种子完成，新增 $n 篇");
