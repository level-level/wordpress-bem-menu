<?php

namespace ImpactInvestor\Connectors\Selligent\Admin;

use Clarkson_Core\Templates;
use Clarkson_Core\WordPress_Object\post;
use ImpactInvestor\Connectors\Selligent\Client;
use Monolog\Registry;

class NewsLetter {
	public function register_hooks(): void {
		add_action( 'impact_investor_send_newsletter', array( $this, 'send_newsletter' ), 10, 1 );
	}

	/**
	 * @param int[] $article_ids
	 */
	public function send_newsletter( array $article_ids ): void {
		$client     = new Client();
		$has_errors = false;
		$status     = array(
			'time'       => date_i18n( 'Y-m-d H:i:s' ),
			'articles'   => array(),
			'newsletter' => null,
			'send'       => null,
		);

		foreach ( $article_ids as $article_id ) {
			$article = post::get( $article_id );

			if ( is_null( $article ) ) {
				$has_errors           = true;
				$status['articles'][] = array(
					'code'    => 'article does not exist',
					'message' => 'The given article ID is not a valid article',
					'body'    => '',
				);
				continue;
			}

			$registration_result  = $client->register_post( $article );
			$status['articles'][] = $this->get_status( $registration_result );

			if ( 200 !== $registration_result['response']['code'] ) {
				$has_errors = true;
			}
		}

		/**
		 * If there were no errors while registering articles, register the
		 * newsletter.
		 */
		if ( ! $has_errors ) {
			$newsletter_result    = $client->register_newsletter( $article_ids );
			$status['newsletter'] = $this->get_status( $newsletter_result );

			if ( 200 !== $newsletter_result['response']['code'] ) {
				$has_errors = true;
			}
		}

		/**
		 * If there was no error while registering the newsletter, send it.
		 */
		if ( ! $has_errors ) {
			$send_result    = $client->send_newsletter();
			$status['send'] = $this->get_status( $send_result );

			if ( 200 !== $send_result['response']['code'] ) {
				$has_errors = true;
			}
		}

		/**
		 * If there were no errors, remove the status message.
		 */
		if ( ! $has_errors ) {
			delete_option( Settings::META_MESSAGE );
		} else {
			$this->handle_errors( $status );
			update_option(
				Settings::META_MESSAGE, sprintf(
					__( '[%s] There was a problem while sending the newsletter', 'impact-investor' ),
					date_i18n( 'Y-m-d H:i:s' )
				)
			);
		}

		update_option( Settings::META_STATUS, $status );
	}

	/**
	 * Returns the status in a format that can be saved in the databse. If the
	 * returned body is JSON, decode that to prevent issues with serialization.
	 *
	 * @return array<string, string>
	 */
	public function get_status( array $api_response ): array {
		$body = json_decode( $api_response['body'] );

		if ( is_null( $body ) ) {
			$body = $api_response['body'];
		}

		return array(
			'code'    => $api_response['response']['code'],
			'message' => $api_response['response']['message'],
			'body'    => $body,
		);
	}

	/**
	 * If there were any errors, log it to Monolog and optionally send a email
	 * with the results of each request.
	 */
	public function handle_errors( array $status ): void {
		Registry::wordpress()->error( 'There was an error while sending the newsletter.', $status );

		$email = get_option( Status::FIELD_EMAIL, 'options' );

		if ( $email ) {
			wp_mail(
				$email,
				__( 'Error while sending Impact Investor newsletter', 'impact-investor' ),
				Templates::get_instance()->render_twig(
					'partials/admin/selligent/email.twig',
					array( 'status' => $status )
				)
			);
		}
	}
}
