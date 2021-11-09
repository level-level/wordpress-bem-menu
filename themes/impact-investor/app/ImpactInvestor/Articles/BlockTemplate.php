<?php

namespace ImpactInvestor\Articles;

use WP_Post_Type;

class BlockTemplate {

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'post_block_template' ) );
	}

	public function post_block_template(): void {
		$post = get_post_type_object( 'post' );

		if ( ! $post instanceof WP_Post_Type ) {
			return;
		}

		$post->template = array(
			array(
				'core/paragraph',
				array(
					'placeholder' => _x( 'Introduction text.', 'Block', 'impact-investor' ),
					'fontSize'    => 'intro',
				),
			),
			array( 'core/heading' ),
			array(
				'core/paragraph',
				array(
					'placeholder' => _x( 'Start article text..', 'Block', 'impact-investor' ),
				),
			),
			array( 'acf/block-social-share' ),
			array( 'acf/block-related-articles' ),
		);
	}
}
