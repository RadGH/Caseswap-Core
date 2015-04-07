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

      // Show custom fields on the Edit User and Your Profile screens in the dashboard
      add_action( 'show_user_profile', array( &$this, 'render_user_custom_fields' ), 3 );
      add_action( 'edit_user_profile', array( &$this, 'render_user_custom_fields' ), 3 );
    }

    public function render_user_custom_fields( $user ) {
      global $CSCore;

      $state = get_user_meta( $user->ID, 'state' );
      $types = get_user_meta( $user->ID, 'investigator-types' );

      $options = $CSCore->Options->get_options();
      $all_states = $options['states'];
      $all_types = $options['investigator-types'];

      ?>
      <h3>CaseSwap Core - Investigator Settings:</h3>
      <input name="cs_nonce" value=caseswap-update-user" type="hidden"/>

      <table class="form-table">
        <tbody>

          <tr>
            <th>
              <label for="cs-state">State:</label>
            </th>
            <td>
              <select name="cs_user[state]" id="cs-state">
                <option value="">&ndash; Select &ndash;</option>
                <?php
                foreach( $all_states as $this_state ) {
                  echo sprintf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $this_state ),
                    selected( $state, $this_state, false ),
                    esc_html( $this_state )
                  );
                }
                ?>
              </select>
            </td>
          </tr>

          <tr>
            <th>
              <label for="">Investigation Types:</label>
            </th>
            <td>
              <div class="cs_checkbox_list">
                <?php
                foreach( $all_types as $this_type ) {
                  $html_id = 'cs-investigator-type-' . sanitize_title_with_dashes($this_type);
                  $checked = in_array( $this_type, $types );

                  echo sprintf(
                    '<div class="cs_cb_item"><label for="%s"><input type="checkbox" name="cs_user[investigator-types][]" id="%s" value="%s" %s> %s</label></div>',
                    esc_attr($html_id),
                    esc_attr($html_id),
                    esc_attr( $this_type ),
                    selected( $checked, true, false ),
                    esc_html( $this_type )
                  );
                }
                ?>
              </div>
            </td>
          </tr>

        </tbody>
      </table>

      <p class="description" style="background: #fc0; color: #000;">Todo: Save the two fields above and implement them on the front end account page.</p>
      <?php
    }

  }
}