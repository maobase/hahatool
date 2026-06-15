<?php
/** /compare 工具 PK：双雷达 + 数据对比表 */
if (!defined('ABSPATH')) exit;
get_header();
$tools = hahatool_tools(['posts_per_page' => 300])->posts;
usort($tools, fn($a, $b) => (float)hh_meta($b->ID, 'monthly_visits') - (float)hh_meta($a->ID, 'monthly_visits'));
$bySlug = [];
foreach ($tools as $p) $bySlug[$p->post_name] = $p;
$a = isset($_GET['a']) ? sanitize_title($_GET['a']) : '';
$b = isset($_GET['b']) ? sanitize_title($_GET['b']) : '';
$A = $bySlug[$a] ?? ($tools[0] ?? null);
$B = $bySlug[$b] ?? null;
if ($A && (!$B || $B->ID === $A->ID)) { foreach ($tools as $t) if ($t->ID !== $A->ID) { $B = $t; break; } }
$CA = 'var(--brand-600)'; $CB = '#f59e0b';
$opts = $tools;
usort($opts, fn($x, $y) => strcmp($x->post_title, $y->post_title));

function pk_win($a, $b, $hi = true) {
    if (!$a || !$b || $a == $b) return '';
    $aw = $hi ? $a > $b : $a < $b;
    return ' <span class="badge" style="background:' . ($aw ? 'var(--brand-500)' : '#f59e0b') . ';color:#fff">' . ($aw ? 'A 胜' : 'B 胜') . '</span>';
}
?>
<div class="wrap" style="padding-top:40px;max-width:1000px">
  <h1 class="section-title-lg">⚔️ 工具 PK</h1>
  <p class="muted">任选两款工具，能力雷达、流量与定价一屏对比</p>

  <?php if (!$A || !$B): ?>
    <div class="empty" style="margin-top:24px">暂无足够的工具数据可供对比。</div>
  <?php else: ?>
  <form method="get" action="<?php echo esc_url(home_url('/compare/')); ?>" class="panel" style="margin-top:20px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
    <div style="flex:1;min-width:160px"><label class="muted" style="display:block;margin-bottom:4px"><span style="color:var(--brand-600)">●</span> 选手 A</label>
      <select name="a" style="width:100%;padding:10px;border-radius:12px;border:1px solid var(--border);background:var(--surface);color:var(--text)"><?php foreach ($opts as $t): ?><option value="<?php echo esc_attr($t->post_name); ?>" <?php selected($t->ID, $A->ID); ?>><?php echo esc_html($t->post_title); ?></option><?php endforeach; ?></select></div>
    <b class="display muted">VS</b>
    <div style="flex:1;min-width:160px"><label class="muted" style="display:block;margin-bottom:4px"><span style="color:#f59e0b">●</span> 选手 B</label>
      <select name="b" style="width:100%;padding:10px;border-radius:12px;border:1px solid var(--border);background:var(--surface);color:var(--text)"><?php foreach ($opts as $t): ?><option value="<?php echo esc_attr($t->post_name); ?>" <?php selected($t->ID, $B->ID); ?>><?php echo esc_html($t->post_title); ?></option><?php endforeach; ?></select></div>
    <button class="btn" type="submit">开始对比</button>
  </form>

  <div class="vs-grid" style="margin-top:20px">
    <?php foreach ([['A', $A, $CA], ['B', $B, $CB]] as [$side, $T, $col]): ?>
      <div class="card" style="border:2px solid <?php echo $col; ?>">
        <div class="card-top"><?php echo hahatool_logo($T->ID, 52); ?><div><div style="display:flex;gap:8px;align-items:center"><span class="badge display" style="background:<?php echo $col; ?>;color:#fff"><?php echo $side; ?></span><a href="<?php echo esc_url(get_permalink($T)); ?>"><b><?php echo esc_html($T->post_title); ?></b></a></div><?php echo hahatool_stars(hh_meta($T->ID, 'rating')); ?></div><span class="spacer"><?php echo hahatool_growth_badge(hh_meta($T->ID, 'growth')); ?></span></div>
        <p class="tagline"><?php echo esc_html(hh_meta($T->ID, 'tagline')); ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="detail-grid" style="margin-top:24px">
    <?php $sa = hahatool_scores($A->ID); $sb = hahatool_scores($B->ID); if (count($sa) >= 3 || count($sb) >= 3): ?>
    <div class="panel">
      <h2 style="font-size:16px">能力雷达对比</h2><p class="muted">五维评分（0-10），由运营团队评估</p>
      <div class="radar-wrap"><?php echo hahatool_radar_dual($sa, $sb, 300, $CA, $CB); ?></div>
      <div class="legend"><span><i style="background:<?php echo $CA; ?>"></i><?php echo esc_html($A->post_title); ?></span><span><i style="background:<?php echo $CB; ?>"></i><?php echo esc_html($B->post_title); ?></span></div>
    </div>
    <?php endif; ?>
    <div class="panel">
      <h2 style="font-size:16px;margin-bottom:12px">数据对比</h2>
      <div class="cmp-table-wrap">
      <table style="width:100%;min-width:320px;font-size:14px;border-collapse:collapse">
        <thead><tr style="color:var(--text-3);font-size:12px;text-align:left"><th style="padding:6px 0;width:72px">指标</th><th style="color:<?php echo $CA; ?>"><?php echo esc_html($A->post_title); ?></th><th style="color:<?php echo $CB; ?>"><?php echo esc_html($B->post_title); ?></th></tr></thead>
        <tbody>
          <?php
          $rows = [
            ['评分', number_format((float)hh_meta($A->ID, 'rating'), 1), number_format((float)hh_meta($B->ID, 'rating'), 1), (float)hh_meta($A->ID, 'rating'), (float)hh_meta($B->ID, 'rating')],
            ['月访问量', hahatool_count(hh_meta($A->ID, 'monthly_visits')), hahatool_count(hh_meta($B->ID, 'monthly_visits')), (float)hh_meta($A->ID, 'monthly_visits'), (float)hh_meta($B->ID, 'monthly_visits')],
            ['月增长率', hh_meta($A->ID, 'growth') . '%', hh_meta($B->ID, 'growth') . '%', (float)hh_meta($A->ID, 'growth'), (float)hh_meta($B->ID, 'growth')],
            ['收藏数', hahatool_count(hh_meta($A->ID, 'likes')), hahatool_count(hh_meta($B->ID, 'likes')), (float)hh_meta($A->ID, 'likes'), (float)hh_meta($B->ID, 'likes')],
            ['定价模式', hh_meta($A->ID, 'pricing', '—'), hh_meta($B->ID, 'pricing', '—'), null, null],
          ];
          foreach ($rows as $r): ?>
            <tr style="border-top:1px solid var(--border-2)"><td style="padding:10px 0;color:var(--text-3)"><?php echo esc_html($r[0]); ?></td><td class="tnum" style="font-weight:500"><?php echo esc_html($r[1]); echo $r[3] !== null ? pk_win($r[3], $r[4]) : ''; ?></td><td class="tnum" style="font-weight:500"><?php echo esc_html($r[2]); ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
  <p class="muted" style="text-align:center;margin-top:24px">数据由运营团队整理，仅供参考 · 在上方选择器中任意切换工具</p>
  <?php endif; ?>
</div>
<?php get_footer();
