<?php
/*
Plugin Name: Scrappy
Plugin URI:
Description:
Version: 2
Author: j4rk0
Author URI:
Original Author: j4rk0
Original Author URI:
*/

// Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
  die( "You can't do anything by accessing this file directly." );
}

define('SCRAPPY_DIR', WP_PLUGIN_DIR . '/scrappy');

require_once SCRAPPY_DIR.'/api/scrappyAPI.php';
require_once SCRAPPY_DIR.'/api/scrappyParser.php';
require_once SCRAPPY_DIR.'/api/scrappyRest.php';

// REST API hook for custom endpoint.
add_action( 'rest_api_init', 'register_rest_link_routes' );
/**
 * Register our routes with the REST API.
 *
 * @since 2.0.0
 */
function register_rest_link_routes() {
  require_once SCRAPPY_DIR.'/api/scrappyRest.php';
	$link_controller = new Slug_Custom_Route();
	$link_controller->register_routes('scrappy');
}

add_action('admin_menu', 'scrappy_menu');

function scrappy_menu() {
  add_menu_page('Scrappy', 'Scrappy', 'read', 'scrappy', 'scrappy_out');
  add_submenu_page('scrappy', 'Live @ radia.sk', 'Processed JSON', 'read', 'scrappy_custom_parsers', 'scrappy_custom_parsers');
}

function scrappy() {
}

function scrappy_out () {
  $start = time();
  foreach (scrappy_get_radios(false) as $radio) {
    echo '<h3>' . $radio->post_title . '</h3>';
    echo scrappy_renderList( get_live_song($radio->ID) );
    echo '<hr>';
  }
  echo 'Execution time: ' . (time() - $start);
}

function scrappy_custom_parsers () {
  foreach (scrappy_get_radios(false) as $radio) {
    $render[$radio->ID] = get_live_song($radio->ID);
  }
  ksort ($render);
  echo scrappy_renderList($render);
}
add_action('init', 'scrappy');

