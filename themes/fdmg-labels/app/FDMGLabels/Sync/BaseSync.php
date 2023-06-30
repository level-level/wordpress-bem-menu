<?php

namespace FDMGLabels\Sync;

class BaseSync {

	/**
	 * @var static[]
	 */
	protected static $instances = array();

	protected function __construct(){}

	/**
	 * Get instance of a class
	 *
	 * @psalm-suppress UnsafeInstantiation
	 *
	 * @return static
	 */
	final public static function get_instance() {
		$class = static::class;
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class();
		}

		return self::$instances[ $class ];
	}

	private function __clone() {}
	public function __sleep() {}
	public function __wakeup() {}

	public function register_hooks(): void {
		$this->add_schedule_action();
	}

	public function add_schedule_action(): void {}
	public function remove_schedule_action(): void {}
}
