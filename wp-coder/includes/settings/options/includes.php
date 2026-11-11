<?php

defined( 'ABSPATH' ) || exit;

return [

	'include' => [
		'type'    => 'select',
		'name'    => '[include]',
		'title'   => __( 'Type', 'wp-coder' ),
		// Labels only; the stored values stay "css" / "js". The type follows the
		// URL's extension as it is typed (admin-jquery.js).
		'options' => [
			'css' => __( 'CSS (stylesheet)', 'wp-coder' ),
			'js'  => __( 'JS (script)', 'wp-coder' ),
		],
		'class'   => 'display-option',
	],

	'include_file' => [
		'type'        => 'url',
		'name'        => '[include_file]',
		'title'       => __( 'URL', 'wp-coder' ),
		'class'       => 'is-grow include-file',
		'placeholder' => 'https://cdn.example.com/library.min.js',
	],

	'css_only_preview' => [
		'type'    => 'checkbox',
		'name'    => '[css_only_preview]',
		'title'   => __( 'Preview only', 'wp-coder' ),
		'text'  => __( 'Enable', 'wp-coder' ),
		'class' => 'is-reverse css-only-preview',
		'tooltip' => __( 'This CSS file will be used only in the Live Preview. It won’t be included on the actual website.', 'wp-coder' ),
	],

	'js_attr' => [
		'type'  => 'select',
		'name'  => '[file_js_att]',
		'title' => __( 'Loading', 'wp-coder' ),
		'options' => [
			0       => __( 'Normal', 'wp-coder' ),
			'defer' => __( 'Defer', 'wp-coder' ),
			'async' => __( 'Async', 'wp-coder' ),
		],
		'class' => 'js-attr',
		'tooltip' => __( 'Defer: runs after the page is parsed, in order. Async: runs as soon as it arrives, in any order.', 'wp-coder' ),
	],

	'dequeue' => [
		'type'    => 'select',
		'name'    => '[dequeue]',
		'title'   => __( 'Type', 'wp-coder' ),
		'options' => [
			'css' => __( 'CSS (stylesheet)', 'wp-coder' ),
			'js'  => __( 'JS (script)', 'wp-coder' ),
		],
		'class' => 'display-option',
	],

	'handle' => [
		'type'  => 'text',
		'name'  => '[handle]',
		'title' => __( 'ID', 'wp-coder' ),
	],


];