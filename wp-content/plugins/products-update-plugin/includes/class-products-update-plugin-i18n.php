<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://test.klejdiarapi.com
 * @since      1.0.0
 *
 * @package    Products_Update_Plugin
 * @subpackage Products_Update_Plugin/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Products_Update_Plugin
 * @subpackage Products_Update_Plugin/includes
 * @author     Klejdi Arapi <arapiklejdi2@gmail.com>
 */
class Products_Update_Plugin_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'products-update-plugin',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
