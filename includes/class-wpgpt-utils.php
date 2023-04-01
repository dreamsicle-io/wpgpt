<?php
/**
 * WPGPT Utils
 *
 * @package wpgpt
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPGPT Utils
 *
 * @since 0.1.0
 */
class WPGPT_Utils {

	/**
	 * Get Thickbox URL
	 *
	 * @since 0.1.0
	 * @param array $params An array of params to be used in the thickbox URL.
	 * @return string The thickbox URL.
	 */
	public static function get_thickbox_url( array $params ): string {
		$params = wp_parse_args(
			$params,
			array(
				'inlineId' => 'wpgpt-thickbox',
				'width'    => 753,
			)
		);
		return '#TB_inline&' . build_query( $params );
	}
}
