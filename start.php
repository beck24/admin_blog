<?php

namespace Admin\Blog;

const PLUGIN_ID = 'admin_blog';

require_once __DIR__ . '/lib/hooks.php';
 
elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

/**
 * Init admin_blog plugin.
 */
function init() {

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
