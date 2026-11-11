<?php

defined( 'ABSPATH' ) || exit;

return [

	// Stored as WP Coder Pro stores it (param[show][0]), so a snippet behaves the
	// same after moving between the two versions. The "pro_" groups list Pro's
	// rules disabled (Field::select), so they can be seen but never saved here.
	'show' => [
		'type'    => 'select',
		'name'    => '[show]',
		'title'   => __( 'Display', 'wp-coder' ),
		'default' => 'shortcode',
		'options' => [
			'shortcode'         => __( 'Shortcode or block only', 'wp-coder' ),
			'everywhere'        => __( 'Everywhere (whole site)', 'wp-coder' ),
			'pro_posts_start'   => __( 'Posts — WP Coder Pro', 'wp-coder' ),
			'pro_post_all'      => __( 'All posts', 'wp-coder' ),
			'pro_post_selected' => __( 'Selected posts', 'wp-coder' ),
			'pro_post_category' => __( 'Posts in a category', 'wp-coder' ),
			'pro_posts_end'     => '',
			'pro_pages_start'   => __( 'Pages — WP Coder Pro', 'wp-coder' ),
			'pro_page_all'      => __( 'All pages', 'wp-coder' ),
			'pro_page_selected' => __( 'Selected pages', 'wp-coder' ),
			'pro_page_type'     => __( 'Page type: home, blog, search, 404', 'wp-coder' ),
			'pro_pages_end'     => '',
			'pro_archive_start' => __( 'Archives — WP Coder Pro', 'wp-coder' ),
			'pro_archive_terms' => __( 'Categories and tags', 'wp-coder' ),
			'pro_archive_other' => __( 'Authors and dates', 'wp-coder' ),
			'pro_archive_end'   => '',
			'pro_cpt_start'     => __( 'Custom post types — WP Coder Pro', 'wp-coder' ),
			'pro_cpt_all'       => __( 'Entries, taxonomies and archives', 'wp-coder' ),
			'pro_cpt_end'       => '',
		],
		'class'   => 'wowp-display-show is-grow',
		'tooltip' => __( 'Everywhere adds the snippet to every page of the site automatically — for analytics, verification meta tags and site-wide scripts — at the Location below. Showing it only on chosen pages, to chosen users or on a schedule is part of WP Coder Pro.', 'wp-coder' ),
	],

	// Stored as WP Coder Pro stores it (param[location]). Only used by a snippet
	// displayed Everywhere, so the tab hides it while Display is "shortcode".
	'location' => [
		'type'    => 'select',
		'name'    => '[location]',
		'title'   => __( 'Location', 'wp-coder' ),
		'default' => 'footer',
		'options' => [
			'head'           => __( 'In <head>', 'wp-coder' ),
			'body_open'      => __( 'After <body> opens', 'wp-coder' ),
			'before_content' => __( 'Before post content', 'wp-coder' ),
			'after_content'  => __( 'After post content', 'wp-coder' ),
			'footer'         => __( 'Before </body> (default)', 'wp-coder' ),
		],
		'class'   => 'wowp-display-location is-grow',
		'tooltip' => __( 'Where the HTML of a snippet displayed Everywhere is printed. "In <head>" is for meta tags, verification codes and JSON-LD. Before/after post content applies on single posts and pages. A shortcode or block always prints where it is placed.', 'wp-coder' ),
	],

];
