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

?>
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

  <!-- Textarea: States -->
  <tr>
    <td style="width: 220px;">
      <strong><label for="cs_options_states">States</label></strong>
      <p class="description"><small>These states will be available for investigators during sign up. Only states which have at least one investigator will appear for visitors submitting a new case.</small></p>
    </td>
    <td>
      <textarea class="wide" name="cs_options[states]" id="cs_options_states" cols="80" rows="6"><?php
        echo esc_textarea( implode( "\n", $options['states'] ) );
        ?></textarea>
      <p class="description">One state per line.</p>
    </td>
  </tr>

  </tbody>
</table>