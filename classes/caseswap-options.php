<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * Responsible for displaying the options menu in the backend.
 *
 * This module has no dependencies.
 *
 * @since 4.1.1
 * @global $CSCore->Options
 * @param none
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

    public $option_pages = array(
      'settings' => array(
        'name' => 'Settings',
        'file' => '/options/settings.php',
        'fields' => array(
          'investigator-types',
          'states',
        ),
      ),
      'email' => array(
        'name' => 'Email',
        'file' => '/options/email.php',
        'fields' => array(
          'mail_from_email',
          'mail_from_name',
          'mail_return_path',
          'smtp_enabled',
          'smtp_user',
          'smtp_pass',
          'smtp_host',
          'smtp_port',
          'smtp_ssl',
          'smtp_auth',
        ),
      ),
      'new-case-template' => array(
        'name' => 'New Case Template',
        'file' => '/options/new-case-template.php',
        'fields' => array(
          'mail_template_new_case',
          'mail_template_new_case_autop',
        ),
      ),
      /*
      'content' => array(
        'name' => 'Content',
        'file' => '/options/content.php',
        'fields' => array(
        ),
      ),
      */
      'cf7' => array(
        'name' => 'Contact Form 7',
        'file' => '/options/cf7.php',
        'fields' => array(
          'cf7-form-id',
          'cf7-success-page-id',
          'cf7-state-key',
          'cf7-investigator-key',
        ),
      ),
      'membership' => array(
        'name' => 'Membership Premium',
        'file' => '/options/membership.php',
        'fields' => array(
          'membership-subscription-level',
        ),
      ),
    );

    public $default_options = array(
      // SMTP
      'mail_from_email'  => "",          // Global default email, which replaces Wordpress@example.org
      'mail_from_name'   => "",          // Global default name, which replaces the name "WordPress" as the sender
      'mail_return_path' => "",          // If set, the Return-Path can be changed. Otherwise the From address will be used.

      'smtp_enabled'     => "",          // Boolean: If not empty, SMTP will be used
      'smtp_user'        => "",          // SMTP user credentials
      'smtp_pass'        => "",          // SMTP user credentials
      'smtp_host'        => "localhost", // String: Hostname of SMTP server
      'smtp_port'        => "25",        // Number: Port used for SMTP server
      'smtp_ssl'         => "none",      // String: none, ssl, tls
      'smtp_auth'        => "",          // Boolean: If not empty, authentication is required

      // New Case Template
      'mail_template_new_case' => "",         // String: Multiline string which makes up the email template used for new cases
      'mail_template_new_case_autop' => "", // Boolean: If not empty, template will use wpautop() to add paragraphs

      // Contact Form 7
      'cf7-form-id'          => "",      // ID of the contact form (post object) used for "Submit Your Case"
      'cf7-success-page-id'  => "",      // Page to redirect to when user fills out contact form
      'cf7-state-key'        => "",
      'cf7-investigator-key' => "",

      // Membership Premium
      'membership-subscription-level' => "", // ID of subscription level required to receive submitted cases

      // General Settings
      'investigator-types' => array(), // Array of strings (submitted as line-separated string)
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



    /**
     * Set up our options object.
     */
    public function __construct() {
      // This is in the plugins_loaded event. You can use init hooks here.
      $this->options_page_url = admin_url('options-general.php?page=' . $this->options_page_slug);

      add_action( 'admin_menu', array(&$this, 'display_admin_messages') );

      add_action( 'admin_menu', array(&$this, 'save_options_menu'), 8 );
      add_action( 'admin_menu', array(&$this, 'create_options_menu'), 10 );

      // Adds "Settings" link to the custon settings page
      add_filter( 'plugin_action_links', array(&$this, 'plugin_settings_link'), 10, 2);
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
      if ( $provided_options === null ) {
        // Pull in existing options from the database as default
        $provided_options = (array) get_option('caseswap-options');
      }

      foreach( $this->default_options as $key => $value ) {
        if ( !isset($provided_options[$key]) ) continue;

        // Arrays which have been provided as strings will be split by new lines. Each line will be trimmed of leading/trailing space.
        if ( is_array($this->default_options[$key]) && is_string($provided_options[$key]) ) {
          $provided_options[$key] = preg_split( "/\s*(\r\n|\r|\n)+\s*/", trim($provided_options[$key])  );
        }

        // Ensure an array is always returned as an array. False or null will become empty, while anything else will use type casting.
        if ( is_array($this->default_options[$key]) ) {
          if ( $provided_options[$key] === false || $provided_options[$key] === null ) {
            $provided_options[$key] = array();
          }else{
            $provided_options[$key] = (array) $provided_options[$key];
          }
        }
      }

      $options = shortcode_atts( $this->default_options, $provided_options, 'caseswap-options' );

      return $options;
    }


    /**
     * Creates the menu item under the "Settings" page in the dashboard.
     */
    public function create_options_menu() {
      add_options_page(
        'CaseSwap Core', // page title
        'CaseSwap Core', // menu name
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

      $section = isset($_REQUEST['cs_page']) ? stripslashes( $_REQUEST['cs_page'] ) : 'settings';

      if ( !isset($this->option_pages[$section]) ) $section = 'settings';

      $template = $this->option_pages[$section]['file'];

      ?>
      <div class="wrap">
        <h2><?php echo esc_html($title); ?></h2>

        <br/>

        <h2 class="nav-tab-wrapper">
          <?php
          foreach( $this->option_pages as $key => $tab ) {
            echo sprintf(
              '<a href="%s" class="nav-tab %s">%s</a>',
              add_query_arg( array( 'cs_page' => $key ), $this->options_page_url ),
              $section == $key ? 'nav-tab-active' : '',
              $tab['name']
            );
          }
          ?>
        </h2>

        <form class="caseswap-form" id="caseswap-options-form" action="<?php echo esc_attr( add_query_arg( array( 'cs_page' => $section ), $this->options_page_url ) ); ?>" method="post">

          <input name="page" value="<?php echo esc_attr($this->options_page_slug); ?>" type="hidden"/>
          <input name="cs_page" value="<?php echo esc_attr($section); ?>" type="hidden"/>
          <input name="cs_nonce" value="<?php echo wp_create_nonce( "save-caseswap-options" ); ?>" type="hidden"/>

          <?php include( CSCore_PATH . $template ); ?>

          <p class="submit">
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
      if ( !isset($_REQUEST['cs_page']) ) return;
      if ( !wp_verify_nonce( stripslashes($_REQUEST['cs_nonce']), 'save-caseswap-options') ) return;

      $section = stripslashes( $_REQUEST['cs_page'] );
      if ( !isset($this->option_pages[$section]) ) return;

      $options = $this->get_options();
      $submitted = $this->get_options( stripslashes_deep($_REQUEST['cs_options']) );

      // We only want to save the updated options for the page we clicked "Save changes" for.
      foreach( $this->option_pages[$section]['fields'] as $key ) {
        if ( isset($submitted[$key]) ) {
          $options[$key] = $submitted[$key];
        }else{
          $options[$key] = false;
        }
      }

      // Save the updated options
      update_option( 'caseswap-options', $options );

      $args = apply_filters( 'caseswap-options-saved-redirect-args', array('cs_page' => $section, 'cs_message' => 'options-saved'), $this );

      wp_redirect( add_query_arg( $args, $this->options_page_url) );
      exit;
    }

    /**
     * Adds a "Settings" link to the plugin page, pointing to our custom options page
     *
     * @param $links
     * @param $file
     */
    public function plugin_settings_link( $links, $file ) {
      if ( $file != "caseswap-core/caseswap-core.php" ) return $links;

      $settings_link = '<a href="'. esc_attr(admin_url('options-general.php?page=caseswap-options')) .'">Settings</a>';

      array_unshift( $links, $settings_link );

      return $links;
    }
  }
}