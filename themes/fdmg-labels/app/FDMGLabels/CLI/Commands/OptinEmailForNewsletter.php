<?php

namespace FDMGLabels\CLI\Commands;

use Exception;
use FDMGLabels\Commands\OptinEmailForNewsletter as CommandsOptinEmailForNewsletter;
use WP_CLI;

use function WP_CLI\Utils\get_flag_value;

class OptinEmailForNewsletter {
	/**
	 * Subscribes the provided e-mail address to the newsletter.
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## Options
	 *
	 * <email>
	 * : E-mail address to subscribe
	 *
	 * [--commercial]
	 * : Opt-in for commercial e-mails
	 *
	 * [--research]
	 * : Opt-in for research e-mails
	 */
	public function run( array $args, array $assoc_args ): void {
		if ( ! filter_var( $args[0], FILTER_VALIDATE_EMAIL ) ) {
			WP_CLI::error( 'No valid e-mail address was given' );
		}

		$command = new CommandsOptinEmailForNewsletter(
			$args[0],
			get_flag_value( $assoc_args, 'commercial', false ),
			get_flag_value( $assoc_args, 'research', false )
		);
		try {
			$command->execute();
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getCode() . ' - ' . $e->getMessage() );
		}
		WP_CLI::success( 'Successfully opted in e-mail for newsletter' );
	}
}
