<?php

namespace Awooga\Core;

use OAuth\OAuth2\Service\GitHub;
use OAuth\Common\Http\Exception\TokenResponseException;

class GitHubAuthService extends GitHub
{
	/**
	 * Custom service provider
	 * 
	 * This adds a fix to the way in which errors are reported
	 */
	protected function parseAccessTokenResponse($responseBody)
	{
		$data = json_decode($responseBody, true);

		if (isset($data['error_description'])) {
			throw new TokenResponseException(
				'Remote authentication error: "' . $data['error_description'] . '"'
			);
		}

		return parent::parseAccessTokenResponse($responseBody);
	}

}
