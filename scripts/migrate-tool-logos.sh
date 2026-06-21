#!/usr/bin/env bash
# 把工具 Logo 从第三方 favicon.im 迁移到自有对象存储（目标 #3：图片接入对象存储、去第三方依赖）。
# 在服务器上执行（需可访问 hahatool-wordpress、manyan-minio-1）。幂等：重跑只会覆盖同名对象、重写 meta。
#
# 取图源：Google favicons（服务端可达，favicon.im 对数据中心 IP 返回 403）。
# Google 返回非 PNG（JPEG）或空白占位（<200B）时，回退 DuckDuckGo ico。最终按真实格式定扩展名。
set -e
mkdir -p /tmp/toollogos; rm -f /tmp/toollogos/* /tmp/toollist.txt /tmp/oklist.txt

# 1. 导出 slug|域名（从现有 favicon.im logo 解析）
docker exec hahatool-wordpress php -r '
require "/var/www/html/wp-load.php";
$q=new WP_Query(["posts_per_page"=>300,"meta_key"=>"url","post_status"=>"publish"]);
foreach($q->posts as $p){
  if(!hahatool_is_tool($p->ID))continue;
  $logo=get_post_meta($p->ID,"logo",true);
  if(strpos($logo,"favicon.im/")===false)continue;
  echo $p->post_name."|".preg_replace("#\?.*$#","",preg_replace("#^https?://favicon\.im/#","",$logo))."\n";
}' > /tmp/toollist.txt

# 2. 下载 + 校验，记录 slug|文件名
while IFS='|' read slug dom; do
  [ -z "$dom" ] && continue
  g="/tmp/toollogos/$slug.png"
  curl -sL -o "$g" --max-time 20 "https://www.google.com/s2/favicons?domain=$dom&sz=128" || true
  t="$(file -b "$g" 2>/dev/null)"; sz="$(stat -c%s "$g" 2>/dev/null || echo 0)"
  if echo "$t" | grep -qi 'PNG image' && [ "$sz" -gt 200 ]; then
    echo "$slug|$slug.png" >> /tmp/oklist.txt; continue
  fi
  if echo "$t" | grep -qi 'JPEG'; then mv "$g" "/tmp/toollogos/$slug.jpg"; echo "$slug|$slug.jpg" >> /tmp/oklist.txt; continue; fi
  # Google 不可用（空白/异常）→ DuckDuckGo ico
  rm -f "$g"; i="/tmp/toollogos/$slug.ico"
  if curl -sfL -o "$i" --max-time 20 "https://icons.duckduckgo.com/ip3/$dom.ico" && [ "$(stat -c%s "$i")" -gt 500 ]; then
    echo "$slug|$slug.ico" >> /tmp/oklist.txt
  else echo "FAIL $slug ($dom)"; rm -f "$i"; fi
done < /tmp/toollist.txt
echo "成功 $(wc -l < /tmp/oklist.txt) / $(wc -l < /tmp/toollist.txt)"

# 3. 上传 MinIO（mc 自动按扩展名设 content-type）
U="$(docker inspect manyan-minio-1 --format '{{range .Config.Env}}{{println .}}{{end}}' | sed -n 's/^MINIO_ROOT_USER=//p')"
P="$(docker inspect manyan-minio-1 --format '{{range .Config.Env}}{{println .}}{{end}}' | sed -n 's/^MINIO_ROOT_PASSWORD=//p')"
docker run --rm -v /tmp/toollogos:/data --network manyan_default --entrypoint sh minio/mc -c "
  mc alias set m http://manyan-minio-1:9000 '$U' '$P' >/dev/null
  mc cp -q /data/* m/hahatool-media/tools/logos/ >/dev/null && echo 上传完成"

# 4. 重写 logo meta 指向自托管
docker cp /tmp/oklist.txt hahatool-wordpress:/tmp/oklist.txt
docker exec hahatool-wordpress php -r '
require "/var/www/html/wp-load.php";
$base="https://tool.hahaha.chat/media/hahatool-media/tools/logos/"; $n=0;
foreach(array_filter(array_map("trim",explode("\n",file_get_contents("/tmp/oklist.txt")))) as $line){
  [$slug,$file]=explode("|",$line);
  if($p=get_page_by_path($slug,OBJECT,"post")){ update_post_meta($p->ID,"logo",$base.$file); $n++; }
}
echo "更新 meta $n 个\n";'
docker exec hahatool-wordpress rm -f /tmp/oklist.txt
cd /opt/haha-apps/hahatool && docker compose run --rm -T wpcli wp cache flush --allow-root
