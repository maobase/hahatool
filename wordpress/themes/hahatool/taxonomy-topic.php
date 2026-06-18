<?php
/** 专题归档：封面头 + 该专题下的工具 */
if (!defined('ABSPATH')) exit;
get_header();
$term = get_queried_object();
$cover = $term ? get_term_meta($term->term_id, 'topic_cover', true) : '';

// 结构化数据：CollectionPage + ItemList（专题=工具合集）+ 面包屑
$tax_items = [];
foreach ($GLOBALS['wp_query']->posts as $tax_i => $tax_p) {
    $tax_items[] = ['@type' => 'ListItem', 'position' => $tax_i + 1, 'name' => get_the_title($tax_p), 'url' => get_permalink($tax_p)];
}
$tax_ld = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $term->name,
    'description' => $term->description ?: null,
    'url' => get_term_link($term),
    'mainEntity' => $tax_items ? ['@type' => 'ItemList', 'itemListElement' => $tax_items] : null,
]);
$tax_crumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
    ['@type' => 'ListItem', 'position' => 1, 'name' => '首页', 'item' => home_url('/')],
    ['@type' => 'ListItem', 'position' => 2, 'name' => '专题', 'item' => home_url('/topics/')],
    ['@type' => 'ListItem', 'position' => 3, 'name' => $term->name, 'item' => get_term_link($term)],
]];
?>
<script type="application/ld+json"><?php echo wp_json_encode($tax_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<script type="application/ld+json"><?php echo wp_json_encode($tax_crumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<div class="wrap" style="padding-top:32px">
  <nav class="crumb"><a href="<?php echo esc_url(home_url('/topics/')); ?>" style="display:inline-flex;align-items:center;gap:4px"><?php echo hh_icon('chevron-left', 16); ?>全部专题</a></nav>
  <?php /* 封面已是「带文字」的编辑图，用作列表缩略图与社媒 OG；详情头改用品牌渐变 + 真实文案，避免与封面内文字重复 */ ?>
  <header class="topic-hero">
    <span class="chip" style="background:rgba(255,255,255,.2);color:#fff">专题</span>
    <h1><?php echo esc_html($term->name); ?></h1>
    <?php if ($term->description): ?><p><?php echo esc_html($term->description); ?></p><?php endif; ?>
    <span class="topic-count"><?php echo hh_icon('layers', 14); ?> <?php echo (int) $term->count; ?> 个工具</span>
  </header>
  <?php if (have_posts()): ?>
    <div class="grid" style="margin-top:24px">
      <?php while (have_posts()): the_post(); $p = get_post();
        if (hahatool_is_tool($p->ID)) hahatool_tool_card($p);
      endwhile; wp_reset_postdata(); ?>
    </div>
    <?php hahatool_pagination(max(1, (int) get_query_var('paged')), $GLOBALS['wp_query']->max_num_pages, 'get_pagenum_link'); ?>
  <?php else: ?>
    <div class="empty" style="margin-top:24px">该专题暂无内容</div>
  <?php endif; ?>
</div>
<?php get_footer();
