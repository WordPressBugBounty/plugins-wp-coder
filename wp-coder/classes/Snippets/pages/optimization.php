<?php
/**
 * Performance snippets.
 *
 * Front-end weight only: asset trimming and <head> cleanup. Feature toggles that
 * used to live here (search, feeds, attachment pages, 404 redirect, trash) moved
 * to core.php, and "Remove WordPress Version" to security.php. Option keys are
 * unchanged, so existing settings carry over untouched.
 */

defined( 'ABSPATH' ) || exit;

?>

    <div class="wowp-snippets__header">
        <h3 class="wowp-snippets__header-title">Performance</h3>
        <p class="wowp-snippets__header-description">Trim front-end assets and remove unused markup from the page head.</p>
    </div>

<?php

$snippets = [
	'enable_separate_block_assets' => [
		'Load Block CSS Only When Used',
		'Load styles per block instead of the whole wp-block-library bundle. The targeted alternative to “Remove Gutenberg Block CSS”, which strips block styles completely.',
	],
	'disable_global_styles' => [
		'Remove Global Styles & SVG Filters',
		'Drop the global-styles inline CSS and duotone SVG filters WordPress prints on every front-end page. The editor keeps them.',
	],
	'disable_gravatar' => [
		'Disable Gravatar',
		'Serve a local placeholder instead of avatars from gravatar.com — removes an external request per avatar and stops leaking hashed emails.',
	],
	'disable_query_strings' => [
		'Remove Query Strings from Assets',
		'Strip the ?ver= query string from front-end CSS and JS for better caching.',
	],
	'disable_heartbeat' => [
		'Disable Heartbeat on Front End',
		'Stop the Heartbeat API on the front end to reduce admin-ajax load (admin autosave is kept).',
	],
	'disable_jquery_migrate' => [
		'Remove jQuery Migrate',
		'Drop the jquery-migrate script from the front end for a small performance gain.',
	],
	'disable_dashicons_frontend' => [
		'Disable Dashicons on Front End',
		'Don’t load the Dashicons stylesheet for logged-out visitors.',
	],
	'disable_emojis' => [
		'Disable Emojis',
		'Prevent WordPress from loading the emoji script and stylesheet.',
	],
	'disable_wp_shortlink' => [
		'Remove Shortlink Tag',
		'Remove the shortlink <link> tag from the page head.',
	],
	'disable_wlwmanifest_link' => [
		'Remove Windows Live Writer Tag',
		'Remove the unused wlwmanifest link from the page head.',
	],
	'disable_rsd_pingback' => [
		'Remove RSD Link & X-Pingback',
		'Remove the Really Simple Discovery link and the X-Pingback header from the site.',
	],
];

self::create_options( $snippets );

?>

    <div class="wowp-snippet__list">
        <div class="wowp-snippet__item">
            <div class="wowp-snippet__item-header">
                <label for="enable_resource_hints">Resource Hints</label>
                <p class="wowp-snippet__item-description">Tell the browser to open connections to third-party domains early, before the assets on them are requested.</p>
            </div>
            <div class="wowp-field has-checkbox">
                <label class="switch">
					<?php self::field( 'checkbox', 'enable_resource_hints' ); ?>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="wowp-snippet__item-expand is-hidden">
                <div class="wowp-field is-column">
                    <label>
                        <span class="label">Preconnect (one URL per line)</span>
						<?php self::field( 'textarea', 'resource_hints_preconnect', '', "https://fonts.gstatic.com" ); ?>
                    </label>
                    <small>Full handshake up front. Use for domains that serve render-blocking assets — keep this list short, each one costs a connection.</small>
                </div>
                <div class="wowp-field is-column">
                    <label>
                        <span class="label">DNS-prefetch (one URL per line)</span>
						<?php self::field( 'textarea', 'resource_hints_dns_prefetch', '', "https://www.googletagmanager.com" ); ?>
                    </label>
                    <small>DNS lookup only — cheaper, good for domains used later on the page.</small>
                </div>
            </div>
        </div>

        <div class="wowp-snippet__item">
            <div class="wowp-snippet__item-header">
                <label>Image Lazy Loading</label>
                <p class="wowp-snippet__item-description">
                    WordPress lazy-loads images on its own and skips the first one so the largest
                    visible image still loads eagerly. Override that only if you need to — the two
                    options below are mutually exclusive.
                </p>
            </div>
            <div class="wowp-fields__group">
                <div class="wowp-field has-checkbox is-inline">
                    <label class="switch">
						<?php self::field( 'checkbox', 'enable_image_lazy_load' ); ?>
                        <span class="slider"></span>
                    </label>
                    <span class="label">Force <code>loading="lazy"</code> on content images<br>
                        <small>Leading images stay eager, so the LCP image isn’t delayed. Needs WordPress 6.0+.</small></span>
                </div>
                <div class="wowp-field has-checkbox is-inline">
                    <label class="switch">
						<?php self::field( 'checkbox', 'disable_lazy_load' ); ?>
                        <span class="slider"></span>
                    </label>
                    <span class="label">Disable lazy loading entirely</span>
                </div>
            </div>
        </div>
    </div>

<?php
// These two settings pull in opposite directions and used to sit in different
// tabs, where nothing stopped a site from enabling both (forcing the attribute
// won, silently defeating the disable). Keep them exclusive at the point of choice.
?>
<script>
(function () {
	var force = document.getElementById('enable_image_lazy_load');
	var off   = document.getElementById('disable_lazy_load');

	if (!force || !off) {
		return;
	}

	force.addEventListener('change', function () {
		if (force.checked) {
			off.checked = false;
		}
	});

	off.addEventListener('change', function () {
		if (off.checked) {
			force.checked = false;
		}
	});
})();
</script>
