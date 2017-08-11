<?php
/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://jba-development.fr
 * @since             1.0.0
 * @package           Facebook_Sharing
 *
 * Plugin Name:       Facebook Sharing
 * Plugin URI:        http://jba-development.fr
 * Description:       A plugin which permit to share all your posts on Facebook timeline or page.
 * Version:           1.0.10
 * Author:            Jean-Baptiste Aramendy
 * Author URI:        http://jba-development.fr/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fb-sharing
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'FS_FB_GRAPH_VERSION', 'v2.6' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-facebook-sharing-activator.php
 */
function activate_facebook_sharing() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-facebook-sharing-activator.php';
	Facebook_Sharing_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-facebook-sharing-deactivator.php
 */
function deactivate_facebook_sharing() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-facebook-sharing-deactivator.php';
	Facebook_Sharing_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_facebook_sharing' );
register_deactivation_hook( __FILE__, 'deactivate_facebook_sharing' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-facebook-sharing.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_facebook_sharing() {
	$plugin = new Facebook_Sharing();
	$plugin->run();
}
run_facebook_sharing();