# HahaTool 常用命令
.PHONY: up down build logs setup dev clean

up:            ## 构建并启动全部服务
	docker compose up -d --build

down:          ## 停止服务
	docker compose down

build:         ## 仅重新构建镜像
	docker compose build

logs:          ## 跟踪日志
	docker compose logs -f

setup:         ## 初始化 WordPress 并导入示例数据（首次部署后执行一次）
	bash scripts/setup-wp.sh

dev:           ## 本地启动前台开发服务器（热更新）
	cd frontend && npm install && \
	WP_API_BASE=http://localhost:$${WP_PORT:-8090}/wp-json \
	npm run dev

clean:         ## 停止并删除数据卷（慎用：清空数据库与 WordPress）
	docker compose down -v
