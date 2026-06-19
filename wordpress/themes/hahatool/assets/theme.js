/* HahaTool 主题前端：明暗/主题色切换、Logo 降级、复制、移动菜单、站内浏览统计 */
(function () {
  'use strict';

  // ---- Logo 加载失败降级为首字母块（供 inline onerror 调用）----
  window.__hhFallback = function (img) {
    var name = (img.alt || '?').trim().charAt(0).toUpperCase();
    var s = img.width || 48;
    return '<span class="logo logo-fallback" style="width:' + s + 'px;height:' + s + 'px;font-size:' + Math.round(s * 0.42) + 'px">' + name + '</span>';
  };

  // ---- 收藏（localStorage）----
  var FAV_KEY = 'hahatool:favs';
  function getFavs() { try { var a = JSON.parse(localStorage.getItem(FAV_KEY) || '[]'); return Array.isArray(a) ? a.map(Number).filter(Boolean) : []; } catch (e) { return []; } }
  function setFavs(a) { localStorage.setItem(FAV_KEY, JSON.stringify(a)); window.dispatchEvent(new Event('hahatool:favs')); }
  window.__hhFavs = getFavs;
  function toggleFav(id) { var f = getFavs(); var i = f.indexOf(id); if (i >= 0) f.splice(i, 1); else f.unshift(id); setFavs(f); return i < 0; }
  function syncFavUI() {
    var favs = getFavs();
    document.querySelectorAll('[data-fav]').forEach(function (b) {
      var on = favs.indexOf(+b.getAttribute('data-fav')) >= 0;
      b.classList.toggle('on', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    var badge = document.getElementById('favCount');
    if (badge) { badge.textContent = favs.length > 99 ? '99+' : favs.length; badge.style.display = favs.length ? 'flex' : 'none'; }
    // 收藏页过滤
    var grid = document.getElementById('favGrid');
    if (grid) {
      var empty = document.getElementById('favEmpty');
      var shown = 0;
      grid.querySelectorAll('[data-fav-card]').forEach(function (c) {
        var on = favs.indexOf(+c.getAttribute('data-fav-card')) >= 0;
        c.style.display = on ? '' : 'none'; if (on) shown++;
      });
      if (empty) empty.style.display = shown ? 'none' : '';
      grid.style.display = shown ? '' : 'none';
    }
  }

  var root = document.documentElement;
  function saveTheme(patch) {
    var t = {};
    try { t = JSON.parse(localStorage.getItem('hahatool:theme') || '{}'); } catch (e) {}
    Object.assign(t, patch);
    localStorage.setItem('hahatool:theme', JSON.stringify(t));
  }
  function applyMode(mode) {
    var dark = mode === 'dark' || (mode === 'system' && matchMedia('(prefers-color-scheme:dark)').matches);
    root.setAttribute('data-mode', dark ? 'dark' : 'light');
  }

  document.addEventListener('DOMContentLoaded', function () {
    // 主题菜单
    var btn = document.getElementById('themeBtn');
    var menu = document.getElementById('themeMenu');
    if (btn && menu) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
      });
      document.addEventListener('click', function (e) {
        if (!menu.contains(e.target) && e.target !== btn) menu.style.display = 'none';
      });
      menu.querySelectorAll('[data-theme-mode] button').forEach(function (b) {
        b.addEventListener('click', function () { applyMode(b.dataset.mode); saveTheme({ mode: b.dataset.mode }); });
      });
      menu.querySelectorAll('[data-theme-accent] button').forEach(function (b) {
        b.addEventListener('click', function () {
          root.setAttribute('data-accent', b.dataset.accent);
          saveTheme({ accent: b.dataset.accent });
          menu.querySelectorAll('[data-theme-accent] button').forEach(function (x) { x.style.borderColor = 'transparent'; });
          b.style.borderColor = '#fff';
        });
      });
    }
    // 跟随系统时响应切换
    matchMedia('(prefers-color-scheme:dark)').addEventListener('change', function () {
      var t = {}; try { t = JSON.parse(localStorage.getItem('hahatool:theme') || '{}'); } catch (e) {}
      if ((t.mode || 'system') === 'system') applyMode('system');
    });

    // 移动菜单
    var toggle = document.getElementById('navToggle');
    var links = document.getElementById('navLinks');
    if (toggle && links) toggle.addEventListener('click', function () {
      var open = links.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    // 复制按钮
    document.querySelectorAll('[data-copy]').forEach(function (b) {
      b.addEventListener('click', function (e) {
        e.preventDefault();
        var text = b.getAttribute('data-copy');
        var done = function () { b.classList.add('done'); var o = b.innerHTML; b.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-.15em"><polyline points="20 6 9 17 4 12"/></svg> 已复制'; setTimeout(function () { b.classList.remove('done'); b.innerHTML = o; }, 2000); };
        if (navigator.clipboard) navigator.clipboard.writeText(text).then(done, done);
        else { var ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); } catch (e) {} ta.remove(); done(); }
      });
    });

    // 收藏按钮
    syncFavUI();
    window.addEventListener('hahatool:favs', syncFavUI);
    window.addEventListener('storage', function (e) { if (e.key === FAV_KEY) syncFavUI(); });
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-fav]');
      if (btn) { e.preventDefault(); e.stopPropagation(); toggleFav(+btn.getAttribute('data-fav')); }
    });

    // 搜索即时建议
    document.querySelectorAll('[data-suggest]').forEach(function (input) {
      var box = document.createElement('ul');
      box.className = 'suggest-box'; box.style.display = 'none';
      input.parentNode.style.position = 'relative';
      input.parentNode.appendChild(box);
      var timer, home = (window.HAHATOOL && HAHATOOL.home) || '/';
      input.addEventListener('input', function () {
        clearTimeout(timer);
        var kw = input.value.trim();
        if (!kw) { box.style.display = 'none'; return; }
        timer = setTimeout(function () {
          fetch(HAHATOOL.restUrl + 'wp/v2/posts?per_page=6&search=' + encodeURIComponent(kw))
            .then(function (r) { return r.json(); })
            .then(function (posts) {
              if (!posts.length) { box.style.display = 'none'; return; }
              box.innerHTML = posts.map(function (p) {
                var m = p.meta || {}, isTool = !!m.url, isPrompt = !!m.prompt;
                var sub = isTool ? (m.tagline || '工具') : isPrompt ? '提示词' : 'AI 资讯';
                var title = (p.title && p.title.rendered ? p.title.rendered : '').replace(/<[^>]+>/g, '');
                return '<li><a href="' + p.link + '"><b>' + title + '</b><span>' + sub + '</span></a></li>';
              }).join('') + '<li class="suggest-all"><a href="' + home + '?s=' + encodeURIComponent(kw) + '">查看「' + kw + '」全部结果 →</a></li>';
              box.style.display = 'block';
            }).catch(function () { box.style.display = 'none'; });
        }, 250);
      });
      document.addEventListener('click', function (e) { if (!input.parentNode.contains(e.target)) box.style.display = 'none'; });
    });

    // 站内浏览统计（每会话每篇一次）
    if (window.__HAHATOOL_TRACK__ && window.HAHATOOL) {
      var cid = window.__HAHATOOL_TRACK__;
      var key = 'hahatool:viewed:' + cid;
      if (!sessionStorage.getItem(key)) {
        sessionStorage.setItem(key, '1');
        fetch(HAHATOOL.restUrl + 'hahatool/v1/track', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ cid: cid, type: 'views' })
        }).catch(function () {});
      }
    }
    // 阅读进度条（文章页）：滚动比例 → scaleX，rAF 节流
    var bar = document.getElementById('readProgress');
    if (bar) {
      var ticking = false;
      var update = function () {
        var doc = document.documentElement;
        var max = (doc.scrollHeight - doc.clientHeight);
        var r = max > 0 ? Math.min(1, Math.max(0, (doc.scrollTop || document.body.scrollTop) / max)) : 0;
        bar.style.transform = 'scaleX(' + r + ')';
        ticking = false;
      };
      window.addEventListener('scroll', function () {
        if (!ticking) { ticking = true; requestAnimationFrame(update); }
      }, { passive: true });
      window.addEventListener('resize', update, { passive: true });
      update();
    }

    // 文章分享：复制链接 + 系统分享（移动端）
    var shareWrap = document.querySelector('.article-share');
    if (shareWrap) {
      var sUrl = shareWrap.getAttribute('data-share-url') || location.href;
      var sTitle = shareWrap.getAttribute('data-share-title') || document.title;
      var copyBtn = shareWrap.querySelector('[data-share-copy]');
      if (copyBtn) copyBtn.addEventListener('click', function () {
        var done = function () {
          copyBtn.classList.add('done');
          var o = copyBtn.innerHTML;
          copyBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-.15em"><polyline points="20 6 9 17 4 12"/></svg> 已复制';
          setTimeout(function () { copyBtn.classList.remove('done'); copyBtn.innerHTML = o; }, 2000);
        };
        if (navigator.clipboard) navigator.clipboard.writeText(sUrl).then(done, done);
        else { var ta = document.createElement('textarea'); ta.value = sUrl; document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); } catch (e) {} ta.remove(); done(); }
      });
      var nativeBtn = shareWrap.querySelector('[data-share-native]');
      if (nativeBtn && navigator.share) {
        nativeBtn.hidden = false;
        nativeBtn.addEventListener('click', function () {
          navigator.share({ title: sTitle, url: sUrl }).catch(function () {});
        });
      }
    }

    // 返回顶部按钮：滚动超 600px 显示，点击平滑回顶（尊重 reduced-motion）
    var toTop = document.getElementById('toTop');
    if (toTop) {
      var toTopTick = false;
      var syncToTop = function () {
        toTop.classList.toggle('show', (window.scrollY || document.documentElement.scrollTop) > 600);
        toTopTick = false;
      };
      window.addEventListener('scroll', function () {
        if (!toTopTick) { toTopTick = true; requestAnimationFrame(syncToTop); }
      }, { passive: true });
      syncToTop();
      toTop.addEventListener('click', function () {
        var rm = matchMedia('(prefers-reduced-motion:reduce)').matches;
        window.scrollTo({ top: 0, behavior: rm ? 'auto' : 'smooth' });
      });
    }

    // 官网点击统计
    document.querySelectorAll('[data-track-click]').forEach(function (a) {
      a.addEventListener('click', function () {
        if (!window.HAHATOOL) return;
        fetch(HAHATOOL.restUrl + 'hahatool/v1/track', {
          method: 'POST', headers: { 'Content-Type': 'application/json' }, keepalive: true,
          body: JSON.stringify({ cid: +a.getAttribute('data-track-click'), type: 'clicks' })
        }).catch(function () {});
      });
    });
  });
})();
