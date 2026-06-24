<?php
/** 资讯 / 快讯 详情 */
if (!defined('ABSPATH')) exit;
$id = get_the_ID();
$cover = hh_meta($id, 'cover');
// 频道感知（资讯 / 快讯）—— 用于可见面包屑与结构化数据正确标注（本模板同时服务 ai-news 与 ai-flash）
$is_flash = has_category('ai-flash', $id);
$chan_name = $is_flash ? 'AI 快讯' : 'AI 资讯';
$chan_link = $is_flash ? home_url('/flash/') : get_category_link_safe('ai-news');
// 侧栏数据（对齐无头版资讯页侧栏：快讯 + 本周热门工具）
$side_flash = hahatool_channel('ai-flash', 6)->posts;
$hot = hahatool_tools(['posts_per_page' => 200])->posts;
usort($hot, fn($a, $b) => (float) hh_meta($b->ID, 'monthly_visits') - (float) hh_meta($a->ID, 'monthly_visits'));
$hot = array_slice($hot, 0, 5);

// 结构化数据：NewsArticle + 面包屑（资讯 SEO，对齐工具详情的 JSON-LD 做法）
$nl_article = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => get_the_title(),
    'datePublished' => get_the_date('c'),
    'dateModified' => get_the_modified_date('c'),
    'image' => $cover ? [hahatool_raster($cover)] : null, // Article 富结果需位图：SVG 封面取同名 PNG
    'description' => mb_substr(wp_strip_all_tags(get_the_excerpt()), 0, 160),
    'mainEntityOfPage' => get_permalink(),
    'author' => ['@type' => 'Organization', 'name' => get_bloginfo('name'), 'url' => home_url('/')],
    'publisher' => ['@type' => 'Organization', 'name' => get_bloginfo('name'), 'logo' => ['@type' => 'ImageObject', 'url' => hahatool_logo_url(), 'width' => 600, 'height' => 140]],
]);
$nl_crumbs = [
    ['@type' => 'ListItem', 'position' => 1, 'name' => '首页', 'item' => home_url('/')],
    ['@type' => 'ListItem', 'position' => 2, 'name' => $chan_name, 'item' => $chan_link],
    ['@type' => 'ListItem', 'position' => 3, 'name' => get_the_title(), 'item' => get_permalink()],
];
$nl_breadcrumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $nl_crumbs];
?>
<script type="application/ld+json"><?php echo wp_json_encode($nl_article, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<script type="application/ld+json"><?php echo wp_json_encode($nl_breadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<div class="read-progress" id="readProgress" aria-hidden="true"></div>
<div class="wrap" style="padding-top:32px">
  <nav class="crumb"><a href="<?php echo esc_url(home_url('/')); ?>">首页</a> / <a href="<?php echo esc_url($chan_link); ?>"><?php echo esc_html($chan_name); ?></a> / <span style="color:var(--text-2)"><?php the_title(); ?></span></nav>
  <div class="detail-grid" style="margin-top:16px">
    <div class="detail-main">
      <article class="panel" style="padding:0;overflow:hidden">
        <?php if ($cover): ?><img src="<?php echo esc_url($cover); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" style="width:100%;aspect-ratio:2/1;object-fit:cover"><?php endif; ?>
        <div style="padding:32px">
          <div class="news-meta"><time><?php echo esc_html(get_the_date('Y-m-d')); ?></time><span>·</span><span class="rt"><?php echo hh_icon('clock', 13); ?><?php echo (int) hahatool_read_time(get_the_content()); ?> 分钟阅读</span></div>
          <h1 style="font-size:28px;margin:8px 0 0"><?php the_title(); ?></h1>
          <?php
          $share_url = get_permalink();
          $share_title = get_the_title();
          $weibo = 'https://service.weibo.com/share/share.php?url=' . rawurlencode($share_url) . '&title=' . rawurlencode($share_title . ' — ' . get_bloginfo('name'));
          ?>
          <div class="article-share" data-share-url="<?php echo esc_attr($share_url); ?>" data-share-title="<?php echo esc_attr($share_title); ?>">
            <span class="muted" style="font-size:13px">分享</span>
            <a class="share-btn" href="<?php echo esc_url($weibo); ?>" target="_blank" rel="noopener nofollow" aria-label="分享到微博"><?php echo hh_icon('message', 14); ?>微博</a>
            <button type="button" class="share-btn" data-share-copy aria-label="复制文章链接"><?php echo hh_icon('link', 14); ?>复制链接</button>
            <button type="button" class="share-btn" data-share-native hidden aria-label="系统分享"><?php echo hh_icon('share', 14); ?>分享</button>
          </div>
          <div class="prose" style="margin-top:20px"><?php the_content(); ?></div>
        </div>
      </article>

      <?php
      // 文中相关工具：扫描正文，匹配站内已收录的工具标题，做内容↔工具交叉链接（利于内链与发现）
      $nl_text = wp_strip_all_tags(get_the_content());
      $nl_mentioned = [];
      foreach (hahatool_tools(['posts_per_page' => 300])->posts as $nt) {
          $tt = get_the_title($nt);
          if ($tt && mb_strlen($tt) >= 2 && mb_strpos($nl_text, $tt) !== false) {
              $nl_mentioned[] = $nt;
              if (count($nl_mentioned) >= 4) break;
          }
      }
      if ($nl_mentioned): ?>
      <section class="panel" style="margin-top:20px">
        <h2 style="font-size:16px;display:flex;align-items:center;gap:6px"><span style="color:var(--brand-500)"><?php echo hh_icon('sparkles', 16); ?></span>文中相关工具</h2>
        <div class="rank-list" style="margin-top:12px">
          <?php foreach ($nl_mentioned as $nt): ?>
            <a class="rank-item" href="<?php echo esc_url(get_permalink($nt)); ?>">
              <?php echo hahatool_logo($nt->ID, 36); ?>
              <span style="flex:1;min-width:0">
                <span style="display:block;font-weight:600;font-size:14px"><?php echo esc_html(get_the_title($nt)); ?></span>
                <?php if ($nt_tag = hh_meta($nt->ID, 'tagline')): ?><span class="muted" style="display:block;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html($nt_tag); ?></span><?php endif; ?>
              </span>
              <span class="muted"><?php echo hh_icon('arrow-up-right', 14); ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php
      // 上一篇 / 下一篇（同分类）
      $prev = get_previous_post(true);
      $next = get_next_post(true);
      if ($prev || $next): ?>
      <nav class="pn-nav">
        <?php if ($prev): ?><a class="pn-card" href="<?php echo esc_url(get_permalink($prev)); ?>"><span class="pn-label"><?php echo hh_icon('chevron-left', 13); ?>上一篇</span><span class="pn-title clamp2"><?php echo esc_html(get_the_title($prev)); ?></span></a><?php else: ?><span></span><?php endif; ?>
        <?php if ($next): ?><a class="pn-card pn-next" href="<?php echo esc_url(get_permalink($next)); ?>"><span class="pn-label">下一篇<?php echo hh_icon('chevron-right', 13); ?></span><span class="pn-title clamp2"><?php echo esc_html(get_the_title($next)); ?></span></a><?php endif; ?>
      </nav>
      <?php endif; ?>

      <?php
      // 相关资讯（同 ai-news 分类，排除当前，取 4，带封面缩略图、紧凑排版）
      $rel_q = new WP_Query(['post_type' => 'post', 'category_name' => 'ai-news', 'posts_per_page' => 4, 'post__not_in' => [$id], 'no_found_rows' => true]);
      if ($rel_q->have_posts()): ?>
      <section style="margin-top:36px">
        <h2 style="font-size:20px;margin-bottom:14px">相关资讯</h2>
        <div class="rel-list">
          <?php while ($rel_q->have_posts()): $rel_q->the_post(); $rc = hh_meta(get_the_ID(), 'cover'); ?>
            <a class="rel-item" href="<?php the_permalink(); ?>">
              <?php if ($rc): ?><img class="rel-thumb" src="<?php echo esc_url($rc); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy"><?php endif; ?>
              <span class="rel-bd"><span class="rel-tt clamp2" style="display:block"><?php the_title(); ?></span><time class="muted" style="font-size:12px;display:block;margin-top:4px"><?php echo esc_html(get_the_date('Y-m-d')); ?></time></span>
              <span class="rel-ic"><?php echo hh_icon('chevron-right', 16); ?></span>
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
