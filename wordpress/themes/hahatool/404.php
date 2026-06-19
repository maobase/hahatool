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
</div>
<?php get_footer();
