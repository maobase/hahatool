<?php
/** 专题归档：封面头 + 该专题下的工具 */
if (!defined('ABSPATH')) exit;
get_header();
$term = get_queried_object();
$cover = $term ? get_term_meta($term->term_id, 'topic_cover', true) : '';
?>
<div class="wrap" style="padding-top:32px">
  <nav class="crumb"><a href="<?php echo esc_url(home_url('/topics/')); ?>" style="display:inline-flex;align-items:center;gap:4px"><?php echo hh_icon('chevron-left', 16); ?>全部专题</a></nav>
  <header class="topic-hero"<?php if ($cover): ?> style="background-image:linear-gradient(120deg,rgba(3,7,18,.62),rgba(3,7,18,.86)),url('<?php echo esc_url($cover); ?>')"<?php endif; ?>>
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
