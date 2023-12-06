<?php
namespace FDMGLabels\Logging;

abstract class BaseLogger {

	abstract protected function get_logger_id(): string;

	/**
	 * Determines how many log lines are stored at maximum in post meta
	 */
	protected function get_meta_log_limit(): int {
		return 50;
	}

	protected function get_logger_meta_key(): string {
		return sprintf( 'log_%s', $this->get_logger_id() );
	}

	protected function get_logger_message_prefix(): string {
		return sprintf( '[%s] ', $this->get_logger_id() );
	}

	/**
	 * Add log line to user meta
	 *
	 * @param integer $user_id
	 * @param string $message
	 * @param LogLine::LEVEL_* $level
	 * @param array<string,mixed> $extra
	 * @return void
	 */
	public function add_user_log( int $user_id, string $message, string $level = 'info', $extra = array() ): void {
		$messages   = $this->get_user_logs( $user_id );
		$messages[] = new LogLine( time(), $message, $level, $extra );
		$messages   = array_slice( $messages, -$this->get_meta_log_limit() );
		$messages   = array_map(
			function( LogLine $log_line ): array {
				return $log_line->get_data();
			}, $messages
		);
		update_user_meta( $user_id, $this->get_logger_meta_key(), $messages );
	}

	/**
	 * Get log lines from user meta
	 *
	 * @return LogLine[]
	 */
	public function get_user_logs( int $user_id ): array {
		$messages = get_user_meta( $user_id, $this->get_logger_meta_key(), true );
		if ( ! is_array( $messages ) ) {
			$messages = array();
		}
		return array_map(
			function( array $message ): LogLine {
				return LogLine::from_data( $message );
			}, $messages
		);
	}

	/**
	 * Add log line to post meta
	 *
	 * @param integer $post_id
	 * @param string $message
	 * @param LogLine::LEVEL_* $level
	 * @param array<string,mixed> $extra
	 * @return void
	 */
	public function add_post_log( int $post_id, string $message, string $level = 'info', $extra = array() ): void {
		$messages   = $this->get_post_logs( $post_id );
		$messages[] = new LogLine( time(), $message, $level, $extra );
		$messages   = array_slice( $messages, -$this->get_meta_log_limit() );
		$messages   = array_map(
			function( LogLine $log_line ): array {
				return $log_line->get_data();
			}, $messages
		);
		update_post_meta( $post_id, $this->get_logger_meta_key(), $messages );
	}

	/**
	 * Get log lines from post meta
	 *
	 * @return LogLine[]
	 */
	public function get_post_logs( int $post_id ): array {
		$messages = get_post_meta( $post_id, $this->get_logger_meta_key(), true );
		if ( ! is_array( $messages ) ) {
			$messages = array();
		}
		return array_map(
			function( array $message ): LogLine {
				return LogLine::from_data( $message );
			}, $messages
		);
	}

	/**
	 * Add log line to gravityforms entry
	 *
	 * @param integer $entry_id
	 * @param string $message
	 * @param LogLine::LEVEL_* $level
	 * @param array<string,mixed> $extra
	 * @return void
	 */
	public function add_gravityforms_entry_log( int $entry_id, string $message, string $level = 'info', $extra = array() ): void {
		if ( ! function_exists( 'gform_update_meta' ) ) {
			return;
		}

		$messages   = $this->get_gravityforms_entry_logs( $entry_id );
		$messages[] = new LogLine( time(), $message, $level );
		$messages   = array_slice( $messages, -$this->get_meta_log_limit() );
		$messages   = array_map(
			function( LogLine $log_line ): array {
				return $log_line->get_data();
			}, $messages
		);
		gform_update_meta( $entry_id, $this->get_logger_meta_key(), $messages );
	}

	/**
	 * Get log lines from gravityforms entry meta
	 *
	 * @return LogLine[]
	 */
	public function get_gravityforms_entry_logs( int $entry_id ): array {
		if ( ! function_exists( 'gform_get_meta' ) ) {
			return array();
		}

		$messages = gform_get_meta( $entry_id, $this->get_logger_meta_key() );
		if ( ! is_array( $messages ) ) {
			$messages = array();
		}
		return array_map(
			function( array $message ): LogLine {
				return LogLine::from_data( $message );
			}, $messages
		);
	}
}
