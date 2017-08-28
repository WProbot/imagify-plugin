<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * Create a new user on Imagify.
 *
 * @param  array $data All user data.
 * @return object
 */
function add_imagify_user( $data ) {
	return imagify()->create_user( $data );
}

/**
 * Update your Imagify account.
 *
 * @param  string $data All user data.
 * @return object
 */
function update_imagify_user( $data ) {
	return imagify()->update_user( $data );
}

/**
 * Get your Imagify account infos.
 *
 * @return object
 */
function get_imagify_user() {
	return imagify()->get_user();
}

/**
 * Get the Imagify API version.
 *
 * @return object
 */
function get_imagify_api_version() {
	return imagify()->get_api_version();
}

/**
 * Check your Imagify API key status.
 *
 * @param  string $data An API key.
 * @return bool
 */
function get_imagify_status( $data ) {
	return imagify()->get_status( $data );
}

/**
 * Optimize an image by uploading it on Imagify.
 *
 * @param  array $data All image data.
 * @return object
 */
function fetch_imagify_image( $data ) {
	return imagify()->fetch_image( $data );
}

/**
 * Optimize an image by sharing its URL on Imagify.
 *
 * @since 1.6.7 $data['image'] can contain the file path (prefered) or the result of `curl_file_create()`.
 *
 * @param  array $data All image data.
 * @return object
 */
function upload_imagify_image( $data ) {
	return imagify()->upload_image( $data );
}

/**
 * Get Imagify Plans Prices.
 *
 * @since  1.5
 * @author Geoffrey Crofte
 *
 * @return object
 */
function get_imagify_plans_prices() {
	return imagify()->get_plans_prices();
}

/**
 * Get Imagify Plans Prices.
 *
 * @since  1.5
 * @author Geoffrey Crofte
 *
 * @return object
 */
function get_imagify_packs_prices() {
	return imagify()->get_packs_prices();
}

/**
 * Get Imagify All Prices (plan & packs).
 *
 * @since  1.5.4
 * @author Geoffrey Crofte
 *
 * @return object
 */
function get_imagify_all_prices() {
	return imagify()->get_all_prices();
}

/**
 * Check if Coupon Code exists.
 *
 * @since  1.6
 * @author Geoffrey Crofte
 *
 * @param  string $coupon the coupon code to check.
 * @return object
 */
function check_imagify_coupon_code( $coupon ) {
	return imagify()->check_coupon_code( $coupon );
}

/**
 * Check if Discount/Promotion is available.
 *
 * @since  1.6.3
 * @author Geoffrey Crofte
 *
 * @return object
 */
function check_imagify_discount() {
	return imagify()->check_discount();
}

/**
 * Get Maximum image size for free plan.
 *
 * @since 1.5.6
 * @author Remy Perona
 *
 * @return string
 */
function get_imagify_max_image_size() {
	$max_image_size = get_transient( 'imagify_max_image_size' );

	if ( false === $max_image_size ) {
		$max_image_size = imagify()->get_public_info();

		if ( ! is_wp_error( $max_image_size ) ) {
			$max_image_size = $max_image_size->max_image_size;
			set_transient( 'imagify_max_image_size', $max_image_size, 6 * HOUR_IN_SECONDS );
		}
	}

	return $max_image_size;
}

/**
 * Check if external requests are blocked for Imagify.
 *
 * @since 1.0
 *
 * return bool True if Imagify API can't be called.
 */
function is_imagify_blocked() {
	if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || ! WP_HTTP_BLOCK_EXTERNAL ) {
		return false;
	}

	if ( ! defined( 'WP_ACCESSIBLE_HOSTS' ) ) {
		return true;
	}

	$accessible_hosts = explode( ',', WP_ACCESSIBLE_HOSTS );
	$accessible_hosts = array_map( 'trim', $accessible_hosts );

	return ! in_array( '*.imagify.io', $accessible_hosts, true );
}

/**
 * Determine if the Imagify API is available by checking the API version.
 *
 * @since 1.0
 *
 * @return bool True if the Imagify API is available.
 */
function is_imagify_servers_up() {
	static $imagify_api_version;

	if ( isset( $imagify_api_version ) ) {
		return $imagify_api_version;
	}

	$transient_name       = 'imagify_check_api_version';
	$transient_expiration = 3 * MINUTE_IN_SECONDS;

	if ( get_site_transient( $transient_name ) ) {
		$imagify_api_version = true;
		return $imagify_api_version;
	}

	if ( is_wp_error( get_imagify_api_version() ) ) {
		set_site_transient( $transient_name, 0, $transient_expiration );

		$imagify_api_version = false;
		return $imagify_api_version;
	}

	set_site_transient( $transient_name, 1, $transient_expiration );

	$imagify_api_version = true;
	return $imagify_api_version;
}
