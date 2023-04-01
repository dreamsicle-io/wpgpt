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
	 * Get Current URL
	 *
	 * @since 0.3.0
	 * @return string The current URL.
	 */
	public static function get_current_url(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$params = ! empty( $_GET ) ? wp_unslash( $_GET ) : array();
		$server = ! empty( $_SERVER ) ? wp_unslash( $_SERVER ) : array();
		$base   = ! empty( $server['REQUEST_URI'] ) ? $server['REQUEST_URI'] : '';
		return add_query_arg( $params, home_url( $base ) );
	}

	/**
	 * Get Thickbox URL
	 *
	 * @since 0.1.0
	 * @param array $params An array of params to be used in the thickbox URL.
	 * @return string The thickbox URL.
	 */
	public static function get_thickbox_url( array $params ): string {
		$current_url  = self::get_current_url();
		$params       = wp_parse_args(
			$params,
			array(
				'inlineId' => 'wpgpt-thickbox',
				'width'    => 753,
			)
		);
		$param_string = build_query( $params );

		// Inline thickbox links break when a `?` sep is aready in the URL,
		// so use `&` as the sep instead if `?` is found in the URL.
		$sep = '?';
		if ( str_contains( $current_url, '?' ) ) {
			$sep = '&';
		}

		return "#TB_inline{$sep}{$param_string}";
	}
}
