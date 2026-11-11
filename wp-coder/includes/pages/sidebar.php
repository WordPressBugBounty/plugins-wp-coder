<?php

use WPCoder\Dashboard\DBManager;
use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\Link;
use WPCoder\WPCoder;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();

$link = ! empty( $default['param']['link'] ) ? $default['param']['link'] : '';

$shortcode       = '';
$shortcode_short = '';
if ( ! empty( $default['id'] ) ) {
	// The field shows the plain form, which is what a page usually needs; the
	// form with every attribute (cut off in the field once there were a few) is
	// copied by its own button.
	$shortcode_short = '[' . WPCoder::SHORTCODE . ' id="' . absint( $default['id'] ) . '"]';
	$shortcode       = '[' . WPCoder::SHORTCODE . ' id="' . absint( $default['id'] ) . '"';

	// Each attribute with what a new block starts with; a Range's value field
	// holds its limits, so the shortcode gets their default. Types set up in
	// WP Coder Pro are left out — their value field is configuration.
	$wpcoder_param = is_array( $default['param'] ?? null ) ? $default['param'] : [];
	foreach ( (array) ( $wpcoder_param['shortcode_attribute'] ?? [] ) as $wpcoder_key => $wpcoder_name ) {
		$wpcoder_type  = (string) ( $wpcoder_param['shortcode_type'][ $wpcoder_key ] ?? 'text' );
		$wpcoder_value = (string) ( $wpcoder_param['shortcode_value'][ $wpcoder_key ] ?? '' );

		if ( $wpcoder_type === 'range' ) {
			$wpcoder_parts = explode( ':', $wpcoder_value );
			$wpcoder_value = count( $wpcoder_parts ) > 1
				? ( is_numeric( $wpcoder_parts[3] ?? '' ) ? $wpcoder_parts[3] : ( is_numeric( $wpcoder_parts[0] ) ? $wpcoder_parts[0] : '0' ) )
				: ( is_numeric( $wpcoder_value ) ? $wpcoder_value : '0' );
		} elseif ( ! in_array( $wpcoder_type, [ 'text', 'textarea', 'number', 'url', 'image', 'color', 'date' ], true ) ) {
			continue;
		}

		$shortcode .= ' ' . $wpcoder_name . '="' . str_replace( [ '"', ']' ], '', $wpcoder_value ) . '"';
	}

	$shortcode .= ']';
}

?>

<div class="wowp-settings__header">

    <div class="wowp-settings__title">
        <label class="screen-reader-text" id="title-prompt-text"
               for="title"><?php esc_html_e( 'Enter title here', 'wp-coder' ); ?>
        </label>
		<?php // A visible prompt: the label above is for screen readers only, and an
		      // empty new snippet showed a bare box. ?>
		<?php Field::text( 'title', '', 'text', '', __( 'Snippet title', 'wp-coder' ) ); ?>
		<?php // Save lives in the save bar (Settings::save_bar()) — one button, always in view. ?>
    </div>

    <div class="wowp-settings__publish">

        <div class="wowp-settings__publish-status">
			<?php
			// "Active": on while the snippet runs. "Deactivate" read backwards —
			// the switch was on for a snippet that was off. The column keeps its
			// meaning (status "1" = deactivated; the list, bulk actions and WP
			// Coder Pro read it), so this switch is inverted: data-inverse makes
			// admin-jquery.js write "1" when it is turned off.
			$status = (string) $default['status'];
			?>
            <div class="wowp-field has-checkbox">
              <span class="label">
                    <?php esc_html_e( 'Active', 'wp-coder' ); ?>
                   <sup class="wowp-tooltip" data-tooltip="<?php esc_attr_e( 'The snippet runs on the site. Turn it off to stop it without deleting it.', 'wp-coder' ); ?>">ℹ</sup>
                </span>
                <label class="switch">
                    <input type="checkbox" value="1" id="checkbox_status" data-inverse="1"<?php checked( $status === '' ); ?>>
                    <input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>" class="checkbox-helper" id="status">
                    <span class="slider"></span>
                </label>
            </div>

            <div class="wowp-field has-checkbox">
                <span class="label">
                    <?php esc_html_e( 'Test mode', 'wp-coder' ); ?>
                    <sup class="wowp-tooltip"
                         data-tooltip="<?php esc_attr_e( 'Code runs only for logged-in administrators. Useful for testing before publishing.', 'wp-coder' ); ?>">ℹ</sup>
                </span>
                <label class="switch">
					<?php Field::checkbox( 'mode' ); ?>
                    <span class="slider"></span>
                </label>
            </div>

        </div>

        <div class="wowp-settings__publish-info">
			<?php if ( ! empty( $shortcode ) ) : ?>
				<?php $shortcode_has_attrs = $shortcode !== $shortcode_short; ?>
                <div class="wowp-field has-addon<?php echo $shortcode_has_attrs ? ' has-end-addon' : ''; ?>">
                    <label for="shortcode" class="label is-addon">
                        <span class="wowp-tooltip on-right is-pointer can-copy" data-tooltip="<?php esc_attr_e( 'Copy', 'wp-coder' ); ?>">
                            <span class="dashicons dashicons-shortcode"></span>
                        </span>
                    </label>
                    <input type="text" id="shortcode" readonly value="<?php echo esc_attr( $shortcode_short ); ?>"
                           aria-label="<?php esc_attr_e( 'Shortcode', 'wp-coder' ); ?>">
					<?php if ( $shortcode_has_attrs ) : ?>
                        <button type="button"
                                class="wowp-field__end-addon wowp-copy-full wowp-tooltip on-left is-pointer"
                                data-copy="<?php echo esc_attr( $shortcode ); ?>"
                                aria-label="<?php esc_attr_e( 'Copy with attributes', 'wp-coder' ); ?>"
                                data-tooltip="<?php esc_attr_e( 'Copy with attributes', 'wp-coder' ); ?>">
                            <span class="dashicons dashicons-editor-code"></span>
                        </button>
					<?php endif; ?>
                </div>
			<?php endif; ?>

            <div class="wowp-field has-addon">
                <label for="tag" class="label is-addon wowp-tooltip on-right"
                       data-tooltip="<?php esc_attr_e( 'Separate several tags with a comma. A tag name cannot itself contain a comma.', 'wp-coder' ); ?>">
                    <span class="dashicons dashicons-tag"></span>
                </label>
                <input list="wowp-tags" type="text" name="tag" id="tag"
                       placeholder="<?php esc_attr_e( 'Tags', 'wp-coder' ); ?>"
                       aria-label="<?php esc_attr_e( 'Tags', 'wp-coder' ); ?>"
                       value="<?php echo esc_attr( $default['tag'] ); ?>">
                <datalist id="wowp-tags">
					<?php DBManager::display_tags(); ?>
                </datalist>
            </div>

            <div class="wowp-field has-addon">
                <label for="link" class="label is-addon">
					<?php if ( ! empty( $link ) ): ?>
                        <a href="<?php echo esc_url( $link ); ?>" target="_blank">
                            <span class="wowp-tooltip on-right is-pointer" data-tooltip="<?php esc_attr_e( 'Open link', 'wp-coder' ); ?>">
                                <span class="dashicons dashicons-admin-links"></span>
                            </span>
                        </a>
					<?php else: ?>
                        <span class="dashicons dashicons-admin-links"></span>
					<?php endif; ?>
                </label>
                <?php // Only for the people editing the snippet; nothing on the site reads it. ?>
                <input type="url" name="param[link]" id="link" value="<?php echo esc_url( $link ); ?>"
                       placeholder="<?php esc_attr_e( 'Reference link: docs, design, task…', 'wp-coder' ); ?>"
                       aria-label="<?php esc_attr_e( 'Reference link', 'wp-coder' ); ?>">
            </div>
        </div>

        <?php
        // A note for whoever edits the snippet next; never printed on the site.
        // Collapsed by default. Empty, its summary says "Add a note" (a bare
        // "Note" did not read as something to click); filled, a dot and the first
        // line show while folded and the whole note on hover. Kept live by
        // admin-jquery.js as the note is typed.
        $note      = (string) ( $default['param']['note'] ?? '' );
        $note_line = strtok( $note, "\n" );
        ?>
        <details class="wowp-settings__publish-note<?php echo $note !== '' ? ' has-note' : ''; ?>">
            <summary<?php echo $note !== '' ? ' title="' . esc_attr( $note ) . '"' : ''; ?>>
                <span class="dashicons dashicons-edit-large" aria-hidden="true"></span>
                <span class="wowp-note__label"
                      data-empty="<?php esc_attr_e( 'Add a note', 'wp-coder' ); ?>"
                      data-filled="<?php esc_attr_e( 'Note', 'wp-coder' ); ?>"><?php
                    echo $note !== '' ? esc_html__( 'Note', 'wp-coder' ) : esc_html__( 'Add a note', 'wp-coder' ); ?></span>
                <span class="wowp-note__dot" aria-hidden="true"></span>
                <span class="wowp-note__preview"><?php echo esc_html( $note_line === false ? '' : $note_line ); ?></span>
                <span class="dashicons dashicons-arrow-down-alt2 wowp-note__chevron" aria-hidden="true"></span>
            </summary>
            <label for="note" class="screen-reader-text"><?php esc_html_e( 'Note', 'wp-coder' ); ?></label>
            <textarea name="param[note]" id="note" rows="3" aria-describedby="wowp-note-help"
                      placeholder="<?php esc_attr_e( 'What this snippet is for, where it is used…', 'wp-coder' ); ?>"><?php
                echo esc_textarea( $note ); ?></textarea>
            <p class="wowp-note__help" id="wowp-note-help"><?php
                esc_html_e( 'Seen only by people who edit snippets — here and under the title in All Codes. Never shown on the site.', 'wp-coder' ); ?></p>
        </details>

    </div>

</div>
