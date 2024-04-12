<?php

namespace FDMGLabels\Connectors\FDMGAPI\Objects;

use JsonSerializable;

/**
 * @template T of array<mixed>
 */
abstract class AbstractObject implements JsonSerializable {

	/**
	 * @param T $data
	 */
	abstract protected function __construct( array $data );

	/**
	 * @param T $data
	 */
	abstract public static function from_data( array $data ): self;

	/**
	 * @return T
	 */
	abstract public function to_data(): array;

	/**
	 * @return T
	 */
	abstract public function jsonSerialize(): array;
}
