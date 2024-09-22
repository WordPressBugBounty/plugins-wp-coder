<?php

use WPCoder\Dashboard\FieldHelper;

defined( 'ABSPATH' ) || exit;

$show = [
	'general_start' => __( 'General', 'wpcoder' ),
	'shortcode'     => __( 'Shortcode', 'wpcoder' ),
	'everywhere'    => __( 'Everywhere', 'wpcoder' ),
	'general_end'   => __( 'General', 'wpcoder' ),
	'post_start'    => __( 'Posts', 'wpcoder' ),
	'post_all'      => __( 'All posts', 'wpcoder' ),
	'post_selected' => __( 'Selected posts', 'wpcoder' ),
	'post_category' => __( 'Post category', 'wpcoder' ),
	'post_end'      => __( 'Posts End', 'wpcoder' ),
	'page_start'    => __( 'Pages', 'wpcoder' ),
	'page_all'      => __( 'All pages', 'wpcoder' ),
	'page_selected' => __( 'Selected pages', 'wpcoder' ),
	'page_type'     => __( 'Page type', 'wpcoder' ),
	'page_end'      => __( 'Pages End', 'wpcoder' ),
];

$post_types = get_post_types( [ 'public' => true, '_builtin' => false, ], 'objects' );

foreach ( $post_types as $key => $post_type ) {
	$show[ $key . '_start' ]                = __( 'Custom Post:', 'wpcoder' ) . ' ' . $post_type->labels->singular_name;
	$show[ 'custom_post_all_' . $key ]      = __( 'All', 'wpcoder' ) . ' ' . $post_type->labels->name;
	$show[ 'custom_post_selected_' . $key ] = __( 'Selected', 'wpcoder' ) . ' ' . $post_type->labels->name;
	$show[ 'custom_post_tax_' . $key ]      = $post_type->labels->name . ' ' . __( 'taxonomy', 'wpcoder' );
	$show[ $key . '_end' ]                  = __( 'Custom Post:', 'wpcoder' ) . ' ' . $post_type->labels->singular_name;

}

$pages_type = [
	'is_front_page' => __( 'Home Page', 'wpcoder' ),
	'is_home'       => __( 'Posts Page', 'wpcoder' ),
	'is_search'     => __( 'Search Pages', 'wpcoder' ),
	'is_404'        => __( '404 Pages', 'wpcoder' ),
	'is_archive'    => __( 'Archive Page', 'wpcoder' ),
];

$operator = [
	'1' => 'is',
	'0' => 'is not',
];

return [
	'show' => [
		'type'    => 'select',
		'name'    => '[show]',
		'title'   => __( 'Display', 'wpcoder' ),
		'options' => $show,
		'class'   => 'display-option',
		'default' => '',
	],

	'operator' => [
		'type'    => 'select',
		'name'    => '[operator]',
		'title'   => __( 'Is or is not', 'wpcoder' ),
		'options' => $operator,
		'class'   => 'display-operator',
		'default' => '1',
	],

	'ids' => [
		'type'  => 'text',
		'name'  => '[ids]',
		'title' => __( 'Enter ID\'s', 'wpcoder' ),
		'class' => 'display-ids',
		'info'  => __( 'Enter IDs, separated by comma.', 'wpcoder' ),
	],

	'page_type'  => [
		'type'    => 'select',
		'name'    => '[page_type]',
		'title'   => __( 'Specific page types', 'wpcoder' ),
		'options' => $pages_type,
		'class'   => 'display-pages',
	],
	
	
	'is_mobile' => [
		'type'  => 'checkbox',
		'name'  => '[is_mobile]',
		'title' => __( 'Mobile Devices', 'wpcoder' ),
		'text'  => __( 'Disable', 'wpcoder' ),
	],

	'is_desktop' => [
		'type'  => 'checkbox',
		'name'  => '[is_desktop]',
		'title' => __( 'Desktop Devices', 'wpcoder' ),
		'text'  => __( 'Disable', 'wpcoder' ),
	],

	'users' => [
		'type'    => 'select',
		'name'    => '[item_user]',
		'title'   => __( 'Users', 'wpcoder' ),
		'options' => [
			1 => __( 'All users', 'wpcoder' ),
			2 => __( 'Authorized Users', 'wpcoder' ),
			3 => __( 'Unauthorized Users', 'wpcoder' ),
		],
	],

	// Browsers
	'opera' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][opera]',
		'title' => __( 'Opera', 'wpcoder' ),
		'text'  => __( 'Disable', 'wpcoder' ),
	],

	'edge' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][edge]',
		'title' => __( 'Microsoft Edge', 'wpcoder' ),
		'text'  => __( 'Disable', 'wpcoder' ),
	],

	'chrome' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][chrome]',
		'title' => __( 'Chrome', 'wpcoder' ),
		'text'  => __( 'Disable', 'wpcoder' ),
	],

	'safari' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][safari]',
		'title' => __( 'Safari', 'wpcoder' ),
		'text'  => __( 'Disable', 'wpcoder' ),
	],

	'firefox' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][firefox]',
		'title' => __( 'Firefox', 'wpcoder' ),
		'text'  => __( 'Disable', 'wpcoder' ),
	],

	'ie' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][ie]',
		'title' => __( 'Internet Explorer', 'wpcoder' ),
		'text'  => __( 'Disable', 'wpcoder' ),
	],

	'other'   => [
		'type'  => 'checkbox',
		'name'  => '[browsers][other]',
		'title' => __( 'Other', 'wpcoder' ),
		'text'  => __( 'Disable', 'wpcoder' ),
	],

	'language_on' => [
		'type'  => 'checkbox',
		'name'  => '[depending_language]',
		'title' => __( 'Depending on the language', 'wpcoder' ),
		'text'  => __( 'Enable', 'wpcoder' ),
	],

	// Schedule
	'weekday' => [
		'type'    => 'select',
		'name'    => '[weekday]',
		'title'   => __( 'Weekday', 'wpcoder' ),
		'options' => [
			'none' => __( 'Everyday', 'wpcoder' ),
			'1'    => __( 'Monday', 'wpcoder' ),
			'2'    => __( 'Tuesday', 'wpcoder' ),
			'3'    => __( 'Wednesday', 'wpcoder' ),
			'4'    => __( 'Thursday', 'wpcoder' ),
			'5'    => __( 'Friday', 'wpcoder' ),
			'6'    => __( 'Saturday', 'wpcoder' ),
			'7'    => __( 'Sunday', 'wpcoder' ),
		],
	],

	'time_start' => [
		'type'  => 'time',
		'name'  => '[time_start]',
		'title' => __( 'Start time', 'wpcoder' ),
	],

	'time_end' => [
		'type'  => 'time',
		'name'  => '[time_end]',
		'title' => __( 'End time', 'wpcoder' ),
	],

	'dates' => [
		'type'  => 'checkbox',
		'name'  => '[dates]',
		'title' => __( 'Define Dates', 'wpcoder' ),
		'text'  => __( 'Enable', 'wpcoder' ),
		'class' => 'wowp-dates',
	],

	'date_start' => [
		'type'  => 'date',
		'name'  => '[date_start]',
		'title' => __( 'Date From', 'wpcoder' ),
		'class' => 'wowp-date-input',
	],

	'date_end'    => [
		'type'  => 'date',
		'name'  => '[date_end]',
		'title' => __( 'Date To', 'wpcoder' ),
		'class' => 'wowp-date-input',
	],
];