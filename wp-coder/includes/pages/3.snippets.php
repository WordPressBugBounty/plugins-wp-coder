<?php


use WPCoder\Dashboard\DashboardInitializer;
use WPCoder\Snippets\SnippetManager;

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-coder' ) );
}


?>

<div class="wowp-header-wrapper">
	<?php
	DashboardInitializer::header(); ?>

    <div class="wowp-header-title">
        <h2><?php
			esc_html_e( 'Snippets Manager', 'wp-coder' ); ?></h2>
    </div>

</div>

<?php SnippetManager::init();

