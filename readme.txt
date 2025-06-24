=== JWT Shield Lite - Basic JWT Authentication ===
Contributors: waqasriaz
Tags: jwt, authentication, rest-api, api, token
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Basic JWT authentication for WordPress REST API. Simple, secure, and easy to use.

== Description ==

JWT Shield Lite provides basic JWT (JSON Web Token) authentication for your WordPress REST API. Perfect for developers who need simple token-based authentication without the complexity.

= Features =

* **Simple JWT Authentication** - Generate and validate JWT tokens easily
* **REST API Integration** - Seamlessly integrates with WordPress REST API
* **Secure Token Generation** - Uses industry-standard JWT tokens
* **Basic Token Management** - View active tokens and users
* **Easy Configuration** - Simple settings interface
* **Developer Friendly** - Clear documentation and examples

= Pro Features =

Upgrade to JWT Shield Pro for advanced features:

* Refresh Tokens
* Token Analytics Dashboard
* IP Blocking & Management
* Multiple Signing Algorithms
* Token Usage Tracking
* Failed Authentication Monitoring
* Email Notifications
* Bulk Token Management
* Export Functionality
* Priority Support

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/jwt-shield-lite` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to JWT Shield Lite → Settings to configure your secret key
4. Start generating JWT tokens!

== Frequently Asked Questions ==

= How do I generate a token? =

Send a POST request to `/wp-json/jwt-shield-lite/v1/token` with username and password:

```
{
    "username": "your-username",
    "password": "your-password"
}
```

= How do I use the token? =

Include the token in the Authorization header:

```
Authorization: Bearer your-jwt-token
```

= Is this secure? =

Yes! JWT Shield Lite uses WordPress's built-in authentication system and industry-standard JWT tokens.

= What's the difference between Lite and Pro? =

The Lite version provides basic JWT authentication functionality. Pro includes advanced features like refresh tokens, analytics, IP management, and more.

== Screenshots ==

1. Settings page
2. Token generation example
3. API documentation
4. Upgrade to Pro features

== Changelog ==

= 1.0.0 =
* Initial release
* Basic JWT authentication
* Token generation and validation
* Simple settings interface
* API documentation

== Upgrade Notice ==

= 1.0.0 =
Initial release of JWT Shield Lite. Upgrade to Pro for advanced features!

== API Endpoints ==

= Generate Token =
* **Endpoint:** `POST /wp-json/jwt-shield-lite/v1/token`
* **Parameters:** username, password
* **Returns:** JWT token and user information

= Validate Token =
* **Endpoint:** `POST /wp-json/jwt-shield-lite/v1/validate`
* **Headers:** Authorization: Bearer {token}
* **Returns:** Validation status and user ID

== Support ==

For support, please visit our [support forum](https://wordpress.org/support/plugin/jwt-shield-lite/).

Pro users get priority support at [jwt-shield.com/support](https://jwt-shield.com/support). 