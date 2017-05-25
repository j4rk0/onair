<?php
/*
Description: WP REST API extension 
Version: 2
Author: j4rk0
Author URI: http://jarko.info
*/
 
class Slug_Custom_Route extends WP_REST_Controller {

  /**
   * Register the routes for the objects of the controller.
   * 
   * @namespace string
   * @base string
   * @version integer
   *
   */
  public function register_routes( $namespace = 'vendor', $base = 'live', $version = 1) {
    
    $namespace = $namespace . '/v' . $version;
    // for all radios
    register_rest_route( $namespace, '/' . $base, array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_items' ),
        'permission_callback' => array( $this, 'get_items_permissions_check' ),
      ),
    ) );
    // for rids delimited with _
    register_rest_route( $namespace, '/' . $base . '/(?P<rid>[\d_,]+)', array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_items' ),
        'permission_callback' => array( $this, 'get_items_permissions_check' ),
        'args'            => array(
          'getimages'          => array(
            'default'      => FALSE,
          ),
        ),
      ),
    ) );
    // for last fm queries
    register_rest_route( $namespace, '/lastfm', array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_lastfm' ),
        'permission_callback' => array( $this, 'get_items_permissions_check' ),
        'args'            => array(
          'url'          => array(
            'default'      => 'http://ws.audioscrobbler.com/2.0/',
          ),
          'api_key'          => array(
            'default'      => SCRAPPY_LASTFM_API_KEY,
          ),
          'method'          => array(
            'default'      => 'track.getInfo',
          ),
          'format'          => array(
            'default'      => 'json',
          ),
        ),
      ),
    ) );
  }

  /**
   * Get a collection of items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items( $request ) {
    $params = $request->get_params();
    if (isset($params['rid'])) $rids = explode('_', $params['rid']);
    $radios = scrappy_get_radios($rids);
    foreach( $radios  as $radio ) {
      $radiodata = $this->prepare_item_for_response( get_live_song($radio->ID, $params['getimages']), $request );
      $data[$radio->ID] = $radiodata;
    }
    if ($data) return new WP_REST_Response( $data, 200 );
    return new WP_Error( 'wrong-radio-id', __( 'No such radio ID', '_onair'), array( 'status' => 500 ) );
  }
  /**
   * Get last fm API response
   *
   * @param mixed query params
   * @return WP_Error|WP_REST_Response
   */
  public function get_lastfm( $request ) {
    $params = $request->get_params();
    $last_fm_url = $params['url'].'?';
    unset($params['url']);
    foreach ($params as $key => $value) {
      $last_fm_url .= '&'.$key.'='.$value;
    }
    $data = file_get_contents($last_fm_url);

    if ($data) return new WP_REST_Response(json_decode($data), 200 );
      return new WP_Error( 'wrong-radio-id', __( 'broken Last FM API url: '.$last_fm_url, '_onair'), array( 'status' => 500 ) );
  }  
 
  /**
   * Check if a given request has access to get items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check( $request ) {
    return true;
    //return current_user_can( 'view_something' ); <--use to eneable restricted access
  }
 
  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response( $item, $request ) {
    return $item;
  }
 
}



