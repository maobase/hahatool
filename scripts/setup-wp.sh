#!/usr/bin/env bash
# WordPress 一键初始化：安装 + 站点配置 + 导入示例数据
# 前提：docker compose up -d 已启动 db 与 wordpress
set -euo pipefail

cd "$(dirname "$0")/.."

if [ -f .env ]; then
  set -a; source .env; set +a
fi

WP_PORT="${WP_PORT:-8090}"
ADMIN_USER="${WP_ADMIN_USER:-admin}"
ADMIN_PASS="${WP_ADMIN_PASS:-hahatool_admin}"
ADMIN_MAIL="${WP_ADMIN_MAIL:-admin@hahatool.local}"

wpcli() { docker compose run --rm --no-deps wpcli wp "$@"; }

echo "==> 等待 WordPress 文件就绪 ..."
for i in $(seq 1 30); do
  if wpcli core version >/dev/null 2>&1; then break; fi
  sleep 2
done

if wpcli core is-installed >/dev/null 2>&1; then
  echo "==> WordPress 已安装，跳过安装步骤"
else
  echo "==> 安装 WordPress ..."
  wpcli core install \
    --url="http://localhost:${WP_PORT}" \
    --title="HahaTool 哈哈工具" \
    --admin_user="$ADMIN_USER" \
    --admin_password="$ADMIN_PASS" \
    --admin_email="$ADMIN_MAIL" \
    --skip-email
fi

echo "==> 站点配置（固定链接 / 评论策略 / 时区）..."
wpcli option update timezone_string 'Asia/Shanghai' >/dev/null
wpcli option update blogdescription '发现最好用的 AI 网站和工具' >/dev/null
wpcli rewrite structure '/%postname%/' --hard >/dev/null
# 评论：免审核直接显示（演示用；生产建议打开审核）
wpcli option update comment_moderation 0 >/dev/null
wpcli option update comment_previously_approved 0 >/dev/null
wpcli option update require_name_email 1 >/dev/null
# 清理默认内容
wpcli post delete 1 2 3 --force >/dev/null 2>&1 || true

echo "==> 导入示例数据 ..."
wpcli eval-file /scripts/seed-wp.php

# 为「提交工具」表单创建 Application Password（前台服务端写入待审文章用）
if ! grep -q '^WP_APP_PASS=' .env 2>/dev/null; then
  echo "==> 创建提交表单专用 Application Password ..."
  APP_PASS=$(wpcli user application-password create "$ADMIN_USER" hahatool-submit --porcelain | tr -d '\r')
  if [ -n "$APP_PASS" ]; then
    {
      echo ""
      echo "# ---- 提交工具表单（由 setup-wp.sh 自动生成）----"
      echo "WP_APP_USER=$ADMIN_USER"
      echo "WP_APP_PASS=$APP_PASS"
    } >> .env
    echo "    已写入 .env（WP_APP_USER / WP_APP_PASS），执行 docker compose up -d frontend 生效"
  else
    echo "    ⚠️ 创建失败，提交表单将不可用；可手动在 wp-admin → 用户 → 应用程序密码 创建后写入 .env"
  fi
fi

echo "✓ 完成！"
echo "  后台： http://localhost:${WP_PORT}/wp-admin/  （${ADMIN_USER} / ${ADMIN_PASS}）"
echo "  前台： http://localhost:${FRONTEND_PORT:-3000}"
