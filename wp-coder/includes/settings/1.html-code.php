<?php
/*
 * Page Name: HTML
 */

use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\Settings;
use WPCoder\Dashboard\Option;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();
$opt     = include( 'options/html-code.php' );
$options = get_option( '_wp_coder_tools', [] );

?>

    <div class="wowp-settings__page">

        <div class="wowp-settings__page-sidebar">
	        <?php
	        // Grouped by what each control is for — inserting into the code,
	        // previewing it, how it is output on the site, and finding your way
	        // around it. Same groups as WP Coder Pro.
	        ?>

            <div class="wowp-sidebar-group">
                <p class="wowp-sidebar-group__title"><?php esc_html_e( 'Insert', 'wp-coder' ); ?></p>
                <button class="button button-add-img"><?php esc_html_e( 'Add Image', 'wp-coder' ); ?></button>
            </div>

            <div class="wowp-sidebar-group">
                <p class="wowp-sidebar-group__title"><?php esc_html_e( 'Preview', 'wp-coder' ); ?></p>
		        <?php Option::init( [
			        $opt['preview'],
		        ] ); ?>
            </div>

            <div class="wowp-sidebar-group">
                <p class="wowp-sidebar-group__title"><?php esc_html_e( 'Output', 'wp-coder' ); ?></p>
		        <?php Option::init( [
			        $opt['minified'],
		        ] ); ?>
            </div>

            <div class="wowp-sidebar-group">
                <p class="wowp-sidebar-group__title"><?php esc_html_e( 'Navigate', 'wp-coder' ); ?></p>
                <button class="button-editor button" id="htmlnav"
                        title="<?php esc_attr_e( 'Insert a <!-- NAV: name --> comment. Each one is listed below; click it to jump to that line.', 'wp-coder' ); ?>"><?php esc_html_e( 'Add NAV Comment', 'wp-coder' ); ?></button>
                <ol id="htmlNavigationMenu" class="wowp-php-nav-menu"></ol>
            </div>

        </div>

        <div class="wowp-settings__page-content">

			<?php Field::textarea( 'html_code' ); ?>

			<?php Settings::editor_hint( 'html', sprintf(
				/* translators: 1: <html>, 2: <body> */
				__( 'Just enter the HTML content. You don’t need to include %1$s or %2$s — it’s already on the page.', 'wp-coder' ),
				'<code>&lt;html&gt;</code>',
				'<code>&lt;body&gt;</code>'
			) ); ?>
        </div>

    </div>


<?php

