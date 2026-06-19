<?php if (!defined('ABSPATH')) exit; ?>
<footer class="site-footer">
  <div class="wrap foot-grid">
    <div>
      <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
        <span class="brand-logo"><?php echo hh_icon('sparkles', 15); ?></span><span><?php bloginfo('name'); ?></span>
      </a>
      <p class="muted" style="margin-top:12px;max-width:280px">发现最好用的 AI 网站和工具。精选全球优秀 AI 产品，让每个人都能找到合适的 AI 工具。</p>
    </div>
    <div>
      <h4>探索</h4>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/tools/')); ?>">全部工具</a></li>
        <li><a href="<?php echo esc_url(home_url('/ranking/')); ?>">流量排行榜</a></li>
        <li><a href="<?php echo esc_url(home_url('/ranking/?by=growth')); ?>">增长黑马榜</a></li>
        <li><a href="<?php echo esc_url(home_url('/compare/')); ?>">工具 PK 对比</a></li>
        <li><a href="<?php echo esc_url(home_url('/prompts/')); ?>">AI 提示词库</a></li>
        <li><a href="<?php echo esc_url(home_url('/topics/')); ?>">专题合集</a></li>
        <li><a href="<?php echo esc_url(home_url('/hot/')); ?>">AI · 科技热榜</a></li>
        <li><a href="<?php echo esc_url(home_url('/favorites/')); ?>">我的收藏</a></li>
        <li><a href="<?php echo esc_url(home_url('/flash/')); ?>">AI 快讯</a></li>
        <li><a href="<?php echo esc_url(home_url('/news/')); ?>">AI 资讯</a></li>
      </ul>
    </div>
    <div>
      <h4>热门分类</h4>
      <ul>
        <?php $hh_foot_cats = get_categories(['hide_empty' => true, 'exclude' => implode(',', hahatool_reserved_ids()), 'number' => 6, 'orderby' => 'count', 'order' => 'DESC']); ?>
        <?php foreach ($hh_foot_cats as $hc): ?>
          <li><a href="<?php echo esc_url(get_category_link($hc)); ?>"><?php echo esc_html($hc->name); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <h4>运营</h4>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/submit/')); ?>">提交工具</a></li>
        <li><a href="<?php echo esc_url(admin_url()); ?>" target="_blank" rel="noopener">内容管理后台</a></li>
      </ul>
    </div>
  </div>
  <div class="foot-bottom">© <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?> · WordPress 主题版 · 由 AI 构建 · 仅供学习交流</div>
</footer>
<button id="toTop" class="to-top" type="button" aria-label="返回顶部" title="返回顶部">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="18 15 12 9 6 15"/></svg>
</button>
<?php wp_footer(); ?>
</body>
</html>
