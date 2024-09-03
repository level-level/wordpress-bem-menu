<?php

namespace FDMGLabels\Paywall;

use LevelLevel\Paywall\ContentExtractor;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WP_Post;

class ContentExtractorTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->post = $this->factory()->post->create_and_get(
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

		$this->content_extractor = new ContentExtractor();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_disable_paywall(): void {
		update_field(
			'paywall', array(
				'disabled' => '1',
			), 'options'
		);

		$content = $this->get_post_content( $this->post );

		$this->assertStringContainsString( 'Text before paywall', $content );
		$this->assertStringContainsString( 'Text after paywall', $content );
	}

	/**
	 * Function to render post content.
	 * Because global $post is modified, please always run in separate process.
	 */
	protected function get_post_content( WP_Post $post_to_get_content ): string {
		global $post;
		setup_postdata( $post_to_get_content );
		$post = $post_to_get_content; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		ob_start();
		the_content();
		$content = ob_get_clean();
		wp_reset_postdata();
		return $content;
	}
}
