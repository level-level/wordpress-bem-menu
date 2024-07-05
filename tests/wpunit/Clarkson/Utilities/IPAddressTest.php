<?php

namespace Clarkson\Utilities;

use lucatume\WPBrowser\TestCase\WPTestCase;

class IPAddressTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		$this->server_backup = array();
		foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $server_var ) {
			$this->server_backup[ $server_var ] = $_SERVER[ $server_var ] ?? null;
		}

		$this->ip_util = new IPAddress();
	}

	public function tearDown(): void {
		foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $server_var ) {
			if ( ! is_null( $this->server_backup[ $server_var ] ) ) {
				$_SERVER[ $server_var ] = $this->server_backup[ $server_var ];
			} else {
				unset( $_SERVER[ $server_var ] );
			}
		}
		parent::tearDown();
	}

	public function test_get_visitor_ip_address(): void {
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '192.168.1.2';
		$_SERVER['HTTP_X_FORWARDED_FOR']  = '192.168.1.3';
		$_SERVER['HTTP_X_REAL_IP']        = '192.168.1.4';
		$_SERVER['REMOTE_ADDR']           = '192.168.1.5';

		$this->assertEquals( '192.168.1.2', $this->ip_util->get_visitor_ip_address(), 'HTTP_CF_CONNECTING_IP should be used first' );
		unset( $_SERVER['HTTP_CF_CONNECTING_IP'] );
		$this->assertEquals( '192.168.1.3', $this->ip_util->get_visitor_ip_address(), 'HTTP_X_FORWARDED_FOR should be used second' );
		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		$this->assertEquals( '192.168.1.4', $this->ip_util->get_visitor_ip_address(), 'HTTP_X_REAL_IP should be used third' );
		unset( $_SERVER['HTTP_X_REAL_IP'] );
		$this->assertEquals( '192.168.1.5', $this->ip_util->get_visitor_ip_address(), 'REMOTE_ADDR should be used last' );
		unset( $_SERVER['REMOTE_ADDR'] );
		$this->assertNull( $this->ip_util->get_visitor_ip_address(), 'If no IP address is set, null should be returned' );
	}

	public function test_validate_ip_address(): void {
		$this->assertTrue( $this->ip_util->validate_ip_address( '192.168.1.2' ), 'IPV4 is a valid IP address' );
		$this->assertFalse( $this->ip_util->validate_ip_address( '192.168.1' ), 'IPV4 without a full address is not a valid IP address' );
		$this->assertTrue( $this->ip_util->validate_ip_address( '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IPV6 is a valid IP address' );
		$this->assertFalse( $this->ip_util->validate_ip_address( '2001:0db8:85a3:0000:0000:8a2e:0370:7334:0000' ), 'IPV6 with a too long address is not a valid IP address' );
	}

	public function test_validate_ipv4_address(): void {
		$this->assertTrue( $this->ip_util->validate_ipv4_address( '192.168.1.2' ), 'IPV4 is a valid IPV4 address' );
		$this->assertFalse( $this->ip_util->validate_ipv4_address( '192.168.1' ), 'IPV4 without a full address is not a valid IPV4 address' );
		$this->assertFalse( $this->ip_util->validate_ipv4_address( '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IPV6 is not a valid IPv4 address' );
		$this->assertFalse( $this->ip_util->validate_ipv4_address( '2001:0db8:85a3:0000:0000:8a2e:0370:7334:0000' ), 'IPV6 with a too long address is not a valid IPV4 address' );
	}

	public function test_validate_ipv6_address(): void {
		$this->assertFalse( $this->ip_util->validate_ipv6_address( '192.168.1.2' ), 'IPV4 is not a valid IPV6 address' );
		$this->assertFalse( $this->ip_util->validate_ipv6_address( '192.168.1' ), 'IPV4 without a full address is not a valid IPV6 address' );
		$this->assertTrue( $this->ip_util->validate_ipv6_address( '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IPV6 is a valid IPV6 address' );
		$this->assertFalse( $this->ip_util->validate_ipv6_address( '2001:0db8:85a3:0000:0000:8a2e:0370:7334:0000' ), 'IPV6 with a too long address is not a valid IPV6 address' );
	}

	public function test_ip_in_range(): void {
		$this->assertTrue( $this->ip_util->is_ip_in_range( '192.168.1.2', '192.168.1.2', '192.168.1.2' ), 'IP address should be in range where end and start are the same as the provided IP' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '192.168.1.3', '192.168.1.2', '192.168.1.4' ), 'IP address should be in range if it is between the start and end IP addresses' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '100.0.0.0', '100.0.0.0', '100.0.0.0' ), 'IP address should be in range where end and start are the same as the provided IP' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '100.50.100.150', '100.0.0.0', '100.100.150.200' ), 'IP address should be in range if it is between the start and end IP addresses' );

		$this->assertFalse( $this->ip_util->is_ip_in_range( '192.168.1.3', '192.168.1.2', '192.168.1.2' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '192.168.1.1', '192.168.1.2', '192.168.1.4' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '99.999.999.999', '100.0.0.0', '100.0.0.0' ), 'IP address should not be in range if it is not a valid IP address' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '100.100.150.200', '100.0.0.0', '100.50.100.150' ), 'IP address should not be in range if it is not between the start and end IP addresses' );

		$this->assertTrue( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IP address should be in range where end and start are the same as the provided IP' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7335', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7336' ), 'IP address should be in range if it is between the start and end IP addresses' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IP address should be in range where end and start are the same as the provided IP' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7335' ), 'IP address should be in range if it is between the start and end IP addresses' );

		$this->assertFalse( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7335', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7333', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7336' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7335', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7333', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7335' ), 'IP address should not be in range if it is not between the start and end IP addresses' );

		$this->assertFalse( $this->ip_util->is_ip_in_range( '192.168.1.2', '0000:0000:0000:0000:0000:0000:0000:0000', '9999:9999:9999:9999:9999:9999:9999:9999' ), 'IPV4 address should not be in range if the start and end IP addresses are IPV6 addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '0120:0000:0000:0000:0000:0000:0000:0000', '100.0.0.2', '192.168.1.99' ), 'IPV6 address should not be in range if the start and end IP addresses are IPV4 addresses' );
	}

	public function test_is_ipv4_in_range(): void {
		$this->assertTrue( $this->ip_util->is_ip_in_range( '192.168.1.2', '192.168.1.2', '192.168.1.2' ), 'IP address should be in range where end and start are the same as the provided IP' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '192.168.1.3', '192.168.1.2', '192.168.1.4' ), 'IP address should be in range if it is between the start and end IP addresses' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '100.0.0.0', '100.0.0.0', '100.0.0.0' ), 'IP address should be in range where end and start are the same as the provided IP' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '100.50.100.150', '100.0.0.0', '100.100.150.200' ), 'IP address should be in range if it is between the start and end IP addresses' );

		$this->assertFalse( $this->ip_util->is_ip_in_range( '192.168.1.3', '192.168.1.2', '192.168.1.2' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '192.168.1.1', '192.168.1.2', '192.168.1.4' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '99.999.999.999', '100.0.0.0', '100.0.0.0' ), 'IP address should not be in range if it is not a valid IP address' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '100.100.150.200', '100.0.0.0', '100.50.100.150' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
	}

	public function test_is_ipv6_in_range(): void {
		$this->assertTrue( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IP address should be in range where end and start are the same as the provided IP' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7335', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7336' ), 'IP address should be in range if it is between the start and end IP addresses' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IP address should be in range where end and start are the same as the provided IP' );
		$this->assertTrue( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7335' ), 'IP address should be in range if it is between the start and end IP addresses' );

		$this->assertFalse( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7335', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7333', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7336' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7335', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
		$this->assertFalse( $this->ip_util->is_ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7333', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7335' ), 'IP address should not be in range if it is not between the start and end IP addresses' );
	}
}
