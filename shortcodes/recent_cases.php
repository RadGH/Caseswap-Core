<?php
/**
 * Created by PhpStorm.
 * User: Radley
 * Date: 4/16/2015
 * Time: 11:08 PM
 */

// Shortcodes
add_shortcode( 'recent_cases', 'shortcode_recent_cases' );

function shortcode_recent_cases( $atts, $content = '' ) {
  $args = array(
    'post_type' => 'case',

    'post_status' => 'publish',

    'orderby' => 'date',
    'order' => 'DESC',

    'posts_per_page' => 5,

    'meta_query' => array(
      // Require a state to be displayed. Type can be faked. Date should always be given.
      array(
        'key' => 'state',
        'value' => '',
        'compare' => '!=',
      ),
    ),
  );

  $recent = get_posts( $args );

  ob_start();

  ?>
  <table class="avia-table avia-data-table avia-table-1  avia-builder-el-6  el_after_av_heading  el_before_av_hr " itemscope="itemscope" itemtype="https://schema.org/Table">
  <tbody>
    <?php
    foreach( $recent as $post ) {
      $type = get_post_meta( $post->ID, 'type', true );
      $state = get_post_meta( $post->ID, 'state', true );
      $date = get_the_date( "m/d/Y", $post->ID );

      if ( !$type ) $type = "Private Investigator";
      if ( !$state ) continue;
      ?>
      <tr>
        <td>I need a: <?php echo esc_html($type); ?></td>
        <td>in: <?php echo esc_html($state); ?></td>
        <td>on: <?php echo esc_html($date); ?></td>
      </tr>

      <?php
    }
    ?>
  </tbody>
  </table>
  <?php

  return ob_get_clean();
}