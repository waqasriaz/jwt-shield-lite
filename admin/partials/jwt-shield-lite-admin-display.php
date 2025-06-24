<?php
/**
 * Main admin display for JWT Shield Lite
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

<div class="wrap jwt-shield-lite">
    <h1>JWT Shield Lite - Settings</h1>
    
    <?php if (get_option('jwt_shield_lite_show_upgrade_notice') && !get_option('jwt_shield_lite_hide_upgrade_notice')): ?>
    <div class="notice notice-info">
        <p>
            <strong>Get JWT Shield Pro!</strong> Unlock advanced features like refresh tokens, token analytics, IP management, and more.
            <a href="<?php echo admin_url('admin.php?page=jwt-shield-lite-upgrade'); ?>">Learn More</a>
            <a href="#" class="dismiss-upgrade-notice" style="float:right;">Dismiss</a>
        </p>
    </div>
    <?php endif; ?>

    <div class="jwt-shield-lite-container">
        <div class="jwt-shield-lite-main">
            <form method="post" action="options.php">
                <?php settings_fields('jwt_shield_lite_settings'); ?>
                
                <h2>Basic Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="jwt_shield_lite_secret_key">Secret Key</label>
                        </th>
                        <td>
                            <input type="text" id="jwt_shield_lite_secret_key" 
                                   name="jwt_shield_lite_secret_key" 
                                   value="<?php echo esc_attr(get_option('jwt_shield_lite_secret_key')); ?>" 
                                   class="regular-text" />
                            <button type="button" class="button" id="generate-key">Generate New Key</button>
                            <p class="description">
                                The secret key used to sign JWT tokens. Keep this secure!
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="jwt_shield_lite_token_expiration">Token Expiration</label>
                        </th>
                        <td>
                            <?php
                            $expiration = get_option('jwt_shield_lite_token_expiration', 604800);
                            $days = floor($expiration / 86400);
                            ?>
                            <input type="number" id="jwt_shield_lite_token_expiration_days" 
                                   min="1" max="365" value="<?php echo $days; ?>" 
                                   class="small-text" /> days
                            <input type="hidden" id="jwt_shield_lite_token_expiration" 
                                   name="jwt_shield_lite_token_expiration" 
                                   value="<?php echo $expiration; ?>" />
                            <p class="description">
                                How long tokens remain valid after creation.
                                <strong>Pro Feature:</strong> Refresh tokens allow extending sessions without re-authentication.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="jwt_shield_lite_algorithm">Signing Algorithm</label>
                        </th>
                        <td>
                            <select id="jwt_shield_lite_algorithm" name="jwt_shield_lite_algorithm">
                                <?php
                                $current_algo = get_option('jwt_shield_lite_algorithm', 'HS256');
                                ?>
                                <option value="HS256" <?php selected($current_algo, 'HS256'); ?>>HS256 (HMAC SHA-256)</option>
                            </select>
                            <p class="description">
                                Algorithm used for signing tokens.
                                <strong>Pro Feature:</strong> Support for RS256, ES256, and more algorithms.
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <h2>API Endpoints</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th>Method</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code><?php echo rest_url('jwt-shield-lite/v1/token'); ?></code></td>
                        <td>POST</td>
                        <td>Generate JWT token</td>
                    </tr>
                    <tr>
                        <td><code><?php echo rest_url('jwt-shield-lite/v1/validate'); ?></code></td>
                        <td>POST</td>
                        <td>Validate JWT token</td>
                    </tr>
                    <tr class="pro-feature">
                        <td><code><?php echo rest_url('jwt-shield/v1/token/refresh'); ?></code></td>
                        <td>POST</td>
                        <td>Refresh token (Pro only)</td>
                    </tr>
                </tbody>
            </table>

            <h2>Active Tokens</h2>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'jwt_shield_lite_tokens';
            $token_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE expires_at > NOW()");
            $user_count = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table_name WHERE expires_at > NOW()");
            ?>
            <p>
                Active tokens: <strong><?php echo $token_count; ?></strong><br>
                Active users: <strong><?php echo $user_count; ?></strong>
            </p>
            <p class="description">
                <strong>Pro Features:</strong> Detailed token management, usage analytics, IP tracking, and more.
            </p>
        </div>

        <div class="jwt-shield-lite-sidebar">
            <div class="jwt-shield-lite-box">
                <h3>Upgrade to Pro</h3>
                <ul>
                    <li>✓ Refresh Tokens</li>
                    <li>✓ Token Analytics</li>
                    <li>✓ IP Management</li>
                    <li>✓ Advanced Security</li>
                    <li>✓ Multiple Algorithms</li>
                    <li>✓ Priority Support</li>
                </ul>
                <a href="<?php echo admin_url('admin.php?page=jwt-shield-lite-upgrade'); ?>" 
                   class="button button-primary button-hero">Upgrade Now</a>
            </div>

            <div class="jwt-shield-lite-box">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo admin_url('admin.php?page=jwt-shield-lite-docs'); ?>">Documentation</a></li>
                    <li><a href="https://wordpress.org/support/plugin/jwt-shield-lite/" target="_blank">Support Forum</a></li>
                    <li><a href="https://wordpress.org/support/plugin/jwt-shield-lite/reviews/" target="_blank">Rate Plugin</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Generate secret key
    $('#generate-key').on('click', function() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        var key = '';
        for (var i = 0; i < 32; i++) {
            key += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        $('#jwt_shield_lite_secret_key').val(key);
    });

    // Update expiration in seconds
    $('#jwt_shield_lite_token_expiration_days').on('change', function() {
        var days = parseInt($(this).val());
        var seconds = days * 86400;
        $('#jwt_shield_lite_token_expiration').val(seconds);
    });

    // Dismiss upgrade notice
    $('.dismiss-upgrade-notice').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.notice').fadeOut();
        // Save dismissal
        $.post(ajaxurl, {
            action: 'jwt_shield_lite_dismiss_upgrade',
            _wpnonce: '<?php echo wp_create_nonce('jwt_shield_lite_dismiss'); ?>'
        });
    });
});
</script> 