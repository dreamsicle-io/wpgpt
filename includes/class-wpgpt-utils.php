<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPGPT_Utils {

	public static function get_thickbox_url( array $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'inlineId' => 'wpgpt-thickbox',
				'width'    => 753,
			)
		);
		return '#TB_inline&' . build_query( $args );
	}
}
