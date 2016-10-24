<?php

$page_type = elgg_extract('page_type', $vars);

$params = blog_get_page_content_list();

$params['sidebar'] = elgg_view('blog/sidebar', ['page' => $page_type]);
$params['filter'] = false;

if (!elgg_is_admin_logged_in()) {
	elgg_unregister_menu_item('title', 'add');
}

$body = elgg_view_layout('content', $params);

echo elgg_view_page($params['title'], $body);
