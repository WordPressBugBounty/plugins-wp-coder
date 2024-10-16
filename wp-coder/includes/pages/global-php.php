<?php

use WPCoder\Dashboard\DashboardInitializer;
use WPCoder\Dashboard\SaveGlobal;

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-coder' ) );
}


?>

    <div class="wowp-header-wrapper">
		<?php DashboardInitializer::header(); ?>

        <div class="wowp-header-title">
            <h2><?php esc_html_e( 'Global PHP', 'wp-coder' ); ?></h2>
        </div>

    </div>

<?php SaveGlobal::init();