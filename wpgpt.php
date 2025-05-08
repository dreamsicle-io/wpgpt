<?php
/**
 * Plugin Name:       WPGPT
 * Plugin URI:        https://github.com/dreamsicle-io/wpgpt/
 * Description:       An experimental plugin integrating ChatGPT with WordPress.
 * Version:           0.4.0
 * Requires at least: 6.0.0
 * Requires PHP:      8.1.0
 * Author:            Dreamsicle
 * Author URI:        https://www.dreamsicle.io/
 * License:           UNLICENSED
 * Text Domain:       wpgpt
 * Domain Path:       /languages
 *
 * @package wpgpt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPGPT_PATH', plugin_dir_path( __FILE__ ) );

require WPGPT_PATH . '/vendor/autoload.php';
require WPGPT_PATH . '/includes/class-wpgpt-api.php';
require WPGPT_PATH . '/includes/class-wpgpt-settings.php';
require WPGPT_PATH . '/includes/class-wpgpt-post-generator.php';
require WPGPT_PATH . '/includes/class-wpgpt-elaborator.php';
require WPGPT_PATH . '/includes/class-wpgpt-caption-generator.php';
require WPGPT_PATH . '/includes/class-wpgpt-utils.php';

/**
 * Initialize
 *
 * @since 0.4.0
 */
function wpgpt_init() {
	add_action( 'init', array( new WPGPT_API(), 'init' ), 10 );
	add_action( 'init', array( new WPGPT_Settings(), 'init' ), 10 );
	add_action( 'init', array( new WPGPT_Post_Generator(), 'init' ), 10 );
	add_action( 'init', array( new WPGPT_Elaborator(), 'init' ), 10 );
	add_action( 'init', array( new WPGPT_Caption_Generator(), 'init' ), 10 );
}

add_action( 'init', 'wpgpt_init', 0 );
