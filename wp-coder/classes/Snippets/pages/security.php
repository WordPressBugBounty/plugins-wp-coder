<?php
/**
 * Security snippets.
 *
 * Collected here from Core Functionality, Login and Cleanup, where they used to
 * be spread across three tabs. Moving a snippet between tabs keeps its option key,
 * so those settings carried over untouched; the one key that did change,
 * disable_XML_RPC → disable_xml_rpc, is handled by the 5.0 UpdateDB migration.
 */

defined( 'ABSPATH' ) || exit;

?>

	<div class="wowp-snippets__header">
		<h3 class="wowp-snippets__header-title">Security</h3>
		<p class="wowp-snippets__header-description">Harden WordPress against common attacks and information leaks.</p>
	</div>

<?php

$snippets = [
	'disable_xml_rpc' => [
		'Disable XML-RPC',
		'Fully disable XML-RPC functionality.',
	],

	'disable_file_editing' => [
		'Disable Theme & Plugin File Editing',
		'Remove the built-in code editors from the admin (sets DISALLOW_FILE_EDIT).',
	],

	'disable_file_mods' => [
		'Disable Plugin & Theme Installation',
		'Block installing, updating and deleting plugins and themes from the admin (sets DISALLOW_FILE_MODS). Also hides the built-in editors.',
	],

	'disable_author_archives' => [
		'Disable Author Archives',
		'Redirect /author/name/ pages to the homepage and drop users from the sitemap, so usernames can’t be confirmed that way.',
	],

	'enable_login_notification' => [
		'Email on Administrator Login',
		'Send the site admin an email with the user, IP and time whenever an account that can manage the site signs in.',
	],

	'disable_user_enumeration' => [
		'Disable User Enumeration',
		'Block ?author=N probes and the public REST users endpoints used to harvest usernames.',
	],

	'enable_security_headers' => [
		'Add Security Headers',
		'Send X-Content-Type-Options, X-Frame-Options and Referrer-Policy headers on the front end.',
	],

	'disable_application_passwords' => [
		'Disable Application Passwords',
		'Turn off the Application Passwords feature for all users.',
	],

	'disable_login_error_details' => [
		'Hide Login Error Details',
		'Show a generic login error so valid usernames can’t be discovered.',
	],

	'remove_wp_version' => [
		'Remove WordPress Version Number',
		'Hide version info from the site source and feeds so the exact release can’t be fingerprinted.',
	],
];

self::create_options( $snippets );

?>

	<div class="wowp-snippet__list">
		<div class="wowp-snippet__item">
			<div class="wowp-snippet__item-header">
				<label for="enable_limit_login_attempts">Limit Login Attempts</label>
				<p class="wowp-snippet__item-description">Temporarily block an IP address after repeated failed logins. The block lifts on its own, and a successful login clears the counter.</p>
			</div>
			<div class="wowp-field has-checkbox">
				<label class="switch">
					<?php self::field( 'checkbox', 'enable_limit_login_attempts' ); ?>
					<span class="slider"></span>
				</label>
			</div>
			<div class="wowp-snippet__item-expand is-hidden">
				<div class="wowp-fields__group">
					<div class="wowp-field">
						<label>
							<span class="label">Allowed attempts</span>
							<?php self::field( 'number', 'limit_login_attempts_max', 5, '5' ); ?>
						</label>
					</div>
					<div class="wowp-field">
						<label>
							<span class="label">Block for (minutes)</span>
							<?php self::field( 'number', 'limit_login_attempts_minutes', 15, '15' ); ?>
						</label>
					</div>
				</div>
				<small>Attempts are counted per IP address (<code>REMOTE_ADDR</code>). If the site sits behind a proxy or CDN that doesn’t pass the visitor IP through, all visitors may share one counter.</small>
			</div>
		</div>

		<div class="wowp-snippet__item">
			<div class="wowp-snippet__item-header">
				<label for="disable_rest_api">Disable WordPress REST API</label>
				<p class="wowp-snippet__item-description">Prevent public access to the REST API.</p>
			</div>
			<div class="wowp-field has-checkbox">
				<label class="switch">
					<?php self::field( 'checkbox', 'disable_rest_api' ); ?>
					<span class="slider"></span>
				</label>
			</div>
			<div class="wowp-snippet__item-expand is-hidden">
				<div class="wowp-field has-checkbox is-inline">
					<label class="switch">
						<?php self::field( 'checkbox', 'disable_rest_api_logged_out_only' ); ?>
						<span class="slider"></span>
					</label>
					<span class="label">Only for logged-out visitors</span>
				</div>
				<small>Recommended: keeps the REST API working for the block editor and logged-in users, while blocking anonymous access. Leave off to block the REST API for everyone.</small>
			</div>
		</div>
	</div>

<?php
