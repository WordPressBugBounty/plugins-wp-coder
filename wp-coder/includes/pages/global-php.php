<?php

use WPCoder\Dashboard\DashboardInitializer;
use WPCoder\Dashboard\SaveGlobal;

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.', 'wpcoder' ) );
}


?>

    <div class="wowp-header-wrapper">
		<?php DashboardInitializer::header(); ?>

        <div class="wowp-header-title">
            <h2><?php esc_html_e( 'Global PHP', 'wpcoder' ); ?></h2>
        </div>

    </div>

<?php SaveGlobal::init();