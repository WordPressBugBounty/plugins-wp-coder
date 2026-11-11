<?php
/**
 * Core & System snippets.
 *
 * Behaviour toggles that are neither performance nor security: updates, feeds,
 * search, revisions. Security items moved to security.php and the head/asset
 * cleanup to optimization.php; option keys are unchanged.
 */

defined( 'ABSPATH' ) || exit;

?>

    <div class="wowp-snippets__header">
        <h3 class="wowp-snippets__header-title">Core &amp; System</h3>
        <p class="wowp-snippets__header-description">Control built-in WordPress behaviours and features.</p>
    </div>

<?php

$snippets = [
	'disable_automatic_updates' => [
		'Disable Automatic Updates',
		'Turn off automatic updates for WordPress core, plugins and themes.',
	],
	'disable_automatic_updates_emails' => [
		'Disable Automatic Update Emails',
		'Stop email notifications for auto-updates.',
	],
	'disable_admin_email_verification' => [
		'Disable Admin Email Verification',
		'Stop the periodic “Is this email correct?” admin verification screen.',
	],
	'disable_automatic_trash' => [
		'Disable Automatic Trash Emptying',
		'Stop automatic emptying of trash every 30 days.',
	],
	'disable_search' => [
		'Disable Search',
		'Disable the built-in WordPress search functionality.',
	],
	'disable_rss_feeds' => [
		'Disable RSS Feeds',
		'Disable all default WordPress RSS feeds.',
	],
	'disable_attachment_pages' => [
		'Disable Attachment Pages',
		'Redirect attachment pages to the parent post or homepage.',
	],
	'enable_redirect_404_to_home' => [
		'Redirect 404 to Homepage',
		'Send every “not found” page to the homepage with a temporary (302) redirect, so publishing a real page at that URL later still works.',
	],
];

self::create_options( $snippets );

?>

	<div class="wowp-snippet__list">
		<div class="wowp-snippet__item">
			<div class="wowp-snippet__item-header">
				<label for="change_mail_from">Custom Mail From Name & Address</label>
				<p class="wowp-snippet__item-description">Replace the default <code>wordpress@yourdomain</code> sender on outgoing email.</p>
			</div>
			<div class="wowp-field has-checkbox">
				<label class="switch">
					<?php self::field( 'checkbox', 'change_mail_from' ); ?>
					<span class="slider"></span>
				</label>
			</div>
			<div class="wowp-snippet__item-expand is-hidden">
				<div class="wowp-fields__group">
					<div class="wowp-field is-column">
						<label>
							<span class="label">From name</span>
							<?php self::field( 'text', 'change_mail_from_name', '', get_bloginfo( 'name' ) ); ?>
						</label>
					</div>
					<div class="wowp-field is-column">
						<label>
							<span class="label">From address</span>
							<?php self::field( 'text', 'change_mail_from_email', '', 'hello@' . wp_parse_url( home_url(), PHP_URL_HOST ) ); ?>
						</label>
					</div>
				</div>
				<small>Each field is applied only when filled in — an invalid address is ignored rather than breaking outgoing mail. Use an address on your own domain, or SPF/DKIM checks will reject the message. An SMTP plugin may override these.</small>
			</div>
		</div>

		<div class="wowp-snippet__item">
			<div class="wowp-snippet__item-header">
				<label for="change_revisions_control">Limit Post Revisions</label>
				<p class="wowp-snippet__item-description">Limit the number of stored revisions per post.</p>
			</div>
			<div class="wowp-field has-checkbox">
				<label class="switch">
					<?php self::field( 'checkbox', 'change_revisions_control' ); ?>
					<span class="slider"></span>
				</label>
			</div>
			<div class="wowp-snippet__item-expand is-hidden">
				<div class="wowp-field">
					<label>
						<span class="label">Number of revisions</span>
						<?php self::field( 'number', 'change_revisions_control_number', 10 ); ?>
					</label>
				</div>
			</div>
		</div>
	</div>

<?php
