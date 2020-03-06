<?php
/**
* Plugin Name: Dine Child Hello Bar
* Plugin URI: https://github.com/
* Description: Creating a hello bar ad space for Hubs & Hops' Dine-child theme.
* Version: 1.0
* Author: Lucas Ogden-Davis
* Author URI: https://ogdendavis.com/
**/

function dc_register_session() {
  // Start a session, so we can remember when the user closes the hello bar ad
  if( !session_id() ) {
    session_start();
    $_SESSION['hello_bar'] = True;
  }
}
add_action( 'init', 'dc_register_session', 1 );

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
  // Function used in templates to add the hello bar
  if ($_SESSION['hello_bar'] == True) {
    include 'src/template.php';
  }
}

function dc_modify_session( WP_REST_Request $request ) {
  // Handle requests to endpoint for changing hello_bar session variable
  // Will always just hide hello bar for duration of the session
  $_SESSION['hello_bar'] = False;
  return $_SESSION;
}
// Register endpont for changing hello_bar session variable
add_action( 'rest_api_init', function() {
  register_rest_route( 'dc/v1', '/hello', array(
    'methods' => 'GET',
    'callback' => 'dc_modify_session'
  ));
});