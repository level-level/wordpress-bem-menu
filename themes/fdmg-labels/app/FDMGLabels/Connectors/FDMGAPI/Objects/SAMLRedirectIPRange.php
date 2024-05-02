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

	public function get_idp_alias(): string {
		return $this->data['idpAlias'];
	}

	public function get_ip_start(): string {
		return $this->data['ipStart'];
	}

	public function get_ip_end(): string {
		return $this->data['ipEnd'];
	}

	public function get_clients(): string {
		return $this->data['clients'];
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
