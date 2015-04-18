<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * Allows wp_mail() to use an SMTP account to send email instead of regular PHP mail(). Based on WP Mail SMTP by Callum Macdonald.
 *
 * Options configured by $CSCore->Options
 *
 * @since 4.1.1
 * @global $CSCore->Email
 * @param none
 */

if ( !class_exists('CSCore_Email') ) {
  class CSCore_Email {

    public $mail_settings = array (
      'mail_from_email'  => null,
      'mail_from_name'   => null,
      'mail_return_path' => null,

      'smtp_enabled'     => null,
      'smtp_host'        => null,
      'smtp_port'        => null,
      'smtp_ssl'         => null,
      'smtp_auth'        => null,
      'smtp_user'        => null,
      'smtp_pass'        => null,
    );

    /**
     * Set up our options object.
     */
    public function __construct() {
      // Replace the default from name/email with our options, if set
      add_filter( 'wp_mail_from', array( &$this, 'mail_default_from_email') );
      add_filter( 'wp_mail_from_name', array( &$this, 'mail_default_from_name') );
      add_action( 'phpmailer_init', array( &$this, 'phpmailer_init') );

      add_filter( 'caseswap-options-saved-redirect-args', array( &$this, 'options_saved_test_email') );

      // Displays an email template in the browser, or sends to email, depending on action.
      // These is linked to from the options page ($CSCore->Options)
      add_action( 'admin_init', array(&$this, 'preview_email_template') );
    }

    /**
     * Allows a link to preview email templates, either in browser or by sending to the logged in user's email address.
     */
    public function preview_email_template() {
      if ( !isset($_REQUEST['cs_preview_nonce']) ) return;
      $nonce = $_REQUEST['cs_preview_nonce'];

      $user = wp_get_current_user();

      $tags = array(
        '[name]' => 'John Doe',
        '[email]' => 'jdoe@example.org',
        '[type]' => 'Due Diligence Investigator',
        '[state]' => 'Oregon',
        '[message]' => 'Hello, this is a test message!',
        '[contact_method]' => 'Please call me at 555-555-5555',

        /*
        '[investigator_first_name]' => $user->get('first_name'),
        '[investigator_last_name]' => $user->get('last_name'),
        '[investigator_login]' => $user->get('user_login'),
        '[investigator_email]' => $user->get('user_email'),
        '[investigator_id]' => $user->ID,
        */
      );

      $template = $this->get_email_template( 'mail_template_new_case', $tags );

      if ( wp_verify_nonce( $nonce, 'preview-email-display') ) {
        // Just display the template and abort. No other markup should be displayed.
        echo $template;
        exit;
      }

      if ( wp_verify_nonce( $nonce, 'preview-email-send') ) {
        // Send the template in an email to the logged in user.
        $sent = wp_mail( $user->get('user_email'), '[Test] Email Template Preview', $template, 'Content-Type: text/html; charset=UTF-8' );

        $back_url = admin_url('options-general.php?page=caseswap-options&cs_page=new-case-template');

        if ( $sent ) {
          wp_die('<h2>Test Email Sent</h2><p>The email template was sent successfully to <code>'. esc_html($user->get('user_email')) .'</code>.</p><p><a href="'. esc_attr($back_url) .'" onclick="window.close();">&laquo; Go Back</a></p>');
          exit;
        }else{
          wp_die('<h2>Test Email Failed</h2><p>Sorry, we failed to send the email template to <code>'. esc_html($user->get('user_email')) .'</code>.</p><p><a href="'. esc_attr($back_url) .'" onclick="window.close();">&laquo; Go Back</a></p>');
          exit;
        }

      }
    }

    /**
     * Retrieves an email template from the given key and inserts the tags from a key-value array.
     *
     * Tags should be formed such as: array( "[name]" => "Bob" ). This would replace "Hello, [name]!" with "Hello, Bob!"
     *
     * @param $template_key
     * @param $tags
     * @return mixed|string
     */
    public function get_email_template( $template_key, $tags = array() ) {
      global $CSCore;
      $options = $CSCore->Options->get_options();

      // Die if the template key is not set
      if ( !isset($options[$template_key]) ) {
        wp_die('<h2>Invalid template</h2><p>The template <code>'. esc_html( $template_key ) .'</code> does not exist.</p>');
        exit;
      }

      if ( !is_array($tags) ) $tags = array();

      // Get our template as a string
      $template = $options[$template_key];

      // Automatically add paragraphs, if the option is checked
      if ( $options[$template_key . '_autop'] != '' ) {
        $template = wpautop($template);
      }

      // Merge our tags into our template
      $template = str_replace(array_keys($tags), array_values($tags), $template);

      // Return our template HTML
      return $template;
    }


    /**
     * When the options page for "Email" is saved, if the "Send Test Email" box is checked, this will route the value through the redirect to the "Your settings have been saved" screen.
     *
     * The actual test email is sent inline, after the redirect.
     *
     * @param $args
     * @return mixed
     */
    public function options_saved_test_email( $args ) {
      if ( isset($_REQUEST['cs_test_recipient']) && $_REQUEST['cs_test_recipient'] ) {
        $args['cs_test_recipient'] = urlencode(stripslashes($_REQUEST['cs_test_recipient']));
      }

      return $args;
    }


    /**
     * This option gets a single mail_setting, but caches the results for future uses.
     *
     * @param $key
     * @return mixed
     */
    public function setting( $key ) {
      if ( $this->mail_settings[$key] === null ) {
        // Options have not been loaded yet. Loading them from CSCore->Options.
        // This will speed up all other uses of $this->setting()
        global $CSCore;
        $options = $CSCore->Options->get_options();

        foreach( $this->mail_settings as $k => $v ) {
          // All $mail_settings should be defined in Options->$default_options.
          $this->mail_settings[$k] = $options[$k];
        }
      }

      return $this->mail_settings[$key];
    }


    /**
     * Overrides the default "From" name used by wp_mail().
     *
     * @param $name
     * @return mixed
     */
    public function mail_default_from_name($name) {
      // If From name is the WordPress default, replace it
      if ($name == 'WordPress' && $this->setting('mail_from_name') != '' ) {
        return $this->setting('mail_from_name') ;
      }

      return $name;
    }


    /**
     * Overrides the default "From" email address used by wp_mail()
     *
     * @param $email
     * @return mixed
     */
    public function mail_default_from_email($email) {
      // ## COPIED FROM pluggable.php:349-354 (rev 10150)
      $sitename = strtolower( $_SERVER['SERVER_NAME'] );
      if ( substr( $sitename, 0, 4 ) == 'www.' ) {
        $sitename = substr( $sitename, 4 );
      }

      $default_email = 'wordpress@' . $sitename;
      // ## END copied code

      if ( $email == $default_email && is_email( $this->setting('mail_from_email') ) ) {
        // Email is default and custom email has been specified to replace it
        return $this->setting('mail_from_email');
      }else{
        // Use the provided email
        return $email;
      }
    }


    /**
     * Customizes the PHPMailer object during wp_mail, allowing us to inject custom settings like SMTP credentials.
     *
     * @param $phpmailer
     */
    public function phpmailer_init( $phpmailer ) {
      // Having issues? turn this on
      // $phpmailer->SMTPDebug = 1;

      // Set the mailer type as per config above, this overrides the already called isMail method
      if ( $this->setting('smtp_enabled') != '' ) {
        $phpmailer->Mailer = 'smtp';
      }

      // Set the Sender (return-path) if not already provided
      if ( !isset($phpmailer->Sender) || !$phpmailer->Sender ) {
        if ( $this->setting('mail_return_path') && is_email( $this->setting('mail_return_path') ) ) {
          $phpmailer->Sender = $this->setting('mail_return_path');
        }else{
          // Fall back to use the From address
          $phpmailer->Sender = $phpmailer->From;
        }
      }

      // If we're sending via SMTP, set the host
      if ( $this->setting('smtp_enabled') != '' ) {

        // Set the SMTPSecure value, if set to none, leave this blank
        $phpmailer->SMTPSecure = in_array($this->setting('smtp_ssl'), array('tls', 'ssl')) ? $this->setting('smtp_ssl') : 'none';

        // Set the other options
        $phpmailer->Host = $this->setting('smtp_host') ? $this->setting('smtp_host') : 'localhost';
        $phpmailer->Port = $this->setting('smtp_port') ? absint( $this->setting('smtp_port') ) : 25;

        // If we're using smtp auth, set the username & password
        if ( $this->setting('smtp_auth') != '' ) {
          $phpmailer->SMTPAuth = true;
//          $phpmailer->AuthType = 'basic';
          $phpmailer->Username = $this->setting('smtp_user');
          $phpmailer->Password = $this->setting('smtp_pass');
        }
      }

    }

  }
}