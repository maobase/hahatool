<?php
/** 标签归档：该标签下的工具 + 其他标签云 */
if (!defined('ABSPATH')) exit;
get_header();
$tag = get_queried_object();
$others = get_tags(['hide_empty' => true, 'exclude' => [$tag->term_id], 'orderby' => 'count', 'order' => 'DESC', 'number' => 14]);
?>
<div class="wrap" style="padding-top:40px">
  <h1 class="section-title-lg"># <?php echo esc_html($tag->name); ?></h1>
  <p class="muted">共 <?php echo (int)$tag->count; ?> 款「<?php echo esc_html($tag->name); ?>」相关工具</p>

  <?php if ($others): ?>
  <div class="tagcloud-grid" style="margin-top:16px">
    <?php foreach ($others as $t): ?><a href="<?php echo esc_url(get_tag_link($t)); ?>"># <?php echo esc_html($t->name); ?><b><?php echo (int)$t->count; ?></b></a><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (have_posts()): ?>
    <div class="grid" style="margin-top:24px">
      <?php while (have_posts()): the_post(); $p = get_post();
        if (hahatool_is_tool($p->ID)) hahatool_tool_card($p); wp_reset_postdata();
      endwhile; ?>
    </div>
    <div class="pagination"><?php echo paginate_links(); ?></div>
  <?php else: ?>
    <div class="empty" style="margin-top:24px">该标签下暂无工具</div>
  <?php endif; ?>
</div>
<?php get_footer();
