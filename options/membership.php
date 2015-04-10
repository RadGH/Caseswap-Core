<?php
/**
 * Displays the main settings page for CaseSwap Core
 *
 * Fields managed:
 *   membership-subscription-level
 *
 */

global $CSCore, $wpdb;

$options = $CSCore->Options->get_options();

// Get all active membership levels
$sql = <<<SQL
SELECT id, level_title
FROM wp_m_membership_levels
WHERE level_active = 1;
SQL;

// Build key-value array of active membership levels
$subs_query = $wpdb->get_results( $sql );
$subscriptions = array();
foreach( $subs_query as $item ) {
  $subscriptions[$item->id] = $item->level_title;
}
?>
<table class="form-table caseswap-form-table">
  <tbody>

  <!-- Select: Membership Subscription Level -->
  <tr>
    <td style="width: 220px;">
      <strong><label for="cs_options_cf7-form-id">Membership Level</label></strong>
      <p class="description"><small>If enabled, investigators will only be included if their subscription level is set to this value.</small></p>
    </td>
    <td>
      <select name="cs_options[membership-subscription-level]" id="cs_options_membership-subscription-level">
        <option value="">&ndash; All &ndash;</option>
        <?php
        foreach( $subscriptions as $key => $label ) {
          echo sprintf(
            '<option value="%s" %s>%s</option>',
            esc_attr($key),
            selected($key, $options['membership-subscription-level'], false),
            esc_html($label)
          );
        }
        ?>
      </select>
    </td>
  </tr>

  </tbody>
</table>