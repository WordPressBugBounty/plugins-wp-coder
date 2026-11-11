<?php

defined( 'ABSPATH' ) || exit;

?>

    <div class="wowp-snippets__header">
        <h3 class="wowp-snippets__header-title">Editor & Content</h3>
        <p class="wowp-snippets__header-description">Customize editor and content features.</p>
    </div>

<?php
$snippets = [
//	'disable_gutenberg' => [
//		'Disable Gutenberg Editor',
//		'Use the Classic Editor instead of Gutenberg.',
//	],

	'disable_gutenberg_css' => [
		'Remove Gutenberg Block CSS',
		'Prevent loading of Gutenberg block styles (wp-block-library) on the frontend.',
	],

	'enable_page_slug_to_body_class' => [
		'Add Page Slug to Body Class',
		'Add a {post_type}-{slug} class to the <body> tag for easier per-page CSS targeting.',
	],

	'disable_widget_blocks' => [
		'Disable Widget Blocks',
		'Revert to the Classic Widgets interface.',
	],

	'enable_shortcode_in_text_widgets'    => [
		'Enable Shortcode Execution in Text Widgets',
		'Allow shortcodes in text widgets.',
	],
	'enable_pages_excerpt'                => [
		'Enable Excerpt for Pages',
		'Add excerpt field support for pages.',
	],
	'enable_force_external_links_new_tab' => [
		'Open External Links in New Tab',
		'Automatically open all external links in post content in a new browser tab. Adds target="_blank" and rel="noopener noreferrer nofollow" for better UX, security, and SEO.',
	],
	'disable_block_patterns' => [
		'Disable Block Patterns',
		'Remove all default Gutenberg block patterns from the pattern inserter.',
	],
	'unsynced_patterns_default' => [
		'Unsynced Patterns by Default',
		'New patterns start as "Not synced" instead of "Synced" in the Create pattern modal. JS-based workaround for a WordPress core limitation — verify after major WordPress core updates.',
	],
];

self::create_options( $snippets );

?>

    <div class="wowp-snippet__list">

        <div class="wowp-snippet__item">
            <div class="wowp-snippet__item-header">
                <label for="change_read_more">Change "Read More" Text for Excerpts</label>
                <p class="wowp-snippet__item-description">Customize the default "Read More" link text.</p>
            </div>
            <div class="wowp-field has-checkbox">
                <label class="switch">
					<?php self::field( 'checkbox', 'change_read_more' ); ?>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="wowp-snippet__item-expand is-hidden">
                <div class="wowp-field">
                    <label>
                        <span class="label">Text</span>
						<?php self::field( 'text', 'change_read_more_text', '', 'Read More' ); ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="wowp-snippet__item">
            <div class="wowp-snippet__item-header">
                <label for="custom_excerpt_length">Custom Excerpt Length by Post Type</label>
                <p class="wowp-snippet__item-description">Set different excerpt lengths (in words) for posts, pages, and custom post types.</p>
            </div>
            <div class="wowp-field has-checkbox">
                <label class="switch">
					<?php self::field( 'checkbox', 'custom_excerpt_length' ); ?>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="wowp-snippet__item-expand is-hidden">
                <div class="wowp-field is-column">
                    <label>
                        <span class="label">Default Length (words)</span>
						<?php self::field( 'number', 'custom_excerpt_length_default', '55', '55' ); ?>
                    </label>
                </div>
                <div class="wowp-field is-column">
                    <label>
                        <span class="label">Per Post Type overrides (one per line)</span>
						<?php self::field( 'textarea', 'custom_excerpt_length_by_type', '', "post=30\npage=50" ); ?>
                    </label>
                    <small>Format: <code>post_type=length</code>. Example: <code>post=30</code> sets 30-word excerpts for posts. Leave empty to use the default length above.</small>
                </div>
            </div>
        </div>

        <div class="wowp-snippet__item">
            <div class="wowp-snippet__item-header">
                <label for="change_autosave_interval">Change Autosave Interval</label>
                <p class="wowp-snippet__item-description">How often the block editor saves a draft in the background.</p>
            </div>
            <div class="wowp-field has-checkbox">
                <label class="switch">
					<?php self::field( 'checkbox', 'change_autosave_interval' ); ?>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="wowp-snippet__item-expand is-hidden">
                <div class="wowp-field">
                    <label>
                        <span class="label">Interval (seconds)</span>
						<?php self::field( 'number', 'change_autosave_interval_seconds', 60, '60' ); ?>
                    </label>
                </div>
                <small>Block editor only. The classic editor reads the <code>AUTOSAVE_INTERVAL</code> constant, which WordPress defines before plugins load — to change that, set it in <code>wp-config.php</code>.</small>
            </div>
        </div>

        <div class="wowp-snippet__item">
            <div class="wowp-snippet__item-header">
                <label for="custom_editor_styles">Custom Editor Styles</label>
                <p class="wowp-snippet__item-description">Load a custom stylesheet in the Gutenberg block editor.</p>
            </div>
            <div class="wowp-field has-checkbox">
                <label class="switch">
					<?php self::field( 'checkbox', 'custom_editor_styles' ); ?>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="wowp-snippet__item-expand is-hidden">
                <div class="wowp-field is-column">
                    <label>
                        <span class="label">Stylesheet URL</span>
						<?php self::field( 'text', 'custom_editor_styles_url', '', 'https://example.com/editor-style.css' ); ?>
                    </label>
                    <small>Enter the full URL of the CSS file to load inside the block editor.</small>
                </div>
            </div>
        </div>

        <div class="wowp-snippet__item">
            <div class="wowp-snippet__item-header">
                <label for="disable_gutenberg">Disable Gutenberg Editor</label>
                <p class="wowp-snippet__item-description">Use the Classic Editor instead of Gutenberg.</p>
            </div>
            <div class="wowp-field has-checkbox">
                <label class="switch">
					<?php self::field( 'checkbox', 'disable_gutenberg' ); ?>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="wowp-snippet__item-expand is-hidden">
                <div class="wowp-field is-column">
                    <label><span class="label">Allowed Gutenberg IDs</span>
						<?php self::field( 'text', 'disable_gutenberg_except', '', '1, 25, 42' ); ?>
                    </label>
                    <small>Enter the IDs of posts or pages where you want to keep the Gutenberg editor.<br>
                        Separate multiple IDs with commas (e.g. <code>1, 25, 42</code>).</small>
                </div>
            </div>
        </div>

    </div>

<?php

