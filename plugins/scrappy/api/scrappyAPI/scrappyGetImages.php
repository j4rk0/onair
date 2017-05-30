<?php

// Getting images by Last FM API
function getImages($artist, $track) {

	function get_lastfm_images($artist, $track, $method = 'track.getInfo', $echo = FALSE) {

		$url = 'http://ws.audioscrobbler.com/2.0/?method=' . $method . '&api_key=' . SCRAPPY_LASTFM_API_KEY . '&artist=' . urlencode($artist) . '&track=' . urlencode($track) . '&format=json';
		if ($echo) {
			echo $url;
			exit;
		}
		return json_decode(file_get_contents($url));
	}

	function reverse_name($artist, $delimiter = ' ') {
		$array = explode($delimiter, $artist);
		if (count($array) == 2) {
			return implode($delimiter, array_reverse($array));
		}
		else
			return FALSE;
	}

	function extract_images($lfimages) {
		foreach ($lfimages as $image) {
			$images['image_' . $image->size] = $image->{'#text'};
		}
		return $images;
	}

	$return['original_data']['title'] = $track;
	$return['original_data']['artist'] = $artist;

	// to be removed eventually, radios are giving messed titles
	$result = get_lastfm_images($artist, $track);

	if (isset($result->track->name)) {
		$return['image_method'] ='track.getInfo';
		$track = $result->track->name;
		$artist = $result->track->artist->name;
		$return['artist'] = $artist;
		$return['title'] = $track;
	}

	if (isset($result->track->album->image)) {
		if (!empty($result->track->album->image[0]->{'#text'})) return array_merge(extract_images($result->track->album->image), $return);
	}

	$result = get_lastfm_images($artist, $track, 'track.search')->results->trackmatches->track;
	if (is_array($result)) {
		if(isset($result[0]->name, $result[0]->artist)){
			$return['image_method'] ='track.search';
			$track = $result[0]->name;
			$artist = $result[0]->artist;
		}
		foreach ($result as $r) {
			if (!empty($r->image[0]->{'#text'})) {
				return array_merge(
						extract_images($r->image),
						array(
							'artist' => $r->artist,
							'title' => $r->name,
							),
						$return
				);
			}
		}
	}

	// to be removed eventually
	$result = get_lastfm_images($artist, $track, 'artist.getInfo');
	if (isset($result->artist->name)) {
		$artist = $result->artist->name;
		$return['artist'] = $artist;
	}
	if (isset($result->artist->image)) {
		if (!empty($result->artist->image[0]->{'#text'})) {
			return array_merge(extract_images(get_lastfm_images($artist, $track, 'artist.getInfo')->artist->image), array('image_method' => 'artist.getInfo'), $return);
		}
	}

	$result = get_lastfm_images($artist, $track, 'artist.search')->results->artistmatches->artist;
	if (isset($result->artist->name)) {
		$return['artist'] = $artist;
	}
	if (is_array($result)) {
		foreach ($result as $r) {
			if (!empty($r->image[0]->{'#text'})) {
				return array_merge(
								extract_images($r->image),
								array(
									'image_method' => 'artist.search',
									'artist' => $r->name
									),
								$return
				);
			}
		}
	}

	return $return;
}
