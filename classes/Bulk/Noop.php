<?php
namespace Imagify\Bulk;

defined( 'ABSPATH' ) || die( 'Cheatin’ uh?' );

/**
 * Falback class for bulk.
 *
 * @since  1.9
 * @author Grégory Viguier
 */
class Noop implements BulkInterface {

	/**
	 * Get all unoptimized media ids.
	 *
	 * @since  1.9
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  int $optimization_level The optimization level.
	 * @return array                   A list of unoptimized media. Array keys are media IDs prefixed with an underscore character, array values are the main file’s URL.
	 */
	public function get_unoptimized_media_ids( $optimization_level ) {
		return [];
	}

	/**
	 * Get ids of all optimized media without webp versions.
	 *
	 * @since  1.9
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array A list of media. Array keys are media IDs prefixed with an underscore character, array values are the main file’s URL.
	 */
	public function get_optimized_media_ids_without_webp() {
		return [];
	}

	/**
	 * Get the context data.
	 *
	 * @since  1.9
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array {
	 *     The formated data.
	 *
	 *     @type string $count-optimized Number of media optimized.
	 *     @type string $count-errors    Number of media having an optimization error, with a link to the page listing the optimization errors.
	 *     @type string $optimized-size  Optimized filesize.
	 *     @type string $original-size   Original filesize.
	 * }
	 */
	public function get_context_data() {
		$data = [
			'count-optimized' => 0,
			'count-errors'    => 0,
			'optimized-size'  => 0,
			'original-size'   => 0,
			'errors_url'      => get_imagify_admin_url( 'folder-errors', 'noop' ),
		];

		return $this->format_context_data( $data );
	}
}
