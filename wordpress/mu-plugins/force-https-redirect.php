<?php
/**
 * http → https 301 跳转（仅 hahatool 站，不影响同 zone 其他应用）。
 * 仅当 Cloudflare 的 CF-Visitor 头明确标识原始请求为 http 时才跳转；无该头/为 https 时不动，
 * 因此绝不会产生重定向环，最坏情况只是「检测不到则不跳」的无害空操作。
 * 与 https-behind-proxy.php 互补：后者让 WP 生成 https URL，本插件把 http 访问导向 https。
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    if (is_admin() || (defined('WP_CLI') && WP_CLI) || (defined('DOING_CRON') && DOING_CRON) || (defined('REST_REQUEST') && REST_REQUEST)) return;
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'HEAD') return;
    // WP 的 wp_magic_quotes() 会给 $_SERVER 值加反斜杠，需 wp_unslash 还原后再判断（X-Forwarded-Proto 被 Caddy 写死 https，故只信 CF-Visitor）
    $cf = wp_unslash($_SERVER['HTTP_CF_VISITOR'] ?? '');
    if ($cf !== '' && strpos($cf, '"scheme":"http"') !== false) {
        $host = $_SERVER['HTTP_HOST'] ?? 'tool.hahaha.chat';
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        wp_redirect('https://' . $host . $uri, 301);
        exit;
    }
}, 1);
