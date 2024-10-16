<?php

use WPCoder\Dashboard\FieldHelper;

defined( 'ABSPATH' ) || exit;

$show = [
	'general_start' => __( 'General', 'wp-coder' ),
	'shortcode'     => __( 'Shortcode', 'wp-coder' ),
	'everywhere'    => __( 'Everywhere', 'wp-coder' ),
	'general_end'   => __( 'General', 'wp-coder' ),
	'post_start'    => __( 'Posts', 'wp-coder' ),
	'post_all'      => __( 'All posts', 'wp-coder' ),
	'post_selected' => __( 'Selected posts', 'wp-coder' ),
	'post_category' => __( 'Post category', 'wp-coder' ),
	'post_end'      => __( 'Posts End', 'wp-coder' ),
	'page_start'    => __( 'Pages', 'wp-coder' ),
	'page_all'      => __( 'All pages', 'wp-coder' ),
	'page_selected' => __( 'Selected pages', 'wp-coder' ),
	'page_type'     => __( 'Page type', 'wp-coder' ),
	'page_end'      => __( 'Pages End', 'wp-coder' ),
];

$post_types = get_post_types( [ 'public' => true, '_builtin' => false, ], 'objects' );

foreach ( $post_types as $key => $post_type ) {
	$show[ $key . '_start' ]                = __( 'Custom Post:', 'wp-coder' ) . ' ' . $post_type->labels->singular_name;
	$show[ 'custom_post_all_' . $key ]      = __( 'All', 'wp-coder' ) . ' ' . $post_type->labels->name;
	$show[ 'custom_post_selected_' . $key ] = __( 'Selected', 'wp-coder' ) . ' ' . $post_type->labels->name;
	$show[ 'custom_post_tax_' . $key ]      = $post_type->labels->name . ' ' . __( 'taxonomy', 'wp-coder' );
	$show[ $key . '_end' ]                  = __( 'Custom Post:', 'wp-coder' ) . ' ' . $post_type->labels->singular_name;

}

$pages_type = [
	'is_front_page' => __( 'Home Page', 'wp-coder' ),
	'is_home'       => __( 'Posts Page', 'wp-coder' ),
	'is_search'     => __( 'Search Pages', 'wp-coder' ),
	'is_404'        => __( '404 Pages', 'wp-coder' ),
	'is_archive'    => __( 'Archive Page', 'wp-coder' ),
];

$operator = [
	'1' => 'is',
	'0' => 'is not',
];

return [
	'show' => [
		'type'    => 'select',
		'name'    => '[show]',
		'title'   => __( 'Display', 'wp-coder' ),
		'options' => $show,
		'class'   => 'display-option',
		'default' => '',
	],

	'operator' => [
		'type'    => 'select',
		'name'    => '[operator]',
		'title'   => __( 'Is or is not', 'wp-coder' ),
		'options' => $operator,
		'class'   => 'display-operator',
		'default' => '1',
	],

	'ids' => [
		'type'  => 'text',
		'name'  => '[ids]',
		'title' => __( 'Enter ID\'s', 'wp-coder' ),
		'class' => 'display-ids',
		'info'  => __( 'Enter IDs, separated by comma.', 'wp-coder' ),
	],

	'page_type'  => [
		'type'    => 'select',
		'name'    => '[page_type]',
		'title'   => __( 'Specific page types', 'wp-coder' ),
		'options' => $pages_type,
		'class'   => 'display-pages',
	],
	
	
	'is_mobile' => [
		'type'  => 'checkbox',
		'name'  => '[is_mobile]',
		'title' => __( 'Mobile Devices', 'wp-coder' ),
		'text'  => __( 'Disable', 'wp-coder' ),
	],

	'is_desktop' => [
		'type'  => 'checkbox',
		'name'  => '[is_desktop]',
		'title' => __( 'Desktop Devices', 'wp-coder' ),
		'text'  => __( 'Disable', 'wp-coder' ),
	],

	'users' => [
		'type'    => 'select',
		'name'    => '[item_user]',
		'title'   => __( 'Users', 'wp-coder' ),
		'options' => [
			1 => __( 'All users', 'wp-coder' ),
			2 => __( 'Authorized Users', 'wp-coder' ),
			3 => __( 'Unauthorized Users', 'wp-coder' ),
		],
	],

	// Browsers
	'opera' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][opera]',
		'title' => __( 'Opera', 'wp-coder' ),
		'text'  => __( 'Disable', 'wp-coder' ),
	],

	'edge' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][edge]',
		'title' => __( 'Microsoft Edge', 'wp-coder' ),
		'text'  => __( 'Disable', 'wp-coder' ),
	],

	'chrome' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][chrome]',
		'title' => __( 'Chrome', 'wp-coder' ),
		'text'  => __( 'Disable', 'wp-coder' ),
	],

	'safari' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][safari]',
		'title' => __( 'Safari', 'wp-coder' ),
		'text'  => __( 'Disable', 'wp-coder' ),
	],

	'firefox' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][firefox]',
		'title' => __( 'Firefox', 'wp-coder' ),
		'text'  => __( 'Disable', 'wp-coder' ),
	],

	'ie' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][ie]',
		'title' => __( 'Internet Explorer', 'wp-coder' ),
		'text'  => __( 'Disable', 'wp-coder' ),
	],

	'other'   => [
		'type'  => 'checkbox',
		'name'  => '[browsers][other]',
		'title' => __( 'Other', 'wp-coder' ),
		'text'  => __( 'Disable', 'wp-coder' ),
	],

	'language_on' => [
		'type'  => 'checkbox',
		'name'  => '[depending_language]',
		'title' => __( 'Depending on the language', 'wp-coder' ),
		'text'  => __( 'Enable', 'wp-coder' ),
	],

	// Schedule
	'weekday' => [
		'type'    => 'select',
		'name'    => '[weekday]',
		'title'   => __( 'Weekday', 'wp-coder' ),
		'options' => [
			'none' => __( 'Everyday', 'wp-coder' ),
			'1'    => __( 'Monday', 'wp-coder' ),
			'2'    => __( 'Tuesday', 'wp-coder' ),
			'3'    => __( 'Wednesday', 'wp-coder' ),
			'4'    => __( 'Thursday', 'wp-coder' ),
			'5'    => __( 'Friday', 'wp-coder' ),
			'6'    => __( 'Saturday', 'wp-coder' ),
			'7'    => __( 'Sunday', 'wp-coder' ),
		],
	],

	'time_start' => [
		'type'  => 'time',
		'name'  => '[time_start]',
		'title' => __( 'Start time', 'wp-coder' ),
	],

	'time_end' => [
		'type'  => 'time',
		'name'  => '[time_end]',
		'title' => __( 'End time', 'wp-coder' ),
	],

	'dates' => [
		'type'  => 'checkbox',
		'name'  => '[dates]',
		'title' => __( 'Define Dates', 'wp-coder' ),
		'text'  => __( 'Enable', 'wp-coder' ),
		'class' => 'wowp-dates',
	],

	'date_start' => [
		'type'  => 'date',
		'name'  => '[date_start]',
		'title' => __( 'Date From', 'wp-coder' ),
		'class' => 'wowp-date-input',
	],

	'date_end'    => [
		'type'  => 'date',
		'name'  => '[date_end]',
		'title' => __( 'Date To', 'wp-coder' ),
		'class' => 'wowp-date-input',
	],
];