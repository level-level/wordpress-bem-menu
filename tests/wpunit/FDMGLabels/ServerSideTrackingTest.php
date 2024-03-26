<?php

namespace FDMGLabels;

use Clarkson_Core\WordPress_Object\post;
use Codeception\TestCase\WPTestCase;
use WP_Query;

class ServerSideTrackingTest extends WPTestCase {

	public function test_user_variables(): void {
		// Create and login user
		$user = $this->factory()->user->create_and_get(
			array(
				'role'       => 'fdmg_subscriber',
				'meta_input' => array(
					'fdmg_user_id'         => '7945',
					'fdmg_organisation_id' => '7964',
				),
			)
		);
		wp_set_auth_cookie( $user->ID, false, true );
		wp_set_current_user( $user->ID, $user->user_login );

		// Get user variables
		$user_variables = array(); // Default data from plugin is empty
		$user_variables = apply_filters( 'fdmg_server_side_tracking_user_variables', $user_variables );

		// Reset the logged in user
		wp_logout();

		// Check if the user variables are correct
		$this->assertSame( '7945', $user_variables['fdmg_user_id'] );
		$this->assertSame( '7964', $user_variables['fdmg_organisation_id'] );
		$this->assertSame( '1', $user_variables['has_subscription'] );
	}

	public function test_page_variables(): void {
		// Create a post with a paywall block
		$post = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'     => 'Test article',
					'post_content'   => '<!-- wp:paragraph -->
						<p>Text before paywall</p>
						<!-- /wp:paragraph -->
						<!-- wp:acf/block-ll-paywall-paywall {"name":"acf/block-ll-paywall-paywall","data":{"require_roles_for_access":"1","_require_roles_for_access":"field_5632706c","allowed_user_roles":["example_role"],"_allowed_user_roles":"field_a47006e0"},"align":"wide","mode":"preview","id":"acf-block-64d633e14925b"} /-->
						<!-- wp:paragraph -->
						<p>Text after paywall</p>
						<!-- /wp:paragraph -->',
					'post_excerpt'   => 'Test article excerpt',
					'post_status'    => 'publish',
					'post_type'      => 'post',
					'post_date'      => '2020-01-01 12:00:00',
					'post_name'      => 'test-article',
					'comment_status' => 'open',
					'meta_input'     => array(
						'alternative_title'  => 'Test article alternative title',
						'fdmg_import_old_id' => 'article_123',
					),
				)
			)
		);

		// Overwrite the global WP_Query to be on a single post view
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$old_query                           = $GLOBALS['wp_query'];
		$GLOBALS['wp_query']                 = new WP_Query();
		$GLOBALS['wp_query']->queried_object = $post->get_object();
		$GLOBALS['wp_query']->is_singular    = true;
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		// Get page variables
		$page_variables = array(
			'post-id'       => (string) $post->get_id(),
			'post-type'     => $post->get_post_type(),
			'template-type' => 'single-post',
		);
		$page_variables = apply_filters( 'fdmg_server_side_tracking_page_variables', $page_variables );

		// Reset the global WP_Query
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_query'] = $old_query;
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		// Check if the page variables are correct
		$this->assertSame( (string) $post->get_id(), $page_variables['post-id'] );
		$this->assertSame( 'post', $page_variables['post-type'] );
		$this->assertSame( 'single-post', $page_variables['template-type'] );
		$this->assertSame( 'subscription', $page_variables['paywall'] );
	}
}
