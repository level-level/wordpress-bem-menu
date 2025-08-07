<?php
namespace FDMGLabels\Auth\IdentityProviders;

interface IdentityProviderInterface {

	/**
	 * Function that allows running WordPress hooks.
	 */
	public function register_hooks(): void;

	/**
	 * Returns the identifier for the identity provider.
	 * Should be unique.
	 */
	public function get_id(): string;

	/**
	 * Determines if the identity provider is enabled.
	 */
	public function is_enabled(): bool;

	/**
	 * Handles how the login page should be displayed.
	 */
	public function handle_login_page( string $redirect_url_after_login ): void;
}
