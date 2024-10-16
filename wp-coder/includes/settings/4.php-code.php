<?php
/*
 * Page Name: PHP
 */

use WPCoder\Dashboard\Field;

defined( 'ABSPATH' ) || exit;
?>

    <h4>
        <span class="codericon codericon-filetype-php"></span>
		<?php esc_html_e( 'PHP Code', 'wp-coder' ); ?>
    </h4>

    <fieldset>
        <div class="wowp-field is-full">
            <ol id="phpNavigationMenu" class="wowp-php-nav-menu"></ol>
            <button class="button-editor button" id="phpnav">Add NAV Comment</button>
        </div>
    </fieldset>

<?php Field::textarea( 'php_code' ); ?>

    <div class="wowp-notification -warning">
        Please input only the PHP content, omitting the <code>php</code> tag
    </div>

<?php


