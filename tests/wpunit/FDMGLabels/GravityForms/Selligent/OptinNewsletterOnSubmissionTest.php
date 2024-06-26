<?php

namespace FDMGLabels\Sync;

use lucatume\WPBrowser\TestCase\WPTestCase;
use FDMGLabels\GravityForms\FormSettings\IsSelligentForm;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;
use GF_Field_Consent;
use GF_Field_Email;
use GFAPI;
use GFFormDisplay;
use GFFormsModel;

class OptinNewsletterOnSubmissionTest extends WPTestCase {

	public function setUp(): void {
		$this->gf_display_submission_state = serialize( GFFormDisplay::$submission ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		parent::setUp();
	}

	public function tearDown(): void {
		global $_gf_state;
		$_gf_state                 = null;
		$_POST                     = array();
		$_REQUEST                  = array();
		GFFormDisplay::$submission = unserialize( $this->gf_display_submission_state ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		parent::tearDown();
	}

	public function test_schedule(): void {
		$form = GFAPI::get_form(
			GFAPI::add_form(
				array(
					'title'  => 'form title',
					'fields' => array(
						new GF_Field_Email(
							array(
								'ids'        => 1,
								'label'      => 'Email',
								'isRequired' => true,
							)
						),
						new GF_Field_Consent(
							array(
								'id'            => 2,
								'label'         => 'Commercial optin',
								'checkboxLabel' => 'Yes you may send me commercial emails',
								'inputs'        => array(
									array(
										'id'    => '2.1',
										'label' => 'Consent',
										'name'  => '',
									),
									array(
										'id'       => '2.2',
										'label'    => 'Text',
										'name'     => '',
										'isHidden' => true,
									),
									array(
										'id'       => '2.3',
										'label'    => 'Description',
										'name'     => '',
										'isHidden' => true,
									),
								),
								'choices'       => array(
									array(
										'text'       => 'Checked',
										'value'      => '1',
										'isSelected' => false,
										'price'      => false,
									),
								),
							)
						),
						new GF_Field_Consent(
							array(
								'id'            => 3,
								'label'         => 'Research optin',
								'checkboxLabel' => 'You may contact me for research purposes',
								'inputs'        => array(
									array(
										'id'    => '3.1',
										'label' => 'Consent',
										'name'  => '',
									),
									array(
										'id'       => '3.2',
										'label'    => 'Text',
										'name'     => '',
										'isHidden' => true,
									),
									array(
										'id'       => '3.3',
										'label'    => 'Description',
										'name'     => '',
										'isHidden' => true,
									),
								),
								'choices'       => array(
									array(
										'text'       => 'Checked',
										'value'      => '1',
										'isSelected' => false,
										'price'      => false,
									),
								),
								'visibility'    => 'hidden',
							)
						),
					),
				)
			)
		);
		GFAPI::add_feed(
			$form['id'],
			array(
				'selligent_enabled'                    => true,
				'selligent_field_map_optin_commercial' => 2,
				'selligent_field_map_optin_research'   => 3,
			),
			'selligent'
		);

		$submission_data = GFAPI::submit_form(
			$form['id'], array(
				sprintf( 'input_%s', (string) $form['fields'][0]->id ) => 'mail@example.test',
				'input_2_1' => '1',
				'input_2_2' => 'Yes you may send me commercial emails',
				'input_2_3' => GFFormsModel::get_latest_form_revisions_id( $form['id'] ),
				'input_3_1' => '',
				'input_3_2' => 'You may contact me for research purposes',
				'input_3_3' => GFFormsModel::get_latest_form_revisions_id( $form['id'] ),
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
