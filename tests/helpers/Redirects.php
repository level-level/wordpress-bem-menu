<?php
namespace FDMGLabels\Tests\Helpers;

use PHPUnit\Framework\Assert;

class Redirects {

	protected string $redirect_location = '';
	protected int $redirect_status      = 0;

	// HOOK: before test
	public function start() {
		$this->reset();
		add_filter( 'wp_redirect', array( $this, 'handle_redirect' ), 1, 2 );
	}

	// HOOK: after test
	public function stop() {
		remove_filter( 'wp_redirect', array( $this, 'handle_redirect' ), 1 );
	}

	public function reset(): void {
		$this->redirect_location = '';
		$this->redirect_status   = 0;
	}

	public function handle_redirect( string $location, int $status ): bool {
		$this->redirect_location = $location;
		$this->redirect_status   = $status;
		return false;
	}

	public function get_redirect_location(): string {
		return $this->redirect_location;
	}

	public function get_redirect_status(): int {
		return $this->redirect_status;
	}

	public function assert_redirected( string $message = '' ): void {
		Assert::assertNotEmpty( $this->redirect_location, $message );
		Assert::assertGreaterThan( 0, $this->redirect_status, $message );
	}

	public function assert_not_redirected( string $message = '' ): void {
		Assert::assertEmpty( $this->redirect_location, $message );
		Assert::assertSame( 0, $this->redirect_status, $message );
	}

	public function assert_redirect_location( string $location, string $message = '' ): void {
		Assert::assertSame( $location, $this->redirect_location, $message );
	}

	public function assert_not_redirect_location( string $location, string $message = '' ): void {
		Assert::assertNotSame( $location, $this->redirect_location, $message );
	}

	public function assert_redirect_status( int $status, string $message = '' ): void {
		Assert::assertSame( $status, $this->redirect_status, $message );
	}

	public function assert_not_redirect_status( int $status, string $message = '' ): void {
		Assert::assertNotSame( $status, $this->redirect_status, $message );
	}

}
