<?php
/*
 * Page Name: HTML
 */

use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\Option;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();
$opt     = include( 'options/html-code.php' );

?>

    <h4>
        <span class="codericon codericon-filetype-html"></span>
		<?php
		esc_html_e( 'HTML Code', 'wp-coder' ); ?>
    </h4>

    <details class="details">
        <summary><?php esc_html_e( 'Settings', 'wp-coder' ); ?></summary>
        <div>
			<?php Option::init( [
				$opt['minified'],
			] ); ?>
        </div>
    </details>


    <details class="details">
        <summary>
			<?php esc_html_e( 'Navigation', 'wp-coder' ); ?>
            <div class="button-group alignright">
                <button class="button-editor button" id="htmlnav">Add NAV Comment</button>
                <button class="button button-primary button-add-img">Add Image</button>
            </div>
        </summary>
        <div class="wowp-field">
            <ol id="htmlNavigationMenu" class="wowp-php-nav-menu"></ol>
        </div>
    </details>

<?php Field::textarea( 'html_code' ); ?>

    <div class="wowp-notification -info">
        Please provide only the inner HTML content, excluding the <code>html</code> and <code>body</code> tags. The page
        already contains these tags, and your code will be inserted within the <code>body</code> tag directly.
    </div>

<?php

