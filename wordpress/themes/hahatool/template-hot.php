<?php
/** /hot 热榜：聚合 momoyu 多源热榜（知乎/IT之家/虎嗅/掘金/爱范儿等） */
if (!defined('ABSPATH')) exit;
get_header();
$hot = function_exists('hahatool_fetch_hot') ? hahatool_fetch_hot() : ['updated' => 0, 'sources' => []];
?>
<div class="wrap" style="padding-top:40px">
  <h1 class="section-title-lg" style="display:flex;align-items:center;gap:8px"><span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:12px;background:var(--brand-600);color:#fff"><?php echo hh_icon('flame', 18); ?></span>AI · 科技热榜</h1>
  <p class="muted">聚合知乎 / IT之家 / 虎嗅 / 掘金 / 爱范儿 / 中关村等站点热榜 · 实时追踪 AI 与科技热点<?php if (!empty($hot['updated'])): ?> · 更新于 <?php echo esc_html(wp_date('H:i', $hot['updated'])); ?><?php endif; ?></p>

  <?php if (empty($hot['sources'])): ?>
    <div class="empty" style="margin-top:24px">热榜暂时拉取失败，稍后再来看看。</div>
  <?php else: ?>
  <div class="hot-grid">
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
  <p class="muted" style="text-align:center;margin-top:28px;font-size:12px">数据来源：momoyu.cc · 服务端缓存 15 分钟 · 仅聚合标题与链接，版权归原站</p>
  <?php endif; ?>
</div>
<?php get_footer();
