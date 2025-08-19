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
	public function handle_login_page( string $redirect_url_after_login ): never;

	/**
	 * Handles actions to do after login at the identity provider.
	 */
	public function handle_after_login_page( string $redirect_url_after_login ): never;

	/**
	 * Handles how the registration page should be displayed.
	 */
	public function handle_registration_page( string $redirect_url_after_registration ): void;

	/**
	 * Handles actions to do after registration at the identity provider.
	 */
	public function handle_after_registration_page( string $redirect_url_after_registration ): void;

	/**
	 * Handles the logout page should be displayed.
	 */
	public function handle_logout_page( string $redirect_url_after_logout ): never;
}
