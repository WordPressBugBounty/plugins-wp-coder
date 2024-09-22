<?php

defined( 'ABSPATH' ) || exit;

return [

	'inline' => [
		'type'  => 'checkbox',
		'name'  => '[inline_css]',
		'title' => __( 'Inline', 'wpcoder' ),
		'text'  => __( 'Enable', 'wpcoder' ),
	],

	'minified' => [
		'type'  => 'checkbox',
		'name'  => '[minified_css]',
		'title' => __( 'Minified', 'wpcoder' ),
		'text'  => __( 'Enable', 'wpcoder' ),
	],

	'css_code' => [
		'type'  => 'textarea',
		'name'  => 'css_code',
		'class' => 'is-full'
	],

];