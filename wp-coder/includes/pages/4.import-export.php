<?php
/**
 * Page Name: Import/Export
 *
 */

use WPCoder\Dashboard\DashboardInitializer;
use WPCoder\Dashboard\ImporterExporter;
use WPCoder\WPCoder;

defined( 'ABSPATH' ) || exit;
?>

    <div class="wowp-header-wrapper">
		<?php DashboardInitializer::header(); ?>

        <div class="wowp-header-title">
            <h2><?php esc_html_e( 'Import / Export', 'wpcoder' ); ?></h2>
        </div>

    </div>

    <div class="wrap wowp-wrap">
        <div class="w_block w_has-border">

            <div class="inside">
                <h3><span class="dashicons dashicons-database-export"></span> <span><?php esc_attr_e( 'Export Settings', 'wpcoder' ); ?></span></h3>
                <div class="inside">
                    <p><?php
						printf( esc_attr__( 'Export the  settings for %s as a .json file. This allows you to easily import the configuration into another site.', 'wpcoder' ), '<b>' . esc_attr( WPCoder::info( 'name' ) ) . '</b>' ); ?></p>
					<?php ImporterExporter::form_export(); ?>
                </div>
            </div>
            <hr>

            <div class="inside">
                <h3><span class="dashicons dashicons-database-import"></span> <span><?php esc_attr_e( 'Import Settings', 'wpcoder' ); ?></span></h3>
                <div class="inside">
                    <p><?php
						printf( esc_attr__( 'Import the %s settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'wpcoder' ), '<b>' . esc_attr( WPCoder::info( 'name' ) ) . '</b>    ' ); ?></p>
					<?php ImporterExporter::form_import(); ?>
                </div>
            </div>


        </div>
    </div>

<?php
