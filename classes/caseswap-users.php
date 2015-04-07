<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * Module for CaseSwap Core which adds custom fields to user profiles.
 *
 * @since 4.1.1
 * @global $CSCore->Users
 * @param none
 */

if ( !class_exists('CSCore_Users') ) {
  class CSCore_Users {

    public function __construct() {
      // This is in the plugins_loaded event. You can use init hooks here.
    }

  }
}