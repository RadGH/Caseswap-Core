<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * Allows wp_mail() to use an SMTP account to send email instead of regular PHP mail(). Based on WP Mail SMTP by Callum Macdonald.
 *
 * Options configured by $CSCore->Options
 *
 * @since 4.1.1
 * @global $CSCore->SMTP
 * @param none
 */

if ( !class_exists('CSCore_SMTP') ) {
  class CSCore_SMTP {

    /**
     * Set up our options object.
     */
    public function __construct() {

    }
  }
}