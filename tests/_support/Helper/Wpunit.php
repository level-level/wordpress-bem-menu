<?php
namespace Helper;

use Codeception\TestInterface;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Wpunit extends \Codeception\Module {

	private $original_acf_stores;
	private $original_acf_instances;

	public function _before( TestInterface $test ): void {
		global $acf_stores;
		global $acf_instances;
		$this->original_acf_stores = serialize( $acf_stores );
		$this->original_acf_instances = serialize( $acf_instances );
	}

	public function _after( TestInterface $test ): void {
		global $acf_stores;
		global $acf_instances;
		$acf_stores = unserialize( $this->original_acf_stores );
		$acf_instances = unserialize( $this->original_acf_instances );
	}

}
