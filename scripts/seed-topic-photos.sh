#!/usr/bin/env bash
# 专题封面：从 Unsplash（免版权）下载精选高质量照片 → 自有对象存储，替换原扁平 SVG 渐变封面，提升「高级感」。
# 照片均经人工挑选、与专题主题相关；卡片上叠加品牌色渐变遮罩（CSS .topic-card-cover::after）保证视觉统一。
# 在服务器执行（需可访问 manyan-minio-1）。幂等：重跑覆盖同名对象并重写 topic_cover meta。
set -e
declare -a MAP=(
  "ai-writing|1488190211105-8b0e65b80b4e"       # writing desk
  "ai-office|1556761175-4b46a572b786"           # modern office
  "ai-learn|1521587760476-6c12a4b040da"         # library books
  "ai-search|1644088379091-d574269d422f"        # data / technology
  "ai-avatar|1478737270239-2f02b77fc618"        # podcast microphone studio
  "ai-coding|1461749280684-dccba630e2f6"        # blue code on screen
  "ai-marketing|1533750349088-cd871a92f312"     # marketing / creative
  "ai-video-create|1607276159787-9ef4db5c0d0b"  # film camera / cinema
  "ai-audio|1598488035139-bdbb2231ce04"         # music studio
)
mkdir -p /tmp/topicphoto && rm -f /tmp/topicphoto/*.jpg
for it in "${MAP[@]}"; do s="${it%%|*}"; id="${it##*|}"; id="${id%% *}"
  curl -sL -o "/tmp/topicphoto/$s.jpg" --max-time 30 \
    "https://images.unsplash.com/photo-$id?w=1200&h=600&q=80&fit=crop&auto=format"
  echo "$s: $(stat -c%s "/tmp/topicphoto/$s.jpg" 2>/dev/null)b"
done
U="$(docker inspect manyan-minio-1 --format '{{range .Config.Env}}{{println .}}{{end}}' | sed -n 's/^MINIO_ROOT_USER=//p')"
P="$(docker inspect manyan-minio-1 --format '{{range .Config.Env}}{{println .}}{{end}}' | sed -n 's/^MINIO_ROOT_PASSWORD=//p')"
docker run --rm -v /tmp/topicphoto:/data --network manyan_default --entrypoint sh minio/mc -c "
  mc alias set m http://manyan-minio-1:9000 '$U' '$P' >/dev/null
  mc cp -q /data/*.jpg m/hahatool-media/topics/photo/ >/dev/null && echo uploaded"
docker exec hahatool-wordpress php -r '
require "/var/www/html/wp-load.php";
$base="https://tool.hahaha.chat/media/hahatool-media/topics/photo/";
foreach(["ai-writing","ai-office","ai-learn","ai-search","ai-avatar","ai-coding","ai-marketing","ai-video-create","ai-audio"] as $s){
  $t=get_term_by("slug",$s,"topic"); if($t){ update_term_meta($t->term_id,"topic_cover",$base.$s.".jpg"); }
}
echo "topic_cover meta updated\n";'
cd /opt/haha-apps/hahatool && docker compose run --rm -T wpcli wp cache flush --allow-root
