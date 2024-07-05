<?php

namespace FDMGLabels\Connectors\FDMGAPI\Objects;

/**
 * @template T of array{
 *  'access_token': string,
 *  'expires_in': int,
 *  'refresh_expires_in': int,
 *  'token_type': string,
 *  'not-before-policy': int,
 *  'scope': string
 * } $data
 */
class OAuthTokenResponse extends AbstractObject {

	/** @var T */
	protected array $data;


	/**
	 * @param T $data
	 */
	protected function __construct( array $data ) {
		$this->data = $data;
	}

	public function get_access_token(): string {
		return $this->data['access_token'];
	}

	public function get_expires_in(): int {
		return $this->data['expires_in'];
	}

	public function get_refresh_expires_in(): int {
		return $this->data['refresh_expires_in'];
	}

	public function get_token_type(): string {
		return $this->data['token_type'];
	}

	public function get_not_before_policy(): int {
		return $this->data['not-before-policy'];
	}

	public function get_scope(): string {
		return $this->data['scope'];
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
