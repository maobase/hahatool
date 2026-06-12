<?php
/**
 * Plugin Name: HahaTool Core
 * Description: HahaTool 导航站核心配置——注册工具/资讯自定义字段到 REST API、放开匿名评论。
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 工具与资讯的自定义字段（meta）清单。
 * 全部注册 show_in_rest，前台 Next.js 通过 /wp-json/wp/v2/posts 的 meta 对象读取。
 */
const HAHATOOL_META_KEYS = [
    // 工具基础
    'url', 'logo', 'tagline', 'pricing', 'screenshot',
    // 数据指标
    'likes', 'monthly_visits', 'growth', 'rating', 'visits_history', 'regions', 'scores',
    // 内容增强
    'faq', 'cover',
    // 提示词频道
    'prompt', 'prompt_model', 'prompt_scene',
    // 运营位
    'featured', 'banner', 'promo',
];

add_action('init', function () {
    foreach (HAHATOOL_META_KEYS as $key) {
        register_post_meta('post', $key, [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_textarea_field',
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }
});

/** 允许匿名评论（前台评论框免登录，仍受 WP 审核设置约束） */
add_filter('rest_allow_anonymous_comments', '__return_true');

/**
 * 允许在非 HTTPS 环境使用 Application Passwords（本地 Docker 为 http）。
 * 「提交工具」表单的服务端写入依赖它。生产环境请务必启用 HTTPS。
 */
add_filter('wp_is_application_passwords_available', '__return_true');

/** REST 列表默认带出分类/标签信息时减少额外请求成本：放宽 per_page 上限到 100（WP 默认即 100，留作显式说明） */
