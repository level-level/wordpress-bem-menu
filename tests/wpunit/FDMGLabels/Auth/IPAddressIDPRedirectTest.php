<?php

namespace FDMGLabels\Auth;

use lucatume\WPBrowser\TestCase\WPTestCase;
use FDMGLabels\Connectors\FDMGAPI\Objects\SAMLRedirectIPRangesResponse;

class IPAddressIDPRedirectTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		update_field(
			'fdmg_api', array(
				'site_connection' => 'energeia',
			), 'options'
		);
	}

	public function tearDown(): void {
		delete_transient( 'fdmg_labels_redirect_ip_ranges' );
		delete_field( 'fdmg_api', 'options' );
		parent::tearDown();
	}

	public function test_ip_address_to_idp_alias_transient(): void {
		$ip_address_idp_redirect = $this->make(
			IPAddressIDPRedirect::class, array(
				'get_redirect_list_from_api' => function (): array {
					$json = file_get_contents( codecept_data_dir() . '/json-responses/fdmg-api/saml/redirect-ip-ranges.json' ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					$parsed_json = json_decode( $json, true );
					$response = SAMLRedirectIPRangesResponse::from_data( $parsed_json );
					return $response->get_content();
				},
			)
		);

		$ip_address_idp_redirect_2 = $this->make(
			IPAddressIDPRedirect::class, array(
				'get_redirect_list_from_api' => function (): array {
					$json = file_get_contents( codecept_data_dir() . '/json-responses/fdmg-api/saml/redirect-ip-ranges.json' ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					$parsed_json = json_decode( $json, true );
					$parsed_json['content'] = array_slice( $parsed_json['content'], 0, 1 );
					$response = SAMLRedirectIPRangesResponse::from_data( $parsed_json );
					return $response->get_content();
				},
			)
		);

		$this->assertCount( 4, $ip_address_idp_redirect->get_redirect_list( true ), 'There should be 4 redirect ranges in the API' );
		$this->assertCount( 4, $ip_address_idp_redirect_2->get_redirect_list( true ), 'There should be 4 redirect ranges because the previous API result was stored in a transient' );
		$this->assertCount( 1, $ip_address_idp_redirect_2->get_redirect_list( false ), 'There should be 1 redirect range in the API because we bypass the transient' );
		$this->assertCount( 1, $ip_address_idp_redirect->get_redirect_list( true ), 'There should be 1 redirect range because the previous API result was stored in a transient' );
	}

	public function test_ip_address_to_idp_alias(): void {
		$ip_address_idp_redirect = $this->make(
			IPAddressIDPRedirect::class, array(
				'get_redirect_list_from_api' => function (): array {
					$json = file_get_contents( codecept_data_dir() . '/json-responses/fdmg-api/saml/redirect-ip-ranges.json' ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					$parsed_json = json_decode( $json, true );
					$response = SAMLRedirectIPRangesResponse::from_data( $parsed_json );
					return $response->get_content();
				},
			)
		);

		$this->assertNull( $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.0.1' ), 'The IP address should not correspond to any alias because energeia is not the site connection' );
		$this->assertNull( $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.0.5' ), 'The IP address should not correspond to any alias because energeia is not the site connection' );
		$this->assertNull( $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.0.11' ), 'The IP address should not correspond to any alias because energeia is not the site connection' );

		$this->assertNull( $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.1.0' ), 'The IP address should not correspond to aliastwo because of an IP that\'s out of range' );
		$this->assertSame( 'aliastwo', $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.1.1' ), 'The IP address should correspond to aliastwo' );
		$this->assertSame( 'aliastwo', $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.1.2' ), 'The IP address should correspond to aliastwo' );
		$this->assertSame( 'aliastwo', $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.1.3' ), 'The IP address should correspond to aliastwo' );
		$this->assertSame( 'aliastwo', $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.1.4' ), 'The IP address should correspond to aliastwo' );
		$this->assertSame( 'aliastwo', $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.1.5' ), 'The IP address should correspond to aliastwo' );
		$this->assertNull( $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.1.6' ), 'The IP address should not correspond to aliastwo because of an IP that\'s out of range' );

		$this->assertNull( $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.2.0' ), 'The IP address should not correspond to aliasthree because of an IP that\'s out of range' );
		$this->assertSame( 'aliasthree', $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.2.1' ), 'The IP address should correspond to aliasthree' );
		$this->assertNull( $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.2.2' ), 'The IP address should not correspond to aliasthree because of an IP that\'s out of range' );

		$this->assertNull( $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.3.0' ), 'The IP address should not correspond to aliasfour because of an IP that\'s out of range' );
		$this->assertSame( 'aliasfour', $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.3.1' ), 'The IP address should correspond to aliasfour' );
		$this->assertNull( $ip_address_idp_redirect->ip_address_to_idp_alias( '192.168.3.2' ), 'The IP address should not correspond to aliasfour because of an IP that\'s out of range' );
	}
}
