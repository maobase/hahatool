<?php
/** /submit 提交工具：写入待审文章 */
if (!defined('ABSPATH')) exit;
get_header();

$done = false; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce($_POST['hh_nonce'] ?? '', 'hahatool_submit')) {
    $name = sanitize_text_field($_POST['name'] ?? '');
    $url = esc_url_raw($_POST['url'] ?? '');
    $tagline = sanitize_text_field($_POST['tagline'] ?? '');
    $pricing = sanitize_text_field($_POST['pricing'] ?? '');
    $mail = sanitize_email($_POST['mail'] ?? '');
    $catId = (int)($_POST['cat'] ?? 0);
    $note = sanitize_textarea_field($_POST['note'] ?? '');
    if (!$name || mb_strlen($name) > 50) $err = '请填写工具名称（50 字内）';
    elseif (!$url) $err = '请填写有效的官网链接';
    elseif (!$tagline || mb_strlen($tagline) > 60) $err = '请填写一句话简介（60 字内）';
    elseif (!in_array($pricing, ['免费', '免费增值', '付费'], true)) $err = '请选择定价模式';
    elseif (!is_email($mail)) $err = '请填写有效的联系邮箱';
    else {
        $pid = wp_insert_post([
            'post_title' => $name, 'post_status' => 'pending', 'post_type' => 'post',
            'post_content' => '<p><em>—— 用户提交，待审核 ——</em></p><p>联系邮箱：' . esc_html($mail) . '</p>' . ($note ? '<p>补充：' . esc_html($note) . '</p>' : ''),
            'post_category' => $catId ? [$catId] : [],
        ], true);
        if (is_wp_error($pid)) $err = '提交失败：' . $pid->get_error_message();
        else { update_post_meta($pid, 'url', $url); update_post_meta($pid, 'tagline', $tagline); update_post_meta($pid, 'pricing', $pricing); $done = true; }
    }
}
$tool_cats = get_categories(['hide_empty' => false, 'exclude' => implode(',', hahatool_reserved_ids())]);
$criteria = ['产品可正常访问、功能可用，非纯落地页', '与 AI 相关：大模型、AIGC、AI 工作流等', '提供清晰的产品介绍与定价信息', '无恶意行为（捆绑下载、欺诈营销等）'];
$submit_count = count(hahatool_tools(['posts_per_page' => 300])->posts);
// 收录权益 & 常见问题
$benefits = [
    ['sparkles', '免费永久收录', '基础收录完全免费，审核通过后长期展示，不下架不收费。'],
    ['chart', '完整工具详情页', '自动生成流量数据、评分、能力雷达与替代品对比，自带 SEO 友好结构。'],
    ['flame', '首页与榜单曝光', '有机会进入首页「编辑精选」「增长最快」及各推广位，触达精准用户。'],
    ['heart', '真实用户互动', '用户可收藏、点评并一键直达你的官网，沉淀口碑。'],
];
$faqs = [
    ['收录要花钱吗？', '基础收录完全免费。仅首页 banner、置顶等增值推广位为可选付费。'],
    ['多久能审核通过？', '通常 1-3 个工作日。通过后会自动出现在对应分类与搜索结果中。'],
    ['信息有误怎么修改？', '用提交时填写的联系邮箱发邮件给我们，注明工具名与要更新的内容即可。'],
    ['可以申请推广位吗？', '可以。收录后通过邮件沟通首页「编辑精选」、banner 等展示方案。'],
];
?>
<div class="wrap" style="padding-top:40px">
  <h1 class="section-title-lg">提交你的 AI 工具</h1>
  <p class="muted">填写下方表单即可，审核通过后<b style="color:var(--text)">免费收录</b>，并有机会获得首页「编辑精选」与各推广位展示<?php if ($submit_count > 0): ?> · 已收录 <b style="color:var(--text)" class="tnum"><?php echo (int) $submit_count; ?>+</b> 款工具<?php endif; ?>。</p>

  <div class="detail-grid" style="margin-top:24px">
    <div>
      <div class="panel">
        <h2 style="font-size:16px">收录标准</h2>
        <ul style="margin:14px 0 0;padding-left:0;list-style:none;display:flex;flex-direction:column;gap:10px">
          <?php foreach ($criteria as $c): ?><li style="display:flex;gap:8px;color:var(--text-2);font-size:14px"><span style="color:#10b981;flex-shrink:0"><?php echo hh_icon('check-circle', 18); ?></span><?php echo esc_html($c); ?></li><?php endforeach; ?>
        </ul>
      </div>

      <?php if ($done): ?>
        <div class="panel" style="margin-top:20px;text-align:center;border-color:#10b981">
          <div style="color:#10b981"><?php echo hh_icon('check-circle', 40, 1.5); ?></div>
          <h2 style="margin-top:8px">提交成功！</h2>
          <p class="muted">你的工具已进入审核队列，通过后将自动出现在站内（通常 1-3 个工作日）。感谢贡献！</p>
          <a class="btn btn-ghost" style="margin-top:16px;display:inline-flex" href="<?php echo esc_url(home_url('/tools/')); ?>">先逛逛工具库</a>
        </div>
      <?php else: ?>
        <form class="cform panel" method="post" style="margin-top:20px">
          <?php wp_nonce_field('hahatool_submit', 'hh_nonce'); ?>
          <h2 style="font-size:16px">在线提交</h2>
          <?php if ($err): ?><p style="color:#f43f5e;margin-top:8px;display:flex;align-items:center;gap:6px"><?php echo hh_icon('alert', 16); ?><?php echo esc_html($err); ?></p><?php endif; ?>
          <div class="grid2" style="margin-top:8px">
            <div><label for="f-name">工具名称 *</label><input id="f-name" name="name" required maxlength="50" placeholder="如：Gemini" value="<?php echo esc_attr($_POST['name'] ?? ''); ?>"></div>
            <div><label for="f-url">官网链接 *</label><input id="f-url" name="url" type="url" required placeholder="https://…" value="<?php echo esc_attr($_POST['url'] ?? ''); ?>"></div>
          </div>
          <label for="f-tagline">一句话简介 *（60 字内）</label><input id="f-tagline" name="tagline" required maxlength="60" placeholder="谁 + 能干什么" value="<?php echo esc_attr($_POST['tagline'] ?? ''); ?>">
          <div class="grid2">
            <div><label for="f-cat">分类 *</label><select id="f-cat" name="cat" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:12px;background:var(--surface);color:var(--text)"><option value="">请选择</option><?php foreach ($tool_cats as $c): ?><option value="<?php echo (int)$c->term_id; ?>"><?php echo esc_html($c->name); ?></option><?php endforeach; ?></select></div>
            <div><label for="f-pricing">定价模式 *</label><select id="f-pricing" name="pricing" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:12px;background:var(--surface);color:var(--text)"><option value="免费">免费</option><option value="免费增值" selected>免费增值</option><option value="付费">付费</option></select></div>
          </div>
          <label for="f-mail">联系邮箱 *（不公开）</label><input id="f-mail" name="mail" type="email" required value="<?php echo esc_attr($_POST['mail'] ?? ''); ?>">
          <label for="f-note">补充说明（可选）</label><textarea id="f-note" name="note" rows="3" maxlength="500"></textarea>
          <button class="btn" type="submit" style="margin-top:16px">提交审核</button>
          <p class="muted" style="margin-top:10px;font-size:12px">提交即表示同意我们按收录标准审核。</p>
        </form>
      <?php endif; ?>
    </div>

    <aside>
      <div class="panel">
        <h2 style="font-size:16px;margin-bottom:14px">收录后能获得什么</h2>
        <div style="display:flex;flex-direction:column;gap:16px">
          <?php foreach ($benefits as $b): ?>
            <div style="display:flex;gap:12px">
              <span style="flex-shrink:0;width:36px;height:36px;border-radius:10px;background:var(--brand-50);color:var(--brand-600);display:inline-flex;align-items:center;justify-content:center"><?php echo hh_icon($b[0], 18); ?></span>
              <div style="min-width:0"><div style="font-weight:600;font-size:14px"><?php echo esc_html($b[1]); ?></div><div class="muted" style="font-size:13px;line-height:1.6;margin-top:2px"><?php echo esc_html($b[2]); ?></div></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="panel faq" style="margin-top:24px">
        <h2 style="font-size:16px;margin-bottom:8px">常见问题</h2>
        <div><?php foreach ($faqs as $f): ?><details><summary><?php echo esc_html($f[0]); ?></summary><p><?php echo esc_html($f[1]); ?></p></details><?php endforeach; ?></div>
      </div>
    </aside>
  </div>
</div>
<?php get_footer();
