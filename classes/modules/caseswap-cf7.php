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

  public function __construct() {
    // This works. This is in the plugins_loaded event. You can use init here.

    // $scanned_tag = apply_filters( 'wpcf7_form_tag', $scanned_tag, $this->exec );
    add_filter( 'wpcf7_form_tag',  array( &$this, 'format_tag'), 10, 2 );
  }

  public function format_tag( $tag, $exec ) {
/*
array(10) {
  ["type"]=>
  string(5) "text*"
  ["basetype"]=>
  string(4) "text"
  ["name"]=>
  string(4) "name"
  ["options"]=>
  array(4) {
      [0]=>
    string(10) "id:cs-name"
    [1]=>
    string(17) "class:field-input"
    [2]=>
    string(16) "class:field-text"
    [3]=>
    string(11) "placeholder"
  }
  ["raw_values"]=>
  array(1) {
      [0]=>
    string(9) "Full name"
  }
  ["values"]=>
  array(1) {
      [0]=>
    string(9) "Full name"
  }
  ["pipes"]=>
  object(WPCF7_Pipes)#1906 (1) {
  ["pipes":"WPCF7_Pipes":private]=>
    array(1) {
      [0]=>
      object(WPCF7_Pipe)#1913 (2) {
      ["before"]=>
        string(9) "Full name"
        ["after"]=>
        string(9) "Full name"
      }
    }
  }
["labels"]=>
array(1) {
[0]=>
string(9) "Full name"
}
  ["attr"]=>
  string(0) ""
  ["content"]=>
  string(0) ""
}
*/
    var_dump($tag);
    exit;
  }

}
}