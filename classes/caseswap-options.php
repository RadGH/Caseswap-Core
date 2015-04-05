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
        'case-form' => "",
        'investigator-types' => array(),
        'states' => array(
          "Alabama",
          "Alaska",
          "Arizona",
          "Arkansas",
          "California",
          "Colorado",
          "Connecticut",
          "Delaware",
          "Florida",
          "Georgia",
          "Hawaii",
          "Idaho",
          "Illinois",
          "Indiana",
          "Iowa",
          "Kansas",
          "Kentucky",
          "Louisiana",
          "Maine",
          "Maryland",
          "Massachusetts",
          "Michigan",
          "Minnesota",
          "Mississippi",
          "Missouri",
          "Montana",
          "Nebraska",
          "Nevada",
          "New Hampshire",
          "New Jersey",
          "New Mexico",
          "New York",
          "North Carolina",
          "North Dakota",
          "Ohio",
          "Oklahoma",
          "Oregon",
          "Pennsylvania",
          "Rhode Island",
          "South Carolina",
          "South Dakota",
          "Tennessee",
          "Texas",
          "Utah",
          "Vermont",
          "Virginia",
          "Washington",
          "West Virginia",
          "Wisconsin",
          "Wyoming",
          "District of Columbia",
          "Puerto Rico",
          "Guam",
          "American Samoa",
          "U.S. Virgin Islands",
          "Northern Mariana Islands"
        )
      );

      if ( $provided_options === null ) {
        // Pull in existing options from the database as default
        $provided_options = (array) get_option('caseswap-options');
      }

      foreach( $default_options as $key => $value ) {
        if ( !isset($provided_options[$key]) ) continue;

        // Arrays which have been provided as strings will be split by new lines. Each line will be trimmed of leading/trailing space.
        if ( is_array($default_options[$key]) && is_string($provided_options[$key]) ) {
          $provided_options[$key] = preg_split( "/\s*(\r\n|\r|\n)+\s*/", trim($provided_options[$key])  );
        }

        // Ensure an array is always returned as an array. False or null will become empty, while anything else will use type casting.
        if ( is_array($default_options[$key]) ) {
          if ( $provided_options[$key] === false || $provided_options[$key] === null ) {
            $provided_options[$key] = array();
          }else{
            $provided_options[$key] = (array) $provided_options[$key];
          }
        }
      }

      $options = shortcode_atts( $default_options, $provided_options, 'caseswap-options' );

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

      $args = array(
        'post_type' => 'wpcf7_contact_form',
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'asc',
      );
      $contact_forms = get_posts( $args );

      ?>
      <div class="wrap">
        <h2><?php echo esc_html($title); ?></h2>

        <form class="caseswap-form" id="caseswap-options-form" action="<?php echo esc_attr($this->options_page_url); ?>" method="post">

          <table class="form-table caseswap-form-table">
            <tbody>

            <!-- Select: Submit Case (Contact Form) -->
            <tr>
              <td style="width: 220px;">
                <strong><label for="cs_options_case-form">New Case Form</label></strong>
                <p class="description"><small>Managed by <a href="<?php echo esc_attr( admin_url('admin.php?page=wpcf7') ); ?>">Contact Form 7</a>.</small></p>
              </td>
              <td>
                <select name="cs_options[case-form]" id="cs_options_case-form">
                  <option value="">&ndash; Select &ndash;</option>
                  <?php
                  foreach( $contact_forms as $post ) {
                    echo sprintf(
                      '<option value="%s" %s>%s</option>',
                      esc_attr($post->ID),
                      selected($post->ID, $options['case-form'], false),
                      esc_html($post->post_title)
                    );
                  }
                  ?>
                </select>

                <p class="hide-if-no-js"><a href="#" onclick="jQuery('#cf7-form-help').show(); jQuery(this).hide(); return false">View Contact Form Requirements</a></p>

                <div id="cf7-form-help" class="hide-if-js">
                  <p>Contact form should have the following fields:</p>

                  <ul class="ul-disc">
                    <li><code>name</code> (Text)</li>
                    <li><code>email</code> (Text)</li>
                    <li><code>investigator_type</code> (* Dropdown)</li>
                    <li><code>state</code> (* Dropdown)</li>
                    <li><code>message</code> (Textarea)</li>
                    <li><code>contact_method</code> (Text)</li>
                  </ul>

                  <p class="description">* These fields will have values added automatically using the fields below. Any value you set will be overwritten.</p>
                </div>
              </td>
            </tr>

            <!-- Textarea: Investigator Types -->
            <tr>
              <td style="width: 220px;">
                <strong><label for="cs_options_investigator-types">Investigation Types</label></strong>
                <p class="description"><small>Investigators who sign up may select one or more Investigation Type. When an email is sent, it is sent to any active member who has selected that Investigator Type.</small></p>
              </td>
              <td>
                <textarea class="wide" name="cs_options[investigator-types]" id="cs_options_investigator-types" cols="80" rows="6"><?php
                  echo esc_textarea( implode( "\n", $options['investigator-types'] ) );
                ?></textarea>
                <p class="description">One investigator type per line.</p>
              </td>
            </tr>

            <!-- Textarea: States -->
            <tr>
              <td style="width: 220px;">
                <strong><label for="cs_options_states">States</label></strong>
                <p class="description"><small>These states will be available for investigators during sign up. Only states which have at least one investigator will appear for visitors submitting a new case.</small></p>
              </td>
              <td>
                <textarea class="wide" name="cs_options[states]" id="cs_options_states" cols="80" rows="6"><?php
                  echo esc_textarea( implode( "\n", $options['states'] ) );
                ?></textarea>
                <p class="description">One state per line.</p>
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