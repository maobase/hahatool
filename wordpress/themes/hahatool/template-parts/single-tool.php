<?php
/** 工具详情 */
if (!defined('ABSPATH')) exit;
$id = get_the_ID();
$url = hh_meta($id, 'url');
$cats = array_filter(get_the_category($id), fn($c) => !in_array($c->slug, HAHATOOL_RESERVED, true));
$main = $cats ? reset($cats) : null;
$scores = hahatool_scores($id);
$faq = array_filter(array_map(fn($l) => explode('|', $l, 2), preg_split('/\r?\n/', hh_meta($id, 'faq'))), fn($x) => count($x) === 2);
$history = array_values(array_filter(array_map('floatval', explode(',', hh_meta($id, 'visits_history'))), fn($v) => $v > 0));
$regions = array_filter(array_map(fn($p) => explode(':', $p), explode(',', hh_meta($id, 'regions'))), fn($x) => count($x) === 2);

// 替代品 & 侧栏推广
$alt = [];
if ($main) {
    $aq = hahatool_tools(['posts_per_page' => 6, 'cat' => $main->term_id, 'post__not_in' => [$id]]);
    usort($aq->posts, fn($a, $b) => (float)hh_meta($b->ID, 'monthly_visits') - (float)hh_meta($a->ID, 'monthly_visits'));
    $alt = array_slice($aq->posts, 0, 5);
}
$side_promo = hahatool_promo('detail-side', 2, $id);

// 结构化数据
$pricing = hh_meta($id, 'pricing');
$jsonld = ['@context' => 'https://schema.org', '@type' => 'SoftwareApplication', 'name' => get_the_title(), 'description' => hh_meta($id, 'tagline'), 'url' => $url, 'applicationCategory' => $main ? $main->name : null];
$jsonld['offers'] = array_filter(['@type' => 'Offer', 'price' => $pricing === '免费' ? '0' : null, 'description' => $pricing]);
if ((float)hh_meta($id, 'rating')) $jsonld['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => (float)hh_meta($id, 'rating'), 'bestRating' => 5, 'ratingCount' => max(1, (int)hh_meta($id, 'likes'))];
// 面包屑结构化数据
$crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => '首页', 'item' => home_url('/')]];
if ($main) $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $main->name, 'item' => get_category_link($main)];
$crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => get_the_title(), 'item' => get_permalink($id)];
$breadcrumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $crumbs];
?>
<script type="application/ld+json"><?php echo wp_json_encode($jsonld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<script type="application/ld+json"><?php echo wp_json_encode($breadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

<div class="wrap" style="padding-top:32px">
  <nav class="crumb"><a href="<?php echo esc_url(home_url('/')); ?>">首页</a> / <?php if ($main): ?><a href="<?php echo esc_url(get_category_link($main)); ?>"><?php echo esc_html($main->name); ?></a> / <?php endif; ?><span style="color:var(--text-2)"><?php the_title(); ?></span></nav>

  <div class="detail-grid">
    <div class="detail-main">
      <div class="panel">
        <div class="detail-head">
          <?php echo hahatool_logo($id, 72); ?>
          <div style="min-width:0;flex:1">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap"><h1><?php the_title(); ?></h1><?php echo hahatool_growth_badge(hh_meta($id, 'growth')); ?></div>
            <div style="margin-top:6px"><?php echo hahatool_stars(hh_meta($id, 'rating')); ?></div>
            <p style="margin:8px 0 0;color:var(--text-2)"><?php echo esc_html(hh_meta($id, 'tagline')); ?></p>
            <?php $tags = get_the_tags($id); if ($tags): ?>
              <p style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">
                <?php foreach ($tags as $t): ?><a class="chip" href="<?php echo esc_url(get_tag_link($t)); ?>"># <?php echo esc_html($t->name); ?></a><?php endforeach; ?>
              </p>
            <?php endif; ?>
          </div>
          <div class="detail-actions">
            <?php echo hahatool_fav_button($id, true); ?>
            <a class="btn" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener nofollow" data-track-click="<?php echo (int)$id; ?>">访问官网 <?php echo hh_icon('arrow-up-right', 15); ?></a>
          </div>
        </div>
        <div class="detail-stats">
          <span><?php echo hh_icon('bookmark', 15); ?> <?php echo hahatool_count(hh_meta($id, 'likes')); ?> 收藏</span>
          <span><?php echo hh_icon('trending', 15); ?> 月访问 <?php echo hahatool_count(hh_meta($id, 'monthly_visits')); ?></span>
          <?php if ((int)hh_meta($id, 'views')): ?><span><?php echo hh_icon('eye', 15); ?> <?php echo hahatool_count(hh_meta($id, 'views')); ?> 浏览</span><?php endif; ?>
          <span><?php echo hh_icon('calendar', 15); ?> <?php echo esc_html(get_the_date('Y-m-d')); ?> 收录</span>
          <?php echo hahatool_pricing_badge(hh_meta($id, 'pricing')); ?>
        </div>
      </div>

      <?php $shot = hh_meta($id, 'screenshot') ?: 'https://s0.wp.com/mshots/v1/' . urlencode($url) . '?w=1280'; ?>
      <figure class="screenshot">
        <div class="screenshot-frame">
          <img loading="lazy" src="<?php echo esc_url($shot); ?>" alt="<?php the_title_attribute(); ?> 官网截图" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <div class="screenshot-fallback" style="display:none"><?php echo hahatool_logo($id, 56); ?><span>截图生成中，稍后再来看看</span></div>
        </div>
        <figcaption><?php the_title(); ?> 官网预览 · 截图自动生成，以官网实际为准</figcaption>
      </figure>

      <?php if ($history && count($history) > 1 || $regions): ?>
      <div class="panel">
        <h2 style="font-size:18px;display:flex;align-items:center;gap:6px"><span style="color:var(--brand-500)"><?php echo hh_icon('chart', 18); ?></span><?php the_title(); ?> 流量分析 <span class="muted" style="font-weight:400">数据由运营整理，仅供参考</span></h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-top:18px" class="ta-grid">
          <?php if (count($history) > 1): $mx = max($history); ?>
          <div>
            <h3 style="font-size:14px;color:var(--text-3)">近 <?php echo count($history); ?> 月访问量趋势</h3>
            <div class="bars" style="margin-top:12px">
              <?php foreach ($history as $v): ?><div class="b" style="height:<?php echo max(6, round($v / $mx * 110)); ?>px" title="<?php echo hahatool_count($v); ?>"></div><?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($regions): ?>
          <div>
            <h3 style="font-size:14px;color:var(--text-3);display:flex;align-items:center;gap:5px"><?php echo hh_icon('globe', 14); ?>主要地区分布</h3>
            <div style="margin-top:12px">
              <?php foreach ($regions as $r): ?><div class="region"><span class="name"><?php echo esc_html(trim($r[0])); ?></span><span class="track"><span class="fill" style="width:<?php echo min(100, (float)$r[1]); ?>%"></span></span><span class="pct tnum"><?php echo (float)$r[1]; ?>%</span></div><?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (get_the_content()): ?>
        <div class="panel prose"><?php the_content(); ?></div>
      <?php endif; ?>

      <?php if ($faq): ?>
      <div class="panel faq">
        <h2 style="font-size:18px;display:flex;align-items:center;gap:6px"><span style="color:var(--brand-500)"><?php echo hh_icon('help', 18); ?></span>关于 <?php the_title(); ?> 的常见问题</h2>
        <div style="margin-top:12px"><?php foreach ($faq as $f): ?><details><summary><?php echo esc_html(trim($f[0])); ?></summary><p><?php echo esc_html(trim($f[1])); ?></p></details><?php endforeach; ?></div>
      </div>
      <?php endif; ?>

      <div class="panel">
        <?php comments_template(); ?>
      </div>

      <?php hahatool_render_promo('detail-bottom', hahatool_promo('detail-bottom', 1, $id)); ?>
    </div>

    <aside>
      <div class="panel">
        <h2 style="font-size:16px;margin-bottom:14px">工具信息</h2>
        <dl class="dl">
          <div class="row"><dt>官网</dt><dd><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener nofollow" style="color:var(--brand-600)"><?php echo esc_html(hahatool_domain($url)); ?></a></dd></div>
          <div class="row"><dt>评分</dt><dd><?php echo hahatool_stars(hh_meta($id, 'rating')) ?: '—'; ?></dd></div>
          <div class="row"><dt>定价模式</dt><dd><?php echo esc_html(hh_meta($id, 'pricing', '—')); ?></dd></div>
          <div class="row"><dt>月访问量</dt><dd class="tnum"><?php echo hahatool_count(hh_meta($id, 'monthly_visits')); ?></dd></div>
          <div class="row"><dt>月增长</dt><dd><?php echo hahatool_growth_badge(hh_meta($id, 'growth')) ?: '—'; ?></dd></div>
          <div class="row"><dt>收藏数</dt><dd class="tnum"><?php echo hahatool_count(hh_meta($id, 'likes')); ?></dd></div>
          <div class="row"><dt>站内浏览</dt><dd class="tnum"><?php echo hahatool_count(hh_meta($id, 'views')); ?></dd></div>
          <div class="row"><dt>官网直达</dt><dd class="tnum"><?php echo hahatool_count(hh_meta($id, 'clicks')); ?></dd></div>
          <?php if ($cats): ?><div class="row"><dt>分类</dt><dd style="display:flex;flex-wrap:wrap;gap:6px;justify-content:flex-end"><?php foreach ($cats as $c): ?><a class="chip chip-brand" href="<?php echo esc_url(get_category_link($c)); ?>"><?php echo esc_html($c->name); ?></a><?php endforeach; ?></dd></div><?php endif; ?>
        </dl>
      </div>

      <?php if (count($scores) >= 3): ?>
      <div class="panel" style="margin-top:24px">
        <h2 style="font-size:16px;margin-bottom:8px">能力雷达</h2>
        <div class="radar-wrap"><?php echo hahatool_radar_svg($scores, 280); ?></div>
      </div>
      <?php endif; ?>

      <div style="margin-top:24px"><?php hahatool_render_promo('detail-side', $side_promo); ?></div>

      <?php if ($alt): ?>
      <div class="panel" style="margin-top:24px">
        <h2 style="font-size:16px;margin-bottom:10px"><?php the_title(); ?> 的替代品 <span class="muted">按流量</span></h2>
        <div class="rank-list">
          <?php foreach ($alt as $i => $t): ?>
          <a class="rank-item" href="<?php echo esc_url(get_permalink($t)); ?>">
            <span class="num"><?php echo $i + 1; ?></span>
            <?php echo hahatool_logo($t->ID, 36); ?>
            <span style="min-width:0;flex:1"><span style="display:block;font-weight:500;font-size:14px" class="truncate"><?php echo esc_html(get_the_title($t)); ?></span><span class="muted" style="font-size:12px">月访问 <?php echo hahatool_count(hh_meta($t->ID, 'monthly_visits')); ?></span></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </aside>
  </div>
</div>
<style>@media(max-width:640px){.ta-grid{grid-template-columns:1fr!important}}</style>
