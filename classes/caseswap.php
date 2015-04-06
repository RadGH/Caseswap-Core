<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * The primary class for the CaseSwap Core plugin.
 *
 * This class is responsible for initializing all other classes and modules, and many general tasks.
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

  // Objects.
  public $Form = false; // Core module - Manages the front end form and submission
  public $Options = false; // Core module - Manages the options menus in the backend
  public $Membership = false; // Plugin module - Memberships Premium (by WPMUDev)

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

    add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_admin_scripts' ) );
    add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_theme_scripts' ) );
  }

  /**
   * $this->plugins_loaded()
   *
   * Fires when plugins have loaded. We can test if other required plugins are active and initialize modules based on the result.
   *
   * @since 4.1.1
   */
  public function plugins_loaded() {
    // Initialize Paid Memberships Pro module, or give a warning
    if ( !function_exists('set_membership_url') ) {
      add_action( 'admin_notices', array( &$this, 'admin_warning_no_plugin_membership_premium' ) );
    }else{
      require_once( CSCore_PATH . '/classes/caseswap-membership-premium.php' );

      $this->Membership = new CSCore_Members();
    }

    // Core modules. No dependencies.
    require_once(CSCore_PATH . '/classes/caseswap-options.php');
    $this->Options = new CSCore_Options();

    require_once(CSCore_PATH . '/classes/caseswap-form.php');
    $this->Form = new CSCore_Form();

    do_action( 'caseswap_modules_loaded', $this );
  }

  /**
   * $this->init_general()
   *
   * Fires on init. General settings.
   */
  public function init_general() {

  }

  /**
   * $this->enqueue_admin_scripts()
   *
   * Adds our plugin JavaScript and Stylesheet to the admin
   */
  public function enqueue_admin_scripts() {
    wp_enqueue_script( "caseswap_admin", CSCore_URL . '/includes/cs_admin.js', array( 'jquery' ), CSCore_VERSION );
    wp_enqueue_style( "caseswap_admin", CSCore_URL . '/includes/cs_admin.css', false, CSCore_VERSION );
  }

  /**
   * $this->enqueue_theme_scripts()
   *
   * Adds our plugin JavaScript and Stylesheet to the front end theme
   */
  public function enqueue_theme_scripts() {
    wp_enqueue_script( "caseswap_theme", CSCore_URL . '/includes/cs_theme.js', array( 'jquery' ), CSCore_VERSION );
    wp_enqueue_style( "caseswap_theme", CSCore_URL . '/includes/cs_theme.css', false, CSCore_VERSION );
  }

  /**
   * $this->admin_warning_no_plugin_membership_premium()
   *
   * Displays an error that Membership Premium has not been loaded.
   */
  public function admin_warning_no_plugin_membership_premium() {
    ?>
    <div class="error">
      <p><strong>CaseSwap Core:</strong> The plugin Membership Premium (by WPMU Dev) is not active. The membership module will not be loaded.</p>
    </div>
    <?php
  }

  /**
   * Displays an admin message of type  "updated" or "error".
   *
   * You can specify a type (updated/error), error code, textual message and a slug to identify the error. Or, you can simply pass one WP_Error object as the only parameter.
   *
   * @param (string or WP_Error) $type
   * @param null $error_code
   * @param null $text
   * @param string $slug
   */
  public function admin_message( $type, $error_code = null, $text = null, $slug = 'caseswap' ) {
    if ( is_object($type) && is_wp_error($type) ) {
      // Retrieve values from WP_Error object
      /** @noinspection PhpUndefinedMethodInspection */
      $error_code = $type->get_error_code();
      /** @noinspection PhpUndefinedMethodInspection */
      $text = $type->get_error_message();
      $type = 'error';
    }else{
      // Verify type/text values
      $type = in_array( strtolower($type), array('updated', 'error') ) ? strtolower($type) : false;
      $text = is_string($text) ? $text : "";
    }

    // Type should be either "updated" or "error", otherwise we'll append a notice to the message
    if ( $type === false ) {
      $type = 'error';
      $text .= " <em>Notice: Invalid admin message type provided. Valid options: <code>updated</code>, <code>error</code>.</em>";
    }

    // Let the Settings API handle the rest!
    add_settings_error( $slug, $error_code, $text, $type );
  }

}
}