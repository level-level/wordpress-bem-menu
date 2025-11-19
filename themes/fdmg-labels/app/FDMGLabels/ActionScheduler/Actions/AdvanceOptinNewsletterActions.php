<?php

namespace FDMGLabels\ActionScheduler\Actions;

use Exception;
use LevelLevel\ActionScheduler\Actions\AbstractSingleAction;

class AdvanceOptinNewsletterActions extends AbstractSingleAction {

	public const ADVANCE_OPTIN_NEWSLETTER_HOOK  = 'fdmg_advance_optin_newsletter_on_submission';
	public const ADVANCE_OPTIN_NEWSLETTER_GROUP = 'fdmg_advance_optin_newsletter_actions';

	/**
	 * The hook name and the Action Scheduler action name.
	 */
	public function get_hook_name(): string {
		return self::ADVANCE_OPTIN_NEWSLETTER_HOOK;
	}

	/**
	 * The action scheduler group name.
	 */
	public function get_group_name(): string {
		return self::ADVANCE_OPTIN_NEWSLETTER_GROUP;
	}

	/**
	 * Execute the code for the action.
	 * If handle() fails, you may manually call maybe_schedule_retry() to
	 * register an action for a retry, and then throw an exception.
	 *
	 * @param array<string,mixed> $data
	 * @param int $attempt
	 * @return void
	 * @throws Exception
	 */
	public function handle( array $data, int $attempt = 1 ): void {
		$success = $this->handle_optin_newsletter_on_submission( $data );
		if ( $success ) {
			return;
		}

		$retry_action_id = $this->maybe_schedule_retry( $data, $attempt );
		if ( $retry_action_id ) {
			throw new Exception( 'Failed to do something, retry was scheduled' );
		} else {
			throw new Exception( 'Failed to do something, retry was not scheduled' );
		}
	}

	/**
	 *
	 *
	 * @param array<int> $data
	 * @return boolean
	 */
	public function handle_optin_newsletter_on_submission( array $data ): bool {
		if ( empty( $data['entry_id'] ) && empty( $data['feed_id'] ) ) {
			return false;
		}
		$command = new \FDMGLabels\GravityForms\ADvance\ADvanceOptinNewsletterOnSubmission( $data['entry_id'], $data['feed_id'] );
		return $command->execute();
	}
}
