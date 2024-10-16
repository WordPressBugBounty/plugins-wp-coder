<?php
/**
 * Page Name: PRO Features
 *
 */

use WPCoder\Dashboard\DashboardInitializer;

defined( 'ABSPATH' ) || exit;

$features = [
     'templates' => [
	     __( 'Templates', 'wp-coder' ),
         __('Design custom templates for pages, posts, categories, and more, infusing your unique code for brand-aligned layouts.', 'wp-coder')
     ],

    'dequeue' => [
	    __( 'Dequeue CSS and JS', 'wp-coder' ),
	    __('Increase site speed by adding only selected styles/scripts and removing redundant resources from the queue to optimize user experience.', 'wp-coder')
    ],

    'logic' => [
	    __( 'Conditional Logic', 'wp-coder' ),
	    __('Utilize our Conditional Logic rule to dictate where your code will work: Pages Rules, Devices Rules, Scheduled, Browsers Rules, Users Rules, Language Rules', 'wp-coder')
    ],

     'tools' => [
	     __( 'Uses extra Tools', 'wp-coder' ),
	     __('Enable Style and Script on the Login Page, Enable Maintenance Mode, Enable Extra Icon Enable Breadcrumbs', 'wp-coder')
     ],

     'attributes' => [
	     __( 'Shortcode Attributes', 'wp-coder' ),
	     __('Add custom attributes for shortcodes. Embed these attributes into your HTML code for tailored content display.', 'wp-coder')
     ],

];

?>

    <div class="wowp-header-wrapper">
		<?php DashboardInitializer::header(); ?>

        <div class="wowp-header-title">
            <h2><?php esc_html_e( 'PRO Features', 'wp-coder' ); ?></h2>
        </div>

    </div>

    <div class="wrap wowp-wrap">
    <div class="w_block w_has-border">

        <div class="w_block-titles">
            <div class="w_block-subtitle"><?php esc_html_e('what you get', 'wp-coder');?></div>
            <h3 class="w_block-title"><?php esc_html_e('PRO Features', 'wp-coder');?></h3>
        </div>

        <div class="w_block-btns">
            <a href="https://wpcoder.pro" class="w_btn-pro" target="_blank"><?php esc_html_e('Get More with Pro', 'wp-coder');?></a>
            <a href="https://wpcoder.pro/category/documentation/" target="_blank" class="w_btn-demo"><?php esc_html_e('Docs', 'wp-coder');?></a>
        </div>

        <div class="w_boxes__3-col">

			<?php foreach ( $features as $icon => $text ): ?>
                <div class="w_box">
                    <div class="w_card">
                        <div class="w_card-content">
                            <h5 class="w_card-title"><?php echo esc_html( $text[0] ); ?></h5>
                            <p class="w_card-description">
								<?php echo esc_html( $text[1] ); ?>
                            </p>
                        </div>
                    </div>

                </div>

			<?php endforeach; ?>

        </div>


    </div>
    </div>


<?php
