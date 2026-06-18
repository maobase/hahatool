<?php
/**
 * 专题（topic 分类法）种子数据 —— 幂等。
 * 运行：docker compose run --rm wpcli wp eval-file /scripts/seed-topics.php
 */
$topics = [
    [
        'name' => 'AI 视频创作',
        'slug' => 'ai-video-create',
        'desc' => '用 AI 生成与剪辑视频：文生视频、配乐、设计一站式创作合集。',
        'cover' => 'https://tool.hahaha.chat/media/hahatool-media/topics/ai-video-create.svg',
        'tools' => ['runway', 'midjourney', 'stable-diffusion', 'canva', 'suno'],
    ],
    [
        'name' => 'AI 编程提效',
        'slug' => 'ai-coding',
        'desc' => '让 AI 帮你写代码、补全、审查与重构，开发者效率工具合集。',
        'cover' => 'https://tool.hahaha.chat/media/hahatool-media/topics/ai-coding.svg',
        'tools' => ['github-copilot', 'cursor', 'claude', 'chatgpt'],
    ],
    [
        'name' => 'AI 写作助手',
        'slug' => 'ai-writing',
        'desc' => '从灵感到成稿：写作、润色、翻译、笔记，中文创作者必备合集。',
        'cover' => 'https://tool.hahaha.chat/media/hahatool-media/topics/ai-writing.svg',
        'tools' => ['notion-ai', 'xiezuocat', 'grammarly', 'kimi', 'chatgpt'],
    ],
];

foreach ($topics as $t) {
    $existing = term_exists($t['slug'], 'topic');
    if (!$existing) {
        $existing = wp_insert_term($t['name'], 'topic', ['slug' => $t['slug'], 'description' => $t['desc']]);
    } else {
        wp_update_term((int) $existing['term_id'], 'topic', ['name' => $t['name'], 'description' => $t['desc']]);
    }
    if (is_wp_error($existing)) {
        WP_CLI::warning($t['slug'] . ': ' . $existing->get_error_message());
        continue;
    }
    $tid = is_array($existing) ? (int) $existing['term_id'] : (int) $existing;
    update_term_meta($tid, 'topic_cover', $t['cover']);
    $assigned = 0;
    foreach ($t['tools'] as $slug) {
        $p = get_page_by_path($slug, OBJECT, 'post');
        if ($p) {
            wp_set_object_terms($p->ID, $t['slug'], 'topic', true);
            $assigned++;
        }
    }
    WP_CLI::log("专题「{$t['name']}」(#{$tid}) 已关联 {$assigned} 个工具");
}
WP_CLI::success('专题种子完成');
