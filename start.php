<?php

namespace Admin\Blog;

const PLUGIN_ID = 'admin_blog';

require_once __DIR__ . '/lib/hooks.php';
 
elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

/**
 * Init admin_blog plugin.
 */
function init() {

	// routing of urls
	elgg_unregister_page_handler('blog', 'blog_page_handler');
	elgg_register_page_handler('blog', __NAMESPACE__ . '\\page_handler');

	elgg_register_plugin_hook_handler('register', 'menu:owner_block', __NAMESPACE__ . '\\owner_block_menu');
	
	elgg_register_action('blog/save', elgg_get_config('pluginspath') .'blog/actions/blog/save.php', 'admin');
	elgg_register_action('blog/auto_save_revision', elgg_get_config('pluginspath') .'blog/actions/blog/auto_save_revision.php', 'admin');
	elgg_register_action('blog/delete', elgg_get_config('pluginspath') . 'blog/actions/blog/delete.php', 'admin');
	
	$group_blogs = (int) elgg_get_plugin_setting('group_blog', PLUGIN_ID);
	$widget_contexts = array('profile', 'dashboard', 'index');
	if (!$group_blogs) {
		elgg_unextend_view('groups/tool_latest', 'blog/group_module');
		remove_group_tool_option('blog');
	} else {
		$widget_contexts[] = 'group';
	}
	
	elgg_unregister_widget_type('blog');
	elgg_register_widget_type('blog', elgg_echo('blog'), elgg_echo('blog:widget:description'), $widget_contexts);
}

/**
 * Dispatches blog pages.
 * URLs take the form of
 *  All blogs:       blog/all
 *  User's blogs:    blog/owner/<username>
 *  Friends' blog:   blog/friends/<username>
 *  User's archives: blog/archives/<username>/<time_start>/<time_stop>
 *  Blog post:       blog/view/<guid>/<title>
 *  New post:        blog/add/<guid>
 *  Edit post:       blog/edit/<guid>/<revision>
 *  Preview post:    blog/preview/<guid>
 *  Group blog:      blog/group/<guid>/all
 *
 * Title is ignored
 *
 * @todo no archives for all blogs or friends
 *
 * @param array $page
 * @return bool
 */
function page_handler($page) {

	elgg_load_library('elgg:blog');

	// push all blogs breadcrumb
	elgg_push_breadcrumb(elgg_echo('blog:blogs'), "blog/all");

	if (!isset($page[0])) {
		$page[0] = 'all';
	}

	$page_type = $page[0];
	switch ($page_type) {
		case 'owner':
			$user = get_user_by_username($page[1]);
			if (!$user || !$user->isAdmin()) {
				forward('', '404');
			}
			$params = blog_get_page_content_list($user->guid);
			if(!elgg_is_admin_logged_in()){
				elgg_unregister_menu_item('title','add');
			}
			
			$params['filter'] = false; // no need for all/mine/friends anymore
			break;
		case 'archive':
			$user = get_user_by_username($page[1]);
			if (!$user || !$user->isAdmin()) {
				forward('', '404');
			}
			$params = blog_get_page_content_archive($user->guid, $page[2], $page[3]);
			break;
		case 'view':
			$params = blog_get_page_content_read($page[1]);
			break;
		case 'add':
			elgg_admin_gatekeeper();
			$params = blog_get_page_content_edit($page_type, $page[1]);
			break;
		case 'edit':
			elgg_admin_gatekeeper();
			$params = blog_get_page_content_edit($page_type, $page[1], $page[2]);
			break;
		case 'group':
			$group_blogs = (int) elgg_get_plugin_setting('group_blog', PLUGIN_ID);
			
			if (!$group_blogs) {
				forward('', '404');
			}
			
			$group = get_entity($page[1]);
			if (!elgg_instanceof($group, 'group')) {
				forward('', '404');
			}
			if (!isset($page[2]) || $page[2] == 'all') {
				$params = blog_get_page_content_list($page[1]);
			} else {
				$params = blog_get_page_content_archive($page[1], $page[3], $page[4]);
			}
			if(!elgg_is_admin_logged_in()){
				elgg_unregister_menu_item('title','add');
			}
			break;
		case 'all':
			$params = blog_get_page_content_list();
			if(!elgg_is_admin_logged_in()){
				elgg_unregister_menu_item('title','add');
			}
			$params['filter'] = false;
			break;
		default:
			return false;
	}

	if (isset($params['sidebar'])) {
		$params['sidebar'] .= elgg_view('blog/sidebar', array('page' => $page_type));
	} else {
		$params['sidebar'] = elgg_view('blog/sidebar', array('page' => $page_type));
	}

	$body = elgg_view_layout('content', $params);

	echo elgg_view_page($params['title'], $body);
	return true;
}
