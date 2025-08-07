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
     * Get client IP address with proper validation and security checks
     *
     * @return string
     */
    public static function get_client_ip() {
        // Prioritize headers in order of trust
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',    // Cloudflare (most trusted proxy)
            'HTTP_X_REAL_IP',           // Nginx proxy
            'HTTP_X_FORWARDED_FOR',     // Load balancers/proxies
            'HTTP_CLIENT_IP',           // Proxy servers
            'REMOTE_ADDR'               // Direct connection (most reliable)
        );
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                // Handle comma-separated IP lists
                $ips = explode(',', $_SERVER[$key]);
                
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    
                    // Validate IP format
                    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                        continue;
                    }
                    
                    // Skip private and reserved ranges for external headers
                    if ($key !== 'REMOTE_ADDR') {
                        if (filter_var($ip, FILTER_VALIDATE_IP, 
                            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                            continue;
                        }
                    }
                    
                    // Additional security: check for suspicious patterns
                    if (self::is_suspicious_ip($ip)) {
                        continue;
                    }
                    
                    return $ip;
                }
            }
        }
        
        // Fallback with validation
        $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return filter_var($remote_addr, FILTER_VALIDATE_IP) ? $remote_addr : '0.0.0.0';
    }

    /**
     * Check if an IP address is suspicious
     *
     * @param string $ip The IP address to check
     * @return bool True if suspicious, false otherwise
     */
    private static function is_suspicious_ip($ip) {
        // Check for obviously fake IPs
        $suspicious_patterns = array(
            '127.0.0.1',        // Localhost
            '0.0.0.0',          // Invalid
            '255.255.255.255',  // Broadcast
        );
        
        if (in_array($ip, $suspicious_patterns)) {
            return true;
        }
        
        // Check for private ranges that shouldn't be in forwarded headers
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long_ip = ip2long($ip);
            if ($long_ip !== false) {
                // 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16
                if (($long_ip >= ip2long('10.0.0.0') && $long_ip <= ip2long('10.255.255.255')) ||
                    ($long_ip >= ip2long('172.16.0.0') && $long_ip <= ip2long('172.31.255.255')) ||
                    ($long_ip >= ip2long('192.168.0.0') && $long_ip <= ip2long('192.168.255.255'))) {
                    return true;
                }
            }
        }
        
        return false;
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
            'jwt_auth_rate_limited' => __('Too many authentication attempts. Please try again later.', 'jwt-shield-lite'),
        );

        return isset($messages[$code]) ? $messages[$code] : __('Unknown error.', 'jwt-shield-lite');
    }

    /**
     * Hash a token for storage using WordPress's secure hash function
     *
     * @param string $token
     * @return string
     */
    public static function hash_token($token) {
        // Use WordPress's wp_hash() which includes salts and is more secure
        return wp_hash($token);
    }

    /**
     * Constant-time string comparison to prevent timing attacks
     *
     * @param string $known The known string
     * @param string $user The user-provided string
     * @return bool True if strings match, false otherwise
     */
    public static function hash_equals_safe($known, $user) {
        // Use PHP's hash_equals if available (PHP 5.6+)
        if (function_exists('hash_equals')) {
            return hash_equals($known, $user);
        }
        
        // Fallback implementation for older PHP versions
        if (strlen($known) !== strlen($user)) {
            return false;
        }
        
        $result = 0;
        for ($i = 0; $i < strlen($known); $i++) {
            $result |= ord($known[$i]) ^ ord($user[$i]);
        }
        
        return $result === 0;
    }

    /**
     * Check if Pro advertising should be shown
     * 
     * @return bool True if Pro ads should be shown, false otherwise
     */
    public static function pro_ads_enabled() {
        // Check if Pro advertising is explicitly disabled
        if (defined('JWT_SHIELD_LITE_DISABLE_PRO_ADS') && JWT_SHIELD_LITE_DISABLE_PRO_ADS === true) {
            return false;
        }
        
        // Default: show Pro advertising
        return true;
    }
} 