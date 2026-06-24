<?php
/**
 * HahaTool 主题 —— 主题设置、虚拟路由与资源加载。
 * 与 Next.js 无头前台共用 mu-plugin 注册的自定义字段，本主题不重复注册。
 */
if (!defined('ABSPATH')) exit;

define('HAHATOOL_VERSION', '1.6.122');
define('HAHATOOL_RESERVED', ['ai-news', 'ai-flash', 'ai-prompts']);

require_once get_template_directory() . '/inc/helpers.php';

/**
 * 头部精简：移除 WP 默认 emoji（本项目禁用 emoji 图标，纯冗余）、版本号暴露、RSD/WLW 等遗留标签。
 * 减小首屏体积、去掉对 s.w.org 的额外请求，并隐藏 WordPress 版本号。
 */
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
});
add_filter('emoji_svg_url', '__return_false'); // 去掉 s.w.org 的 dns-prefetch
add_filter('tiny_mce_plugins', fn($p) => is_array($p) ? array_diff($p, ['wpemoji']) : $p);

/**
 * Web App Manifest（/site.webmanifest）—— 配合 icons / theme-color / apple-touch-icon，
 * 支持安卓「添加到主屏」。用 init 直接拦截请求输出，无需重写规则与 flush。
 */
add_action('init', function () {
    if (rtrim(strtok($_SERVER['REQUEST_URI'] ?? '', '?'), '/') !== '/site.webmanifest') return;
    header('Content-Type: application/manifest+json; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    echo wp_json_encode([
        'name'             => get_bloginfo('name'),
        'short_name'       => 'HahaTool',
        'description'      => get_bloginfo('description'),
        'lang'             => 'zh-CN',
        'start_url'        => home_url('/'),
        'display'          => 'standalone',
        'background_color' => '#ffffff',
        'theme_color'      => '#7c3aed',
        'icons'            => [
            ['src' => home_url('/media/hahatool-media/brand/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => home_url('/media/hahatool-media/brand/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
});

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
    add_rewrite_rule('^hot/?$', 'index.php?hh_page=hot', 'top');
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

/** html 上的语言纠正（站点为中文，WP 默认 en-US 不符，影响读屏与 SEO）+ 主题色/明暗默认值（theme.js 首屏前覆盖） */
add_filter('language_attributes', function ($o) {
    $o = preg_replace('/lang="[^"]*"/', 'lang="zh-CN"', $o, 1);
    if (strpos($o, 'lang=') === false) $o = 'lang="zh-CN" ' . $o;
    return $o . ' data-accent="violet" data-mode="light"';
});

/** RSS：纠正 Feed 语言为 zh-CN（站点 locale 默认 en_US，与中文内容不符，影响阅读器与 SEO） */
add_filter('bloginfo_rss', fn($info, $show) => $show === 'language' ? 'zh-CN' : $info, 10, 2);

/** RSS：全站主 Feed 聚焦内容流——排除提示词（实用模板而非文章，作 Feed 条目突兀），保留资讯/快讯/新工具。分类/标签 Feed 不受影响。 */
add_action('pre_get_posts', function ($q) {
    if (!$q->is_main_query() || !$q->is_feed() || $q->is_category() || $q->is_tag()) return;
    $pc = get_category_by_slug('ai-prompts');
    if ($pc) $q->set('category__not_in', [(int) $pc->term_id]);
});

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
        'hot'       => '全网热榜',
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
    } elseif (is_tax('topic')) {
        $parts['title'] = single_term_title('', false) . ' - 专题';
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
        'hot'       => '聚合知乎、微博、B站、IT之家、虎嗅、掘金、爱范儿等全网站点热榜，每 5 分钟更新，实时追踪科技与社会热点。',
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
    if (is_tax('topic')) {
        $t = get_queried_object();
        return $t->description ?: ($t->name . ' —— 精选 AI 工具专题合集，按场景与主题归类。');
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
        // OG 大图：仅用资讯封面 / 上传截图。工具 Logo 是小图标（无论第三方 favicon 还是自托管 128px），
        // 作 summary_large_image 都会被裁切糊化，故不纳入 og:image —— 无大图时回退自有品牌卡（见下 og-default）。
        $image = hh_meta($id, 'cover') ?: hh_meta($id, 'screenshot') ?: '';
    } elseif (is_tax('topic')) {
        // 专题归档：用专题封面作 OG 图
        $image = get_term_meta(get_queried_object_id(), 'topic_cover', true);
    }
    // 其余页面（首页/工具库/排行/热榜/搜索等）回退到默认品牌社交卡，确保分享有预览图
    if (!$image) $image = home_url('/media/hahatool-media/brand/og-default.png');
    // 频道页规范链接指向清爽 URL（/prompts /flash /news），避免与 /category/ 重复内容
    if (is_category()) {
        $cslug = get_queried_object()->slug ?? '';
        $clean = ['ai-prompts' => '/prompts/', 'ai-flash' => '/flash/', 'ai-news' => '/news/'];
        if (isset($clean[$cslug])) {
            echo "\n<link rel=\"canonical\" href=\"" . esc_url(home_url($clean[$cslug])) . "\">\n";
        }
    }
    // self-canonical：首页 / 虚拟枢纽页(/tools /ranking /compare /topics /hot 等) / 专题归档 —— WP 默认不输出，补齐避免重复内容
    $hh_canonical = '';
    if ($hh_vp = get_query_var('hh_page')) { // 必须先判虚拟枢纽页：其主查询无文章，is_front_page() 会误判为 true
        $hh_canonical = home_url('/' . $hh_vp . '/');
    } elseif (is_front_page()) {
        $hh_canonical = home_url('/');
    } elseif (is_tax('topic')) {
        $tl = get_term_link(get_queried_object());
        if (!is_wp_error($tl)) $hh_canonical = $tl;
    }
    if ($hh_canonical && !is_paged()) {
        echo "\n<link rel=\"canonical\" href=\"" . esc_url($hh_canonical) . "\">\n";
    }
    echo "\n<meta name=\"description\" content=\"" . esc_attr($desc) . "\">\n";
    echo '<meta property="og:type" content="' . esc_attr($type) . "\">\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . "\">\n";
    echo '<meta property="og:locale" content="zh_CN">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . "\">\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . "\">\n";
    echo '<meta property="og:url" content="' . esc_url(home_url(add_query_arg(null, null))) . "\">\n";
    echo '<meta name="twitter:card" content="' . ($image ? 'summary_large_image' : 'summary') . "\">\n";
    if ($image) {
        // 社媒 og:image 必须是位图：站内 SVG 品牌封面替换为同名 PNG（FB/Twitter/微信/微博等不渲染 SVG og:image）。
        // 页面 <img> 仍用 SVG（清晰、体积小），仅社媒预览改用 PNG。
        $is_media = strpos($image, '/media/hahatool-media/') !== false;
        $og_image = hahatool_raster($image);
        echo '<meta property="og:image" content="' . esc_url($og_image) . "\">\n";
        echo '<meta name="twitter:image" content="' . esc_url($og_image) . "\">\n";
        if ($is_media) { // 品牌封面固定 1200×600，提示尺寸利于社媒即时渲染
            echo '<meta property="og:image:width" content="1200">' . "\n";
            echo '<meta property="og:image:height" content="600">' . "\n";
        }
    }
    // 文章页补充 OpenGraph article:* 元信息（资讯/提示词/工具详情）—— 利于社媒与新闻聚合识别
    if (is_singular('post')) {
        $pid = get_queried_object_id();
        echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c', $pid)) . "\">\n";
        echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c', $pid)) . "\">\n";
        echo '<meta property="article:publisher" content="' . esc_url(home_url('/')) . "\">\n";
        $cats = get_the_category($pid);
        if (!empty($cats)) echo '<meta property="article:section" content="' . esc_attr($cats[0]->name) . "\">\n";
        foreach ((get_the_tags($pid) ?: []) as $tg) {
            echo '<meta property="article:tag" content="' . esc_attr($tg->name) . "\">\n";
        }
    }
}, 5);

/** 首页站点级结构化数据：WebSite（含站内搜索 SearchAction）+ Organization —— SEO/Sitelinks 搜索框 */
add_action('wp_head', function () {
    if (!is_front_page() && !is_home()) return;
    $home = home_url('/');
    $name = get_bloginfo('name');
    $website = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $name,
        'url' => $home,
        'inLanguage' => 'zh-CN',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => ['@type' => 'EntryPoint', 'urlTemplate' => $home . '?s={search_term_string}'],
            'query-input' => 'required name=search_term_string',
        ],
    ];
    $org = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $name,
        'url' => $home,
        'logo' => hahatool_logo_url(),
        'description' => get_bloginfo('description'),
    ];
    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($website, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    echo '<script type="application/ld+json">' . wp_json_encode($org, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}, 6);

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

/**
 * 评论反垃圾（无第三方依赖、无验证码摩擦）：蜜罐字段 + 最短填写时间。
 * 机器人会填充隐藏蜜罐字段或秒级提交，据此拦截。
 */
function hahatool_comment_spam_fields() {
    echo '<p class="hh-hp" style="position:absolute!important;left:-9999px!important;height:0;overflow:hidden" aria-hidden="true">'
        . '<label>如果你是人类请留空<input type="text" name="hh_hp" value="" autocomplete="off" tabindex="-1"></label></p>';
    echo '<input type="hidden" name="hh_ts" value="' . esc_attr(time()) . '">';
}
add_action('comment_form_after_fields', 'hahatool_comment_spam_fields');
add_action('comment_form_logged_in_after', 'hahatool_comment_spam_fields');

add_filter('preprocess_comment', function ($commentdata) {
    if (is_user_logged_in() && current_user_can('moderate_comments')) return $commentdata; // 管理员豁免
    if (!empty($_POST['hh_hp'])) {                       // 蜜罐被填 → 机器人
        wp_die('提交未通过校验。', '评论被拦截', ['response' => 403, 'back_link' => true]);
    }
    $ts = isset($_POST['hh_ts']) ? (int) $_POST['hh_ts'] : 0;
    if ($ts > 0 && (time() - $ts) < 3) {                 // 秒级提交 → 机器人
        wp_die('提交太快了，请稍等几秒再发表。', '评论被拦截', ['response' => 403, 'back_link' => true]);
    }
    return $commentdata;
});
