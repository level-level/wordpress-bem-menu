<?php
namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Exception;
use FDMGLabels\Commands\SendNewsletter as CommandsSendNewsletter;
use Monolog\Registry;

class NewsletterSenderService {

	/**
	 * Run command to send the newsletter
	 *
	 * @param int $newsletter_id
	 * @param Newsletter::META_PREVIEW_RECIPIENTS_OPTION_* $recipients_group
	 */
	public function send( int $newsletter_id, string $recipients_group ): void {
		$command = new CommandsSendNewsletter( $newsletter_id, $recipients_group );
		$command->setLogger( Registry::wordpress() );
		try {
			$command->execute();
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}
}
