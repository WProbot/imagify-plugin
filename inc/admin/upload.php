<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

add_filter( 'manage_media_columns', '_imagify_manage_media_columns' );
/**
 * Add "Imagify" column in upload.php.
 *
 * @since 1.0
 * @author Jonathan Buttigieg
 *
 * @param  array $columns An array of columns displayed in the Media list table.
 * @return array
 */
function _imagify_manage_media_columns( $columns ) {
	$columns['imagify_optimized_file'] = __( 'Imagify', 'imagify' );
	return $columns;
}

add_action( 'manage_media_custom_column', '_imagify_manage_media_custom_column', 10, 2 );
/**
 * Add content to the "Imagify" columns in upload.php.
 *
 * @since 1.0
 * @author Jonathan Buttigieg
 *
 * @param string $column_name   Name of the custom column.
 * @param int    $attachment_id Attachment ID.
 */
function _imagify_manage_media_custom_column( $column_name, $attachment_id ) {
	if ( 'imagify_optimized_file' !== $column_name ) {
		return;
	}

	$class_name = get_imagify_attachment_class_name( 'wp', $attachment_id, 'manage_media_custom_column' );
	$attachment = new $class_name( $attachment_id );

	echo get_imagify_media_column_content( $attachment );
}

add_action( 'restrict_manage_posts', '_imagify_attachments_filter_dropdown' );
/**
 * Adds a dropdown that allows filtering on the attachments Imagify status.
 *
 * @since 1.0
 * @author Jonathan Buttigieg
 */
function _imagify_attachments_filter_dropdown() {
	if ( 'upload.php' !== $GLOBALS['pagenow'] ) {
		return;
	}

	$optimized   = imagify_count_optimized_attachments();
	$unoptimized = imagify_count_unoptimized_attachments();
	$errors      = imagify_count_error_attachments();
	$status      = isset( $_GET['imagify-status'] ) ? $_GET['imagify-status'] : 0; // WPCS: CSRF ok.
	$options     = array(
		'optimized'   => __( 'Optimized','imagify' ),
		'unoptimized' => __( 'Unoptimized','imagify' ),
		'errors'      => __( 'Errors','imagify' ),
	);

	echo '<label class="screen-reader-text" for="filter-by-optimization-status">' . __( 'Filter by status','imagify' ) . '</label>';
	echo '<select id="filter-by-optimization-status" name="imagify-status">';
		echo '<option value="0" selected="selected">' . __( 'All images','imagify' ) . '</option>';

	foreach ( $options as $value => $label ) {
		echo '<option value="' . $value . '" ' . selected( $status, $value, false ) . '>' . $label . ' (' . ${$value} . ')</option>';
	}
	echo '</select>&nbsp;';
}

add_filter( 'request', '_imagify_sort_attachments_by_status' );
/**
 * Modify the query based on the imagify-status variable in $_GET.
 *
 * @since 1.0
 * @author Jonathan Buttigieg
 *
 * @param  array $vars The array of requested query variables.
 * @return array
 */
function _imagify_sort_attachments_by_status( $vars ) {
	if ( 'upload.php' !== $GLOBALS['pagenow'] || empty( $_GET['imagify-status'] ) ) { // WPCS: CSRF ok.
		return $vars;
	}

	$status       = $_GET['imagify-status']; // WPCS: CSRF ok.
	$meta_key     = '_imagify_status';
	$meta_compare = '=';
	$relation     = array();

	switch ( $status ) {
		case 'unoptimized':
			$meta_key     = '_imagify_data';
			$meta_compare = 'NOT EXISTS';
			break;
		case 'optimized':
			$status   = 'success';
			$relation = array(
				'key'     => $meta_key,
				'value'   => 'already_optimized',
				'compare' => $meta_compare,
			);
			break;
		case 'errors':
			$status = 'error';
			break;
		default:
			return $vars;
	}

	$vars = array_merge( $vars, array(
		'meta_query' => array(
			'relation' => 'or',
			array(
				'key'     => $meta_key,
				'value'   => $status,
				'compare' => $meta_compare,
			),
			$relation,
		),
	) );

	$vars['post_mime_type'] = get_imagify_mime_type();

	return $vars;
}
