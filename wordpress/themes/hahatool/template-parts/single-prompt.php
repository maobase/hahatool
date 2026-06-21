<?php
/** 提示词详情（两栏：内容 + 侧栏，内容更丰富） */
if (!defined('ABSPATH')) exit;
$id = get_the_ID();
$prompt = hh_meta($id, 'prompt');
$scene = hh_meta($id, 'prompt_scene', '其他');
$all_prompts = hahatool_channel('ai-prompts', 100)->posts;

// 相关提示词：同场景优先，不足用其他热门补足到 4
$same = array_values(array_filter($all_prompts, fn($p) => $p->ID !== $id && hh_meta($p->ID, 'prompt_scene') === $scene));
if (count($same) < 4) {
    $others = array_values(array_filter($all_prompts, fn($p) => $p->ID !== $id && hh_meta($p->ID, 'prompt_scene') !== $scene));
    usort($others, fn($a, $b) => (float) hh_meta($b->ID, 'likes') - (float) hh_meta($a->ID, 'likes'));
    $same = array_merge($same, array_slice($others, 0, 4 - count($same)));
}
$related = array_slice($same, 0, 4);

// 侧栏：热门提示词（按热度）+ 全部场景
$hot_prompts = array_values(array_filter($all_prompts, fn($p) => $p->ID !== $id));
usort($hot_prompts, fn($a, $b) => (float) hh_meta($b->ID, 'likes') - (float) hh_meta($a->ID, 'likes'));
$hot_prompts = array_slice($hot_prompts, 0, 6);
$scenes = array_values(array_unique(array_map(fn($p) => hh_meta($p->ID, 'prompt_scene', '其他'), $all_prompts)));

// 「拿去试试」的聊天助手
$cb = get_category_by_slug('chatbot');
$try_tools = $cb ? array_slice(hahatool_tools(['posts_per_page' => 5, 'cat' => $cb->term_id])->posts, 0, 5) : [];
?>
<div class="wrap" style="padding-top:32px">
  <nav class="crumb"><a href="<?php echo esc_url(home_url('/')); ?>">首页</a> / <a href="<?php echo esc_url(home_url('/prompts/')); ?>">AI 提示词库</a> / <span style="color:var(--text-2)"><?php the_title(); ?></span></nav>
  <div class="detail-grid" style="margin-top:16px">
    <div class="detail-main">
      <div class="panel">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
          <h1 style="font-size:26px"><?php the_title(); ?></h1>
          <button class="copy-btn" data-copy="<?php echo esc_attr($prompt); ?>"><?php echo hh_icon('check', 14); ?>复制提示词</button>
        </div>
        <div class="meta-row" style="margin-top:8px">
          <span class="chip chip-brand"><?php echo esc_html($scene); ?></span>
          <span class="chip">适用：<?php echo esc_html(hh_meta($id, 'prompt_model', '通用')); ?></span>
          <span class="muted tnum" style="display:inline-flex;align-items:center;gap:4px"><?php echo hh_icon('bookmark', 13); ?><?php echo hahatool_count(hh_meta($id, 'likes')); ?> 热度</span>
          <span class="muted"><?php echo esc_html(get_the_date('Y-m-d')); ?> 收录</span>
        </div>
        <pre style="margin-top:18px;background:var(--surface-2);border:1px solid var(--border-2);border-radius:12px;padding:20px;font-family:ui-monospace,monospace;font-size:14px;line-height:1.8;white-space:pre-wrap;color:var(--text)"><?php echo esc_html($prompt); ?></pre>
        <?php if (get_the_content()): ?><div class="prose" style="margin-top:20px"><?php the_content(); ?></div><?php endif; ?>
      </div>

      <?php if ($try_tools): ?>
      <section class="panel" style="margin-top:20px">
        <h2 style="font-size:16px;display:flex;align-items:center;gap:6px"><span style="color:var(--brand-500)"><?php echo hh_icon('sparkles', 16); ?></span>复制后，用这些 AI 试试</h2>
        <p class="muted" style="margin:6px 0 12px;font-size:13px">把上面的提示词粘贴进任一对话助手即可使用</p>
        <div class="rank-list">
          <?php foreach ($try_tools as $t): $turl = hh_meta($t->ID, 'url'); ?>
            <a class="rank-item" href="<?php echo esc_url($turl ?: get_permalink($t)); ?>"<?php if ($turl): ?> target="_blank" rel="noopener nofollow" data-track-click="<?php echo (int) $t->ID; ?>"<?php endif; ?>>
              <?php echo hahatool_logo($t->ID, 32); ?>
              <span style="flex:1;min-width:0">
                <span style="display:block;font-weight:600;font-size:14px"><?php echo esc_html(get_the_title($t)); ?></span>
                <?php if ($tt = hh_meta($t->ID, 'tagline')): ?><span class="muted" style="display:block;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html($tt); ?></span><?php endif; ?>
              </span>
              <span class="muted"><?php echo hh_icon('arrow-up-right', 14); ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php if ($related): ?>
      <section style="margin-top:24px">
        <h2 style="font-size:20px;margin-bottom:16px">相关提示词</h2>
        <div class="grid grid-2"><?php foreach ($related as $p) hahatool_prompt_card($p); ?></div>
      </section>
      <?php endif; ?>
    </div>

    <aside>
      <?php if ($scenes): ?>
      <div class="panel">
        <h2 style="font-size:16px;display:flex;align-items:center;gap:5px"><?php echo hh_icon('layers', 15); ?>按场景浏览</h2>
        <div class="tagcloud-grid" style="margin-top:12px">
          <?php foreach ($scenes as $s): ?><a href="<?php echo esc_url(add_query_arg('scene', urlencode($s), get_category_link_safe('ai-prompts'))); ?>"<?php if ($s === $scene): ?> style="border-color:var(--brand-300);color:var(--brand-700)"<?php endif; ?>><?php echo esc_html($s); ?></a><?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($hot_prompts): ?>
      <div class="panel" style="margin-top:24px">
        <h2 style="font-size:16px;display:flex;align-items:center;gap:5px"><?php echo hh_icon('flame', 15); ?>热门提示词</h2>
        <div class="rank-list" style="margin-top:10px">
          <?php foreach ($hot_prompts as $i => $p): ?>
            <a class="rank-item" href="<?php echo esc_url(get_permalink($p)); ?>">
              <span class="num"><?php echo $i + 1; ?></span>
              <span style="flex:1;min-width:0">
                <span style="display:block;font-weight:500;font-size:14px" class="truncate"><?php echo esc_html(get_the_title($p)); ?></span>
                <span class="muted" style="font-size:12px"><?php echo esc_html(hh_meta($p->ID, 'prompt_scene', '其他')); ?> · <?php echo hahatool_count(hh_meta($p->ID, 'likes')); ?> 热度</span>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
        <a href="<?php echo esc_url(get_category_link_safe('ai-prompts')); ?>" style="margin-top:12px;display:block;text-align:center;font-size:14px;color:var(--brand-600)">进入提示词库 →</a>
      </div>
      <?php endif; ?>

      <div class="panel" style="margin-top:24px;text-align:center">
        <p style="font-weight:600">有好用的提示词？</p>
        <p class="muted" style="font-size:13px;margin:6px 0 12px">投稿分享给更多人</p>
        <a class="btn" href="<?php echo esc_url(home_url('/submit/')); ?>">提交提示词</a>
      </div>
    </aside>
  </div>
</div>
