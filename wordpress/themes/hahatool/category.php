<?php
/** 分类归档：工具分类→网格；保留分类→对应频道布局 */
if (!defined('ABSPATH')) exit;
get_header();
$cat = get_queried_object();
$slug = $cat->slug;
?>
<div class="wrap" style="padding-top:40px">

<?php if ($slug === 'ai-prompts'): /* 提示词库 */
  $scene = isset($_GET['scene']) ? sanitize_text_field($_GET['scene']) : '';
  $all = hahatool_channel('ai-prompts', 100)->posts;
  $scenes = array_values(array_unique(array_map(fn($p) => hh_meta($p->ID, 'prompt_scene', '其他'), $all)));
  $list = $scene ? array_filter($all, fn($p) => hh_meta($p->ID, 'prompt_scene') === $scene) : $all;
  usort($list, fn($a, $b) => (float)hh_meta($b->ID, 'likes') - (float)hh_meta($a->ID, 'likes'));
?>
  <h1 class="section-title-lg">✍️ AI 提示词库</h1>
  <p class="muted">高质量中文提示词，按热度排序，点「复制」直接粘贴给任何 AI 使用</p>
  <div class="filters" style="margin-top:20px">
    <a class="<?php echo $scene ? '' : 'on'; ?>" href="<?php echo esc_url(get_category_link($cat)); ?>">全部</a>
    <?php foreach ($scenes as $s): ?><a class="<?php echo $scene === $s ? 'on' : ''; ?>" href="<?php echo esc_url(add_query_arg('scene', urlencode($s), get_category_link($cat))); ?>"><?php echo esc_html($s); ?></a><?php endforeach; ?>
  </div>
  <div class="grid grid-3" style="margin-top:24px"><?php foreach ($list as $p) hahatool_prompt_card($p); ?></div>

<?php elseif ($slug === 'ai-flash'): /* 快讯时间线 */ ?>
  <h1 class="section-title-lg">⚡ AI 快讯</h1>
  <p class="muted">行业即时短讯 · 按时间线更新</p>
  <div class="flash" style="margin-top:28px;max-width:680px">
    <?php while (have_posts()): the_post(); ?>
      <div class="it"><time><?php echo esc_html(get_the_date('n月j日 H:i')); ?></time><a href="<?php the_permalink(); ?>"><p><?php the_title(); ?></p></a></div>
    <?php endwhile; ?>
  </div>
  <?php the_posts_pagination(['mid_size' => 1]); ?>

<?php elseif ($slug === 'ai-news'): /* 资讯列表 */ ?>
  <h1 class="section-title-lg">📰 AI 资讯</h1>
  <p class="muted">行业新闻、趋势解读与工具动态</p>
  <div style="margin-top:24px;display:flex;flex-direction:column;gap:16px">
    <?php while (have_posts()): the_post(); $cover = hh_meta(get_the_ID(), 'cover'); ?>
      <a class="news-item" href="<?php the_permalink(); ?>">
        <div class="body"><time><?php echo esc_html(get_the_date('Y-m-d')); ?></time><h3><?php the_title(); ?></h3><p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_content()), 50)); ?></p></div>
        <?php if ($cover): ?><img class="thumb" src="<?php echo esc_url($cover); ?>" alt=""><?php endif; ?>
      </a>
    <?php endwhile; ?>
  </div>
  <?php the_posts_pagination(['mid_size' => 1]); ?>

<?php else: /* 工具分类 */
  $q = hahatool_tools(['cat' => $cat->term_id, 'posts_per_page' => 24, 'paged' => max(1, get_query_var('paged')), 'no_found_rows' => false]);
?>
  <h1 class="section-title-lg"><?php echo esc_html($cat->name); ?></h1>
  <p class="muted"><?php echo esc_html($cat->description ?: '该分类下的 AI 工具'); ?> · 共 <?php echo (int)$cat->count; ?> 款</p>
  <?php if ($q->posts): ?>
    <div class="grid" style="margin-top:24px"><?php foreach ($q->posts as $p) hahatool_tool_card($p); wp_reset_postdata(); ?></div>
    <div class="pagination"><?php echo paginate_links(['total' => $q->max_num_pages, 'current' => max(1, get_query_var('paged'))]); ?></div>
  <?php else: ?>
    <div class="empty" style="margin-top:24px">该分类下暂无工具</div>
  <?php endif; ?>
<?php endif; ?>

</div>
<?php get_footer();
