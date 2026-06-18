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
    // 数据指标（views / clicks 为站内真实统计，由 /hahatool/v1/track 自动累加）
    'likes', 'monthly_visits', 'growth', 'rating', 'visits_history', 'regions', 'scores', 'views', 'clicks',
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

/**
 * 文章预计阅读时长（分钟）——中文按 ~400 字/分钟，至少 1 分钟。
 * 同时供 WP 主题与无头版（REST 字段）调用，保证两版口径一致。
 */
function hahatool_read_time($content) {
    $text = trim(wp_strip_all_tags((string) $content));
    if ($text === '') return 0;
    $chars = mb_strlen(preg_replace('/\s+/u', '', $text));
    return max(1, (int) ceil($chars / 400));
}

/** 阅读时长暴露到 REST（无头版列表/详情读取 post.read_time） */
add_action('rest_api_init', function () {
    register_rest_field('post', 'read_time', [
        'get_callback' => fn($post) => hahatool_read_time(get_post_field('post_content', $post['id'])),
        'schema' => ['type' => 'integer'],
    ]);
});

/**
 * 专题（Special Topics）自定义分类法 —— 跨分类的策划合集（一篇可属多个专题）。
 * 归档 URL：/topic/<slug>/；REST：/wp-json/wp/v2/topic。封面图存 term meta `topic_cover`。
 */
add_action('init', function () {
    register_taxonomy('topic', 'post', [
        'labels' => ['name' => '专题', 'singular_name' => '专题', 'menu_name' => '专题'],
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite' => ['slug' => 'topic'],
    ]);
    register_term_meta('topic', 'topic_cover', [
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback' => fn() => current_user_can('manage_categories'),
    ]);
});

/** 允许匿名评论（前台评论框免登录，仍受 WP 审核设置约束） */
add_filter('rest_allow_anonymous_comments', '__return_true');

/**
 * 允许在非 HTTPS 环境使用 Application Passwords（本地 Docker 为 http）。
 * 「提交工具」表单的服务端写入依赖它。生产环境请务必启用 HTTPS。
 */
add_filter('wp_is_application_passwords_available', '__return_true');

/**
 * 站内真实统计端点：POST /wp-json/hahatool/v1/track  {cid, type: views|clicks}
 * 只做计数器自增，不更新文章本身（不产生修订、不改动 modified 时间）。
 * 服务端防刷：同一 IP 对同一条目同一类型 30 分钟内只计一次（transient 去重），
 * 与无头版 /api/track 代理行为一致——主题模式直连本端点时同样受保护。
 */
add_action('rest_api_init', function () {
    register_rest_route('hahatool/v1', '/track', [
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'args' => [
            'cid' => ['required' => true, 'type' => 'integer'],
            'type' => ['required' => true, 'type' => 'string', 'enum' => ['views', 'clicks']],
        ],
        'callback' => function (WP_REST_Request $req) {
            $cid = (int) $req['cid'];
            $type = $req['type'];
            $post = get_post($cid);
            if (!$post || $post->post_status !== 'publish' || $post->post_type !== 'post') {
                return new WP_Error('not_found', 'post not found', ['status' => 404]);
            }
            // IP+条目+类型 30 分钟去重（防刷）
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $ip = trim(explode(',', $ip)[0]);
            $key = 'hh_trk_' . md5($ip . ':' . $cid . ':' . $type);
            $n = (int) get_post_meta($cid, $type, true);
            if (get_transient($key)) {
                return ['ok' => true, 'value' => $n, 'deduped' => true];
            }
            set_transient($key, 1, 30 * MINUTE_IN_SECONDS);
            $n++;
            update_post_meta($cid, $type, (string) $n);
            return ['ok' => true, 'value' => $n];
        },
    ]);
});

/**
 * 热榜数据：代理 momoyu.cc 的 /api/hot/list（公开聚合端点，需带 Referer/Origin），
 * 服务端缓存 15 分钟（transient）+ 一天兜底，规范化为 {updated, sources:[{name,key,color,items:[{title,extra,link}]}]}。
 * 供 REST 接口与热榜页模板共用。
 */
function hahatool_fetch_hot($per = 12) {
    $cache = get_transient('hahatool_hot');
    if ($cache !== false) return $cache;
    $resp = wp_remote_get('https://momoyu.cc/api/hot/list', [
        'timeout' => 12,
        'headers' => [
            'Referer'    => 'https://momoyu.cc/',
            'Origin'     => 'https://momoyu.cc',
            'User-Agent' => 'Mozilla/5.0 (compatible; HahaToolBot/1.0)',
        ],
    ]);
    if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
        $stale = get_transient('hahatool_hot_stale');
        return $stale !== false ? $stale : ['updated' => 0, 'sources' => []];
    }
    $body = json_decode(wp_remote_retrieve_body($resp), true);
    $out = ['updated' => time(), 'sources' => []];
    foreach (($body['data'] ?? []) as $s) {
        $items = [];
        foreach (array_slice($s['data'] ?? [], 0, $per) as $it) {
            $link = $it['link'] ?? '';
            if (!$link) continue;
            $items[] = [
                'title' => (string) ($it['title'] ?? ''),
                'extra' => (string) ($it['extra'] ?? ''),
                'link'  => esc_url_raw($link),
            ];
        }
        if (!$items) continue;
        $out['sources'][] = [
            'name'  => (string) ($s['name'] ?? ''),
            'key'   => (string) ($s['source_key'] ?? ''),
            'color' => sanitize_hex_color($s['icon_color'] ?? '') ?: '#7c3aed',
            'items' => $items,
        ];
    }
    set_transient('hahatool_hot', $out, 15 * MINUTE_IN_SECONDS);
    set_transient('hahatool_hot_stale', $out, DAY_IN_SECONDS);
    return $out;
}

/**
 * 把虚拟枢纽页（/tools /ranking /compare /topics /hot）加入 wp-sitemap。
 * wp-sitemap 默认只收录文章/分类法，虚拟路由需自定义 provider 补充（SEO）。
 */
add_action('wp_sitemaps_init', function ($sitemaps) {
    if (!class_exists('WP_Sitemaps_Provider')) return;
    $provider = new class extends WP_Sitemaps_Provider {
        public function __construct() {
            $this->name = 'hahatool-hubs';
            $this->object_type = 'hahatool-hubs';
        }
        public function get_url_list($page_num, $object_subtype = '') {
            $out = [];
            foreach (['tools', 'ranking', 'compare', 'topics', 'hot'] as $h) {
                $out[] = ['loc' => home_url("/$h/")];
            }
            return $out;
        }
        public function get_max_num_pages($object_subtype = '') {
            return 1;
        }
    };
    $sitemaps->registry->add_provider('hahatool-hubs', $provider);
});

/** 热榜接口：GET /wp-json/hahatool/v1/hot —— 规范化的多源热榜（服务端缓存）。 */
add_action('rest_api_init', function () {
    register_rest_route('hahatool/v1', '/hot', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => fn() => hahatool_fetch_hot(),
    ]);
});

/** REST 列表默认带出分类/标签信息时减少额外请求成本：放宽 per_page 上限到 100（WP 默认即 100，留作显式说明） */
