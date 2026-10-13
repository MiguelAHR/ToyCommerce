<?php
/**
 * @link     https://www.letsgodev.com/
 * @since    1.0.0
 * @package  culqi-full-integration
 * 
 * Plugin Name:          Culqi Full Integration 
 * Plugin URI:           https://wordpress.com/plugins/culqi-full-integration
 * Description:          Culqi is a Payment method to Peru.
 * Version:              3.0.5
 * Author:               Lets Go Dev
 * Author URI:           https://www.letsgodev.com/
 * Developer:            Alexander Gonzales
 * Developer URI:        https://vcard.gonzalesc.org/
 * License:              GPL-3.0+
 * License URI:          https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:          fullculqi
 * Domain Path:          /languages
 * WP stable tag:        7.1.0
 * WP requires at least: 7.1.0
 * WP tested up to:      7.1.2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define( 'FULLCULQI_FILE' , __FILE__ );
define( 'FULLCULQI_DIR' , plugin_dir_path(__FILE__) );
define( 'FULLCULQI_URL' , plugin_dir_url(__FILE__) );
define( 'FULLCULQI_BASE' , plugin_basename( __FILE__ ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require_once FULLCULQI_DIR . 'includes/class-fullculqi.php';


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fullculqi-activator.php
 */
function fullculqi_activate() {
	require_once FULLCULQI_DIR . 'includes/class-fullculqi-activator.php';
	fullculqi_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fullculqi-deactivator.php
 */
//function culqi_deactivate() {
//	require_once FULLCULQI_DIR . 'includes/class-fullculqi-deactivator.php';
//	fullculqi_Deactivator::deactivate();
//}


register_activation_hook( __FILE__, 'fullculqi_activate' );
//register_deactivation_hook( __FILE__, 'fullculqi_deactivate' );

/**
 * Store the plugin global
 */
global $fullculqi;

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */

function fullculqi() {
	return FullCulqi::instance();
}

$fullculqi = fullculqi();
?>