<?php
/**
 * Page Name: Support
 *
 */

use WPCoder\Dashboard\DashboardInitializer;
use WPCoder\Dashboard\SupportForm;
use WPCoder\WPCoder;

defined( 'ABSPATH' ) || exit;
$emil = WPCoder::info( 'email' );
?>

    <div class="wowp-header-wrapper">
		<?php DashboardInitializer::header(); ?>

        <div class="wowp-header-title">
            <h2><?php esc_html_e( 'Support', 'wpcoder' ); ?></h2>
        </div>

    </div>

    <div class="wrap wowp-wrap">
        <div class="w_block w_has-border">

            <p>
				<?php printf( esc_html__( 'To get your support related question answered in the fastest timing, please send a message via the form below or write to us on email %1$s.', 'wpcoder' ), '<a href="mailto:' . sanitize_email( $emil ) . '">' . sanitize_email( $emil ) . '</a>' ); ?>
            </p>

            <p>
				<?php esc_html_e( 'Also, you can send us your ideas and suggestions for improving the plugin.', 'wpcoder' ); ?>
            </p>

			<?php SupportForm::init(); ?>

        </div>
    </div>
<?php
