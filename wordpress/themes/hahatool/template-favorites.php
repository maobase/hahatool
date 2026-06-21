<?php
/** /favorites 我的收藏（localStorage，前端筛选） */
if (!defined('ABSPATH')) exit;
get_header();
$tools = hahatool_tools(['posts_per_page' => 300])->posts;
?>
<div class="wrap" style="padding-top:40px">
  <h1 class="section-title-lg" style="display:flex;align-items:center;gap:8px"><span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:12px;background:#e11d48;color:#fff"><?php echo hh_icon('heart', 18); ?></span>我的收藏</h1>
  <p class="muted">收藏保存在本机浏览器中，无需登录</p>

  <div id="favEmpty" class="empty" style="margin-top:24px;display:none">
    <div style="color:var(--text-3)"><?php echo hh_icon('heart', 40, 1.5); ?></div>
    <p style="margin-top:8px;font-weight:500;color:var(--text-2)">还没有收藏任何工具</p>
    <p>在工具卡片或详情页点击心形按钮即可收藏</p>
    <a class="btn" style="margin-top:16px;display:inline-flex" href="<?php echo esc_url(home_url('/tools/')); ?>">去逛逛工具库</a>
    <?php $fav_hot = hahatool_hot_tools(8); if ($fav_hot): ?>
    <div style="margin-top:44px;text-align:left">
      <p style="text-align:center;font-weight:600;display:flex;align-items:center;justify-content:center;gap:6px"><span style="color:#f97316"><?php echo hh_icon('flame', 16); ?></span>先从这些热门工具收藏起</p>
      <div class="grid" style="margin-top:18px"><?php foreach ($fav_hot as $t) hahatool_tool_card($t); wp_reset_postdata(); ?></div>
    </div>
    <?php endif; ?>
  </div>

  <div class="grid" id="favGrid" style="margin-top:24px;display:none">
    <?php foreach ($tools as $p): ?>
      <div data-fav-card="<?php echo (int)$p->ID; ?>"><?php hahatool_tool_card($p); wp_reset_postdata(); ?></div>
    <?php endforeach; ?>
  </div>
</div>
<script>/* 首屏即按收藏过滤，避免闪现全部 */
(function(){var f=[];try{f=JSON.parse(localStorage.getItem('hahatool:favs')||'[]')}catch(e){}
document.addEventListener('DOMContentLoaded',function(){
  var grid=document.getElementById('favGrid'),empty=document.getElementById('favEmpty'),n=0;
  grid.querySelectorAll('[data-fav-card]').forEach(function(c){var on=f.indexOf(+c.getAttribute('data-fav-card'))>=0;c.style.display=on?'':'none';if(on)n++;});
  grid.style.display=n?'grid':'none';empty.style.display=n?'none':'block';
});})();</script>
<?php get_footer();
