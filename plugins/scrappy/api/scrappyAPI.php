<?php
/*
Description: Mainly for helper functions,which shall be moved outside plugin components
Version: 2
Author: j4rk0
Author URI: http://jarko.info
*/

// Setting up
define('UNIDELIM', '*/|/*');
if (!defined('SEE_QUERY')) define('SEE_QUERY', TRUE);
$api_key = 'ec09b194f6246092d4c38bf029fe0893';
$query_url  = 'http://ws.audioscrobbler.com/2.0/?';

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

/* Legacy begins here */


// Exploding array by array of delimiters
// We are get them by replacing all array items with unified delimiter and explode 

function explode_by_array($delim, $input) {
  $step_01 = str_replace($delim, UNIDELIM, $input); //Extra step to create a uniform value
  return explode(UNIDELIM, $step_01);
}

// Get string between two strings

function get_string_between($string, $start, $end){
  $string = ' ' . $string;
  $ini = strpos($string, $start);
  if ($ini == 0) return '';
  $ini += strlen($start);
  $len = strpos($string, $end, $ini) - $ini;
  return substr($string, $ini, $len);
}

// Array of words that usually connecting featured artist names

function split_words() {
  $sw = array('featuring', 'ft ', 'ft.', 'feat.', 'vs ', 'vs. ', '+ ', 'feat ');
  foreach ($sw as $w){
    $sw_u[] = ucfirst($w);
    $sw_u[] = ucfirst($w).'.';
    $sw_f[] = strtoupper($w);
    $sw_f[] = strtoupper($w).'.';
    $sw_a[] = $w.'.';
  }
  return array_merge($sw, $sw_u, $sw_f, $sw_a);
}

// Before processing locally we ask last fm for grooming

function get_corrected_name($item) {
  global $api_key, $query_url;
  $query = $query_url . 'method=track.getCorrection&api_key='.$api_key.'&format=json&artist='.urlencode(str_replace(' & ', ' and ', $item['artist'])).'&track='.urlencode($item['title']); 
  $content = file_get_contents($query);

  $data=json_decode($content, TRUE);
  
  if(isset($data['corrections']['correction']['track']['name']) && $data['corrections']['correction']['track']['name'] && ($data['corrections']['correction']['track']['name'] <> $item['title'])){
    // $return['corrected']['title']=  TRUE;
    $return['title'] = $data['corrections']['correction']['track']['name'];
  }
  if(isset($data['corrections']['correction']['track']['artist']['name']) && $data['corrections']['correction']['track']['artist']['name'] && $data['corrections']['correction']['track']['artist']['name'] <> $item['artist'] ){
    // $return['corrected']['artist'] = TRUE;
    $return['artist'] = $data['corrections']['correction']['track']['artist']['name'];
  }
  if (isset($return)) return $return;
  else return;
}
 
// Searching in last fm database and returning details for songs and artists

function get_last_fm_data($aname, $method = 'artist.search', $limit=1, $page=1, $query_params = array()) {
  if($_GET['debug']) echo $aname;
  global $api_key, $query_url;
  switch ($method) {
    case 'track.search':
      $keys = array('trackmatches','track', 'track.getCorrection', $query_params['track']);
    break;  
    default:
      $keys = array('artistmatches','artist','artist.getCorrection', $aname);
  }
  $query_string  = $query_url . 'method=' . $method;
  $query_string .= '&api_key='.$api_key.'&format=json';
  $query_string .= '&artist='.urlencode($aname);
  if(!empty($query_params)) $query_string .= '&' . http_build_query($query_params);
  
  // For first itteration only look on first result of last fm search
  // When no artist found exted pagination to 5 items and search page by page
  
  for($i=0; $i<4; $i++){
    $query = $query_string;
    if($i) $limit = 20;
    if($i > 1) $page++;
    //$query .= $query_params;
    $query .= '&limit='.$limit;
    $query .= '&page='.$page;
    if($_GET['debug']) {
    echo $query.'<br> query:';
    print_r($query);
    echo '<br> query params: ';
    print_r($query_params);
    echo '<hr>';
    }
    $content=file_get_contents($query);
    $data=json_decode($content, TRUE);
    if(is_object($data)) $data =  get_object_vars($data->results->{$keys[0]}->{$keys[1]});
    if(is_array($data)) $data =  $data['results'][$keys[0]][$keys[1]];
    
    //if (empty($data)) break;
    foreach ($data as $lfm_item){
      if(SEE_QUERY) $lfm_item['query'] =  $query;
      foreach($lfm_item['image'] as $ikey => $img){
        $lfm_item['image'][$img['size']] =  $img['#text'];
        unset($lfm_item['image'][$ikey]);
      }
      if($keys[3] === $lfm_item['name']) {
        return $lfm_item;
        break;
      }
      elseif(iconv_strlen($keys[3]) == iconv_strlen($lfm_item['name'])) {
        return $lfm_item;
        break;
      }
    }
    unset($quey);
  }

  return null;
}

// Reduce multidimensional to keys given as an array

function radios_reduce_array ($radios, $leave) {
  foreach ($radios as $key => $radio) {
    if(!in_array($key, $leave)) unset($radios[$key]);
  }
  return $radios;
}

// Consolidation of data returned by parser

function canonicalName($item, $lastfm = FALSE, $radio_id){
  
  $item = get_corrected_name($item);

  // Moving featured artist from song name to interpret

  if( preg_match_all( '/\((.*?)\)/', $item['song'], $matches ) ) {
    foreach(split_words() as $sw){
      foreach ($matches[1] as $mkey => $match) {
        if(strpos($match, $sw) === 0) {
          $item['song'] = str_replace($matches[0][$mkey], '', $item['song']); 
          if(strpos($item['interpret'], $match) !== FALSE) $item['interpret'] = $item['interpret'] . ' ' . $match;
        }
      }
    }
  }  

  foreach(split_words() as $ssw){
    if (strpos($item['song'], $ssw) !== FALSE){

      // Split string in two by given position

      list($beg, $end) = preg_split('/(?<=.{'.strpos($item['song'], $ssw).'})/', $item['song'], 2);
      $item['song'] = str_replace($end, '', $item['song']); 
      $item['interpret'] = $item['interpret'] . ' ' . $end;
    }
  }
  unset($ssw);
  
  $name = $item['song'];
  unset($item['song']);
  
  $sname = str_replace('�', '_', trim ($name));
  $sslug = sanitize_title($sname);
  $item['song'][$sslug]['slug'] = $sslug;
  $item['song'][$sslug]['name'] = $sname;
  
  $args = array(
    'meta_key' => 'remote_id',
    'meta_value' => $radio_id,
    'post_type' => 'radio',
    'post_status' => 'any',
    'posts_per_page' => -1
  );
  
  //echo scrappy_renderList(get_posts($args));
  //echo '<hr>';
  //echo get_posts($args)[0]->ID;
  //exit;
  
  $item['song'][$sslug]['radio_id'] = get_posts($args)[0]->ID;
  unset($name);
  
  // Removing broken chars returned by parser
  
  $name = trim(str_replace(array('�'), array('_'), $item['interpret']));
  unset($item['interpret']);
  
  foreach (split_words() as $sssword) $split_words[] = ' '.$sssword;
  
  $name = str_replace(array('[ ', ' ]'), array('[', ']'), $name);
  $names = explode_by_array($split_words,  str_replace(array(' & ', ' and ', ' And ', ' AND ', ',', ' , ', ' a ',  ' A '), UNIDELIM, $name));
  if(!empty($names)){
    foreach ($names as $nkey => $aname){
      $item['interpret']['artists'][$nkey]['name'] = trim($aname);
      $item['interpret']['artists'][$nkey]['slug'] = sanitize_title($item['interpret']['artists'][$nkey]['name']);
      $sword_candidate = strtolower(trim(get_string_between($name, $names[($nkey - 1)], $aname)));
      if (strpos($sword_candidate, 'feat') !== FALSE) $sword_candidate = 'ft.';
      if (strpos($sword_candidate, 'vs') !== FALSE) $sword_candidate = 'vs.';
      if ((strpos($sword_candidate, 'and') !== FALSE) || $sword_candidate == 'a' || $sword_candidate == '+') $sword_candidate = ',';
      $item['interpret']['artists'][$nkey]['sword'] = $sword_candidate;
      if(!$item['interpret']['artists'][$nkey]['sword']) unset($item['interpret']['artists'][$nkey]['sword']);
      
      // Now we are asking last fm for artist data
      
      if($item['interpret']['artists'][$nkey]['last_fm']['name']) $item['interpret']['artists'][$nkey]['name'] = $item['interpret']['artists'][$nkey]['last_fm']['name'];
    }
    // Removing duplicates
    $item['interpret']['artists'] = array_unique($item['interpret']['artists'], SORT_REGULAR);
 
  }
  
  // Removes empty spaces from begining and end of artist name, further grooming may be required here
  $name = trim ($name);
  $item['interpret']['original_interpret_name'] = $name;
  $slug = sanitize_title($name);
  
  $item['song'][$sslug]['slug'] = $slug . '-' .$item['song'][$sslug]['slug'];
  $item['song'][$slug . '-' .$sslug] = $item['song'][$sslug];
  unset($item['song'][$sslug]);

  foreach ($item['interpret']['artists'] as $artist){
    $groomed_name .= ' ' . $artist['sword'] . ' ' . $artist['name'];
  }
  $groomed_name = str_replace(' , ', ', ', trim($groomed_name));
  $groomed_name = str_replace('. . ', '. ', trim($groomed_name));
  
  if (!in_array($groomed_name, array_column($item['interpret']['artists'], 'name'))){
    $item['interpret']['artists']['groomed']['name'] = $groomed_name;
    $item['interpret']['artists']['groomed']['slug'] = sanitize_title($groomed_name);
  }
  $item['interpret']['groomed_name'] = $groomed_name;
  //$item['song']['groomed_slug'] = sanitize_title($groomed_name)
  //$item['song']['groomed']['last_fm'] = get_last_fm_data($groomed_name, 'track.search', 1 , 1,  array('track'=>$item['song']['name']));
  if(empty($item['interpret']['artists']['groomed'])) unset($item['interpret']['artists']['groomed']); 
  
  return $item;
}

// Check if artis exists in local DB

function artist_in_db($items = FALSE) {

  foreach($items as $item){
    if($item['last_fm']['mbid']){
      $meta_query[] = array(
                        'key'     => 'mbid',
                        'value'   => $item['last_fm']['mbid'],
                        'compare' => 'LIKE'
                      );
    }
    elseif($item['slug']) {
      $meta_query[] = array(
                        'key'     => 'slug',
                        'value'   => $item['slug'],
                        'compare' => 'LIKE'
                      );
    }
  }
  if(sizeof($meta_query)>1) {
    $meta_query = array_merge($meta_query, array('relation' => 'OR'));
  }
  
  $query =  array(
                'taxonomy'   => 'interpret',
                'hide_empty' => false,
                'meta_query' => $meta_query
              ); 
 
  $terms = get_terms($query);
  
  
  foreach($terms as $term) {
    $slugs[] = $term->slug;
  }

  foreach ($items as $key => $item){
    $return[$item['slug']]['name'] = $item['name'];
    $return[$item['slug']]['slug'] = $item['slug'];
    if(isset($item['sword'])) $return[$item['slug']]['sword'] = $item['sword'];
    if (empty($terms)) $return[$item['slug']]['action'] = 'insert';
    else{ 
        if(in_array($item['slug'], $slugs)) $return[$item['slug']]['action'] = 'none';
        else $return[$item['slug']]['action'] = 'insert';
    }
  }

  return $return;
}

function song_in_db($items = FALSE) {

  $meta_query = array(
                  'key'     => 'slug',
                  'compare' => 'IN'
                );
  
  foreach($items as $item) $meta_query['value'][] = $item['slug'];

  $args = array(
            'posts_per_page'   => -1,
            'numberposts'      => -1, 
            //'meta_key'         => 'slug',
            'meta_query'       => array($meta_query),
            'post_type'        => 'song',
            'post_status'      => 'publish',
            'suppress_filters' => true 
          );
  
  $slugs = array();
  $posts_array = get_posts( $args );

  if(is_array($posts_array)){
    foreach($posts_array as $post){ 
      //$slugs = array_merge($slugs,get_post_meta($post->ID)['slug']);
      $slugs[$post->ID] = get_post_meta($post->ID)['slug'][0];
    }
  }

  // echo scrappy_renderList($slugs);
  
  // echo '<hr>';
  
  // echo scrappy_renderList($slugs2);
  


  foreach ($items as $key => $item){
    $return[$item['slug']]['name'] = $item['name'];
    $return[$item['slug']]['slug'] = $item['slug'];
    $return[$item['slug']]['radio_id'] = $item['radio_id'];
    if(isset($item['sword'])) $return[$item['slug']]['sword'] = $item['sword'];
    if (empty($slugs)) $return[$item['slug']]['action'] = 'insert';
    else{ 
        if(in_array($item['slug'], $slugs)) $return[$item['slug']]['action'] = array_search($item['slug'], $slugs);
        else $return[$item['slug']]['action'] = 'insert';
    }
  }

 // echo scrappy_renderList($return);
 //   exit;
  return $return;
}


function create_artist($artist, $output=FALSE) {
  if($output) echo 'creating artist: <i><b>' . $artist['name'] . '</i></b> <br>';
  $new_term = wp_insert_term(
                  $artist['name'], // the term 
                  'interpret' // the taxonomy
                );
  if(is_array($new_term)){
    add_term_meta ($new_term['term_id'], 'slug', $artist['slug']);
    if(is_array($artist['last_fm'])){
      foreach ($artist['last_fm'] as $meta_key => $meta){
        if(!empty($meta)){
          add_term_meta ($new_term['term_id'], $meta_key, $meta);
        }
      }
    }
    if($output) echo 'artist <strong>' . $artist['name'] . '</strong> [' . $new_term['term_id'] . '] created <hr>';
    $return['success'] = $artist['name'].'['.$new_term['term_id'].']';
  }
  else{
    print_r($new_term);
    $return['fail'] = $artist['name'];
  }
  
  return $return;
}

function create_song($song, $output=FALSE) {
  if($output) echo 'creating song: <i><b>' . $song['name'] . '</i></b> <br>';
  
  
  $meta = $song['last_fm'];
  $meta['slug'] = $song['slug'];

  $attr = array(
            'post_title' => $song['name'],
            'post_status' => 'publish',
            'post_type' => 'song',
            'post_author' => 0,
            'meta_input' => $meta
          );

  $new_song = wp_insert_post($attr, true);
  
  if(is_numeric($new_song)){
    if($output) echo 'song <strong>' . $song['name'] . '</strong> ['.$new_song.'] created <hr>';
    $return['success']['name'] = $song['name'];
    $return['success']['id'] = $new_song;
    wp_set_post_terms( $new_song, array_column(artist_in_db($song['artists']), 'slug'), 'interpret');
    //wpdb::insert( 'table', array( 'song_id' => 'foo', 'field' => 1337 );
  }
  else{
    print_r($new_song);
    $return['fail'] = $song['name'].'['.$new_song.']';
  }
  
  return $return;
}

function lastfm_encapsulation($radios){
  $artists = array();
  $songs = array();
  foreach ($radios as $key => $radio) {
    $radios[$key]['playing'] = canonicalName($radio['playing'], TRUE, $radios[$key]['id']);
    $artists = array_merge($artists, $radios[$key]['playing']['interpret']['artists']);
    $songs = array_merge($songs, $radios[$key]['playing']['song']);
  }
  
  $db_check_artist = artist_in_db($artists);
  $db_check_song = song_in_db($songs);
  
  // Decission about items action

  foreach ($radios as $key => $radio) {
    foreach ($radio['playing']['interpret']['artists'] as $akey => $artist) {
      $radios[$key]['playing']['interpret']['artists'][$akey] = $db_check_artist[$artist['slug']];
      if($db_check_artist[$artist['slug']]['action'] == 'insert') {
        $radios[$key]['playing']['interpret']['artists'][$akey]['last_fm'] = get_last_fm_data($artist['name']);
      }
    }
    foreach ($radio['playing']['song'] as $skey => $song) {
      $radios[$key]['playing']['song'][$song['slug']]['action'] = $db_check_song[$song['slug']]['action'];
      if($db_check_song[$song['slug']]['action'] == 'insert') {
        $radios[$key]['playing']['song'][$song['slug']]['last_fm'] = get_last_fm_data($radio['playing']['interpret']['groomed_name'], 'track.search', 1 , 1,  array('track'=>$song['name']));
      }
    }    
    
  }
  
  return $radios;
}

function recent_songs($ids=FALSE) {
  global $wpdb;
  $results = $wpdb->get_results( 'SELECT radio_id, song_id, max(time) time FROM wp_scrappy_onair GROUP BY radio_id ORDER BY radio_id ASC', ARRAY_A );
  if($ids) return array_column ($results,'song_id');
  return $results;
}

?>
