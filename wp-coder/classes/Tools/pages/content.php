<?php

defined( 'ABSPATH' ) || exit;

?>

<div class="wowp-snippets__header">
	<h3 class="wowp-snippets__header-title">Content & Templates</h3>
	<p class="wowp-snippets__header-description">Manage custom templates and menus to better structure and present your site’s content.</p>
</div>

<div class="wowp-snippet__list">

    <div class="wowp-snippet__item">
        <div class="wowp-snippet__item-header">
            <label for="markdown_editor">Markdown Editor</label>
            <p class="wowp-snippet__item-description">Disables Gutenberg and TinyMCE, replacing them with a Markdown editor powered by CodeMirror.</p>
        </div>
        <div class="wowp-field has-checkbox">
            <label class="switch">
				<?php self::field( 'checkbox', 'markdown_editor' ); ?>
                <span class="slider"></span>
            </label>
        </div>
    </div>

</div>

<?php
$wpcoder_pro_tools = [
	[ __( 'HTML Editor for Posts', 'wp-coder' ), __( 'Replace Gutenberg and TinyMCE with an HTML or Markdown editor powered by CodeMirror.', 'wp-coder' ) ],
	[ __( 'Markdown Editor for Taxonomies', 'wp-coder' ), __( 'Use the CodeMirror Markdown editor for category, tag and custom taxonomy descriptions.', 'wp-coder' ) ],
	[ __( 'Create Page Templates', 'wp-coder' ), __( 'Define custom page templates by name and pick them in the page editor.', 'wp-coder' ) ],
	[ __( 'Global Variables', 'wp-coder' ), __( 'Define site-level variables and use them in any snippet as {{global.name}} — in HTML, CSS and JS.', 'wp-coder' ) ],
	[ __( 'Register Menu', 'wp-coder' ), __( 'Register WordPress menu locations without editing the theme.', 'wp-coder' ) ],
	[ __( 'Register Sidebar / Widget Area', 'wp-coder' ), __( "Register widget areas and output them with wpc_get_sidebar('id') or the [wpc_sidebar] shortcode.", 'wp-coder' ) ],
];
require WPCoder\WPCoder::dir() . 'includes/pages/pro-tools.php';
