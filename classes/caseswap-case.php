<?php
if ( !defined('ABSPATH') ) exit; // Do not run directly.

/**
 * Module for CaseSwap Core which adds a custom post type name "case", and handles creation and management of those cases.
 *
 * @since 4.1.1
 * @global $CSCore->Case
 * @param none
 */

if ( !class_exists('CSCore_Case') ) {
  class CSCore_Case
  {

    public function __construct() {
      // This is in the plugins_loaded event. You can use init hooks here.
      add_action( 'init', array( &$this, 'register_post_type' ) );

      // This hook should be triggered by any of our functions which will send an email. Only once per submission (aka, don't trigger this for every investigator that gets an email)
      add_action( 'caseswap_send_case', array( &$this, 'add_case_from_submission' ), 10, 6 );
    }

    function register_post_type() {
      // Gifts Post Type
      $args = array(
        'labels' => array(
          'name' => 'Cases',
          'singular_name' => 'Case',
          'add_new' => 'Add Case',
          'add_new_item' => 'Add New Case',
          'edit_item' => 'Edit Case',
          'new_item' => 'New Case',
          'all_items' => 'View Cases',
          'view_item' => 'View Cases',
          'search_items' => 'Search Cases',
          'not_found' => 'No cases available.',
          'not_found_in_trash' => 'No cases found in Trash',
          'parent_item_colon' => '',
          'menu_name' => 'Submitted Cases'
        ),

        'hierarchical' => false, // Does this post type support child pages?

        'public' => true, // Is this post type intended to be publically accessible?
        'show_in_nav_menus' => false, // Whether to show this post type in Appearance > Menus

        'show_in_menu' => true, // True: Show custom admin menu for this post type. False: Do not show menu. String: Append menu as child of an existing menu
//        'menu_position' => 30, // Should be on top of the parent menu's list
        'menu_icon' => 'dashicons-portfolio', // http://melchoyce.github.io/dashicons/

        'exclude_from_search' => true,  // Hide any result from search?
        'rewrite' => false,  // Whether you can access this post type via URL
        'query_var' => false, // The query var used to specify the post type ID (eg, ?my_query_var=1)
        'publicly_queryable' => false, // If true, accessible via search and using query vars

        'supports' => array('title'), // What features are supported

        'capabilities' => array(
          "edit_post"              => "manage_options",
          "read_post"              => "manage_options",
          "delete_post"            => "manage_options",
          "edit_posts"             => "manage_options",
          "edit_others_posts"	     => "manage_options",
          "publish_posts"          => "manage_options",
          "read_private_posts"	   => "manage_options",
          "delete_posts"           => "manage_options",
          "delete_private_posts"   => "manage_options",
          "delete_published_posts" => "manage_options",
          "delete_others_posts"    => "manage_options",
          "edit_private_posts"     => "manage_options",
          "edit_published_posts"   => "manage_options",
          "create_posts"           => "manage_options",
        )
      );

      register_post_type('case', $args);
    }

    function create_case( $name, $email, $type, $state, $message, $contact_method = false ) {
      if ( !$name && !$email ) {
        // We gotta have something to log...
        return false;
      }

      $count = $this->count_cases_by_email( $email ); // Eg, "22"
      $count = $count + 1; // This case is the next one in the series, so add one.

      // Generate a readable version of the case number to display such as "Rad's 2nd Case"
      if ( $count == 1 ) $case_num = ""; // Don't show "Rad's 1st Case", that would be irritating
      else $case_num = $count . $this->day_ordinal( $count ) . " "; // Eg, "22nd"

      // Generate a title for the case, using the number of cases to help identify them. This number is irrelevant other than visual identification.
      $name_split = preg_split("/\s+/", $name); // Eg, [Radley, Sustaire]

      if ($name_split) {
        $title = $name_split[0] . "'s ". $case_num ."Case";
      } else {
        $title = $name . "'s ". $case_num ."Case";
      }

      $args = array(
        'post_type' => 'case',
        'post_title' => $title,
        'post_status' => 'publish',
      );

      $post_id = wp_insert_post( $args );

      if ( !$post_id ) return false;

      update_post_meta( $post_id, 'name', $name );
      update_post_meta( $post_id, 'email', $email );
      update_post_meta( $post_id, 'type', $type );
      update_post_meta( $post_id, 'state', $state );
      update_post_meta( $post_id, 'message', $message );
      update_post_meta( $post_id, 'contact_method', $contact_method );

      return $post_id;
    }

    public function add_case_from_submission( $name, $email, $type, $state, $message, $contact_method ) {
      $this->create_case( $name, $email, $type, $state, $message, $contact_method );
    }



    // Find the number of cases submitted by this user (email address)
    public function count_cases_by_email( $email ) {
      $args = array(
        'post_type' => 'case',
        'post_status' => 'any',
        'meta_query' => array(
          array(
            'key' => 'email',
            'value' => $email,
          ),
        ),
      );
      $cases_by_email = get_posts( $args );

      if ( is_wp_error($cases_by_email) ) return 0;

      return empty($cases_by_email) ? 0 : count($cases_by_email);
    }

    public function day_ordinal( $input ) {
      $ends = array('th','st','nd','rd','th','th','th','th','th','th');
      if (($input %100) >= 11 && ($input%100) <= 13)
        return 'th';
      else
        return $ends[$input % 10];
    }
  }
}