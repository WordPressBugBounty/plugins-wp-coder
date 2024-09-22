<?php

defined( 'ABSPATH' ) || exit;

?>

<dl class="wowp-snippet__list">

    <dt><label><?php
			self::field( 'checkbox', 'change_logo_on_site_icon' ); ?>Change logo on Login Page</label></dt>
    <dd>Change the default WP logo on Site Icon. Go to the <a href="<?php
		echo esc_url( get_admin_url() ); ?>customize.php">customizer</a> to set or change your site icon.
    </dd>

    <dt><label><?php
			self::field( 'checkbox', 'change_logo_link' ); ?>Change URL for logo on Login Page</label></dt>
    <dd>Change the default wordpress.org URL of the logo to the blog home URL.</dd>

    <dt><label><?php
			self::field( 'checkbox', 'change_redirect_after_login' ); ?>Change Redirect After Login</label></dt>
    <dd>Change the redirect URL for all users after Login.
        <details class="wpcoder-expand">
            <summary class="wpcoder-expand__title">Expand</summary>
            <div class="wpcoder-expand__content">
                <fieldset>
                    <div class="wowp-field">
                        <label><span class="label">Redirect to: <?php echo esc_url(get_site_url());?>/</span>
							<?php
							self::field( 'text', 'change_redirect_login_link', '', 'account' ); ?>
                        </label>
                    </div>
                </fieldset>

            </div>
        </details>
    </dd>

    <dt><label><?php
			self::field( 'checkbox', 'change_redirect_after_logout' ); ?>Change Redirect After Logout</label></dt>
    <dd>Change the redirect URL for all users after Logout.
        <details class="wpcoder-expand">
            <summary class="wpcoder-expand__title">Expand</summary>
            <div class="wpcoder-expand__content">
                <fieldset>
                    <div class="wowp-field">
                        <label><span class="label">Redirect to: <?php echo esc_url(get_site_url());?>/</span>
							<?php
							self::field( 'text', 'change_redirect_logout_link', '', 'visit-again' ); ?>
                        </label>
                    </div>
                </fieldset>

            </div>
        </details>
    </dd>

    <dt><label><?php
			self::field( 'checkbox', 'change_revisions_control' ); ?>Change Number of Post Revisions</label></dt>
    <dd>Set the limiting post revisions to reduce Database size.
        <details class="wpcoder-expand">
            <summary class="wpcoder-expand__title">Expand</summary>
            <div class="wpcoder-expand__content">
                <fieldset>
                    <div class="wowp-field">
                        <label><span class="label">Number of revisions</span>
							<?php
							self::field( 'number', 'change_revisions_control_number', 10 ); ?>
                        </label>
                    </div>
                </fieldset>

            </div>
        </details>
    </dd>

    <dt><label><?php
			self::field( 'checkbox', 'change_oEmbed_size' ); ?>Change oEmbed Max Width and Height</label></dt>
    <dd>Change a max Width and Height for the embeds using oEmbed in the content.
        <details class="wpcoder-expand">
            <summary class="wpcoder-expand__title">Expand</summary>
            <div class="wpcoder-expand__content">
                <fieldset>
                    <div class="wowp-field">
                        <label><span class="label">Width</span>
							<?php
							self::field( 'number', 'change_oEmbed_size_width', '', '400' ); ?>
                        </label>
                    </div>
                    <div class="wowp-field">
                        <label><span class="label">Height</span>
			                <?php
			                self::field( 'number', 'change_oEmbed_size_height', '', '280' ); ?>
                        </label>
                    </div>
                </fieldset>

            </div>
        </details>
    </dd>

    <dt><label><?php
			self::field( 'checkbox', 'change_read_more' ); ?>Change Read More Text for Excerpts</label></dt>
    <dd>Customize the "Read More" text that shows up after excerpts.
        <details class="wpcoder-expand">
            <summary class="wpcoder-expand__title">Expand</summary>
            <div class="wpcoder-expand__content">
                <fieldset>
                    <div class="wowp-field">
                        <label><span class="label">Text</span>
							<?php
							self::field( 'text', 'change_read_more_text', '', 'Read More' ); ?>
                        </label>
                    </div>
                </fieldset>

            </div>
        </details>
    </dd>

    <dt><label><?php
			self::field( 'checkbox', 'change_expiration_remember_me' ); ?>Extend Login Expiration Time</label></dt>
    <dd>Toggling "Remember Me" will keep you logged in for your needed enter days instead of 14 days.
        <details class="wpcoder-expand">
            <summary class="wpcoder-expand__title">Expand</summary>
            <div class="wpcoder-expand__content">
                <fieldset>
                    <div class="wowp-field">
                        <label><span class="label">Days</span>
							<?php
							self::field( 'number', 'change_expiration_remember_me_day', 30, 14 ); ?>
                        </label>
                    </div>
                </fieldset>

            </div>
        </details>
    </dd>



</dl>
