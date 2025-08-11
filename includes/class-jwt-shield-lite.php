<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Jwt_Shield_Lite
 * @subpackage Jwt_Shield_Lite/includes
 */
class Jwt_Shield_Lite {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Jwt_Shield_Lite_Loader    $loader
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->version = JWT_SHIELD_LITE_VERSION;
        $this->plugin_name = 'jwt-shield-lite';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_rest_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // Core classes
        require_once JWT_SHIELD_LITE_PLUGIN_DIR . 'includes/class-jwt-shield-lite-loader.php';
        require_once JWT_SHIELD_LITE_PLUGIN_DIR . 'includes/class-jwt-shield-lite-i18n.php';
        require_once JWT_SHIELD_LITE_PLUGIN_DIR . 'includes/class-jwt-shield-lite-helpers.php';
        require_once JWT_SHIELD_LITE_PLUGIN_DIR . 'includes/class-jwt-shield-lite-auth.php';
        require_once JWT_SHIELD_LITE_PLUGIN_DIR . 'includes/class-jwt-shield-lite-rest-controller.php';

        // Admin classes
        require_once JWT_SHIELD_LITE_PLUGIN_DIR . 'admin/class-jwt-shield-lite-admin.php';

        $this->loader = new Jwt_Shield_Lite_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Jwt_Shield_Lite_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Jwt_Shield_Lite_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        
        // AJAX handlers
        $this->loader->add_action('wp_ajax_jwt_shield_lite_generate_key', $plugin_admin, 'ajax_generate_secret_key');
        $this->loader->add_action('wp_ajax_jwt_shield_lite_dismiss_upgrade', $plugin_admin, 'ajax_dismiss_upgrade_notice');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        // No public hooks needed for JWT authentication
    }

    /**
     * Register REST API hooks
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_rest_hooks() {
        $rest_controller = new Jwt_Shield_Lite_REST_Controller();
        $auth = Jwt_Shield_Lite_Auth::instance();

        // Register REST routes
        $this->loader->add_action('rest_api_init', $rest_controller, 'register_routes');
        
        // Authentication hooks
        $this->loader->add_filter('determine_current_user', $auth, 'authenticate_user');
        $this->loader->add_filter('rest_pre_dispatch', $auth, 'show_jwt_error', 10, 3);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
} 