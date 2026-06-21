<?php
/**
 * 提示词库扩充（第一批，6 条原创高质量中文提示词）—— 幂等。
 * 提示词为原创实用模板（非事实陈述），用于丰富 /prompts 模块。
 * 结构：[slug, 标题, 场景, likes, 提示词正文]
 */
$cat = get_category_by_slug('ai-prompts');
if (!$cat) { WP_CLI::error('缺少 ai-prompts 分类'); }
$cid = (int) $cat->term_id;

$items = [
    ['prompt-mj-photography', 'Midjourney 摄影级提示词生成器', '绘画', 3120,
"你是 Midjourney 提示词专家。我给你一个画面主题，请输出一条可直接粘贴的英文提示词，并在下方附中文解读。要求：\n1. 结构顺序：主体 + 场景/环境 + 光线 + 镜头/视角 + 风格 + 画质\n2. 光线、镜头、材质等使用专业摄影术语（如 golden hour、85mm、shallow depth of field、cinematic lighting）\n3. 结尾附参数：--ar 16:9 --style raw --v 6\n4. 不要堆砌无关词，保持画面统一\n画面主题：{在此填写，如「雨夜霓虹下的赛博朋克街道，独行的侦探」}"],

    ['prompt-ecommerce-selling-points', '电商详情页卖点提炼', '营销', 2460,
"你是资深电商文案。针对产品「{产品名 + 一句话描述}」，请输出：\n1. 一句话核心卖点（直接戳中目标人群痛点）\n2. FABE 卖点清单：特征→优势→利益→证据，至少 4 组\n3. 详情页主文案：场景化、有画面感，可直接使用\n4. 5 个促单短句（用于按钮 / 浮窗 / 客服话术）\n补充信息：目标人群「{人群}」，价格档位「{高/中/低}」。\n要求：卖点具体可信，不夸大、不堆砌形容词。"],

    ['prompt-code-review', '代码审查与重构助手', '编程', 2980,
"你是一位严格而务实的高级工程师。请审查我贴出的代码，按以下结构输出：\n1. 一句话总评（可读性 / 健壮性 / 性能）\n2. 问题清单：按「严重 / 建议 / 可选」分级，每条标出大致位置、问题描述与原因\n3. 重构后的完整代码：保留原功能与整体风格，补必要注释\n4. 潜在边界情况与测试建议\n代码语言：{语言}\n代码：\n{在此粘贴代码}"],

    ['prompt-meeting-notes', '会议纪要智能整理器', '办公', 2210,
"你是高效的会议秘书。我会粘贴一段杂乱的会议记录或录音转写，请整理成规范纪要：\n1. 会议主题与时间（原文未提及则留空，不要编造）\n2. 关键结论：3-5 条，按重要性排序\n3. 待办事项表格：任务 | 负责人 | 截止时间\n4. 待确认 / 有分歧的问题\n要求：只基于原文，不臆测；信息缺失处标「（待补充）」。\n原始记录：\n{在此粘贴内容}"],

    ['prompt-feynman', '费曼学习法讲解器', '学习', 2640,
"请用费曼学习法帮我彻底搞懂「{概念}」：\n1. 用一个生活化类比，讲到 12 岁孩子也能听懂\n2. 拆解核心原理：分点，每点一句话\n3. 指出最常见的 2 个理解误区，并说明为什么会错\n4. 出 3 道由浅入深的自测题（先不要给答案）\n我作答后，请逐题点评、纠错，并补全我暴露出的知识盲点。"],

    ['prompt-wechat-longform', '公众号深度长文框架', '写作', 1890,
"你是资深公众号主编。围绕主题「{主题}」，先给我一份深度长文写作框架，而不是直接成文：\n1. 3 个备选标题：含一个悬念式、一个干货式、一个观点式\n2. 开头钩子：用故事 / 数据 / 反常识三选一，给出具体写法\n3. 正文 4-6 个小标题，并标注每节的核心论点\n4. 可引用的金句 2-3 条\n5. 结尾的行动号召（CTA）\n要求：观点鲜明、有信息增量，避免正确的废话与空洞口号。"],
];

$n = 0;
foreach ($items as $it) {
    if (get_page_by_path($it[0], OBJECT, 'post')) { WP_CLI::log("跳过：{$it[0]}"); continue; }
    $excerpt = mb_substr(str_replace("\n", ' ', $it[4]), 0, 60);
    $pid = wp_insert_post([
        'post_title' => $it[1], 'post_name' => $it[0], 'post_status' => 'publish', 'post_type' => 'post',
        'post_content' => '<p>' . esc_html($excerpt) . '…</p>', 'post_category' => [$cid],
    ], true);
    if (is_wp_error($pid)) { WP_CLI::warning($it[0] . ': ' . $pid->get_error_message()); continue; }
    update_post_meta($pid, 'prompt', $it[4]);
    update_post_meta($pid, 'prompt_scene', $it[2]);
    update_post_meta($pid, 'likes', $it[3]);
    $n++;
    WP_CLI::log("新增：{$it[1]}（{$it[2]}）");
}
WP_CLI::success("提示词第一批完成，新增 $n 条");
