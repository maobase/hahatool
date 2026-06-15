<?php
/** 资讯 / 快讯 详情 */
if (!defined('ABSPATH')) exit;
$id = get_the_ID();
$cover = hh_meta($id, 'cover');
?>
<div class="wrap" style="padding-top:32px;max-width:820px">
  <nav class="crumb"><a href="<?php echo esc_url(get_category_link_safe('ai-news')); ?>">‹ 返回资讯</a></nav>
  <article class="panel" style="padding:0;overflow:hidden">
    <?php if ($cover): ?><img src="<?php echo esc_url($cover); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" style="width:100%;aspect-ratio:2/1;object-fit:cover"><?php endif; ?>
    <div style="padding:32px">
      <time class="muted"><?php echo esc_html(get_the_date('Y-m-d')); ?></time>
      <h1 style="font-size:28px;margin:8px 0 0"><?php the_title(); ?></h1>
      <div class="prose" style="margin-top:20px"><?php the_content(); ?></div>
    </div>
  </article>

  <?php
  // 上一篇 / 下一篇（同分类）
  $prev = get_previous_post(true);
  $next = get_next_post(true);
  if ($prev || $next): ?>
  <nav style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:20px">
    <?php if ($prev): ?><a class="panel" style="padding:16px" href="<?php echo esc_url(get_permalink($prev)); ?>"><span class="muted">‹ 上一篇</span><div style="margin-top:4px;font-weight:500"><?php echo esc_html(get_the_title($prev)); ?></div></a><?php else: ?><span></span><?php endif; ?>
    <?php if ($next): ?><a class="panel" style="padding:16px;text-align:right" href="<?php echo esc_url(get_permalink($next)); ?>"><span class="muted">下一篇 ›</span><div style="margin-top:4px;font-weight:500"><?php echo esc_html(get_the_title($next)); ?></div></a><?php endif; ?>
  </nav>
  <?php endif; ?>

  <?php
  // 相关资讯（同 ai-news 分类，排除当前，取 3）—— 对齐无头版
  $rel_q = new WP_Query(['post_type' => 'post', 'category_name' => 'ai-news', 'posts_per_page' => 3, 'post__not_in' => [$id], 'no_found_rows' => true]);
  if ($rel_q->have_posts()): ?>
  <section style="margin-top:40px">
    <h2 style="font-size:20px;margin-bottom:16px">相关资讯</h2>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php while ($rel_q->have_posts()): $rel_q->the_post(); ?>
        <a class="panel" style="padding:16px;display:block" href="<?php the_permalink(); ?>">
          <time class="muted" style="font-size:12px"><?php echo esc_html(get_the_date('Y-m-d')); ?></time>
          <div style="margin-top:4px;font-weight:500"><?php the_title(); ?></div>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </section>
  <?php endif; ?>
</div>
