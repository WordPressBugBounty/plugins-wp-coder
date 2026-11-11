<?php
/*
 * Page Name: Attributes
 */

use WPCoder\Dashboard\Field;

defined( 'ABSPATH' ) || exit;

/*
 * Stored exactly as WP Coder Pro stores attributes — five index-aligned arrays,
 * shortcode_attribute / _type / _value / _label / _help — so a snippet keeps
 * them when it moves between the two versions.
 *
 * Every row posts all five, in order: a row of a type only Pro can edit (from a
 * snippet made in Pro) is shown read-only and travels in hidden inputs, and the
 * label and help text Pro may have set are carried along the same way. A
 * disabled <select> option would not be posted and would shift every array.
 */

$default = Field::getDefault();
$param   = is_array( $default['param'] ?? null ) ? $default['param'] : [];

$wpcoder_free_types = [
	'text'     => __( 'Text (single line)', 'wp-coder' ),
	'textarea' => __( 'Textarea (multi line)', 'wp-coder' ),
	'number'   => __( 'Number', 'wp-coder' ),
	'range'    => __( 'Range / Slider', 'wp-coder' ),
	'url'      => __( 'URL / Link', 'wp-coder' ),
	'image'    => __( 'Image', 'wp-coder' ),
	'color'    => __( 'Color', 'wp-coder' ),
	'date'     => __( 'Date / Time', 'wp-coder' ),
];

$wpcoder_pro_types = [
	'richtext'      => __( 'Rich Text', 'wp-coder' ),
	'toggle'        => __( 'Toggle (On/Off)', 'wp-coder' ),
	'select'        => __( 'Select / Dropdown', 'wp-coder' ),
	'repeater'      => __( 'Repeater', 'wp-coder' ),
	'post'          => __( 'Post / Page', 'wp-coder' ),
	'term'          => __( 'Taxonomy Term', 'wp-coder' ),
	'gradient'      => __( 'Gradient', 'wp-coder' ),
	'color-picker'  => __( 'Color Picker (Advanced)', 'wp-coder' ),
	'image_preload' => __( 'Image (with Preload)', 'wp-coder' ),
	'icon'          => __( 'Font Awesome Icon', 'wp-coder' ),
	'svg'           => __( 'SVG File', 'wp-coder' ),
	'library_icon'  => __( 'Icon (Icons page)', 'wp-coder' ),
];

$wpcoder_rows   = [];
$wpcoder_names  = isset( $param['shortcode_attribute'] ) && is_array( $param['shortcode_attribute'] ) ? $param['shortcode_attribute'] : [];
foreach ( $wpcoder_names as $wpcoder_key => $wpcoder_name ) {
	$wpcoder_rows[] = [
		'name'  => (string) $wpcoder_name,
		'type'  => (string) ( $param['shortcode_type'][ $wpcoder_key ] ?? 'text' ),
		'value' => (string) ( $param['shortcode_value'][ $wpcoder_key ] ?? '' ),
		'label' => (string) ( $param['shortcode_label'][ $wpcoder_key ] ?? '' ),
		'help'  => (string) ( $param['shortcode_help'][ $wpcoder_key ] ?? '' ),
	];
}

$wpcoder_attr_row = static function ( array $row ) use ( $wpcoder_free_types, $wpcoder_pro_types ) {
	$is_free = $row['type'] === '' || isset( $wpcoder_free_types[ $row['type'] ] );
	?>
    <div class="wowp-fields__group wowp-attr-row<?php echo $is_free ? '' : ' is-pro'; ?>">
		<?php if ( $is_free ) : ?>
            <div class="wowp-field wowp-attr-name">
                <label><span class="label"><?php esc_html_e( 'Attribute name', 'wp-coder' ); ?></span>
                    <input type="text" name="param[shortcode_attribute][]" value="<?php echo esc_attr( $row['name'] ); ?>"
                           placeholder="button_text">
                </label>
            </div>
            <div class="wowp-field wowp-attr-type">
                <label><span class="label"><?php esc_html_e( 'Field type', 'wp-coder' ); ?></span>
                    <select name="param[shortcode_type][]">
						<?php foreach ( $wpcoder_free_types as $type => $label ) : ?>
                            <option value="<?php echo esc_attr( $type ); ?>" <?php selected( $row['type'] ?: 'text', $type ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
                        <optgroup label="<?php esc_attr_e( 'WP Coder Pro', 'wp-coder' ); ?>">
							<?php foreach ( $wpcoder_pro_types as $type => $label ) : ?>
                                <option value="<?php echo esc_attr( $type ); ?>" disabled><?php echo esc_html( $label ); ?> · PRO</option>
							<?php endforeach; ?>
                        </optgroup>
                    </select>
                </label>
            </div>
            <div class="wowp-field wowp-attr-value">
                <label><span class="label"><?php esc_html_e( 'Default value', 'wp-coder' ); ?>
                        <sup class="wowp-tooltip" data-tooltip="<?php esc_attr_e( 'What a new block starts with. For a Range: min:max:step:default, e.g. 0:200:5:100.', 'wp-coder' ); ?>">ℹ</sup></span>
                    <input type="text" name="param[shortcode_value][]" value="<?php echo esc_attr( $row['value'] ); ?>">
                </label>
            </div>
            <?php // At the end of the row, as in Pro. ?>
            <button type="button" class="wowp-button__icon is-remove dashicons dashicons-trash"
                    title="<?php esc_attr_e( 'Remove attribute', 'wp-coder' ); ?>"
                    aria-label="<?php esc_attr_e( 'Remove attribute', 'wp-coder' ); ?>"></button>
		<?php else : ?>
            <span class="wowp-pro__badge">PRO</span>
            <div class="wowp-attr-pro-text">
                <code><?php echo esc_html( $row['name'] ); ?></code> —
				<?php echo esc_html( $wpcoder_pro_types[ $row['type'] ] ?? $row['type'] ); ?>.
				<?php esc_html_e( 'Set up in WP Coder Pro: it keeps working and is kept as it is.', 'wp-coder' ); ?>
            </div>
            <input type="hidden" name="param[shortcode_attribute][]" value="<?php echo esc_attr( $row['name'] ); ?>">
            <input type="hidden" name="param[shortcode_type][]" value="<?php echo esc_attr( $row['type'] ); ?>">
            <input type="hidden" name="param[shortcode_value][]" value="<?php echo esc_attr( $row['value'] ); ?>">
		<?php endif; ?>
        <input type="hidden" name="param[shortcode_label][]" value="<?php echo esc_attr( $row['label'] ); ?>">
        <input type="hidden" name="param[shortcode_help][]" value="<?php echo esc_attr( $row['help'] ); ?>">
    </div>
	<?php
};

?>

<fieldset id="code-attributes" class="wowp-fieldset">

    <div class="wowp-fieldset__header">
        <div class="wowp-fieldset__header-title">
			<?php esc_html_e( 'Attributes', 'wp-coder' ); ?>
            <p><?php
				printf(
				/* translators: 1: shortcode example, 2: placeholder example */
					esc_html__( 'Values each shortcode or block fills in: %1$s, used in the code as %2$s.', 'wp-coder' ),
					'<code>[wp_code id="1" button_text="Buy now"]</code>',
					'<code>{{button_text}}</code>'
				); ?>
                <br><?php esc_html_e( 'An Image attribute also gives {{name_url}}, {{name_alt}}, {{name_width}}, {{name_height}}, {{name_srcset}} and a ready {{name_html}} tag with WordPress lazy-loading applied.', 'wp-coder' ); ?>
            </p>
        </div>

        <div class="wowp-fieldset__header-button">
            <?php // A button, not an <a> without href: that one could not be reached with Tab. ?>
            <button type="button" class="button button-secondary" id="add-attribute">+ <?php esc_html_e( 'Add attribute', 'wp-coder' ); ?></button>
        </div>
    </div>

    <?php // Shown while the list is empty (admin-jquery.js keeps it in step). ?>
    <p class="wowp-attr-empty"<?php echo $wpcoder_rows ? ' hidden' : ''; ?>><?php
	    esc_html_e( 'No attributes yet. Add one to let each shortcode or block set its own text, image, link or colour without copying the snippet.', 'wp-coder' ); ?></p>

	<?php foreach ( $wpcoder_rows as $wpcoder_row ) {
		$wpcoder_attr_row( $wpcoder_row );
	} ?>

    <?php // Also at the foot of the list, where a long list ends; hidden while it is empty. ?>
    <div class="btn-add-display"<?php echo $wpcoder_rows ? '' : ' hidden'; ?>>
        <button type="button" class="button" id="add-attribute-bottom">+ <?php esc_html_e( 'Add attribute', 'wp-coder' ); ?></button>
    </div>
    <?php // Undo for a removal, as in WP Coder Pro. ?>
    <div class="wowp-attr-undo" id="wowp-attr-undo" role="status" hidden
         data-removed="<?php /* translators: %s: attribute name. */ esc_attr_e( 'Attribute “%s” removed.', 'wp-coder' ); ?>">
        <span class="wowp-attr-undo__text"></span>
        <button type="button" class="button button-small" id="wowp-attr-undo-button"><?php esc_html_e( 'Undo', 'wp-coder' ); ?></button>
    </div>
</fieldset>

<template id="clone-attributes">
	<?php $wpcoder_attr_row( [ 'name' => '', 'type' => 'text', 'value' => '', 'label' => '', 'help' => '' ] ); ?>
</template>

<?php
$wpcoder_pro_inline      = true; // The tab's own fields sit above the panel.
$wpcoder_pro_title       = __( 'More in WP Coder Pro', 'wp-coder' );
$wpcoder_pro_description = __( 'Turn a snippet into a full component for the block editor.', 'wp-coder' );
$wpcoder_pro_features    = [
	[
		'forms',
		__( 'More field types', 'wp-coder' ),
		__( 'Rich text, toggle, select, gradient, post and taxonomy pickers, Font Awesome, SVG and icon library fields, and an image with preload for the hero image.', 'wp-coder' ),
	],
	[
		'list-view',
		__( 'Repeater fields', 'wp-coder' ),
		__( 'Lists of items such as slides, team members or FAQ entries, each with its own sub-fields, rendered with a loop.', 'wp-coder' ),
	],
	[
		'block-default',
		__( 'A friendlier block', 'wp-coder' ),
		__( 'Labels and help text for every field, and a live preview that updates as you edit.', 'wp-coder' ),
	],
	[
		'randomize',
		__( 'QuickCode and Loop Logic', 'wp-coder' ),
		__( 'Live site data such as {{post.title}} in any snippet, and <If> / <Loop> blocks driven by attribute values.', 'wp-coder' ),
	],
];

require WPCoder\WPCoder::dir() . 'includes/pages/pro-features.php';
