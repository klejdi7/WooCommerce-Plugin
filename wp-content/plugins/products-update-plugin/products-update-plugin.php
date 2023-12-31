<?php

use Automattic\WooCommerce\Admin\API\Products;
use WC_Product_Simple;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://test.klejdiarapi.com
 * @since             1.0.0
 * @package           Products_Update_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Products Update Plugin
 * Plugin URI:        https://http://test.wordpressplugin.com/
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Klejdi Arapi
 * Author URI:        https://test.klejdiarapi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       products-update-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PRODUCTS_UPDATE_PLUGIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-products-update-plugin-activator.php
 */
function activate_products_update_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-products-update-plugin-activator.php';
	Products_Update_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-products-update-plugin-deactivator.php
 */
function deactivate_products_update_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-products-update-plugin-deactivator.php';
	Products_Update_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_products_update_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_products_update_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-products-update-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_products_update_plugin() {

	$plugin = new Products_Update_Plugin();
	$plugin->run();

}

add_action('init', 'scheduleDailyCronJob');

function scheduleDailyCronJob() {
	if (!wp_next_scheduled('daily_cron_hook')) wp_schedule_event(strtotime('3:00 am'), 'daily', 'daily_cron_hook');
}

add_action('daily_cron_hook', 'syncCategories');
add_action('daily_cron_hook', 'syncProducts');
add_action('daily_cron_hook', 'checkForDuplicates');

function getBrands() {

	$apiUrl = 'https://dev.dropshippingb2b.com/api/';

	$data = array(
		'data' => '{
			"uid": 77651,
			"pid": 11,
			"lid": 10,
			"key": "4AwqQu7BZ1TU1M0sNZUoe284y9jlJbkV3jX1oMnkP00HCZ86b6c54IKl4zp3kM5e",
			"api_version": "1.0.0",
			"request": "get_brands"
		  }',
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $apiUrl);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Set this to true if you have a valid SSL certificate

	$response = curl_exec($ch);

	if (curl_errno($ch)) {
		echo 'cURL Error: ' . curl_error($ch);
	}

	curl_close($ch);

	$brands_decoded = json_decode($response, true);

	return $brands_decoded;
	
}

function getProductsByBrandID($id){
	
	$apiUrl = 'https://dev.dropshippingb2b.com/api/';

	$data = array(
		'data' => '{
			"uid": 77651,
			"pid": 11,
			"lid": 10,
			"key": "4AwqQu7BZ1TU1M0sNZUoe284y9jlJbkV3jX1oMnkP00HCZ86b6c54IKl4zp3kM5e",
			"api_version": "1.0.0",
			"request": "get_brand_items",
			"id_brand":'.$id.',
			"display_attributes":true, 
			"display_discount":true, 
			"display_retail_price":true, 
			"display_id_supplier":false, 
			"display_currency":false, 
			"display_icon_path":false, 
			"display_image_last_update":false
		}',
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $apiUrl);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Set this to true if you have a valid SSL certificate

	$response = curl_exec($ch);

	if (curl_errno($ch)) {
		echo 'cURL Error: ' . curl_error($ch);
	}

	curl_close($ch);

	$products_decoded = json_decode($response, true);

	return $products_decoded;
}

function addCategory($category_name) {
	$category_slug = sanitize_title($category_name);
	$category_args = array(
		'description' => '',
		'slug'		  => $category_slug,
	);

	$existing_term = term_exists($category_name, 'product_cat');
	
	if (!$existing_term) {
		$new_category = wp_insert_term($category_name, 'product_cat', $category_args);
		if (!is_wp_error($new_category)) {
			return $new_category;
		} else {
			return $new_category->get_error_message();
		}
	} else {
		return 'Category already exists';
	}
}

function checkForDuplicates() {

	$duplicateProducts = array();
	$allProducts = get_posts(array('post_type' => 'product', 'posts_per_page' => -1));

	// Find duplicate SKUs and group them.
	foreach ($allProducts as $product) {
		$sku = get_post_meta($product->ID, '_sku', true);

		if (!empty($sku)) {
			if (!isset($duplicateProducts[$sku])) {
				$duplicateProducts[$sku] = array();
			}

			$duplicateProducts[$sku][] = $product->ID;
		}
	}

	foreach ($duplicateProducts as $sku => $products) {
		if (count($products) > 1) {
			$product_to_keep = array_shift($products);

			foreach ($products as $product_id) {
				wp_delete_post($product_id, true); 
			}
		}
	}

}

function syncCategories() {

	$brands = getBrands();

	add_action('init', function() use ($brands) {
		foreach ($brands["rows"] as $brand) {
			addCategory($brand["group"]);
		}
	});

}

function syncProducts() {

	$brands = getBrands();

	foreach($brands["rows"] as $brand) {
		$productsByBrand = getProductsByBrandID($brand["id_brand"]);

		foreach($productsByBrand["rows"] as $product){
			$category = term_exists($brand["group"], 'product_cat');
			newProductAdd($product, $category);
		}
	}

	checkForDuplicates();

}

function checkProductExists($sku) {
	$args = array(
		'post_type' => 'product',
		'meta_query' => array(
			array(
				'key' => '_sku',
				'value' => $sku,
				'compare' => '='
			)
		),
		'posts_per_page' => 1,
		'fields' => 'ids'
	);

	$product_ids = get_posts($args);

	if (!empty($product_ids)) {
		return $product_ids[0];
	}

	return false;
}

function newProductAdd($productData, $category){

	$productTitle = $productData["name"];
	$productDescription = '-';
	$productPrice = $productData["price"];
	$productSKU = $productData["id_product"];
	$productCategoryID = array($category["term_id"]);
	$productSlug = sanitize_title($productTitle);
	$imageFilePath = $productData["image_path"];

	$product_id = wc_get_product_id_by_sku($productSKU);

	if ($product_id) $product = wc_get_product($product_id);
	else $product = new WC_Product();

	$product->set_name($productTitle);
	$product->set_slug($productSlug);
	$product->set_sku($productSKU);
	$product->set_description($productDescription);
	$product->set_regular_price($productPrice);

	$image_id = insertImageToProduct( $imageFilePath);
	$product->set_image_id($image_id);

	if ($productCategoryID) $product->set_category_ids($productCategoryID);

	$product->save();

	echo 'Product added directly to the database. Product ID: ';

}

function insertImageToProduct($image_url) {

	if (!empty($image_url)) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$image_name = basename($image_url);
		$upload_dir = wp_upload_dir();
		$image_path = $upload_dir['path'] . '/' . $image_name;

		if (copy($image_url, $image_path)) {

			$attachment = array(
				'post_mime_type' => 'image/jpeg', // Adjust the MIME type if needed
				'post_title'     => sanitize_file_name($image_name),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$attach_id = wp_insert_attachment($attachment, $image_path, $product_id);

			return $attach_id;
		}
	}
}

function log_sync_activity($message) {
	$log_file = WP_CONTENT_DIR . '/sync_log.txt';
	$current_time = current_time('mysql');
	$log_message = $current_time . ': ' . $message . "\n";
	file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

log_sync_activity('Products synchronized successfully.');

run_products_update_plugin();
