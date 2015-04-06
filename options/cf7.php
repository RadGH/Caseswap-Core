<?php
/**
 * Displays the main settings page for CaseSwap Core
 *
 * Fields managed:
 *   cf7-form-id
 *   cf7-state-key
 *   cf7-investigator-key
 *
 */

global $CSCore;

$options = $CSCore->Options->get_options();

$args = array(
  'post_type' => 'wpcf7_contact_form',
  'post_status' => 'publish',
  'orderby' => 'title',
  'order' => 'asc',
);

$all_contact_forms = get_posts($args);

?>
<table class="form-table caseswap-form-table">
  <tbody>

  <!-- Select: Contact Form ID -->
  <tr>
    <td style="width: 220px;">
      <strong><label for="cs_options_investigator-types">Investigation Types</label></strong>
      <p class="description"><small>Investigators who sign up may select one or more Investigation Type. When an email is sent, it is sent to any active member who has selected that Investigator Type.</small></p>
    </td>
    <td>
      <select name="cs_options[cf7-form-id]" id="cs_options_cf7-form-id">
        <option value="">&ndash; Select &ndash;</option>
        <?php
        foreach( $all_contact_forms as $post ) {
          echo sprintf(
            '<option value="%s" %s>%s</option>',
            esc_attr($post->ID),
            selected($post->ID, $options['cf7-form-id'], false),
            esc_html($post->post_title)
          );
        }
        ?>
      </select>
      <p class="hide-if-no-js"><a href="#" onclick="jQuery('#cf7-form-help').show(); jQuery(this).hide(); return false">View Contact Form Requirements</a></p>

      <div id="cf7-form-help" class="hide-if-js">
        <p>Contact form should have the following fields:</p>

        <ul class="ul-disc">
          <li><code>name</code> (Text)</li>
          <li><code>email</code> (Text)</li>
          <li><code>investigator_type</code> (* Dropdown)</li>
          <li><code>state</code> (* Dropdown)</li>
          <li><code>message</code> (Textarea)</li>
          <li><code>contact_method</code> (Text)</li>
        </ul>

        <p class="description">* These fields will have values added automatically using the values from the Settings page.</p>
      </div>
    </td>
  </tr>

  </tbody>
</table>