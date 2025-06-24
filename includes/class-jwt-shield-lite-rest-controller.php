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
        $auth = Jwt_Shield_Lite_Auth::instance();
        
        $params = array(
            'username' => $request->get_param('username'),
            'password' => $request->get_param('password'),
        );

        $token = $auth->generate_token($params);

        if (is_wp_error($token)) {
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
} 