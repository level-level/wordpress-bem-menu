<?php

namespace FDMGLabels\Paywall;

use Codeception\TestCase\WPTestCase;
use LevelLevel\Paywall\ContentAccess;

class ContentAccessTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->post_no_role = $this->factory()->post->create_and_get(
			array(
				'post_title'   => 'test post with paywall',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:paragraph -->
				<p>Text before paywall</p>
				<!-- /wp:paragraph -->

				<!-- wp:acf/block-ll-paywall-paywall {"name":"acf/block-ll-paywall-paywall","data":{"require_roles_for_access":"0","_require_roles_for_access":"field_5632706c"},"align":"wide","mode":"preview","id":"acf-block-64d6267625850"} /-->

				<!-- wp:paragraph -->
				<p>Text after paywall</p>
				<!-- /wp:paragraph -->',
			)
		);

		$this->post_with_role = $this->factory()->post->create_and_get(
			array(
				'post_title'   => 'test post with paywall',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:paragraph -->
				<p>Text before paywall</p>
				<!-- /wp:paragraph -->

				<!-- wp:acf/block-ll-paywall-paywall {"name":"acf/block-ll-paywall-paywall","data":{"require_roles_for_access":"1","_require_roles_for_access":"field_5632706c","allowed_user_roles":["example_role"],"_allowed_user_roles":"field_a47006e0"},"align":"wide","mode":"preview","id":"acf-block-64d633e14925b"} /-->

				<!-- wp:paragraph -->
				<p>Text after paywall</p>
				<!-- /wp:paragraph -->',
			)
		);

		$this->content_access = new ContentAccess();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_user_can_access_restricted_post_no_user(): void {
		$this->assertFalse( $this->content_access->user_can_access_restricted_post( $this->post_no_role ) );
		$this->assertFalse( $this->content_access->user_can_access_restricted_post( $this->post_with_role ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_user_can_access_restricted_post_with_non_edit_posts_user(): void {
		$user = $this->factory()->user->create_and_get(
			array(
				'user_login' => 'contentaccesssubscribertest',
				'user_email' => 'contentaccesssubscribertest@example.test',
				'role'       => 'subscriber',
			),
		);

		$this->assertTrue( $this->content_access->user_can_access_restricted_post( $this->post_no_role, $user ) );
		$this->assertFalse( $this->content_access->user_can_access_restricted_post( $this->post_with_role, $user ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_user_can_access_restricted_post_with_edit_posts_user(): void {
		$user = $this->factory()->user->create_and_get(
			array(
				'user_login' => 'contentaccesseditortest',
				'user_email' => 'contentaccesseditortest@example.test',
				'role'       => 'editor',
			),
		);

		$this->assertTrue( $this->content_access->user_can_access_restricted_post( $this->post_no_role, $user ) );
		$this->assertTrue( $this->content_access->user_can_access_restricted_post( $this->post_with_role, $user ) );
	}
}
