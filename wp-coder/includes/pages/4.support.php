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
            <h2><?php esc_html_e( 'Support', 'wp-coder' ); ?></h2>
        </div>

    </div>

    <div class="wrap wowp-wrap">
        <div class="w_block w_has-border">

            <p>
	            <?php
	            esc_html_e( 'To get your support related question answered in the fastest timing, please send a message via the form below or write to us via', 'wp-coder' );
	            echo ' <a href="https://wordpress.org/support/plugin/wp-coder/" target="_blank">' . esc_html__( 'support page', 'wp-coder' ) . '</a>';
	            ?>
            </p>

            <p>
				<?php esc_html_e( 'Also, you can send us your ideas and suggestions for improving the plugin.', 'wp-coder' ); ?>
            </p>

			<?php SupportForm::init(); ?>

        </div>
    </div>
<?php
