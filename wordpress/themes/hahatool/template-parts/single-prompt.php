<?php
/** 提示词详情 */
if (!defined('ABSPATH')) exit;
$id = get_the_ID();
$prompt = hh_meta($id, 'prompt');
$scene = hh_meta($id, 'prompt_scene', '其他');
$related = hahatool_channel('ai-prompts', 100)->posts;
$related = array_filter($related, fn($p) => $p->ID !== $id && hh_meta($p->ID, 'prompt_scene') === $scene);
$related = array_slice(array_values($related), 0, 3);
?>
<div class="wrap" style="padding-top:32px;max-width:840px">
  <nav class="crumb"><a href="<?php echo esc_url(get_category_link_safe('ai-prompts')); ?>">‹ 返回提示词库</a></nav>
  <div class="panel">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
      <h1 style="font-size:26px"><?php the_title(); ?></h1>
      <button class="copy-btn" data-copy="<?php echo esc_attr($prompt); ?>">复制提示词</button>
    </div>
    <div class="meta-row" style="margin-top:8px">
      <span class="chip chip-brand"><?php echo esc_html($scene); ?></span>
      <span class="chip">适用：<?php echo esc_html(hh_meta($id, 'prompt_model', '通用')); ?></span>
      <span class="muted tnum">☆ <?php echo hahatool_count(hh_meta($id, 'likes')); ?> 热度</span>
      <span class="muted"><?php echo esc_html(get_the_date('Y-m-d')); ?> 收录</span>
    </div>
    <pre style="margin-top:18px;background:var(--surface-2);border:1px solid var(--border-2);border-radius:12px;padding:20px;font-family:ui-monospace,monospace;font-size:14px;line-height:1.8;white-space:pre-wrap;color:var(--text)"><?php echo esc_html($prompt); ?></pre>
    <?php if (get_the_content()): ?><div class="prose" style="margin-top:20px"><?php the_content(); ?></div><?php endif; ?>
  </div>

  <?php if ($related): ?>
  <section class="section" style="margin-top:36px">
    <h2 style="font-size:20px;margin-bottom:16px">同场景提示词</h2>
    <div class="grid grid-3"><?php foreach ($related as $p) hahatool_prompt_card($p); ?></div>
  </section>
  <?php endif; ?>
</div>
