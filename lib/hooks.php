<?php

namespace Admin\Blog;

/**
 * Modify which owner blocks show blog links
 * 
 * @param type $hook
 * @param type $type
 * @param type $return
 * @param type $params
 */
function owner_block_menu($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'user') && is_array($return)) {
		foreach ($return as $key => $r) {
			if ($r->getName() == 'blog' && !$params['entity']->isAdmin()) {
				unset($return[$key]);
			}
		}
	} else {
		if ($params['entity']->blog_enable != "no") {
			$group_blogs = (int) elgg_get_plugin_setting('group_blog', PLUGIN_ID);
			
			if (!$group_blogs && is_array($return)) {
				foreach ($return as $key => $r) {
					if ($r->getName() == 'blog') {
						unset($return[$key]);
					}
				}
			}
		}
	}
	
	return $return;
}