<?php

defined( 'ABSPATH' ) || exit;

return [

	'jquery' => [
		'type'  => 'checkbox',
		'name'  => '[jquery_dependency]',
		'title' => __( 'JQuery Dependency', 'wpcoder' ),
		'text'  => __( 'Disable', 'wpcoder' ),
	],

	'inline' => [
		'type'  => 'checkbox',
		'name'  => '[inline_js]',
		'title' => __( 'Inline', 'wpcoder' ),
		'text'  => __( 'Enable', 'wpcoder' ),
	],

	'minified'   => [
		'type'    => 'select',
		'name'    => '[minified_js]',
		'title'   => __( 'Minified', 'floating-button' ),
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
		'title'   => __( 'Attribute', 'floating-button' ),
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