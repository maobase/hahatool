<?php
/** 搜索结果：工具 / 提示词 / 资讯 分组 */
if (!defined('ABSPATH')) exit;
get_header();
$kw = get_search_query();
$tools = $prompts = $news = [];
if (have_posts()) {
    while (have_posts()) { the_post(); $p = get_post();
        if (hh_meta($p->ID, 'prompt')) $prompts[] = $p;
        elseif (hahatool_is_tool($p->ID)) $tools[] = $p;
        else $news[] = $p;
    }
}
?>
<div class="wrap" style="padding-top:40px">
  <h1 class="section-title-lg">搜索</h1>
  <form class="hero-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" style="margin:20px 0 0;max-width:600px">
    <span class="s-icon" style="color:var(--text-3);display:inline-flex"><?php echo hh_icon('search', 20); ?></span>
    <input type="search" name="s" value="<?php echo esc_attr($kw); ?>" placeholder="搜索 AI 工具…" data-suggest autocomplete="off" style="color:var(--text);background:var(--surface);border:1px solid var(--border);box-shadow:none">
    <button class="btn" type="submit">搜索</button>
  </form>
  <p class="muted" style="margin-top:16px">「<b style="color:var(--text)"><?php echo esc_html($kw); ?></b>」共找到 <?php echo count($tools) + count($prompts) + count($news); ?> 条结果</p>

  <?php if (!$tools && !$prompts && !$news): ?>
    <div class="empty" style="margin-top:24px">没有找到相关结果，换个关键词试试，比如「写作」「视频」「编程」。</div>
  <?php endif; ?>

  <?php if ($tools): ?>
    <section class="section" style="margin-top:28px"><h2 style="font-size:18px;margin-bottom:16px">工具（<?php echo count($tools); ?>）</h2>
      <div class="grid"><?php foreach ($tools as $p) hahatool_tool_card($p); wp_reset_postdata(); ?></div></section>
  <?php endif; ?>

  <?php if ($prompts): ?>
    <section class="section"><h2 style="font-size:18px;margin-bottom:16px">提示词（<?php echo count($prompts); ?>）</h2>
      <div class="grid grid-3"><?php foreach ($prompts as $p) hahatool_prompt_card($p); ?></div></section>
  <?php endif; ?>

  <?php if ($news): ?>
    <section class="section"><h2 style="font-size:18px;margin-bottom:16px">资讯（<?php echo count($news); ?>）</h2>
      <div style="display:flex;flex-direction:column;gap:12px"><?php foreach ($news as $p): ?>
        <a class="news-item" href="<?php echo esc_url(get_permalink($p)); ?>"><div class="body"><time><?php echo esc_html(get_the_date('Y-m-d', $p)); ?></time><h3><?php echo esc_html(get_the_title($p)); ?></h3></div></a>
      <?php endforeach; ?></div></section>
  <?php endif; ?>
</div>
<?php get_footer();
