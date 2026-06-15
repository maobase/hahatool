<?php
/** /ranking 排行榜：流量/收藏/增长/人气/新品 + 领奖台 */
if (!defined('ABSPATH')) exit;
get_header();
$by = isset($_GET['by']) ? sanitize_key($_GET['by']) : 'visits';
$cat = isset($_GET['cat']) ? sanitize_title($_GET['cat']) : '';
$tabs = ['visits' => ['流量榜', '按月访问量排序'], 'likes' => ['收藏榜', '按用户收藏数排序'], 'growth' => ['增长榜', '按月环比增速排序'], 'hot' => ['人气榜', '按本站真实浏览量排序（站内统计）'], 'new' => ['新品榜', '按收录时间排序']];

$tools = hahatool_tools(['posts_per_page' => 300])->posts;
if ($cat) $tools = array_values(array_filter($tools, function ($p) use ($cat) {
    foreach (get_the_category($p->ID) as $c) if ($c->slug === $cat) return true; return false;
}));
$cmp = [
    'visits' => fn($a, $b) => (float)hh_meta($b->ID, 'monthly_visits') - (float)hh_meta($a->ID, 'monthly_visits'),
    'likes'  => fn($a, $b) => (float)hh_meta($b->ID, 'likes') - (float)hh_meta($a->ID, 'likes'),
    'growth' => fn($a, $b) => (float)hh_meta($b->ID, 'growth') - (float)hh_meta($a->ID, 'growth'),
    'hot'    => fn($a, $b) => (int)hh_meta($b->ID, 'views') - (int)hh_meta($a->ID, 'views'),
    'new'    => fn($a, $b) => strtotime($b->post_date) - strtotime($a->post_date),
];
usort($tools, $cmp[$by] ?? $cmp['visits']);
$active = $tabs[$by] ?? $tabs['visits'];
$podium = array_slice($tools, 0, 3);
$rest = array_slice($tools, 3);
$tool_cats = get_categories(['hide_empty' => true, 'exclude' => implode(',', hahatool_reserved_ids())]);
$promo = hahatool_promo('ranking-top', 1);
$medal = ['#fbbf24', '#d1d5db', '#fb923c'];
$base = home_url('/ranking/');
$rlink = fn($k, $v) => esc_url(add_query_arg(array_filter([$k === 'by' ? 'by' : 'cat' => $v, $k === 'by' ? 'cat' : 'by' => ($k === 'by' ? $cat : ($by === 'visits' ? '' : $by))]), $base));
$valfmt = fn($t) => $by === 'likes' ? hahatool_count(hh_meta($t->ID, 'likes')) : ($by === 'hot' ? hahatool_count(hh_meta($t->ID, 'views')) : hahatool_count(hh_meta($t->ID, 'monthly_visits')));
$vallbl = $by === 'likes' ? '收藏' : ($by === 'hot' ? '站内浏览' : '月访问');
?>
<div class="wrap" style="padding-top:40px;max-width:1000px">
  <h1 class="section-title-lg">AI 工具排行榜</h1>
  <p class="muted"><?php echo esc_html($active[1]); ?> · 数据由运营团队维护，仅供参考</p>

  <div class="filters" style="margin-top:18px">
    <?php foreach ($tabs as $k => $t): ?><a class="<?php echo $by === $k ? 'on' : ''; ?>" href="<?php echo esc_url(add_query_arg(array_filter(['by' => $k === 'visits' ? '' : $k, 'cat' => $cat]), $base)); ?>"><?php echo esc_html($t[0]); ?></a><?php endforeach; ?>
  </div>
  <div class="filters">
    <a class="<?php echo $cat ? '' : 'on'; ?>" style="font-size:12px;padding:4px 12px" href="<?php echo esc_url(add_query_arg(array_filter(['by' => $by === 'visits' ? '' : $by]), $base)); ?>">全部分类</a>
    <?php foreach ($tool_cats as $c): ?><a class="<?php echo $cat === $c->slug ? 'on' : ''; ?>" style="font-size:12px;padding:4px 12px" href="<?php echo esc_url(add_query_arg(array_filter(['by' => $by === 'visits' ? '' : $by, 'cat' => $c->slug]), $base)); ?>"><?php echo esc_html($c->name); ?></a><?php endforeach; ?>
  </div>

  <div style="margin-top:18px"><?php hahatool_render_promo('ranking-top', $promo); ?></div>

  <?php if ($podium): ?>
  <div class="grid grid-3" style="margin-top:24px">
    <?php foreach ($podium as $i => $t): ?>
      <div class="card" style="border:2px solid <?php echo $medal[$i]; ?>">
        <a class="stretched" href="<?php echo esc_url(get_permalink($t)); ?>" aria-label="查看 <?php echo esc_attr(get_the_title($t)); ?> 详情"></a>
        <span class="badge display" style="position:absolute;left:-8px;top:-10px;background:<?php echo $medal[$i]; ?>;color:#3b2f00">NO.<?php echo $i + 1; ?></span>
        <div class="card-top"><?php echo hahatool_logo($t->ID, 52); ?><div><h3><?php echo esc_html(get_the_title($t)); ?></h3><?php echo hahatool_stars(hh_meta($t->ID, 'rating')); ?></div></div>
        <p class="tagline"><?php echo esc_html(hh_meta($t->ID, 'tagline')); ?></p>
        <div class="card-foot"><span class="display" style="font-size:18px;font-weight:700;color:var(--text)"><?php echo $valfmt($t); ?></span><span class="muted"><?php echo $vallbl; ?></span><?php echo '<span class="spacer">' . hahatool_growth_badge(hh_meta($t->ID, 'growth')) . '</span>'; ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px">
    <?php foreach ($rest as $i => $t): ?>
      <div class="card" style="flex-direction:row;align-items:center;gap:16px;padding:14px">
        <a class="stretched" href="<?php echo esc_url(get_permalink($t)); ?>" aria-label="查看 <?php echo esc_attr(get_the_title($t)); ?> 详情"></a>
        <span class="display" style="width:32px;text-align:center;font-weight:700;color:var(--text-3)"><?php echo $i + 4; ?></span>
        <?php echo hahatool_logo($t->ID, 44); ?>
        <div style="flex:1;min-width:0"><div style="display:flex;align-items:center;gap:8px"><b><?php echo esc_html(get_the_title($t)); ?></b><?php echo hahatool_pricing_badge(hh_meta($t->ID, 'pricing')); ?></div><div class="muted" style="font-size:13px"><?php echo esc_html(hh_meta($t->ID, 'tagline')); ?></div></div>
        <div class="tnum display" style="font-weight:600;text-align:right;min-width:70px"><?php echo $valfmt($t); ?><div class="muted" style="font-size:11px;font-weight:400"><?php echo $vallbl; ?></div></div>
        <a style="position:relative;z-index:1;color:var(--text-3)" href="<?php echo esc_url(hh_meta($t->ID, 'url')); ?>" target="_blank" rel="noopener nofollow" data-track-click="<?php echo (int)$t->ID; ?>">↗</a>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if (!$tools): ?><div class="empty" style="margin-top:24px">该分类下暂无工具</div><?php endif; ?>
</div>
<?php get_footer();
