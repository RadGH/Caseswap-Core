<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * Responsible for displaying the "New Case" submission form and processing the form when it is submitted.
 *
 * This module has no dependencies.
 *
 * @since 4.1.1
 * @global $CSCore->Form
 * @param none
 */

if ( !class_exists('CSCore_Form') ) {
  class CSCore_Form {

    // General variables


    /**
     * Set up our options object. This is executed during the plugins_loaded hook.
     */
    public function __construct() {
      // This is in the plugins_loaded event. You can use init hooks here.
      add_shortcode( 'caseswap_form', array(&$this, 'shortcode_caseswap_form') );
    }


    /**
     * Displays a multi-page form for CaseSwap
     */
    public function shortcode_caseswap_form( $atts, $content = "" ) {
      return 'Not yet implemented: Multi-page New Case form';
    }
  }
}