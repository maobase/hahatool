#!/usr/bin/env node
/**
 * 轻量截图工具（CDP，无需 puppeteer）。支持强制暗色模式，用于浅/深双色 QA。
 * 用法: node scripts/shot.js <url> <out.png> <width> <height> [dark]
 * 依赖: 本机 Chrome + Node ≥21（全局 WebSocket）。
 */
const { spawn } = require('child_process');
const fs = require('fs');

const [url, out, w = '1440', h = '1600', mode = 'light'] = process.argv.slice(2);
if (!url || !out) { console.error('用法: node shot.js <url> <out.png> <w> <h> [dark]'); process.exit(1); }
const dark = mode === 'dark' || mode === 'sysdark'; // sysdark: 仅模拟暗色 OS，不写 localStorage（验证「跟随系统」默认）
const injectStorage = mode === 'dark';
const W = +w, H = +h;
const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';
const PORT = 9300 + Math.floor((Date.now() % 600));

const sleep = (ms) => new Promise(r => setTimeout(r, ms));

(async () => {
  const proc = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--hide-scrollbars', `--remote-debugging-port=${PORT}`,
    `--user-agent=${UA}`, '--no-first-run', '--user-data-dir=/tmp/hh-cdp-' + PORT,
  ], { stdio: 'ignore' });

  try {
    // 等待 devtools 端口就绪
    let ver;
    for (let i = 0; i < 40; i++) {
      try { ver = await (await fetch(`http://127.0.0.1:${PORT}/json/version`)).json(); break; }
      catch { await sleep(150); }
    }
    if (!ver) throw new Error('devtools 端口未就绪');

    // 新建标签页
    const tab = await (await fetch(`http://127.0.0.1:${PORT}/json/new?about:blank`, { method: 'PUT' })).json();
    const ws = new WebSocket(tab.webSocketDebuggerUrl);
    await new Promise((res, rej) => { ws.onopen = res; ws.onerror = rej; });

    let id = 0; const pending = new Map(); const events = [];
    ws.onmessage = (m) => {
      const msg = JSON.parse(m.data);
      if (msg.id && pending.has(msg.id)) { pending.get(msg.id)(msg); pending.delete(msg.id); }
      else if (msg.method) events.push(msg.method);
    };
    const send = (method, params = {}) => new Promise((res) => { const i = ++id; pending.set(i, res); ws.send(JSON.stringify({ id: i, method, params })); });

    await send('Page.enable');
    await send('Emulation.setDeviceMetricsOverride', { width: W, height: H, deviceScaleFactor: 1, mobile: W < 600 });
    if (dark) {
      await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-color-scheme', value: 'dark' }] });
      // 仅 dark 模式写入站点主题偏好强制暗色；sysdark 留空以验证「跟随系统」默认
      if (injectStorage) await send('Page.addScriptToEvaluateOnNewDocument', {
        source: `try{localStorage.setItem('hahatool:theme', JSON.stringify({mode:'dark'}));}catch(e){}`,
      });
    }
    await send('Page.navigate', { url });
    // 等 load 事件
    for (let i = 0; i < 60 && !events.includes('Page.loadEventFired'); i++) await sleep(100);
    await sleep(800);

    const shot = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true,
      clip: { x: 0, y: 0, width: W, height: H, scale: 1 } });
    fs.writeFileSync(out, Buffer.from(shot.result.data, 'base64'));
    console.log('saved', out, dark ? '(dark)' : '(light)');
    ws.close();
  } finally {
    proc.kill('SIGKILL');
  }
})().catch(e => { console.error('ERR', e.message); process.exit(1); });
