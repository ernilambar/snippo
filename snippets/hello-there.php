<?php
/**
 * Hello There Snippet Configuration
 *
 * @package Snippo
 */

return [
	'title'      => 'Hello There',
	'categories' => [
		'beginner',
	],
	'fields'     => [
		[
			'name'  => 'name',
			'label' => 'Name',
			'type'  => 'text',
		],
	],
	'template'   => "Hello there, <b>{{name}}</b>.\n<br>\nHave a great day!",
];
