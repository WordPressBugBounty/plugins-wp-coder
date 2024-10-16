<?php

use WPCoder\Dashboard\FieldHelper;

defined( 'ABSPATH' ) || exit;

$disabled_snippets = [
	'disable_gutenberg' => [
		'Disable Gutenberg Editor',
		'Disable Gutenberg Editor and Return to the Familiar Classic Editor',
	],

	'disable_gutenberg_css' => [
		'Remove Gutenberg Block CSS',
		'Remove Gutenberg Block Library CSS from loading on the frontend',
	],

	'disable_widget_blocks' => [
		'Disable Widget Blocks',
		'Use the classic interface instead of Blocks to manage Widgets.',
	],
	'remove_wp_version' => [
		'Remove WordPress Version Number',
		'Hide the WordPress version number from your site\'s frontend and feeds.',
	],
	'disable_XML_RPC' => [
		'Disable XML-RPC',
		'On sites running WordPress 3.5+, disable XML-RPC completely.',
	],

	'disable_automatic_updates_emails' => [
		'Disable Automatic Updates Emails',
		'Stop getting emails about automatic updates on your WordPress site.',
	],

	'disable_automatic_updates' => [
		'Disable Automatic Updates',
		'Completely disable automatic updates on your website.',
	],

	'disable_attachment_pages' => [
		'Disable Attachment Pages',
		'Hide the Attachment/Attachments pages on the frontend from all visitors.',
	],

	'disable_rest_api' => [
		'Disable WordPress REST API',
		'Disable the WP REST API on your website.',
	],

	'disable_comments' => [
		'Disable Comments',
		'Disable comments for all post types, in the admin and the frontend.',
	],

	'disable_automatic_trash' => [
		'Disable Automatic Trash Emptying',
		'Prevent WordPress from automatically deleting trashed posts after 30 days.',
	],

	'disable_emojis' => [
		'Disable Emojis',
		'Disable the emojis in WordPress.',
	],

	'disable_screen_options' => [
		'Disable ‘Screen Options’ Tab',
		'Remove the Screen Options menu at the top of admin pages.',
	],

	'disable_welcome_panel' => [
		'Disable Welcome Panel',
		'Hide the Welcome Panel on the WordPress dashboard for all users.',
	],

	'disable_rss_feeds' => [
		'Disable RSS Feeds',
		'Turn off the WordPress RSS Feeds for your website.',
	],

	'disable_search' => [
		'Disable Search',
		'Completely disable search on your WordPress website.',
	],

	'disable_login_language_dropdown' => [
		'Disable Login Page Language Switcher',
		'Disable the Language Switcher on the default WordPress login page.',
	],

	'disable_login_by_email' => [
		'Disable Login by Email',
		'Force your users to login only using their username.',
	],

	'disable_comment_from_website_url' => [
		'Disable Comment Form Website URL',
		'Remove the Website URL field from the Comments form.',
	],

	'disable_self_pingbacks' => [
		'Disable Self Pingbacks',
		'Prevent WordPress from automatically creating pingbacks from your own site.',
	],

	'disable_wlwmanifest_link' => [
		'Disable wlwmanifest link',
		'Prevent WordPress from adding the Windows Live Writer manifest link to your pages.',
	],

	'disable_embeds' => [
		'Disable Embeds',
		'Remove an extra request and prevent others from adding embeds in your site.',
	],

	'disable_lazy_load' => [
		'Disable Lazy Load',
		'Prevent WordPress from adding the lazy-load attribute to all images and iframes.',
	],

	'disable_wp_shortlink' => [
		'Disable the WordPress Shortlink',
		'Remove link rel shortlink from your site head area.',
	],

	'disable_admin_pass_reset_email' => [
		'Disable Admin Password Reset Emails',
		'Don\'t send an email to the administrator of the site after a user resets their password.',
	],

];

self::create_options($disabled_snippets);

?>

<dl class="wowp-snippet__list">
	<dt><label><?php
			self::field( 'checkbox', 'disable_admin_bar' ); ?>Disable The WP Admin Bar</label></dt>
	<dd>Disable the WordPress Admin Bar for all users in the frontend.
		<details class="wpcoder-expand">
			<summary class="wpcoder-expand__title">Expand</summary>
			<div class="wpcoder-expand__content">
				<p><b>Show the sidebar for users:</b></p>
				<fieldset class="_bg-white">
					<?php foreach ( FieldHelper::user_roles() as $key => $value ) :
						if ( $key === 'all' ) {
							continue;
						}
						?>
						<div class="wowp-field has-checkbox">
							<label>
								<span class="label"><?php echo esc_html( $value ); ?></span>
								<?php self::field( 'checkbox', 'disable_admin_bar_user_' . $key ); ?>
								<span><?php esc_html_e( 'Enable', 'wp-coder' ); ?></span>
							</label>
						</div>
					<?php endforeach; ?>
				</fieldset>

			</div>
		</details>
	</dd>
</dl>