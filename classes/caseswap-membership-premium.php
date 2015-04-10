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
      add_filter( 'cscore_filter_matching_investigators', array( &$this, "filter_matching_investigators") );
    }

    public function filter_matching_investigators( $user_ids ) {
      if ( empty($user_ids) ) return $user_ids;

      // Get required member level
      global $CSCore;
      $options = $CSCore->Options->get_options();
      $membership_level = (int) $options['membership-subscription-level'];

      // Is membership level required?
      if ( $membership_level < 1 ) return $user_ids;

      // Begin collecting array of users who are the correct member level.
      $valid_user_ids = array();
      $MembershipFactory = Membership_Plugin::factory();

      foreach( $user_ids as $user_id ) {
        $member = $MembershipFactory->get_member( $user_id );

        // Does this user have the required membership level? (Ignoring subscription type)
        if ( $member->on_level( $membership_level, null ) ) {
          $valid_user_ids[] = $user_id;
        }
      }

      return $valid_user_ids;
    }
  }
}