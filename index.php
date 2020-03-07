<?php
/**
* Plugin Name: Dine Child Hello Bar
* Plugin URI: https://github.com/
* Description: Creating a hello bar ad space for Hubs & Hops' Dine-child theme.
* Version: 1.0
* Author: Lucas Ogden-Davis
* Author URI: https://ogdendavis.com/
**/


function dc_add_post_types() {
  register_post_type( 'dc_hello_ad',
    array(
      'labels' => array('name' => __('Hello Bar Ads'), 'singular_name' => __('Hello Bar Ad')),
      'public' => true,
    )
  );
}
add_action( 'init', 'dc_add_post_types' );

function dc_include_hello_bar() {
  // Function used in footer template to add the hello bar
  include_once 'src/template.php';
}

// TRYING TO CREATE CUSTOM POST TYPES HERE, INSTEAD OF REYLING ON ACF PLUGIN
// THIS IS BASED ON STEP 3 OF https://blog.teamtreehouse.com/create-your-first-wordpress-custom-post-type

function dc_admin_init(){
  add_meta_box("dc_ad_days", "Days to Display Ad", "dc_select_ad_days", "dc_hello_ad", "side", "high");
}
add_action( 'admin_init', 'dc_admin_init' );

function dc_select_ad_days(){
  global $post;
  $custom = get_post_custom($post->ID);
  ?>
  <style>
    <?php include 'src/admin-styles.css'; ?>
  </style>
  <div class="dc-ad-days-subtitle">Check all that apply</div>
  <input type="checkbox" id="dc_monday" name="dc_monday" value="true"<?php echo $custom['dc_monday'][0] == '1' ? 'checked' : ''; ?>>Monday</input><br />
  <input type="checkbox" id="dc_tuesday" name="dc_tuesday" value="true"<?php echo $custom['dc_tuesday'][0] == '1' ? 'checked' : ''; ?>>Tuesday</input><br />
  <input type="checkbox" id="dc_wednesday" name="dc_wednesday" value="true"<?php echo $custom['dc_wednesday'][0] == '1' ? 'checked' : ''; ?>>Wednesday</input><br />
  <input type="checkbox" id="dc_thursday" name="dc_thursday" value="true"<?php echo $custom['dc_thursday'][0] == '1' ? 'checked' : ''; ?>>Thursday</input><br />
  <input type="checkbox" id="dc_friday" name="dc_friday" value="true"<?php echo $custom['dc_friday'][0] == '1' ? 'checked' : ''; ?>>Friday</input><br />
  <input type="checkbox" id="dc_saturday" name="dc_saturday" value="true"<?php echo $custom['dc_saturday'][0] == '1' ? 'checked' : ''; ?>>Saturday</input><br />
  <input type="checkbox" id="dc_sunday" name="dc_sunday" value="true"<?php echo $custom['dc_sunday'][0] == '1' ? 'checked' : ''; ?>>Sunday</input><br />
  <?php
}

function dc_save_ad_info($post_id) {
  // loop to save daily info
  $days = ['dc_monday', 'dc_tuesday', 'dc_wednesday', 'dc_thursday', 'dc_friday', 'dc_saturday', 'dc_sunday'];
  foreach ($days as $day) {
    if( isset( $_POST[$day] ) ) {
      update_post_meta( $post_id, $day, '1' );
    } else {
      update_post_meta( $post_id, $day, '0' );
    }
  }
}
add_action('save_post', 'dc_save_ad_info');
