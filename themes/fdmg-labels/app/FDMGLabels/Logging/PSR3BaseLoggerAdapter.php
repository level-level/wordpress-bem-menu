<?php
namespace FDMGLabels\Logging;

use FDMGLabels\Logging\LogLine;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class PSR3BaseLoggerAdapter extends AbstractLogger {

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed   $level
	 * @param string  $message
	 * @param mixed[] $context
	 *
	 * @return void
	 */
	public function log( $level, $message, array $context = array() ) {
		$fdmg_logger = $context['fdmg_logger'] ?? null;
		if ( ! $fdmg_logger instanceof BaseLogger ) {
			return;
		}

		$fdmg_error_level = $this->map_error_level( $level, $message );
		if ( isset( $context['fdmg_logger_post_id'] ) && is_int( $context['fdmg_logger_post_id'] ) ) {
			$fdmg_logger->add_post_log( $context['fdmg_logger_post_id'], $message, $fdmg_error_level, $context );
		}

		if ( isset( $context['fdmg_logger_user_id'] ) && is_int( $context['fdmg_logger_user_id'] ) ) {
			$fdmg_logger->add_user_log( $context['fdmg_logger_user_id'], $message, $fdmg_error_level, $context );
		}

		if ( isset( $context['fdmg_logger_gf_entry_id'] ) && is_int( $context['fdmg_logger_gf_entry_id'] ) ) {
			$fdmg_logger->add_gravityforms_entry_log( $context['fdmg_logger_gf_entry_id'], $message, $fdmg_error_level, $context );
		}
	}

	/**
	 * @param mixed $psr_log_level
	 * @return LogLine::LEVEL_*
	 */
	protected function map_error_level( $psr_log_level, string $message ): string {
		$fdmg_level = LogLine::LEVEL_INFO;
		switch ( $psr_log_level ) {
			case LogLevel::EMERGENCY:
			case LogLevel::ALERT:
			case LogLevel::CRITICAL:
			case LogLevel::ERROR:
				$fdmg_level = LogLine::LEVEL_ERROR;
				break;
			case LogLevel::WARNING:
				$fdmg_level = LogLine::LEVEL_WARNING;
				break;
		}

		/**
		 * PSR logging has no success log level to map from.
		 * So instead, if the $message starts with "Success: " and has been mapped to the "info" level, map it to the "success" level.
		 */
		if ( $fdmg_level === LogLine::LEVEL_INFO && strpos( $message, 'Success: ' ) === 0 ) {
			return LogLine::LEVEL_SUCCESS;
		}
		return $fdmg_level;
	}

}
