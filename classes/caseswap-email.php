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
    }

    public function options_saved_test_email( $args ) {
      if ( isset($_REQUEST['cs_test_recipient']) && $_REQUEST['cs_test_recipient'] ) {
        $args['cs_test_recipient'] = urlencode(stripslashes($_REQUEST['cs_test_recipient']));
      }

      return $args;
    }

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


    public function mail_default_from_name($name) {
      // If From name is the WordPress default, replace it
      if ($name == 'WordPress' && $this->setting('mail_from_name') != '' ) {
        return $this->setting('mail_from_name') ;
      }

      return $name;
    }

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

    public function phpmailer_init( $phpmailer ) {

      $phpmailer->SMTPDebug = 1;

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
          $phpmailer->SMTPAuth = TRUE;
          $phpmailer->AuthType = 'basic';
          $phpmailer->Username = $this->setting('smtp_user');
          $phpmailer->Password = $this->setting('smtp_pass');
        }
      }

    }

  }
}