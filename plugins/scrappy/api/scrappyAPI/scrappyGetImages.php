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

	if (isset(get_lastfm_images($artist, $track)->track->album->image[0]->{'#text'}))
		return array_merge(extract_images(get_lastfm_images($artist, $track)->track->album->image), array('method' => 'track.getInfo'));
	elseif (isset(get_lastfm_images($artist, $track, 'track.search')->results->trackmatches->track[0]->image[0]->{'#text'})) {
		$result = get_lastfm_images($artist, $track, 'track.search')->results->trackmatches->track[0];
		return array_merge(
				extract_images($result->image),
				array(
					'method' => 'track.search',
					'artist' => $result->artist,
					'title' => $result->name,
					)
		);
	}
	elseif (isset(get_lastfm_images($artist, $track, 'artist.getInfo')->artist->image[0]->{'#text'}))
		return array_merge(extract_images(get_lastfm_images($artist, $track, 'artist.getInfo')->artist->image), array('method' => 'artist.getInfo'));
	elseif (isset(get_lastfm_images($artist, $track, 'artist.search')->results->artistmatches->artist[0]->image[0]->{'#text'})) {
		$result = get_lastfm_images($artist, $track, 'artist.search')->results->trackmatches->artist[0];
		return array_merge(
						extract_images($result->image),
						array(
							'method' => 'artist.search',
							'artist' => $result->name
							)
		);
	}
	else
		return [];
}
