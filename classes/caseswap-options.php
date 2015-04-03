<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * Module for the options menus in the backend.
 *
 * This module has no dependencies.
 *
 * @since 4.1.1
 * @global $CSCore->Options
 * @param none
 */

/*
Private Investigator
Background Investigator
Due Diligence Investigator
Missing Person Investigator
Criminal Investigator
Certified Fraud Examiner
*/

if ( !class_exists('CSCore_Options') ) {
  class CSCore_Options {

    // General variables
    public $options_page_slug = 'caseswap-options'; // Slug of the options page
    public $options_page_url = null; // Defined in constructor, use admin_url()

    // Message values that are available. These can be displayed in the backend using the query variable: ?cs_message=MESSAGE_KEY
    public $message_values = array(
      'options-saved' => array(
        'type' => 'updated',
        'text' => 'CaseSwap Options: Your settings have been saved successfully.',
      ),
    );



    /**
     * Set up our options object. This is executed during the plugins_loaded hook.
     */
    public function __construct() {
      $this->options_page_url = admin_url('options-general.php?page=' . $this->options_page_slug);

      add_action( 'admin_menu', array(&$this, 'display_admin_messages') );

      add_action( 'admin_menu', array(&$this, 'save_options_menu'), 8 );
      add_action( 'admin_menu', array(&$this, 'create_options_menu'), 10 );
    }


    /**
     * Gets a message from the "message_values" property based on key.
     *
     * @param null $key
     * @return array|bool
     */
    public function get_message( $key = null ) {
      // Gets the message for the given key from the message_values property.
      if ( $key === null ) return $this->message_values;
      else if ( isset($this->message_values[$key] ) ) return $this->message_values[$key];
      else return false;
    }


    /**
     * Displays a message in the admin dashboard when cs_message is specified, using a message from the "message_values" property.
     */
    public function display_admin_messages() {
      $key = isset($_REQUEST['cs_message']) ? stripslashes($_REQUEST['cs_message']) : false;

      if ( $key !== false ) {
        $msg = $this->get_message( $key );

        global $CSCore;
        $CSCore->admin_message( $msg['type'], $key, $msg['text'], $this->options_page_slug );
      }
    }


    /**
     * Gets the options for the plugin.
     *
     * If $provided_options is specified, options will be formatted using those in place of the options currently stored in the database.
     *
     * @param null $provided_options
     * @return array
     */
    public function get_options( $provided_options = null ) {
      // Gets the options from the database, or if $provided_options is available, uses that instead
      $default_options = array(
        'investigator-types' => array(),
      );

      if ( $provided_options === null ) {
        // Pull in existing options from the database as default
        $provided_options = (array) get_option('caseswap-options');
      }

      $options = shortcode_atts( $default_options, $provided_options, 'caseswap-options' );

      // Investigator types should be an array
      if ( is_string($options['investigator-types']) ) {
        $options['investigator-types'] = preg_split( "/\s*(\r\n|\r|\n)\s*/", $options['investigator-types'] );
      }else if ( !is_array($options['investigator-types'] ) ) {
        $options['investigator-types'] = array();
      }

      return $options;
    }


    /**
     * Creates the menu item under the "Settings" page in the dashboard.
     */
    public function create_options_menu() {
      add_options_page(
        'CaseSwap Options', // page title
        'CaseSwap Options', // menu name
        'edit_theme_options', // Capability Required
        $this->options_page_slug, // Slug
        array( &$this, "render_options_menu" ) // Displaying function
      );
    }


    /**
     * Displays the options menu HTML form when viewing the settings menu.
     */
    public function render_options_menu() {
      global $title;

      $options = $this->get_options();

      ?>
      <div class="wrap">
        <h2><?php echo esc_html($title); ?></h2>

        <form class="caseswap-form" id="caseswap-options-form" action="<?php echo esc_attr($this->options_page_url); ?>" method="post">

          <table class="form-table caseswap-form-table">
            <tbody>

            <!-- Investigator Types -->
            <tr>
              <td style="width: 220px;">
                <strong><label for="cs_options_investigator-types">Investigation Types</label></strong>
                <p class="description"><small>Investigators who sign up may select one or more Investigation Type. When an email is sent, it is sent to any active member who has selected that Investigator Type.</small></p>
              </td>
              <td>
                <textarea class="wide" name="cs_options[investigator-types]" id="cs_options_investigator-types" cols="80" rows="6"><?php
                  echo esc_textarea( implode( "\n", $options['investigator-types'] ) );
                ?></textarea>
                <p class="description">Enter one investigator type per line.</p>
              </td>
            </tr>

            </tbody>
          </table>

          <p class="submit">
            <input name="page" value="<?php echo esc_attr($this->options_page_slug); ?>" type="hidden"/>
            <input name="cs_nonce" value="<?php echo wp_create_nonce( "save-caseswap-options" ); ?>" type="hidden"/>
            <input class="button button-primary" type="submit" value="Save Changes" />
          </p>

        </form>
      </div>
      <?php
    }


    /**
     * Saves the options from the settings menu to the database.
     */
    public function save_options_menu() {
      if ( !isset($_REQUEST['cs_nonce']) ) return;
      if ( !isset($_REQUEST['cs_options']) ) return;
      if ( !wp_verify_nonce( stripslashes($_REQUEST['cs_nonce']), 'save-caseswap-options') ) return;

      $new_options = $this->get_options( stripslashes_deep($_REQUEST['cs_options']) );

      update_option( 'caseswap-options', $new_options );

      wp_redirect( add_query_arg( array('cs_message' => 'options-saved'), $this->options_page_url) );
      exit;
    }
  }
}