<?php

defined( 'ABSPATH' ) || exit;

return [

	'jquery' => [
		'type'  => 'checkbox',
		'name'  => '[jquery_dependency]',
		'title' => __( 'JQuery Dependency', 'wp-coder' ),
		'text'  => __( 'Disable', 'wp-coder' ),
	],

	'inline' => [
		'type'  => 'checkbox',
		'name'  => '[inline_js]',
		'title' => __( 'Inline', 'wp-coder' ),
		'text'  => __( 'Enable', 'wp-coder' ),
	],

	'minified'   => [
		'type'    => 'select',
		'name'    => '[minified_js]',
		'title'   => __( 'Minified', 'wp-coder' ),
		'default' => 'obfuscate',
		'options' => [
			'none'      => 'none',
			'minify'    => 'Minify',
			'obfuscate' => 'Obfuscate'
		],
	],

	'attributes'   => [
		'type'    => 'select',
		'name'    => '[js_attributes]',
		'title'   => __( 'Attribute', 'wp-coder' ),
		'options' => [
			0       => 'none',
			'defer' => 'defer',
			'async' => 'async'
		],
	],

	'js_code' => [
		'type'  => 'textarea',
		'name'  => 'js_code',
		'class' => 'is-full'
	],


];