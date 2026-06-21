<?php
/** 404 */
if (!defined('ABSPATH')) exit;
get_header();
?>
<div class="wrap" style="padding:72px 24px;text-align:center;max-width:640px">
  <p class="display" style="font-size:72px;font-weight:800;color:var(--brand-200)">404</p>
  <h1 style="margin-top:8px;font-size:22px">页面不存在</h1>
  <p class="muted" style="margin-top:8px">你访问的内容可能已被删除或尚未收录。试试搜索，或从下面的入口继续。</p>
  <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" style="display:flex;gap:8px;max-width:460px;margin:24px auto 0">
    <input type="search" name="s" placeholder="搜索 AI 工具、提示词、资讯…" aria-label="搜索" style="flex:1;min-width:0;padding:12px 16px;min-height:46px;border:1px solid var(--border);border-radius:12px;background:var(--surface);color:var(--text);font-size:15px">
    <button class="btn" type="submit">搜索</button>
  </form>
  <div style="margin-top:18px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a class="btn btn-ghost" href="<?php echo esc_url(home_url('/')); ?>">返回首页</a>
    <a class="btn btn-ghost" href="<?php echo esc_url(home_url('/tools/')); ?>">浏览工具库</a>
    <a class="btn btn-ghost" href="<?php echo esc_url(get_category_link_safe('ai-news')); ?>">AI 资讯</a>
  </div>
  <?php $hot404 = function_exists('hahatool_hot_tools') ? hahatool_hot_tools(8) : []; if ($hot404): ?>
  <div style="margin-top:44px">
    <p class="muted" style="font-size:13px;margin-bottom:14px">或看看大家都在用的工具</p>
    <div class="tagcloud-grid" style="justify-content:center">
      <?php foreach ($hot404 as $t): ?><a href="<?php echo esc_url(get_permalink($t)); ?>"><?php echo esc_html(get_the_title($t)); ?></a><?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php // 把死胡同变成发现页：热门专题 + 最新资讯，内容更丰富、更有去处
  $t404 = get_terms(['taxonomy' => 'topic', 'hide_empty' => true, 'number' => 6, 'orderby' => 'count', 'order' => 'DESC']);
  if (!is_wp_error($t404) && $t404): ?>
  <div style="margin-top:40px">
    <p class="muted" style="font-size:13px;margin-bottom:14px">按场景逛专题</p>
    <div class="tagcloud-grid" style="justify-content:center">
      <?php foreach ($t404 as $tm): ?><a href="<?php echo esc_url(get_term_link($tm)); ?>"># <?php echo esc_html($tm->name); ?></a><?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php $n404 = new WP_Query(['post_type' => 'post', 'category_name' => 'ai-news', 'posts_per_page' => 4, 'no_found_rows' => true, 'ignore_sticky_posts' => true]);
  if ($n404->have_posts()): ?>
  <div style="margin-top:40px;text-align:left;max-width:520px;margin-left:auto;margin-right:auto">
    <p class="muted" style="font-size:13px;margin-bottom:12px;text-align:center">最新 AI 资讯</p>
    <div style="display:flex;flex-direction:column;gap:8px">
      <?php while ($n404->have_posts()): $n404->the_post(); ?>
        <a class="rank-item" href="<?php echo esc_url(get_permalink()); ?>" style="text-decoration:none"><span class="muted tnum" style="flex-shrink:0;font-size:12px"><?php echo esc_html(get_the_date('m-d')); ?></span><span class="clamp1" style="flex:1;min-width:0;font-size:14px;font-weight:500;color:var(--text)"><?php the_title(); ?></span><?php echo hh_icon('chevron-right', 15); ?></a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <p style="text-align:center;margin-top:14px"><a class="more" href="<?php echo esc_url(get_category_link_safe('ai-news')); ?>" style="font-size:13px">查看全部资讯<?php echo hh_icon('chevron-right', 14); ?></a></p>
  </div>
  <?php endif; ?>
</div>
<?php get_footer();
