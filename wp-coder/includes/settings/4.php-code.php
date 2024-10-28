<?php
/*
 * Page Name: PHP
 */

use WPCoder\Dashboard\Field;

defined( 'ABSPATH' ) || exit;

$php_include = [
	0 => __( 'Where insert snippet', 'wpcoderpro' ),
	1 => __( 'Only run in administration area', 'wpcoderpro' ),
	2 => __( 'Only run on site front-end', 'wpcoderpro' ),
	3 => __( 'Everywhere', 'wpcoderpro' ),
]

?>

    <h4>
        <span class="codericon codericon-filetype-php"></span>
		<?php esc_html_e( 'PHP Code', 'wp-coder' ); ?>
    </h4>

    <details class="details">
        <summary><?php esc_html_e( 'Settings', 'wpcoder' ); ?></summary>
        <div>
            <div class="wowp-field" data-option="php_include">
                <label><span class="label">
                <?php esc_html_e( 'Include PHP', 'wpcoder' ); ?></span>
					<?php Field::select( 'php_include', null, $php_include ); ?>
                </label>
            </div>
        </div>
    </details>

    <details class="details">
        <summary>
			<?php esc_html_e( 'Navigation', 'wpcoder' ); ?>
            <div class="button-group alignright">
                <button class="button-editor button" id="phpnav">Add NAV Comment</button>
            </div>
        </summary>
        <div class="wowp-field">
            <ol id="phpNavigationMenu" class="wowp-php-nav-menu"></ol>
        </div>
    </details>

<?php Field::textarea( 'php_code' ); ?>

    <div class="wowp-notification -warning">
        Please input only the PHP content, omitting the <code>php</code> tag
    </div>

<?php


