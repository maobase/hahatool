<?php
/** 资讯 / 快讯 详情 */
if (!defined('ABSPATH')) exit;
$id = get_the_ID();
$cover = hh_meta($id, 'cover');
?>
<div class="wrap" style="padding-top:32px;max-width:820px">
  <nav class="crumb"><a href="<?php echo esc_url(get_category_link_safe('ai-news')); ?>">‹ 返回资讯</a></nav>
  <article class="panel" style="padding:0;overflow:hidden">
    <?php if ($cover): ?><img src="<?php echo esc_url($cover); ?>" alt="" style="width:100%;aspect-ratio:2/1;object-fit:cover"><?php endif; ?>
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
</div>
