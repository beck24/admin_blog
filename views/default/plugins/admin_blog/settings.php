<?php

$title = elgg_echo('admin_blog:enable:group_blog');

$body = elgg_view('input/dropdown', array(
	'name' => 'params[group_blog]',
	'value' => (int) $vars['entity']->group_blog,
	'options_values' => array(
		1 => elgg_echo('option:yes'),
		0 => elgg_echo('option:no')
	)
));
$body .= elgg_view('output/longtext', array(
	'value' => elgg_echo('admin_blog:enable:group_blog:help'),
	'class' => 'elgg-subtext mtm'
));

echo elgg_view_module('main', $title, $body);