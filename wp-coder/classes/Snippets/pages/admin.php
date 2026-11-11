<?php

use WPCoder\Dashboard\FieldHelper;

defined( 'ABSPATH' ) || exit;

?>

    <div class="wowp-snippets__header">
        <h3 class="wowp-snippets__header-title">Admin Interface</h3>
        <p class="wowp-snippets__header-description">Streamline the WordPress admin experience.</p>
    </div>

<?php

$snippets = [
	'disable_screen_options' => [
		'Disable "Screen Options" Tab',
		'Remove the "Screen Options" tab from admin pages.',
	],

	'disable_welcome_panel' => [
		'Disable Welcome Panel',
		'Hide the welcome panel on the dashboard for all users.',
	],
	'enable_duplication' => [
		'Duplicate Posts & Pages',
		'Allow duplicating posts from the admin panel.',
	],
	'enable_search_by_id' => [
		'Enable Search by ID',
		'Find posts, pages, custom post types and taxonomy terms by their numeric ID in the admin search box.',
	],
	'enable_id_column' => [
		'Show ID Column',
		'Add an ID column to post, page, custom post type and taxonomy term lists.',
	],
	'enable_featured_image_column' => [
		'Show Featured Image Column',
		'Add a thumbnail column with the featured image to post and page lists.',
	],
	'enable_last_modified_column' => [
		'Show Last Modified Column',
		'Add a “Last Modified” date column to post and page lists.',
	],
	'enable_hide_admin_notices' => [
		'Collapse Admin Notices',
		'Gather admin notices into one collapsible panel instead of stacking them above every page. Nothing is suppressed — the panel shows how many there are.',
	],
];

self::create_options( $snippets );

?>
	<div class="wowp-snippet__list">
		<div class="wowp-snippet__item">
			<div class="wowp-snippet__item-header">
				<label for="disable_admin_bar">Disable The WP Admin Bar</label>
				<p class="wowp-snippet__item-description">Hide the admin bar for all users on the frontend.</p>
			</div>
			<div class="wowp-field has-checkbox">
				<label class="switch">
					<?php self::field( 'checkbox', 'disable_admin_bar' ); ?>
					<span class="slider"></span>
				</label>
			</div>
			<div class="wowp-snippet__item-expand is-hidden">
				<p class="wowp-snippet__expand-title">Show the sidebar for users:</p>
				<div class="wowp-fields__group">
					<?php foreach ( FieldHelper::user_roles() as $key => $value ) :
						if ( $key === 'all' ) {
							continue;
						}
						?>
						<div class="wowp-field has-checkbox">
							<span class="label"><?php echo esc_html( $value ); ?></span>
							<label class="switch">
								<?php self::field( 'checkbox', 'disable_admin_bar_user_' . $key ); ?>
								<span class="slider"></span>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

<?php
