<?php

use WPCoder\Dashboard\DashboardInitializer;
use WPCoder\Dashboard\SaveGlobal;

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'unfiltered_html' ) ) {
	wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.', 'wp-coder' ) );
}


?>

    <div class="wowp">
	    <?php DashboardInitializer::header(); ?>

        <div class="wowp-page-header">
            <h2 class="wowp-page-header__title">
			    <?php esc_html_e( 'Global PHP', 'wp-coder' ); ?>
            </h2>
        </div>

        <?php SaveGlobal::init(); ?>

    </div>

<?php
