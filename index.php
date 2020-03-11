<?php
/**
* Plugin Name: Dine Child Hello Bar
* Plugin URI: https://github.com/
* Description: Creating a hello bar ad space for Hubs & Hops' Dine-child theme.
* Version: 1.0
* Author: Lucas Ogden-Davis
* Author URI: https://ogdendavis.com/
**/

/* Use in theme templates to add hello bar to any page on which the template is used. */
function dc_include_hello_bar() {
  include_once 'src/template.php';
}

/* Add custom post type for hello bar ads */
function dc_add_post_types() {
  // Add custom post type for the ads
  register_post_type( 'dc_hello_ad',
    array(
      'labels' => array('name' => __('Hello Bar Ads'), 'singular_name' => __('Hello Bar Ad')),
      'public' => true,
    )
  );
}
add_action( 'init', 'dc_add_post_types' );

/* Add endpoint to get content of exactly one ad that's valid for display */
function dc_get_hello_ad() {
  // For use at endpoint for all hello ads
  $raw = get_posts( array(
    'numberposts' => -1,
    'post_type' => 'dc_hello_ad',
  ));
  $relevant = [];
  // Use raw info to get relevant data for display
  foreach ($raw as $ad) {
    $custom_all = get_post_custom($ad->ID);
    $custom_filtered = array_filter($custom_all, function($key) {
      return substr($key,0,3) === 'dc_';
    }, ARRAY_FILTER_USE_KEY);

    $item = [
      'ID' => $ad->ID,
      'content' => $ad->post_content,
      'custom' => $custom_filtered
    ];
    array_push($relevant, $item);
  }
  // Filter ads down to only those in valid range
  $filtered = array_filter($relevant, 'dc_filter_hello_ads');

  // Return the content of one of the random ads
  $choice = array_rand( $filtered, 1 );
  return $filtered[$choice]['content'];
}
function dc_filter_hello_ads($ad) {
  // Get date in month and day of week, and format content from that!
  date_default_timezone_set('America/New_York');

  if ( !empty( $ad['custom']['dc_start_date'][0] ) or !empty( $ad['custom']['dc_end_date'][0] ) ) {
    // If start and end dates are set, deal with that first
    $today_date = date('d');
    // If no value given, set out of range to ensure it all goes
    $start_date = empty( $ad['custom']['dc_start_date'][0] ) ? 0 : $ad['custom']['dc_start_date'][0];
    $end_date = empty( $ad['custom']['dc_end_date'][0] ) ? 32 : $ad['custom']['dc_end_date'][0];

    // Case for start date < end date (dates in same month)
    if ($start_date < $end_date) {
      $is_date_valid = ($start_date <= $today_date) && ($end_date >= $today_date);
      //$is_date_valid = false;
    }
    // Case for end date < start date (dates in different months)
    elseif ($end_date < $start_date) {
      $is_date_valid = (($start_date >= $today_date) && ($end_date >= $today_date)) || (($start_date <= $today_date) && ($end_date <= $today_date));
    }
  }
  else {
    $is_date_valid = true;
  }

  // Check if ad should be displayed today
  $is_day_valid = $ad['custom']['dc_' . strtolower(date('l'))][0] == '1' ? true : false;

  // Return if we're within valid date range for ad, and on a day of the week that's valid for it
  return $is_date_valid && $is_day_valid;
}
add_action( 'rest_api_init', function() {
  register_rest_route( 'dc-hello/v1', '/get-ad', array(
    'methods' => 'GET',
    'callback' => 'dc_get_hello_ad',
  ));
});

/* Add custom meta boxes to dc_hello_ad post type. These control when ads display */
function dc_admin_init(){
  // Add meta boxes to ad post edit
  add_meta_box("dc_ad_days", "Days to Display Ad", "dc_select_ad_days", "dc_hello_ad", "side", "default");
  add_meta_box("dc_ad_date_range", "Restrict Ad to Dates", "dc_select_ad_date_range", "dc_hello_ad", "side", "default");
}
add_action( 'admin_init', 'dc_admin_init' );

function dc_select_ad_days() {
  // Function to display meta box to select days of week for the ad
  global $post;
  $custom = get_post_custom($post->ID);
  ?>
  <style>
    <?php include 'src/admin-styles.css'; ?>
  </style>
  <input type="checkbox" id="dc_select_all_days"><b>Select All</b></input><br />
  <input type="checkbox" class="dc_day_checkbox" id="dc_sunday" name="dc_sunday" value="true"<?php echo $custom['dc_sunday'][0] == '1' ? 'checked' : ''; ?>>Sunday</input><br />
  <input type="checkbox" class="dc_day_checkbox" id="dc_monday" name="dc_monday" value="true"<?php echo $custom['dc_monday'][0] == '1' ? 'checked' : ''; ?>>Monday</input><br />
  <input type="checkbox" class="dc_day_checkbox" id="dc_tuesday" name="dc_tuesday" value="true"<?php echo $custom['dc_tuesday'][0] == '1' ? 'checked' : ''; ?>>Tuesday</input><br />
  <input type="checkbox" class="dc_day_checkbox" id="dc_wednesday" name="dc_wednesday" value="true"<?php echo $custom['dc_wednesday'][0] == '1' ? 'checked' : ''; ?>>Wednesday</input><br />
  <input type="checkbox" class="dc_day_checkbox" id="dc_thursday" name="dc_thursday" value="true"<?php echo $custom['dc_thursday'][0] == '1' ? 'checked' : ''; ?>>Thursday</input><br />
  <input type="checkbox" class="dc_day_checkbox" id="dc_friday" name="dc_friday" value="true"<?php echo $custom['dc_friday'][0] == '1' ? 'checked' : ''; ?>>Friday</input><br />
  <input type="checkbox" class="dc_day_checkbox" id="dc_saturday" name="dc_saturday" value="true"<?php echo $custom['dc_saturday'][0] == '1' ? 'checked' : ''; ?>>Saturday</input><br />
  <script>
    <?php // Use Select All to select all days! ?>
    function dcSelectAllDays(e) {
      document.querySelectorAll('.dc_day_checkbox').forEach(function(box) {
        box.checked = e.target.checked;
      });
    }
    document.querySelector('#dc_select_all_days').addEventListener('click',dcSelectAllDays);
  </script>
  <?php
}

function dc_select_ad_date_range() {
  // Function to display meta box to select date ranges to restrict ad publication within
  global $post;
  $custom = get_post_custom($post->ID);
  $start = isset($custom['dc_start_date'][0]) ? $custom['dc_start_date'][0] : '';
  $end = isset($custom['dc_end_date'][0]) ? $custom['dc_end_date'][0] : '';
  ?>
  <div class="dc-ad-admin-subtitle">Optional. Restrict ad display to between the below dates. Can roll over between months. (e.g. Start on 27th and end on 3rd of following month).</div>
  <label>Start date:</label>
  <input id="dc_start_date" name="dc_start_date" value="<?php echo esc_html($start); ?>" /><br />
  <label>End date:</label>
  <input id="dc_end_date" name="dc_end_date" value="<?php echo esc_html($end); ?>" />
  <?php
}

function dc_save_ad_info($post_id) {
  // Function to save custom meta when post is saved
  // loop to save daily info
  $days = ['dc_monday', 'dc_tuesday', 'dc_wednesday', 'dc_thursday', 'dc_friday', 'dc_saturday', 'dc_sunday'];
  foreach ($days as $day) {
    if( isset( $_POST[$day] ) ) {
      update_post_meta( $post_id, $day, '1' );
    } else {
      update_post_meta( $post_id, $day, '0' );
    }
  }

  // Save restricted dates
  $dates = ['dc_start_date','dc_end_date'];
  foreach ($dates as $date) {
    if ( isset($_POST[$date]) ) {
      update_post_meta( $post_id, $date, $_POST[$date] );
    }
  }
}
add_action('save_post', 'dc_save_ad_info');
