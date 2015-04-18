<?php
/**
 * Created by PhpStorm.
 * User: Radley
 * Date: 4/16/2015
 * Time: 11:08 PM
 */

// Shortcodes
add_shortcode( 'investigator_settings', 'shortcode_investigator_settings' );
add_action( 'init', 'save_investigator_settings_shortcode' );
add_action( 'ava_after_main_container', 'display_investigator_settings_saved_message' );

function shortcode_investigator_settings( $atts, $content = '' ) {
  if ( !is_user_logged_in() ) return '<!-- Not logged in, no investigator settings can be modified -->';

  global $CSCore;

  $user_id = get_current_user_id();

  $updated = defined('CS_SAVED_UPDATED') ? CS_SAVED_UPDATED : false;
  $error_occurred = defined('CS_SAVED_ERROR') ? CS_SAVED_ERROR : false;


  $state = get_user_meta( $user_id, 'state', false );
  $types = get_user_meta( $user_id, 'investigator-types', false );

  $options = $CSCore->Options->get_options();
  $all_states = $options['states'];
  $all_types = $options['investigator-types'];

  ob_start();

  ?>
  <form class="investigator-settings-form" action="">

    <p><strong>What State do you conduct your operations?</strong></p>

    <p>
      <select name="cs_user[state]" id="cs-state">
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
    </p>

    <p><strong>What Services do you offer?</strong></p>

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

    <?php
    if ( $updated ) {
      if ( $error_occurred ) {
        // Save successful, error occurred
        ?>
        <div class="avia_message_box avia-color-orange avia-size-large avia-icon_select-yes avia-border-  avia-builder-el-2  el_after_av_promobox  el_before_av_headline_rotator ">
          <span class="avia_message_box_title">Warning</span>
          <div class="avia_message_box_content">
            <span class="avia_message_box_icon" aria-hidden="true" data-av_icon="" data-av_iconfont="entypo-fontello"></span>
            <p>Settings incomplete, please provide State and Investigation Types.</p>
          </div>
        </div>
      <?php
      }else{
        // Save successful, no errors
        ?>
        <div class="avia_message_box avia-color-green avia-size-large avia-icon_select-yes avia-border-  avia-builder-el-2  el_after_av_promobox  el_before_av_headline_rotator ">
          <span class="avia_message_box_title">Saved</span>
          <div class="avia_message_box_content">
            <span class="avia_message_box_icon" aria-hidden="true" data-av_icon="" data-av_iconfont="entypo-fontello"></span>
            <p>All done! Your settings have been saved.</p>
          </div>
        </div>
      <?php
      }
    }
    ?>

    <p class="submit">
      <input name="cs_nonce" value="<?php echo wp_create_nonce('caseswap-update-user'); ?>" type="hidden"/>
      <input type="submit" value="Update Settings" class="button button-primary " name="submit">
    </p>
  </form>
  <?php

  return ob_get_clean();
}




function save_investigator_settings_shortcode() {
  $user_id = get_current_user_id();

  if ( isset($_REQUEST['cs_nonce']) && wp_verify_nonce($_REQUEST['cs_nonce'], 'caseswap-update-user') ) {
    $submit_state = isset($_REQUEST['cs_user']['state']) ?              (array) stripslashes_deep($_REQUEST['cs_user']['state'])              : array();
    $submit_types = isset($_REQUEST['cs_user']['investigator-types']) ? (array) stripslashes_deep($_REQUEST['cs_user']['investigator-types']) : array();

    // Remove empty keys from both arrays
    foreach( $submit_state as $k => $v ) if ( !$v ) unset( $submit_state[$k] );
    foreach( $submit_types as $k => $v ) if ( !$v ) unset( $submit_types[$k] );

    // Save each field. Each value is one meta value, do NOT use update_user_meta.
    if ( $submit_state !== null ) {
      delete_user_meta( $user_id, 'state' );
      foreach( $submit_state as $val ) {
        add_user_meta( $user_id, 'state', $val );
      }
    }

    if ( $submit_types !== null ) {
      delete_user_meta( $user_id, 'investigator-types' );
      foreach( $submit_types as $val ) {
        add_user_meta( $user_id, 'investigator-types', $val );
      }
    }

    if ( empty($submit_state) || empty($submit_types) ) {
      define('CS_SAVED_ERROR', true);
    }else{
      // Save successful, redirect to account page
      $membership_options = get_option('membership_options');
      $account_page = isset($membership_options['account_page']) ? $membership_options['account_page'] : false;

      if ( $account_page ) {
        wp_redirect( add_query_arg( array( 'cs_msg' => 'profile-complete' ), get_permalink( $account_page ) ) );
        exit;
      }else{
        // Just notify that the settings were saved
        define('CS_SAVED_ERROR', false);
      }
    }

    define('CS_SAVED_UPDATED', true);
  }
}



function display_investigator_settings_saved_message() {
  if ( isset($_REQUEST['cs_msg']) ) {
    add_filter('the_content', 'render_investigator_alert');
  }
}

function render_investigator_alert($content) {
  if ( !isset($_REQUEST['cs_msg']) ) return $content;

  if ( $_REQUEST['cs_msg'] == 'profile-complete' ) {
    remove_filter('the_content', 'render_investigator_alert');
    ob_start();
    ?>
    <div
      class="avia_message_box avia-color-green avia-size-large avia-icon_select-yes avia-border-  avia-builder-el-2  el_after_av_promobox  el_before_av_headline_rotator ">
      <span class="avia_message_box_title">Saved</span>

      <div class="avia_message_box_content">
        <span class="avia_message_box_icon" aria-hidden="true" data-av_icon=""
              data-av_iconfont="entypo-fontello"></span>

        <p>Your settings have been saved. Your profile is complete.</p>
      </div>
    </div>
    <?php
    $content = ob_get_clean() . "\n\n" . $content;
  }

  return $content;
}