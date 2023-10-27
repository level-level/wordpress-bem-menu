<?php

namespace FDMGLabels\Commands;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use Exception;
use FDMGLabels\Connectors\Selligent\Client;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use WP_Error;

class SendNewsletter implements CommandInterface, LoggerAwareInterface {

	use LoggerAwareTrait;

	protected int $newsletter_id;

	/** @var Newsletter::META_SEND_TEST_OPTION_* */
	protected string $test_mode;

	protected SelligentLogger $selligent_logger;

	/**
	 * @param integer $newsletter_id
	 * @param Newsletter::META_SEND_TEST_OPTION_* $test_mode
	 */
	public function __construct(
		int $newsletter_id,
		string $test_mode
	) {
		$this->logger           = new NullLogger();
		$this->selligent_logger = new SelligentLogger();
		$this->newsletter_id    = $newsletter_id;
		$this->test_mode        = $test_mode;
	}

	/**
	 * Register articles in selligent, and then send the newsletter.
	 *
	 * @return array<string,mixed>
	 * @throws Exception
	 */
	public function execute(): array {
		$newsletter = ii_newsletter::get( $this->newsletter_id );
		if ( ! $newsletter instanceof ii_newsletter ) {
			$newsletter_not_exists_message = 'Cannot send newsletter that does not exist.';
			$this->logger?->error(
				$newsletter_not_exists_message,
				array(
					'fdmg_logger'         => $this->selligent_logger,
					'fdmg_logger_post_id' => $this->newsletter_id,
					'newsletter_id'       => $this->newsletter_id,
				)
			);
			throw new Exception( $newsletter_not_exists_message );
		}

		$started_message = 'Sending the newsletter has started.';
		if ( $this->test_mode !== Newsletter::META_SEND_TEST_OPTION_NO ) {
			$started_message = sprintf( 'Sending the newsletter in testmode %s has started.', $this->test_mode );
		}
		$this->selligent_logger->add_post_log(
			$this->newsletter_id,
			$started_message,
			LogLine::LEVEL_INFO,
		);

		// Get unique list of article ids
		$article_ids = array();
		foreach ( $newsletter->get_article_groups() as $article_group ) {
			$article_ids = array_merge( $article_ids, $article_group->get_article_ids() );
		}
		$article_ids = array_unique( $article_ids );

		// Register the articles
		$client = new Client();
		foreach ( $article_ids as $article_id ) {
			// Get the article
			$article = post::get( $article_id );
			if ( ! $article instanceof post ) {
				$post_not_exists_message = sprintf( 'Cannot send post %d that does not exist.', $article_id );
				$this->logger?->error(
					$post_not_exists_message,
					array(
						'fdmg_logger'         => $this->selligent_logger,
						'fdmg_logger_post_id' => $this->newsletter_id,
						'newsletter_id'       => $this->newsletter_id,
						'post_id'             => $article_id,
					)
				);
				throw new Exception( $post_not_exists_message );
			}

			// Register the article
			$response = $client->register_post( $article, $newsletter );
			if ( $response instanceof WP_Error ) {
				$register_post_api_message = sprintf(
					'The article %1$d - %2$s could not be registered: %3$s - %4$s.',
					$article->get_id(),
					$article->get_title(),
					(string) $response->get_error_code(),
					$response->get_error_message()
				);

				$this->logger?->error(
					$register_post_api_message,
					array(
						'fdmg_logger'         => $this->selligent_logger,
						'fdmg_logger_post_id' => $this->newsletter_id,
						'newsletter_id'       => $this->newsletter_id,
						'post_id'             => $article->get_id(),
						'response'            => $response,
					)
				);
				throw new Exception( $register_post_api_message );
			}
		}

		// Register the newsletter
		$response = $client->register_newsletter( $newsletter, $this->test_mode );
		if ( $response instanceof WP_Error ) {
			$register_newsletter_api_message = sprintf(
				'The newsletter could not be registered: %1$s - %2$s.',
				(string) $response->get_error_code(),
				$response->get_error_message()
			);
			$this->logger?->error(
				$register_newsletter_api_message,
				array(
					'fdmg_logger'         => $this->selligent_logger,
					'fdmg_logger_post_id' => $this->newsletter_id,
					'newsletter_id'       => $this->newsletter_id,
					'response'            => $response,
				)
			);
			throw new Exception( $register_newsletter_api_message );
		}

		// Send the newsletter
		$response = $client->send_newsletter( $newsletter );
		if ( $response instanceof WP_Error ) {
			$send_newsletter_api_message = sprintf(
				'The newsletter could not be sent: %1$s - %2$s.',
				(string) $response->get_error_code(),
				$response->get_error_message()
			);

			$this->logger?->error(
				$send_newsletter_api_message,
				array(
					'fdmg_logger'         => $this->selligent_logger,
					'fdmg_logger_post_id' => $this->newsletter_id,
					'newsletter_id'       => $this->newsletter_id,
					'response'            => $response,
				)
			);
			throw new Exception( $send_newsletter_api_message );
		}

		$success_message = 'The newsletter has been sent.';
		if ( $this->test_mode !== Newsletter::META_SEND_TEST_OPTION_NO ) {
			$success_message = sprintf( 'The newsletter has been sent in testmode %s.', $this->test_mode );
		}
		$this->selligent_logger->add_post_log(
			$this->newsletter_id,
			$success_message,
			LogLine::LEVEL_SUCCESS,
		);
		return $response;
	}
}
