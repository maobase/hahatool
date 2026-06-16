<?php
/** 分类归档：工具分类→网格；保留分类→对应频道布局 */
if (!defined('ABSPATH')) exit;
get_header();
$cat = get_queried_object();
$slug = $cat->slug;
$paged = max(1, (int)get_query_var('paged'));
?>
<div class="wrap" style="padding-top:40px">

<?php if ($slug === 'ai-prompts'): /* 提示词库 */
  $scene = isset($_GET['scene']) ? sanitize_text_field($_GET['scene']) : '';
  $all = hahatool_channel('ai-prompts', 100)->posts;
  $scenes = array_values(array_unique(array_map(fn($p) => hh_meta($p->ID, 'prompt_scene', '其他'), $all)));
  $list = $scene ? array_filter($all, fn($p) => hh_meta($p->ID, 'prompt_scene') === $scene) : $all;
  usort($list, fn($a, $b) => (float)hh_meta($b->ID, 'likes') - (float)hh_meta($a->ID, 'likes'));
?>
  <h1 class="section-title-lg" style="display:flex;align-items:center;gap:8px"><span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:12px;background:var(--brand-600);color:#fff"><?php echo hh_icon('wand', 18); ?></span>AI 提示词库</h1>
  <p class="muted">高质量中文提示词，按热度排序，点击「复制」直接粘贴给任何 AI 使用</p>
  <div class="filters" style="margin-top:20px">
    <a class="<?php echo $scene ? '' : 'on'; ?>" href="<?php echo esc_url(get_category_link($cat)); ?>">全部</a>
    <?php foreach ($scenes as $s): ?><a class="<?php echo $scene === $s ? 'on' : ''; ?>" href="<?php echo esc_url(add_query_arg('scene', urlencode($s), get_category_link($cat))); ?>"><?php echo esc_html($s); ?></a><?php endforeach; ?>
  </div>
  <div class="grid grid-3" style="margin-top:24px"><?php foreach ($list as $p) hahatool_prompt_card($p); ?></div>
  <p class="muted" style="text-align:center;margin-top:40px;font-size:12px">有好用的提示词想分享？通过 <a href="<?php echo esc_url(home_url('/submit/')); ?>" style="color:var(--brand-600)">提交页</a> 投稿给我们</p>

<?php elseif ($slug === 'ai-flash'): /* 快讯时间线（按天分组）*/ ?>
  <h1 class="section-title-lg" style="display:flex;align-items:center;gap:8px"><span style="color:var(--brand-500)"><?php echo hh_icon('zap', 26); ?></span>AI 快讯</h1>
  <p class="muted">行业即时短讯 · 按时间线更新</p>
  <div style="margin-top:28px;max-width:720px">
    <?php
    $fp = [];
    while (have_posts()) { the_post(); $fp[] = get_post(); }
    hahatool_flash_timeline($fp);
    ?>
  </div>
  <?php hahatool_pagination(max(1, (int) get_query_var('paged')), $GLOBALS['wp_query']->max_num_pages, 'get_pagenum_link'); ?>

<?php elseif ($slug === 'ai-news'): /* 资讯列表 + 侧栏 */
  $news_posts = []; while (have_posts()) { the_post(); $news_posts[] = get_post(); }
  $headline = ($paged <= 1 && $news_posts) ? array_shift($news_posts) : null;
  $side_flash = hahatool_channel('ai-flash', 6)->posts;
  $hot = hahatool_hot_tools(5);
?>
  <h1 class="section-title-lg" style="display:flex;align-items:center;gap:8px"><span style="color:var(--brand-500)"><?php echo hh_icon('newspaper', 26); ?></span>AI 资讯</h1>
  <p class="muted">行业新闻、趋势解读与工具动态</p>
  <div class="detail-grid" style="margin-top:24px">
    <div>
      <?php if ($headline): $hc = hh_meta($headline->ID, 'cover'); ?>
        <a class="news-headline" href="<?php echo esc_url(get_permalink($headline)); ?>" style="position:relative;display:block;overflow:hidden;border-radius:var(--radius);background:#111827;color:#fff;min-height:180px">
          <?php if ($hc): ?><img src="<?php echo esc_url($hc); ?>" alt="<?php echo esc_attr(get_the_title($headline)); ?>" loading="lazy" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.5"><?php endif; ?>
          <span style="position:absolute;inset:0;background:linear-gradient(to top,rgba(3,7,18,.92),transparent)"></span>
          <span style="position:relative;display:block;padding:96px 28px 28px">
            <span class="chip chip-brand" style="background:rgba(124,58,237,.3);color:#ddd6fe">头条</span>
            <span style="display:block;font-size:22px;font-weight:700;margin-top:12px"><?php echo esc_html(get_the_title($headline)); ?></span>
            <span class="muted" style="display:block;margin-top:8px"><?php echo esc_html(get_the_date('Y-m-d', $headline)); ?></span>
          </span>
        </a>
      <?php endif; ?>
      <div style="margin-top:16px;display:flex;flex-direction:column;gap:16px">
        <?php foreach ($news_posts as $np): $cover = hh_meta($np->ID, 'cover'); ?>
          <a class="news-item" href="<?php echo esc_url(get_permalink($np)); ?>">
            <div class="body"><time><?php echo esc_html(get_the_date('Y-m-d', $np)); ?></time><h3><?php echo esc_html(get_the_title($np)); ?></h3><p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($np->post_content), 50)); ?></p></div>
            <?php if ($cover): ?><img class="thumb" src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr(get_the_title($np)); ?>" loading="lazy"><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
      <?php hahatool_pagination(max(1, (int) get_query_var('paged')), $GLOBALS['wp_query']->max_num_pages, 'get_pagenum_link'); ?>
    </div>
    <aside>
      <div class="panel">
        <div style="display:flex;justify-content:space-between;align-items:center"><h2 style="font-size:16px;display:flex;align-items:center;gap:5px"><?php echo hh_icon('zap', 16); ?>AI 快讯</h2><a class="muted" style="font-size:12px" href="<?php echo esc_url(get_category_link_safe('ai-flash')); ?>">全部 →</a></div>
        <div style="margin-top:14px"><?php hahatool_flash_timeline($side_flash, true); ?></div>
      </div>
      <?php if ($hot): ?>
      <div class="panel" style="margin-top:24px">
        <h2 style="font-size:16px;margin-bottom:10px">本周热门工具</h2>
        <div class="rank-list">
          <?php foreach ($hot as $i => $t): ?>
            <a class="rank-item" href="<?php echo esc_url(get_permalink($t)); ?>"><span class="num"><?php echo $i + 1; ?></span><?php echo hahatool_logo($t->ID, 32); ?><span style="flex:1;min-width:0;font-size:14px;font-weight:500"><?php echo esc_html(get_the_title($t)); ?></span><span class="muted tnum" style="font-size:12px"><?php echo hahatool_count(hh_meta($t->ID, 'monthly_visits')); ?></span></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </aside>
  </div>

<?php else: /* 工具分类 */
  $q = hahatool_tools(['cat' => $cat->term_id, 'posts_per_page' => 24, 'paged' => max(1, get_query_var('paged')), 'no_found_rows' => false]);
?>
  <h1 class="section-title-lg"><?php echo esc_html($cat->name); ?></h1>
  <p class="muted"><?php echo esc_html($cat->description ?: '该分类下的 AI 工具'); ?> · 共 <?php echo (int)$cat->count; ?> 款</p>
  <?php if ($q->posts): ?>
    <div class="grid" style="margin-top:24px"><?php foreach ($q->posts as $p) hahatool_tool_card($p); wp_reset_postdata(); ?></div>
    <?php hahatool_pagination(max(1, (int) get_query_var('paged')), $q->max_num_pages, 'get_pagenum_link'); ?>
  <?php else: ?>
    <div class="empty" style="margin-top:24px">该分类下暂无工具</div>
  <?php endif; ?>
<?php endif; ?>

</div>
<?php get_footer();
