# 部署 HahaTool → tool.hahaha.chat

## ✅ 实际生产现状（2026-06-19 已上线）
`https://tool.hahaha.chat` 已上线，**服务 WordPress 版**（hahatool 主题）。实际链路与下方「方案 A（自建 Caddy + DNS）」不同，采用服务器上已有的 **Cloudflare Tunnel**：

```
用户 → Cloudflare(HTTPS) → Cloudflare Tunnel(haha-cloudflared)
     → hahatool-frontend:3000  (caddy:2-alpine 反代，docker-compose.override.yml)
     → hahatool-wordpress:80    (WordPress + hahatool 主题)
     → hahatool-db (mysql)
```
- 代码目录：服务器 `/opt/haha-apps/hahatool`（主题/插件经 `rsync` 同步）。
- 仅维护 WordPress：原 Next.js `frontend` 容器已替换为 Caddy 反代（保持容器名/端口/网络，隧道 ingress 无需改）。
- WP `home/siteurl = https://tool.hahaha.chat`；mu-plugin `https-behind-proxy.php` 在隧道后强制识别 HTTPS（避免回环）。
- 更新主题：本地 `rsync -az ./wordpress/ root@23.82.99.201:/opt/haha-apps/hahatool/wordpress/`（主题为挂载卷，即时生效）。
- 切回 Next.js（回滚）：恢复 `docker-compose.override.yml.bak.*` 后 `docker compose up -d --force-recreate frontend`。

---

# 方案 A（备用：自建 Caddy + DNS）—— 原始脚手架

> 目标：把 HahaTool（**仅 WordPress 版**）部署到服务器 `23.82.99.201`，对外域名 `tool.hahaha.chat`，
> 复用服务器上已有的 **Caddy**（自动 HTTPS）与 **MinIO**（对象存储）。
> 参考：`hahaha.chat`（Cloudflare Worker，仅静态）与该服务器的 `manyan` 栈（Docker + Caddy + MinIO）。

## 0. 前置（需人工提供）
- 服务器 SSH：`ssh root@23.82.99.201`（密码单独保管，**未写入仓库**）。
- 域名 `hahaha.chat` 在 Cloudflare（账户 `springs1218@gmail.com`，Zone ID `f827a21cc105d243088275cd5ce75a9c`）。

## 1. DNS（Cloudflare）
为子域加一条 A 记录指向服务器：

| 类型 | 名称 | 内容 | 代理 |
|------|------|------|------|
| A | `tool` (= tool.hahaha.chat) | `23.82.99.201` | **先 DNS-only（灰云）** |

> 先用 DNS-only，让 Caddy 通过 HTTP-01 直接签发 Let's Encrypt 证书；待 HTTPS 正常后，
> 若要套 Cloudflare CDN，再改为「橙云 proxied」并在 SSL/TLS 设为 **Full (strict)**。

## 2. 服务器：准备网络与代码
```bash
ssh root@23.82.99.201
# 共享反代网络（Caddy 与 hahatool 都接入）
docker network create edge 2>/dev/null || true
# 把现有 manyan 的 Caddy 接入 edge（容器名以实际为准）
docker network connect edge manyan-caddy-1 2>/dev/null || true

# 拉取代码
mkdir -p /opt/hahatool && cd /opt/hahatool
git clone https://github.com/maobase/hahatool.git . || git pull
cp deploy/.env.prod.example deploy/.env.prod
# 编辑 deploy/.env.prod 填强密码
```

## 3. 起栈（db + wordpress）
```bash
cd /opt/hahatool
docker compose -f deploy/docker-compose.prod.yml --env-file deploy/.env.prod up -d
docker compose -f deploy/docker-compose.prod.yml ps
```

## 4. Caddy：加路由
把 `deploy/Caddyfile.hahatool.snippet` 的内容追加到服务器 Caddy 的 Caddyfile，然后热重载：
```bash
# 假设 manyan 的 Caddy 用挂载的 Caddyfile
docker exec manyan-caddy-1 caddy reload --config /etc/caddy/Caddyfile
```

## 5. WordPress 初始化（首次）
用 wpcli 安装内核、装主题、导入种子数据（参考仓库 `scripts/setup-wp.sh`）：
```bash
RUN="docker compose -f deploy/docker-compose.prod.yml --env-file deploy/.env.prod run --rm wpcli wp"
$RUN core install --url=https://tool.hahaha.chat --title=HahaTool \
    --admin_user=admin --admin_password='<强密码>' --admin_email=admin@hahaha.chat
$RUN theme activate hahatool
$RUN rewrite structure '/%postname%/' && $RUN rewrite flush --hard
# 种子：分类/工具/资讯/专题（见 scripts/ 下各 seed 脚本）
bash scripts/setup-wp.sh   # 或逐个 eval-file scripts/seed-*.php
```

## ✅ 对象存储（MinIO）—— 已接入
- 复用服务器 `manyan-minio-1`（S3 兼容，`manyan_default` 网络），新建公共读桶 `hahatool-media`：
  ```sh
  docker run --rm --network manyan_default --entrypoint sh minio/mc -c \
    "mc alias set m http://manyan-minio-1:9000 <ROOT_USER> <ROOT_PASS>; mc mb -p m/hahatool-media; mc anonymous set download m/hahatool-media"
  ```
- 公网路由：`deploy/Caddyfile.proxy` 内 `handle_path /media/* → reverse_proxy manyan-minio-1:9000`，
  即 `https://tool.hahaha.chat/media/hahatool-media/<obj>` 直读对象存储；**仅 2xx 加 1 年 immutable 缓存**（避免缓存 404）。
- 资讯封面已从第三方 picsum 迁移到对象存储：图片 `mc cp` 到 `news/<slug>.jpg`，文章 `cover` meta 改为 `/media/...?v=N`。
- 上传图片：`docker run -v /tmp:/data --network manyan_default --entrypoint sh minio/mc -c 'mc alias set m ...; mc cp /data/x.jpg m/hahatool-media/news/x.jpg'`。

## 6. 对象存储（MinIO）—— 历史说明
- 服务器 MinIO 已在跑（manyan-minio，S3 兼容，内网 9000）。
- 计划：建桶 `hahatool-media`，WP 装 S3 媒体插件（如 *Media Cloud* / *WP Offload Media* / `humanmade/s3-uploads`），
  endpoint 指向内网 MinIO，上传的图片走对象存储；Caddy 对 `/uploads` 等静态资源加长缓存（已在 snippet 内）。

## 7. 验证
```bash
curl -sL https://tool.hahaha.chat -o /dev/null -w "HTTP %{http_code}\n"
curl -sL https://tool.hahaha.chat/wp-sitemap.xml -o /dev/null -w "sitemap %{http_code}\n"
```

## ✅ Redis 对象缓存（复用现有 Redis，已接入）
- **复用** 服务器现有 `manyan-redis-1`（`manyan_default` 网络，无密码），**不新建 Redis 容器**。
- 隔离：专用 **DB 索引 7** + 键前缀 `hahatool:`（与其他应用互不影响；实测 DB7 约 560+ 键，DB0 不受影响）。
- 接入方式（Docker 原生）：
  - `docker-compose.override.yml` 给 `wordpress` / `wpcli` 加 `hahanet`(=`manyan_default`) 网络 → 可解析 `manyan-redis-1`。
  - `wp-config.php` 写入常量（`wp config set`）：`WP_REDIS_HOST=manyan-redis-1`、`PORT=6379`、`DATABASE=7`、`PREFIX=hahatool:`、`TIMEOUT/READ_TIMEOUT=1`、**`MAXTTL=604800`**。
  - 插件 `redis-cache`(Till Krüss) 提供 `object-cache.php` drop-in，客户端 **Predis（纯 PHP，无需装 php-redis 扩展）**。
- **共享 Redis 良民**：`manyan-redis-1` 为 `maxmemory 0` + `noeviction`（键不会自动淘汰），故必须设 `WP_REDIS_MAXTTL=604800`(7天) 让缓存键到期自动过期，避免无限增长影响其他应用。改 MAXTTL 后需 `wp cache flush` 让旧键（TTL=-1）重建为带 TTL（实测重建后 TTL≈604788）。
- 运维：`docker compose run --rm -T wpcli wp redis status|enable|disable --allow-root` + `wp cache flush`（`-T` 必加，否则 `docker compose run` 会吞掉 heredoc 的 stdin）。出问题即 `wp redis disable` 秒级回滚（删 drop-in）。已验证 `docker compose up -d wordpress` 重启后缓存仍 Connected。

## Cloudflare 边缘设置（zone: hahaha.chat, id f827a21cc105d243088275cd5ce75a9c）
> 该 zone 托管多个应用，改 zone 级设置会影响全部子域，谨慎。用 API（从服务器执行，本机 DNS 解析不了 api.cloudflare.com）：
> `curl -s [-X PATCH] https://api.cloudflare.com/client/v4/zones/<ZID>/settings/<key> -H "X-Auth-Email: <email>" -H "X-Auth-Key: <globalkey>" [-H "Content-Type: application/json" --data '{"value":"on"}']`

- ✅ 已开启（安全、纯增益）：`brotli`、`http3`、**`0rtt`**、**`early_hints`**（后两项本轮开启，加速 TLS 恢复与 103 资源预提示，不改变任何行为、不会破坏应用）。
- ⚠️ 建议但**未自动改**（影响全 zone 行为，留待确认）：
  - `always_use_https=on`：当前 `off`（zone 级未改，避免影响其他应用）。**hahatool 自身的 http→https 已在 WP 层解决**：mu-plugin `force-https-redirect.php` 凭 `CF-Visitor` 头（需 `wp_unslash`，因 `wp_magic_quotes` 会转义；X-Forwarded-Proto 被 Caddy 写死 https 不可用）判定原始 http 并 301 跳 https，仅作用于本站、无环、检测不到则无害空操作。
  - `min_tls_version=1.2`：当前 `1.0`（已弃用/不安全），建议提到 1.2。
  - 仅作用于 hahatool 而不动全 zone 的话：用 Redirect Rule 限定 `http.host eq "tool.hahaha.chat"` 跳 https（注意避免重定向环）。

## 缓存策略（#3）
- **静态资源**（css/js/字体/图片）：Caddy `Cache-Control: public, max-age=31536000, immutable`（见 snippet）+ 主题资源已带版本号（`HAHATOOL_VERSION`）刷新。
- **对象缓存**：WP 对象缓存走 Redis（见上节），降低 DB 查询、加速动态页面。
- **HTML/REST**：不强缓存；如需可在 Cloudflare 层加页面缓存规则。
- 图片走 MinIO 后，可在对象存储/CDN 侧再设长缓存。

## 回滚
```bash
cd /opt/hahatool && docker compose -f deploy/docker-compose.prod.yml down   # 保留卷
# 数据在 named volume db_data / wp_data，down 不删卷
```
