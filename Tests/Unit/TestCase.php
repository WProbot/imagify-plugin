<?php
/**
 * Test Case for all of the unit tests.
 *
 * @package Imagify\Tests\Unit
 */

namespace Imagify\Tests\Unit;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Brain\Monkey;
use Imagify\Tests\TestCaseTrait;
use WP_Error;

abstract class TestCase extends PHPUnitTestCase {
	use TestCaseTrait;

	/**
	 * Prepares the test environment before each test.
	 */
	protected function setUp() {
		parent::setUp();
		Monkey\setUp();

		$this->mockCommonWpFunctions();
	}

	/**
	 * Cleans up the test environment after each test.
	 */
	protected function tearDown() {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Mock common WP functions.
	 */
	protected function mockCommonWpFunctions() {
		Monkey\Functions\stubs(
			[
				'__',
				'esc_attr__',
				'esc_html__',
				'_x',
				'esc_attr_x',
				'esc_html_x',
				'_n',
				'_nx',
				'esc_attr',
				'esc_html',
				'esc_textarea',
				'esc_url',
			]
		);

		$functions = [
			'_e',
			'esc_attr_e',
			'esc_html_e',
			'_ex',
		];

		foreach ( $functions as $function ) {
			Monkey\Functions\when( $function )->echoArg();
		}

		include_once IMAGIFY_PLUGIN_TESTS_ROOT . '../Fixtures/WP/class-wp-error.php';

		Monkey\Functions\when( 'is_wp_error' )
			->alias( function( $thing ) {
				return $thing instanceof WP_Error;
			} );
	}
}
