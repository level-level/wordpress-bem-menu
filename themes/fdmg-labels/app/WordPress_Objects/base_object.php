<?php
namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\ACFPages\ThemeOptions;
use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\Objects;
use Clarkson_Core\Templates;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\Clarkson_Term;
use FDMGLabels\Comments\Comments;
use FDMGLabels\StaticPages\AuthController;
use WP_Term;
use WPSEO_Primary_Term;

class base_object extends Clarkson_Object {

	/**
	 * Get WPSEO (Yoast) primary term or first term from array
	 *
	 * @return null|Clarkson_Term
	 */
	public function get_primary_term( string $taxonomy ) {
		if ( class_exists( 'WPSEO_Primary_Term' ) ) {
			$core_objects       = Objects::get_instance();
			$wpseo_primary_term = new WPSEO_Primary_Term( $taxonomy, $this->get_id() );
			$primary_term_id    = $wpseo_primary_term->get_primary_term();

			if ( $primary_term_id ) {
				$term = get_term( (int) $primary_term_id );

				if ( $term instanceof WP_Term ) {
					return $core_objects->get_term( $term );
				}
			}
		}

		$terms = $this->get_terms( $taxonomy );
		$terms = is_array( $terms ) ? $terms : array();
		return array_shift( $terms );
	}

	/**
	 * Get estimate of reading time in minutes.
	 * The average reading speed of humans is 238 words per minute.
	 * @see https://www.sciencedirect.com/science/article/abs/pii/S0749596X19300786#:~:text=Based%20on%20the%20analysis%20of,and%20260%20wpm%20for%20fiction.
	 *
	 * It uses the raw content instead of the parsed content to determine the amount of words.
	 * This is needed to prevent an endless loop.
	 *
	 * @return int
	 */
	public function get_reading_time_in_minutes(): int {
		$content = wp_strip_all_tags( $this->get_raw_content() );
		$words   = (int) str_word_count( $content );
		$minutes = (int) ceil( $words / 238 );
		return $minutes < 1 ? 1 : $minutes;
	}

	/**
	 * @return array<string,string>
	 */
	public function get_publish_date( string $date_format = '' ): array {
		$date_format = $date_format ?: get_option( 'date_format' );

		return array(
			'date'      => $this->get_date_i18n( $date_format ),
			'datetime'  => $this->get_date_i18n( 'Y-m-d' ),
			'date_full' => $this->get_date_i18n( 'j F Y' ),
			'readable'  => $this->get_human_readable_date(),
		);
	}

	/**
	 * Gets the comment form HTML for the current object.
	 * This does not check for custom user permissions.
	 */
	public function get_comment_form(): string {
		global $post;
		// Setup global post variable for comment_form() function
		$post_backup = $post;
		setup_postdata( $this->_post );
		$post = $this->_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Setup variables needed to render comment form
		$twig                = Templates::get_instance();
		$user                = wp_get_current_user();
		$user_identity       = $user->exists() ? $user->display_name : '';
		$login_required_html = ( new Comments() )->get_subscription_required_html( $this );

		$args = array(
			'class_form'           => 'form-group',
			'format'               => 'html5',
			'label_submit'         => _x( 'Add comment', 'Comments', 'fdmg-labels' ),
			'title_reply'          => _x( 'Leave a comment', 'Comments', 'fdmg-labels' ),
			'comment_field'        => $twig->render_twig(
				'styleguide/components/comments/form-fields/textarea.twig',
				array(), true
			),
			'must_log_in'          => $login_required_html,
			'logged_in_as'         => sprintf(
				'<p class="logged-in-as">%s</p>',
				implode(
					'<br/>', array(
						sprintf(
							_x( 'Logged in as %s.', 'Comments', 'fdmg-labels' ),
							$user_identity,
						),
						_x( 'Your email address will not be published.', 'Comments', 'fdmg-labels' ),
						_x( 'A comment is held for moderation.', 'Comments', 'fdmg-labels' ),
						_x( 'Required fields are marked *.', 'Comments', 'fdmg-labels' ),
					)
				)
			),
			'comment_notes_before' => sprintf(
				'<p class="comment-notes">%s</p>',
				implode(
					'<br/>', array(
						_x( 'Your email address will not be published.', 'Comments', 'fdmg-labels' ),
						_x( 'A comment is held for moderation.', 'Comments', 'fdmg-labels' ),
						_x( 'Required fields are marked *.', 'Comments', 'fdmg-labels' ),
					)
				)
			),
		);

		ob_start();
		comment_form( $args, $this->_post );
		$html = ob_get_contents() ?: '';
		ob_end_clean();

		// Reset global post variable
		$post = $post_backup; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wp_reset_postdata();
		return $html;
	}

	/**
	 * Display the publish date of the post in a human readable format if it's been published for less than 24 hours
	 */
	public function get_human_readable_date(): string {
		$date_str     = $this->get_date();
		$date         = (int) $date_str;
		$current_time = time();
		$time_diff    = $current_time - $date;

		if ( $time_diff >= 86400 ) { // 86400 seconds = 24 hours
			return date_i18n( get_option( 'date_format' ), $date );
		} else {
			return sprintf( __( '%s ago', 'fdmg-labels' ), human_time_diff( $date, $current_time ) );
		}
	}

	public function get_imported_old_id(): ?string {
		return ( (string) $this->get_meta( 'fdmg_import_old_id', true ) ) ?: null;
	}
}
