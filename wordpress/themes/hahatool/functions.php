<?php
/**
 * HahaTool 主题 —— 主题设置、虚拟路由与资源加载。
 * 与 Next.js 无头前台共用 mu-plugin 注册的自定义字段，本主题不重复注册。
 */
if (!defined('ABSPATH')) exit;

define('HAHATOOL_VERSION', '1.0.0');
define('HAHATOOL_RESERVED', ['ai-news', 'ai-flash', 'ai-prompts']);

require_once get_template_directory() . '/inc/helpers.php';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['comment-list', 'comment-form', 'search-form']);
    add_theme_support('custom-logo');
    register_nav_menus(['primary' => '主导航']);
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('hahatool', get_stylesheet_uri(), [], HAHATOOL_VERSION);
    wp_enqueue_style('hahatool-font', 'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&display=swap', [], null);
    wp_enqueue_script('hahatool', get_template_directory_uri() . '/assets/theme.js', [], HAHATOOL_VERSION, true);
    wp_localize_script('hahatool', 'HAHATOOL', [
        'restUrl' => esc_url_raw(rest_url()),
        'nonce'   => wp_create_nonce('wp_rest'),
        'home'    => esc_url_raw(home_url('/')),
    ]);
});

/* ---------------- 虚拟页面路由 ---------------- */
function hahatool_routes() {
    add_rewrite_rule('^tools/?$', 'index.php?hh_page=tools', 'top');
    add_rewrite_rule('^ranking/?$', 'index.php?hh_page=ranking', 'top');
    add_rewrite_rule('^submit/?$', 'index.php?hh_page=submit', 'top');
    add_rewrite_rule('^compare/?$', 'index.php?hh_page=compare', 'top');
    add_rewrite_rule('^favorites/?$', 'index.php?hh_page=favorites', 'top');
}
add_action('init', 'hahatool_routes');
add_filter('query_vars', fn($v) => array_merge($v, ['hh_page']));
add_filter('template_include', function ($tpl) {
    $p = get_query_var('hh_page');
    if ($p) {
        $f = get_template_directory() . "/template-{$p}.php";
        if (file_exists($f)) {
            status_header(200);
            return $f;
        }
    }
    return $tpl;
});
/** 激活主题时注册路由并刷新一次伪静态 */
add_action('after_switch_theme', function () {
    hahatool_routes();
    flush_rewrite_rules();
});

/** 分类/标签/搜索归档每页 24 条 */
add_action('pre_get_posts', function ($q) {
    if (is_admin() || !$q->is_main_query()) return;
    if ($q->is_category() || $q->is_tag() || $q->is_search()) {
        $q->set('posts_per_page', 24);
    }
});

/** html 上的主题色/明暗默认值（theme.js 会在首屏前覆盖） */
add_filter('language_attributes', fn($o) => $o . ' data-accent="violet" data-mode="light"');

/** 工具详情页：输出站内浏览自增信号（theme.js 调用 REST /hahatool/v1/track） */
add_action('wp_footer', function () {
    if (is_singular('post') && hahatool_is_tool(get_queried_object_id())) {
        echo '<script>window.__HAHATOOL_TRACK__=' . (int)get_queried_object_id() . ';</script>';
    }
});

/** 自定义评论渲染 */
function hahatool_comment($comment, $args, $depth) {
    ?>
    <li <?php comment_class('comment'); ?> id="comment-<?php comment_ID(); ?>">
        <div class="who"><b><?php comment_author(); ?></b> · <?php echo esc_html(get_comment_date()); ?></div>
        <div class="txt"><?php comment_text(); ?></div>
    <?php
}
