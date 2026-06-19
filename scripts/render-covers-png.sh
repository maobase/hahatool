#!/usr/bin/env bash
# 把品牌封面 SVG 渲染为 PNG 并上传对象存储，供社媒 og:image 使用
# （多数平台不渲染 SVG og:image；页面 <img> 仍用 SVG）。幂等，可重复执行。
#
# 用法（本机有 Chrome + 已配好到服务器的 SSH）：
#   bash scripts/render-covers-png.sh
set -euo pipefail
SRV=root@23.82.99.201
CHROME="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
OUT=/tmp/hh-covers

# 1) 生成全部封面 SVG
python3 "$(dirname "$0")/gen-news-covers.py"

# 2) 逐个渲染为 1200×600 PNG
mkdir -p "$OUT/png"
for svg in "$OUT"/*.svg; do
  b=$(basename "$svg" .svg)
  "$CHROME" --headless=new --disable-gpu --force-device-scale-factor=1 \
    --window-size=1200,600 --screenshot="$OUT/png/$b.png" "file://$svg" >/dev/null 2>&1
done
echo "rendered $(ls "$OUT"/png/*.png | wc -l) PNGs"

# 3) 上传：topic-* → topics/<slug>.png，其余 → news/covers/<slug>.png
ssh -o BatchMode=yes "$SRV" 'rm -rf /tmp/cov-png && mkdir -p /tmp/cov-png'
scp -q "$OUT"/png/*.png "$SRV":/tmp/cov-png/
ssh -o BatchMode=yes "$SRV" 'bash -s' <<'EOF'
U="$(docker inspect manyan-minio-1 --format '{{range .Config.Env}}{{println .}}{{end}}' | sed -n 's/^MINIO_ROOT_USER=//p')"
P="$(docker inspect manyan-minio-1 --format '{{range .Config.Env}}{{println .}}{{end}}' | sed -n 's/^MINIO_ROOT_PASSWORD=//p')"
docker run --rm -v /tmp/cov-png:/data --network manyan_default --entrypoint sh minio/mc -c "
  mc alias set m http://manyan-minio-1:9000 '$U' '$P' >/dev/null
  for f in /data/*.png; do b=\${f##*/}; case \$b in
    topic-*) mc cp -q \"\$f\" m/hahatool-media/topics/\${b#topic-} >/dev/null;;
    *) mc cp -q \"\$f\" m/hahatool-media/news/covers/\$b >/dev/null;; esac
  done; echo uploaded"
EOF
echo "done"
