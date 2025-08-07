<?php
/**
 * JWT Shield - Basic JWT Authentication for WordPress
 *
 * @link              https://waqasriaz.com
 * @since             1.0.0
 * @package           Jwt_Shield_Lite
 *
 * @wordpress-plugin
 * Plugin Name:       JWT Shield - Basic Authentication
 * Plugin URI:        https://jwt-shield.com
 * Description:       Basic JWT authentication for WordPress REST API. Upgrade to Pro for advanced features like refresh tokens, analytics, and IP management.
 * Version:           1.0.0
 * Author:            Waqas Riaz
 * Author URI:        https://waqasriaz.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jwt-shield-lite
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('JWT_SHIELD_LITE_VERSION', '1.0.0');
define('JWT_SHIELD_LITE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JWT_SHIELD_LITE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JWT_SHIELD_LITE_DISABLE_PRO_ADS', true);

// Load Composer autoloader if exists
if (file_exists(JWT_SHIELD_LITE_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once JWT_SHIELD_LITE_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * The code that runs during plugin activation.
 */
function activate_jwt_shield_lite() {
    require_once JWT_SHIELD_LITE_PLUGIN_DIR . 'includes/class-jwt-shield-lite-activator.php';
    Jwt_Shield_Lite_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_jwt_shield_lite() {
    require_once JWT_SHIELD_LITE_PLUGIN_DIR . 'includes/class-jwt-shield-lite-deactivator.php';
    Jwt_Shield_Lite_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_jwt_shield_lite');
register_deactivation_hook(__FILE__, 'deactivate_jwt_shield_lite');

/**
 * The core plugin class
 */
require JWT_SHIELD_LITE_PLUGIN_DIR . 'includes/class-jwt-shield-lite.php';

/**
 * Begins execution of the plugin.
 */
function run_jwt_shield_lite() {
    // Include plugin.php to use is_plugin_active()
    if (!function_exists('is_plugin_active')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    
    // Check if Pro version is active
    if (is_plugin_active('jwt-shield/jwt-shield.php')) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e('JWT Shield Pro is already active. Please deactivate JWT Shield Lite to avoid conflicts.', 'jwt-shield-lite'); ?></p>
            </div>
            <?php
        });
        return;
    }

    $plugin = new Jwt_Shield_Lite();
    $plugin->run();
}
run_jwt_shield_lite(); 