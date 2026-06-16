<?php
/** 首页 */
if (!defined('ABSPATH')) exit;
get_header();

$all = hahatool_tools(['posts_per_page' => 200]);
$tools = $all->posts;
$tool_cats = get_categories(['hide_empty' => true, 'exclude' => implode(',', hahatool_reserved_ids())]);
$week_ago = time() - 7 * 86400;
$weekly_new = count(array_filter($tools, fn($p) => strtotime($p->post_date_gmt . ' UTC') > $week_ago));

// 派生板块
$banners = array_slice(array_values(array_filter($tools, fn($p) => hh_meta($p->ID, 'banner') === '1')), 0, 2);
$featured = array_values(array_filter($tools, fn($p) => hh_meta($p->ID, 'featured') === '1'));
usort($tools, fn($a, $b) => (float)hh_meta($b->ID, 'growth') - (float)hh_meta($a->ID, 'growth'));
$trending = array_slice($tools, 0, 4);
$latest = $all->posts; // 已按日期倒序

$flash = hahatool_channel('ai-flash', 8)->posts;
$news = hahatool_channel('ai-news', 3)->posts;
$prompts_q = hahatool_channel('ai-prompts', 100)->posts;
usort($prompts_q, fn($a, $b) => (float)hh_meta($b->ID, 'likes') - (float)hh_meta($a->ID, 'likes'));
$hot_prompts = array_slice($prompts_q, 0, 3);
$mid_promo = hahatool_promo('home-mid', 1);

$hot_kw = ['AI写作', 'AI绘画', '视频生成', 'AI编程', '数字人', 'AI音乐'];
$tags = get_tags(['hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 16]);
?>

<section class="hero">
  <div class="wrap hero-inner">
    <span class="pill" style="display:inline-flex;align-items:center;gap:5px"><?php echo hh_icon('sparkles', 12); ?>全球 AI 工具中文导航 · 每日更新</span>
    <h1>发现最好用的 <span class="hl">AI 工具</span></h1>
    <p class="sub">收录全球优秀 AI 产品，附流量数据、定价与真实点评，帮你少踩坑、快上手。</p>
    <form class="hero-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
      <span class="s-icon" style="display:inline-flex"><?php echo hh_icon('search', 20); ?></span>
      <input type="search" name="s" placeholder="搜索 AI 工具，例如：视频生成、写作助手…" aria-label="搜索" data-suggest autocomplete="off">
      <button class="btn" type="submit">搜索</button>
    </form>
    <div class="hero-tags">
      <span>热门：</span>
      <?php foreach ($hot_kw as $kw): ?>
        <a href="<?php echo esc_url(home_url('/?s=' . urlencode($kw))); ?>"><?php echo esc_html($kw); ?></a>
      <?php endforeach; ?>
    </div>
    <div class="hero-stats">
      <div><div class="n tnum"><?php echo count($all->posts); ?><span>+</span></div><div class="l">收录工具</div></div>
      <div><div class="n tnum"><?php echo count($tool_cats); ?><span>+</span></div><div class="l">工具分类</div></div>
      <div><div class="n tnum"><?php echo $weekly_new; ?><span>+</span></div><div class="l">本周新增</div></div>
    </div>
  </div>
</section>

<?php if ($flash): ?>
<div class="ticker">
  <div class="ticker-inner">
    <a class="ticker-badge" href="<?php echo esc_url(get_category_link_safe('ai-flash')); ?>" style="display:inline-flex;align-items:center;gap:4px"><?php echo hh_icon('zap', 11); ?>快讯</a>
    <div class="ticker-mask"><div class="ticker-track">
      <?php foreach (array_merge($flash, $flash) as $f): ?><a href="<?php echo esc_url(get_permalink($f)); ?>"><?php echo esc_html(get_the_title($f)); ?></a><?php endforeach; ?>
    </div></div>
    <a href="<?php echo esc_url(get_category_link_safe('ai-flash')); ?>" class="muted" style="flex-shrink:0;font-size:12px">全部 →</a>
  </div>
</div>
<?php endif; ?>

<nav class="catbar">
  <div class="catbar-inner">
    <a href="#featured">精选</a>
    <a href="#trending" style="display:inline-flex;align-items:center;gap:4px"><span style="color:#f97316"><?php echo hh_icon('flame', 14); ?></span>增长最快</a>
    <?php foreach ($tool_cats as $c): ?>
      <a href="#cat-<?php echo esc_attr($c->slug); ?>"><?php echo esc_html($c->name); ?></a>
    <?php endforeach; ?>
  </div>
</nav>

<main class="wrap">
  <?php if (!$tools): ?>
    <div class="empty">还没有任何工具数据。请在后台发布带 <code>url</code> 字段的文章，或运行示例数据导入。</div>
  <?php else: ?>

    <?php if ($banners): ?>
    <section class="section" aria-label="推广位">
      <div class="grid" style="grid-template-columns:repeat(2,1fr)" id="bannerGrid">
        <?php foreach ($banners as $bp): $bid = $bp->ID; ?>
          <div class="hero-banner">
            <span class="tag" style="display:inline-flex;align-items:center;gap:4px"><?php echo hh_icon('megaphone', 11); ?>推广</span>
            <div style="display:flex;align-items:center;gap:16px">
              <?php echo hahatool_logo($bid, 56); ?>
              <div><h3 style="font-size:20px"><?php echo esc_html(get_the_title($bid)); ?></h3><p style="margin:4px 0 0;color:rgba(255,255,255,.8);font-size:14px;max-width:380px" class="clamp2"><?php echo esc_html(hh_meta($bid, 'tagline')); ?></p></div>
            </div>
            <div style="margin-top:20px;display:flex;gap:12px">
              <a class="btn" style="background:#fff;color:var(--brand-700)" href="<?php echo esc_url(hh_meta($bid, 'url')); ?>" target="_blank" rel="noopener nofollow" data-track-click="<?php echo (int)$bid; ?>">立即体验<?php echo hh_icon('arrow-right', 15); ?></a>
              <a class="btn btn-ghost" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.4)" href="<?php echo esc_url(get_permalink($bid)); ?>">查看详情</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($featured): ?>
    <section class="section" id="featured">
      <div class="section-head"><div><h2>编辑精选</h2><div class="sub">运营团队为你挑选的优质 AI 工具</div></div><a class="more" href="<?php echo esc_url(home_url('/tools/')); ?>">查看全部<?php echo hh_icon('chevron-right', 16); ?></a></div>
      <div class="grid">
        <?php foreach (array_slice($featured, 0, 8) as $p) hahatool_tool_card($p); wp_reset_postdata(); ?>
      </div>
    </section>
    <?php endif; ?>

    <section class="section" id="trending">
      <div class="section-head"><div><h2>增长最快</h2><div class="sub">本月访问量增速最高的黑马工具</div></div><a class="more" href="<?php echo esc_url(home_url('/ranking/?by=growth')); ?>">查看增长榜<?php echo hh_icon('chevron-right', 16); ?></a></div>
      <div class="grid">
        <?php foreach ($trending as $i => $p) hahatool_tool_card($p, 'NO.' . ($i + 1)); wp_reset_postdata(); ?>
      </div>
    </section>

    <?php if ($mid_promo): ?>
      <section class="section"><?php hahatool_render_promo('home-mid', $mid_promo); ?></section>
    <?php endif; ?>

    <section class="section">
      <div class="section-head"><div><h2>最新收录</h2><div class="sub">刚刚加入 HahaTool 的新工具</div></div><a class="more" href="<?php echo esc_url(home_url('/tools/')); ?>">查看全部<?php echo hh_icon('chevron-right', 16); ?></a></div>
      <div class="grid">
        <?php foreach (array_slice($latest, 0, 8) as $p) hahatool_tool_card($p); wp_reset_postdata(); ?>
      </div>
    </section>

    <?php foreach ($tool_cats as $cat):
      $cq = hahatool_tools(['posts_per_page' => 4, 'category__and' => [], 'cat' => $cat->term_id]);
      if (!$cq->posts) continue; ?>
      <section class="section" id="cat-<?php echo esc_attr($cat->slug); ?>">
        <div class="section-head"><div><h2><?php echo esc_html($cat->name); ?></h2><div class="sub"><?php echo esc_html($cat->description); ?></div></div><a class="more" href="<?php echo esc_url(get_category_link($cat)); ?>">全部 <?php echo (int)$cat->count; ?> 款<?php echo hh_icon('chevron-right', 16); ?></a></div>
        <div class="grid"><?php foreach ($cq->posts as $p) hahatool_tool_card($p); wp_reset_postdata(); ?></div>
      </section>
    <?php endforeach; ?>

    <?php if ($hot_prompts): ?>
    <section class="section">
      <div class="section-head"><div><h2>热门提示词</h2><div class="sub">复制即用的高质量中文 Prompt</div></div><a class="more" href="<?php echo esc_url(get_category_link_safe('ai-prompts')); ?>">进入提示词库<?php echo hh_icon('chevron-right', 16); ?></a></div>
      <div class="grid grid-3"><?php foreach ($hot_prompts as $p) hahatool_prompt_card($p); wp_reset_postdata(); ?></div>
    </section>
    <?php endif; ?>

    <?php if ($tags): ?>
    <section class="section">
      <div class="section-head"><div><h2>按标签找工具</h2><div class="sub">从使用场景出发，快速定位同类工具</div></div></div>
      <div class="tagcloud-grid">
        <?php foreach ($tags as $t): ?><a href="<?php echo esc_url(get_tag_link($t)); ?>"># <?php echo esc_html($t->name); ?><b><?php echo (int)$t->count; ?></b></a><?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($news): ?>
    <section class="section">
      <div class="section-head"><div><h2>AI 资讯</h2><div class="sub">行业新闻与趋势解读</div></div><a class="more" href="<?php echo esc_url(get_category_link_safe('ai-news')); ?>">查看全部<?php echo hh_icon('chevron-right', 16); ?></a></div>
      <div class="grid grid-3">
        <?php foreach ($news as $p): $nc = hh_meta($p->ID, 'cover'); ?>
          <a class="card news-card" href="<?php echo esc_url(get_permalink($p)); ?>">
            <?php if ($nc): ?><img class="news-cover" src="<?php echo esc_url($nc); ?>" alt="<?php echo esc_attr(get_the_title($p)); ?>" loading="lazy"><?php endif; ?>
            <div class="news-body">
              <time class="muted"><?php echo esc_html(get_the_date('Y-m-d', $p)); ?></time>
              <h3 style="margin-top:8px"><?php echo esc_html(get_the_title($p)); ?></h3>
              <p class="tagline"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($p->post_content), 40)); ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  <?php endif; ?>
</main>

<?php get_footer();
