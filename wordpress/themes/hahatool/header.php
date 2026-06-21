<?php if (!defined('ABSPATH')) exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#030712" media="(prefers-color-scheme: dark)">
    <link rel="apple-touch-icon" href="<?php echo esc_url(home_url('/media/hahatool-media/brand/icon-192.png')); ?>">
    <link rel="icon" type="image/svg+xml" href="<?php echo esc_url(home_url('/media/hahatool-media/brand/icon.svg')); ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo esc_url(home_url('/media/hahatool-media/brand/icon-192.png')); ?>">
    <link rel="manifest" href="<?php echo esc_url(home_url('/site.webmanifest')); ?>">
    <?php wp_head(); ?>
    <script>/* 首屏防闪烁：在样式应用前写入已存的主题偏好 */
    (function(){try{var t=JSON.parse(localStorage.getItem('hahatool:theme')||'{}');var m=t.mode||'system';var d=m==='dark'||(m==='system'&&matchMedia('(prefers-color-scheme:dark)').matches);var e=document.documentElement;e.setAttribute('data-mode',d?'dark':'light');if(t.accent)e.setAttribute('data-accent',t.accent);}catch(e){}})();</script>
</head>
<body <?php body_class(); ?>>
<a class="skip-link" href="#main-content">跳到主要内容</a>
<header class="site-header">
  <div class="wrap nav">
    <?php
    // 站名「HahaTool 哈哈工具」按首个空格拆成 英文字标 + 中文副标，避免与旧版写死的 <small> 重复
    $hh_name = get_bloginfo('name');
    $hh_brand = preg_split('/\s+/', $hh_name, 2);
    ?>
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
      <span class="brand-logo"><?php echo hh_icon('sparkles', 18); ?></span>
      <span><?php echo esc_html($hh_brand[0]); ?><?php if (!empty($hh_brand[1])): ?><small><?php echo esc_html($hh_brand[1]); ?></small><?php endif; ?></span>
    </a>
    <nav class="nav-links" id="navLinks">
      <form class="nav-links-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <input type="search" name="s" placeholder="搜索 AI 工具…" aria-label="搜索">
      </form>
      <a href="<?php echo esc_url(home_url('/')); ?>"<?php echo hahatool_nav_attr('home'); ?>>首页</a>
      <a href="<?php echo esc_url(home_url('/tools/')); ?>"<?php echo hahatool_nav_attr('tools'); ?>>工具库</a>
      <a href="<?php echo esc_url(home_url('/ranking/')); ?>"<?php echo hahatool_nav_attr('ranking'); ?>>排行榜</a>
      <a href="<?php echo esc_url(home_url('/compare/')); ?>"<?php echo hahatool_nav_attr('compare'); ?>>工具PK</a>
      <a href="<?php echo esc_url(home_url('/prompts/')); ?>"<?php echo hahatool_nav_attr('prompts'); ?>>提示词</a>
      <a href="<?php echo esc_url(home_url('/flash/')); ?>"<?php echo hahatool_nav_attr('flash'); ?>>AI快讯</a>
      <a href="<?php echo esc_url(home_url('/news/')); ?>"<?php echo hahatool_nav_attr('news'); ?>>AI资讯</a>
      <a href="<?php echo esc_url(home_url('/topics/')); ?>"<?php echo hahatool_nav_attr('topics'); ?>>专题</a>
      <a href="<?php echo esc_url(home_url('/hot/')); ?>"<?php echo hahatool_nav_attr('hot'); ?>>热榜</a>
      <?php $hh_nav_cats = get_categories(['hide_empty' => true, 'exclude' => implode(',', hahatool_reserved_ids())]); if ($hh_nav_cats): ?>
      <span class="nav-dropdown">
        <button type="button" class="nav-dropdown-btn" aria-haspopup="true" aria-expanded="false">分类<?php echo hh_icon('chevron-down', 14); ?></button>
        <div class="nav-dropdown-menu">
          <?php foreach ($hh_nav_cats as $hc): ?>
            <a href="<?php echo esc_url(get_category_link($hc)); ?>"><?php echo esc_html($hc->name); ?><span><?php echo (int)$hc->count; ?></span></a>
          <?php endforeach; ?>
        </div>
      </span>
      <?php endif; ?>
      <a class="menu-only" href="<?php echo esc_url(home_url('/submit/')); ?>" style="color:var(--brand-600);font-weight:600">提交工具</a>
      <a class="menu-only" href="<?php echo esc_url(home_url('/favorites/')); ?>" style="color:#e11d48;font-weight:600;gap:6px"><?php echo hh_icon('heart', 16); ?>我的收藏</a>
    </nav>
    <div class="nav-right">
      <form class="nav-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" autocomplete="off">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" name="s" placeholder="搜索 AI 工具…" value="<?php echo esc_attr(get_search_query()); ?>" aria-label="搜索" data-suggest>
      </form>
      <a class="icon-btn fav-nav" href="<?php echo esc_url(home_url('/favorites/')); ?>" aria-label="我的收藏" title="我的收藏"><?php echo hh_icon('heart', 19); ?><span class="badge-count" id="favCount"></span></a>
      <button class="icon-btn" id="themeBtn" aria-label="外观设置" title="外观设置"><?php echo hh_icon('palette', 19); ?></button>
      <a class="btn nav-cta" href="<?php echo esc_url(home_url('/submit/')); ?>">提交工具</a>
      <button class="icon-btn nav-toggle" id="navToggle" aria-label="菜单" aria-expanded="false"><?php echo hh_icon('menu', 22); ?></button>
    </div>
  </div>
  <div id="themeMenu" style="display:none;position:absolute;right:24px;top:64px;z-index:60;background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:16px;box-shadow:var(--shadow-lg);width:220px">
    <p class="muted" style="margin:0 0 8px">外观模式</p>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px" data-theme-mode>
      <button data-mode="light" class="btn-ghost" style="padding:8px;font-size:12px">浅色</button>
      <button data-mode="dark" class="btn-ghost" style="padding:8px;font-size:12px">深色</button>
      <button data-mode="system" class="btn-ghost" style="padding:8px;font-size:12px">系统</button>
    </div>
    <p class="muted" style="margin:14px 0 8px">主题色</p>
    <div style="display:flex;gap:10px" data-theme-accent role="radiogroup" aria-label="主题色">
      <button data-accent="violet" title="紫罗兰" aria-label="紫罗兰主题色" style="width:30px;height:30px;border-radius:50%;border:2px solid transparent;background:#7c3aed;cursor:pointer"></button>
      <button data-accent="sky" title="海蓝" aria-label="海蓝主题色" style="width:30px;height:30px;border-radius:50%;border:2px solid transparent;background:#0284c7;cursor:pointer"></button>
      <button data-accent="emerald" title="翡翠" aria-label="翡翠主题色" style="width:30px;height:30px;border-radius:50%;border:2px solid transparent;background:#059669;cursor:pointer"></button>
      <button data-accent="rose" title="玫红" aria-label="玫红主题色" style="width:30px;height:30px;border-radius:50%;border:2px solid transparent;background:#e11d48;cursor:pointer"></button>
    </div>
  </div>
</header>
<span id="main-content" tabindex="-1"></span>
