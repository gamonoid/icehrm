<?php

namespace Utils;

class Curl
{
	public static function get( $url ) {
		$cURLConnection = curl_init();

		curl_setopt($cURLConnection, CURLOPT_URL, $url);
		curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURLConnection, CURLOPT_USERAGENT, 'IceHrm/Updater');

		$content = curl_exec($cURLConnection);
		curl_close($cURLConnection);

		return $content;
	}
}
