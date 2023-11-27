<?php
namespace FDMGLabels\Logging;

use DateTime;

final class LogLine {

	public const LEVEL_ERROR   = 'error';
	public const LEVEL_WARNING = 'warning';
	public const LEVEL_SUCCESS = 'success';
	public const LEVEL_INFO    = 'info';

	/** @var int */
	protected $timestamp = 0;

	/** @var string */
	protected $message = '';

	/** @var LogLine::LEVEL_* */
	protected $level = 'info';

	/** @var array<string,mixed> */
	protected $extra = array();

	/**
	 * @param integer $timestamp
	 * @param string $message
	 * @param LogLine::LEVEL_* $level
	 * @param array<string,mixed> $extra
	 */
	public function __construct( int $timestamp = 0, string $message = '', string $level = 'info', array $extra = array() ) {
		$this->timestamp = $timestamp;
		$this->message   = $message;
		$this->level     = $level;
		$this->extra     = $extra;
	}

	public function get_data(): array {
		return array(
			'timestamp' => $this->timestamp,
			'message'   => $this->message,
			'level'     => $this->level,
			'extra'     => $this->extra,
		);
	}

	public static function from_data( array $data ): self {
		return new self( $data['timestamp'] ?? 0, $data['message'] ?? '', $data['level'] ?? 'info', $data['extra'] ?? array() );
	}

	public function get_timestamp(): int {
		return $this->timestamp;
	}

	public function get_date(): ?DateTime {
		return DateTime::createFromFormat( 'U', (string) $this->timestamp, wp_timezone() ) ?: null;
	}

	public function set_timestamp( int $timestamp ): void {
		$this->timestamp = $timestamp;
	}

	public function get_message(): string {
		return $this->message;
	}

	public function set_message( string $message ): void {
		$this->message = $message;
	}

	/**
	 * @return LogLine::LEVEL_*
	 */
	public function get_level(): string {
		return $this->level;
	}

	/**
	 * @param LogLine::LEVEL_* $level
	 * @return void
	 */
	public function set_level( string $level ): void {
		$this->level = $level;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_extra(): array {
		return $this->extra;
	}

	/**
	 * @param array<string,mixed> $extra
	 * @return void
	 */
	public function set_extra( array $extra ): void {
		$this->extra = $extra;
	}
}
