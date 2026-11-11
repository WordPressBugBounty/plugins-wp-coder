<?php
/**
 * Site Tools and the AI Assistant exist only in WP Coder Pro; this tab
 * describes them. It has no inputs, so its Save button is hidden.
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wowp-snippets__header">
	<h3 class="wowp-snippets__header-title">Site Tools <span class="wowp-pro__badge">PRO</span></h3>
	<p class="wowp-snippets__header-description">Site-wide features that come with WP Coder Pro.
		<a href="<?php echo esc_url( WPCoder\WPCoder::PRO_URL ); ?>" target="_blank" rel="noopener">Learn more</a></p>
</div>

<?php
$wpcoder_pro_tools = [
	[ __( 'QuickCode', 'wp-coder' ), __( 'Placeholders such as {{post.title}}, {{user.name}}, {{site.name}}, {{product.price}} or {{date.now}} that resolve to live site data inside any snippet.', 'wp-coder' ) ],
	[ __( 'Loop Logic', 'wp-coder' ), __( 'Conditional and repeating blocks in snippet HTML — <If>, <ElseIf>, <Else> and <Loop> — driven by attribute values.', 'wp-coder' ) ],
	[ __( 'Maintenance Mode', 'wp-coder' ), __( 'Show a customizable maintenance page to visitors while logged-in administrators keep seeing the site as usual.', 'wp-coder' ) ],
	[ __( 'Login Page Style and Script', 'wp-coder' ), __( 'Add your own CSS and JavaScript to the WordPress login screen.', 'wp-coder' ) ],
	[ __( 'Breadcrumbs', 'wp-coder' ), __( 'Output breadcrumbs anywhere in a template with wpc_get_breadcrumbs().', 'wp-coder' ) ],
	[ __( 'Extra Icon', 'wp-coder' ), __( 'Attach an icon to categories, tags, pages and posts.', 'wp-coder' ) ],
	[ __( 'WP Coder in the Admin Bar', 'wp-coder' ), __( 'Quick links to All Codes, Add New, Snippets, Tools and Global PHP from the front end and the dashboard.', 'wp-coder' ) ],
	[ __( 'AI Assistant', 'wp-coder' ), __( 'Connect an AI provider to generate snippet code and to write or translate content right in the block editor.', 'wp-coder' ) ],
];
$wpcoder_pro_tools_heading = false;
require WPCoder\WPCoder::dir() . 'includes/pages/pro-tools.php';
?>
<span class="wowp-pro"></span>
