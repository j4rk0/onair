<?php

// Helper function to detect string is json
function scrappy_isJson($string) {
    return ((is_string($string) &&
            (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
}

// Get radios out of WP
function scrappy_get_radios($id) {
  $args = array(
    'posts_per_page'   => -1,
    'offset'           => 0,
    'category'         => '',
    'category_name'    => '',
    'orderby'          => 'meta_value',
    'order'            => 'DESC',
    'include'          => '',
    'exclude'          => '',
    'meta_key'         => '',
    'meta_value'       => '',
    'post_type'        => 'radio',
    'post_mime_type'   => '',
    'post_parent'      => '',
    'author'	   => '',
    'author_name'	   => '',
    'post_status'      => 'publish',
    'suppress_filters' => true,
    /*'tax_query' => array(
      array(
          'taxonomy' => 'radio_category',
          'field' => 'id',
          'terms' => 2
        )
      ),*/
    'meta_key' => 'scrappy_url',
  );
  if ( $id ) {
    $args['post__in'] = ( is_array($id) ?  $id : array($id));
  }
  return get_posts( $args );
}

// Helper function for output multidimensional array to html
function scrappy_renderList( $data ) {
  if ( !is_array( $data ) ) return; 
  $html = '';
  foreach ( $data as $key => $value ) {
    $html .= '<ul style="padding-left:15px">';
      if (is_object( $value ) ) $value = get_object_vars( $value );
      if (is_array( $value ) ) {
        $html .= '<li><strong>' . $key .':</strong> ';
        $html .= scrappy_renderList( $value );
        $html .= '</li>';
      } else {
        $html .= '<li><strong>' . $key .':</strong> '. $value . '</li>';
      }
        $html .= '</ul>';  
      }
   return $html;
}

// Helper function to unzip gzipped content
function scrappy_unzip($html) {
  return (0 === mb_strpos( $html , "\x1f" . "\x8b" . "\x08" )) ? gzdecode($html) : $html;
}

// Helper function, checks if string is a date and converts to timestamp
function scrappy_dateToTimestamp($string)
{
  $check = (is_int($string) OR is_float($string)) ? $string : (string) (int) $string;
	if (($check === $string) AND ( (int) $string <=  PHP_INT_MAX) AND ( (int) $string >= ~PHP_INT_MAX)) return $string;
  return (bool)strtotime($string) ? strtotime($string) : $string;
}

?>