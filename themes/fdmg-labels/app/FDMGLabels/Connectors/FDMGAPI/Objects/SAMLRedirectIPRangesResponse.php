<?php

namespace FDMGLabels\Connectors\FDMGAPI\Objects;

/**
 * @template T of array{
 *  'status': integer,
 *  'statusType': string,
 *  'content': array<
 *      array{
 *          'idpAlias': string,
 *          'ipStart': string,
 *          'ipEnd': string,
 *          'clients': string
 *      }
 *  >,
 *  'errorType': string,
 *  'message': string,
 *  'details': string,
 *  'errorCode': string,
 *  'path': string,
 *  'method': string
 * } $data
 */
class SAMLRedirectIPRangesResponse extends AbstractObject {

	/** @var T */
	protected array $data;

	/**
	 * @param T $data
	 */
	protected function __construct( array $data ) {
		$this->data = $data;
	}

	public function get_status(): int {
		return $this->data['status'];
	}

	public function get_status_type(): string {
		return $this->data['statusType'];
	}

	/**
	 * @return SAMLRedirectIPRange[]
	 */
	public function get_content(): array {
		return array_map(
			function ( array $range_data ): SAMLRedirectIPRange {
				return SAMLRedirectIPRange::from_data( $range_data );
			},
			$this->data['content']
		);
	}

	public function get_error_type(): string {
		return $this->data['errorType'];
	}

	public function get_message(): string {
		return $this->data['message'];
	}

	public function get_details(): string {
		return $this->data['details'];
	}

	public function get_error_code(): string {
		return $this->data['errorCode'];
	}

	public function get_path(): string {
		return $this->data['path'];
	}

	public function get_method(): string {
		return $this->data['method'];
	}

	/**
	 * @param T $data
	 */
	public static function from_data( array $data ): self {
		return new self( $data );
	}

	/** @return T */
	public function to_data(): array {
		return $this->data;
	}

	/** @return T */
	public function jsonSerialize(): array {
		return $this->to_data();
	}
}
