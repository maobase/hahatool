<?php
/** 404 */
if (!defined('ABSPATH')) exit;
get_header();
?>
<div class="wrap" style="padding:96px 24px;text-align:center">
  <p class="display" style="font-size:72px;font-weight:800;color:var(--brand-200)">404</p>
  <h1 style="margin-top:8px;font-size:22px">页面不存在</h1>
  <p class="muted" style="margin-top:8px">你访问的内容可能已被删除或尚未收录。</p>
  <div style="margin-top:24px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a class="btn" href="<?php echo esc_url(home_url('/')); ?>">返回首页</a>
    <a class="btn btn-ghost" href="<?php echo esc_url(home_url('/tools/')); ?>">浏览工具库</a>
  </div>
</div>
<?php get_footer();
