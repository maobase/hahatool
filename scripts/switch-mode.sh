#!/usr/bin/env bash
# 在「WordPress 主题模式」和「无头模式」之间切换 WordPress 当前主题。
# 两种模式的数据完全共用；无头前台(Next.js)始终可用，本脚本只影响 http://localhost:8090 自身渲染什么。
#
#   bash scripts/switch-mode.sh theme      # 激活 HahaTool 主题，WordPress 直接渲染导航站
#   bash scripts/switch-mode.sh headless   # 切回默认主题，WordPress 仅作 REST 数据源
set -euo pipefail
cd "$(dirname "$0")/.."

MODE="${1:-}"
wpcli() { docker compose run --rm --no-deps wpcli wp "$@"; }

case "$MODE" in
  theme)
    wpcli theme activate hahatool
    wpcli rewrite flush
    echo "✓ 已切换到「WordPress 主题模式」：访问 http://localhost:${WP_PORT:-8090}"
    ;;
  headless)
    # 回退到 WordPress 自带的默认主题（按存在性择一）
    for t in twentytwentyfive twentytwentyfour twentytwentythree; do
      if wpcli theme is-installed "$t" >/dev/null 2>&1; then wpcli theme activate "$t"; break; fi
    done
    echo "✓ 已切回「无头模式」：WordPress 仅作 REST 数据源，前台用 Next.js (http://localhost:${FRONTEND_PORT:-3000})"
    ;;
  *)
    echo "用法: bash scripts/switch-mode.sh [theme|headless]"
    echo "  theme    - 激活 HahaTool 主题，由 WordPress 直接渲染整站"
    echo "  headless - 切回默认主题，仅用 REST API + Next.js 前台"
    exit 1
    ;;
esac
