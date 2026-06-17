<?php
/**
 * HahaTool 主题 —— 主题设置、虚拟路由与资源加载。
 * 与 Next.js 无头前台共用 mu-plugin 注册的自定义字段，本主题不重复注册。
 */
if (!defined('ABSPATH')) exit;

define('HAHATOOL_VERSION', '1.6.32');
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
    // 字体自托管在 style.css 的 @font-face（无第三方请求），版本号随主题更新刷新缓存
    wp_enqueue_style('hahatool', get_stylesheet_uri(), [], HAHATOOL_VERSION);
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
    add_rewrite_rule('^topics/?$', 'index.php?hh_page=topics', 'top');
    // 频道清爽 URL 别名，与无头版一致（/prompts /flash /news → 对应分类归档）
    add_rewrite_rule('^prompts/?$', 'index.php?category_name=ai-prompts', 'top');
    add_rewrite_rule('^flash/?$', 'index.php?category_name=ai-flash', 'top');
    add_rewrite_rule('^news/?$', 'index.php?category_name=ai-news', 'top');
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

/** 分类/标签归档每页 24 条；搜索一次取 60 条（与无头版搜索抓取量对齐） */
add_action('pre_get_posts', function ($q) {
    if (is_admin() || !$q->is_main_query()) return;
    if ($q->is_category() || $q->is_tag()) {
        $q->set('posts_per_page', 24);
    } elseif ($q->is_search()) {
        $q->set('posts_per_page', 60);
    }
});

/** html 上的主题色/明暗默认值（theme.js 会在首屏前覆盖） */
add_filter('language_attributes', fn($o) => $o . ' data-accent="violet" data-mode="light"');

/**
 * SEO：为虚拟路由页与工具详情设置规范的文档标题，对齐无头版。
 * 虚拟路由（/tools、/ranking 等）WordPress 默认取不到标题，会回退站名。
 */
add_filter('document_title_parts', function ($parts) {
    $vmap = [
        'tools'     => '全部工具',
        'ranking'   => 'AI 工具排行榜',
        'compare'   => 'AI 工具 PK 对比',
        'submit'    => '提交工具',
        'favorites' => '我的收藏',
        'topics'    => '专题合集',
    ];
    $vp = get_query_var('hh_page');
    if ($vp && isset($vmap[$vp])) {
        $parts['title'] = $vmap[$vp];
    } elseif (is_singular('post')) {
        $id = get_queried_object_id();
        // 工具详情：标题 + 一句话简介，对齐无头版
        $tagline = hh_meta($id, 'tagline');
        if (hahatool_is_tool($id) && $tagline) {
            $parts['title'] = get_the_title($id) . ' - ' . $tagline;
        }
    }
    return $parts;
}, 20);

/** 计算各页面的 meta 描述（对齐无头版文案） */
function hahatool_meta_description() {
    $vmap = [
        'tools'     => '浏览 HahaTool 收录的全部 AI 工具，支持按分类、定价筛选与多维排序。',
        'ranking'   => '按月访问量、收藏数、增长速度排序的 AI 工具排行榜。',
        'compare'   => '任选两款 AI 工具，能力雷达、流量、评分、定价一屏对比。',
        'submit'    => '向 HahaTool 提交你的 AI 工具，在线表单免费收录。',
        'favorites' => '我的 AI 工具收藏夹（本机保存，无需登录）。',
        'topics'    => '精心策划的 AI 工具专题合集，按场景与主题归类，快速找到同类好工具。',
    ];
    $vp = get_query_var('hh_page');
    if ($vp && isset($vmap[$vp])) return $vmap[$vp];
    if (is_singular('post')) {
        $id = get_queried_object_id();
        if ($tagline = hh_meta($id, 'tagline')) return $tagline;          // 工具
        if ($prompt = hh_meta($id, 'prompt')) return mb_substr(trim($prompt), 0, 80) . '…'; // 提示词
        $excerpt = wp_strip_all_tags(get_the_excerpt($id));               // 资讯/快讯
        if ($excerpt) return mb_substr($excerpt, 0, 110);
    }
    if (is_category()) {
        $t = get_queried_object();
        if ($t->slug === 'ai-prompts') return '高质量中文 AI 提示词：写作、编程、营销、办公、学习，一键复制即用。';
        if ($t->slug === 'ai-news') return 'AI 行业新闻、趋势解读与工具动态。';
        if ($t->slug === 'ai-flash') return 'AI 行业即时短讯，按时间线滚动更新。';
        return $t->description ?: ($t->name . ' 分类下的优质 AI 工具，附流量与评分数据。');
    }
    if (is_tag()) {
        $t = get_queried_object();
        return $t->name . ' 相关 AI 工具 —— HahaTool 收录整理。';
    }
    if (is_search()) return '搜索 AI 工具、提示词与资讯。';
    return get_bloginfo('description');
}

/** SEO：meta description + OpenGraph / Twitter 卡片 */
add_action('wp_head', function () {
    $title = wp_get_document_title();
    $desc = hahatool_meta_description();
    $image = '';
    $type = 'website';
    if (is_singular('post')) {
        $id = get_queried_object_id();
        $type = 'article';
        $image = hh_meta($id, 'cover') ?: hh_meta($id, 'screenshot');
        if (!$image && hh_meta($id, 'url')) $image = 'https://s0.wp.com/mshots/v1/' . rawurlencode(hh_meta($id, 'url')) . '?w=1200';
    }
    // 频道页规范链接指向清爽 URL（/prompts /flash /news），避免与 /category/ 重复内容
    if (is_category()) {
        $cslug = get_queried_object()->slug ?? '';
        $clean = ['ai-prompts' => '/prompts/', 'ai-flash' => '/flash/', 'ai-news' => '/news/'];
        if (isset($clean[$cslug])) {
            echo "\n<link rel=\"canonical\" href=\"" . esc_url(home_url($clean[$cslug])) . "\">\n";
        }
    }
    echo "\n<meta name=\"description\" content=\"" . esc_attr($desc) . "\">\n";
    echo '<meta property="og:type" content="' . esc_attr($type) . "\">\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . "\">\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . "\">\n";
    echo '<meta property="og:url" content="' . esc_url(home_url(add_query_arg(null, null))) . "\">\n";
    echo '<meta name="twitter:card" content="' . ($image ? 'summary_large_image' : 'summary') . "\">\n";
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . "\">\n";
        echo '<meta name="twitter:image" content="' . esc_url($image) . "\">\n";
    }
}, 5);

/** 工具/资讯详情页：输出站内浏览自增信号（theme.js 调用 REST /hahatool/v1/track） */
add_action('wp_footer', function () {
    if (!is_singular('post')) return;
    $id = get_queried_object_id();
    // 工具与资讯文章都累计 views（资讯用于「热门资讯榜」排序）
    $is_news = has_category('ai-news', $id);
    if (hahatool_is_tool($id) || $is_news) {
        echo '<script>window.__HAHATOOL_TRACK__=' . (int)$id . ';</script>';
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
