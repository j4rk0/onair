<?php
//header('Content-Type: application/json');
//include_once('../api/simplehtmldom_1_5/simple_html_dom.php');
if(SCRAPPY_DIR <> 'SCRAPPY_DIR') require_once SCRAPPY_DIR.'/api/simplehtmldom_1_5/simple_html_dom.php';
else require_once getcwd().'/../api/simplehtmldom_1_5/simple_html_dom.php';
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

// Create DOM from URL or file
$html = file_get_html('http://www.radia.sk/', false, stream_context_create($arrContextOptions));

// Find all images 
foreach($html->find('div[id^=radia_onair_box_holder]') as $key => $element) {
       
       $radios[$key]['id'] = str_replace(array('radia_onair_box_holder','[',']'), array('','',''), $element->getAttribute('id'));
       
       foreach($element->find('div.radio a') as $e) {
       	$radios[$key]['name'] = $e->plaintext;
        $radios[$key]['url'] = $e->href;
        $radios[$key]['machine_name'] = basename($e->href, ".html");
       }
       
       foreach($element->find('div.interpret') as $e)
       	$radios[$key]['playing']['interpret'] = $e->plaintext;
        //$radios[$key]['playing']['interpret'] = "Elan";

       foreach($element->find('div.titul') as $e)
       	$radios[$key]['playing']['song'] = $e->plaintext;
       //$radios[$key]['playing']['song'] = 'Amnestia na neveru';
}

foreach ($radios as $key => $radio) {
  if($radio['playing']['song']=='pesničke nie je k dispozícii' || $radio['playing']['song']=='žiadna pesnička') unset($radios[$key]);
}
foreach ($radios as $key => $radio) {
  $radioss[$radio['machine_name']] = $radio;
}
$radios = $radioss;
unset($radioss);

//echo json_encode( $radios );

?>