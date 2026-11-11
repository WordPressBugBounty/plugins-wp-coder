<?php
/*
 * Page Name: PHP
 */

use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\Settings;

defined( 'ABSPATH' ) || exit;

$php_include = [
	0 => __( 'Run where inserted', 'wp-coder' ),
	1 => __( 'Run only in admin area', 'wp-coder' ),
	2 => __( 'Run only on front-end', 'wp-coder' ),
	3 => __( 'Run everywhere', 'wp-coder' ),
]
?>

    <div class="wowp-settings__page">
        <div class="wowp-settings__page-sidebar">

            <div class="wowp-sidebar-group">
                <p class="wowp-sidebar-group__title"><?php esc_html_e( 'Output', 'wp-coder' ); ?></p>
                <div class="wowp-field" data-option="php_include">
                    <label>
                        <span class="label">
                                <?php esc_html_e( 'Include PHP', 'wp-coder' ); ?>
                            <sup class="wowp-tooltip" data-tooltip="<?php esc_attr_e( 'Define where this PHP code should run: by shortcode, in admin, front-end, or everywhere.', 'wp-coder' ); ?>">ℹ</sup>
                        </span>
				        <?php Field::select( 'php_include', null, $php_include ); ?>
                    </label>
                </div>
            </div>

            <div class="wowp-sidebar-group">
                <p class="wowp-sidebar-group__title"><?php esc_html_e( 'Navigate', 'wp-coder' ); ?></p>
                <button class="button-editor button" id="phpnav"
                        title="<?php esc_attr_e( 'Insert a // NAV: name comment. Each one is listed below; click it to jump to that line.', 'wp-coder' ); ?>"><?php esc_html_e( 'Add NAV Comment', 'wp-coder' ); ?></button>
                <ol id="phpNavigationMenu" class="wowp-php-nav-menu"></ol>
            </div>

        </div>
        <div class="wowp-settings__page-content">

			<?php Field::textarea( 'php_code' ); ?>

			<?php
			// The editor numbers from 4 (firstLineNumber in admin.js): the saved
			// file starts with three lines of its own (FolderManager::update_code()),
			// and PHP's error messages count those.
			Settings::editor_hint( 'php', sprintf(
				/* translators: %1$s: <?php */
				__( 'Enter the code without %1$s — the file already starts with it. If your code begins with %1$s, it is removed when you save. Lines are numbered from 4, as in PHP’s error messages: the saved file starts with three lines of its own.', 'wp-coder' ),
				'<code>&lt;?php</code>'
			) ); ?>

        </div>
    </div>

<?php


