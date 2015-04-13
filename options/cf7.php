<?php
/**
 * Displays settings for the Contact Form 7 integration
 *
 *
 * Fields managed:
 *   cf7-form-id
 *   cf7-success-page-id
 *   cf7-state-key
 *   cf7-investigator-key
 *
 */

global $CSCore;

$options = $CSCore->Options->get_options();


// Get array of contact form posts, used to build a <select> menu
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
      <strong><label for="cs_options_cf7-form-id">Submit Case form</label></strong>
      <p class="description"><small>Forms managed by <a href="<?php echo esc_attr( admin_url( "admin.php?page=wpcf7" ) ); ?>">Contact Form 7</a>.</small></p>
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

        <p>The contact form's email template will be replaced by the <a href="<?php echo esc_attr( admin_url( 'options-general.php?page=caseswap-options&cs_page=new-case-template') ); ?>">New Case Template</a>.</p>
      </div>
    </td>
  </tr>

  <!-- Select: Submission success page -->
  <tr>
    <td style="width: 220px;">
      <strong><label for="cs_options_cf7-success-page-id">Success Page</label></strong>
      <p class="description"><small>When form is sent OK, visitors are redirected to this page.</small></p>
    </td>
    <td>
      <?php
      $args = array(
        'name' => 'cs_options[cf7-success-page-id]',
        'show_option_none' => '&ndash; Select &ndash;',
        'selected' => $options['cf7-success-page-id'],
      );

      wp_dropdown_pages( $args );
      ?>
      </div>
    </td>
  </tr>

  </tbody>
</table>