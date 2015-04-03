<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * Module for CaseSwap Core which allows Memberships Premium (by WPMUDev) integration.
 *
 * This module is loaded only when Memberships Pro is installed and activated in the theme. It can be accessed via $CSCore->Members.
 *
 * @since 4.1.1
 * @global $CSCore->PMP
 * @param none
 */

if ( !class_exists('CSCore_Members') ) {
  class CSCore_Members {

    public function __construct() {
      // This works. This is in the plugins_loaded event. You can use init here.
    }

  }
}