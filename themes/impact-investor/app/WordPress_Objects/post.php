<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Templates;
use ImpactInvestor\Comments\CommentWalker;

class post extends base_object {
	public static $type = 'post';

	public function get_authors(): array {
		return get_field( 'authors', $this->get_id() ) ?: array();
	}

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
		$twig = Templates::get_instance();

		$args = array(
			'class_form'           => 'form-group',
			'format'               => 'html5',
			'label_submit'         => __( 'Add comment', 'impact-investor' ),
			'title_reply'          => __( 'Leave a comment', 'impact-investor' ),
			'comment_notes_before' => '<p>' . __( 'Your email address will not be published. A comment is held for moderation. <br>Required fields are marked *.', 'impact-investor' ) . '</p>',
			'comment_field'        => $twig->render_twig(
				'styleguide/components/comments/form-fields/textarea.twig',
				array(), true
			),
		);

		return comment_form( $args );
	}
}
