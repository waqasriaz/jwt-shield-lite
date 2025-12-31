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
     * Maximum authentication attempts before rate limiting.
     *
     * @var int
     */
    const MAX_ATTEMPTS = 5;

    /**
     * Rate limit lockout duration in seconds (15 minutes).
     *
     * @var int
     */
    const LOCKOUT_DURATION = 15 * MINUTE_IN_SECONDS;

    /**
     * Maximum username length.
     *
     * @var int
     */
    const MAX_USERNAME_LENGTH = 60;

    /**
     * Maximum password length.
     *
     * @var int
     */
    const MAX_PASSWORD_LENGTH = 4096;

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
                            'required'          => true,
                            'type'              => 'string',
                            'description'       => 'User login name',
                            'validate_callback' => array($this, 'validate_username'),
                        ),
                        'password' => array(
                            'required'          => true,
                            'type'              => 'string',
                            'description'       => 'User password',
                            'validate_callback' => array($this, 'validate_password'),
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
     * Validate username parameter
     *
     * @param mixed $value The parameter value.
     * @param WP_REST_Request $request The request object.
     * @param string $param The parameter name.
     * @return bool|WP_Error True if valid, WP_Error if invalid.
     */
    public function validate_username($value, $request, $param) {
        if (!is_string($value) || empty($value)) {
            return new WP_Error(
                'jwt_auth_invalid_param',
                __('Username is required.', 'jwt-shield-lite'),
                array('status' => 400)
            );
        }

        if (strlen($value) > self::MAX_USERNAME_LENGTH) {
            return new WP_Error(
                'jwt_auth_invalid_param',
                __('Invalid username or password.', 'jwt-shield-lite'),
                array('status' => 400)
            );
        }

        return true;
    }

    /**
     * Validate password parameter
     *
     * @param mixed $value The parameter value.
     * @param WP_REST_Request $request The request object.
     * @param string $param The parameter name.
     * @return bool|WP_Error True if valid, WP_Error if invalid.
     */
    public function validate_password($value, $request, $param) {
        if (!is_string($value) || empty($value)) {
            return new WP_Error(
                'jwt_auth_invalid_param',
                __('Password is required.', 'jwt-shield-lite'),
                array('status' => 400)
            );
        }

        if (strlen($value) > self::MAX_PASSWORD_LENGTH) {
            return new WP_Error(
                'jwt_auth_invalid_param',
                __('Invalid username or password.', 'jwt-shield-lite'),
                array('status' => 400)
            );
        }

        return true;
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

        // Reset rate limit on successful authentication
        $this->clear_rate_limit('token');

        return rest_ensure_response($token);
    }

    /**
     * Validate JWT token
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function validate_token($request) {
        // Check rate limiting first
        $rate_limit_check = $this->check_rate_limit('validate');
        if (is_wp_error($rate_limit_check)) {
            return $rate_limit_check;
        }

        $auth = Jwt_Shield_Lite_Auth::instance();
        $validation = $auth->validate_token();

        if (is_wp_error($validation)) {
            // Increment failed attempts for rate limiting
            $this->record_failed_attempt('validate');
            return $validation;
        }

        return rest_ensure_response($validation);
    }

    /**
     * Get rate limit transient key for an endpoint
     *
     * @param string $endpoint The endpoint name
     * @return string The transient key
     */
    private function get_rate_limit_key($endpoint) {
        $client_ip = Jwt_Shield_Lite_Helpers::get_client_ip();
        return 'jwt_shield_lite_rate_limit_' . md5($client_ip . '_' . $endpoint);
    }

    /**
     * Check rate limiting for an endpoint
     *
     * @param string $endpoint The endpoint name
     * @return bool|WP_Error True if allowed, WP_Error if rate limited
     */
    private function check_rate_limit($endpoint) {
        $transient_key = $this->get_rate_limit_key($endpoint);
        $attempts = get_transient($transient_key);

        if ($attempts !== false && $attempts >= self::MAX_ATTEMPTS) {
            return Jwt_Shield_Lite_Helpers::create_error(
                'jwt_auth_rate_limited',
                __('Too many attempts. Please try again later.', 'jwt-shield-lite'),
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
        $transient_key = $this->get_rate_limit_key($endpoint);
        $attempts = get_transient($transient_key);
        $attempts = ($attempts !== false) ? $attempts + 1 : 1;

        set_transient($transient_key, $attempts, self::LOCKOUT_DURATION);
    }

    /**
     * Clear rate limit on successful authentication
     *
     * @param string $endpoint The endpoint name
     */
    private function clear_rate_limit($endpoint) {
        $transient_key = $this->get_rate_limit_key($endpoint);
        delete_transient($transient_key);
    }
} 