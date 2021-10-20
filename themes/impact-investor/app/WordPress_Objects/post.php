<?php

namespace Clarkson_Core\WordPress_Object;

use ImpactInvestor\Comments\CommentWalker;

class post extends base_object {
	public static $type = 'post';

	public function has_comments_enabled(): bool {
		return comments_open( $this->get_id() );
	}

	public function get_comments_count(): int {
		$comments = wp_count_comments( $this->get_id() );
		return $comments->approved;
	}

	/**
	 * @return mixed
	 */
	public function get_comments() {
		$args = array(
			'post_id' => $this->get_id(),
			'order'   => 'ASC',
			'status'  => 1,
		);

		return wp_list_comments(
			array(
				'walker' => new CommentWalker(),
				'style'  => 'ol',
			), get_comments( $args )
		);
	}

	/**
	 * @return mixed
	 */
	public function get_comment_form() {
		$args = array(
			'class_form'    => 'form-group',
			'format'        => 'html5',
			'label_submit'  => __( 'Add comment', 'impact-investor' ),
			'title_reply'   => __( 'Leave a comment', 'impact-investor' ),
			'comment_field' => '<div class="form-group__item">
									<label for="comment">' . _x( 'Comment', 'noun', 'impact-investor' ) . '</label>
									<textarea id="comment" name="comment" aria-required="true"></textarea>
								</div>',
		);

		return comment_form( $args );
	}
}
