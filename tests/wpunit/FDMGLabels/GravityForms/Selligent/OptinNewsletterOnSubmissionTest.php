<?php

namespace FDMGLabels\Sync;

use Codeception\TestCase\WPTestCase;
use FDMGLabels\GravityForms\FormSettings\IsSelligentForm;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;
use GF_Field_Email;
use GFAPI;

class OptinNewsletterOnSubmissionTest extends WPTestCase {

	public function test_schedule(): void {
		$form = GFAPI::get_form(
			GFAPI::add_form(
				array(
					'title'  => 'form title',
					'fields' => array(
						new GF_Field_Email(
							array(
								'label'      => 'Email',
								'isRequired' => true,
							)
						),
					),
				)
			)
		);
		GFAPI::add_feed(
			$form['id'],
			array(
				'selligent_enabled' => true,
			),
			'selligent'
		);

		$submission_data = GFAPI::submit_form(
			$form['id'], array(
				sprintf( 'input_%s', (string) $form['fields'][0]->id ) => 'mail@example.test',
			)
		);

		$scheduled = as_has_scheduled_action( 'fdmg_labels_optin_newsletter', array( (int) $submission_data['entry_id'] ), 'fdmg_labels' );
		$this->assertTrue( $scheduled, 'Sync should be scheduled' );

		$this->assert_is_logged( (int) $submission_data['entry_id'], false );

		GFAPI::delete_entry( (int) $submission_data['entry_id'] );
		GFAPI::delete_form( $form['id'] );
		as_unschedule_action( $scheduled, 'fdmg_labels_optin_newsletter', array( (int) $submission_data['entry_id'] ), 'fdmg_labels' );
	}

	public function test_no_schedule(): void {
		$form = GFAPI::get_form(
			GFAPI::add_form(
				array(
					'title'  => 'form title',
					'fields' => array(
						new GF_Field_Email(
							array(
								'label'      => 'Email',
								'isRequired' => true,
							)
						),
					),
				)
			)
		);

		GFAPI::add_feed(
			$form['id'],
			array(
				'selligent_enabled' => false,
			),
			'selligent'
		);

		$submission_data = GFAPI::submit_form(
			$form['id'], array(
				sprintf( 'input_%s', (string) $form['fields'][0]->id ) => 'mail@example.test',
			)
		);

		$scheduled = as_has_scheduled_action( 'fdmg_labels_optin_newsletter', array( (int) $submission_data['entry_id'] ), 'fdmg_labels' );
		$this->assertFalse( $scheduled, 'Sync should not be scheduled' );

		GFAPI::delete_entry( (int) $submission_data['entry_id'] );
		GFAPI::delete_form( $form['id'] );
	}

	protected function assert_is_logged( int $entry_id, bool $testmode ) {
		$logger    = new SelligentLogger();
		$log_lines = $logger->get_gravityforms_entry_logs( $entry_id );

		$this->assertInstanceOf( LogLine::class, $log_lines[0], 'A log line should have been added.' );
		$this->assertEquals( 'info', $log_lines[0]->get_level(), 'The added log line should have error level \'info\'.' );

		if ( $testmode ) {
			$this->assertStringContainsString( 'test', $log_lines[0]->get_message(), 'The added log line should specify it is a test.' );
		}
	}
}
