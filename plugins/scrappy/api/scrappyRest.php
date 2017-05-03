<?php
 
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
        'args'            => array(
 
        ),
      ),
    ) );
    // for rids delimited with _
    register_rest_route( $namespace, '/' . $base . '/(?P<rid>[\d_,]+)', array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_items' ),
        'permission_callback' => array( $this, 'get_items_permissions_check' ),
        'args'            => array(
          'context'          => array(
            'default'      => 'view',
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
      $radiodata = $this->prepare_item_for_response( get_live_song($radio->ID), $request );
      $data[$radio->ID] = $radiodata;
    }
    if ($data) return new WP_REST_Response( $data, 200 );
    return new WP_Error( 'wrong-radio-id', __( 'No such radio ID', '_onair'), array( 'status' => 500 ) );
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
