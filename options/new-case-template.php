<?php
/**
 * Customizable code used for the New Case email, which is sent to investigators
 *
 *
 * Fields managed:
 *   mail_template_new_case
 *   mail_template_new_case_autop
 *
 */

global $CSCore;

$options = $CSCore->Options->get_options();

$template = $options['mail_template_new_case'];

$this_url = admin_url('options-general.php?page=caseswap-options&cs_page=new-case-template');
$preview_url = add_query_arg(array( 'cs_template' => 'mail_template_new_case', 'cs_preview_nonce' => wp_create_nonce('preview-email-display') ));
$send_url = add_query_arg(array( 'cs_template' => 'mail_template_new_case', 'cs_preview_nonce' => wp_create_nonce('preview-email-send') ));
?>

<h3>New Case Email Template</h3>

<table class="form-table caseswap-form-table codemirror-table">
  <tbody>

  <!-- Select: Submission success page -->
  <tr>
    <td>
      <!-- Textarea: New Case template -->
      <textarea name="cs_options[mail_template_new_case]" id="template_textarea" cols="140" rows="20"><?php
        echo esc_textarea( $template );
      ?></textarea>
    </td>

    <td class="caseswap-template-controls">
      <h3>Template Controls:</h3>

      <p>
        <label for="cs_mail_template_new_case_autop">
          <input type="checkbox" name="cs_options[mail_template_new_case_autop]" id="cs_mail_template_new_case_autop" <?php checked( $options['mail_template_new_case_autop'] != false ); ?> />
          Automatically add paragraphs
        </label>
      </p>

      <p class="submit">
        <input type="submit" class="button button-primary" value="Save Template"/>
        <a class="button button-secondary" target="_blank" href="<?php echo esc_attr($preview_url); ?>">Preview</a>
        <a class="button button-secondary" target="_blank" href="<?php echo esc_attr($send_url); ?>">Send Test</a>
      </p>

      <h3>Template Tags:</h3>

      <h4>Visitor's Submitted Information:</h4>

      <table class="tag-table">
        <tr>
          <td class="tag-code">[name]</td>
          <td class="tag-desc">Name</td>
        </tr>
        <tr>
          <td class="tag-code">[email]</td>
          <td class="tag-desc">Email</td>
        </tr>
        <tr>
          <td class="tag-code">[type]</td>
          <td class="tag-desc">Investigation Type</td>
        </tr>
        <tr>
          <td class="tag-code">[state]</td>
          <td class="tag-desc">State</td>
        </tr>
        <tr>
          <td class="tag-code">[message]</td>
          <td class="tag-desc">Message</td>
        </tr>
        <tr>
          <td class="tag-code">[contact_method]</td>
          <td class="tag-desc">Preferred contact method</td>
        </tr>
      </table>

      <h4>Investigator's Information:</h4>

      <table class="tag-table">
        <tr>
          <td class="tag-code">[investigator_name]</td>
          <td class="tag-desc">Investigator's Name</td>
        </tr>
        <tr>
          <td class="tag-code">[investigator_email]</td>
          <td class="tag-desc">Investigator's Email</td>
        </tr>
        <tr>
          <td class="tag-code">[investigator_id]</td>
          <td class="tag-desc">Investigator's User ID</td>
        </tr>
      </table>

    </td>
  </tr>
  
  </tbody>
</table>

<script>
jQuery(function() {
  var $textarea = jQuery('#template_textarea');
  var codeMirrorEditor = CodeMirror.fromTextArea( $textarea[0] );
});
</script>