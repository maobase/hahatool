<?php
/** /tools 工具库：分类 + 定价筛选 + 排序 */
if (!defined('ABSPATH')) exit;
get_header();
$cat = isset($_GET['cat']) ? sanitize_title($_GET['cat']) : '';
$pricing = isset($_GET['pricing']) ? sanitize_text_field($_GET['pricing']) : '';
$sort = isset($_GET['sort']) ? sanitize_key($_GET['sort']) : 'latest';

$tools = hahatool_tools(['posts_per_page' => 300])->posts;
$all_tools = $tools; // 留存筛选前全量，用于工具库统计
if ($cat) $tools = array_filter($tools, function ($p) use ($cat) {
    foreach (get_the_category($p->ID) as $c) if ($c->slug === $cat) return true; return false;
});
if ($pricing) $tools = array_filter($tools, fn($p) => hh_meta($p->ID, 'pricing') === $pricing);
$tools = array_values($tools);
$cmp = [
    'visits' => fn($a, $b) => (float)hh_meta($b->ID, 'monthly_visits') - (float)hh_meta($a->ID, 'monthly_visits'),
    'likes'  => fn($a, $b) => (float)hh_meta($b->ID, 'likes') - (float)hh_meta($a->ID, 'likes'),
    'growth' => fn($a, $b) => (float)hh_meta($b->ID, 'growth') - (float)hh_meta($a->ID, 'growth'),
    'rating' => fn($a, $b) => (float)hh_meta($b->ID, 'rating') - (float)hh_meta($a->ID, 'rating'),
];
if (isset($cmp[$sort])) usort($tools, $cmp[$sort]);

// 分页 24/页（对齐无头版 PAGE_SIZE，保留筛选/排序）
$hh_total = count($tools);
$hh_per = 24;
$hh_pages = max(1, (int) ceil($hh_total / $hh_per));
$hh_cur = max(1, min($hh_pages, (int) ($_GET['paged'] ?? 1)));
$tools = array_slice($tools, ($hh_cur - 1) * $hh_per, $hh_per);

$tool_cats = get_categories(['hide_empty' => true, 'exclude' => implode(',', hahatool_reserved_ids())]);
$base = home_url('/tools/');
$link = function ($k, $v) use ($cat, $pricing, $sort, $base) {
    $args = array_filter(['cat' => $cat, 'pricing' => $pricing, 'sort' => $sort === 'latest' ? '' : $sort]);
    if ($v === null) unset($args[$k]); else $args[$k] = $v;
    return esc_url(add_query_arg($args, $base));
};
// 工具库统计（基于筛选前全量），让目录页更有「数据感」
$stat_total = count($all_tools);
$stat_cats  = count($tool_cats);
$stat_free  = count(array_filter($all_tools, fn($p) => in_array(hh_meta($p->ID, 'pricing'), ['免费', '免费增值'], true)));
$stat_month = count(array_filter($all_tools, fn($p) => get_the_date('Y-m', $p) === current_time('Y-m')));
if ($stat_month > 0) { $stat4_n = $stat_month; $stat4_l = '本月更新'; }
else {
    $ratings = array_filter(array_map(fn($p) => (float) hh_meta($p->ID, 'rating'), $all_tools), fn($r) => $r > 0);
    $stat4_n = $ratings ? round(array_sum($ratings) / count($ratings), 1) : 0;
    $stat4_l = '平均评分';
}
// 结构化数据：当前页工具列表 CollectionPage + ItemList
echo hahatool_itemlist_ld($tools, '全部工具 - ' . get_bloginfo('name'), home_url('/tools/'), '浏览 HahaTool 收录的全部 AI 工具，支持分类、定价筛选与多维排序。');
?>
<div class="wrap" style="padding-top:40px">
  <h1 class="section-title-lg">全部工具</h1>
  <p class="muted">收录全球优秀 AI 产品，附流量数据、定价与真实点评 · 共 <?php echo (int) $hh_total; ?> 款<?php if ($hh_pages > 1) echo ' · 第 ' . $hh_cur . '/' . $hh_pages . ' 页'; ?></p>

  <div class="dir-stats">
    <div><div class="n tnum"><?php echo (int) $stat_total; ?><span>+</span></div><div class="l">收录工具</div></div>
    <div><div class="n tnum"><?php echo (int) $stat_cats; ?></div><div class="l">工具分类</div></div>
    <div><div class="n tnum"><?php echo (int) $stat_free; ?></div><div class="l">免费 / 免费增值</div></div>
    <div><div class="n tnum"><?php echo esc_html($stat4_n); ?></div><div class="l"><?php echo esc_html($stat4_l); ?></div></div>
  </div>

  <div class="filters" style="margin-top:18px"><span class="lbl">分类</span>
    <a class="<?php echo $cat ? '' : 'on'; ?>" href="<?php echo $link('cat', null); ?>">全部</a>
    <?php foreach ($tool_cats as $c): ?><a class="<?php echo $cat === $c->slug ? 'on' : ''; ?>" href="<?php echo $link('cat', $c->slug); ?>"><?php echo esc_html($c->name); ?></a><?php endforeach; ?>
  </div>
  <div class="filters"><span class="lbl">定价</span>
    <a class="<?php echo $pricing ? '' : 'on'; ?>" href="<?php echo $link('pricing', null); ?>">全部</a>
    <?php foreach (['免费', '免费增值', '付费'] as $pr): ?><a class="<?php echo $pricing === $pr ? 'on' : ''; ?>" href="<?php echo $link('pricing', $pr); ?>"><?php echo esc_html($pr); ?></a><?php endforeach; ?>
  </div>
  <div class="filters"><span class="lbl">排序</span>
    <?php foreach (['latest' => '最新收录', 'visits' => '流量最高', 'likes' => '收藏最多', 'growth' => '增长最快', 'rating' => '评分最高'] as $k => $lbl): ?>
      <a class="<?php echo $sort === $k ? 'on' : ''; ?>" href="<?php echo $link('sort', $k === 'latest' ? null : $k); ?>"><?php echo esc_html($lbl); ?></a>
    <?php endforeach; ?>
  </div>

  <?php if ($tools): $promo = hahatool_promo('tools-inline', 1); ?>
    <div class="grid" style="margin-top:24px"><?php foreach (array_slice($tools, 0, 8) as $p) hahatool_tool_card($p); wp_reset_postdata(); ?></div>
    <div style="margin-top:16px"><?php hahatool_render_promo('tools-inline', $promo); ?></div>
    <?php if (count($tools) > 8): ?><div class="grid" style="margin-top:16px"><?php foreach (array_slice($tools, 8) as $p) hahatool_tool_card($p); wp_reset_postdata(); ?></div><?php endif; ?>
    <?php hahatool_pagination($hh_cur, $hh_pages, function ($n) use ($cat, $pricing, $sort, $base) {
      $args = array_filter(['cat' => $cat, 'pricing' => $pricing, 'sort' => $sort === 'latest' ? '' : $sort, 'paged' => $n > 1 ? $n : '']);
      return empty($args) ? $base : add_query_arg($args, $base);
    }); ?>
  <?php else: ?>
    <div class="empty" style="margin-top:24px">
      <div style="color:var(--text-3)"><?php echo hh_icon('search', 36, 1.5); ?></div>
      <p style="margin-top:10px;font-weight:500;color:var(--text-2)">没有符合当前筛选条件的工具</p>
      <p>试试放宽分类或定价条件</p>
      <a class="btn" style="margin-top:16px;display:inline-flex" href="<?php echo esc_url($base); ?>">清除全部筛选</a>
    </div>
    <?php $et_hot = hahatool_hot_tools(8); if ($et_hot): ?>
    <section class="section" style="margin-top:40px">
      <div class="section-head"><div><h2>大家都在用</h2><div class="sub">先看看这些高人气工具</div></div><a class="more" href="<?php echo esc_url($base); ?>">浏览全部<?php echo hh_icon('chevron-right', 16); ?></a></div>
      <div class="grid"><?php foreach ($et_hot as $t) hahatool_tool_card($t); wp_reset_postdata(); ?></div>
    </section>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php get_footer();
