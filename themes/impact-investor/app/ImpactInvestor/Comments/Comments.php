<?php

namespace ImpactInvestor\Comments;

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

		$fields['cookies'] =
			'<div class="form-group__item">
				<div class="comment-form-cookies-consent">
					<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $consent . ' />' .
					'<label for="wp-comment-cookies-consent">' . __( 'Save my name and email in this browser for the next time I comment.', 'impact-investor' ) .
					'</label>
				</div>
			</div>';

		$fields['author'] =
			'<div class="form-group__item">
				<label for="author">' . __( 'Name', 'impact-investor' ) . '<span class="required">*</span></label>
				<input id="author" name="author" type="text" value="' . $commenter['comment_author'] . '" size="30" maxlength="245" required="required">
			</div>';

		$fields['email'] =
			'<div class="form-group__item">
				<label for="email">' . __( 'E-mail', 'impact-investor' ) . '<span class="required">*</span></label>
				<input id="email" name="email" type="email" value="' . $commenter['comment_author_email'] . '" size="30" maxlength="100" required="required">
			</div>';

		unset( $fields['url'] );
		return $fields;
	}
}
