<?php

namespace FDMGLabels\Commands;

use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\post;
use DateTime;
use Exception;
use FDMGContentAPIClient\Api\ArticleHookApi;
use FDMGContentAPIClient\ApiException;
use FDMGContentAPIClient\Model\ErrorResponse;
use FDMGContentAPIClient\Model\SuccessResponse;
use FDMGLabels\Connectors\FDMGContentAPI\Configuration as FDMGContentAPIConfiguration;
use FDMGLabels\Connectors\FDMGContentAPI\Init as FDMGContentAPIInit;
use FDMGLabels\Connectors\FDMGContentAPI\ObjectConverter;
use FDMGLabels\Logging\FDMGContentAPI\FDMGContentAPILogger;
use GuzzleHttp\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use WP_Post;

class SendArticleToFDMGContentAPI implements CommandInterface, LoggerAwareInterface {

	use LoggerAwareTrait;

	protected int $post_id;
	protected FDMGContentAPILogger $fdmg_logger;

	public function __construct(
		int $post_id
	) {
		$this->logger      = new NullLogger();
		$this->fdmg_logger = new FDMGContentAPILogger();
		$this->post_id     = $post_id;
	}

	// phpcs:disable SlevomatCodingStandard.Commenting.ForbiddenAnnotations.AnnotationForbidden
	/**
	 * Send an article to the FDMG Content API.
	 *
	 * @return SuccessResponse|ErrorResponse
	 * @throws Exception
	 */
	public function execute(): SuccessResponse|ErrorResponse {
		// Make sure post exists
		$post = get_post( $this->post_id );
		if ( ! $post instanceof WP_Post ) {
			$error_message = 'SendArticleToFDMGContentAPI: Post does not exist.';
			if ( $this->logger ) {
				$this->logger->error(
					$error_message,
					array(
						'fdmg_logger'         => $this->fdmg_logger,
						'fdmg_logger_post_id' => $this->post_id,
						'post_id'             => $this->post_id,
					),
				);
			}
			throw new Exception( $error_message );
		}

		// Check if object is of type post
		$object = Objects::get_instance()->get_object( $post );
		if ( ! $object instanceof post ) {
			$error_message = 'SendArticleToFDMGContentAPI: Post is not of post type post.';
			if ( $this->logger ) {
				$this->logger->error(
					$error_message,
					array(
						'fdmg_logger'         => $this->fdmg_logger,
						'fdmg_logger_post_id' => $this->post_id,
						'post_id'             => $this->post_id,
					),
				);
			}
			throw new Exception( $error_message );
		}

		// Make sure FDMG Content API is enabled
		$configuration = new FDMGContentAPIConfiguration();
		if ( ! $configuration->is_enabled() ) {
			$error_message = 'SendArticleToFDMGContentAPI: FDMG Content API is not enabled.';
			if ( $this->logger ) {
				$this->logger->error(
					$error_message,
					array(
						'fdmg_logger'         => $this->fdmg_logger,
						'fdmg_logger_post_id' => $this->post_id,
						'post_id'             => $this->post_id,
					),
				);
			}
			throw new Exception( $error_message );
		}

		// Get configuration model
		$configuration_model = $configuration->get_configuration();

		// Make article object and send it to the FDMG Content API
		$article = ( new ObjectConverter() )->article( $object );
		if ( $this->logger ) {
			$this->logger->debug(
				'SendArticleToFDMGContentAPI: Prepared article',
				array(
					'fdmg_logger'         => $this->fdmg_logger,
					'fdmg_logger_post_id' => $this->post_id,
					'post_id'             => $this->post_id,
					'article'             => $article,
				),
			);
		}

		$api = new ArticleHookApi(
			new Client(
				array(
					'timeout' => 10,
				)
			), $configuration_model
		);
		try {
			$response = $api->hooksHookNamePost( $configuration->get_article_hook_name(), $article );
		} catch ( ApiException $e ) {
			$error_message = 'SendArticleToFDMGContentAPI: ' . $e->getMessage();
			if ( $this->logger ) {
				$this->logger->error(
					$error_message,
					array(
						'fdmg_logger'         => $this->fdmg_logger,
						'fdmg_logger_post_id' => $this->post_id,
						'post_id'             => $this->post_id,
						'article'             => $article,
					),
				);
			}
			throw $e;
		}

		$publish_response            = $response instanceof SuccessResponse ? $response->getPublishResponse() : null;
		$publish_response_message_id = $publish_response ? $publish_response->getPublishResult()->getMessageId() : '';

		$object->update_meta( FDMGContentAPIInit::META_LAST_UPDATED, ( new DateTime( 'now', wp_timezone() ) )->format( 'Y-m-d H:i:s' ) );
		$object->update_meta( FDMGContentAPIInit::META_PUBLISH_RESPONSE_MESSAGE_ID, $publish_response_message_id );
		if ( $this->logger ) {
			$this->logger->info(
				'Success: SendArticleToFDMGContentAPI: Successfully sent post to FDMG Content API',
				array(
					'fdmg_logger'         => $this->fdmg_logger,
					'fdmg_logger_post_id' => $this->post_id,
					'post_id'             => $this->post_id,
					'publish_response'    => $publish_response,
					'posted_article'      => $article,
				),
			);
		}

		return $response;
	}
	// phpcs:enable SlevomatCodingStandard.Commenting.ForbiddenAnnotations.AnnotationForbidden
}
