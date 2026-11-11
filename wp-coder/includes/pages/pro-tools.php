<?php
/**
 * "More in WP Coder Pro" list shown at the end of a Tools tab.
 *
 * Expects $wpcoder_pro_tools: array of [ title, description ]. Optional
 * $wpcoder_pro_tools_heading = false drops the heading, for a tab that has its own.
 * Read-only: no inputs, so saving the Tools page never stores anything for these.
 */

defined( 'ABSPATH' ) || exit;

$wpcoder_pro_tools_url = WPCoder\WPCoder::PRO_URL;

?>
<div class="wowp-pro__list">
	<?php if ( $wpcoder_pro_tools_heading ?? true ) : ?>
    <div class="wowp-snippets__header">
        <h3 class="wowp-snippets__header-title">
			<?php esc_html_e( 'More in WP Coder Pro', 'wp-coder' ); ?>
            <span class="wowp-pro__badge">PRO</span>
        </h3>
        <p class="wowp-snippets__header-description">
			<?php esc_html_e( 'These tools are part of WP Coder Pro.', 'wp-coder' ); ?>
            <a href="<?php echo esc_url( $wpcoder_pro_tools_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Learn more', 'wp-coder' ); ?></a>
        </p>
    </div>
	<?php endif; ?>

    <div class="wowp-snippet__list">
		<?php foreach ( $wpcoder_pro_tools as $wpcoder_pro_tool ) : ?>
            <div class="wowp-snippet__item">
                <div class="wowp-snippet__item-header">
                    <label><?php echo esc_html( $wpcoder_pro_tool[0] ); ?></label>
                    <p class="wowp-snippet__item-description"><?php echo esc_html( $wpcoder_pro_tool[1] ); ?></p>
                </div>
                <span class="wowp-pro__badge">PRO</span>
            </div>
		<?php endforeach; ?>
    </div>
</div>
<?php
// Tab pages share one function scope; do not leak into the next tab.
unset( $wpcoder_pro_tools, $wpcoder_pro_tools_heading );
