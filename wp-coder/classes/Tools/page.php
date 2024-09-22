<?php

use WPCoder\Dashboard\FieldHelper;

defined( 'ABSPATH' ) || exit;

?>

<dl class="wowp-snippet__list">

    <dt><label>
			<?php self::field( 'checkbox', 'enable_tracking_tool' ); ?>Tracking Code Manager</label>
    </dt>
    <dd>Easily integrate your website with popular platforms like Google, Facebook, and Pinterest by adding their
        respective IDs, enabling seamless tracking and analytics.
        <details class="wpcoder-expand"<?php self::expand( 'enable_tracking_tool' ); ?>>
            <summary class="wpcoder-expand__title">Expand</summary>
            <div class="wpcoder-expand__content">
                <fieldset class="_bg-white">
                    <div class="wowp-field">
                        <label><span class="label">Google Analytics</span>
							<?php self::field( 'text', 'tracking_tool_google', '', 'set tracking ID' ); ?>
                        </label>
                        <a target="_blank" href="https://support.google.com/analytics/answer/9539598?hl=en">How to
                            find the tracking ID</a>
                    </div>
                    <div class="wowp-field">
                        <label><span class="label">Facebook Pixel</span>
							<?php self::field( 'text', 'tracking_tool_facebook', '', 'set Pixel ID' ); ?>
                        </label>
                        <a target="_blank"
                           href="https://en-gb.facebook.com/business/help/952192354843755?id=1205376682832142">How
                            to find the Facebook pixel ID</a>
                    </div>
                    <div class="wowp-field">
                        <label><span class="label">Pintrest Pixel</span>
							<?php self::field( 'text', 'tracking_tool_pintrest', '', 'set Pixel ID' ); ?>
                        </label>
                        <a target="_blank"
                           href="https://help.pinterest.com/en/business/article/install-the-pinterest-tag">How to
                            find the Pinterest pixel ID</a>
                    </div>
                </fieldset>
                <p><b>Disable tracking for users:</b></p>
                <fieldset class="_bg-white">
		            <?php foreach ( FieldHelper::user_roles() as $key => $value ) :
			            if ( $key === 'all' ) {
				            continue;
			            }
			            ?>
                        <div class="wowp-field has-checkbox">
                            <label>
                                <span class="label"><?php echo esc_html( $value ); ?></span>
					            <?php self::field( 'checkbox', 'disable_tracking_tool_user_' . $key ); ?>
                                <span><?php esc_html_e( 'Enable', 'wpcoder' ); ?></span>
                            </label>
                        </div>
		            <?php endforeach; ?>
                </fieldset>

            </div>
        </details>
    </dd>

    <dt><label>
			<?php self::field( 'checkbox', 'enable_google_adsense' ); ?>Google AdSense </label>
    </dt>
    <dd>Easily add Google AdSense to your WordPress site.
        <details class="wpcoder-expand"<?php self::expand( 'enable_google_adsense' ); ?>>
            <summary class="wpcoder-expand__title">Expand</summary>
            <div class="wpcoder-expand__content">
                <fieldset class="_bg-white">
                    <div class="wowp-field">
                        <label><span class="label">Publisher ID</span>
			                <?php self::field( 'text', 'google_adsense_publisher', '', 'e.g pub-1234567890111213' ); ?>
                        </label>
                        <a target="_blank"
                           href="https://support.google.com/adsense/answer/105516?hl=en">Find your publisher ID</a>
                    </div>
                </fieldset>
                <p><b>Disable Google AdSense Ads for users:</b></p>
                <fieldset class="_bg-white">
		            <?php foreach ( FieldHelper::user_roles() as $key => $value ) :
			            if ( $key === 'all' ) {
				            continue;
			            }
			            ?>
                        <div class="wowp-field has-checkbox">
                            <label>
                                <span class="label"><?php echo esc_html( $value ); ?></span>
					            <?php self::field( 'checkbox', 'disable_google_adsense_user_' . $key ); ?>
                                <span><?php esc_html_e( 'Enable', 'wpcoder' ); ?></span>
                            </label>
                        </div>
		            <?php endforeach; ?>
                </fieldset>

            </div>
        </details>
    </dd>

    <dt><label><b style="color: var(--wowp-orange);">PRO</b> Enable Style and Script on Login Page </label></dt>
    <dd>Add Login page custom Style and Script

    </dd>

    <dt><label><b style="color: var(--wowp-orange);">PRO</b> Enable Maintenance Mode</label></dt>
    <dd>Show a customizable maintenance page on the frontend while performing a brief maintenance to your site. Logged-in administrators can still view the site as usual.

    </dd>

    <dt><label><b style="color: var(--wowp-orange);">PRO</b> Enable Extra Icon</label>
    </dt>
    <dd>Enable Extra Icon for categories/tags and pages/posts.</dd>

    <dt><label><b style="color: var(--wowp-orange);">PRO</b> Enable Breadcrumbs</label
    </dt>
    <dd>You can use the function <code>wpc_get_breadcrumbs()</code> on the template for display breadcrumbs. </dd>

    <dt><label><b style="color: var(--wowp-orange);">PRO</b> Enable QuickCode</label
    </dt>
    <dd>Enable tool for uses QuickCodes in the Items. </dd>

</dl>
