<?php
/*
 * Page Name: List
 */

use WPCoder\Dashboard\DashboardInitializer;
use WPCoder\Dashboard\ListTable;
use WPCoder\WPCoder;

defined( 'ABSPATH' ) || exit;

$list_table = new ListTable();
$list_table->prepare_items();
$add_url = add_query_arg( [
	'page'   => WPCoder::SLUG . '-settings',
	'action' => 'new'
], admin_url( 'admin.php' ) );
?>

    <div class="wowp-header-wrapper">
		<?php
		DashboardInitializer::header(); ?>

        <div class="wowp-header-title">
            <h2><?php
				esc_html_e( 'All Codes', 'wp-coder' ); ?></h2>
            <a href="<?php
			echo esc_url( $add_url ); ?>" class="button button-primary button-large">
                + <?php
				esc_html_e( 'Add New', 'wp-coder' ); ?>
            </a>
            <a href="https://wpcoder.pro/snippet/?utm_source=wordpress&utm_medium=admin-menu&utm_campaign=snippets"
               class="button button-secondary button-large" target="_blank">
                <?php esc_html_e( 'Find Snippet', 'wp-coder' ); ?>
            </a>
        </div>

    </div>

    <form method="post" class="wowp-list">
		<?php
		$list_table->search_box( esc_attr__( 'Search', 'wp-coder' ), WPCoder::PREFIX );
		$list_table->display();
        $current_page = isset( $_GET['page'] ) ? (wp_unslash($_GET['page'])) : '';
		?>
        <input type="hidden" name="page" value="<?php echo esc_attr( $current_page ); ?>"/>
		<?php
		wp_nonce_field( WPCoder::PREFIX . '_nonce', WPCoder::PREFIX . '_list_action' ); ?>
    </form>
<?php
