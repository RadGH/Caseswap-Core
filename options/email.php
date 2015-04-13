<?php
/**
 * Displays configuration for Email and SMTP settings
 *
 * Fields managed:
 *   mail_from_email
 *   mail_from_name
 *   mail_return_path
 *   smtp_enabled
 *   smtp_user
 *   smtp_pass
 *   smtp_host
 *   smtp_port
 *   smtp_ssl
 *   smtp_auth
 *
 */

global $CSCore;

$options = $CSCore->Options->get_options();

$SMTP = $CSCore->SMTP;
if ( !$SMTP instanceof CSCore_SMTP ) return; // Smarten up my IDE


// ## COPIED FROM pluggable.php:349-354 (rev 10150)
$sitename = strtolower( $_SERVER['SERVER_NAME'] );
if ( substr( $sitename, 0, 4 ) == 'www.' ) {
  $sitename = substr( $sitename, 4 );
}

$default_email = 'wordpress@' . $sitename;
// ## END copied code

?>
<h3>Email Defaults</h3>

<p>These settings can replace the default email settings, but are not used if a different option has been provided. These settings affect all emails sent by WordPress and any plugin.</p>

<table class="form-table caseswap-form-table">
  <tbody>

  <!-- Email: Default Email -->
  <tr>
    <td style="width: 160px;">
      <strong><label for="cs_options_mail_from_email">From Email</label></strong>
    </td>
    <td>
      <input class="regular-text" type="email" name="cs_options[mail_from_email]" id="cs_options_mail_from_email" value="<?php echo esc_attr($SMTP->setting('mail_from_email')); ?>" />
      <span class="description">Default: <code><?php echo esc_html($default_email); ?></code></span>
    </td>
  </tr>

  <!-- Text: Default Name -->
  <tr>
    <td style="width: 160px;">
      <strong><label for="cs_options_mail_from_name">From Name</label></strong>
    </td>
    <td>
      <input class="regular-text" type="text" name="cs_options[mail_from_name]" id="cs_options_mail_from_name" value="<?php echo esc_attr($SMTP->setting('mail_from_name')); ?>" />
      <span class="description">Default: <code>WordPress</code></span>
    </td>
  </tr>

  <!-- Email: Return Path -->
  <tr>
    <td style="width: 160px;">
      <strong><label for="cs_options_mail_return_path">Return Path</label></strong>
    </td>
    <td>
      <input class="regular-text" type="email" name="cs_options[mail_return_path]" id="cs_options_mail_return_path" value="<?php echo esc_attr($SMTP->setting('mail_return_path')); ?>" />
      <span class="description">Default: <code>("From email")</code></span>

      <p class="description">Any bounced emails will be sent to this email address, unless a return path has already been specified elsewhere.</p>
    </td>
  </tr>

  </tbody>
</table>


<h3>SMTP Settings</h3>

<p>If SMTP is disabled, the settings below will have no effect. The settings above still apply, however.</p>

<table class="form-table caseswap-form-table">
  <tbody>

  <!-- Checkbox: Enable SMTP -->
  <tr>
    <td style="width: 160px;">
      <strong><label for="cs_options_smtp_enabled">Use SMTP</label></strong>
    </td>
    <td>
      <label for="cs_options_smtp_enabled">
        <input type="checkbox" name="cs_options[smtp_enabled]" id="cs_options_smtp_enabled" <?php checked($SMTP->setting('smtp_enabled') != "", true); ?> />
        Enable SMTP for all email
      </label>
    </td>
  </tr>

  </tbody>
</table>

<div id="smtp-required">

  <table class="form-table caseswap-form-table">
    <tbody>

    <!-- Text: SMTP Host -->
    <tr>
      <td style="width: 160px;">
        <strong><label for="cs_options_smtp_host">SMTP Host</label></strong>
      </td>
      <td>
        <input class="regular-text" type="text" name="cs_options[smtp_host]" id="cs_options_smtp_host" value="<?php echo esc_attr($SMTP->setting('smtp_host')); ?>" />
        <span class="description">Default: <code>localhost</code></span>
      </td>
    </tr>

    <!-- Text: SMTP Port -->
    <tr>
      <td style="width: 160px;">
        <strong><label for="cs_options_smtp_port">SMTP Port</label></strong>
      </td>
      <td>
        <input class="small-text" type="number" name="cs_options[smtp_port]" id="cs_options_smtp_port" value="<?php echo esc_attr($SMTP->setting('smtp_port')); ?>" />
        <span class="description">Default: <code>25</code></span>
      </td>
    </tr>

    <!-- Select: SMTP Encryption -->
    <tr>
      <td style="width: 160px;">
        <strong><label for="cs_options_smtp_ssl">SMTP Encryption</label></strong>
      </td>
      <td>
        <select name="cs_options[smtp_ssl]" id="cs_options_smtp_ssl">
          <?php
          $ssl_types = array(
            'none' => 'None',
            'ssl' => 'SSL Encryption',
            'tls' => 'TLS Encryption (Uncommon)',
          );

          foreach( $ssl_types as $value => $label ) {
            echo sprintf(
              '<option value="%s" %s>%s</option>',
              esc_attr( $value ),
              selected( $value, $SMTP->setting('smtp_ssl'), false ),
              esc_html( $label )
            );
          }
          ?>
        </select>
        <span class="description">Default: <code>None</code></span>
      </td>
    </tr>

    <!-- Checkbox: Enable Authentication -->
    <tr>
      <td style="width: 160px;">
        <strong><label for="cs_options_smtp_auth">Authentication</label></strong>
      </td>
      <td>
        <label for="cs_options_smtp_auth">
          <input type="checkbox" name="cs_options[smtp_auth]" id="cs_options_smtp_auth" <?php checked($SMTP->setting('smtp_auth') != "", true); ?> />
          Use SMTP user authentication
        </label>
      </td>
    </tr>

    </tbody>
  </table>

  <div id="smtp-auth-required">

    <table class="form-table caseswap-form-table">
      <tbody>
      
      <!-- Text: Username -->
      <tr>
        <td style="width: 160px;">
          <strong><label for="cs_options_smtp_user">Username</label></strong>
        </td>
        <td>
          <input class="regular-text" type="text" name="cs_options[smtp_user]" id="cs_options_smtp_user" value="<?php echo esc_attr($SMTP->setting('smtp_user')); ?>" />
          <span class="description">This is often the <strong>full</strong> email address.</span>
        </td>
      </tr>
      
      <!-- Password: Password -->
      <tr>
        <td style="width: 160px;">
          <strong><label for="cs_options_smtp_pass">Password</label></strong>
        </td>
        <td>
          <input class="regular-text" type="password" name="cs_options[smtp_pass]" id="cs_options_smtp_pass" value="<?php echo esc_attr($SMTP->setting('smtp_pass')); ?>" />
        </td>
      </tr>
      
      </tbody>
    </table>
    
  </div>
  
</div>


<h3>Send Test Email</h3>

<?php
// SEND TEST EMAILS
if ( isset($_REQUEST['cs_test_recipient']) ) {
  $email = stripslashes($_REQUEST['cs_test_recipient']);

  if ( is_email($email) ) {
    $subject = '[' . get_bloginfo('name') . '] Test email';
    $message = '<p>This is a test email sent from <a href="'. esc_attr(get_bloginfo('url')) .'" target="_blank">'. esc_html(get_bloginfo('name')) .'</a>.</p>';
    $header = 'Content-Type: text/html; charset=UTF-8';

    $sent = wp_mail( $email, $subject, $message, $header );

    if ( $sent ) {
      echo '<div class="updated"><p><strong>Email Sent:</strong> A test email was sent to <code>'. esc_html($email) .'</code></p></div>';
    }else{
      echo '<div class="error"><p><strong>Email Error:</strong> wp_mail() failed to send email.</p></div>';
    }
  }else{
    echo '<div class="error"><p><strong>Email Error:</strong> Invalid email address: <code>'. esc_html($email) .'</code>.</p></div>';
  }
}
?>

<table class="form-table caseswap-form-table">
  <tbody>

  <!-- Text: Test Recipient -->
  <tr>
    <td style="width: 160px;">
      <strong><label for="cs_test_recipient">Recipient Email</label></strong>
    </td>
    <td>
      <input type="email" name="cs_test_recipient" id="cs_test_recipient" />
      <p class="description">If set, an email will be sent to the given address when you save your settings, after the settings have been applied.</p>
    </td>
  </tr>

  </tbody>
</table>

<style type="text/css">
#smtp-required,
#smtp-auth-required {
  border-left: 2px solid #dfdfdf;
  padding-left: 20px;
  margin-left: 10px;
}
#smtp-required table.form-table,
#smtp-auth-required table.form-table {
  margin-top: 0;
}
</style>

<script type="text/javascript">
jQuery(function() {
  // Show #smtp-required only when #cs_options_smtp_enabled is checked
  jQuery('#cs_options_smtp_enabled').change(function() {

    jQuery("#smtp-required").toggle( jQuery(this).prop('checked') );

  }).trigger("change");


  // Show #smtp-auth-required only when #cs_options_smtp_auth is checked
  jQuery('#cs_options_smtp_auth').change(function() {

    jQuery("#smtp-auth-required").toggle( jQuery(this).prop('checked') );

  }).trigger("change");
});
</script>