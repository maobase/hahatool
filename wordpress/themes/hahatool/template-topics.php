<?php
/** /topics 全部专题列表 */
if (!defined('ABSPATH')) exit;
get_header();
$terms = get_terms(['taxonomy' => 'topic', 'hide_empty' => true]);
?>
<div class="wrap" style="padding-top:40px">
  <h1 class="section-title-lg" style="display:flex;align-items:center;gap:8px"><span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:12px;background:var(--brand-600);color:#fff"><?php echo hh_icon('layers', 18); ?></span>专题合集</h1>
  <p class="muted">精心策划的 AI 工具合集，按场景与主题归类</p>
  <?php if (!empty($terms) && !is_wp_error($terms)): ?>
  <div class="topic-grid">
    <?php foreach ($terms as $t): $cover = get_term_meta($t->term_id, 'topic_cover', true); ?>
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
  <?php else: ?>
    <div class="empty" style="margin-top:24px">暂无专题</div>
  <?php endif; ?>
</div>
<?php get_footer();
