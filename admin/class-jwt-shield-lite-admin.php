<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Jwt_Shield_Lite
 * @subpackage Jwt_Shield_Lite/admin
 */
class Jwt_Shield_Lite_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name
     * @param    string    $version
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        if (strpos($screen->id, 'jwt-shield-lite') !== false) {
            wp_enqueue_style(
                $this->plugin_name,
                JWT_SHIELD_LITE_PLUGIN_URL . 'admin/css/jwt-shield-lite-admin.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        if (strpos($screen->id, 'jwt-shield-lite') !== false) {
            wp_enqueue_script(
                $this->plugin_name,
                JWT_SHIELD_LITE_PLUGIN_URL . 'admin/js/jwt-shield-lite-admin.js',
                array('jquery'),
                $this->version,
                false
            );
        }
    }

    /**
     * Add plugin admin menu
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('JWT Shield Lite', 'jwt-shield-lite'),
            __('JWT Shield Lite', 'jwt-shield-lite'),
            'manage_options',
            'jwt-shield-lite',
            array($this, 'display_plugin_admin_page'),
            'dashicons-shield',
            81
        );

        add_submenu_page(
            'jwt-shield-lite',
            __('Settings', 'jwt-shield-lite'),
            __('Settings', 'jwt-shield-lite'),
            'manage_options',
            'jwt-shield-lite',
            array($this, 'display_plugin_admin_page')
        );

        add_submenu_page(
            'jwt-shield-lite',
            __('Documentation', 'jwt-shield-lite'),
            __('Documentation', 'jwt-shield-lite'),
            'manage_options',
            'jwt-shield-lite-docs',
            array($this, 'display_documentation_page')
        );

        add_submenu_page(
            'jwt-shield-lite',
            __('Upgrade to Pro', 'jwt-shield-lite'),
            '<span style="color:#ff9900;">Upgrade to Pro</span>',
            'manage_options',
            'jwt-shield-lite-upgrade',
            array($this, 'display_upgrade_page')
        );
    }

    /**
     * Display the plugin admin page
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once JWT_SHIELD_LITE_PLUGIN_DIR . 'admin/partials/jwt-shield-lite-admin-display.php';
    }

    /**
     * Display the documentation page
     *
     * @since    1.0.0
     */
    public function display_documentation_page() {
        include_once JWT_SHIELD_LITE_PLUGIN_DIR . 'admin/partials/jwt-shield-lite-docs-display.php';
    }

    /**
     * Display the upgrade page
     *
     * @since    1.0.0
     */
    public function display_upgrade_page() {
        include_once JWT_SHIELD_LITE_PLUGIN_DIR . 'admin/partials/jwt-shield-lite-upgrade-display.php';
    }

    /**
     * Register plugin settings
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting('jwt_shield_lite_settings', 'jwt_shield_lite_secret_key');
        register_setting('jwt_shield_lite_settings', 'jwt_shield_lite_token_expiration');
        register_setting('jwt_shield_lite_settings', 'jwt_shield_lite_algorithm');
        register_setting('jwt_shield_lite_settings', 'jwt_shield_lite_hide_upgrade_notice');
    }
} 