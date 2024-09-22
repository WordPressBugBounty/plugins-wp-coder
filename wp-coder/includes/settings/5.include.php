<?php
/*
 * Page Name: Include files
 */

use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\Option;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();
$opt = include( 'options/includes.php' );
$count   = ! empty( $default['param']['include'] ) ? count( $default['param']['include'] ) : 0;
?>

    <h4>
        <span class="codericon codericon-toggles"></span>
		<?php esc_html_e( 'Assets', 'wpcoder' ); ?>
    </h4>

    <fieldset id="includes-files">
        <legend><?php esc_html_e( 'Adding', 'wpcoder' ); ?></legend>

		<?php if ( $count > 0 ) :
			for ( $i = 0; $i < $count; $i ++ ):
				?>

                <div class="wowp-fields-group has-padding has-shadow has-radius">
                    <span class="dashicons dashicons-trash"></span>
					<?php Option::init( [
						$opt['include'],
						$opt['include_file'],
						$opt['js_attr'],
					], $i ); ?>
                </div>

			<?php
			endfor;
		endif; ?>

        <div class="btn-add-display">
            <a class="button button-primary button-large" id="add-include"><?php esc_html_e( 'Add New' ); ?></a>
        </div>
    </fieldset>


    <template id="clone-includes">
        <div class="wowp-fields-group has-padding has-shadow has-radius">
            <span class="dashicons dashicons-trash"></span>
		    <?php Option::init( [
			    $opt['include'],
			    $opt['include_file'],
			    $opt['js_attr'],
		    ], -1 ); ?>
        </div>

    </template>

<?php
