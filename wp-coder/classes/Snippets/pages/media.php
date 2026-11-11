<?php

defined( 'ABSPATH' ) || exit;

?>

    <div class="wowp-snippets__header">
        <h3 class="wowp-snippets__header-title">Media & Embeds</h3>
        <p class="wowp-snippets__header-description">Optimize and control how media and embeds are handled on your
            site.</p>
    </div>

<?php

$snippets = [
	'enable_svg_upload' => [
		'Allow SVG Uploads',
		'Allow SVG files in the media library. Each upload is cleaned first — scripts, event handlers and javascript: links are removed, and a file that can’t be read as valid SVG is rejected.',
	],

	'disable_big_image_scaling' => [
		'Disable Big Image Scaling',
		'Stop WordPress from downscaling large images and adding the “-scaled” suffix.',
	],

	'disable_unused_image_sizes' => [
		'Disable Unused Image Sizes',
		'Stop generating the medium_large, 1536x1536 and 2048x2048 copies WordPress adds on top of thumbnail/medium/large. Affects new uploads only.',
	],

	'enable_auto_alt_from_filename' => [
		'Auto Alt Text from Filename',
		'When an image is uploaded with no alt text, derive one from its filename (“my-red-car.jpg” → “My red car”).',
	],

	'default_image_link_none' => [
		'Default Image Link to None',
		'Insert images without an automatic link to the file or attachment page.',
	],

	'enable_lowercase_filenames_for_uploads' => [
		'Enable Lowercase Filenames for Uploads',
		'Automatically convert uploaded filenames to lowercase.',
	],

	'enable_default_alt_to_avatar' => [
		'Add Alt Text to Avatars',
		'Automatically set the user name as alt text for avatar and Gravatar images.',
	],

	'enable_featured_img_rss_feeds' => [
		'Add Featured Image to RSS Feeds',
		'Prepend the post’s featured image to its content/excerpt in RSS feeds.',
	],

	'disable_embeds' => [
		'Disable Embeds',
		'Prevent embedding content from external sites.',
	],

];

self::create_options( $snippets );

?>
    <div class="wowp-snippet__item">
        <div class="wowp-snippet__item-header">
            <label for="change_max_upload_size">Limit Maximum Upload Size</label>
            <p class="wowp-snippet__item-description">Cap the size of files that can be uploaded to the media library.</p>
        </div>
        <div class="wowp-field has-checkbox">
            <label class="switch">
				<?php self::field( 'checkbox', 'change_max_upload_size' ); ?>
                <span class="slider"></span>
            </label>
        </div>
        <div class="wowp-snippet__item-expand is-hidden">
            <div class="wowp-field">
                <label><span class="label">Max size (MB)</span>
					<?php self::field( 'number', 'change_max_upload_size_mb', '', '10' ); ?>
                </label>
            </div>
            <small>This can only <strong>lower</strong> the limit. The server’s own <code>upload_max_filesize</code> and <code>post_max_size</code> stay the hard ceiling — a larger value here is ignored. Current server limit: <code><?php echo esc_html( size_format( wp_max_upload_size() ) ); ?></code>.</small>
        </div>
    </div>

    <div class="wowp-snippet__item">
        <div class="wowp-snippet__item-header">
            <label for="change_oembed_size">Change oEmbed Max Width and Height</label>
            <p class="wowp-snippet__item-description">Set max width and height for embedded content.</p>
        </div>
        <div class="wowp-field has-checkbox">
            <label class="switch">
				<?php self::field( 'checkbox', 'change_oembed_size' ); ?>
                <span class="slider"></span>
            </label>
        </div>
        <div class="wowp-snippet__item-expand is-hidden">
            <div class="wowp-fields__group">
                <div class="wowp-field">
                    <label><span class="label">Width</span>
						<?php self::field( 'number', 'change_oembed_size_width', '', '400' ); ?>
                    </label>
                </div>
                <div class="wowp-field">
                    <label><span class="label">Height</span>
						<?php self::field( 'number', 'change_oembed_size_height', '', '280' ); ?>
                    </label>
                </div>
            </div>
        </div>
    </div>

<?php
