<?php
/**
 * HahaTool 数据辅助函数。
 * 「工具」= 带 url 字段、且不在保留分类(ai-news/ai-flash/ai-prompts)的文章，
 * 与 Next.js 前台 lib/api.ts 的分类逻辑保持一致。
 */
if (!defined('ABSPATH')) exit;

/**
 * 内联 SVG 图标（lucide 风格，currentColor 描边），替代 emoji 作功能图标。
 * 用法：echo hh_icon('bookmark', 14);
 */
function hh_icon($name, $size = 14, $stroke = 2) {
    $paths = [
        'bookmark' => '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
        'trending' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'trending-down' => '<polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>',
        'eye'      => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'external' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
        'heart'    => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 1 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z"/>',
        'arrow-up-right' => '<line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>',
        'chevron-left' => '<polyline points="15 18 9 12 15 6"/>',
        'chevron-right' => '<polyline points="9 18 15 12 9 6"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'search'   => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'zap'      => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'chart'    => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'globe'    => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'help'     => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'message'  => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/>',
        'swords'   => '<polyline points="14.5 17.5 3 6 3 3 6 3 17.5 14.5"/><line x1="13" y1="19" x2="19" y2="13"/><line x1="16" y1="16" x2="20" y2="20"/><line x1="19" y1="21" x2="21" y2="19"/>',
        'palette'  => '<circle cx="13.5" cy="6.5" r="1.5"/><circle cx="17.5" cy="10.5" r="1.5"/><circle cx="8.5" cy="7.5" r="1.5"/><circle cx="6.5" cy="12.5" r="1.5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.9 0 1.5-.7 1.5-1.5 0-.4-.2-.8-.4-1-.3-.3-.4-.6-.4-1 0-.8.7-1.5 1.5-1.5H16c3.3 0 6-2.7 6-6 0-4.9-4.5-9-10-9z"/>',
        'wand'     => '<path d="M15 4V2"/><path d="M15 16v-2"/><path d="M8 9h2"/><path d="M20 9h2"/><path d="M17.8 11.8 19 13"/><path d="M15 9h0"/><path d="M17.8 6.2 19 5"/><path d="M3 21l9-9"/><path d="M12.2 6.2 11 5"/>',
        'megaphone'=> '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
        'newspaper'=> '<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8z"/>',
        'sparkles' => '<path d="M12 3l1.9 5.8a2 2 0 0 0 1.3 1.3L21 12l-5.8 1.9a2 2 0 0 0-1.3 1.3L12 21l-1.9-5.8a2 2 0 0 0-1.3-1.3L3 12l5.8-1.9a2 2 0 0 0 1.3-1.3z"/>',
        'flame'    => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
        'menu'     => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'check'    => '<polyline points="20 6 9 17 4 12"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'alert'    => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
    ];
    $p = $paths[$name] ?? '';
    if (!$p) return '';
    $s = (int) $size;
    return '<svg width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $stroke . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:inline-block;vertical-align:-.15em;flex-shrink:0">' . $p . '</svg>';
}

/** 分类链接（按 slug，缺失回退首页） */
function get_category_link_safe($slug) {
    $t = get_category_by_slug($slug);
    return $t ? get_category_link($t) : home_url('/');
}

/** 读 meta 简写 */
function hh_meta($id, $key, $default = '') {
    $v = get_post_meta($id, $key, true);
    return $v === '' || $v === null ? $default : $v;
}

/** 是否为工具 */
function hahatool_is_tool($id) {
    if (!hh_meta($id, 'url')) return false;
    foreach (wp_get_post_categories($id) as $tid) {
        $term = get_term($tid);
        if ($term && in_array($term->slug, HAHATOOL_RESERVED, true)) return false;
    }
    return true;
}

/** 保留分类的 term_id 列表（用于查询排除） */
function hahatool_reserved_ids() {
    static $ids = null;
    if ($ids !== null) return $ids;
    $ids = [];
    foreach (HAHATOOL_RESERVED as $slug) {
        $t = get_category_by_slug($slug);
        if ($t) $ids[] = $t->term_id;
    }
    return $ids;
}

/**
 * 工具查询。$extra 可覆盖/追加 WP_Query 参数。
 * 默认排除保留分类、要求 url 字段存在。
 */
function hahatool_tools(array $extra = []) {
    $args = array_merge([
        'post_type'      => 'post',
        'posts_per_page' => 100,
        'category__not_in' => hahatool_reserved_ids(),
        'meta_query'     => [['key' => 'url', 'compare' => 'EXISTS'], ['key' => 'url', 'value' => '', 'compare' => '!=']],
        'no_found_rows'  => true,
        'ignore_sticky_posts' => true,
    ], $extra);
    return new WP_Query($args);
}

/** 取某保留分类下的文章（资讯/快讯/提示词） */
function hahatool_channel($slug, $limit = 20) {
    return new WP_Query([
        'post_type' => 'post',
        'category_name' => $slug,
        'posts_per_page' => $limit,
        'no_found_rows' => true,
    ]);
}

/** 数字格式化：123456 -> 12.3万，4.6e8 -> 4.6亿 */
function hahatool_count($n) {
    $n = (float)$n;
    if ($n <= 0) return '—';
    if ($n >= 1e8) return rtrim(rtrim(number_format($n / 1e8, 1), '0'), '.') . '亿';
    if ($n >= 1e4) return rtrim(rtrim(number_format($n / 1e4, 1), '0'), '.') . '万';
    return (string)(int)$n;
}

/** 提取域名 */
function hahatool_domain($url) {
    $h = parse_url($url, PHP_URL_HOST);
    return $h ? preg_replace('/^www\./', '', $h) : $url;
}

/** Logo：有图用图，无图用首字母渐变块 */
function hahatool_logo($id, $size = 48) {
    $logo = hh_meta($id, 'logo');
    $title = get_the_title($id);
    $s = (int)$size;
    if ($logo) {
        return '<img class="logo" loading="lazy" width="' . $s . '" height="' . $s . '" src="' . esc_url($logo) . '" alt="' . esc_attr($title) . ' Logo" style="width:' . $s . 'px;height:' . $s . 'px" onerror="this.outerHTML=window.__hhFallback(this)">';
    }
    $ch = mb_substr($title, 0, 1);
    return '<span class="logo logo-fallback" style="width:' . $s . 'px;height:' . $s . 'px;font-size:' . round($s * .42) . 'px">' . esc_html(strtoupper($ch)) . '</span>';
}

/** 收藏按钮（localStorage，前端 theme.js 接管状态） */
function hahatool_fav_button($id, $large = false) {
    if ($large) {
        return '<button type="button" class="fav-btn fav-lg" data-fav="' . (int)$id . '" aria-pressed="false" aria-label="收藏"><span class="heart">' . hh_icon('heart', 16) . '</span><span class="fav-txt">收藏</span></button>';
    }
    return '<button type="button" class="fav-btn" data-fav="' . (int)$id . '" aria-pressed="false" aria-label="收藏"><span class="heart">' . hh_icon('heart', 16) . '</span></button>';
}

/** 定价徽章 */
function hahatool_pricing_badge($pricing) {
    $map = ['免费' => 'badge-free', '免费增值' => 'badge-freemium', '付费' => 'badge-paid'];
    if (!$pricing || !isset($map[$pricing])) return '';
    return '<span class="badge ' . $map[$pricing] . '">' . esc_html($pricing) . '</span>';
}

/** 增长徽章 */
function hahatool_growth_badge($g) {
    $g = (float)$g;
    if (!$g) return '';
    $up = $g > 0;
    // 用 lucide 风格 SVG 箭头（与无头版 GrowthBadge 一致，符合「图标用 SVG 不用字符」标准）
    $icon = hh_icon($up ? 'trending' : 'trending-down', 11);
    return '<span class="badge ' . ($up ? 'badge-up' : 'badge-down') . '">' . $icon . ($up ? '+' : '') . $g . '%</span>';
}

/** 星级（双层填充支持小数） */
function hahatool_stars($rating, $show = true) {
    $r = (float)$rating;
    if (!$r) return '';
    $pct = min(100, $r / 5 * 100);
    // 五角星用 SVG（与无头版 lucide Star 一致，符合「图标用 SVG 不用字符」标准）
    $star = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="display:block;flex-shrink:0"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
    $five = str_repeat($star, 5);
    $out = '<span class="stars"><span class="bar"><span class="bg">' . $five . '</span>';
    $out .= '<span class="fg" style="width:' . $pct . '%">' . $five . '</span></span>';
    if ($show) $out .= '<b>' . number_format($r, 1) . '</b>';
    return $out . '</span>';
}

/** 解析 scores 字段 -> [[label,value],...] */
function hahatool_scores($id) {
    $raw = hh_meta($id, 'scores');
    if (!$raw) return [];
    $out = [];
    foreach (explode(',', $raw) as $pair) {
        $p = explode(':', $pair);
        if (count($p) === 2 && (float)$p[1] > 0) $out[] = [trim($p[0]), (float)$p[1]];
    }
    return $out;
}

/** 能力雷达 SVG（单系列） */
function hahatool_radar_svg($scores, $size = 280, $color = 'var(--brand-600)') {
    $n = count($scores);
    if ($n < 3) return '';
    $cx = $size / 2; $cy = $size / 2 + 6; $r = $size / 2 - 44; $MAX = 10;
    $pt = function ($i, $radius) use ($cx, $cy, $n) {
        $a = 2 * M_PI * $i / $n - M_PI / 2;
        return [$cx + $radius * cos($a), $cy + $radius * sin($a)];
    };
    $poly = function ($vals) use ($pt, $r, $MAX) {
        $s = [];
        foreach ($vals as $i => $v) { [$x, $y] = $pt($i, min($v, $MAX) / $MAX * $r); $s[] = round($x, 1) . ',' . round($y, 1); }
        return implode(' ', $s);
    };
    $svg = '<svg viewBox="0 0 ' . $size . ' ' . ($size + 10) . '" width="100%" role="img" aria-label="能力雷达图">';
    foreach ([0.25, 0.5, 0.75, 1] as $f) {
        $svg .= '<polygon points="' . $poly(array_fill(0, $n, $MAX * $f)) . '" fill="' . ($f == 1 ? 'color-mix(in srgb,var(--brand-500) 5%,transparent)' : 'none') . '" stroke="var(--border)" stroke-width="1"/>';
    }
    foreach ($scores as $i => [$label, $v]) {
        [$x2, $y2] = $pt($i, $r); [$lx, $ly] = $pt($i, $r + 20);
        $svg .= '<line x1="' . $cx . '" y1="' . $cy . '" x2="' . round($x2, 1) . '" y2="' . round($y2, 1) . '" stroke="var(--border)"/>';
        $svg .= '<text x="' . round($lx, 1) . '" y="' . round($ly, 1) . '" text-anchor="middle" dominant-baseline="middle" font-size="12" fill="var(--text-3)">' . esc_html($label) . '</text>';
    }
    $vals = array_map(fn($s) => $s[1], $scores);
    $svg .= '<polygon points="' . $poly($vals) . '" fill="' . $color . '" fill-opacity="0.18" stroke="' . $color . '" stroke-width="2" stroke-linejoin="round"/>';
    foreach ($vals as $i => $v) { [$px, $py] = $pt($i, min($v, $MAX) / $MAX * $r); $svg .= '<circle cx="' . round($px, 1) . '" cy="' . round($py, 1) . '" r="3" fill="' . $color . '"/>'; }
    return $svg . '</svg>';
}

/** 运营位栏位中文名 */
function hahatool_slot_label($slot) {
    $m = ['home-mid' => '首页中部横幅', 'ranking-top' => '榜单顶部横幅', 'detail-side' => '详情页侧栏', 'detail-bottom' => '详情页底部横幅', 'tools-inline' => '工具库信息流', 'news-inline' => '资讯信息流'];
    return $m[$slot] ?? $slot;
}

/** 渲染运营位：有则推广卡，无则「虚位以待」占位 */
function hahatool_render_promo($slot, $posts) {
    if ($posts) {
        $p = $posts[0]; $id = $p->ID;
        ?>
        <div class="promo">
            <span class="tag" style="display:inline-flex;align-items:center;gap:4px"><?php echo hh_icon('megaphone', 11); ?>推广</span>
            <?php echo hahatool_logo($id, 56); ?>
            <div><h3><?php echo esc_html(get_the_title($id)); ?></h3><p><?php echo esc_html(hh_meta($id, 'tagline')); ?></p></div>
            <div class="promo-actions">
                <a class="btn" href="<?php echo esc_url(hh_meta($id, 'url')); ?>" target="_blank" rel="noopener nofollow">立即体验<?php echo hh_icon('arrow-right', 15); ?></a>
                <a class="btn btn-ghost" href="<?php echo esc_url(get_permalink($id)); ?>">详情</a>
            </div>
        </div>
        <?php
    } else {
        ?>
        <a class="ad-empty" href="<?php echo esc_url(home_url('/submit/')); ?>"><?php echo hh_icon('megaphone', 16); ?> 广告位 <b class="display">AD</b> · <?php echo esc_html(hahatool_slot_label($slot)); ?> · 虚位以待 · 联系投放 →</a>
        <?php
    }
}

/** 提示词卡 */
function hahatool_prompt_card($post) {
    $id = $post->ID;
    $prompt = hh_meta($id, 'prompt');
    ?>
    <div class="card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
            <h3><a href="<?php echo esc_url(get_permalink($id)); ?>"><?php echo esc_html(get_the_title($id)); ?></a></h3>
            <button class="copy-btn" data-copy="<?php echo esc_attr($prompt); ?>">复制</button>
        </div>
        <div class="meta-row" style="margin-top:8px">
            <span class="chip chip-brand"><?php echo esc_html(hh_meta($id, 'prompt_scene', '其他')); ?></span>
            <span class="chip"><?php echo esc_html(hh_meta($id, 'prompt_model', '通用')); ?></span>
            <span class="spacer tnum" style="margin-left:auto;display:inline-flex;align-items:center;gap:4px"><?php echo hh_icon('bookmark', 12); ?><?php echo hahatool_count(hh_meta($id, 'likes')); ?></span>
        </div>
        <pre class="prompt-pre"><?php echo esc_html($prompt); ?></pre>
    </div>
    <?php
}

/** 快讯时间线（按天分组渲染） */
function hahatool_flash_timeline($posts, $compact = false) {
    if (!$posts) return;
    $groups = [];
    foreach ($posts as $p) {
        $day = get_the_date('n月j日', $p);
        $groups[$day][] = $p;
    }
    echo '<div style="display:flex;flex-direction:column;gap:22px">';
    foreach ($groups as $day => $items) {
        echo '<section><h3 style="font-size:14px"><span style="background:#111827;color:#fff;padding:4px 10px;border-radius:8px" class="display">' . esc_html($day) . '</span></h3>';
        echo '<div class="flash" style="margin-top:12px">';
        foreach ($items as $p) {
            echo '<div class="it"><time>' . esc_html(get_the_date('H:i', $p)) . '</time><a href="' . esc_url(get_permalink($p)) . '"><p>' . esc_html(get_the_title($p)) . '</p>';
            if (!$compact) {
                $d = wp_trim_words(wp_strip_all_tags($p->post_content), 36);
                if ($d) echo '<span class="muted" style="font-size:12px;font-weight:400;display:block;margin-top:2px">' . esc_html($d) . '</span>';
            }
            echo '</a></div>';
        }
        echo '</div></section>';
    }
    echo '</div>';
}

/** 热门工具列表（按月访问量） */
function hahatool_hot_tools($limit = 5) {
    $t = hahatool_tools(['posts_per_page' => 300])->posts;
    usort($t, fn($a, $b) => (float)hh_meta($b->ID, 'monthly_visits') - (float)hh_meta($a->ID, 'monthly_visits'));
    return array_slice($t, 0, $limit);
}

/** 双系列雷达（PK 用）。轴取并集，按 A 的标签顺序。 */
function hahatool_radar_dual($a, $b, $size = 300, $ca = 'var(--brand-600)', $cb = '#f59e0b') {
    $labels = array_map(fn($s) => $s[0], count($a) >= 3 ? $a : $b);
    $n = count($labels);
    if ($n < 3) return '';
    $cx = $size / 2; $cy = $size / 2 + 6; $r = $size / 2 - 44; $MAX = 10;
    $val = function ($set, $label) { foreach ($set as $s) if ($s[0] === $label) return $s[1]; return 0; };
    $pt = function ($i, $radius) use ($cx, $cy, $n) { $ang = 2 * M_PI * $i / $n - M_PI / 2; return [$cx + $radius * cos($ang), $cy + $radius * sin($ang)]; };
    $poly = function ($set) use ($labels, $val, $pt, $r, $MAX) {
        $pts = [];
        foreach ($labels as $i => $lb) { [$x, $y] = $pt($i, min($val($set, $lb), $MAX) / $MAX * $r); $pts[] = round($x, 1) . ',' . round($y, 1); }
        return implode(' ', $pts);
    };
    $svg = '<svg viewBox="0 0 ' . $size . ' ' . ($size + 10) . '" width="100%" role="img" aria-label="能力雷达对比">';
    foreach ([0.25, 0.5, 0.75, 1] as $f) { $svg .= '<polygon points="' . $poly(array_map(fn($l) => [$l, $MAX * $f], $labels)) . '" fill="none" stroke="var(--border)"/>'; }
    foreach ($labels as $i => $lb) { [$x2, $y2] = $pt($i, $r); [$lx, $ly] = $pt($i, $r + 20);
        $svg .= '<line x1="' . $cx . '" y1="' . $cy . '" x2="' . round($x2, 1) . '" y2="' . round($y2, 1) . '" stroke="var(--border)"/>';
        $svg .= '<text x="' . round($lx, 1) . '" y="' . round($ly, 1) . '" text-anchor="middle" dominant-baseline="middle" font-size="11" fill="var(--text-3)">' . esc_html($lb) . '</text>'; }
    foreach ([[$a, $ca], [$b, $cb]] as [$set, $col]) {
        if (count($set) < 3) continue;
        $svg .= '<polygon points="' . $poly($set) . '" fill="' . $col . '" fill-opacity="0.16" stroke="' . $col . '" stroke-width="2" stroke-linejoin="round"/>';
    }
    return $svg . '</svg>';
}

/** 取运营位工具（promo 字段） */
function hahatool_promo($slot, $limit = 1, $exclude = 0) {
    $q = hahatool_tools([
        'posts_per_page' => $limit,
        'meta_query' => [['key' => 'promo', 'value' => $slot]],
        'post__not_in' => $exclude ? [$exclude] : [],
    ]);
    return $q->posts;
}

/** 渲染工具卡（传入 post 对象，需在 the_post/setup 后） */
function hahatool_tool_card($post, $rank = null) {
    $id = $post->ID;
    setup_postdata($post);
    $url = hh_meta($id, 'url');
    $cats = get_the_category($id);
    $cat = null;
    foreach ($cats as $c) { if (!in_array($c->slug, HAHATOOL_RESERVED, true)) { $cat = $c; break; } }
    ?>
    <div class="card">
        <a class="stretched" href="<?php the_permalink($id); ?>" aria-label="查看 <?php echo esc_attr(get_the_title($id)); ?> 详情"></a>
        <?php if ($rank !== null): ?><span class="badge" style="position:absolute;left:16px;top:-8px;z-index:2;background:#f97316;color:#fff;box-shadow:var(--shadow-lg)"><?php echo hh_icon('flame', 11); ?><?php echo esc_html($rank); ?></span><?php endif; ?>
        <div class="card-top">
            <?php echo hahatool_logo($id, 48); ?>
            <div style="min-width:0;flex:1">
                <div style="display:flex;align-items:center;gap:8px">
                    <h3><?php echo esc_html(get_the_title($id)); ?></h3>
                    <?php echo hahatool_growth_badge(hh_meta($id, 'growth')); ?>
                </div>
                <div class="meta-row">
                    <?php if ($cat): ?><a class="chip" style="position:relative;z-index:1" href="<?php echo esc_url(get_category_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a><?php endif; ?>
                    <?php echo hahatool_pricing_badge(hh_meta($id, 'pricing')); ?>
                </div>
            </div>
        </div>
        <p class="tagline"><?php echo esc_html(hh_meta($id, 'tagline', '——')); ?></p>
        <div class="card-foot">
            <?php echo hahatool_stars(hh_meta($id, 'rating')); ?>
            <span class="tnum" style="display:inline-flex;align-items:center;gap:4px"><?php echo hh_icon('bookmark', 13); ?><?php echo hahatool_count(hh_meta($id, 'likes')); ?></span>
            <span class="tnum" style="display:inline-flex;align-items:center;gap:4px"><?php echo hh_icon('trending', 13); ?><?php echo hahatool_count(hh_meta($id, 'monthly_visits')); ?></span>
            <span class="spacer" style="position:relative;z-index:1;display:inline-flex;gap:2px"><?php echo hahatool_fav_button($id); ?><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener nofollow" data-track-click="<?php echo (int)$id; ?>" aria-label="访问官网" title="访问官网" style="color:var(--text-3);padding:4px;display:inline-flex"><?php echo hh_icon('arrow-up-right', 16); ?></a></span>
        </div>
    </div>
    <?php
}
