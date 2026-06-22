<?php
/** /hot 全网热榜：聚合 momoyu 多源热榜（知乎/微博/B站/IT之家/虎嗅/掘金/爱范儿等） */
if (!defined('ABSPATH')) exit;
get_header();
$hot = function_exists('hahatool_fetch_hot') ? hahatool_fetch_hot() : ['updated' => 0, 'sources' => []];
$src_count = count($hot['sources']);
$item_count = array_sum(array_map(fn($s) => count($s['items']), $hot['sources']));

// 结构化数据：CollectionPage + 面包屑（SEO）
$hot_ld = ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => '全网热榜', 'url' => home_url('/hot/'), 'description' => '聚合知乎、微博、B站、IT之家、虎嗅、掘金、爱范儿等全网站点热榜，实时追踪科技与社会热点。'];
$hot_crumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
    ['@type' => 'ListItem', 'position' => 1, 'name' => '首页', 'item' => home_url('/')],
    ['@type' => 'ListItem', 'position' => 2, 'name' => '全网热榜', 'item' => home_url('/hot/')],
]];
?>
<script type="application/ld+json"><?php echo wp_json_encode($hot_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<script type="application/ld+json"><?php echo wp_json_encode($hot_crumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<div class="wrap" style="padding-top:32px">
  <nav class="crumb"><a href="<?php echo esc_url(home_url('/')); ?>">首页</a> / <span style="color:var(--text-2)">全网热榜</span></nav>
  <h1 class="section-title-lg" style="display:flex;align-items:center;gap:8px;margin-top:12px"><span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:12px;background:var(--brand-600);color:#fff"><?php echo hh_icon('flame', 18); ?></span>全网热榜</h1>
  <p class="muted">聚合知乎 / 微博 / B站 / IT之家 / 虎嗅 / 掘金 / 爱范儿 / 中关村等全网站点热榜 · 每 5 分钟更新</p>

  <?php if (!empty($hot['sources'])): ?>
  <div class="dir-stats">
    <div><div class="n tnum"><?php echo (int) $src_count; ?></div><div class="l">站点源</div></div>
    <div><div class="n tnum"><?php echo (int) $item_count; ?></div><div class="l">实时热点</div></div>
    <div><div class="n tnum">5<span>min</span></div><div class="l">更新频率</div></div>
    <div><div class="n tnum"><?php echo !empty($hot['updated']) ? esc_html(wp_date('H:i', $hot['updated'])) : '—'; ?></div><div class="l">最近更新</div></div>
  </div>
  <?php endif; ?>

  <?php if (empty($hot['sources'])): ?>
    <div class="empty" style="margin-top:24px">热榜暂时拉取失败，稍后再来看看。</div>
  <?php else: ?>
  <div class="hot-grid" style="margin-top:24px">
    <?php foreach ($hot['sources'] as $s): ?>
      <section class="hot-card panel">
        <h2 class="hot-card-head"><span class="hot-dot" style="background:<?php echo esc_attr($s['color']); ?>"></span><?php echo esc_html($s['name']); ?></h2>
        <ol class="hot-list">
          <?php foreach ($s['items'] as $i => $it): ?>
            <li><a href="<?php echo esc_url($it['link']); ?>" target="_blank" rel="noopener nofollow">
              <span class="hot-rank<?php echo $i < 3 ? ' top' : ''; ?>"><?php echo $i + 1; ?></span>
              <span class="hot-title"><?php echo esc_html($it['title']); ?></span>
              <?php if ($it['extra']): ?><span class="hot-extra"><?php echo esc_html($it['extra']); ?></span><?php endif; ?>
            </a></li>
          <?php endforeach; ?>
        </ol>
      </section>
    <?php endforeach; ?>
  </div>
  <p class="muted" style="text-align:center;margin-top:28px;font-size:12px">数据来源：momoyu.cc · 服务端缓存 5 分钟 · 仅聚合标题与链接，版权归原站</p>
  <?php endif; ?>
</div>
<?php get_footer();
