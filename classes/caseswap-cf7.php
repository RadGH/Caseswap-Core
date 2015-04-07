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
  class CSCore_CF7 {

    public $cached_options = false;

    public function __construct() {
      // This is in the plugins_loaded event. You can use init hooks here.

     add_filter( 'wpcf7_form_tag',  array( &$this, 'format_tag'), 10, 2 );
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

       // If the tag name is investigator_type or state, or a plurral version, or using hyphens, replace values
       switch( $tag['name'] ) {
         case 'investigator_type': case 'investigator-type':
         case 'investigator_types': case 'investigator-types':
           $values = $this->cached_options['investigator-types'];
           break;

         case 'states':case 'state':
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

  }
}