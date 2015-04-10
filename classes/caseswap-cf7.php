<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * Module for CaseSwap Core which allows Contact Form 7 integration.
 *
 * This module is loaded only when Contact Form 7 is installed and activated in the theme. It can be accessed via $CSCore->CF7.
 *
 * @since 4.1.1
 * @global $CSCore->CF7
 * @param none
 */

if ( !class_exists('CSCore_CF7') ) {
  class CSCore_CF7
  {

    public $cached_options = false;

    public $state = null;
    public $type = null;

    public function __construct()
    {
      // This is in the plugins_loaded event. You can use init hooks here.

      add_filter('wpcf7_form_tag', array(&$this, 'format_tag'), 10, 2);

      add_filter( 'wpcf7_validate', array( &$this, 'validate_cf7' ), 10, 2 );
    }

    public function format_tag( $tag, $exec ) {
      if ( is_admin() ) return $tag;

      if ( $tag['basetype'] == 'select' ) {
         // Get options, specifically to access the state/investigator type values
         // We only do this once.
         if ( $this->cached_options === false ) {
           global $CSCore;

           $this->cached_options = $CSCore->Options->get_options();
         }

         // If the tag name is investigator_type or state, replace values
         switch( $tag['name'] ) {
           case 'investigator_type':
             $values = $this->cached_options['investigator-types'];
             break;

           case 'state':
             $values = $this->cached_options['states'];
             break;

           default:
             return $tag;
             break;
         }

         // If our options do not have a value, do nothing. This shouldn't be the case unless the options are invalid.
         if ( !$values ) return $tag;

         // Update the values for the dropdown. We don't use pipes, the values and labels are identical.
         $tag['raw_values'] = $values;
         $tag['values'] = $values;
         $tag['labels'] = $values;

         // Still overwrite pipes, even though we don't use them.
         if ( WPCF7_USE_PIPE ) {
           $pipes = new WPCF7_Pipes( $values );
           $tag['pipes'] = $pipes;
         }
      }

      return $tag;
    }

    public function get_state() {
      if ( $this->state === null ) {
        $this->state = isset($_REQUEST['state']) ? (string) stripslashes($_REQUEST['state']) : null;
      }

      return $this->state;
    }

    public function get_investigator_type() {
      if ( $this->type === null ) {
        $this->type = isset($_REQUEST['investigator_type']) ? (string) stripslashes($_REQUEST['investigator_type']) : null;
      }

      return $this->type;
    }

    public function validate_cf7( $result, $tags ) {
      global $CSCore;

      $contact_form = wpcf7_get_current_contact_form();
      $options = $CSCore->Options->get_options();

      // If a specific form is set, only check for fields in that form.
      if ( (int) $contact_form->id() !== (int) $options['cf7-form-id'] ) {
        return $result;
      }

      // Get the state/type which the user has provided
      $state = $this->get_state();
      $type = $this->get_investigator_type();

      // If both of these are unset, they must be filling out the wrong form.
      if ( $state === null && $type === null ) {
        return $result;
      }

      // Validate that the fields provided are allowed in the options
      global $CSCore;
      $options = $CSCore->Options->get_options();

      $allowed = true;
      $all_states = $options['states'];
      $all_types = $options['investigator-types'];

      // Check if state is allowed
      if ( !in_array( $state, $all_states ) ) {
        $index = null;
        foreach( $tags as $k => $v ) if ( $v['name'] == 'state' ) $index = $k;

        $result->invalidate( $tags[$index], 'That state is not currently supported.' );
        $allowed = false;
      }

      // Check if type is allowed
      if ( !in_array( $type, $all_types ) ) {
        $index = null;
        foreach( $tags as $k => $v ) if ( $v['name'] == 'investigator_type' ) $index = $k;

        $result->invalidate( $tags[$index], 'That type of investigation is not currently supported.' );
        $allowed = false;
      }

      // Check if there are any investigators of the selected type within the selected state
      if ( $allowed ) {
        $investigators = $CSCore->get_investigators( $state, $type );

        if ( empty($investigators) ) {
          // No investigators found, give an error
          $index = null;
          foreach( $tags as $k => $v ) if ( $v['name'] == 'investigator_type' ) $index = $k;

          $result->invalidate( $tags[$index], "No investigators from " . esc_html($state) . " match this investigation type. Try something else." );
        }else{
          // Investigators are found, wait for send mail event
          add_filter( 'wpcf7_before_send_mail', array(&$this, 'catch_cf7') );
        }
      }

      return $result;
    }


    public function catch_cf7( $contact_form ) {
      $state = $this->get_state();
      $type = $this->get_investigator_type();

      if ( $state === null && $type === null ) {
        // State and type not specified, do not intercept this message.
        return $contact_form;
      }

      global $CSCore;
      $user_ids = $CSCore->get_investigators( $state, $type );

      $properties = $contact_form->get_properties();

      $emails = $this->user_ids_to_email_string( $user_ids );

      if ( $emails ) {
        // Add the email string as the recipient for this contact form
        $properties['mail']['recipient'] = $emails;
        $contact_form->set_properties( $properties );
      }else{
        // We should not get here
        wp_die('No investigators to send mail to');
        exit;
      }

      // Send the mail!
      return $contact_form;
    }


    public function user_ids_to_email_string( $user_ids ) {
      global $wpdb;

      // Convert an array of user IDs to a mysql-friendly list
      $uids = array();
      foreach( $user_ids as $id ) {
        if ( absint($id) > 0 ) $uids[] = absint($id); // esc_sql isn't necessary
      }
      $user_id_list = implode(", ", $uids); // 42, 43, 45
      if ( empty($user_id_list) ) return false;

      // Sql query to get the email address of every user ID.
      // Note: Relationship between ID and email address is not preserved.
      $sql = "
SELECT u.user_email
FROM {$wpdb->users} u
WHERE u.ID IN ( {$user_id_list} )
LIMIT 2000;
";

      $email_array = $wpdb->get_col( $sql );
      if ( empty($email_array) ) return false;

      // Sanitize all email addresses
      $sanitized_emails = array();

      foreach( $email_array as $e ) {
        $san = sanitize_email( $e );
        if ( $san ) $sanitized_emails[] = $san;
      }

      if ( empty($sanitized_emails) ) return false;

      return implode(", ", $sanitized_emails);
    }

  }
}