<?php

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

// Hook the function to a scheduled event (once a day) using WordPress Cron or ActionScheduler
// Example using WordPress Cron
function schedule_sync_products() {
	if (!wp_next_scheduled('sync_products_event')) {
		wp_schedule_event(time(), 'daily', 'sync_products_event');
	}
}
add_action('init', 'schedule_sync_products');

// Hook the sync_products_with_supplier function to the scheduled event
add_action('sync_products_event', 'sync_products_with_supplier');

function sync_products_with_supplier() {
	// Retrieve data from the supplier API using Postman collection or any other method
	$supplier_data = get_products_data(); // Replace this with the function that fetches the supplier data

	if (!$supplier_data) {
		log_sync_activity('Failed to fetch supplier data.');
		return;
	}

	// Convert the JSON data to an array (assuming the data is in JSON format)
	$products = $supplier_data;

	if (!$products || !is_array($products)) {
		log_sync_activity('Error: Invalid supplier data format.');
		return;
	}

	foreach ($products as $product_data) {
		// Extract necessary product information from the supplier data
		$product_id = isset($product_data['id']) ? $product_data['id'] : 0;
		$product_name = isset($product_data['name']) ? $product_data['name'] : '';
		$product_price = isset($product_data['price']) ? $product_data['price'] : 0.00;
		$product_description = isset($product_data['description']) ? $product_data['description'] : '';

		// Check if the product already exists in WooCommerce based on a unique identifier (e.g., product_id)
		$existing_product = get_product_by_unique_identifier($product_id);

		if ($existing_product) {
			// Product already exists, update it
			$existing_product->set_name($product_name);
			$existing_product->set_price($product_price);
			$existing_product->set_description($product_description);
			// Set other product data if needed (e.g., images, categories, attributes)

			// Save the updated product
			$existing_product->save();

			log_sync_activity('Updated product: ' . $product_id . ' - ' . $product_name);
		} else {
			// Product does not exist, create a new product
			$new_product = new WC_Product();

			// Set product data
			$new_product->set_name($product_name);
			$new_product->set_price($product_price);
			$new_product->set_description($product_description);
			// Set other product data if needed (e.g., images, categories, attributes)

			// Save the new product
			$new_product->save();

			log_sync_activity('Created new product: ' . $product_id . ' - ' . $product_name);
		}
	}

	// Check for products that might have been deleted from the supplier and delete them from WooCommerce
	cleanup_deleted_products($products);
}

// function get_products_data() {

// 	$endpoint = 'https://dev.dropshippingb2b.com/api/';

// 	// Request data
// 	$data = array(
// 		"uid" => 77651,
// 		"pid" => 11,
// 		"lid" => 10,
// 		"key" => "4AwqQu7BZ1TU1M0sNZUoe284y9jlJbkV3jX1oMnkP00HCZ86b6c54IKl4zp3kM5e",
// 		"api_version" => "1.0.0",
// 		"request" => "get_brand_items",
// 		"id_brand" => "45",
// 		"display_attributes" => true,
// 		"display_discount" => true,
// 		"display_retail_price" => true,
// 		"display_id_supplier" => false,
// 		"display_currency" => false,
// 		"display_icon_path" => false,
// 		"display_image_last_update" => false	  
// 	);

// 	// Prepare the request body
// 	$body = array(
// 				'key' => 'data',
// 				'value' => json_encode($data),
// 				'type' => 'text'
// 	);

// 	// API request arguments
// 	$args = array(
// 		'method' => 'POST',
// 		'headers' => array(),
// 		'body' => $body,
// 	);

// 	// Make the API request
// 	// $response = wp_remote_request($endpoint, $args);

// 	$ch = curl_init();

// 	// Set cURL options
// 	curl_setopt($ch, CURLOPT_URL, $endpoint);
// 	curl_setopt($ch, CURLOPT_POST, 1);
// 	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// 	// Execute cURL session and store the API response
// 	$response = curl_exec($ch);

// 	// Check for API request errors
// 	if (is_wp_error($response)) {
// 		// Handle API request error
// 		print_R("error");
// 		return false;
// 	}

// 	// Get the response body
// 	$response_body = wp_remote_retrieve_body($response);

// 	// Decode the JSON response
// 	$decoded_response = json_decode($response_body, true);

// 	print_r($response);
// 	die();
// 	// Check if the response was successfully decoded
// 	if (is_array($decoded_response)) {
// 		// Process the API response data here
// 		// You can access the response using $decoded_response variable
// 		return $decoded_response;
// 	}

// 	return false;
// }

function get_products_data() {
// API endpoint URL
$apiUrl = 'https://dev.dropshippingb2b.com/api/';

// Data to send in the POST request (URL-encoded)
$data = array(
    'data' => '{"uid": 77651,"pid": 11,"lid": 10,"key": "4AwqQu7BZ1TU1M0sNZUoe284y9jlJbkV3jX1oMnkP00HCZ86b6c54IKl4zp3kM5e","api_version": "1.0.0","request": "get_brand_items", "id_brand":"45","display_attributes":true, "display_discount":true, "display_retail_price":true, "display_id_supplier":false, "display_currency":false, "display_icon_path":false, "display_image_last_update":false}',
);

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Set this to true if you have a valid SSL certificate

// Execute cURL session and store the API response
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);

// Display the API response
$decoded_response = json_decode($response, true);

foreach($decoded_response["rows"] as $prod){
	// that's CRUD object
	newProductAdd($prod);
}

}
// print_r($decoded_response["rows"][1]);
function newProductAdd($product){

// Replace these values with your own product details
$productTitle = $product["name"];
$productDescription = '-';
$productPrice = $product["price"];
$productSKU = $product["id_product"];
$productCategoryIds = array(1); // Replace with the category IDs the product belongs to

// Insert product as a post
global $wpdb;

$wpdb->insert(
	$wpdb->posts,
	array(
		'post_title' => $productTitle,
		'post_content' => $productDescription,
		'post_status' => 'publish',
		'post_type' => 'product',
	)
);

$productID = $wpdb->insert_id;

// Add product meta data
update_post_meta($productID, '_regular_price', $productPrice);
update_post_meta($productID, '_price', $productPrice);
update_post_meta($productID, '_sku', $productSKU);

// Add product categories
wp_set_post_terms($productID, $productCategoryIds, 'product_cat');

echo 'Product added directly to the database. Product ID: ' . $productID;

}

function createSlug($str, $delimiter = '-'){

	$slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
	return $slug;

} 

function log_sync_activity($message) {
	$log_file = WP_CONTENT_DIR . '/sync_log.txt';
	$current_time = current_time('mysql');
	$log_message = $current_time . ': ' . $message . "\n";
	file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

// Usage example:
log_sync_activity('Product X was synchronized successfully.');

// get_products_data();
run_products_update_plugin();
