<?php
/*
 * Page Name: Scripts & Styles
 */

use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\Option;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();
$opt     = include( 'options/includes.php' );
$count   = ! empty( $default['param']['include'] ) ? count( $default['param']['include'] ) : 0;
$include_fields = [
	$opt['include'],
	$opt['include_file'],
	$opt['js_attr'],
	$opt['css_only_preview'],
];

// × at the end of a row, as in WP Coder Pro. type="button": without it the
// button submitted the form — cancelling the old confirm() reloaded the page
// without saving and threw away every unsaved change.
$wpcoder_asset_remove = static function () {
	?>
    <button type="button" class="wowp-asset-remove" title="<?php esc_attr_e( 'Remove file', 'wp-coder' ); ?>" aria-label="<?php esc_attr_e( 'Remove file', 'wp-coder' ); ?>">
        <span class="dashicons dashicons-trash" aria-hidden="true"></span>
    </button>
	<?php
};

?>

    <fieldset id="includes-files" class="wowp-fieldset wowp-assets">
        <div class="wowp-fieldset__header">
            <div class="wowp-fieldset__header-title">
				<?php esc_html_e( 'Load scripts and styles', 'wp-coder' ); ?>
                <p><?php esc_html_e( 'Load a stylesheet or script by its URL — a library from a CDN, for example — wherever this snippet is shown.', 'wp-coder' ); ?></p>
            </div>

            <div class="wowp-fieldset__header-button">
                <button type="button" class="button button-secondary" id="add-include">+ <?php esc_html_e( 'Add file', 'wp-coder' ); ?></button>
            </div>
        </div>

        <p class="wowp-assets__empty"<?php echo $count > 0 ? ' hidden' : ''; ?>><?php esc_html_e( 'No files yet.', 'wp-coder' ); ?></p>

		<?php for ( $i = 0; $i < $count; $i ++ ) : ?>
            <div class="wowp-fields__group wowp-asset-row">
				<?php Option::init( $include_fields, $i ); ?>
				<?php $wpcoder_asset_remove(); ?>
            </div>
		<?php endfor; ?>

        <div class="btn-add-display"></div>

        <div class="wowp-attr-undo" role="status" hidden
             data-removed="<?php /* translators: %s: file name. */ esc_attr_e( 'File “%s” removed.', 'wp-coder' ); ?>">
            <span class="wowp-attr-undo__text"></span>
            <button type="button" class="button button-small wowp-undo-button"><?php esc_html_e( 'Undo', 'wp-coder' ); ?></button>
        </div>
    </fieldset>

    <template id="clone-includes">
        <div class="wowp-fields__group wowp-asset-row">
			<?php Option::init( $include_fields, - 1 ); ?>
			<?php $wpcoder_asset_remove(); ?>
        </div>
    </template>

<?php
unset( $wpcoder_asset_remove, $include_fields );
