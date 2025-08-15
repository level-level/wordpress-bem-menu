<?php

namespace FDMGLabels\Connectors\ADvance;

use GuzzleHttp\ClientInterface;

abstract class AbstractAPI {
	public function __construct( protected ClientInterface $client ) {}
}
