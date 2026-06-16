<?php
/** 兜底模板：归档/标签等 */
if (!defined('ABSPATH')) exit;
get_header();
?>
<div class="wrap" style="padding-top:40px">
  <h1 class="section-title-lg"><?php
    if (is_tag()) echo '标签：' . single_tag_title('', false);
    elseif (is_archive()) the_archive_title();
    else echo '全部内容';
  ?></h1>
  <?php if (have_posts()): ?>
    <div class="grid" style="margin-top:24px">
      <?php while (have_posts()): the_post(); $p = get_post();
        if (hh_meta($p->ID, 'prompt')): hahatool_prompt_card($p);
        elseif (hahatool_is_tool($p->ID)): hahatool_tool_card($p);
        else: ?>
          <a class="card" href="<?php the_permalink(); ?>"><time class="muted"><?php echo esc_html(get_the_date('Y-m-d')); ?></time><h3 style="margin-top:8px"><?php the_title(); ?></h3></a>
        <?php endif;
      endwhile; wp_reset_postdata(); ?>
    </div>
    <?php hahatool_pagination(max(1, (int) get_query_var('paged')), $GLOBALS['wp_query']->max_num_pages, 'get_pagenum_link'); ?>
  <?php else: ?>
    <div class="empty" style="margin-top:24px">暂无内容</div>
  <?php endif; ?>
</div>
<?php get_footer();
