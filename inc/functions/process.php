<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Process an image with Imagify.
 *
 * @since 1.0
 *
 * @param   string 	  $file_path 	  Absolute path to the image file.
 * @param   bool   	  $backup 		  Force a backup of the original file.
 * @param   int 	  $is_aggressive  The optimization level (1=aggressive, 0=normal).
 * @return obj|array  Error message | Optimized image data
 */
function do_imagify( $file_path, $backup = false, $is_aggressive = null ) {
	$errors = new WP_Error();
	
	// Check if external HTTP requests are blocked.
	if ( is_imagify_blocked() ) {
		$errors->add( 'http_block_external', __( 'External HTTP requests are blocked', 'imagify' ) );
		return $errors;
	}
	
	// Check that file path isn't empty
	if ( empty( $file_path ) ) {
		$errors->add( 'empty_path', __( 'File path is empty', 'imagify' ) );
		return $errors;
	}

	// Check that the file exists
	if ( ! file_exists( $file_path ) || ! is_file( $file_path ) ) {
		$errors->add( 'file_not_found', sprintf( __( 'Could not find %s', 'imagify' ), $file_path ) );
	}

	// Check that the file is writable
	if ( ! is_writable( dirname( $file_path ) ) ) {
		$errors->add( 'not_writable', sprintf( __( "%s is not writable", 'imagify' ), dirname( $file_path ) ) );
		return $errors;
	}

	// Get file size
	$file_size = file_exists( $file_path ) ? filesize( $file_path ) : 0;

	// Check that file exists
	if ( 0 == $file_size ) {
		$errors->add( 'image_not_found', sprintf( __( 'Skipped (%s), image not found.', 'imagify' ), size_format( $file_size ) ) );
		return $errors;
	}

	/**
	 * Fires before to optimize the Image with Imagify.
	 *
	 * @since 1.0
	 *
	 * @param   string 	$file_path 	Absolute path to the image file.
	 * @param   bool   	$backup 	Force a backup of the original file.
	*/
	do_action( 'before_do_imagify', $file_path, $backup );

	// Send image for optimization and fetch the response
	$response = upload_imagify_image(
		array(
			'image' => curl_file_create( $file_path ),
			'data' 	=> json_encode(
				array(
					'aggressive' => ( is_null( $is_aggressive ) ) ? get_imagify_option( 'optimization_level', 1 ) : $is_aggressive
				)
			)
		)
	);

	// Check status code
	if( is_wp_error( $response ) ) {
		$errors->add( 'api_error', $response->get_error_message() );
		return $errors;
	}

	// Create a backup file
	if ( $backup ) {		
		$backup_path      = get_imagify_attachment_backup_path( $file_path );
		$backup_path_info = pathinfo( $backup_path );

		wp_mkdir_p( $backup_path_info['dirname'] );

		// TO DO - check and send a error message if the backup can't be created
		@copy( $file_path, $backup_path );
	}

	if ( ! function_exists( 'download_url' ) ) {
		require( ABSPATH . 'wp-admin/includes/file.php' );
	}

	$temp_file = download_url( $response->image );

	if ( is_wp_error( $temp_file ) ) {
		$errors->add( 'temp_file_not_found', $temp_file->get_error_message() );
		return $errors;
	}

	@rename( $temp_file, $file_path );

	// If temp file still exists, delete it
	if ( file_exists( $temp_file ) ) {
		unlink( $temp_file );
	}

	/**
	 * Fires after to optimize the Image with Imagify.
	 *
	 * @since 1.0
	 *
	 * @param   string 	$file_path 	Absolute path to the image file.
	 * @param   bool   	$backup 	Force a backup of the original file.
	*/
	do_action( 'after_do_imagify', $file_path, $backup );

	return $response;
}