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

      // Save custom fields from the above pages
      add_action( 'personal_options_update', array( &$this, 'save_user_custom_fields' ), 3 );
      add_action( 'edit_user_profile_update', array( &$this, 'save_user_custom_fields' ), 3 );

      // Prevent investigators (subscribers) from viewing the wordpress dashboard
      add_action( 'admin_init', array( &$this, 'redirect_investigators_from_dashboard' ) );
      add_filter( 'show_admin_bar', array( &$this, 'hide_admin_bar_for_investigators' ) );
    }

    public function render_user_custom_fields( $user ) {
      global $CSCore;

      $state = get_user_meta( $user->ID, 'state', false );
      $types = get_user_meta( $user->ID, 'investigator-types', false );

      $options = $CSCore->Options->get_options();
      $all_states = $options['states'];
      $all_types = $options['investigator-types'];

      ?>
      <h3>Investigator Preferences</h3>
      <input name="cs_nonce" value="<?php echo wp_create_nonce('caseswap-admin-update-user'); ?>" type="hidden"/>

      <table class="form-table">
        <tbody>

          <tr>
            <th>
              <label for="cs-state">Licensed States:</label>
            </th>
            <td>
              <select name="cs_user[state][]" id="cs-state" multiple>
                <option value="">&ndash; Select &ndash;</option>
                <?php
                foreach( $all_states as $this_state ) {
                  echo sprintf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $this_state ),
                    selected( in_array($this_state, $state), true, false ),
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

                  echo sprintf(
                    '<div class="cs_cb_item"><label for="%s"><input type="checkbox" name="cs_user[investigator-types][]" id="%s" value="%s" %s> %s</label></div>',
                    esc_attr($html_id),
                    esc_attr($html_id),
                    esc_attr( $this_type ),
                    checked( in_array( $this_type, $types ), true, false ),
                    esc_html( $this_type )
                  );
                }
                ?>
              </div>
            </td>
          </tr>

        </tbody>
      </table>
      <?php
    }


    public function save_user_custom_fields( $user_id ) {
      $nonce = isset($_REQUEST['cs_nonce']) ? stripslashes($_REQUEST['cs_nonce']) : false;
      
      // Did not fill out a form which had our fields?
      if ( !$nonce ) return;
      
      // Filled out the correct form, but nonce invalid?
      if ( !wp_verify_nonce( $nonce, 'caseswap-admin-update-user') ) return;
      
      // Get values submitted
      $submit_state = isset($_REQUEST['cs_user']['state'])              ? (array) stripslashes_deep($_REQUEST['cs_user']['state'])              : array();
      $submit_types = isset($_REQUEST['cs_user']['investigator-types']) ? (array) stripslashes_deep($_REQUEST['cs_user']['investigator-types']) : array();

      // Save each field. Each value is one meta value, do NOT use update_user_meta.
      delete_user_meta( $user_id, 'state' );
      foreach( $submit_state as $val ) {
        add_user_meta( $user_id, 'state', $val );
      }

      delete_user_meta( $user_id, 'investigator-types' );
      foreach( $submit_types as $val ) {
        add_user_meta( $user_id, 'investigator-types', $val );
      }
    }


    public function redirect_investigators_from_dashboard() {
      if ( defined('DOING_AJAX') && DOING_AJAX ) {
        return;
      }

      if ( current_user_can('subscriber') ) {
        $membership_options = get_option('membership_options');
        $account_page = isset($membership_options['account_page']) ? $membership_options['account_page'] : false;

        if ( $account_page ) {
          wp_redirect( get_permalink( $account_page ) );
          exit;
        }else{
          wp_redirect( get_bloginfo('url') );
          exit;
        }
      }
    }


    public function hide_admin_bar_for_investigators( $show_admin_bar ) {
      if ( current_user_can('subscriber') ) {
        return false;
      }

      return $show_admin_bar;
    }

  }
}