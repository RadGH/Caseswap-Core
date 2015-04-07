<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * Module for CaseSwap Core which allows Membership Premium (by WPMU Dev) integration.
 *
 * This module is loaded only when Membership Premium is installed and activated in the theme. It can be accessed via $CSCore->Membership.
 *
 * @since 4.1.1
 * @global $CSCore->Membership
 * @param none
 */

if ( !class_exists('CSCore_Members') ) {
  class CSCore_Members {

    public function __construct() {
      // This is in the plugins_loaded event. You can use init hooks here.
    }

  }
}