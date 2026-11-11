<?php
/*
 * Page Name: Conditions
 */

use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\Option;

defined( 'ABSPATH' ) || exit;

$default       = Field::getDefault();
$opt           = include( 'options/conditions.php' );
$wpcoder_param = is_array( $default['param'] ?? null ) ? $default['param'] : [];

?>

    <fieldset id="code-conditions" class="wowp-fieldset">
        <div class="wowp-fieldset__header">
            <div class="wowp-fieldset__header-title">
				<?php esc_html_e( 'Where the snippet is shown', 'wp-coder' ); ?>
                <p><?php esc_html_e( 'By shortcode or block only, or automatically on every page of the site — then choose where on the page with Location.', 'wp-coder' ); ?></p>
            </div>
        </div>

		<?php if ( \WPCoder\Publisher\Conditions::is_pro_display( $wpcoder_param ) ) :
			// Not rendered, so saving here keeps Pro's rules (see save_settings()).
			?>
            <div class="wowp-notification notification-info">
				<?php esc_html_e( 'This snippet has display rules set in WP Coder Pro. The free version shows it by shortcode or block only, and keeps the rules for when Pro is active again.', 'wp-coder' ); ?>
            </div>
		<?php else : ?>
            <div class="wowp-fields__group">
				<?php Option::init( [ $opt['show'] ], 0 ); ?>
				<?php Option::init( [ $opt['location'] ] ); ?>
            </div>
            <script>
                (function () {
                    var show = document.getElementById('show_0');
                    var location = document.querySelector('#code-conditions .wowp-display-location');
                    if (!show || !location) {
                        return;
                    }
                    function sync() {
                        location.style.display = show.value === 'everywhere' ? '' : 'none';
                    }
                    show.addEventListener('change', sync);
                    sync();
                })();
            </script>
		<?php endif; ?>
    </fieldset>

<?php
$wpcoder_pro_inline      = true; // The Display field sits above the panel.
$wpcoder_pro_title       = __( 'More conditions in WP Coder Pro', 'wp-coder' );
$wpcoder_pro_description = __( 'Show a snippet only on the pages that need it, to the visitors it is meant for, when it is meant for them.', 'wp-coder' );
$wpcoder_pro_features    = [
	[
		'migrate',
		__( 'Placement per page', 'wp-coder' ),
		__( 'Combine Location with page rules: a JSON-LD block in <head> of product pages only, a banner after the content of blog posts.', 'wp-coder' ),
	],
	[
		'admin-page',
		__( 'Where it shows', 'wp-coder' ),
		__( 'Chosen posts, pages, post types, categories, tags, authors, archives, the home page or search results — or everywhere except some of them.', 'wp-coder' ),
	],
	[
		'groups',
		__( 'Who sees it', 'wp-coder' ),
		__( 'Logged-in or logged-out visitors, specific user roles, new or returning visitors, language, cookie, referrer and URL rules.', 'wp-coder' ),
	],
	[
		'smartphone',
		__( 'Devices and browsers', 'wp-coder' ),
		__( 'Hide on mobile or desktop, in particular browsers, or show only on chosen operating systems.', 'wp-coder' ),
	],
	[
		'calendar-alt',
		__( 'Schedule', 'wp-coder' ),
		__( 'Show the snippet on chosen weekdays, between set times (overnight ranges included) or within a date range — ideal for promotions.', 'wp-coder' ),
	],
	[
		'performance',
		__( 'Faster pages', 'wp-coder' ),
		__( 'Combine the CSS and JS of all snippets on a page into one file, load jQuery only when the code needs it, and dequeue files you choose.', 'wp-coder' ),
	],
];

require WPCoder\WPCoder::dir() . 'includes/pages/pro-features.php';
