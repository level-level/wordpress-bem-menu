<?php

namespace FDMGLabels\Paywall;

use Codeception\TestCase\WPTestCase;
use LevelLevel\Paywall\ContentAccess;

class ContentAccessTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->post = $this->factory()->post->create_and_get(
			array(
				'post_title'   => 'test post with paywall',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:paragraph -->
				<p>Text before paywall</p>
				<!-- /wp:paragraph -->

				<!-- wp:acf/block-ll-paywall-paywall {"name":"acf/block-ll-paywall-paywall","data":[],"align":"wide","mode":"preview"} /-->

				<!-- wp:paragraph -->
				<p>Text after paywall</p>
				<!-- /wp:paragraph -->',
			)
		);

		$this->content_access = new ContentAccess();
	}

	public function test_user_cannot_access_restricted_post_no_user(): void {
		$this->assertFalse( $this->content_access->user_can_access_restricted_post( $this->post ) );
	}

	public function test_user_cannot_access_restricted_post_with_non_edit_posts_user(): void {
		$user = $this->factory()->user->create_and_get(
			array(
				'user_login' => 'contentaccesssubscribertest',
				'user_email' => 'contentaccesssubscribertest@example.test',
				'role'       => 'subscriber',
			),
		);

		$this->assertFalse( $this->content_access->user_can_access_restricted_post( $this->post, $user ) );
	}

	public function test_user_can_access_restricted_post_with_edit_posts_user(): void {
		$user = $this->factory()->user->create_and_get(
			array(
				'user_login' => 'contentaccesseditortest',
				'user_email' => 'contentaccesseditortest@example.test',
				'role'       => 'editor',
			),
		);

		$this->assertTrue( $this->content_access->user_can_access_restricted_post( $this->post, $user ) );
	}
}
