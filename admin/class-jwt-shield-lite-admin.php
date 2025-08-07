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

        // Only show upgrade menu if Pro advertising is enabled
        if (Jwt_Shield_Lite_Helpers::pro_ads_enabled()) {
            add_submenu_page(
                'jwt-shield-lite',
                __('Upgrade to Pro', 'jwt-shield-lite'),
                '<span style="color:#ff9900;">Upgrade to Pro</span>',
                'manage_options',
                'jwt-shield-lite-upgrade',
                array($this, 'display_upgrade_page')
            );
        }
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
        // If Pro ads are disabled, redirect to main settings page
        if (!Jwt_Shield_Lite_Helpers::pro_ads_enabled()) {
            wp_redirect(admin_url('admin.php?page=jwt-shield-lite'));
            exit;
        }
        
        include_once JWT_SHIELD_LITE_PLUGIN_DIR . 'admin/partials/jwt-shield-lite-upgrade-display.php';
    }

    /**
     * Register plugin settings
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting('jwt_shield_lite_settings', 'jwt_shield_lite_secret_key', array(
            'sanitize_callback' => array($this, 'sanitize_secret_key')
        ));
        register_setting('jwt_shield_lite_settings', 'jwt_shield_lite_token_expiration', array(
            'sanitize_callback' => array($this, 'sanitize_token_expiration')
        ));
        register_setting('jwt_shield_lite_settings', 'jwt_shield_lite_algorithm', array(
            'sanitize_callback' => array($this, 'sanitize_algorithm')
        ));
        register_setting('jwt_shield_lite_settings', 'jwt_shield_lite_hide_upgrade_notice', array(
            'sanitize_callback' => 'absint'
        ));
    }

    /**
     * Sanitize secret key
     *
     * @param string $key The secret key
     * @return string Sanitized key
     */
    public function sanitize_secret_key($key) {
        // Remove any potentially dangerous characters
        $key = sanitize_text_field($key);
        
        // Ensure minimum length for security
        if (strlen($key) < 32) {
            add_settings_error(
                'jwt_shield_lite_secret_key',
                'key_too_short',
                'Secret key must be at least 32 characters long.',
                'error'
            );
            return get_option('jwt_shield_lite_secret_key');
        }
        
        // Ensure maximum length to prevent DoS
        if (strlen($key) > 256) {
            add_settings_error(
                'jwt_shield_lite_secret_key',
                'key_too_long',
                'Secret key cannot exceed 256 characters.',
                'error'
            );
            return get_option('jwt_shield_lite_secret_key');
        }
        
        return $key;
    }

    /**
     * Sanitize token expiration
     *
     * @param mixed $expiration The expiration value
     * @return int Sanitized expiration
     */
    public function sanitize_token_expiration($expiration) {
        $expiration = absint($expiration);
        
        // Minimum 1 hour, maximum 1 year
        $min_expiration = HOUR_IN_SECONDS;
        $max_expiration = YEAR_IN_SECONDS;
        
        if ($expiration < $min_expiration) {
            add_settings_error(
                'jwt_shield_lite_token_expiration',
                'expiration_too_short',
                'Token expiration must be at least 1 hour.',
                'error'
            );
            return $min_expiration;
        }
        
        if ($expiration > $max_expiration) {
            add_settings_error(
                'jwt_shield_lite_token_expiration',
                'expiration_too_long',
                'Token expiration cannot exceed 1 year.',
                'error'
            );
            return $max_expiration;
        }
        
        return $expiration;
    }

    /**
     * Sanitize algorithm
     *
     * @param string $algorithm The algorithm
     * @return string Sanitized algorithm
     */
    public function sanitize_algorithm($algorithm) {
        // Only allow HS256 in Lite version
        $allowed_algorithms = array('HS256');
        
        if (!in_array($algorithm, $allowed_algorithms)) {
            add_settings_error(
                'jwt_shield_lite_algorithm',
                'invalid_algorithm',
                'Invalid algorithm selected.',
                'error'
            );
            return 'HS256';
        }
        
        return $algorithm;
    }

    /**
     * AJAX handler to generate a secure secret key
     *
     * @since    1.0.0
     */
    public function ajax_generate_secret_key() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'jwt_shield_lite_generate_key')) {
            wp_die('Security check failed');
        }

        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // Generate cryptographically secure key (64 bytes)
        $secure_key = wp_generate_password(64, true, true);
        
        // Return the key
        wp_send_json_success(array(
            'key' => $secure_key
        ));
    }

    /**
     * AJAX handler to dismiss upgrade notice
     *
     * @since    1.0.0
     */
    public function ajax_dismiss_upgrade_notice() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'jwt_shield_lite_dismiss')) {
            wp_die('Security check failed');
        }

        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // Update option to hide upgrade notice
        update_option('jwt_shield_lite_hide_upgrade_notice', true);
        
        wp_send_json_success();
    }
} 