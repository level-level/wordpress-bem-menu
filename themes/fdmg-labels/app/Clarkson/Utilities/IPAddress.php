<?php
namespace Clarkson\Utilities;

class IPAddress {
	public function get_visitor_ip_address(): ?string {
		$server_ip_variables = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP' );
		foreach ( $server_ip_variables as $server_ip_variable ) {
			if ( ! empty( $_SERVER[ $server_ip_variable ] ) ) {
				return $_SERVER[ $server_ip_variable ];
			}
		}
		return null;
	}

	public function validate_ip_address( string $ip_address ): bool {
		return (bool) filter_var( $ip_address, FILTER_VALIDATE_IP );
	}

	public function validate_ipv4_address( string $ip_address ): bool {
		return (bool) filter_var( $ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
	}

	public function validate_ipv6_address( string $ip_address ): bool {
		return (bool) filter_var( $ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 );
	}

	public function is_ip_in_range( string $ip_address, string $start_ip_address, string $end_ip_address ): bool {
		if ( $this->validate_ipv6_address( $ip_address ) ) {
			return $this->is_ipv6_in_range( $ip_address, $start_ip_address, $end_ip_address );
		} elseif ( $this->validate_ipv4_address( $ip_address ) ) {
			return $this->is_ipv4_in_range( $ip_address, $start_ip_address, $end_ip_address );
		}
		return false;
	}

	/**
	 * Determines if the provided IPv4 address is within the provided range.
	 */
	public function is_ipv4_in_range( string $ip_address, string $start_ip_address, string $end_ip_address ): bool {
		$all_ipv4_addresses = array( $ip_address, $start_ip_address, $end_ip_address );
		foreach ( $all_ipv4_addresses as $ipv4_address ) {
			if ( ! $this->validate_ipv4_address( $ipv4_address ) ) {
				return false;
			}
		}

		$start_ip_long = ip2long( $start_ip_address );
		$end_ip_long   = ip2long( $end_ip_address );
		$ip_long       = ip2long( $ip_address );
		if ( $start_ip_long === false || $end_ip_long === false || $ip_long === false ) {
			return false;
		}

		return $ip_long >= $start_ip_long && $ip_long <= $end_ip_long;
	}

	/**
	 * Determines if the provided IPv6 address is within the provided range.
	 */
	public function is_ipv6_in_range( string $ip_address, string $start_ip_address, string $end_ip_address ): bool {
		$all_ipv6_addresses = array( $ip_address, $start_ip_address, $end_ip_address );
		foreach ( $all_ipv6_addresses as $ipv6_address ) {
			if ( ! $this->validate_ipv6_address( $ipv6_address ) ) {
				return false;
			}
		}

		$start_ip_pton = inet_pton( $start_ip_address );
		$end_ip_pton   = inet_pton( $end_ip_address );
		$ip_pton       = inet_pton( $ip_address );
		if ( $start_ip_pton === false || $end_ip_pton === false || $ip_pton === false ) {
			return false;
		}

		return strlen( $ip_pton ) === strlen( $start_ip_pton ) && $ip_pton >= $start_ip_pton && $ip_pton <= $end_ip_pton;
	}
}
