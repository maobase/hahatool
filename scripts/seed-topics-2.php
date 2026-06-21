<?php
/**
 * 专题扩充（第二批，6 个场景化专题）—— 幂等。把 /topics 页面填满，并为每个专题关联现有工具。
 * 封面走对象存储品牌图（scripts/gen-news-covers.py 的 TOPICS 生成）。
 */
$topics = [
    ['name' => 'AI 办公提效', 'slug' => 'ai-office',
        'desc' => 'AI 重塑办公流：文档、表格、演示、待办与跨页问答一站搞定。',
        'tools' => ['notion-ai', 'wps-ai', 'gamma', 'kimi', 'doubao']],
    ['name' => 'AI 数字人', 'slug' => 'ai-avatar',
        'desc' => 'AI 数字人与口播视频：形象生成、口型同步、短视频快速成片。',
        'tools' => ['heygen', 'jimeng', 'kling']],
    ['name' => 'AI 音乐 · 配音', 'slug' => 'ai-audio',
        'desc' => 'AI 音乐与配音：一句话作曲、自然人声合成、视频旁白一键生成。',
        'tools' => ['suno', 'elevenlabs']],
    ['name' => 'AI 营销文案', 'slug' => 'ai-marketing',
        'desc' => 'AI 营销文案：小红书种草、广告投放、品牌内容与出海一站式。',
        'tools' => ['copy-ai', 'jasper', 'xiezuocat', 'gamma']],
    ['name' => 'AI 学习 · 翻译', 'slug' => 'ai-learn',
        'desc' => 'AI 学习与翻译：语言学习、即时翻译、笔记答疑，学习效率拉满。',
        'tools' => ['duolingo', 'youdao', 'kimi']],
    ['name' => 'AI 搜索 · 研究', 'slug' => 'ai-search',
        'desc' => 'AI 搜索与研究：实时联网检索、带来源引用、深度调研更高效。',
        'tools' => ['perplexity', 'kimi', 'chatgpt']],
];
$base = 'https://tool.hahaha.chat/media/hahatool-media/topics/';

foreach ($topics as $t) {
    $existing = term_exists($t['slug'], 'topic');
    if (!$existing) {
        $existing = wp_insert_term($t['name'], 'topic', ['slug' => $t['slug'], 'description' => $t['desc']]);
    } else {
        wp_update_term((int) $existing['term_id'], 'topic', ['name' => $t['name'], 'description' => $t['desc']]);
    }
    if (is_wp_error($existing)) { WP_CLI::warning($t['slug'] . ': ' . $existing->get_error_message()); continue; }
    $tid = is_array($existing) ? (int) $existing['term_id'] : (int) $existing;
    update_term_meta($tid, 'topic_cover', $base . $t['slug'] . '.svg');
    $assigned = 0;
    foreach ($t['tools'] as $slug) {
        $p = get_page_by_path($slug, OBJECT, 'post');
        if ($p) { wp_set_object_terms($p->ID, $t['slug'], 'topic', true); $assigned++; }
    }
    WP_CLI::log("专题「{$t['name']}」(#{$tid}) 关联 {$assigned} 个工具");
}
WP_CLI::success('专题扩充完成');
