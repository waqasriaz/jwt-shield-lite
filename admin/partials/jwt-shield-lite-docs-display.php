<?php
/**
 * Documentation page display for JWT Shield Lite
 *
 * @since      1.0.0
 * @package    Jwt_Shield_Lite
 * @subpackage Jwt_Shield_Lite/admin/partials
 */

// Don't allow direct access
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap jwt-shield-lite-docs">
    <h1>JWT Shield Lite Documentation</h1>
    
    <div class="jwt-shield-lite-container">
        <div class="jwt-shield-lite-main">
            <h2>Getting Started</h2>
            
            <h3>1. Configure Your Secret Key</h3>
            <p>Navigate to <strong>JWT Shield Lite → Settings</strong> and generate a secure secret key. This key is used to sign your JWT tokens and should be kept secure.</p>
            
            <h3>2. Set Token Expiration</h3>
            <p>Choose how long tokens should remain valid. The default is 7 days, but you can adjust this based on your security requirements.</p>
            
            <h3>3. Generate Your First Token</h3>
            <p>Use the authentication endpoint to generate a JWT token:</p>
            
            <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
POST <?php echo rest_url('jwt-shield-lite/v1/token'); ?>

Content-Type: application/json

{
    "username": "your-username",
    "password": "your-password"
}
            </pre>
            
            <h3>4. Use the Token</h3>
            <p>Include the token in the Authorization header for authenticated requests:</p>
            
            <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
Authorization: Bearer your-jwt-token-here
            </pre>
            
            <hr>
            
            <h2>API Endpoints</h2>
            
            <h3>Generate Token</h3>
            <p><strong>Endpoint:</strong> <code>POST /wp-json/jwt-shield-lite/v1/token</code></p>
            <p><strong>Parameters:</strong></p>
            <ul>
                <li><code>username</code> (required) - WordPress username</li>
                <li><code>password</code> (required) - WordPress password</li>
            </ul>
            <p><strong>Response:</strong></p>
            <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user_email": "user@example.com",
    "user_nicename": "username",
    "user_display_name": "User Name",
    "exp": 1234567890
}
            </pre>
            
            <h3>Validate Token</h3>
            <p><strong>Endpoint:</strong> <code>POST /wp-json/jwt-shield-lite/v1/validate</code></p>
            <p><strong>Headers:</strong></p>
            <ul>
                <li><code>Authorization: Bearer your-jwt-token</code></li>
            </ul>
            <p><strong>Response:</strong></p>
            <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
{
    "code": "jwt_auth_valid_token",
    "data": {
        "status": 200,
        "user_id": 1
    }
}
            </pre>
            
            <hr>
            
            <h2>Code Examples</h2>
            
            <h3>PHP Example</h3>
            <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
// Generate token
$response = wp_remote_post('https://your-site.com/wp-json/jwt-shield-lite/v1/token', array(
    'body' => json_encode(array(
        'username' => 'your-username',
        'password' => 'your-password'
    )),
    'headers' => array('Content-Type' => 'application/json')
));

$body = json_decode(wp_remote_retrieve_body($response), true);
$token = $body['token'];

// Use token for authenticated request
$auth_response = wp_remote_get('https://your-site.com/wp-json/wp/v2/users/me', array(
    'headers' => array(
        'Authorization' => 'Bearer ' . $token
    )
));
            </pre>
            
            <h3>JavaScript Example</h3>
            <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
// Generate token
fetch('https://your-site.com/wp-json/jwt-shield-lite/v1/token', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        username: 'your-username',
        password: 'your-password'
    })
})
.then(response => response.json())
.then(data => {
    const token = data.token;
    
    // Use token for authenticated request
    return fetch('https://your-site.com/wp-json/wp/v2/users/me', {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    });
})
.then(response => response.json())
.then(userData => {
    
});
            </pre>
            
            <hr>
            
            <h2>Error Responses</h2>
            
            <p>JWT Shield Lite returns standard WordPress REST API error responses:</p>
            
            <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
{
    "code": "jwt_auth_invalid_credentials",
    "message": "Invalid username or password.",
    "data": {
        "status": 401
    }
}
            </pre>
            
            <p><strong>Common Error Codes:</strong></p>
            <ul>
                <li><code>jwt_auth_empty_credentials</code> - Username or password missing</li>
                <li><code>jwt_auth_invalid_credentials</code> - Invalid login credentials</li>
                <li><code>jwt_auth_bad_config</code> - JWT not configured properly</li>
                <li><code>jwt_auth_no_auth_header</code> - Authorization header not found</li>
                <li><code>jwt_auth_bad_auth_header</code> - Authorization header malformed</li>
                <li><code>jwt_auth_invalid_token</code> - Token validation failed</li>
            </ul>
            
            <hr>
            
            <h2>Need More Features?</h2>
            
            <p>JWT Shield Pro includes advanced features like:</p>
            <ul>
                <li>Refresh tokens for extended sessions</li>
                <li>Token analytics and monitoring</li>
                <li>IP blocking and security management</li>
                <li>Multiple signing algorithms</li>
                <li>And much more!</li>
            </ul>
            
            <p><a href="<?php echo admin_url('admin.php?page=jwt-shield-lite-upgrade'); ?>" class="button button-primary">Upgrade to Pro</a></p>
        </div>
        
        <div class="jwt-shield-lite-sidebar">
            <div class="jwt-shield-lite-box">
                <h3>Quick Links</h3>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><a href="#getting-started">Getting Started</a></li>
                    <li><a href="#api-endpoints">API Endpoints</a></li>
                    <li><a href="#code-examples">Code Examples</a></li>
                    <li><a href="#error-responses">Error Responses</a></li>
                </ul>
            </div>
            
            <div class="jwt-shield-lite-box">
                <h3>Need Help?</h3>
                <p>Visit the <a href="https://wordpress.org/support/plugin/jwt-shield-lite/" target="_blank">support forum</a> for community help.</p>
                <p><strong>Pro users</strong> get priority support with faster response times.</p>
            </div>
        </div>
    </div>
</div> 