# HahaTool 安装手册

本手册指导你在本地用 Docker 从零部署 HahaTool（WordPress + Next.js）。

## 0. 前置要求

- Docker Desktop（或 Docker Engine 24+ 与 Compose v2）
- 空闲端口：`8090`（WordPress）、`3000`（前台）

## 1. 准备环境变量

```bash
cd hahatool
cp .env.example .env
```

建议至少修改 `DB_ROOT_PASSWORD` / `DB_PASSWORD` / `WP_ADMIN_PASS`。

## 2. 构建并启动

```bash
docker compose up -d --build
docker compose ps    # db 应为 healthy，wordpress / frontend 为 running
```

| 服务 | 地址 | 说明 |
| --- | --- | --- |
| `db` | 容器内 `db:3306` | MySQL 8 |
| `wordpress` | http://localhost:8090 | 内容后台（初始化前是安装页） |
| `frontend` | http://localhost:3000 | 前台（初始化前显示空状态，属正常） |

## 3. 一键初始化（安装 + 示例数据）

```bash
bash scripts/setup-wp.sh
```

脚本会自动完成：WordPress 安装（站点名、管理员账号取自 `.env`）、固定链接、时区、评论策略（演示用免审核）、删除默认内容，并导入示例数据（11 分类、14 标签、28 工具、4 资讯、8 快讯、1 条演示评论）。脚本可重复执行，按 slug 去重。

> 不想用脚本也可以浏览器访问 `http://localhost:8090` 手动走 WordPress 安装向导，再执行
> `docker compose run --rm wpcli wp eval-file /scripts/seed-wp.php` 导入数据。

## 4. 验收

- 前台 `http://localhost:3000`：首页应有快讯跑马灯、编辑精选、分类板块（前台 60 秒 ISR 缓存，初始化后最多等 1 分钟）；
- `http://localhost:3000/compare`：工具 PK 页有雷达图；
- 后台 `http://localhost:8090/wp-admin/`：用 `.env` 中的管理员账号登录。

## 常见问题

**前台一直显示「暂无数据」？**
1. 确认已执行 `bash scripts/setup-wp.sh` 且输出 `Success`；
2. 自检 API：`curl http://localhost:8090/wp-json/wp/v2/posts?per_page=1` 应返回 JSON 数组；
3. 前台有 60 秒 ISR 缓存，稍等后强制刷新。

**自定义字段（meta）没出现在 REST 响应里？**
确认 `wordpress/mu-plugins/hahatool.php` 已挂载（compose 卷映射），该文件负责把所有字段注册到 REST。

**端口被占用？**
修改 `.env` 的 `WP_PORT` / `FRONTEND_PORT` 后 `docker compose up -d`。注意 WordPress 的站点 URL 在安装时已写死，改端口后需执行：
`docker compose run --rm wpcli wp option update siteurl http://localhost:<新端口> && docker compose run --rm wpcli wp option update home http://localhost:<新端口>`

**重装一切？**
```bash
docker compose down -v
docker compose up -d --build
bash scripts/setup-wp.sh
```
