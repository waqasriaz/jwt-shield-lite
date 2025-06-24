<?php
/**
 * Helper functions for JWT Shield Lite
 *
 * @since      1.0.0
 * @package    Jwt_Shield_Lite
 * @subpackage Jwt_Shield_Lite/includes
 */
class Jwt_Shield_Lite_Helpers {

    /**
     * Get the authorization header
     *
     * @return string|false
     */
    public static function get_auth_header() {
        $auth_header = false;

        // Check various possible locations for the authorization header
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $auth_header = $headers['Authorization'];
            } elseif (isset($headers['authorization'])) {
                $auth_header = $headers['authorization'];
            }
        }

        return $auth_header;
    }

    /**
     * Extract token from authorization header
     *
     * @param string $auth_header
     * @return string|false
     */
    public static function get_token_from_header($auth_header) {
        if (preg_match('/Bearer\s+(.+)/', $auth_header, $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    public static function get_client_ip() {
        $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    /**
     * Check if the current request is a REST API request
     *
     * @return bool
     */
    public static function is_rest_request() {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $rest_prefix = trailingslashit(rest_get_url_prefix());
        $request_uri = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']));
        
        return (false !== strpos($request_uri, $rest_prefix));
    }

    /**
     * Create error response
     *
     * @param string $code Error code
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return WP_Error
     */
    public static function create_error($code, $message = '', $status = 401) {
        $message = empty($message) ? self::get_error_message($code) : $message;
        
        return new WP_Error(
            $code,
            $message,
            array('status' => $status)
        );
    }

    /**
     * Get default error messages
     *
     * @param string $code
     * @return string
     */
    private static function get_error_message($code) {
        $messages = array(
            'jwt_auth_no_auth_header' => __('Authorization header not found.', 'jwt-shield-lite'),
            'jwt_auth_bad_auth_header' => __('Authorization header malformed.', 'jwt-shield-lite'),
            'jwt_auth_bad_token' => __('Invalid token.', 'jwt-shield-lite'),
            'jwt_auth_expired_token' => __('Token has expired.', 'jwt-shield-lite'),
            'jwt_auth_invalid_token' => __('Token signature verification failed.', 'jwt-shield-lite'),
            'jwt_auth_user_not_found' => __('User not found.', 'jwt-shield-lite'),
            'jwt_auth_invalid_credentials' => __('Invalid username or password.', 'jwt-shield-lite'),
            'jwt_auth_error' => __('Authentication error.', 'jwt-shield-lite'),
        );

        return isset($messages[$code]) ? $messages[$code] : __('Unknown error.', 'jwt-shield-lite');
    }

    /**
     * Hash a token for storage
     *
     * @param string $token
     * @return string
     */
    public static function hash_token($token) {
        return hash('sha256', $token);
    }
} 