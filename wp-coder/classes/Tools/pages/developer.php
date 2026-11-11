<?php

defined( 'ABSPATH' ) || exit;

?>

<div class="wowp-snippets__header">
	<h3 class="wowp-snippets__header-title">Developer / Debug Tools</h3>
	<p class="wowp-snippets__header-description">Advanced utilities for administrators and developers to test, debug, and quickly manage site environments.</p>
</div>

<div class="wowp-snippet__list">

	<div class="wowp-snippet__item">
		<div class="wowp-snippet__item-header">
			<label for="show_page_debug_info">Show Page Debug Info</label>
			<p class="wowp-snippet__item-description">
				Display technical info for the current request in the Admin Bar (template, query type, object, body classes). Admins only.
			</p>
		</div>
		<div class="wowp-field has-checkbox">
			<label class="switch">
				<?php self::field( 'checkbox', 'show_page_debug_info' ); ?>
				<span class="slider"></span>
			</label>
		</div>
	</div>

	<div class="wowp-snippet__item">
		<div class="wowp-snippet__item-header">
			<label for="theme_switcher">Theme Switcher</label>
			<p class="wowp-snippet__item-description">
				Quickly switch between installed themes directly from the admin bar.
			</p>
		</div>
		<div class="wowp-field has-checkbox">
			<label class="switch">
				<?php self::field( 'checkbox', 'theme_switcher' ); ?>
				<span class="slider"></span>
			</label>
		</div>
	</div>


</div>

<?php
$wpcoder_pro_tools = [
	[ __( 'WP Coder Debug Bar', 'wp-coder' ), __( 'A front-end panel for administrators: which snippets are active on the current page, which are shortcode-only, which were skipped and why, and any PHP errors.', 'wp-coder' ) ],
	[ __( 'Test Users', 'wp-coder' ), __( 'Preview the site as another user role from your own account, without changing any real role.', 'wp-coder' ) ],
	[ __( 'Plugin Switcher', 'wp-coder' ), __( 'Activate or deactivate installed plugins directly from the admin bar.', 'wp-coder' ) ],
	[ __( 'Cron Events Viewer', 'wp-coder' ), __( 'List scheduled WP-Cron events with their next run and recurrence in the admin bar.', 'wp-coder' ) ],
	[ __( 'Registered Shortcodes', 'wp-coder' ), __( 'Show every shortcode registered on the site in the admin bar.', 'wp-coder' ) ],
	[ __( 'Block Inspector', 'wp-coder' ), __( 'Hover over the WP Coder block preview to see tags and CSS classes, and click to copy a selector.', 'wp-coder' ) ],
];
require WPCoder\WPCoder::dir() . 'includes/pages/pro-tools.php';
