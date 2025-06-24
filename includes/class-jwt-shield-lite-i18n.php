<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://waqasriaz.com
 * @since      1.0.0
 *
 * @package    Jwt_Shield
 * @subpackage Jwt_Shield/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Jwt_Shield
 * @subpackage Jwt_Shield/includes
 * @author     Waqas Riaz <waqas@houzez.com>
 */
class Jwt_Shield_Lite_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'jwt-shield-lite',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);

	}



}
