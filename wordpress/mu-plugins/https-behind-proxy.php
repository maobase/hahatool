<?php
/**
 * Plugin Name: HahaTool HTTPS-behind-proxy
 * Description: 在 Cloudflare Tunnel / 反向代理后正确识别 HTTPS，避免重定向回环。
 *   仅当转发头表明 https，或主机为生产域名时生效——本地 http 开发不受影响。
 */
if (!defined('ABSPATH')) exit;

$hh_xfp  = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
$hh_host = $_SERVER['HTTP_HOST'] ?? '';
if ($hh_xfp === 'https' || $hh_host === 'tool.hahaha.chat') {
    $_SERVER['HTTPS'] = 'on';
}
