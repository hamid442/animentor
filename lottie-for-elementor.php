<?php
/**
 * Plugin Name: Lottie for Elementor
 * Plugin URI: https://over-engineer.com/plugins/lottie-for-elementor
 * Description: An Elementor extension to add a Lottie widget
 * Version: 1.0.0
 * Author: overengineer
 * Author URI: https://over-engineer.com/
 * Text Domain: lottie-for-elementor
 * Domain Path: /languages
 * License: GPLv2
 *
 * Lottie for Elementor – An Elementor extension to add a Lottie widget
 * Copyright (c) 2020 over-engineer
 *
 * Lottie for Elementor is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Lottie for Elementor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Lottie for Elementor. If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright 2020 over-engineer
 */

namespace LottieForElementor;

// Prevent direct access to files
if ( ! defined( 'ABSPATH' ) ) exit;

final class Plugin {
  /**
   * Plugin version
   *
   * @var string The plugin version
   */
  const VERSION = '1.0.0';

  /**
   * Minimum Elementor version
   *
   * @var string Minimum Elementor version required to run the plugin
   */
  const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

  /**
   * Minimum PHP version
   *
   * @var string Minimum PHP version required to run the plugin
   */
  const MINIMUM_PHP_VERSION = '7.0';

  /**
   * Instance
   *
   * @var Lottie_Elementor_Extension The single instance of the class
   */
  private static $_instance = null;

  /**
   * Instance
   *
   * Ensures only one instance of the class is loaded or can be loaded
   * 
   * @return Lottie_Elementor_Extension An instance of the class
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Lottie_Elementor_Extension constructor
   */
  public function __construct() {
    add_action( 'init', array( $this, 'load_textdomain' ) );
    add_action( 'plugins_loaded', array( $this, 'init' ) );

    $this->setup_constants();
  }

  /**
   * Load plugin localization files
   */
  public function load_textdomain() {
    load_plugin_textdomain( 'lottie-for-elementor',
      false, // this parameter is deprecated
      dirname( plugin_basename( __FILE__ ) ) . '/languages' );
  }

  /**
   * Add an admin notice to warn about Elementor not being installed/activated
   */
  public function admin_notice_missing_main_plugin() {
    if ( isset( $_GET['activate'] ) ) {
      unset( $_GET['activate'] );
    }

    $message = sprintf(
      /* translators: 1: Plugin name 2: Elementor */
      esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'lottie-for-elementor' ),
      '<strong>' . esc_html__( 'Lottie for Elementor', 'lottie-for-elementor' ) . '</strong>',
      '<strong>' . esc_html__( 'Elementor', 'lottie-for-elementor' ) . '</strong>'
    );
    
    printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
  }

  /**
   * Add an admin notice to warn about old version of Elementor
   */
  public function admin_notice_minimum_elementor_version() {
    if ( isset( $_GET['activate'] ) ) {
      unset( $_GET['activate'] );
    }

    $message = sprintf(
      /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
      esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-test-extension' ),
      '<strong>' . esc_html__( 'Lottie for Elementor', 'elementor-test-extension' ) . '</strong>',
      '<strong>' . esc_html__( 'Elementor', 'elementor-test-extension' ) . '</strong>',
      self::MINIMUM_ELEMENTOR_VERSION
    );

    printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
  }

  /**
   * Add an admin notice to warn about old version of PHP
   */
  public function admin_notice_minimum_php_version() {
    if ( isset( $_GET['activate'] ) ) {
      unset( $_GET['activate'] );
    }

    $message = sprintf(
      /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
      esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'lottie-for-elementor' ),
      '<strong>' . esc_html__( 'Lottie for Elementor', 'lottie-for-elementor' ) . '</strong>',
      '<strong>' . esc_html__( 'PHP', 'lottie-for-elementor' ) . '</strong>',
      self::MINIMUM_PHP_VERSION
    );

    printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
  }
  
  /**
   * Initialize widgets
   * Include widget files and register them
   */
  public function init_widgets() {
    // Include widget files
    require_once( __DIR__ . '/widgets/lottie-widget.php' );

    // Register widget
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \LottieForElementor\Widgets\Lottie() );
  }

  /**
   * Initialize the plugin
   * Fire once all activated plugins have loaded
   */
  public function init() {
    // Check if Elementor is installed and activated
    if ( ! did_action( 'elementor/loaded' ) ) {
      add_action( 'admin_notices', array( $this, 'admin_notice_missing_main_plugin' ) );
      return;
    }

    // Check for required Elementor version
    if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
      add_action( 'admin_notices', array( $this, 'admin_notice_minimum_elementor_version' ) );
      return;
    }

    // Check for required PHP version
    if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
      add_action( 'admin_notices', array( $this, 'admin_notice_minimum_php_version' ) );
      return;
    }

    // Include files
    require_once( __DIR__ . '/json-handler.php' );

    // Allow JSON uploads
    new \LottieForElementor\Json_Handler();

    // Add plugin actions
    add_action( 'elementor/widgets/widgets_registered', array( $this, 'init_widgets' ) );
  }

  /**
   * Setup plugin constants
   */
  private function setup_constants() {
    if ( ! defined( 'LottieForElementor\VERSION' ) ) {
      define( 'LottieForElementor\VERSION', self::VERSION );
    }

    if ( ! defined( 'LottieForElementor\PLUGIN_DIR' ) ) {
      define( 'LottieForElementor\PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }

    if ( ! defined( 'LottieForElementor\PLUGIN_URL' ) ) {
      define( 'LottieForElementor\PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }

    if ( ! defined( 'LottieForElementor\PLUGIN_FILE' ) ) {
      define( 'LottieForElementor\PLUGIN_FILE', __FILE__ );
    }
  }
}

// Initialize the plugin
Plugin::instance();
