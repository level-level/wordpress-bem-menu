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
	 * Determines if the identity provider is enabled and configured.
	 */
	public function is_available(): bool;

}
