<?php
/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 * @package    Jwt_Shield_Lite
 * @subpackage Jwt_Shield_Lite/includes
 */
class Jwt_Shield_Lite_Deactivator {

    /**
     * Actions to perform on plugin deactivation.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear any scheduled tasks
        if (wp_next_scheduled('jwt_shield_lite_cleanup_tokens')) {
            wp_clear_scheduled_hook('jwt_shield_lite_cleanup_tokens');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
} 