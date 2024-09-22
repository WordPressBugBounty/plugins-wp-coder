<?php
/*
 * Page Name: Add New
 */

use WPCoder\Dashboard\DashboardInitializer;
use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\Link;
use WPCoder\Dashboard\Settings;
use WPCoder\WPCoder;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();

$action     = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$item_id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';
$item_title = __( 'Add new code', 'wpcoder' );
if ( $action === 'update' && ! empty( $item_id ) ) {
	$item_title = __( 'Update code', 'wpcoder' ) . ' ID: ' . $item_id;
} elseif ( $action === 'duplicate' && ! empty( $item_id ) ) {
	$item_title = __( 'Duplicate the code from', 'wpcoder' ) . ' ID: ' . $item_id;
}
$license_page = add_query_arg( ['page' => WPCoder::SLUG . '-license' ], admin_url( 'admin.php' ) );
$add_url = add_query_arg( [
	'page'   => WPCoder::SLUG . '-settings',
	'action' => 'new'
], admin_url( 'admin.php' ) );


?>

    <div class="wowp-header-wrapper">
		<?php DashboardInitializer::header(); ?>

        <div class="wowp-header-title">
            <h2><?php echo esc_html( $item_title ); ?></h2>
            <a href="<?php echo esc_url( $add_url ); ?>" class="button button-primary button-large">
		        + <?php esc_html_e( 'Add New', 'wpcoder' ); ?>
            </a>

            <a href="<?php echo esc_url( Link::all_codes() ); ?>" class="button button-secondary button-large">
                ⇐ <?php esc_html_e( 'Back to Codes', 'wpcoder' ); ?>
            </a>

        </div>
    </div>


    <form action="" id="wowp-settings" class="wowp-settings wowp-wrap" method="post">

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">

                    <div id="titlediv">

                        <div id="titlewrap">
                            <label class="screen-reader-text" id="title-prompt-text"
                                   for="title"><?php esc_html_e( 'Enter title here', 'wpcoder' ); ?>
                            </label>
							<?php Field::text( 'title' ); ?>
                        </div>

                    </div>
                </div>

                <!--      Sidebar with the setting-->
                <div id="postbox-container-1" class="postbox-container">
					<?php require_once plugin_dir_path( __FILE__ ) . 'sidebar.php'; ?>
                </div>

                <div id="postoptions">
                    <div id="postbox-container-2" class="postbox-container wowp-settings-wrapper">
						<?php Settings::init(); ?>
                    </div>
                </div>
            </div>

        </div>

        <input type="hidden" name="tool_id" value="<?php echo absint( $default['id'] ); ?>" id="tool_id"/>
        <input type="hidden" name="param[time]" value="<?php echo esc_attr( time() ); ?>"/>
		<?php wp_nonce_field( WPCoder::PREFIX . '_nonce', WPCoder::PREFIX . '_settings' ); ?>

    </form>


<?php
