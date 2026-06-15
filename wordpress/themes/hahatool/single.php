<?php
/** 单篇：工具 / 提示词 / 资讯 自动分流 */
if (!defined('ABSPATH')) exit;
get_header();
the_post();
$id = get_the_ID();
$is_prompt = (bool)hh_meta($id, 'prompt');
$is_tool = hahatool_is_tool($id);

if ($is_prompt) {
    get_template_part('template-parts/single-prompt');
} elseif ($is_tool) {
    get_template_part('template-parts/single-tool');
} else {
    get_template_part('template-parts/single-news');
}

get_footer();
