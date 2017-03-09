<?php
  //define('SCRAPPY_DIR', WP_PLUGIN_DIR . '/scrappy');
  require_once 'http://localhost/onair/wp-content/plugins/scrappy/api/scrappyAPI.php';
  foreach (scrappy_get_radios(false) as $radio) {
    echo '<h3>' . $radio->post_title . '</h3>';
    //$scrappy_url = get_post_meta($radio->ID, 'scrappy_url')[0];
    //echo '<a href="' . $scrappy_url . '">'  . $scrappy_url . '</a>';
    //echo '<br>';
    echo scrappy_renderList( get_live_song($radio->ID) );
    echo '<hr>';    
  }
