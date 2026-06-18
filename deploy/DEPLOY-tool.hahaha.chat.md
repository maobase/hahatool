# 部署 HahaTool → tool.hahaha.chat

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

## 6. 对象存储（MinIO）—— 见后续迭代
- 服务器 MinIO 已在跑（manyan-minio，S3 兼容，内网 9000）。
- 计划：建桶 `hahatool-media`，WP 装 S3 媒体插件（如 *Media Cloud* / *WP Offload Media* / `humanmade/s3-uploads`），
  endpoint 指向内网 MinIO，上传的图片走对象存储；Caddy 对 `/uploads` 等静态资源加长缓存（已在 snippet 内）。

## 7. 验证
```bash
curl -sL https://tool.hahaha.chat -o /dev/null -w "HTTP %{http_code}\n"
curl -sL https://tool.hahaha.chat/wp-sitemap.xml -o /dev/null -w "sitemap %{http_code}\n"
```

## 缓存策略（#3）
- **静态资源**（css/js/字体/图片）：Caddy `Cache-Control: public, max-age=31536000, immutable`（见 snippet）+ 主题资源已带版本号（`HAHATOOL_VERSION`）刷新。
- **HTML/REST**：不强缓存；如需可在 Cloudflare 层加页面缓存规则。
- 图片走 MinIO 后，可在对象存储/CDN 侧再设长缓存。

## 回滚
```bash
cd /opt/hahatool && docker compose -f deploy/docker-compose.prod.yml down   # 保留卷
# 数据在 named volume db_data / wp_data，down 不删卷
```
