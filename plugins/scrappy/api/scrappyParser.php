<?php
function get_live_song($radio_id) {
  
  $start = time();
  
  $inputType = false;

  foreach (get_post_meta($radio_id) as $key => $value) {
    // Looking for keys (meta fields) with prefix 'crappy_field'
    if (substr($key, 0, 13) == 'scrappy_field') $fields[str_replace(substr($key, 0, 13).'_','',$key)] = $value[0];
    if ($key == 'scrappy_url') $scrappy_url = $value[0];
  }
  if (substr($scrappy_url, 0, 1) == "/")  $scrappy_url = get_site_url() . $scrappy_url;
  $return['radio_id'] = $radio_id;
  $return['scrappy_url'] = $scrappy_url;
  $return['scrappy_link'] = '<a href="'.$scrappy_url.'">'.$scrappy_url.'</a>';
  // Getting remote content
  $inputText = file_get_contents($scrappy_url);

  // Unzipping in case is zipped
  $inputText = scrappy_unzip($inputText);

  // Detecting it is JSON
  $inputType = (scrappy_isJson($inputText)) ? 'json' : 'html';
  
  // Radio has not set proper values to parse, exit
  if (!isset($fields)) return;

  $return['source_type'] = $inputType;
  
  switch ($inputType) {
      case 'html':

            $dom = new DomDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($inputText, 'HTML-ENTITIES', 'UTF-8'));
            $xpath = new DomXpath($dom);

            foreach ($fields as $key => $xpath_query) {
              $return[$key] = trim($xpath->query($xpath_query)[0]->nodeValue);
            }

            libxml_clear_errors();

          break;
      case 'json':
            
            require_once SCRAPPY_DIR.'/api/libs/jsonpath-0.8.1.php';
            
            foreach ($fields as $key => $json_query) {
              $return[$key] = trim(jsonPath(json_decode($inputText, true), $json_query)[0]);
            }

          break;
  }
  
  foreach($return as $key => $value){
    if(empty($value)) unset($return[$key]);
  } 
   
  if (isset($return['title']) && substr($return['title'], 0, 2) == '- ') $return['title'] = substr($return['title'], 2);
  if(isset($return['start_time'])) $return['start_time'] = scrappy_dateToTimestamp($return['start_time']);

  if(!empty($return['artist']) && !empty($return['title'])){
    //$corrected = get_corrected_name($return);
    //if(!empty($return['artist']) && !empty($return['title']) && !empty($corrected)) $return['corrected'] = get_corrected_name($return);
    $return['playing'] = TRUE;
  }
  $return['working_time'] = time() - $start;
  return $return;
}
  
 ?>