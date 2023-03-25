<?php
/**
 * Plugin Name:       WPGPT
 * Plugin URI:        https://github.com/dreamsicle-io/wpgpt/
 * Description:       An experimental plugin integrating ChapGPT with WordPress.
 * Version:           0.1.0
 * Requires at least: 6.0.0
 * Requires PHP:      7.4.0
 * Author:            Dreamsicle
 * Author URI:        https://www.dreamsicle.io/
 * License:           UNLICENSED
 * Text Domain:       wpgpt
 * Domain Path:       /languages
 *
 * @package wpgpt
 */

define( 'WPGPT_PATH', plugin_dir_path( __FILE__ ) );

require WPGPT_PATH . '/vendor/autoload.php';
require WPGPT_PATH . '/includes/class-wpgpt.php';

add_action( 'admin_init', array( new WPGPT(), 'init' ), 0 );
