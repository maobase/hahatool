#!/usr/bin/env bash
# 资讯封面：用 Unsplash（免版权）真实照片替换扁平 SVG 封面，每篇按主题配相关图（人工挑选、逐篇映射）。
# 主题桶：AI/大模型、机器人、芯片、融资资本、AI 视频、编程/Agent、AR 硬件、大会/政策。
# 在服务器执行（可访问 Unsplash CDN 与 manyan-minio-1）。幂等：覆盖同名对象并重写 cover meta。
set -e
declare -a MAP=(
  "ai-consumption-policy|1540575467063-178a50c2df87"
  "smart-glasses-2026|1518558406542-3dc7f0e69a40"
  "alipay-ai-agent|1443934732608-9de53a872e32"
  "liblibai-evoken-arr|1590283603385-17ffb3a7f29f"
  "openai-codex-record-replay|1515879218367-8466d910aaa4"
  "waic-2026-countdown|1505373877841-8d25f7d46678"
  "star-market-ai-llm|1611974789855-9c2a0a7236a3"
  "daxiao-world-model-funding|1737644467636-6b0053476bb2"
  "suanmiao-tokenpu-tapeout|1562408590-e32931084e23"
  "deepseek-mega-funding|1645226880663-81561dcab0ae"
  "enflame-ipo-approved|1592659762303-90081d34b277"
  "ai-agent-enterprise-2026|1542831371-29b0f74f9713"
  "ai-agent-day-level|1607799279861-4dd421887fb3"
  "multimodal-models-leap|1697577418970-95d99b5a55cf"
  "open-source-llm-catchup|1674027444485-cec3da58eef4"
  "anthropic-revenue-47b|1651341050677-24dba59ce0fd"
  "ai-coding-benchmark|1607706189992-eae578626c86"
  "deepseek-v4-opensource|1694903110330-cc64b7e1d21d"
  "ai-video-cost-down|1632187981988-40f3cbaeef5e"
  "openai-gpt-5-5|1677442135703-1787eea5ce01"
  "ai-regulation-update|1504384764586-bb4cdc1707b0"
  "ai-video-review|1612548403247-aa2873e9422d"
  "gemini-3-pro-multimodal|1694903089438-bf28d4697d9a"
  "chatgpt-code-interpreter-upgrade|1619410283995-43d9134e7656"
  "ai-traffic-2026-05|1560221328-12fe60f83ab8"
  "claude-fable-5-release|1591696331111-ef9586a5b17a"
  "china-ai-going-global|1582192730841-2a682d7375f9"
  "ai-video-minute-era|1619099619226-f96e319e3732"
  "openai-robotics-team|1601132359864-c974e79890ac"
  "nvidia-cosmos-3|1507146153580-69a1fe6d8aa1"
  "nvidia-unitree-humanoid|1593376853899-fbb47a057fa0"
  "aizrobotics-b-round|1516192518150-0d8fee5425e3"
  "anthropic-ipo-s1|1621264448270-9ef00e88a935"
  "minimax-m3-release|1617839625591-e5a789593135"
  "google-antigravity-2|1461749280684-dccba630e2f6"
)
mkdir -p /tmp/newsphoto && rm -f /tmp/newsphoto/*.jpg /tmp/newsphoto/_ok.txt
ok=0
for it in "${MAP[@]}"; do s="${it%%|*}"; id="${it##*|}"
  curl -sL -o "/tmp/newsphoto/$s.jpg" --max-time 30 \
    "https://images.unsplash.com/photo-$id?w=1200&h=600&q=80&fit=crop&auto=format"
  sz=$(stat -c%s "/tmp/newsphoto/$s.jpg" 2>/dev/null || echo 0)
  if file -b "/tmp/newsphoto/$s.jpg" | grep -qi JPEG && [ "$sz" -gt 8000 ]; then ok=$((ok+1)); echo "$s" >> /tmp/newsphoto/_ok.txt; else echo "FAIL $s ($sz)"; rm -f "/tmp/newsphoto/$s.jpg"; fi
done
echo "下载成功 $ok / ${#MAP[@]}"
U="$(docker inspect manyan-minio-1 --format '{{range .Config.Env}}{{println .}}{{end}}' | sed -n 's/^MINIO_ROOT_USER=//p')"
P="$(docker inspect manyan-minio-1 --format '{{range .Config.Env}}{{println .}}{{end}}' | sed -n 's/^MINIO_ROOT_PASSWORD=//p')"
docker run --rm -v /tmp/newsphoto:/data --network manyan_default --entrypoint sh minio/mc -c "
  mc alias set m http://manyan-minio-1:9000 '$U' '$P' >/dev/null
  mc cp -q /data/*.jpg m/hahatool-media/news/photo/ >/dev/null && echo uploaded"
docker cp /tmp/newsphoto/_ok.txt hahatool-wordpress:/tmp/news_ok.txt
docker exec hahatool-wordpress php -r '
require "/var/www/html/wp-load.php";
$base="https://tool.hahaha.chat/media/hahatool-media/news/photo/";
$oks=array_filter(array_map("trim", explode("\n", file_get_contents("/tmp/news_ok.txt"))));
$n=0;
foreach($oks as $s){ $p=get_page_by_path($s,OBJECT,"post"); if($p){ update_post_meta($p->ID,"cover",$base.$s.".jpg"); $n++; } }
echo "cover meta updated for $n posts\n";'
docker exec hahatool-wordpress rm -f /tmp/news_ok.txt
cd /opt/haha-apps/hahatool && docker compose run --rm -T wpcli wp cache flush --allow-root
