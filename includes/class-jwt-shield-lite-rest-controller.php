<?php
/**
 * REST API Controller for JWT Shield Lite
 *
 * @since      1.0.0
 * @package    Jwt_Shield_Lite
 * @subpackage Jwt_Shield_Lite/includes
 */
class Jwt_Shield_Lite_REST_Controller extends WP_REST_Controller {

    /**
     * The namespace of this controller's route.
     *
     * @var string
     */
    protected $namespace = 'jwt-shield-lite/v1';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        // Token generation endpoint
        register_rest_route(
            $this->namespace,
            '/token',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'create_token'),
                    'permission_callback' => '__return_true',
                    'args'                => array(
                        'username' => array(
                            'required' => true,
                            'type'    => 'string',
                            'description' => 'User login name',
                        ),
                        'password' => array(
                            'required' => true,
                            'type'    => 'string',
                            'description' => 'User password',
                        ),
                    ),
                ),
            )
        );

        // Token validation endpoint
        register_rest_route(
            $this->namespace,
            '/validate',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'validate_token'),
                    'permission_callback' => '__return_true',
                ),
            )
        );
    }

    /**
     * Create JWT token
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function create_token($request) {
        // Check rate limiting first
        $rate_limit_check = $this->check_rate_limit('token');
        if (is_wp_error($rate_limit_check)) {
            return $rate_limit_check;
        }
        
        $auth = Jwt_Shield_Lite_Auth::instance();
        
        $params = array(
            'username' => sanitize_text_field($request->get_param('username')),
            'password' => $request->get_param('password'), // Don't sanitize passwords
        );

        $token = $auth->generate_token($params);

        if (is_wp_error($token)) {
            // Increment failed attempts for rate limiting
            $this->record_failed_attempt('token');
            return $token;
        }

        return rest_ensure_response($token);
    }

    /**
     * Validate JWT token
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function validate_token($request) {
        $auth = Jwt_Shield_Lite_Auth::instance();
        $validation = $auth->validate_token();

        if (is_wp_error($validation)) {
            return $validation;
        }

        return rest_ensure_response($validation);
    }

    /**
     * Check rate limiting for an endpoint
     *
     * @param string $endpoint The endpoint name
     * @return bool|WP_Error True if allowed, WP_Error if rate limited
     */
    private function check_rate_limit($endpoint) {
        $client_ip = Jwt_Shield_Lite_Helpers::get_client_ip();
        $transient_key = 'jwt_shield_lite_rate_limit_' . md5($client_ip . '_' . $endpoint);
        
        $attempts = get_transient($transient_key);
        
        // Allow 5 attempts per 15 minutes
        $max_attempts = 5;
        $lockout_duration = 15 * MINUTE_IN_SECONDS;
        
        if ($attempts !== false && $attempts >= $max_attempts) {
            return Jwt_Shield_Lite_Helpers::create_error(
                'jwt_auth_rate_limited',
                'Too many attempts. Please try again later.',
                429
            );
        }
        
        return true;
    }

    /**
     * Record a failed authentication attempt
     *
     * @param string $endpoint The endpoint name
     */
    private function record_failed_attempt($endpoint) {
        $client_ip = Jwt_Shield_Lite_Helpers::get_client_ip();
        $transient_key = 'jwt_shield_lite_rate_limit_' . md5($client_ip . '_' . $endpoint);
        
        $attempts = get_transient($transient_key);
        $attempts = ($attempts !== false) ? $attempts + 1 : 1;
        
        // Set transient for 15 minutes
        set_transient($transient_key, $attempts, 15 * MINUTE_IN_SECONDS);
    }
} 