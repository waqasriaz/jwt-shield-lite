<?php
/**
 * Upgrade page display for JWT Shield Lite
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

<div class="wrap jwt-shield-upgrade-page">
    <h1>Upgrade to JWT Shield Pro</h1>
    <p class="lead">Unlock the full power of JWT authentication for WordPress with advanced features and premium support.</p>

    <div class="feature-comparison">
        <h2>Feature Comparison</h2>
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Lite (Free)</th>
                    <th>Pro</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Basic JWT Authentication</strong></td>
                    <td class="check">✓</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Token Generation & Validation</strong></td>
                    <td class="check">✓</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Basic Settings Management</strong></td>
                    <td class="check">✓</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Refresh Tokens</strong></td>
                    <td class="cross">✗</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Token Analytics Dashboard</strong></td>
                    <td class="cross">✗</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>IP Blocking & Management</strong></td>
                    <td class="cross">✗</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Advanced Security Features</strong></td>
                    <td class="cross">✗</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Multiple Signing Algorithms</strong></td>
                    <td>HS256 only</td>
                    <td>RS256, ES256, PS256 & more</td>
                </tr>
                <tr>
                    <td><strong>Token Usage Tracking</strong></td>
                    <td class="cross">✗</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Failed Authentication Monitoring</strong></td>
                    <td class="cross">✗</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Email Notifications</strong></td>
                    <td class="cross">✗</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Bulk Token Management</strong></td>
                    <td class="cross">✗</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Export Functionality</strong></td>
                    <td class="cross">✗</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Priority Support</strong></td>
                    <td class="cross">✗</td>
                    <td class="check">✓</td>
                </tr>
                <tr>
                    <td><strong>Regular Updates</strong></td>
                    <td>Community</td>
                    <td>Priority Updates</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="pricing-section">
        <div class="pricing-box">
            <h3>Single Site</h3>
            <div class="price">$47<span>/year</span></div>
            <ul>
                <li>1 Site License</li>
                <li>All Pro Features</li>
                <li>1 Year of Updates</li>
                <li>Priority Support</li>
            </ul>
            <a href="#" class="button button-primary button-large">Buy Now</a>
        </div>

        <div class="pricing-box featured">
            <h3>Developer (5 Sites)</h3>
            <div class="price">$97<span>/year</span></div>
            <ul>
                <li>5 Site Licenses</li>
                <li>All Pro Features</li>
                <li>1 Year of Updates</li>
                <li>Priority Support</li>
                <li>Developer Resources</li>
            </ul>
            <a href="#" class="button button-primary button-large">Buy Now</a>
        </div>

        <div class="pricing-box">
            <h3>Agency (Unlimited)</h3>
            <div class="price">$197<span>/year</span></div>
            <ul>
                <li>Unlimited Sites</li>
                <li>All Pro Features</li>
                <li>1 Year of Updates</li>
                <li>Priority Support</li>
                <li>White Label Option</li>
            </ul>
            <a href="#" class="button button-primary button-large">Buy Now</a>
        </div>
    </div>

    <div class="feature-highlights">
        <h2>Pro Feature Highlights</h2>
        
        <div class="feature-grid">
            <div class="feature-box">
                <h3>🔄 Refresh Tokens</h3>
                <p>Keep users logged in securely with token rotation and family tracking. Prevent session hijacking with automatic token invalidation.</p>
            </div>

            <div class="feature-box">
                <h3>📊 Advanced Analytics</h3>
                <p>Monitor token usage, track authentication patterns, and identify security threats with comprehensive analytics dashboard.</p>
            </div>

            <div class="feature-box">
                <h3>🛡️ IP Management</h3>
                <p>Block suspicious IPs, track failed attempts, and implement automatic security measures to protect your API.</p>
            </div>

            <div class="feature-box">
                <h3>🔐 Enhanced Security</h3>
                <p>Multiple signing algorithms, IP binding, token revocation, and advanced threat detection keep your site secure.</p>
            </div>

            <div class="feature-box">
                <h3>📧 Email Alerts</h3>
                <p>Get notified of suspicious activities, blocked IPs, and security events in real-time.</p>
            </div>

            <div class="feature-box">
                <h3>🚀 Priority Support</h3>
                <p>Get help when you need it with priority support from our expert team.</p>
            </div>
        </div>
    </div>

    <div class="testimonials">
        <h2>What Our Users Say</h2>
        <blockquote>
            "JWT Shield Pro transformed how we handle API authentication. The refresh tokens and analytics features are game-changers!"
            <cite>- John D., API Developer</cite>
        </blockquote>
        <blockquote>
            "The IP management features helped us identify and block malicious attempts. Worth every penny!"
            <cite>- Sarah M., Security Engineer</cite>
        </blockquote>
    </div>

    <div class="cta-section">
        <h2>Ready to Upgrade?</h2>
        <p>Join thousands of developers using JWT Shield Pro for secure API authentication.</p>
        <a href="#" class="button button-primary button-hero">Get JWT Shield Pro Now</a>
        <p><small>30-day money-back guarantee • Instant activation • Secure checkout</small></p>
    </div>
</div>

<style>
.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.feature-box {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
}

.feature-box h3 {
    margin-top: 0;
}

.testimonials {
    background: #f9f9f9;
    padding: 30px;
    margin: 30px 0;
}

.testimonials blockquote {
    background: #fff;
    padding: 20px;
    margin: 20px 0;
    border-left: 4px solid #0073aa;
}

.testimonials cite {
    display: block;
    text-align: right;
    margin-top: 10px;
    font-style: normal;
    color: #666;
}

.cta-section {
    text-align: center;
    padding: 40px;
    background: #f0f8ff;
    margin: 30px 0;
}

.lead {
    font-size: 18px;
    color: #666;
}
</style> 