<?php
/**
 * HahaTool 示例数据导入（WordPress 版）
 * 用法：bash scripts/setup-wp.sh（内部通过 wp eval-file 执行）
 * 可重复执行：按 slug 去重，不会产生重复数据。
 */

$json = file_get_contents('/scripts/seed-data.json');
$data = json_decode($json, true);
if (!$data) {
    WP_CLI::error('无法读取 /scripts/seed-data.json');
}

/** 简易 Markdown → HTML（种子内容只用到 ##、列表与段落） */
function hahatool_md2html(string $md): string
{
    $lines = preg_split('/\r?\n/', trim($md));
    $html = [];
    $inList = false;
    foreach ($lines as $line) {
        $line = rtrim($line);
        if ($line === '') {
            if ($inList) { $html[] = '</ul>'; $inList = false; }
            continue;
        }
        if (preg_match('/^## (.+)$/', $line, $m)) {
            if ($inList) { $html[] = '</ul>'; $inList = false; }
            $html[] = '<h2>' . esc_html($m[1]) . '</h2>';
        } elseif (preg_match('/^- (.+)$/', $line, $m)) {
            if (!$inList) { $html[] = '<ul>'; $inList = true; }
            $html[] = '<li>' . esc_html($m[1]) . '</li>';
        } elseif (preg_match('/^<(video|iframe|img)/', $line)) {
            if ($inList) { $html[] = '</ul>'; $inList = false; }
            $html[] = $line; // 富媒体标签原样保留
        } elseif (preg_match('/^!\[([^\]]*)\]\(([^)]+)\)$/', $line, $m)) {
            if ($inList) { $html[] = '</ul>'; $inList = false; }
            $html[] = '<img src="' . esc_url($m[2]) . '" alt="' . esc_attr($m[1]) . '">';
        } elseif (preg_match('/^\*(.+)\*$/', $line, $m)) {
            if ($inList) { $html[] = '</ul>'; $inList = false; }
            $html[] = '<p><em>' . esc_html($m[1]) . '</em></p>';
        } else {
            if ($inList) { $html[] = '</ul>'; $inList = false; }
            $html[] = '<p>' . esc_html($line) . '</p>';
        }
    }
    if ($inList) { $html[] = '</ul>'; }
    return implode("\n", $html);
}

// ---------- 分类 ----------
$catIds = [];
foreach ($data['categories'] as $cat) {
    $term = get_term_by('slug', $cat['slug'], 'category');
    if (!$term) {
        $res = wp_insert_term($cat['name'], 'category', ['slug' => $cat['slug'], 'description' => $cat['desc']]);
        $catIds[$cat['slug']] = is_wp_error($res) ? 0 : $res['term_id'];
    } else {
        $catIds[$cat['slug']] = $term->term_id;
    }
}
WP_CLI::log('分类就绪: ' . count($catIds));

// ---------- 标签 ----------
foreach ($data['tags'] as $tag) {
    if (!get_term_by('slug', $tag['slug'], 'post_tag')) {
        wp_insert_term($tag['name'], 'post_tag', ['slug' => $tag['slug']]);
    }
}
WP_CLI::log('标签就绪: ' . count($data['tags']));

// ---------- 文章 ----------
$created = 0;
foreach ($data['posts'] as $post) {
    if (get_page_by_path($post['slug'], OBJECT, 'post')) {
        continue;
    }
    $postId = wp_insert_post([
        'post_title' => $post['title'],
        'post_name' => $post['slug'],
        'post_content' => hahatool_md2html($post['content']),
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_date_gmt' => gmdate('Y-m-d H:i:s', $post['created']),
        'post_date' => get_date_from_gmt(gmdate('Y-m-d H:i:s', $post['created'])),
    ], true);
    if (is_wp_error($postId)) {
        WP_CLI::warning("跳过 {$post['slug']}: " . $postId->get_error_message());
        continue;
    }
    // 分类（按 slug → id）
    $ids = array_values(array_filter(array_map(fn($slug) => $catIds[$slug] ?? 0, $post['cats'])));
    if ($ids) {
        wp_set_post_categories($postId, $ids);
    }
    // 标签（slug 列表直接设置）
    if (!empty($post['tags'])) {
        wp_set_object_terms($postId, $post['tags'], 'post_tag');
    }
    // 自定义字段
    foreach ($post['fields'] as $key => $value) {
        update_post_meta($postId, $key, $value);
    }
    $created++;
}
WP_CLI::log("新建文章: {$created}");

// ---------- 演示评论 ----------
$chatgpt = get_page_by_path('chatgpt', OBJECT, 'post');
if ($chatgpt && get_comments(['post_id' => $chatgpt->ID, 'count' => true]) == 0) {
    wp_insert_comment([
        'comment_post_ID' => $chatgpt->ID,
        'comment_author' => 'AI重度用户',
        'comment_author_email' => 'demo@hahatool.local',
        'comment_content' => '用了一年多，写方案和改代码都靠它，Plus 很值。',
        'comment_approved' => 1,
    ]);
    WP_CLI::log('演示评论已添加');
}

WP_CLI::success('HahaTool 示例数据导入完成');
