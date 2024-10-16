<?php

use WPCoder\Dashboard\DBManager;
use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\Link;
use WPCoder\WPCoder;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();

$link = ! empty( $default['param']['link'] ) ? $default['param']['link'] : '';

$shortcode = '';
if ( ! empty( $default['id'] ) ) {
	$shortcode = '[' . WPCoder::SHORTCODE . ' id="' . absint( $default['id'] ) . '"]';
}

?>

<div class="postbox ">
    <div class="postbox-header">
        <h2 class="hndle">
			<?php
			esc_html_e( 'Publish', 'wp-coder' ); ?>
        </h2>
    </div>

    <div class="inside">

        <div class="submitbox wowp-sidebar" id="submitpost">

            <div id="minor-publishing">
                <div class="misc-pub-section wowp-sidebar-pub-section">

                    <div class="wowp-field has-checkbox">
                        <label>
                            <span class="label"><?php
	                            esc_html_e( 'Status', 'wp-coder' ); ?></span>
							<?php
							Field::checkbox( 'status' ); ?>
                            <span><?php
								esc_html_e( 'Deactivate', 'wp-coder' ); ?></span>
                        </label>

                    </div>

                    <div class="wowp-field has-checkbox">
                        <label>
                            <span class="label"><?php
	                            esc_html_e( 'Test mode', 'wp-coder' ); ?></span>
							<?php
							Field::checkbox( 'mode' ); ?>
                            <span><?php
								esc_html_e( 'Activate', 'wp-coder' ); ?></span>
                        </label>
                    </div>

                    <div class="wowp-field has-addon border-default">
                        <label for="tag" class="is-addon"><span class="dashicons dashicons-tag"></span></label>
                        <input list="wowp-tags" type="text" name="tag" id="tag" value="<?php
						echo esc_attr( $default['tag'] ); ?>">
                        <datalist id="wowp-tags">
							<?php
							DBManager::display_tags(); ?>
                        </datalist>
                    </div>

                    <div class="wowp-field has-addon border-default">
                        <label for="link" class="is-addon">
							<?php
							if ( ! empty( $link ) ): ?>
                                <a href="<?php
								echo esc_url( $link ); ?>" target="_blank">
                                    <span class="dashicons dashicons-admin-links"></span>
                                </a>
							<?php
							else: ?>
                                <span class="dashicons dashicons-admin-links"></span>
							<?php
							endif; ?>
                        </label>
                        <input type="url" name="param[link]" id="link" value="<?php
						echo esc_url( $link ); ?>">
                    </div>

					<?php
					if ( ! empty( $shortcode ) ) : ?>
                        <div class="wowp-field has-addon border-default">
                            <label for="shortcode" class="is-addon wowp-shortcode-copy">
                                <span class="dashicons dashicons-shortcode"></span>
                            </label>
                            <input type="text" id="shortcode" readonly value="<?php
							echo esc_attr( $shortcode ); ?>">
                        </div>
					<?php
					endif; ?>

                </div>
            </div>

            <div class="major-publishing-actions" id="major-publishing-actions">
				<?php
				if ( ! empty( $default['id'] ) ): ?>
                    <div id="delete-action">
                        <a class="submitdelete deletion" href="<?php
						echo esc_url( Link::remove( $default['id'] ) ); ?>">
							<?php
							esc_html_e( 'Delete', 'wp-coder' ); ?>
                        </a>
                    </div>
				<?php
				endif; ?>

                <div id="publishing-action">
					<?php
					submit_button( null, 'primary', 'submit_settings', false ); ?>
                </div>
                <div class="clear"></div>

            </div>

        </div>
    </div>
</div>

    <div id="download_categorydiv" class="postbox wowp-feature-box">
        <div class="postbox-header">
            <h2 class="hndle">PRO Functions</h2>
        </div>
        <div class="inside">

            <div class="submitbox wowp-sidebar" id="wpcoder-template">

                <div class="misc-pub-section wowp-sidebar-pub-section">
                    <dl class="wowp-feature">
                        <dt class="wowp-feature-title">Create Custom templates</dt>
                        <dd class="wowp-feature-desc">Design custom templates for pages, posts, categories, and more, infusing your unique code for brand-aligned layouts.</dd>

                        <dt class="wowp-feature-title">Dequeue CSS and JS</dt>
                        <dd class="wowp-feature-desc">Increase site speed by adding only selected styles/scripts and removing redundant resources from the queue to optimize user experience.</dd>

                        <dt class="wowp-feature-title">Conditional Logic</dt>
                        <dd class="wowp-feature-desc">Utilize our Conditional Logic rule to dictate where your code will work: Pages Rules, Devices Rules, Scheduled, Browsers Rules, Users Rules, Language Rules</dd>

                        <dt class="wowp-feature-title">Uses extra Tools</dt>
                        <dd class="wowp-feature-desc">Enable Style and Script on the Login Page, Enable Maintenance Mode, Enable Extra Icon Enable Breadcrumbs</dd>

                        <dt class="wowp-feature-title">Shortcode Attributes</dt>
                        <dd class="wowp-feature-desc">Add custom attributes for shortcodes. Embed these attributes into your HTML code for tailored content display.</dd>

                        <dt class="wowp-feature-title minimal-price">Minimal Price</dt>
                        <dd class="wowp-feature-desc">Get PRO function for minimal cost 👍</dd>
                    </dl>
                    <a href="https://wpcoder.pro/" target="_blank">Read More about PRO</a>
                </div>
            </div>
        </div>
    </div>

<?php
