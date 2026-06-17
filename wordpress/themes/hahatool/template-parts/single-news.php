<?php
/** 资讯 / 快讯 详情 */
if (!defined('ABSPATH')) exit;
$id = get_the_ID();
$cover = hh_meta($id, 'cover');
// 侧栏数据（对齐无头版资讯页侧栏：快讯 + 本周热门工具）
$side_flash = hahatool_channel('ai-flash', 6)->posts;
$hot = hahatool_tools(['posts_per_page' => 200])->posts;
usort($hot, fn($a, $b) => (float) hh_meta($b->ID, 'monthly_visits') - (float) hh_meta($a->ID, 'monthly_visits'));
$hot = array_slice($hot, 0, 5);
?>
<div class="wrap" style="padding-top:32px">
  <nav class="crumb"><a href="<?php echo esc_url(get_category_link_safe('ai-news')); ?>" style="display:inline-flex;align-items:center;gap:4px"><?php echo hh_icon('chevron-left', 16); ?>返回资讯列表</a></nav>
  <div class="detail-grid" style="margin-top:16px">
    <div class="detail-main">
      <article class="panel" style="padding:0;overflow:hidden">
        <?php if ($cover): ?><img src="<?php echo esc_url($cover); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" style="width:100%;aspect-ratio:2/1;object-fit:cover"><?php endif; ?>
        <div style="padding:32px">
          <div class="news-meta"><time><?php echo esc_html(get_the_date('Y-m-d')); ?></time><span>·</span><span class="rt"><?php echo hh_icon('clock', 13); ?><?php echo (int) hahatool_read_time(get_the_content()); ?> 分钟阅读</span></div>
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
        <?php if ($prev): ?><a class="panel" style="padding:16px" href="<?php echo esc_url(get_permalink($prev)); ?>"><span class="muted" style="display:inline-flex;align-items:center;gap:3px"><?php echo hh_icon('chevron-left', 13); ?>上一篇</span><div style="margin-top:4px;font-weight:500"><?php echo esc_html(get_the_title($prev)); ?></div></a><?php else: ?><span></span><?php endif; ?>
        <?php if ($next): ?><a class="panel" style="padding:16px;text-align:right" href="<?php echo esc_url(get_permalink($next)); ?>"><span class="muted" style="display:inline-flex;align-items:center;justify-content:flex-end;gap:3px">下一篇<?php echo hh_icon('chevron-right', 13); ?></span><div style="margin-top:4px;font-weight:500"><?php echo esc_html(get_the_title($next)); ?></div></a><?php endif; ?>
      </nav>
      <?php endif; ?>

      <?php
      // 相关资讯（同 ai-news 分类，排除当前，取 3）
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

    <aside>
      <div class="panel">
        <div style="display:flex;justify-content:space-between;align-items:center"><h2 style="font-size:16px;display:flex;align-items:center;gap:5px"><?php echo hh_icon('zap', 16); ?>AI 快讯</h2><a class="muted" style="font-size:12px" href="<?php echo esc_url(get_category_link_safe('ai-flash')); ?>">全部 →</a></div>
        <div style="margin-top:14px"><?php hahatool_flash_timeline($side_flash, true); ?></div>
      </div>
      <?php hahatool_hot_news_panel(5, $id); ?>
      <?php if ($hot): ?>
      <div class="panel" style="margin-top:24px">
        <h2 style="font-size:16px;margin-bottom:10px">本周热门工具</h2>
        <div class="rank-list">
          <?php foreach ($hot as $i => $t): ?>
            <a class="rank-item" href="<?php echo esc_url(get_permalink($t)); ?>"><span class="num"><?php echo $i + 1; ?></span><?php echo hahatool_logo($t->ID, 32); ?><span style="flex:1;min-width:0;font-size:14px;font-weight:500"><?php echo esc_html(get_the_title($t)); ?></span><span class="muted tnum" style="font-size:12px"><?php echo hahatool_count(hh_meta($t->ID, 'monthly_visits')); ?></span></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </aside>
  </div>
</div>
