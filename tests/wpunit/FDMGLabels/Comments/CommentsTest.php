<?php

namespace FDMGLabels\Comments;

use lucatume\WPBrowser\TestCase\WPTestCase;

class CommentsTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		update_option( 'require_name_email', false );
		update_option( 'comment_registration', true );

		$this->post = $this->factory()->post->create_and_get(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);
	}

	public function tearDown(): void {
		update_option( 'require_name_email', true );
		update_option( 'comment_registration', false );

		parent::tearDown();
	}

	public function test_commenting_blocked(): void {
		$user = $this->factory()->user->create_and_get(
			array(
				'role' => 'subscriber',
			)
		);

		$comment_id = wp_new_comment(
			array(
				'comment_author'       => $user->display_name,
				'comment_author_url'   => '',
				'comment_author_email' => '',
				'comment_content'      => 'some comment content',
				'comment_post_ID'      => $this->post->ID,
				'user_id'              => $user->ID,
			), true
		);
		$this->assertWPError( $comment_id );
		$this->assertEquals( $comment_id->get_error_code(), 'comment_no_permission' );
	}

	public function test_commenting_allowed(): void {
		$user = $this->factory()->user->create_and_get(
			array(
				'role' => 'subscriber',
			)
		);

		$user->add_cap( 'fdmg_write_comments' );
		$comment_id = wp_new_comment(
			array(
				'comment_author'       => $user->display_name,
				'comment_author_url'   => '',
				'comment_author_email' => '',
				'comment_content'      => 'some comment content',
				'comment_post_ID'      => $this->post->ID,
				'user_id'              => $user->ID,
			), true
		);
		$this->assertIsInt( $comment_id );
	}
}
