<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * The primary class for the CaseSwap Core plugin.
 *
 * This class is reponsible for initializing all other classes and modules, and many general tasks.
 *
 * @since 4.1.1
 * @global $CSCore
 * @param none
 */

if ( !class_exists('CSCore') ) {
class CSCore {

  /**
   * Variables used by the plugin.
   */

  // Plugin variables. These will be instantiated into objects of other php module classes during the plugins_loaded event.
  public $CF7 = false; // Contact Form 7
  public $Members = false; // Paid Memberships Pro (by WPMUDev)

  /**
   * $this->__construct()
   *
   * Load different modules based on plugin availability.
   * Display errors if a required plugin is not installed/activated.
   *
   * @since 4.1.1
   */
  public function __construct() {
    add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ) );

    add_action( 'init', array( &$this, 'init_general' ) );
  }

  /**
   * $this->plugins_loaded()
   *
   * Fires when plugins have loaded. We can test if other required plugins are active and initialize modules based on the result.
   *
   * @since 4.1.1
   */
  public function plugins_loaded() {
    // Initialize Contact Form 7 module, or give a warning
    if ( !defined('WPCF7_VERSION') ) {
      add_action( 'admin_notices', array( &$this, 'admin_warning_no_plugin_cf7' ) );
    }else{
      require_once( CSCore_PATH . '/classes/caseswap-cf7.php' );

      $this->CF7 = new CSCore_CF7();
    }


    // Initialize Paid Memberships Pro module, or give a warning
    if ( !defined('WPCF7_VERSION') ) {
      add_action( 'admin_notices', array( &$this, 'admin_warning_no_plugin_pmp' ) );
    }else{
      require_once(CSCore_PATH . '/classes/caseswap-members.php');

      $this->Members = new CSCore_Members();
    }
  }

  /**
   * $this->init_general()
   *
   * Fires on init. General settings.
   */
  public function init_general() {

  }

  /**
   * $this->admin_warning_no_plugin_cf7()
   *
   * Displays an error that Contact Form 7 has not been loaded.
   */
  public function admin_warning_no_plugin_cf7() {
    ?>
    <div class="error">
      <p><strong>CaseSwap Core:</strong> The plugin Contact Form 7 was not detected. The Contact Form 7 module will not be available.</p>
    </div>
    <?php
  }

}
}