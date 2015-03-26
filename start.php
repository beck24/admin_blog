<?php
/**
 * Blogs
 *
 * @package Blog
 *
 * @todo
 * - Either drop support for "publish date" or duplicate more entity getter
 * functions to work with a non-standard time_created.
 * - Pingbacks
 * - Notifications
 * - River entry for posts saved as drafts and later published
 */
 
elgg_register_event_handler('init', 'system', 'admin_blog_init');

/**
 * Init admin_blog plugin.
 */
function admin_blog_init() {

	// routing of urls
	elgg_unregister_page_handler('blog', 'blog_page_handler');
	elgg_register_page_handler('blog', 'admin_blog_page_handler');
	
	// unregister actions
	$action_path = elgg_get_plugins_path() . 'blog/actions/blog';
	elgg_unregister_action('blog/save', "$action_path/save.php");
	elgg_unregister_action('blog/auto_save_revision', "$action_path/auto_save_revision.php");
	elgg_unregister_action('blog/delete', "$action_path/delete.php");
	
	// register actions
	$action_path = elgg_get_plugins_path() . 'admin_blog/actions/blog';
	elgg_register_action('blog/save', "$action_path/save.php");
	elgg_register_action('blog/auto_save_revision', "$action_path/auto_save_revision.php");
	elgg_register_action('blog/delete', "$action_path/delete.php");
	
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
function admin_blog_page_handler($page) {

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
			if (!$user) {
				forward('', '404');
			}
			$params = blog_get_page_content_list($user->guid);
			if(!elgg_is_admin_logged_in()){
				elgg_unregister_menu_item('title','add');
			}
			break;
		case 'friends':
			$user = get_user_by_username($page[1]);
			if (!$user) {
				forward('', '404');
			}
			$params = blog_get_page_content_friends($user->guid);
			if(!elgg_is_admin_logged_in()){
				elgg_unregister_menu_item('title','add');
			}
			break;
		case 'archive':
			$user = get_user_by_username($page[1]);
			if (!$user) {
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
