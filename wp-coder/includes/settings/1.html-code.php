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

    <fieldset>
        <legend><?php esc_html_e( 'Settings', 'wp-coder' ); ?></legend>
		<?php Option::init( [
			$opt['minified'],
		] ); ?>

    </fieldset>

    <fieldset>
        <div class="wowp-field">
            <ol id="htmlNavigationMenu" class="wowp-php-nav-menu"></ol>
            <button class="button-editor button" id="htmlnav">Add NAV Comment</button>
        </div>
        <div class="wowp-field add-img-btn _m-is-auto">
            <button class="button button-primary button-add-img">Add Image</button>
        </div>
    </fieldset>


<?php Field::textarea( 'html_code' ); ?>

    <div class="wowp-notification -info">
        Please provide only the inner HTML content, excluding the <code>html</code> and <code>body</code> tags. The page
        already contains these tags, and your code will be inserted within the <code>body</code> tag directly.
    </div>

<?php

