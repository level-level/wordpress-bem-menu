<?php

namespace FDMGLabels\Connectors\ADvance;

use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class NewsletterAPI extends AbstractAPI {
	public function get_newsletters_for_brand( ?bool $use_shared_brands = true ): ResponseInterface {
		$params = array(
			'UseSharedBrands' => $use_shared_brands, // optional
		);

		$response = $this->client->request(
			'POST',
			'/Newsletter/GetNewslettersForBrand/1/',
			array(
				RequestOptions::FORM_PARAMS => $params,
			)
		);

		return $response;
	}
}
