<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Templates;
use FDMGLabels\Comments\CommentWalker;

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
	public function display_comments() {
		$args = array(
			'post_id' => $this->get_id(),
			'order'   => 'ASC',
			'status'  => 'approve',
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
			'label_submit'         => _x( 'Add comment', 'Comments', 'fdmg-labels' ),
			'title_reply'          => _x( 'Leave a comment', 'Comments', 'fdmg-labels' ),
			'comment_notes_before' => '<p>' . _x( 'Your email address will not be published. A comment is held for moderation. <br>Required fields are marked *.', 'Comments', 'fdmg-labels' ) . '</p>',
			'comment_field'        => $twig->render_twig(
				'styleguide/components/comments/form-fields/textarea.twig',
				array(), true
			),
		);

		return comment_form( $args );
	}
}
