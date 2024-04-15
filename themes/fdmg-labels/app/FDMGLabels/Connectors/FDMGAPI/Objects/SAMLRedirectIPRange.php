<?php

namespace FDMGLabels\Connectors\FDMGAPI\Objects;

/**
 * @template T of array{
 *  'idpAlias': string,
 *  'ipStart': string,
 *  'ipEnd': string,
 *  'clients': string
 * } $data
 */
class SAMLRedirectIPRange extends AbstractObject {

	/** @var T */
	protected array $data;


	/**
	 * @param T $data
	 */
	protected function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * @param T $data
	 */
	public static function from_data( array $data ): self {
		return new self( $data );
	}

	/**
	 * @return T
	 */
	public function to_data(): array {
		return $this->data;
	}

	/** @return T */
	public function jsonSerialize(): array {
		return $this->to_data();
	}
}
