<?php
/**
 * A read-only panel describing what WP Coder Pro adds in this place.
 *
 * Expects from the including file:
 *   $wpcoder_pro_title       string
 *   $wpcoder_pro_description string
 *   $wpcoder_pro_features    array of [ dashicon, title, description ]
 *
 * It renders no form fields, so a free install never saves anything for it.
 * Set $wpcoder_pro_inline on a tab that has its own fields above the panel:
 * it is then set apart from them by a rule. Saving is the editor's one save
 * bar (Settings::save_bar()), shown on every tab.
 */

defined( 'ABSPATH' ) || exit;

$wpcoder_pro_url = WPCoder\WPCoder::PRO_URL;

?>
<div class="wowp-pro<?php echo ! empty( $wpcoder_pro_inline ) ? ' is-inline' : ''; ?>">
    <div class="wowp-snippets__header">
        <h3 class="wowp-snippets__header-title">
			<?php echo esc_html( $wpcoder_pro_title ); ?>
            <span class="wowp-pro__badge">PRO</span>
        </h3>
        <p class="wowp-snippets__header-description"><?php echo esc_html( $wpcoder_pro_description ); ?></p>
    </div>

    <div class="wowp-pro__features">
		<?php foreach ( $wpcoder_pro_features as $wpcoder_pro_feature ) : ?>
            <div class="wowp-pro__feature">
                <div class="wowp-pro-feature__icon">
                    <span class="dashicons dashicons-<?php echo esc_attr( $wpcoder_pro_feature[0] ); ?>"></span>
                </div>
                <div class="wowp-pro-feature__content">
                    <div class="wowp-pro-feature__title"><?php echo esc_html( $wpcoder_pro_feature[1] ); ?></div>
                    <div class="wowp-pro-feature__desc"><?php echo esc_html( $wpcoder_pro_feature[2] ); ?></div>
                </div>
            </div>
		<?php endforeach; ?>
    </div>

    <p class="wowp-pro__cta">
        <a href="<?php echo esc_url( $wpcoder_pro_url ); ?>" target="_blank" rel="noopener"
           class="wowp-button button button-dark">
            <span>⭐</span> <?php esc_html_e( 'See WP Coder Pro', 'wp-coder' ); ?>
        </a>
    </p>
</div>
<?php
// Settings tabs share one function scope; do not leak into the next tab.
unset( $wpcoder_pro_title, $wpcoder_pro_description, $wpcoder_pro_features, $wpcoder_pro_url, $wpcoder_pro_inline );
