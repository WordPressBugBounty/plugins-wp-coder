<?php
/*
 * Page Name: JS
 */

use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\FolderManager;
use WPCoder\Dashboard\Option;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();
$opt     = include( 'options/js-code.php' );

$js_link = '';
if ( ! empty( $default['js_code'] ) ) {
	$js_link = FolderManager::path_upload_url() . 'script-' . $default['id'] . '.js';
}

?>
    <h4>
        <span class="codericon codericon-filetype-js"></span>
		<?php esc_html_e( 'JavaScript Code', 'wp-coder' ); ?>
    </h4>


    <details class="details">
        <summary><?php esc_html_e( 'Settings', 'wp-coder' ); ?></summary>
        <div>
			<?php Option::init( [
				$opt['jquery'],
				$opt['inline'],
				$opt['minified'],
				$opt['attributes'],
			] ); ?>
        </div>
    </details>

    <details class="details">
        <summary>
			<?php esc_html_e( 'Navigation', 'wp-coder' ); ?>
            <div class="button-group alignright">
                <button class="button-editor button" id="jsnav">Add NAV Comment</button>
            </div>
        </summary>
        <div class="wowp-field">
            <ol id="jsNavigationMenu" class="wowp-php-nav-menu"></ol>
        </div>
    </details>

    <fieldset>
        <div class="wowp-field is-full">
            <ol id="jsNavigationMenu" class="wowp-php-nav-menu"></ol>
            <button class="button-editor button" id="jsnav">Add NAV Comment</button>
        </div>
    </fieldset>

<?php Option::init( [ $opt['js_code'] ] ); ?>

    <div class="wowp-notification -warning">
        Please input only the JavaScript content, omitting the <code>script</code> tag
    </div>

<?php if ( ! empty( $js_link ) ): ?>
    <div class="wowp-fields-group has-mt">
        <div class="wowp-field is-full has-addon border-default">
            <label for="url-css-file" class="is-addon">
                <span class="dashicons dashicons-admin-links"></span>
            </label>
            <input type="url" id="url-css-file" readonly="readonly" value="<?php echo esc_url( $js_link ); ?>">
            <span class="label"><?php esc_html_e( 'URL to the JS file', 'wp-coder' ); ?></span>
        </div>
    </div>
<?php endif;

