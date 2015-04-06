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
      add_action( 'init', array(&$this, 'submit_case_form'), 8 );
    }

    /**
     * Displays the options menu HTML form when viewing the settings menu.
     */
    public function render_options_menu() {
      global $title;

      $options = array();

      ?>
      <div class="wrap">
        <h2><?php echo esc_html($title); ?></h2>

        <form class="caseswap-form" id="caseswap-options-form" action="<?php echo esc_attr($this->options_page_url); ?>" method="post">

          <table class="form-table caseswap-form-table">
            <tbody>

            <!-- Textarea: Investigator Types -->
            <tr>
              <td style="width: 220px;">
                <strong><label for="cs_options_investigator-types">Investigation Types</label></strong>
                <p class="description"><small>Investigators who sign up may select one or more Investigation Type. When an email is sent, it is sent to any active member who has selected that Investigator Type.</small></p>
              </td>
              <td>
                <textarea class="wide" name="cs_options[investigator-types]" id="cs_options_investigator-types" cols="80" rows="6"><?php
                  echo esc_textarea( implode( "\n", $options['investigator-types'] ) );
                  ?></textarea>
                <p class="description">One investigator type per line.</p>
              </td>
            </tr>

            </tbody>
          </table>

          <p class="submit">
            <input name="page" value="<?php echo esc_attr($this->options_page_slug); ?>" type="hidden"/>
            <input name="cs_nonce" value="<?php echo wp_create_nonce( "save-caseswap-options" ); ?>" type="hidden"/>
            <input class="button button-primary" type="submit" value="Save Changes" />
          </p>

        </form>
      </div>
    <?php
    }


    /**
     * Saves the options from the settings menu to the database.
     */
    public function submit_case_form() {
      if ( !isset($_REQUEST['csx_nonce']) ) return;
      if ( !isset($_REQUEST['csx_options']) ) return;
      if ( !wp_verify_nonce( stripslashes($_REQUEST['csx_nonce']), 'save-caseswap-options') ) return;

      exit;
    }
  }
}