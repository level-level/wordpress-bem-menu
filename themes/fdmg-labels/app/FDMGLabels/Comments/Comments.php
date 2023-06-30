<?php

namespace ImpactInvestor\Comments;

use Clarkson_Core\Templates;

class Comments {

	public function register_hooks(): void {
		add_filter( 'comment_form_default_fields', array( $this, 'edit_comment_form' ) );
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
}
