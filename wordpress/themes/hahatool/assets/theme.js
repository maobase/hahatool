/* HahaTool 主题前端：明暗/主题色切换、Logo 降级、复制、移动菜单、站内浏览统计 */
(function () {
  'use strict';

  // ---- Logo 加载失败降级为首字母块（供 inline onerror 调用）----
  window.__hhFallback = function (img) {
    var name = (img.alt || '?').trim().charAt(0).toUpperCase();
    var s = img.width || 48;
    return '<span class="logo logo-fallback" style="width:' + s + 'px;height:' + s + 'px;font-size:' + Math.round(s * 0.42) + 'px">' + name + '</span>';
  };

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
      if ((t.mode || 'light') === 'system') applyMode('system');
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
        var done = function () { b.classList.add('done'); var o = b.textContent; b.textContent = '✓ 已复制'; setTimeout(function () { b.classList.remove('done'); b.textContent = o; }, 2000); };
        if (navigator.clipboard) navigator.clipboard.writeText(text).then(done, done);
        else { var ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); } catch (e) {} ta.remove(); done(); }
      });
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
