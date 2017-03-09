<?php
/*
 * Custom connector for Radio Lumen
 */
header('Content-Type: application/json');
$dom = new DomDocument;
libxml_use_internal_errors(true);
$dom->loadHTML(mb_convert_encoding(file_get_contents('http://www.lumen.sk/pages/ajax/playlistActual.php'), 'HTML-ENTITIES', 'UTF-8'));
$xpath = new DomXpath($dom);

$array = explode(' -', trim($xpath->query('/html/body/p')[0]->nodeValue));
foreach ($array as $key => $value) $array[$key] = trim(html_entity_decode(str_replace('&nbsp;', ' ',htmlentities($value, ENT_QUOTES, "UTF-8"))));
$json['artist'] = (isset($array[0]) && !empty($array[0])) ? $array[0] : '';
$json['title'] = (isset($array[1]) && !empty($array[0])) ? $array[1] : '';
echo json_encode($json);
?>
