<?php
/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 * @package    Jwt_Shield_Lite
 * @subpackage Jwt_Shield_Lite/includes
 */
class Jwt_Shield_Lite_Activator {

    /**
     * Actions to perform on plugin activation.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create database table for basic token tracking
        self::create_database_table();
        
        // Initialize options
        self::initialize_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create necessary database table
     */
    private static function create_database_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_name = $wpdb->prefix . 'jwt_shield_lite_tokens';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            token_hash varchar(64) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime NOT NULL,
            last_used_at datetime DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY token_hash (token_hash),
            KEY user_id (user_id),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Initialize plugin options
     */
    private static function initialize_options() {
        // Generate a default secret key if not exists
        if (!get_option('jwt_shield_lite_secret_key')) {
            update_option('jwt_shield_lite_secret_key', wp_generate_password(32, true, true));
        }
        
        // Set default token expiration (7 days)
        if (!get_option('jwt_shield_lite_token_expiration')) {
            update_option('jwt_shield_lite_token_expiration', 604800); // 7 days in seconds
        }
        
        // Set default algorithm
        if (!get_option('jwt_shield_lite_algorithm')) {
            update_option('jwt_shield_lite_algorithm', 'HS256');
        }
        
        // Set plugin version
        update_option('jwt_shield_lite_version', JWT_SHIELD_LITE_VERSION);
        
        // Add upgrade notice
        if (!get_option('jwt_shield_lite_hide_upgrade_notice')) {
            update_option('jwt_shield_lite_show_upgrade_notice', true);
        }
    }
} 