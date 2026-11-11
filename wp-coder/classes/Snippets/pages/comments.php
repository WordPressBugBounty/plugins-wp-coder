<?php

defined( 'ABSPATH' ) || exit;

?>

    <div class="wowp-snippets__header">
        <h3 class="wowp-snippets__header-title">Comments & Feedback</h3>
        <p class="wowp-snippets__header-description">Manage comments and user interaction.</p>
    </div>

<?php

$snippets = [
	'disable_comments'                 => [
		'Disable Comments',
		'Turn off comments site-wide, both frontend and admin.',
	],
	// Renamed from disable_comment_from_website_url ("from" → "form") by the 5.0
	// migration in UpdateDB::RENAMED_KEYS.
	'disable_comment_form_website_url' => [
		'Remove Website URL Field',
		'Remove the URL field from the comment form.',
	],

	'disable_self_pingbacks' => [
		'Disable Self Pingbacks',
		'Prevent pingbacks from your own site.',
	],

	'disable_trackbacks_pingbacks' => [
		'Disable Trackbacks & Pingbacks',
		'Close pings on new posts, stop notifying linked sites when you publish, and refuse incoming pingbacks over XML-RPC. Existing posts keep their current setting.',
	],

	'disable_comments_html' => [
		'Disable HTML in Comments',
		'Prevent HTML tags in comments.',
	],

];

self::create_options( $snippets ); ?>

    <div class="wowp-snippet__list">
        <div class="wowp-snippet__item">
            <div class="wowp-snippet__item-header">
                <label for="enable_limit_comment_length">Limit Comment Length</label>
                <p class="wowp-snippet__item-description">Limit the maximum number of characters in comments.</p>
            </div>
            <div class="wowp-field has-checkbox">
                <label class="switch">
					<?php self::field( 'checkbox', 'enable_limit_comment_length' ); ?>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="wowp-snippet__item-expand is-hidden">
                <div class="wowp-fields__group">
                    <div class="wowp-field">
                        <label><span class="label">Max characters</span>
							<?php self::field( 'number', 'limit_comment_length', '', '300' ); ?>
                        </label>
                    </div>
                    <div class="wowp-field">
                        <label><span class="label">Message</span>
							<?php self::field( 'text', 'limit_comment_mess', '', 'Your comment is too long. Max 300 characters.' ); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>
<?php
