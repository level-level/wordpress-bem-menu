<?php

namespace FDMGLabels\Comments;

use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\Templates;
use WP_Error;

class Comments {

	public function register_hooks(): void {
		add_filter( 'comment_form_default_fields', array( $this, 'edit_comment_form' ) );
		add_filter( 'pre_comment_approved', array( $this, 'block_comments_if_no_permission' ), 10, 2 );
	}

	/**
	 * Edit the comment form
	 */
	public function edit_comment_form( array $fields ): array {
		$commenter = wp_get_current_commenter();
		$consent   = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';

		$twig = Templates::get_instance();

		$fields['cookies'] = $twig->render_twig(
			'styleguide/components/comments/form-fields/cookies.twig',
			array(
				'consent' => $consent,
			), true
		);

		$fields['author'] = $twig->render_twig(
			'styleguide/components/comments/form-fields/author.twig',
			array(
				'author' => $commenter['comment_author'],
			), true
		);

		$fields['email'] = $twig->render_twig(
			'styleguide/components/comments/form-fields/email.twig',
			array(
				'email' => $commenter['comment_author_email'],
			), true
		);

		unset( $fields['url'] );

		return $fields;
	}

	/**
	 * If the user commenting has no permission to write comments, block the comment.
	 *
	 * @param integer|string|WP_Error $approved
	 * @param array<string,mixed> $comment_data
	 * @return integer|string|WP_Error
	 */
	public function block_comments_if_no_permission( int|string|WP_Error $approved, array $comment_data ): int|string|WP_Error {
		// Don't block comments if they are not of type comment
		if ( ! empty( $comment_data['comment_type'] ) && $comment_data['comment_type'] !== 'comment' ) {
			return $approved;
		}

		// Don't block comments if they are not for a post ID
		if ( empty( $comment_data['comment_post_ID'] ) ) {
			return $approved;
		}

		// Don't block comments if registration is not required
		if ( ! get_option( 'comment_registration' ) ) {
			return $approved;
		}

		// If user has permission to write comments, return to default behavior.
		if ( user_can( (int) $comment_data['user_id'], Capabilities::CAP_WRITE_COMMENTS ) ) {
			return $approved;
		}

		return new WP_Error( 'comment_no_permission', __( 'You have no permission to write comments.', 'fdmg-labels-admin' ) );
	}
}
