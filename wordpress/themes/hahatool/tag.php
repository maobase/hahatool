<?php
/** 标签归档：该标签下的工具 + 相关标签 + 结构化数据（CollectionPage/ItemList/面包屑） */
if (!defined('ABSPATH')) exit;
get_header();
$tag = get_queried_object();
$others = get_tags(['hide_empty' => true, 'exclude' => [$tag->term_id], 'orderby' => 'count', 'order' => 'DESC', 'number' => 14]);
$paged = max(1, (int) get_query_var('paged'));

// 收集本页工具用于 ItemList 结构化数据（与 /tools、排行榜、专题等列表页一致）
$tg_posts = [];
foreach ($GLOBALS['wp_query']->posts as $tp) { if (hahatool_is_tool($tp->ID)) $tg_posts[] = $tp; }
if ($paged <= 1 && $tg_posts && function_exists('hahatool_itemlist_ld')) {
    echo hahatool_itemlist_ld($tg_posts, '# ' . $tag->name . ' · AI 工具', get_tag_link($tag), $tag->name . ' 相关的 AI 工具合集，附流量、定价与评分。');
}
$tg_crumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
    ['@type' => 'ListItem', 'position' => 1, 'name' => '首页', 'item' => home_url('/')],
    ['@type' => 'ListItem', 'position' => 2, 'name' => '全部工具', 'item' => home_url('/tools/')],
    ['@type' => 'ListItem', 'position' => 3, 'name' => '# ' . $tag->name, 'item' => get_tag_link($tag)],
]];
?>
<script type="application/ld+json"><?php echo wp_json_encode($tg_crumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<div class="wrap" style="padding-top:32px">
  <nav class="crumb"><a href="<?php echo esc_url(home_url('/')); ?>">首页</a> / <a href="<?php echo esc_url(home_url('/tools/')); ?>">全部工具</a> / <span style="color:var(--text-2)"># <?php echo esc_html($tag->name); ?></span></nav>
  <h1 class="section-title-lg" style="display:flex;align-items:center;gap:8px;margin-top:12px"><span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:12px;background:var(--brand-600);color:#fff"><?php echo hh_icon('layers', 18); ?></span># <?php echo esc_html($tag->name); ?></h1>
  <p class="muted">共 <?php echo (int) $tag->count; ?> 款「<?php echo esc_html($tag->name); ?>」相关工具</p>

  <?php if ($others): ?>
  <div style="margin-top:18px">
    <span class="muted" style="font-size:13px">相关标签</span>
    <div class="tagcloud-grid" style="margin-top:8px">
      <?php foreach ($others as $t): ?><a href="<?php echo esc_url(get_tag_link($t)); ?>"># <?php echo esc_html($t->name); ?><b><?php echo (int) $t->count; ?></b></a><?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (have_posts()): ?>
    <div class="grid" style="margin-top:24px">
      <?php while (have_posts()): the_post(); $p = get_post();
        if (hahatool_is_tool($p->ID)) hahatool_tool_card($p); wp_reset_postdata();
      endwhile; ?>
    </div>
    <?php hahatool_pagination($paged, $GLOBALS['wp_query']->max_num_pages, 'get_pagenum_link'); ?>
  <?php else: ?>
    <div class="empty" style="margin-top:24px">
      <div style="color:var(--text-3)"><?php echo hh_icon('search', 36, 1.5); ?></div>
      <p style="margin-top:10px;font-weight:500;color:var(--text-2)">该标签下暂无工具</p>
      <a class="btn" style="margin-top:14px;display:inline-flex" href="<?php echo esc_url(home_url('/tools/')); ?>">浏览全部工具</a>
    </div>
  <?php endif; ?>

  <p class="muted" style="margin-top:40px;text-align:center;font-size:13px">没找到合适的？<a href="<?php echo esc_url(home_url('/submit/')); ?>" style="color:var(--brand-600)">提交工具</a> 或 <a href="<?php echo esc_url(home_url('/tools/')); ?>" style="color:var(--brand-600)">浏览全部工具</a></p>
</div>
<?php get_footer();
