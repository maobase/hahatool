<?php
/** 首页 */
if (!defined('ABSPATH')) exit;
get_header();

$all = hahatool_tools(['posts_per_page' => 200]);
$tools = $all->posts;
$tool_cats = get_categories(['hide_empty' => true, 'exclude' => implode(',', hahatool_reserved_ids())]);
$week_ago = time() - 7 * 86400;
$weekly_new = count(array_filter($tools, fn($p) => strtotime($p->post_date_gmt . ' UTC') > $week_ago));
// 第三项 hero 统计：优先「本周新增」，为 0 时回退「AI 资讯」总数，避免 hero 出现「0」
$hh_news_total = (int) (($hh_nc = get_category_by_slug('ai-news')) ? $hh_nc->count : 0);
if ($weekly_new > 0) { $stat3_n = $weekly_new; $stat3_l = '本周新增'; }
else { $stat3_n = $hh_news_total; $stat3_l = 'AI 资讯'; }

// 派生板块
$banners = array_slice(array_values(array_filter($tools, fn($p) => hh_meta($p->ID, 'banner') === '1')), 0, 2);
$featured = array_values(array_filter($tools, fn($p) => hh_meta($p->ID, 'featured') === '1'));
usort($tools, fn($a, $b) => (float)hh_meta($b->ID, 'growth') - (float)hh_meta($a->ID, 'growth'));
$trending = array_slice($tools, 0, 4);
$latest = $all->posts; // 已按日期倒序

$flash = hahatool_channel('ai-flash', 8)->posts;
$news = hahatool_channel('ai-news', 5)->posts;
$prompts_q = hahatool_channel('ai-prompts', 100)->posts;
usort($prompts_q, fn($a, $b) => (float)hh_meta($b->ID, 'likes') - (float)hh_meta($a->ID, 'likes'));
$hot_prompts = array_slice($prompts_q, 0, 3);
$mid_promo = hahatool_promo('home-mid', 1);
$home_topics = get_terms(['taxonomy' => 'topic', 'hide_empty' => true, 'number' => 3, 'orderby' => 'count', 'order' => 'DESC']);

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
      <div><div class="n tnum"><?php echo count($all->posts); ?><?php if (count($all->posts) > 0): ?><span>+</span><?php endif; ?></div><div class="l">收录工具</div></div>
      <div><div class="n tnum"><?php echo count($tool_cats); ?><?php if (count($tool_cats) > 0): ?><span>+</span><?php endif; ?></div><div class="l">工具分类</div></div>
      <div><div class="n tnum"><?php echo $stat3_n; ?><?php if ($stat3_n > 0): ?><span>+</span><?php endif; ?></div><div class="l"><?php echo esc_html($stat3_l); ?></div></div>
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
              <div><div style="font-size:20px;font-weight:700"><?php echo esc_html(get_the_title($bid)); ?></div><p style="margin:4px 0 0;color:rgba(255,255,255,.8);font-size:14px;max-width:380px" class="clamp2"><?php echo esc_html(hh_meta($bid, 'tagline')); ?></p></div>
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

    <?php if (!empty($home_topics) && !is_wp_error($home_topics)): ?>
    <section class="section">
      <div class="section-head"><div><h2>精选专题</h2><div class="sub">按场景策划的 AI 工具合集</div></div><a class="more" href="<?php echo esc_url(home_url('/topics/')); ?>">全部专题<?php echo hh_icon('chevron-right', 16); ?></a></div>
      <div class="topic-grid">
        <?php foreach ($home_topics as $t): $cover = get_term_meta($t->term_id, 'topic_cover', true); ?>
          <a class="topic-card" href="<?php echo esc_url(get_term_link($t)); ?>">
            <span class="topic-card-cover"<?php if ($cover): ?> style="background-image:url('<?php echo esc_url($cover); ?>')"<?php endif; ?>></span>
            <span class="topic-card-body">
              <span class="topic-card-title"><?php echo esc_html($t->name); ?></span>
              <span class="topic-card-desc"><?php echo esc_html($t->description); ?></span>
              <span class="muted" style="font-size:12px;display:inline-flex;align-items:center;gap:4px;margin-top:auto"><?php echo hh_icon('layers', 12); ?><?php echo (int) $t->count; ?> 个工具</span>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($mid_promo): ?>
      <section class="section"><?php hahatool_render_promo('home-mid', $mid_promo); ?></section>
    <?php endif; ?>

    <section class="section">
      <div class="section-head"><div><h2>最新收录</h2><div class="sub">刚刚加入 HahaTool 的新工具</div></div><a class="more" href="<?php echo esc_url(home_url('/tools/')); ?>">查看全部<?php echo hh_icon('chevron-right', 16); ?></a></div>
      <div class="grid">
        <?php foreach (array_slice($latest, 0, 8) as $p) hahatool_tool_card($p); wp_reset_postdata(); ?>
      </div>
    </section>

    <?php
    // 分类区：热门 5 类做完整橱窗，其余压缩为分类胶囊，避免首页过长（其余分类仍可一键直达）
    $cats_sorted = $tool_cats;
    usort($cats_sorted, fn($a, $b) => $b->count - $a->count);
    $cats_full = array_slice($cats_sorted, 0, 5);
    $cats_rest = array_slice($cats_sorted, 5);
    foreach ($cats_full as $cat):
      $cq = hahatool_tools(['posts_per_page' => 4, 'cat' => $cat->term_id]);
      if (!$cq->posts) continue; ?>
      <section class="section" id="cat-<?php echo esc_attr($cat->slug); ?>">
        <div class="section-head"><div><h2><?php echo esc_html($cat->name); ?></h2><div class="sub"><?php echo esc_html($cat->description); ?></div></div><a class="more" href="<?php echo esc_url(get_category_link($cat)); ?>">全部 <?php echo (int)$cat->count; ?> 款<?php echo hh_icon('chevron-right', 16); ?></a></div>
        <div class="grid"><?php foreach ($cq->posts as $p) hahatool_tool_card($p); wp_reset_postdata(); ?></div>
      </section>
    <?php endforeach; ?>
    <?php if ($cats_rest): ?>
    <section class="section">
      <div class="section-head"><div><h2>更多分类</h2><div class="sub">浏览全部 AI 工具品类</div></div><a class="more" href="<?php echo esc_url(home_url('/tools/')); ?>">全部工具<?php echo hh_icon('chevron-right', 16); ?></a></div>
      <div class="tagcloud-grid">
        <?php foreach ($cats_rest as $c): ?><a href="<?php echo esc_url(get_category_link($c)); ?>"><?php echo esc_html($c->name); ?><b><?php echo (int) $c->count; ?></b></a><?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

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
      <div class="news-feature-grid">
        <?php $feat = $news[0]; $fc = hh_meta($feat->ID, 'cover'); ?>
        <a class="card news-card" href="<?php echo esc_url(get_permalink($feat)); ?>">
          <?php if ($fc): ?><img class="news-cover" style="aspect-ratio:16/9" src="<?php echo esc_url($fc); ?>" alt="<?php echo esc_attr(get_the_title($feat)); ?>" loading="lazy"><?php endif; ?>
          <div class="news-body">
            <div class="news-meta"><time><?php echo esc_html(get_the_date('Y-m-d', $feat)); ?></time><span>·</span><span class="rt"><?php echo hh_icon('clock', 12); ?><?php echo (int) hahatool_read_time($feat->post_content); ?> 分钟阅读</span></div>
            <h3 style="font-size:18px;margin-top:8px"><?php echo esc_html(get_the_title($feat)); ?></h3>
            <p class="tagline"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($feat->post_content), 40)); ?></p>
          </div>
        </a>
        <?php if (count($news) > 1): ?>
        <div class="news-list">
          <?php foreach (array_slice($news, 1, 4) as $p): $nc = hh_meta($p->ID, 'cover'); ?>
            <a class="news-list-item" href="<?php echo esc_url(get_permalink($p)); ?>">
              <?php if ($nc): ?><img class="news-list-thumb" src="<?php echo esc_url($nc); ?>" alt="<?php echo esc_attr(get_the_title($p)); ?>" loading="lazy"><?php endif; ?>
              <div style="min-width:0;flex:1">
                <h4><?php echo esc_html(get_the_title($p)); ?></h4>
                <div class="news-meta" style="margin-top:4px"><time><?php echo esc_html(get_the_date('Y-m-d', $p)); ?></time><span>·</span><span class="rt"><?php echo hh_icon('clock', 12); ?><?php echo (int) hahatool_read_time($p->post_content); ?> 分钟阅读</span></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php
    // AI·科技热榜 首页 teaser：取前 3 源各 6 条。用默认 $per 与 /hot 共享服务端缓存，避免缓存键冲突；拉取失败则不渲染。
    $home_hot = function_exists('hahatool_fetch_hot') ? hahatool_fetch_hot() : ['sources' => []];
    $home_hot_src = array_slice($home_hot['sources'] ?? [], 0, 3);
    if ($home_hot_src): ?>
    <section class="section">
      <div class="section-head"><div><h2 style="display:flex;align-items:center;gap:6px"><span style="color:#f97316"><?php echo hh_icon('flame', 20); ?></span>AI · 科技热榜</h2><div class="sub">聚合知乎 / IT之家 / 虎嗅等全网热点 · 实时追踪<?php if (!empty($home_hot['updated'])): ?> · 更新于 <?php echo esc_html(wp_date('H:i', $home_hot['updated'])); ?><?php endif; ?></div></div><a class="more" href="<?php echo esc_url(home_url('/hot/')); ?>">完整热榜<?php echo hh_icon('chevron-right', 16); ?></a></div>
      <div class="hot-grid">
        <?php foreach ($home_hot_src as $s): ?>
          <section class="hot-card panel">
            <h3 class="hot-card-head"><span class="hot-dot" style="background:<?php echo esc_attr($s['color']); ?>"></span><?php echo esc_html($s['name']); ?></h3>
            <ol class="hot-list">
              <?php foreach (array_slice($s['items'], 0, 6) as $i => $it): ?>
                <li><a href="<?php echo esc_url($it['link']); ?>" target="_blank" rel="noopener nofollow">
                  <span class="hot-rank<?php echo $i < 3 ? ' top' : ''; ?>"><?php echo $i + 1; ?></span>
                  <span class="hot-title"><?php echo esc_html($it['title']); ?></span>
                </a></li>
              <?php endforeach; ?>
            </ol>
          </section>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  <?php endif; ?>
</main>

<?php get_footer();
