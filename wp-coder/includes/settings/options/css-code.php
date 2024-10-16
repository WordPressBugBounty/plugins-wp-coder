<?php

defined( 'ABSPATH' ) || exit;

return [

	'inline' => [
		'type'  => 'checkbox',
		'name'  => '[inline_css]',
		'title' => __( 'Inline', 'wp-coder' ),
		'text'  => __( 'Enable', 'wp-coder' ),
	],

	'minified' => [
		'type'  => 'checkbox',
		'name'  => '[minified_css]',
		'title' => __( 'Minified', 'wp-coder' ),
		'text'  => __( 'Enable', 'wp-coder' ),
	],

	'css_code' => [
		'type'  => 'textarea',
		'name'  => 'css_code',
		'class' => 'is-full'
	],

];