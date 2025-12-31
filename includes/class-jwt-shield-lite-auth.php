<?php
/**
 * Basic JWT Authentication functionality
 *
 * @since      1.0.0
 * @package    Jwt_Shield_Lite
 * @subpackage Jwt_Shield_Lite/includes
 */

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Jwt_Shield_Lite_Auth {

    /**
     * The single instance of the class
     *
     * @var Jwt_Shield_Lite_Auth
     */
    private static $instance = null;

    /**
     * JWT error to display
     *
     * @var WP_Error|null
     */
    private $jwt_shield_error = null;

    /**
     * Main instance
     *
     * @return Jwt_Shield_Lite_Auth
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Generate JWT token
     *
     * @param array $request Request data with username and password
     * @return array|WP_Error
     */
    public function generate_token($request) {
        // Sanitize and validate inputs
        $username = isset($request['username']) ? sanitize_user($request['username']) : '';
        $password = isset($request['password']) ? $request['password'] : ''; // Don't sanitize passwords
        
        // Additional validation
        if (empty($username) || empty($password)) {
            return Jwt_Shield_Lite_Helpers::create_error(
                'jwt_auth_empty_credentials',
                'Username and password are required.'
            );
        }
        
        // Check for reasonable username length (prevent DoS)
        if (strlen($username) > 60) {
            return Jwt_Shield_Lite_Helpers::create_error(
                'jwt_auth_invalid_credentials',
                'Invalid username or password.'
            );
        }
        
        // Check for reasonable password length (prevent DoS)
        if (strlen($password) > 4096) {
            return Jwt_Shield_Lite_Helpers::create_error(
                'jwt_auth_invalid_credentials',
                'Invalid username or password.'
            );
        }

        // Authenticate user
        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            return Jwt_Shield_Lite_Helpers::create_error(
                'jwt_auth_invalid_credentials',
                'Invalid username or password.'
            );
        }

        // Get settings
        $secret_key = get_option('jwt_shield_lite_secret_key');
        $algorithm = get_option('jwt_shield_lite_algorithm', 'HS256');
        $expiration = get_option('jwt_shield_lite_token_expiration', 604800);

        if (!$secret_key) {
            return Jwt_Shield_Lite_Helpers::create_error(
                'jwt_auth_bad_config',
                'JWT is not configured properly.'
            );
        }

        // Create token
        $issued_at = time();
        $expire = $issued_at + $expiration;

        $token_data = array(
            'iss' => site_url(),
            'iat' => $issued_at,
            'nbf' => $issued_at,
            'exp' => $expire,
            'data' => array(
                'user' => array(
                    'id' => $user->ID,
                    'email' => $user->user_email,
                    'roles' => $user->roles,
                )
            )
        );

        try {
            $jwt = JWT::encode($token_data, $secret_key, $algorithm);

            // Store token in database
            $this->store_token($user->ID, $jwt, $expire);

            // Build response
            $response = array(
                'success' => true,
                'data'    => array(
                    'token'             => $jwt,
                    'user_id'           => $user->ID,
                    'user_email'        => $user->user_email,
                    'user_nicename'     => $user->user_nicename,
                    'user_display_name' => $user->display_name,
                    'expires_at'        => $expire,
                ),
            );

            // Add Pro notice separately (not mixed with data)
            if (Jwt_Shield_Lite_Helpers::pro_ads_enabled()) {
                $response['notice'] = sprintf(
                    __('Token expires in %s. Upgrade to Pro for refresh tokens and advanced features!', 'jwt-shield-lite'),
                    human_time_diff(time(), $expire)
                );
            }

            return $response;

        } catch (Exception $e) {
            return Jwt_Shield_Lite_Helpers::create_error(
                'jwt_auth_error',
                __('Token generation failed. Please check your configuration.', 'jwt-shield-lite')
            );
        }
    }

    /**
     * Validate JWT token
     *
     * @return array|WP_Error
     */
    public function validate_token() {
        $auth_header = Jwt_Shield_Lite_Helpers::get_auth_header();

        if (!$auth_header) {
            return Jwt_Shield_Lite_Helpers::create_error('jwt_auth_no_auth_header');
        }

        $token = Jwt_Shield_Lite_Helpers::get_token_from_header($auth_header);
        
        if (!$token) {
            return Jwt_Shield_Lite_Helpers::create_error('jwt_auth_bad_auth_header');
        }

        $decoded = $this->validate_token_internal($token);

        if (is_wp_error($decoded)) {
            return $decoded;
        }

        // Format the response for the REST API (consistent with token endpoint)
        return array(
            'success' => true,
            'data'    => array(
                'user_id' => $decoded->data->user->id,
                'email'   => $decoded->data->user->email,
                'roles'   => $decoded->data->user->roles,
            ),
        );
    }

    /**
     * Internal token validation
     *
     * @param string $token
     * @return array|WP_Error
     */
    private function validate_token_internal($token) {
        $secret_key = get_option('jwt_shield_lite_secret_key');
        $algorithm = get_option('jwt_shield_lite_algorithm', 'HS256');

        if (!$secret_key) {
            return Jwt_Shield_Lite_Helpers::create_error('jwt_auth_bad_config');
        }

        try {
            $decoded = JWT::decode($token, new Key($secret_key, $algorithm));

            // Validate issuer using constant-time comparison
            $expected_issuer = site_url();
            if (!Jwt_Shield_Lite_Helpers::hash_equals_safe($expected_issuer, $decoded->iss)) {
                return Jwt_Shield_Lite_Helpers::create_error('jwt_auth_bad_token');
            }

            // Validate user ID exists in token
            if (!isset($decoded->data->user->id)) {
                return Jwt_Shield_Lite_Helpers::create_error('jwt_auth_bad_token');
            }

            // Update last used
            $this->update_token_last_used($decoded->data->user->id);

            // Return the decoded token object directly for internal use
            return $decoded;

        } catch (Exception $e) {
            return Jwt_Shield_Lite_Helpers::create_error(
                'jwt_auth_invalid_token',
                'Invalid or expired token.'
            );
        }
    }

    /**
     * Authenticate user from token
     *
     * @param int|bool $user_id
     * @return int|bool
     */
    public function authenticate_user($user_id) {
        // Safety check: Never process or return WP_Error objects
        if (is_wp_error($user_id)) {
            return 0;
        }
        
        // Skip if not REST request or user already authenticated
        if (!Jwt_Shield_Lite_Helpers::is_rest_request() || $user_id) {
            return $user_id;
        }

        // Skip for our own endpoints
        if ($this->is_whitelisted_endpoint()) {
            return $user_id;
        }

        $auth_header = Jwt_Shield_Lite_Helpers::get_auth_header();
        if (!$auth_header) {
            return $user_id;
        }

        // Check if the authorization header starts with 'Bearer'
        if (strpos($auth_header, 'Bearer') !== 0) {
            return $user_id;
        }

        $token = Jwt_Shield_Lite_Helpers::get_token_from_header($auth_header);
        if (!$token) {
            return $user_id;
        }

        $decoded = $this->validate_token_internal($token);

        if (is_wp_error($decoded)) {
            if ($decoded->get_error_code() != 'jwt_auth_no_auth_header') {
                /** If there is an error, store it to show it after see rest_pre_dispatch */
                $this->jwt_shield_error = $decoded;
            }
            return $user_id;
        }

        // Return user ID from token (decoded is now an object)
        return $decoded->data->user->id;
    }

    /**
     * Display JWT validation errors
     *
     * @param mixed $result
     * @param WP_REST_Server $server
     * @param WP_REST_Request $request
     * @return mixed|WP_REST_Response
     */
    public function show_jwt_error($result, $server, $request) {
        if (is_wp_error($this->jwt_shield_error)) {
            $error_data = $this->jwt_shield_error->get_error_data();
            $status = isset($error_data['status']) ? $error_data['status'] : 401;
            
            return new WP_REST_Response([
                'success'    => false,
                'code'       => $this->jwt_shield_error->get_error_code(),
                'message'    => $this->jwt_shield_error->get_error_message(),
                'data'       => [
                    'status' => $status
                ]
            ], $status);
        }
        return $result;
    }

    /**
     * Check if current endpoint is whitelisted
     *
     * @return bool
     */
    private function is_whitelisted_endpoint() {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $current_url = $_SERVER['REQUEST_URI'];
        $whitelisted = array(
            '/wp-json/jwt-shield-lite/v1/token',
            '/wp-json/jwt-shield-lite/v1/validate',
        );

        foreach ($whitelisted as $endpoint) {
            if (strpos($current_url, $endpoint) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Store token in database
     *
     * @param int $user_id
     * @param string $token
     * @param int $expires_at
     */
    private function store_token($user_id, $token, $expires_at) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jwt_shield_lite_tokens';
        $token_hash = Jwt_Shield_Lite_Helpers::hash_token($token);
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'token_hash' => $token_hash,
                'expires_at' => date('Y-m-d H:i:s', $expires_at),
                'ip_address' => Jwt_Shield_Lite_Helpers::get_client_ip(),
            ),
            array('%d', '%s', '%s', '%s')
        );

        // Clean up old tokens
        $this->cleanup_expired_tokens($user_id);
    }

    /**
     * Update token last used time
     *
     * @param int $user_id
     */
    private function update_token_last_used($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jwt_shield_lite_tokens';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET last_used_at = %s WHERE user_id = %d ORDER BY created_at DESC LIMIT 1",
            current_time('mysql'),
            $user_id
        ));
    }

    /**
     * Cleanup expired tokens
     *
     * @param int $user_id
     */
    private function cleanup_expired_tokens($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'jwt_shield_lite_tokens';
        
        // Keep only last 5 tokens per user
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name 
            WHERE user_id = %d 
            AND id NOT IN (
                SELECT id FROM (
                    SELECT id FROM $table_name 
                    WHERE user_id = %d 
                    ORDER BY created_at DESC 
                    LIMIT 5
                ) as t
            )",
            $user_id,
            $user_id
        ));

        // Delete expired tokens
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE expires_at < %s",
            current_time('mysql')
        ));
    }
} 