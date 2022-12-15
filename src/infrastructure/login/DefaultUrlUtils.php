<?php

namespace App\infrastructure\login;

use App\application\login\UrlUtils;

class DefaultUrlUtils implements UrlUtils {

	public function getBaseUrl(): string {

		$url = 'http';

		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
			$url .= 's';
		}

		return $url . '://' . $_SERVER['HTTP_HOST'];
	}

}
