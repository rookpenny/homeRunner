<?php
/**
 * Plugin Name: Hostaway WordPress Integration
 * Plugin URI: https://github.com/rookpenny/homeRunner
 * Description: Connect your Hostaway listings to WordPress with full control over look and feel
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://github.com/rookpenny
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: hostaway-integration
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Plugin version
define( 'HOSTAWAY_INTEGRATION_VERSION', '1.0.0' );
define( 'HOSTAWAY_INTEGRATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HOSTAWAY_INTEGRATION_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_hostaway_integration() {
    require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'includes/class-hostaway-activator.php';
    Hostaway_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_hostaway_integration() {
    require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'includes/class-hostaway-deactivator.php';
    Hostaway_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_hostaway_integration' );
register_deactivation_hook( __FILE__, 'deactivate_hostaway_integration' );

/**
 * The core plugin class
 */
require HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'includes/class-hostaway-integration.php';

/**
 * Begins execution of the plugin.
 */
function run_hostaway_integration() {
    $plugin = new Hostaway_Integration();
    $plugin->run();
}
run_hostaway_integration();
