<?php

function get_live_song($radio_id, $getimages = FALSE) {

	$start = time();

	$inputType = false;

	$incorrectArtists = array('Informácia o práve hranej pesničke', 'Na tomto rádiu práve', 'Baví nás baviť vás');
	$incorrectTitles = array('je dočasne nedostupná', 'nehrá žiadna pesnička', 'Baví nás baviť vás');

	foreach (get_post_meta($radio_id) as $key => $value) {
		// Looking for keys (meta fields) with prefix 'crappy_field'
		if (substr($key, 0, 13) == 'scrappy_field')
			$fields[str_replace(substr($key, 0, 13) . '_', '', $key)] = $value[0];
		if ($key == 'scrappy_url')
			$scrappy_url = $value[0];
	}

	if (substr($scrappy_url, 0, 1) == "/")
		$scrappy_url = get_site_url() . $scrappy_url;
	$return['radio_id'] = $radio_id;
	$return['scrappy_url'] = $scrappy_url;
	$return['scrappy_link'] = '<a href="' . $scrappy_url . '">' . $scrappy_url . '</a>';

	// Getting remote content
	$inputText = file_get_contents($scrappy_url);

	// Unzipping in case is zipped
	$inputText = scrappy_unzip($inputText);

	// Detecting it is JSON
	$inputType = (scrappy_isJson($inputText)) ? 'json' : 'html';
	//echo $inputText;
	//exit;
	// Radio has not set proper values to parse, exit
	if (!isset($fields) || !$inputText)
		return;

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

			require_once SCRAPPY_DIR . '/api/libs/jsonpath-0.8.1.php';

			foreach ($fields as $key => $json_query) {
				$return[$key] = trim(jsonPath(json_decode($inputText, true), $json_query)[0]);
			}

			break;
	}

	foreach ($return as $key => $value) {
		if (empty($value))
			unset($return[$key]);
	}

	if (isset($return['title']) && substr($return['title'], 0, 2) == '- ')
		$return['title'] = substr($return['title'], 2);
	if (isset($return['start_time']))
		$return['start_time'] = scrappy_dateToTimestamp($return['start_time']);

	// check whenever the radio currently plays a song
	if (!empty($return['artist']) && !empty($return['title']) && !in_array($return['artist'], $incorrectArtists) && !in_array($return['title'], $incorrectTitles)) {
		$return['title'] = str_replace(array(' - NOVINKA'), array(''), $return['title']);
		$return['playing'] = TRUE;
		if ($getimages) {
			require_once 'scrappyAPI/scrappyGetImages.php';
			$return = array_merge($return, getImages($return['artist'], $return['title']));
		}
	}
	else {
		$return['playing'] = FALSE;
		unset($return['artist'], $return['title']);
	}
	$return['working_time'] = time() - $start;

	return $return;
}

?>