<?php

// Getting images by Last FM API
function getImages($artist, $track) {
	$url = 'http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key=' . SCRAPPY_LASTFM_API_KEY . '&artist=' . urlencode($artist) . '&track=' . urlencode($track) . '&format=json';
	$json = file_get_contents($url);

	if (isset(json_decode($json)->track->album->image, $artist, $track)) {
		foreach (json_decode($json)->track->album->image as $image) {
			$images['image_' . $image->size] = $image->{'#text'};
		}
		return $images;
	}
	// Track method did not return any data or no images, we try once again with artist method. Not so accurate but more often succesfull.
	else {
		$url = 'http://ws.audioscrobbler.com/2.0/?method=artist.getInfo&api_key=' . SCRAPPY_LASTFM_API_KEY . '&artist=' . urlencode($artist) . '&format=json';
		$json = file_get_contents($url);
		if (isset(json_decode($json)->artist->image, $artist)) {
			foreach (json_decode($json)->artist->image as $image) {
				$images['image_' . $image->size] = $image->{'#text'};
			}
			return $images;
		}
		else
			return [];
	}
}

?>