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
  public $Options = false; // Core module - Manages the options menus in the backend
  public $Users = false; // Core module - Adds and manages custom fields for investigator's user profiles
  public $Membership = false; // Plugin module - Memberships Premium (by WPMUDev)
  public $CF7 = false; // Plugin module - Contact Form 7 Integration

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
    if ( !defined('WPCF7_PLUGIN_DIR') ) {
      add_action( 'admin_notices', array( &$this, 'admin_warning_no_plugin_cf7' ) );
    }else{
      require_once( CSCore_PATH . '/classes/caseswap-cf7.php' );

      $this->CF7 = new CSCore_CF7();
    }


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

    require_once(CSCore_PATH . '/classes/caseswap-email.php');
    $this->Email = new CSCore_Email();

    require_once(CSCore_PATH . '/classes/caseswap-users.php');
    $this->Users = new CSCore_Users();

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
    // CodeMirror - Syntax highlighting for textareas (Used on email template options pages)
    wp_enqueue_script( "codemirror", CSCore_URL . '/includes/codemirror.js', array(), '5.1' );
    wp_enqueue_style( "codemirror", CSCore_URL . '/includes/codemirror.css', false, '5.1' );

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
   * $this->admin_warning_no_plugin_cf7()
   *
   * Displays an error that Contact Form 7 has not been loaded.
   */
  public function admin_warning_no_plugin_cf7() {
    ?>
    <div class="error">
      <p><strong>CaseSwap Core:</strong> The plugin Contact Form 7 is not active. Contact Form 7 integration will not be loaded.</p>
    </div>
    <?php
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


  public function get_investigators( $states, $types, $count_only = false ) {
    global $wpdb;

    // Create list for states/types array comparison
    $wstate = array();
    foreach( (array) $states as $s ) {
      $wstate[] = "'" . esc_sql($s) . "'"; // 'Oregon'
    }
    $where_states = implode(", ", $wstate); // 'Oregon', 'California'

    $wtype = array();
    foreach( (array) $types as $s ) {
      $wtype[] = "'" . esc_sql($s) . "'"; // 'Private Investigator'
    }
    $where_types = implode(", ", $wtype); // 'Private Investigator', 'Due Diligence Investigator'


    // SQL to look up all users with a matching state and type, returns user IDs
    $sql = "
SELECT u.ID

FROM {$wpdb->users} u

JOIN {$wpdb->usermeta} types
  ON  ( types.user_id = u.ID )
  AND ( types.meta_key = 'investigator-types' )

JOIN {$wpdb->usermeta} states
  ON  ( states.user_id = u.ID )
  AND ( states.meta_key = 'state' )

WHERE
  ( states.meta_value IN ( {$where_states} ) )
	AND
	( types.meta_value IN ( {$where_types} ) )
	AND
	( u.user_email != '' )

GROUP BY u.ID

LIMIT 2000;";

    // Get results as a flat array: [0, 1, 2]
    $results = $wpdb->get_col( $sql );

    // Allow filtering results further via plugins / modules
    $results = apply_filters( 'cscore_filter_matching_investigators', $results, $this );

    // Return the results
    return $results;
  }

}
}